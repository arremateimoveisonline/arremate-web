# Fluxo de Atualização de Imóveis — v1

| Campo | Valor |
|---|---|
| **Versão** | v1 |
| **Data** | 2026-04-27 |
| **Autor** | César + Claude |
| **Versão anterior** | — (documento inicial) |
| **Próxima versão** | Crie `fluxo-atualizacao-v2.md` quando algo mudar |

---

## Visão geral (resumo simples)

O site exibe imóveis da Caixa Econômica Federal. Esses imóveis precisam estar sempre atualizados em relação ao site da CAIXA. Fazemos isso em duas etapas:

1. **Import CSV** — a CAIXA disponibiliza arquivos CSV com a lista de imóveis (preço, cidade, tipo). Baixamos e importamos 2x por dia automaticamente.
2. **Scraping de detalhe** — visitamos a página de cada imóvel no site da CAIXA com um browser real (Puppeteer/Chrome) para pegar dados extras que não vêm no CSV (datas, FGTS, edital, modalidade correta, foto).

---

## De onde vem cada dado

| Campo | Fonte | Frequência |
|---|---|---|
| Preço, avaliação, desconto | CSV da CAIXA | 2x por dia (6h e 18h BRT) |
| Cidade, bairro, endereço, UF | CSV da CAIXA | 2x por dia |
| Tipo do imóvel (casa, apto...) | CSV da CAIXA | 2x por dia |
| Descrição | CSV da CAIXA | 2x por dia |
| **Modalidade** (Compra Direta, Leilão...) | Página CAIXA via Puppeteer | A cada ciclo do scraper |
| Data do leilão / encerramento | Página CAIXA via Puppeteer | A cada ciclo do scraper |
| FGTS permitido | Página CAIXA via Puppeteer | A cada ciclo do scraper |
| Financiamento permitido | Página CAIXA via Puppeteer | A cada ciclo do scraper |
| Condomínio, IPTU (quem paga) | Página CAIXA via Puppeteer | A cada ciclo do scraper |
| Foto principal | Página CAIXA via Puppeteer | A cada ciclo do scraper |
| URL do edital (PDF) | Página CAIXA via Puppeteer | A cada ciclo do scraper |
| Áreas (privativa, total, terreno) | Página CAIXA via Puppeteer | A cada ciclo do scraper |
| Status removido | Página CAIXA via Puppeteer | A cada ciclo do scraper |

> **Por que dois métodos?** O CSV é rápido e cobre todos os imóveis de uma vez, mas tem poucos campos. A página de detalhe tem muito mais informação, mas requer visitar uma página por imóvel — mais lento, porém completo.

---

## Fluxo diário hora a hora (BRT)

```
05h55  cron_atualizar.sh começa
06h00  scraper_caixa.php baixa CSVs da CAIXA e importa no banco
         → ~35.000 imóveis processados
         → ~450 imóveis marcados com csv_updated_at = agora
           (novos ou com preço/modalidade alterados)
         → dados do scraper anterior preservados (datas, FGTS, etc.)

06h05  monitor-mudancas.js registra movimentação do dia
         → compara com snapshot de ontem
         → loga entradas, saídas e mudanças de status

06h30  caixa-detail-scraper.js roda (cron a cada 30 min, --limit 100)
         → Prioridade 1: imóveis nunca raspados
         → Prioridade 2: imóveis com csv_updated_at > scraped_at (~450 marcados)
         → Prioridade 3: imóveis raspados há mais de 7 dias (manutenção)
         → Abre Chrome real, visita página da CAIXA, extrai dados

07h00  mais 100 imóveis raspados...
...
~10h00 os ~450 imóveis que mudaram hoje já foram todos re-raspados ✅

08h00  status-diario-ti.sh envia resumo matinal via Telegram

18h00  segundo import CSV (mesma lógica do 06h00)
18h05  monitor-mudancas.js (segunda rodada)
18h30  scraper processa o novo lote marcado

Resto do dia  scraper faz manutenção: raspa imóveis mais antigos
              (nenhum fica mais de 7 dias sem revisão)
```

---

## Comportamento de imóveis removidos

Quando a CAIXA remove um imóvel, o scraper detecta (`status_caixa = 'removido'`) e o imóvel:

- **Fica no banco** — nunca é deletado (a CAIXA às vezes reativa imóveis)
- **Some da busca** — `api.php` filtra `status_caixa != 'removido'` em todos os resultados
- **Acesso direto** — `imovel.php` exibe página "Imóvel indisponível — foi removido pela CAIXA"
- **Favoritos** — aparece com badge "Removido" para o usuário saber o que aconteceu
- **Reativação automática** — se a CAIXA reativar, o scraper detecta e o imóvel volta à busca sem intervenção

