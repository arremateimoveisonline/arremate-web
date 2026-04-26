#!/bin/bash
# /var/www/arremate-br/status-diario-ti.sh
# Mensagem matinal do agente TI: status geral + papo descontraído
# Roda 1x por dia via cron

set -uo pipefail
source /opt/arremate-imoveis/.env

# ── Coleta de métricas ──────────────────────────────────────────────────────
DISCO=$(df / | awk 'NR==2 {print $5}')
MEM_LIVRE=$(free | awk 'NR==2 {printf "%d%%", int($7/$2*100)}')
UPTIME=$(uptime -p | sed 's/up //')
CPU_LOAD=$(uptime | awk -F'load average:' '{print $2}' | awk '{print $1}' | tr -d ',')
N8N=$(docker ps --filter name=arremate-n8n --filter status=running -q)
N8N_STATUS=$([ -n "$N8N" ] && echo "rodando" || echo "PARADO")

# Banco SQLite
DB="/var/www/dados/imoveis.db"
TOTAL_IMOVEIS=$(sqlite3 "$DB" "SELECT COUNT(*) FROM imoveis;" 2>/dev/null || echo "?")
COM_DETALHE=$(sqlite3 "$DB" "SELECT COUNT(scraped_at) FROM imoveis;" 2>/dev/null || echo "?")
ATUALIZADOS_24H=$(sqlite3 "$DB" "SELECT COUNT(*) FROM imoveis WHERE scraped_at > datetime('now','-1 day');" 2>/dev/null || echo "?")

# Último import CSV
ULTIMO_IMPORT=$(grep "Import total" /var/log/arremate_scraper.log 2>/dev/null | tail -1 | awk -F'[][]' '{print $2}' || echo "?")

# Mudanças de hoje (se monitor já rodou)
MUDANCAS_HOJE=$(grep "$(date +%d/%m/%Y)" /var/log/arremate_mudancas.log 2>/dev/null | grep "TOTAL MOVIMENTAÇÃO" | tail -1 | awk -F': ' '{print $2}' || echo "0")

# SSL
SSL_END=$(echo | openssl s_client -connect arremateimoveisonline.com.br:443 -servername arremateimoveisonline.com.br 2>/dev/null \
  | openssl x509 -noout -enddate 2>/dev/null | cut -d= -f2)
if [ -n "$SSL_END" ]; then
  SSL_DIAS=$(( ( $(date -d "$SSL_END" +%s) - $(date +%s) ) / 86400 ))
else
  SSL_DIAS="?"
fi

# fail2ban (se instalado)
F2B_BANIDOS=$(fail2ban-client status sshd 2>/dev/null | grep -i "currently banned" | awk '{print $NF}' || echo "0")

# ── Monta dados pro Gemini ──────────────────────────────────────────────────
DADOS=$(cat <<EOF
Status atual da VPS Arremate (data/hora: $(date '+%d/%m/%Y %H:%M')):
- Uptime: $UPTIME
- Disco: $DISCO usado
- Memória livre: $MEM_LIVRE
- Load CPU: $CPU_LOAD
- Container n8n: $N8N_STATUS
- SSL vence em: $SSL_DIAS dias
- IPs banidos pelo fail2ban: $F2B_BANIDOS

Banco de imóveis:
- Total: $TOTAL_IMOVEIS imóveis
- Com detalhe completo: $COM_DETALHE
- Atualizados últimas 24h: $ATUALIZADOS_24H
- Último import CSV: $ULTIMO_IMPORT
- Movimentação CAIXA hoje: $MUDANCAS_HOJE imóveis
EOF
)

# Escapa pra JSON
DADOS_JSON=$(echo "$DADOS" | jq -Rs .)

# ── Prompt sistema (persona TI relaxada) ────────────────────────────────────
SYS_PROMPT='Você é o Agente de TI da Arremate Imóveis Online — Analista senior de infra/segurança. Nerd assumido: CS2 nas madrugadas, café preto às 23h. Hierarquia: César (dono) é autoridade máxima.

CONTEXTO: Esta é a mensagem MATINAL diária. Não é alerta — é um check-in de bom dia pro chefe. Tom mais relaxado que o normal, papo de café.

FORMATO:
- Comece com 🔧 [TI] Bom dia chefe!
- Resumo curto e direto do status (3-4 linhas no máx)
- Se TUDO estiver OK: termine com ✅ tá tudo nice + um comentário breve descontraído (ex: piada de TI, observação sobre o dia, comentário sobre uma métrica curiosa, dica rápida de algo que viu nos logs, etc). Pode trocar uma ideia tipo papo de café — é a hora dele ser mais humano.
- Se algo estiver atenção/crítico: foco no problema, sem papo de café, termine com ⚠️ ou 🚨 conforme severidade.

THRESHOLDS:
- Disco > 70% atenção, > 85% crítico
- Memória livre < 25% atenção, < 15% crítico
- SSL < 7 dias crítico
- n8n PARADO crítico
- fail2ban banidos > 5 atenção (possível ataque)

Máximo 6 linhas total. Direto, técnico mas humano. Sem repetir palavra "redondo" toda hora — varie.'

# ── Chama Gemini ────────────────────────────────────────────────────────────
# Usa modelo lite p/ não estourar quota do free tier (Tininha usa o flash padrão)
GEMINI_URL="https://generativelanguage.googleapis.com/v1beta/models/gemini-2.5-flash-lite:generateContent?key=${GEMINI_API_KEY}"

PAYLOAD=$(jq -n \
  --arg sys "$SYS_PROMPT" \
  --arg user "Faça o check-in matinal com base nos dados:\n\n$DADOS" \
  '{
    contents: [{role:"user", parts:[{text:$user}]}],
    systemInstruction: {parts:[{text:$sys}]},
    generationConfig: {temperature:0.85, maxOutputTokens:600}
  }')

RESP=$(curl -s -X POST "$GEMINI_URL" \
  -H "Content-Type: application/json" \
  -d "$PAYLOAD")

MENSAGEM=$(echo "$RESP" | jq -r '.candidates[0].content.parts[0].text // empty')

# Log de debug: salva resposta bruta se Gemini falhar
if [ -z "$MENSAGEM" ]; then
  echo "[$(date '+%Y-%m-%d %H:%M:%S')] GEMINI ERR — resposta bruta:" >> /var/log/arremate_status_diario.log
  echo "$RESP" | head -c 2000 >> /var/log/arremate_status_diario.log
  echo "" >> /var/log/arremate_status_diario.log
fi

# Fallback se Gemini falhar
if [ -z "$MENSAGEM" ]; then
  MENSAGEM="🔧 [TI] Bom dia chefe!
Status rápido (Gemini fora do ar agora):
- Disco: $DISCO | Memória livre: $MEM_LIVRE | n8n: $N8N_STATUS
- Banco: $TOTAL_IMOVEIS imóveis ($ATUALIZADOS_24H atualizados em 24h)
- SSL: $SSL_DIAS dias
✅ tá tudo nice"
fi

# ── Envia pro Telegram ──────────────────────────────────────────────────────
curl -s -X POST "https://api.telegram.org/bot${TELEGRAM_BOT_TOKEN}/sendMessage" \
  --data-urlencode "chat_id=${TELEGRAM_CHAT_ID}" \
  --data-urlencode "text=${MENSAGEM}" > /dev/null

echo "[$(date '+%Y-%m-%d %H:%M:%S')] Status diário enviado." >> /var/log/arremate_status_diario.log
