#!/usr/bin/env php
<?php
/**
 * fgts-batch-scraper.php — Enriquecimento proativo de FGTS/Financiamento em lote
 *
 * Processa imóveis onde scraped_at IS NULL (nunca visitados) em batches.
 * Extrai FGTS e Financiamento da página da Caixa e atualiza o banco.
 *
 * Uso:
 *   php fgts-batch-scraper.php              # lote padrão de 100 imóveis
 *   php fgts-batch-scraper.php --batch=50   # lote de 50
 *   php fgts-batch-scraper.php --uf=SP      # filtra por estado
 *   php fgts-batch-scraper.php --dry-run    # mostra sem atualizar banco
 */

set_time_limit(0);
ini_set('memory_limit', '256M');
date_default_timezone_set('America/Sao_Paulo');

define('DB_PATH',  '/var/www/dados/imoveis.db');
define('LOG_FILE', '/var/log/arremate_fgts_batch.log');
define('DELAY_MS', 3200000); // 3.2s entre requests (respeita limite anti-bot da Caixa)

// Parse de argumentos
$args    = array_slice($argv, 1);
$dryRun  = in_array('--dry-run', $args);
$batch   = 100;
$ufAlvo  = null;
foreach ($args as $arg) {
    if (preg_match('/^--batch=(\d+)$/', $arg, $m)) $batch  = (int) $m[1];
    if (preg_match('/^--uf=([A-Z]{2})$/i', $arg, $m)) $ufAlvo = strtoupper($m[1]);
}
$batch = max(1, min($batch, 500)); // limita entre 1 e 500

// User-Agents reais para rotação anti-bloqueio
$userAgents = [
    'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/124.0.0.0 Safari/537.36',
    'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/123.0.0.0 Safari/537.36 Edg/123.0.0.0',
    'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/124.0.0.0 Safari/537.36',
    'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:125.0) Gecko/20100101 Firefox/125.0',
    'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/122.0.0.0 Safari/537.36',
    'Mozilla/5.0 (Macintosh; Intel Mac OS X 14_4_1) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/17.4.1 Safari/605.1.15',
];

function logMsg(string $m): void {
    $l = '[' . date('Y-m-d H:i:s') . '] ' . $m . "\n";
    echo $l;
    @file_put_contents(LOG_FILE, $l, FILE_APPEND);
}

function randomUA(array $uas): string {
    return $uas[array_rand($uas)];
}

/**
 * Faz o scraping da página de detalhe da Caixa e retorna array com fgts e financiamento.
 * Retorna null se bloqueado ou erro.
 */
function scrapeDetalhe(string $hdn, array $userAgents): ?array {
    $url = 'https://venda-imoveis.caixa.gov.br/sistema/detalhe-imovel.asp?hdnimovel=' . $hdn;

    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_MAXREDIRS      => 4,
        CURLOPT_TIMEOUT        => 15,
        CURLOPT_USERAGENT      => randomUA($userAgents),
        CURLOPT_SSL_VERIFYPEER => false,
        CURLOPT_SSL_VERIFYHOST => 0,
        CURLOPT_ENCODING       => '',
        CURLOPT_HTTPHEADER     => [
            'Accept: text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,*/*;q=0.8',
            'Accept-Language: pt-BR,pt;q=0.9,en-US;q=0.8',
            'Referer: https://venda-imoveis.caixa.gov.br/sistema/busca-imovel.asp',
            'Connection: keep-alive',
        ],
    ]);
    $html     = curl_exec($ch);
    $httpCode = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curlErr  = curl_errno($ch);
    curl_close($ch);

    // Erros de rede
    if ($curlErr || !$html) return null;

    // HTTP 429 (rate limit) ou 403 (bloqueado)
    if ($httpCode === 429 || $httpCode === 403) {
        logMsg("  ⚠ HTTP {$httpCode} para {$hdn} — aguardando 15s antes de continuar");
        sleep(15);
        return null;
    }

    if ($httpCode !== 200 || strlen($html) < 1000) return null;

    // Detecta bloqueio Radware / captcha
    $htmlLow = strtolower(substr($html, 0, 3000));
    if (strpos($htmlLow, 'bot manager') !== false
        || strpos($htmlLow, 'captcha')   !== false
        || strpos($htmlLow, 'radware')   !== false) {
        logMsg("  ⚠ Bloqueio detectado (Radware/captcha) — interrompendo lote");
        return null; // sinaliza para parar o lote
    }

    // ── Extração independente de FGTS e Financiamento ──
    // FGTS: SOMENTE a frase "Permite utilização de FGTS"
    // Financiamento: "Permite financiamento" em qualquer variante
    // NUNCA vincular um ao outro.
    $fgts          = 0;
    $financiamento = 0;

    if (stripos($html, 'Permite utiliza') !== false && stripos($html, 'FGTS') !== false) {
        $fgts = 1;
    }

    if (stripos($html, 'Permite financiamento') !== false) {
        $financiamento = 1;
    }

    // Condomínio — CAIXA paga excedente de 10%
    $htmlDec   = html_entity_decode($html, ENT_QUOTES | ENT_HTML5, 'UTF-8');
    $htmlClean = preg_replace('/\s+/', ' ', strip_tags($htmlDec));
    $caixa_paga_excedente = 0;
    if (stripos($htmlClean, 'Sob responsabilidade do comprador') !== false
        && stripos($htmlClean, 'limite de 10%') !== false
        && stripos($htmlClean, 'CAIXA realizar') !== false
        && stripos($htmlClean, 'exceder') !== false) {
        $caixa_paga_excedente = 1;
    }

    // Datas (1º Leilão + 2º Leilão / única)
    $datas = extrairDatasLeilaoBatch($htmlDec);
    $data_leilao_1     = $datas['data_leilao_1'];
    $data_encerramento = $datas['data_encerramento'];

    // Foto
    $foto_url = '';
    if (preg_match('/src=[\'"][^"\']*?(\/fotos\/F[^"\']+\.jpg)[\'"]/', $html, $m)) {
        $foto_url = 'https://venda-imoveis.caixa.gov.br' . $m[1];
    }

    return compact('fgts', 'financiamento', 'caixa_paga_excedente', 'data_leilao_1', 'data_encerramento', 'foto_url');
}