---

## Componentes do sistema

### 1. `cron_atualizar.sh`
**O que faz:** Script shell que roda 2x por dia na VPS. Chama o `scraper_caixa.php`.

**Onde fica:** `/var/www/arremate-br/cron_atualizar.sh`

**Crontab:**
```
0 6  * * *  /bin/bash /var/www/arremate-br/cron_atualizar.sh
0 18 * * *  /bin/bash /var/www/arremate-br/cron_atualizar.sh
```

---

### 2. `scraper_caixa.php`
**O que faz:** Baixa os CSVs da CAIXA (um por estado), importa no banco SQLite. Compara com o banco anterior e marca imóveis novos ou com dados alterados com `csv_updated_at = agora`. Preserva todos os dados que o scraper Puppeteer já tinha coletado.

**Lógica de marcação `csv_updated_at`:**
```
Imóvel marcado como "precisa re-scraping prioritário" quando:
  → É um imóvel novo (não existia ontem)
  → O preço mudou
  → A modalidade mudou
```

**Log:** `/var/log/arremate_scraper.log`

---

### 3. `caixa-detail-scraper.js` (Puppeteer)
**O que faz:** Abre o Chrome real via Puppeteer, visita a página de detalhe de cada imóvel no site da CAIXA e extrai dados extras. Usa browser real porque a CAIXA tem proteção Radware que bloqueia requisições simples (curl/PHP).

**Onde fica:** `/var/www/arremate-br/caixa-detail-scraper.js`

**Crontab VPS:**
```
30 * * * *  cd /var/www/arremate-br && node caixa-detail-scraper.js --limit 100
```
Roda a cada 30 minutos, processa 100 imóveis por vez = 4.800/dia.

**Lock file:** `/tmp/arremate_detail_scraper.lock` — impede duas instâncias simultâneas.

**Sessão renovada:** a cada 50 imóveis o browser reinicia para evitar acúmulo de memória.

**Prioridade de processamento:**
```
1º  Imóveis nunca raspados (scraped_at vazio)
2º  Imóveis marcados pelo CSV import (csv_updated_at > scraped_at)
3º  Imóveis mais antigos (scraped_at < 7 dias atrás) — manutenção
```

**Modalidades reconhecidas e normalizadas:**
| Texto na página CAIXA | Gravado no banco como |
|---|---|
| "Compra Direta" | Compra Direta |
| "Venda Direta Online" | Compra Direta (mesmo produto, nome diferente no CSV) |
| "Venda Online" | Venda Online |
| "Licitação Aberta" | Licitação Aberta |
| "1º Leilão", "2º Leilão", "Leilão Único"... | Leilão SFI - Edital Único |

**Extração de data para Venda Online:**
A data de encerramento dos imóveis Venda Online não aparece no texto visível da página — fica dentro de um trecho JavaScript (`carregaContador`). O scraper busca o padrão `"DD/MM/YYYY HH:MM:SS"` diretamente no HTML bruto quando não encontra data pelo texto visível. Para Leilão e Licitação, a data está no texto visível nos formatos `DD/MM/YYYY - HHhMM` ou `DD/MM/YYYY às HH:MM`.

**Log:** `/var/log/arremate_detail_scraper.log` (VPS) | `scraper-local.log` (local)

---

### 4. `monitor-mudancas.js`
**O que faz:** Compara o banco de hoje com o snapshot de ontem e registra quantos imóveis entraram, saíram ou mudaram de status. Apenas monitora — não altera dados. Média histórica: ~450 movimentações/dia.

**Crontab:**
```
5 6  * * *  cd /var/www/arremate-br && node monitor-mudancas.js
5 18 * * *  cd /var/www/arremate-br && node monitor-mudancas.js
```

**Log:** `/var/log/arremate_mudancas.log`

---

### 5. `status-diario-ti.sh`
**O que faz:** Toda manhã às 8h BRT coleta métricas da VPS (disco, memória, n8n, SSL, banco) e envia mensagem via Telegram usando Gemini AI (modelo `gemini-2.5-flash-lite`, 1.500 req/dia grátis) com a persona do "Agente TI". Se o Gemini falhar, envia um resumo de fallback com os dados brutos.

**Métricas coletadas:** disco %, memória livre %, uptime, CPU load, status container n8n, dias SSL, IPs banidos (fail2ban), total imóveis, atualizados em 24h, último import CSV, movimentação do dia.

