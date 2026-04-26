# Fluxo de Atualização de Imóveis — v1

| Campo | Valor |
|---|---|
| **Versão** | v1 |
| **Data** | 2026-04-26 |
| **Autor** | César + Claude |
| **Versão anterior** | — (documento inicial) |
| **Próxima versão** | Crie `fluxo-atualizacao-v2.md` quando algo mudar |

---

## O que mudou nesta versão
_Documento inicial — descreve o estado do sistema em 26/04/2026 após todas as correções de modalidade, encoding e implementação da priorização inteligente de scraping._

---

## Visão geral (resumo simples)

O site exibe imóveis da Caixa Econômica Federal. Esses imóveis têm dados que precisam ser mantidos atualizados em relação ao site da CAIXA. Fazemos isso em duas etapas:

1. **Import CSV** — a CAIXA disponibiliza arquivos CSV com lista de imóveis (preço, cidade, tipo). Baixamos e importamos 2x por dia.
2. **Scraping de detalhe** — visitamos a página de cada imóvel no site da CAIXA com um browser real (Puppeteer) para pegar dados extras que não vêm no CSV (datas, FGTS, edital, modalidade correta).

---

## De onde vem cada dado

| Campo | Fonte | Frequência |
|---|---|---|
| Preço, avaliação, desconto | CSV da CAIXA | 2x por dia (6h e 18h) |
| Cidade, bairro, endereço, UF | CSV da CAIXA | 2x por dia |
| Tipo do imóvel (casa, apto...) | CSV da CAIXA | 2x por dia |
| Descrição | CSV da CAIXA | 2x por dia |
| **Modalidade** (Compra Direta, Leilão...) | **Página CAIXA** via Puppeteer | A cada ciclo do scraper |
| Data do leilão / encerramento | Página CAIXA via Puppeteer | A cada ciclo do scraper |
| FGTS permitido | Página CAIXA via Puppeteer | A cada ciclo do scraper |
| Financiamento permitido | Página CAIXA via Puppeteer | A cada ciclo do scraper |
| Condomínio, IPTU (quem paga) | Página CAIXA via Puppeteer | A cada ciclo do scraper |
| Foto principal | Página CAIXA via Puppeteer | A cada ciclo do scraper |
| URL do edital | Página CAIXA via Puppeteer | A cada ciclo do scraper |
| Áreas (privativa, total, terreno) | Página CAIXA via Puppeteer | A cada ciclo do scraper |
| Status removido | Página CAIXA via Puppeteer | A cada ciclo do scraper |

> **Por que dois métodos?** O CSV é rápido e cobre todos os imóveis de uma vez, mas tem poucos campos. A página de detalhe tem muito mais informação, mas requer visitar uma página por imóvel — mais lento, mas completo.

---

## Fluxo diário hora a hora

```
05h55  cron_atualizar.sh começa
06h00  scraper_caixa.php baixa CSVs da CAIXA e importa no banco
         → ~35.000 imóveis processados
         → ~450 imóveis marcados com csv_updated_at = agora
           (novos ou com preço/modalidade alterados)
         → dados do scraper anterior preservados (datas, FGTS, etc.)

06h30  caixa-detail-scraper.js roda (cron a cada 30 min, --limit 100)
         → Prioridade 1: imóveis nunca raspados
         → Prioridade 2: imóveis com csv_updated_at > scraped_at (os ~450!)
         → Prioridade 3: imóveis raspados há mais de 7 dias (manutenção)
         → Abre Chrome real, visita página da CAIXA, extrai dados

07h00  mais 100 imóveis raspados...
07h30  mais 100...
...
~10h00 os ~450 imóveis que mudaram hoje já foram todos re-raspados ✅

18h00  segundo import CSV (mesma lógica)
18h30  scraper volta a processar os novos marcados

Resto do dia  scraper faz manutenção: raspa imóveis mais antigos
              (garantia de que nenhum fica mais de 7 dias sem revisão)
```

---

## Componentes do sistema

### 1. `cron_atualizar.sh`
**O que faz:** Script shell que roda 2x por dia (6h e 18h) na VPS. Chama o `scraper_caixa.php` para baixar e importar os CSVs da CAIXA.

**Onde fica:** `/var/www/arremate-br/cron_atualizar.sh` (VPS)

**Configurado em:** crontab do root
```
0 6  * * *  /bin/bash /var/www/arremate-br/cron_atualizar.sh
0 18 * * *  /bin/bash /var/www/arremate-br/cron_atualizar.sh
```

---

### 2. `scraper_caixa.php`
**O que faz:** Baixa os arquivos CSV da CAIXA (um por estado), importa para o banco SQLite. Ao importar, compara com o banco anterior e marca os imóveis que mudaram com `csv_updated_at = agora`. Preserva todos os dados que o scraper Puppeteer já tinha coletado (datas, fotos, etc.).

**Lógica de marcação:**
```
Imóvel marcado como "precisa re-scraping" quando:
  - É um imóvel novo (não existia ontem)
  - O preço mudou
  - A modalidade mudou
```

---

### 3. `caixa-detail-scraper.js` (Puppeteer)
**O que faz:** Abre o Chrome real (via Puppeteer/Chromium), visita a página de detalhe de cada imóvel no site da CAIXA e extrai dados extras. Usa browser real porque a CAIXA tem proteção Radware que bloqueia requisições simples (curl/PHP).