/** Valida uma data montada de partes e retorna YYYY-MM-DD HH:MM:SS ou '' se inválida. */
function validarDataBatch(string $Y, string $M, string $D, string $h, string $min): string {
    $iso = sprintf('%04d-%02d-%02d %02d:%02d:00', (int)$Y, (int)$M, (int)$D, (int)$h, (int)$min);
    $dt  = DateTime::createFromFormat('Y-m-d H:i:s', $iso, new DateTimeZone('America/Sao_Paulo'));
    if (!$dt || $dt->format('Y-m-d H:i:s') !== $iso) return '';
    return $iso;
}

/** Captura datas de 1º e 2º leilão (SFI) ou única (Licitação/Venda Online). */
function extrairDatasLeilaoBatch(string $htmlDec): array {
    $d1 = '';
    $d2 = '';

    if (preg_match('/1[ºo°]?\s*Leil[aã]o[^\d]{0,60}(\d{2})\/(\d{2})\/(\d{4})\s+[àa]s\s+(\d{2}):(\d{2})/iu', $htmlDec, $m)) {
        $d1 = validarDataBatch($m[3], $m[2], $m[1], $m[4], $m[5]);
    }
    if (preg_match('/2[ºo°]?\s*Leil[aã]o[^\d]{0,60}(\d{2})\/(\d{2})\/(\d{4})\s+[àa]s\s+(\d{2}):(\d{2})/iu', $htmlDec, $m)) {
        $d2 = validarDataBatch($m[3], $m[2], $m[1], $m[4], $m[5]);
    }

    if (!$d1 && !$d2) {
        preg_match_all('/(\d{2})\/(\d{2})\/(\d{4})\s+[àa]s\s+(\d{2}):(\d{2})/iu', $htmlDec, $all, PREG_SET_ORDER);
        $datas = [];
        foreach ($all as $mm) {
            $v = validarDataBatch($mm[3], $mm[2], $mm[1], $mm[4], $mm[5]);
            if ($v) $datas[] = $v;
        }
        $datas = array_values(array_unique($datas));
        sort($datas);
        if (count($datas) >= 2) { $d1 = $datas[0]; $d2 = end($datas); }
        elseif (count($datas) === 1) { $d2 = $datas[0]; }
    }

    if (!$d1 && !$d2 && preg_match('/(?:Encerramento|Prazo|Data)[^\d]*(\d{2})\/(\d{2})\/(20\d{2})/i', $htmlDec, $m)) {
        $d2 = validarDataBatch($m[3], $m[2], $m[1], '23', '59');
    }

    if ($d1 && !$d2) { $d2 = $d1; $d1 = ''; }
    return ['data_leilao_1' => $d1, 'data_encerramento' => $d2];
}

/* ── Conexão com banco ── */
try {
    $db = new PDO('sqlite:' . DB_PATH);
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $db->exec('PRAGMA journal_mode=WAL; PRAGMA synchronous=NORMAL;');
} catch (Exception $e) {
    logMsg("ERRO ao abrir banco: " . $e->getMessage());
    exit(1);
}