**Thresholds de alerta:**
```
Disco    > 85%  → CRÍTICO | > 70% → ATENÇÃO
Memória  < 15%  → CRÍTICO | < 25% → ATENÇÃO
SSL      < 7 dias → CRÍTICO
n8n parado → CRÍTICO
fail2ban > 5 IPs/h → ATENÇÃO (possível ataque)
```

**Crontab:**
```
0 8 * * *  /bin/bash /var/www/arremate-br/status-diario-ti.sh
```

**Log:** `/var/log/arremate_status_diario.log`

---

### 6. `monitor-vps.sh`
**O que faz:** Roda a cada 5 minutos e dispara alertas Telegram imediatos se algum threshold crítico for atingido (disco, memória, n8n, SSL). É o sistema de alerta em tempo real — complementar ao status diário do item 5.

**Crontab:**
```
*/5 * * * *  bash /opt/arremate-imoveis/scripts/monitor-vps.sh
```

---

### 7. `api.php`
**O que faz:** API JSON consumida pelo frontend para busca, filtro e paginação de imóveis. Todas as queries excluem automaticamente imóveis com `status_caixa = 'removido'` — busca, contagem por cidade e totais gerais.

**Ações disponíveis:** `buscar` | `detalhe` | `cidades` | `stats`

> A ação `detalhe` retorna o imóvel mesmo se removido — necessário para o badge de "Removido" nos favoritos.

---

### 8. `imovel.php`
**O que faz:** Página de detalhe de um imóvel. Se o imóvel tiver `status_caixa = 'removido'`, exibe página "Imóvel indisponível — foi removido pela CAIXA" em vez dos dados. Se não encontrado, exibe "Imóvel não encontrado".

---

### 9. `deploy-hotfix.js`
**O que faz:** Script local (roda no PC do César) que faz deploy de código e banco para a VPS via SFTP+SSH.

**Como usar:**
```
node deploy-hotfix.js
```
Roda na pasta `C:/Users/César/Downloads/arremate-br/`

**O que o deploy faz (em ordem):**
1. Upload dos arquivos PHP/JS/HTML via SFTP
2. **Backup automático do banco** na VPS: `/var/www/dados/imoveis.db.backup-YYYYMMDDHHMM`
3. **Upload do banco local** (`C:/xampp/htdocs/dados/imoveis.db`) para a VPS
4. Migrações de banco (ALTER TABLE para novas colunas — idempotente)
5. Correções de dados (encoding, normalização de modalidades)
6. Ajuste de permissões (`www-data`)

---

## Banco de dados

**Localização:**
- Produção (VPS): `/var/www/dados/imoveis.db`
- Local (desenvolvimento): `C:/xampp/htdocs/dados/imoveis.db`

**Tamanho:** ~22MB | **Total de imóveis:** 35.079

**Backups na VPS:**
```
/var/www/dados/imoveis.db.backup-202604271832  ← mais recente (2026-04-27)
```
> Backups são criados automaticamente pelo `deploy-hotfix.js` antes de subir um banco novo.

**Código-fonte no GitHub:** `github.com/arremateimoveisonline/arremate-web` (branch `main`)

**Colunas principais da tabela `imoveis`:**

| Coluna | Fonte | Descrição |
|---|---|---|
| `hdnimovel` | CSV | ID único do imóvel na CAIXA |
| `preco` | CSV | Preço de venda |
| `modalidade` | Puppeteer | Tipo de venda (Compra Direta, Leilão...) |
| `scraped_at` | Puppeteer | Última visita do scraper à página |
| `csv_updated_at` | CSV import | Última vez que o CSV marcou este imóvel como alterado |
| `data_leilao_1` | Puppeteer | Data do 1º leilão |
| `data_encerramento` | Puppeteer | Data de encerramento |
| `status_caixa` | Puppeteer | `"removido"` se a CAIXA retirou o imóvel |
| `fgts` | Puppeteer | 1 = permite uso de FGTS |
| `financiamento` | Puppeteer | 1 = permite financiamento |
| `edital_url` | Puppeteer | URL do PDF do edital |
| `foto_url` | Puppeteer | URL da foto principal |
| `area_privativa` | Puppeteer | Área privativa em m² |
| `area_total` | Puppeteer | Área total em m² |
| `area_terreno` | Puppeteer | Área do terreno em m² |

---

## Distribuição de modalidades (estado em 2026-04-27)

