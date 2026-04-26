# Fluxo de Atualização de Imóveis — v1

| Campo | Valor |
|---|---|
| **Versão** | v1 |
| **Data** | 2026-04-26 |
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
| Preço, avaliação, desconto | CSV da CAIXA | 2x por dia (6h e 18h) |
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
         → Prioridade 2: imóveis com csv_updated_at > scraped_at (~450 marcados)
         → Prioridade 3: imóveis raspados há mais de 7 dias (manutenção)
         → Abre Chrome real, visita página da CAIXA, extrai dados

07h00  mais 100 imóveis raspados...
...
~10h00 os ~450 imóveis que mudaram hoje já foram todos re-raspados ✅

18h00  segundo import CSV (mesma lógica)
18h30  scraper processa o novo lote marcado

Resto do dia  scraper faz manutenção: raspa imóveis mais antigos
              (nenhum fica mais de 7 dias sem revisão)
```

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
Imóvel marcado como "precisa re-scraping" quando:
  → É um imóvel novo (não existia ontem)
  → O preço mudou
  → A modalidade mudou
```

---

### 3. `caixa-detail-scraper.js` (Puppeteer)
**O que faz:** Abre o Chrome real via Puppeteer, visita a página de detalhe de cada imóvel no site da CAIXA e extrai dados extras. Usa browser real porque a CAIXA tem proteção Radware que bloqueia requisições simples (curl/PHP).

**Onde fica:** `/var/www/arremate-br/caixa-detail-scraper.js`

**Crontab:**
```
30 * * * *  node caixa-detail-scraper.js --limit 100
```
Roda a cada 30 minutos, processa 100 imóveis por vez = 4.800/dia.

**Lock file:** `/tmp/arremate_detail_scraper.lock` — impede duas instâncias simultâneas.

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
| "Venda Direta Online" | Compra Direta (mesma coisa, nome diferente no CSV) |
| "Venda Online" | Venda Online |
| "Licitação Aberta" | Licitação Aberta |
| "1º Leilão", "2º Leilão", "Leilão Único"... | Leilão SFI - Edital Único |

**Extração de data para Venda Online:**
A data de encerramento dos imóveis Venda Online não aparece no texto visível da página — fica dentro de um trecho JavaScript (`carregaContador`). O scraper busca o padrão `"DD/MM/YYYY HH:MM:SS"` diretamente no HTML bruto quando não encontra data pelo texto visível.

**Sessão renovada:** a cada 50 imóveis o browser reinicia para evitar acúmulo de memória.

---

### 4. `monitor-mudancas.js`
**O que faz:** Compara o banco de hoje com o snapshot de ontem e registra quantos imóveis entraram, saíram ou mudaram de status. Apenas monitora — não altera dados.

**Crontab:**
```
5 6  * * *  node monitor-mudancas.js
5 18 * * *  node monitor-mudancas.js
```

**Log:** `/var/log/arremate_mudancas.log`

---

### 5. `status-diario-ti.sh`
**O que faz:** Toda manhã às 8h coleta métricas da VPS (disco, memória, n8n, SSL, banco) e envia mensagem via Telegram usando Gemini AI com a persona do "Agente TI". Se o Gemini falhar, envia um resumo de fallback direto.

**Crontab:**
```
0 8 * * *  /var/www/arremate-br/status-diario-ti.sh
```

**Log:** `/var/log/arremate_status_diario.log`

---

## Banco de dados

**Localização:**
- Produção (VPS): `/var/www/dados/imoveis.db`
- Local (desenvolvimento): `C:/xampp/htdocs/dados/imoveis.db`

**Tamanho atual:** ~21MB | **Total de imóveis:** 35.079

**Backups na VPS:**
```
/var/www/dados/imoveis.db.backup-20260419-0041
/var/www/dados/imoveis.db.backup-20260426-0846  ← mais recente
```

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

A CAIXA usa o **Radware Bot Manager** que detecta e bloqueia requisições automatizadas simples (curl, PHP). O Puppeteer abre um Chrome real que resolve os desafios JavaScript do Radware normalmente. Bloqueio identificado em abril/2026.

---

## Proteções do scraper

| Proteção | Como funciona |
|---|---|
| Lock file | Impede duas instâncias simultâneas |
| Sessão renovada | Browser reinicia a cada 50 imóveis |
| Limite por execução | --limit 100 por cron (ritmo seguro) |
| Detecção de bloqueio | HTML < 10KB = Radware bloqueando, pula o imóvel |
| Fallback de banco | Se better-sqlite3 falhar, usa sqlite3 CLI (Linux) |

---

## Crontab completo da VPS (root)

```
*/5 * * * *   bash /opt/arremate-imoveis/scripts/monitor-vps.sh
5 6    * * *  cd /var/www/arremate-br && node monitor-mudancas.js
5 18   * * *  cd /var/www/arremate-br && node monitor-mudancas.js
30 * * * *    cd /var/www/arremate-br && node caixa-detail-scraper.js --limit 100
0 6    * * *  /bin/bash /var/www/arremate-br/cron_atualizar.sh
0 18   * * *  /bin/bash /var/www/arremate-br/cron_atualizar.sh
0 8    * * *  /bin/bash /var/www/arremate-br/status-diario-ti.sh
```

---

## Pendências conhecidas

| Item | Prioridade | Descrição |
|---|---|---|
| Swap permanente na VPS | Média | `echo '/swapfile none swap sw 0 0' >> /etc/fstab` |
| Backup automático do banco | Alta | Hoje é manual. Criar cron semanal de backup |

---

## Como criar a v2 deste documento

Quando uma mudança relevante acontecer (novo cron, nova lógica de scraping, nova coluna no banco, bug importante corrigido):

1. Copie este arquivo → salve como `fluxo-atualizacao-v2.md`
2. Preencha "O que mudou nesta versão" no topo
3. Atualize as seções afetadas
4. Atualize o link no `docs/README.md`
5. **Não apague este arquivo (v1)**