/* ── Busca imóveis para processar ── */
// Prioridade:
// 1. Nunca scraped (scraped_at IS NULL)
// 2. Erros anteriores (scraped_at LIKE 'ERR%')
// Ordena por: imóveis com financiamento=1 primeiro (mais propensos a ter FGTS)
$ufWhere = $ufAlvo ? "AND uf = :uf" : '';
$sql = "SELECT hdnimovel, uf, cidade, tipo FROM imoveis
        WHERE (scraped_at IS NULL OR scraped_at = '' OR scraped_at LIKE 'ERR%')
        AND hdnimovel IS NOT NULL AND hdnimovel != ''
        {$ufWhere}
        ORDER BY financiamento DESC, id DESC
        LIMIT :lim";

$stmt = $db->prepare($sql);
$stmt->bindValue(':lim', $batch, PDO::PARAM_INT);
if ($ufAlvo) $stmt->bindValue(':uf', $ufAlvo, PDO::PARAM_STR);
$stmt->execute();
$imoveis = $stmt->fetchAll(PDO::FETCH_ASSOC);

$total   = count($imoveis);
$ok      = 0;
$erros   = 0;
$bloq    = 0;

logMsg("=== FGTS BATCH SCRAPER ===");
logMsg("Lote: {$total} imóveis" . ($ufAlvo ? " | UF: {$ufAlvo}" : '') . ($dryRun ? ' | DRY-RUN' : ''));

if ($total === 0) {
    logMsg("Nenhum imóvel pendente. Banco já está enriquecido.");
    exit(0);
}

/* ── Prepared statements ── */
$upd = $db->prepare(
    "UPDATE imoveis SET
        fgts = :fg,
        financiamento = :fi,
        caixa_paga_excedente = :cpe,
        data_leilao_1 = CASE WHEN :d1 != '' THEN :d1b ELSE data_leilao_1 END,
        data_encerramento = CASE WHEN :de != '' THEN :de2 ELSE data_encerramento END,
        foto_url = CASE WHEN :fo != '' THEN :fo2 ELSE foto_url END,
        scraped_at = :ts
     WHERE hdnimovel = :h"
);

$updErr = $db->prepare(
    "UPDATE imoveis SET scraped_at = :ts WHERE hdnimovel = :h"
);

/* ── Loop principal ── */
foreach ($imoveis as $i => $im) {
    $hdn  = $im['hdnimovel'];
    $info = "[{$hdn}] {$im['tipo']} {$im['cidade']}/{$im['uf']}";

    if ($dryRun) {
        logMsg("  DRY-RUN {$info}");
        $ok++;
        continue;
    }

    $resultado = scrapeDetalhe($hdn, $userAgents);

    if ($resultado === null) {
        // Pode ser bloqueio — se for, para o lote
        $bloq++;
        $updErr->execute([':ts' => 'ERR:' . date('Y-m-d H:i:s'), ':h' => $hdn]);
        logMsg("  ✗ ERRO/BLOQUEIO {$info}");

        // Se mais de 3 erros seguidos, provavelmente IP bloqueado — para
        if ($bloq >= 3) {
            logMsg("  ⛔ 3 erros consecutivos — interrompendo lote para não desperdiçar requests");
            break;
        }
        usleep(DELAY_MS);
        continue;
    }

    // Reset contador de erros se teve sucesso
    $bloq = 0;

    $upd->execute([
        ':fg'  => $resultado['fgts'],
        ':fi'  => $resultado['financiamento'],
        ':cpe' => $resultado['caixa_paga_excedente'],
        ':d1'  => $resultado['data_leilao_1'],
        ':d1b' => $resultado['data_leilao_1'],
        ':de'  => $resultado['data_encerramento'],
        ':de2' => $resultado['data_encerramento'],
        ':fo'  => $resultado['foto_url'],
        ':fo2' => $resultado['foto_url'],
        ':ts'  => date('Y-m-d H:i:s'),
        ':h'   => $hdn,
    ]);

    $ok++;
    $fgtsLabel = $resultado['fgts'] ? '✓FGTS' : '—';
    $finLabel  = $resultado['financiamento'] ? '✓Fin' : '—';
    logMsg("  ✓ [{$i}/{$total}] {$info} | {$fgtsLabel} {$finLabel}");

    usleep(DELAY_MS);
}

logMsg("=== CONCLUÍDO === OK:{$ok} | Erros/Bloq:{$erros} | Total:{$total}");

// Estatísticas finais
$stats = $db->query("SELECT SUM(fgts) fg, SUM(financiamento) fi, COUNT(*) t,
                     SUM(CASE WHEN scraped_at IS NULL OR scraped_at='' THEN 1 ELSE 0 END) pendentes
                     FROM imoveis")->fetch(PDO::FETCH_ASSOC);
logMsg("Banco: {$stats['t']} imóveis | FGTS:{$stats['fg']} | Fin:{$stats['fi']} | Pendentes:{$stats['pendentes']}");