| Modalidade | Ativos | Removidos |
|---|---|---|
| Compra Direta | 15.857 | — |
| Leilão SFI - Edital Único | 8.305 | — |
| Venda Online | 5.146 | — |
| Licitação Aberta | 3.082 | — |
| **Total ativos** | **32.391** | **2.688** |

> Venda Online com data de encerramento preenchida: **4.882** (demais estavam encerrados ou removidos no momento do scraping).

---

## Por que Puppeteer e não curl/PHP?

A CAIXA usa o **Radware Bot Manager** que detecta e bloqueia requisições automatizadas simples (curl, PHP) — retorna uma página CAPTCHA de ~6KB no lugar do conteúdo real. O Puppeteer abre um Chrome real que resolve os desafios JavaScript do Radware normalmente. O bloqueio é por IP e temporário (libera após horas/dias). Identificado em abril/2026.

---

## Proteções do scraper

| Proteção | Como funciona |
|---|---|
| Lock file | Impede duas instâncias simultâneas |
| Sessão renovada | Browser reinicia a cada 50 imóveis |
| Limite por execução | `--limit 100` por cron (ritmo seguro, não dispara Radware) |
| Detecção de bloqueio | HTML < 10KB = Radware bloqueando, pula o imóvel |
| Fallback de banco | Se better-sqlite3 falhar, usa sqlite3 CLI (Linux/VPS) |

---

## Todos os logs do sistema

| Log | Localização | O que registra |
|---|---|---|
| Import CSV | `/var/log/arremate_scraper.log` | Importações, total de imóveis, erros |
| Detail scraper | `/var/log/arremate_detail_scraper.log` | Cada imóvel raspado, dados extraídos |
| Mudanças CAIXA | `/var/log/arremate_mudancas.log` | Entradas, saídas, mudanças de status diárias |
| Status TI | `/var/log/arremate_status_diario.log` | Mensagem matinal enviada + erros Gemini |

---

## Crontab completo da VPS (root)

```
*/5 * * * *   bash /opt/arremate-imoveis/scripts/monitor-vps.sh > /dev/null 2>&1
5 6    * * *  cd /var/www/arremate-br && node monitor-mudancas.js >> /var/log/arremate_mudancas.log 2>&1
5 18   * * *  cd /var/www/arremate-br && node monitor-mudancas.js >> /var/log/arremate_mudancas.log 2>&1
30 * * * *    cd /var/www/arremate-br && /usr/bin/node caixa-detail-scraper.js --limit 100 >> /var/log/arremate_scraper.log 2>&1
0 6    * * *  /bin/bash /var/www/arremate-br/cron_atualizar.sh
0 18   * * *  /bin/bash /var/www/arremate-br/cron_atualizar.sh
0 8    * * *  /bin/bash /var/www/arremate-br/status-diario-ti.sh
```

---

## Como recuperar a VPS do zero

Se a VPS travar ou precisar reconfigurar:

1. **Código:** `git clone github.com/arremateimoveisonline/arremate-web` na pasta `/var/www/arremate-br/`
2. **Banco:** copiar o backup mais recente de `/var/www/dados/imoveis.db.backup-*` para `/var/www/dados/imoveis.db`
3. **Crontab:** copiar o bloco da seção anterior e rodar `crontab -e` como root
4. **Variáveis de ambiente:** restaurar `/opt/arremate-imoveis/.env` (TELEGRAM_BOT_TOKEN, TELEGRAM_CHAT_ID, GEMINI_API_KEY)
5. **Permissões:** `chown www-data:www-data /var/www/dados/imoveis.db && chmod 664 /var/www/dados/imoveis.db`

---

## Pendências conhecidas

| Item | Prioridade | Descrição |
|---|---|---|
| Swap permanente na VPS | Média | `echo '/swapfile none swap sw 0 0' >> /etc/fstab` |
| Backup automático agendado | Média | Cron semanal de backup do banco (hoje é gerado só no deploy) |

---

## Histórico de versões

| Versão | Data | Resumo |
|---|---|---|
| v1 | 2026-04-27 | Documento inicial — estado completo do sistema após scraping total e filtro de removidos |

---

## Como criar a v2 deste documento

Quando uma mudança relevante acontecer (novo cron, nova lógica de scraping, nova coluna no banco, bug importante corrigido):

1. Copie este arquivo → salve como `fluxo-atualizacao-v2.md`
2. Preencha "O que mudou nesta versão" no topo
3. Atualize as seções afetadas
4. Adicione uma linha na tabela "Histórico de versões" deste arquivo
5. Atualize o link no `docs/README.md`
6. **Não apague este arquivo (v1)**
