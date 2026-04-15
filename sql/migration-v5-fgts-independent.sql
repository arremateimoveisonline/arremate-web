-- ═══════════════════════════════════════════════════════════════════════════
-- migration-v5-fgts-independent.sql
-- ═══════════════════════════════════════════════════════════════════════════
-- Aplica as regras v5 ao banco de produção SEM recriar a tabela.
--
-- USO (VPS):
--   sqlite3 /var/www/dados/imoveis.db < /var/www/arremate-br/sql/migration-v5-fgts-independent.sql
--
-- Mudanças:
--   1. Adiciona colunas caixa_paga_excedente e data_leilao_1
--   2. Reseta FGTS derivado do CSV (scraped_at NULL) — será re-scraped em lote
--   3. Reseta FGTS de registros que podem ter sido contaminados pela lógica
--      antiga "somente SBPE → fgts=1" (força re-scrape desses)
-- ═══════════════════════════════════════════════════════════════════════════

BEGIN TRANSACTION;

-- ── 1. Adiciona colunas novas (ignora se já existirem) ─────────────────────
-- SQLite não suporta "ADD COLUMN IF NOT EXISTS", então usar via shell:
--   sqlite3 imoveis.db "ALTER TABLE imoveis ADD COLUMN caixa_paga_excedente INTEGER DEFAULT 0;" 2>/dev/null || true
--   sqlite3 imoveis.db "ALTER TABLE imoveis ADD COLUMN data_leilao_1 TEXT DEFAULT '';" 2>/dev/null || true
ALTER TABLE imoveis ADD COLUMN caixa_paga_excedente INTEGER DEFAULT 0;
ALTER TABLE imoveis ADD COLUMN data_leilao_1 TEXT DEFAULT '';

-- ── 2. Índice para ordenação cronológica rápida ────────────────────────────
CREATE INDEX IF NOT EXISTS idx_data_encerramento ON imoveis(data_encerramento);
CREATE INDEX IF NOT EXISTS idx_caixa_paga_excedente ON imoveis(caixa_paga_excedente);

-- ── 3. Reseta FGTS que foi derivado do CSV (nunca scraped) ─────────────────
-- Esses valores vieram da fórmula antiga "financiamento && tipo != terreno".
-- Marca como NULL para que o batch scraper re-processe e use a frase exata.
UPDATE imoveis
   SET fgts = 0
 WHERE (scraped_at IS NULL OR scraped_at = '' OR scraped_at LIKE 'ERR%');

-- ── 4. Força re-scrape de registros que foram processados pelo scraper antigo
-- A lógica antiga vinculava FGTS ao financiamento via "somente SBPE".
-- Limpa scraped_at para esses registros serem re-visitados em lote.
UPDATE imoveis
   SET scraped_at = NULL
 WHERE scraped_at IS NOT NULL
   AND scraped_at NOT LIKE 'ERR%'
   AND financiamento = 1
   AND fgts = 1;

-- ── 5. Normaliza datas antigas (YYYY-MM-DD HH:MM → YYYY-MM-DD HH:MM:SS) ───
UPDATE imoveis
   SET data_encerramento = data_encerramento || ':00'
 WHERE data_encerramento LIKE '____-__-__ __:__'
   AND LENGTH(data_encerramento) = 16;

-- ── 6. Remove datas malformadas para serem re-capturadas ──────────────────
UPDATE imoveis
   SET data_encerramento = ''
 WHERE data_encerramento IS NOT NULL
   AND data_encerramento != ''
   AND data_encerramento NOT LIKE '____-__-__%';

COMMIT;

-- ── Verificação ────────────────────────────────────────────────────────────
SELECT
    COUNT(*) AS total,
    SUM(CASE WHEN scraped_at IS NULL OR scraped_at = '' THEN 1 ELSE 0 END) AS pendentes_rescrape,
    SUM(fgts) AS total_fgts,
    SUM(financiamento) AS total_fin,
    SUM(caixa_paga_excedente) AS total_caixa_paga_exc,
    SUM(CASE WHEN data_encerramento LIKE '____-__-__ __:__:__' THEN 1 ELSE 0 END) AS datas_ok
FROM imoveis;