**Onde fica:** `/var/www/arremate-br/caixa-detail-scraper.js` (VPS)

**Cron:** a cada 30 minutos, processa 100 imóveis por vez
```
30 * * * *  node caixa-detail-scraper.js --limit 100
```

**Lock file:** `/tmp/arremate_detail_scraper.lock` — impede que duas instâncias rodem ao mesmo tempo.

**Prioridade de processamento (implementado em 2026-04-26):**
```
1º  Imóveis nunca raspados (scraped_at vazio)
2º  Imóveis marcados pelo CSV import (csv_updated_at > scraped_at)
3º  Imóveis mais antigos (scraped_at < 7 dias atrás)
```

**Modalidades reconhecidas:**
| Texto na página CAIXA | Gravado no banco como |
|---|---|
| "Compra Direta" | Compra Direta |
| "Venda Direta Online" | Compra Direta (mesma coisa, nome diferente) |
| "Venda Online" | Venda Online |
| "Licitação Aberta" | Licitação Aberta |
| "1º Leilão", "2º Leilão", "Leilão Único"... | Leilão SFI - Edital Único |

**Sessão renovada:** a cada 50 imóveis o browser reinicia para evitar acúmulo de memória.

---

### 4. `monitor-mudancas.js`
**O que faz:** Compara o banco de hoje com o snapshot de ontem e registra quantos imóveis entraram, saíram ou mudaram de status. Gera log diário. Não altera dados — só monitora.

**Cron:** 2x por dia, logo após o import CSV
```
5 6  * * *  node monitor-mudancas.js
5 18 * * *  node monitor-mudancas.js
```

**Log:** `/var/log/arremate_mudancas.log`

---

### 5. `status-diario-ti.sh`
**O que faz:** Toda manhã às 8h coleta métricas da VPS (disco, memória, n8n, SSL, banco) e envia uma mensagem via Telegram usando Gemini AI com a persona do "Agente TI".

**Cron:**
```
0 8 * * *  /var/www/arremate-br/status-diario-ti.sh
```

---

## Banco de dados

**Localização:**
- Produção (VPS): `/var/www/dados/imoveis.db`
- Local (desenvolvimento): `C:/xampp/htdocs/dados/imoveis.db`

**Tamanho atual:** ~21MB | **Total de imóveis:** 35.079

**Backups disponíveis na VPS:**
```
/var/www/dados/imoveis.db.backup-20260419-0041   (19/04)
/var/www/dados/imoveis.db.backup-20260426-0846   (26/04) ← mais recente
```

**Colunas importantes da tabela `imoveis`:**

| Coluna | Fonte | Descrição |
|---|---|---|
| `hdnimovel` | CSV | ID único do imóvel na CAIXA |
| `preco` | CSV | Preço de venda em centavos |
| `modalidade` | Puppeteer | Tipo de venda (Compra Direta, Leilão...) |
| `scraped_at` | Puppeteer | Última vez que o Puppeteer visitou a página |
| `csv_updated_at` | CSV import | Última vez que o CSV marcou este imóvel como alterado |
| `data_leilao_1` | Puppeteer | Data do 1º leilão |
| `data_encerramento` | Puppeteer | Data de encerramento |
| `status_caixa` | Puppeteer | "removido" se a CAIXA retirou o imóvel |
| `fgts` | Puppeteer | 1 = permite uso de FGTS |
| `financiamento` | Puppeteer | 1 = permite financiamento |
| `edital_url` | Puppeteer | URL do PDF do edital |

---

## Distribuição de modalidades (estado em 2026-04-26)

| Modalidade | Quantidade |
|---|---|
| Compra Direta | 15.137 |
| Leilão SFI - Edital Único | 8.578 |
| Venda Online | 7.622 |
| Licitação Aberta | 3.741 |

---

## Por que Puppeteer e não curl/PHP?

A CAIXA usa o sistema **Radware Bot Manager** que detecta e bloqueia requisições automatizadas simples (curl, file_get_contents do PHP). O Puppeteer abre um Chrome real que resolve os desafios JavaScript do Radware e acessa normalmente. O bloqueio por curl foi identificado em abril/2026.

---

## Proteções e segurança do scraper

| Proteção | Como funciona |
|---|---|
| Lock file | Impede duas instâncias simultâneas |
| Sessão renovada | Browser reinicia a cada 50 imóveis (evita travamento) |
| Limite por execução | --limit 100 por cron (não sobrecarrega a CAIXA) |
| Detecção de bloqueio | Se página retornar HTML < 10KB = está bloqueado, pula |
| Fallback de banco | Se better-sqlite3 falhar, usa sqlite3 CLI |

---

## Pendências conhecidas

| Item | Prioridade | Descrição |
|---|---|---|
| Swap permanente na VPS | Média | Rodar: `echo '/swapfile none swap sw 0 0' >> /etc/fstab` |
| Backup automático do banco | Alta | Hoje é manual. Criar cron semanal de backup |
| Índice em csv_updated_at | Baixa | Melhorar performance da query de priorização |

---

## Como criar a v2 deste documento

Quando uma mudança relevante acontecer (ex: novo cron, mudança na lógica do scraper, nova coluna no banco):

1. Copie este arquivo e salve como `fluxo-atualizacao-v2.md`
2. Preencha o bloco "O que mudou nesta versão" no topo
3. Atualize as seções afetadas
4. Atualize o link no `docs/README.md`
5. **Não apague este arquivo (v1)**
