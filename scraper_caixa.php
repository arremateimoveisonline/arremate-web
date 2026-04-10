#!/usr/bin/env php
<?php
/**
 * scraper_caixa.php v4 — Arremate Imóveis Online
 *
 * PROTEÇÃO ANTI-RADWARE:
 *   - Valida cada CSV antes de aceitar (detecta páginas HTML de captcha)
 *   - NUNCA apaga o banco se downloads falharem
 *   - Só atualiza o banco se pelo menos 15 estados baixaram CSVs válidos
 *   - Em caso de falha, mantém banco existente e registra alerta
 *
 * Uso:
 *   php scraper_caixa.php              # todos os estados
 *   php scraper_caixa.php SP RJ        # estados específicos
 *   php scraper_caixa.php --csv-only   # sem scraping de detalhes
 */

set_time_limit(0);
ini_set('memory_limit','512M');
date_default_timezone_set('America/Sao_Paulo');

define('DB_PATH',    '/var/www/dados/imoveis.db');
define('CSV_DIR',    '/var/www/dados/csv');
define('CSV_TMP',    '/var/www/dados/csv_tmp');  // temporário — só salva se válido
define('LOG_FILE',   '/var/log/arremate_scraper.log');
define('MIN_VALID_UFS', 15);  // mínimo de estados válidos para atualizar banco

$UFS = ['AC','AL','AM','AP','BA','CE','DF','ES','GO','MA','MG','MS','MT',
        'PA','PB','PE','PI','PR','RJ','RN','RO','RR','RS','SC','SE','SP','TO'];

$args    = array_slice($argv, 1);
$csvOnly    = in_array('--csv-only', $args);
$importOnly = in_array('--import-only', $args);
$args    = array_values(array_filter($args, fn($a) => !\in_array($a, ['--csv-only','--import-only'])));
$ufsAlvo = $args ? array_map('strtoupper', $args) : $UFS;

function logMsg($m) {
    $l = '[' . date('Y-m-d H:i:s') . '] ' . $m . "\n";
    echo $l;
    @file_put_contents(LOG_FILE, $l, FILE_APPEND);
}

function curlGet($url, $timeout = 45) {
    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => 1,
        CURLOPT_FOLLOWLOCATION => 1,
        CURLOPT_MAXREDIRS      => 5,
        CURLOPT_TIMEOUT        => $timeout,
        CURLOPT_USERAGENT      => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/124.0.0.0 Safari/537.36',
        CURLOPT_SSL_VERIFYPEER => false,
        CURLOPT_SSL_VERIFYHOST => 0,
        CURLOPT_ENCODING       => '',
        CURLOPT_HTTPHEADER     => [
            'Accept: text/csv,text/plain,*/*',
            'Accept-Language: pt-BR,pt;q=0.9',
            'Cache-Control: no-cache',
        ],
    ]);
    $body = curl_exec($ch);
    $code = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    return ['body' => $body, 'code' => $code];
}

/**
 * Valida se o conteúdo baixado é um CSV real (não página HTML de captcha)
 */
function isCsvValido($body) {
    if (strlen($body) < 200) return false;
    // Detecta páginas HTML (Radware, captcha, erro)
    $inicio = strtolower(substr(ltrim($body), 0, 200));
    if (strpos($inicio, '<html')  !== false) return false;
    if (strpos($inicio, '<head')  !== false) return false;
    if (strpos($inicio, '<!doc')  !== false) return false;
    if (strpos($inicio, 'radware') !== false) return false;
    if (strpos($inicio, 'captcha') !== false) return false;
    // Deve ter pelo menos uma linha com separador ";"
    if (substr_count($body, ';') < 5) return false;
    // Deve ter ao menos 3 linhas
    if (substr_count($body, "\n") < 3) return false;
    return true;
}

function parseCentavos($s) {
    $s = trim($s);
    if ($s === '' || $s === '0') return 0;
    $s = str_replace('.', '', $s);
    $s = str_replace(',', '.', $s);
    return (int) round((float) $s * 100);
}

function inferTipo($d) {
    // Normaliza: minúsculo e remove acentos para comparação robusta
    $d = mb_strtolower($d, 'UTF-8');
    $d = strtr($d, [
        'á'=>'a','à'=>'a','â'=>'a','ã'=>'a','ä'=>'a',
        'é'=>'e','ê'=>'e','ë'=>'e','è'=>'e',
        'í'=>'i','î'=>'i','ï'=>'i','ì'=>'i',
        'ó'=>'o','ô'=>'o','õ'=>'o','ö'=>'o','ò'=>'o',
        'ú'=>'u','û'=>'u','ü'=>'u','ù'=>'u',
        'ç'=>'c','ñ'=>'n',
    ]);
    // Ordem importa: verificar 'predio' antes de 'comercial' pois prédio comercial existe
    foreach (['apartamento','casa','terreno','gleba','loja','sala','lote','predio','comercial'] as $t)
        if (strpos($d, $t) !== false) return $t;
    return 'imovel';
}

function normMod($m) {
    $l = mb_strtolower(trim($m));
    if (strpos($l, 'venda direta') !== false || strpos($l, 'compra direta') !== false) return 'Venda Direta Online';
    if (strpos($l, 'leilão sfi')   !== false || strpos($l, 'leilao sfi')   !== false) return 'Leilão SFI';
    if (strpos($l, 'licitação')    !== false || strpos($l, 'licitacao')    !== false) return 'Licitação Aberta';
    if (strpos($l, 'venda online') !== false) return 'Venda Online';
    return trim($m);
}

function inferDisp($m) {
    $l = mb_strtolower($m);
    return (strpos($l, 'venda online') !== false && strpos($l, 'direta') === false) ? 1 : 0;
}

function hdnFrom($link) {
    if (preg_match('/hdnimovel=(\d+)/i', $link, $m)) return $m[1];
    return '';
}

/* ══════════════════════════════════════════════
   FASE 1 — Download CSVs para pasta temporária
   ══════════════════════════════════════════════ */
logMsg("=== PIPELINE v4 ===");
logMsg("Estados: " . implode(',', $ufsAlvo) . ($csvOnly ? ' [CSV-ONLY]' : '') . ($importOnly ? ' [IMPORT-ONLY]' : ''));
logMsg("Banco atual: " . (file_exists(DB_PATH) ? round(filesize(DB_PATH)/1024/1024, 1) . 'MB' : 'inexistente'));

if (!is_dir(CSV_DIR)) mkdir(CSV_DIR, 0775, true);
if (!is_dir(CSV_TMP)) mkdir(CSV_TMP, 0775, true);

$csvValidos  = [];
$csvBloq     = 0;
$csvFalhou   = 0;

if ($importOnly) {
    // Modo import-only: usa CSVs já presentes em CSV_DIR (enviados pelo GitHub Actions)
    foreach ($ufsAlvo as $uf) {
        $csvPath = CSV_DIR . "/Lista_imoveis_{$uf}.csv";
        if (file_exists($csvPath) && filesize($csvPath) > 500) {
            $csvValidos[$uf] = $csvPath;
        } else {
            logMsg("  CSV {$uf}: ausente em CSV_DIR — ignorado");
            $csvFalhou++;
        }
    }
    $totalValidos = count($csvValidos);
    logMsg("Import-only: {$totalValidos} CSVs disponíveis.");
} else {
foreach ($ufsAlvo as $uf) {
    logMsg("CSV {$uf}...");
    $r = curlGet("https://venda-imoveis.caixa.gov.br/listaweb/Lista_imoveis_{$uf}.csv");

    if ($r['code'] >= 200 && $r['code'] < 400 && isCsvValido($r['body'])) {
        $tmpDest = CSV_TMP . "/Lista_imoveis_{$uf}.csv";
        file_put_contents($tmpDest, $r['body']);
        $csvValidos[$uf] = $tmpDest;
        logMsg("  ✓ OK: " . substr_count($r['body'], "\n") . " linhas | " . round(strlen($r['body'])/1024, 1) . "KB");
    } elseif (strpos(strtolower($r['body'] ?? ''), 'radware') !== false ||
              strpos(strtolower($r['body'] ?? ''), 'captcha') !== false ||
              strpos(strtolower($r['body'] ?? ''), '<html')   !== false) {
        logMsg("  ✗ BLOQUEADO por Radware/captcha");
        $csvBloq++;
    } else {
        logMsg("  ✗ FALHA: HTTP={$r['code']} len=" . strlen($r['body'] ?? ''));
        $csvFalhou++;
    }
    usleep(600000); // 0.6s entre requests
}

$totalValidos = count($csvValidos);
} // fim else (não import-only)

logMsg("CSVs válidos: {$totalValidos} | Bloqueados: {$csvBloq} | Falhou: {$csvFalhou}");

/* ══════════════════════════════════════════════
   VERIFICAÇÃO DE SEGURANÇA
   Só atualiza o banco se tiver CSVs suficientes
   ══════════════════════════════════════════════ */
$minRequerido = $importOnly ? 1 : ((count($ufsAlvo) === count($UFS)) ? MIN_VALID_UFS : max(1, (int)(count($ufsAlvo) * 0.7)));

if ($totalValidos < $minRequerido) {
    logMsg("⚠️  ATENÇÃO: apenas {$totalValidos} CSVs válidos (mínimo: {$minRequerido}).");
    logMsg("⚠️  Banco NÃO atualizado — mantendo dados existentes para não perder imóveis.");
    // Copia CSVs válidos para pasta definitiva mesmo assim (para uso futuro)
    foreach ($csvValidos as $uf => $tmp) {
        copy($tmp, CSV_DIR . "/Lista_imoveis_{$uf}.csv");
    }
    // Limpa temporários
    array_map('unlink', glob(CSV_TMP . '/*.csv'));
    exit(2); // exit code 2 = blocked, banco preservado
}

/* ══════════════════════════════════════════════
   FASE 2 — Atualiza banco com CSVs válidos
   Para estados sem CSV novo, reutiliza CSV antigo
   ══════════════════════════════════════════════ */
logMsg("✅ Downloads suficientes — atualizando banco...");

// Completa com CSVs antigos para estados não baixados
foreach ($ufsAlvo as $uf) {
    if (!isset($csvValidos[$uf])) {
        $antigo = CSV_DIR . "/Lista_imoveis_{$uf}.csv";
        if (file_exists($antigo) && filesize($antigo) > 1000) {
            $csvValidos[$uf] = $antigo;
            logMsg("  Reutilizando CSV antigo: {$uf}");
        }
    }
}

// Move CSVs válidos para pasta definitiva
foreach ($csvValidos as $uf => $tmp) {
    if (strpos($tmp, CSV_TMP) !== false) {
        rename($tmp, CSV_DIR . "/Lista_imoveis_{$uf}.csv");
        $csvValidos[$uf] = CSV_DIR . "/Lista_imoveis_{$uf}.csv";
    }
}

// Backup antes de recriar
$bakPath = DB_PATH . '.bak';
if (file_exists(DB_PATH) && filesize(DB_PATH) > 0) {
    copy(DB_PATH, $bakPath);
    logMsg("Backup criado: " . round(filesize($bakPath)/1024/1024, 1) . "MB");
}

// Cria banco novo em arquivo temporário (não apaga o atual ainda)
$dbTmp = DB_PATH . '.new';
if (file_exists($dbTmp)) unlink($dbTmp);

$db = new PDO('sqlite:' . $dbTmp);
$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
$db->exec('PRAGMA journal_mode=WAL; PRAGMA synchronous=NORMAL;');

$db->exec("CREATE TABLE imoveis(
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    hdnimovel TEXT, numero TEXT, uf TEXT, cidade TEXT, bairro TEXT, endereco TEXT,
    preco INTEGER DEFAULT 0, avaliacao INTEGER DEFAULT 0, desconto REAL DEFAULT 0,
    financiamento INTEGER DEFAULT 0, fgts INTEGER DEFAULT 0, disputa INTEGER DEFAULT 0,
    tipo TEXT DEFAULT '', modalidade TEXT DEFAULT '', modalidade_raw TEXT DEFAULT '',
    descricao TEXT DEFAULT '', condominio TEXT DEFAULT '', iptu TEXT DEFAULT '',
    link TEXT DEFAULT '', data_encerramento TEXT DEFAULT '',
    foto_url TEXT DEFAULT '', scraped_at TEXT DEFAULT ''
)");
foreach (['uf','cidade','preco','tipo','desconto','modalidade','hdnimovel','fgts','financiamento'] as $idx)
    $db->exec("CREATE INDEX idx_{$idx} ON imoveis({$idx})");

$ins = $db->prepare(
    "INSERT INTO imoveis(hdnimovel,numero,uf,cidade,bairro,endereco,preco,avaliacao,desconto,
     financiamento,fgts,disputa,tipo,modalidade,modalidade_raw,descricao,link)
     VALUES(:h,:n,:uf,:ci,:ba,:en,:pr,:av,:de,:fi,:fg,:di,:ti,:mo,:mr,:ds,:li)"
);

$ok = 0; $skip = 0;
$db->beginTransaction();

foreach ($csvValidos as $uf => $csv) {
    $fh = @fopen($csv, 'r');
    if (!$fh) { logMsg("  Não abre: {$csv}"); continue; }
    $ln = 0;
    while (($line = fgets($fh)) !== false) {
        $ln++;
        $line = trim($line);
        if ($line === '' || $ln <= 2) continue;
        if (!mb_check_encoding($line, 'UTF-8'))
            $line = @mb_convert_encoding($line, 'UTF-8', 'ISO-8859-1');
        $r = str_getcsv($line, ';');
        if (count($r) < 8) { $skip++; continue; }
        $num = trim($r[0]);
        $ufR = strtoupper(trim($r[1]));
        $ci  = mb_strtoupper(trim($r[2]));
        $ba  = trim($r[3] ?? '');
        $en  = trim($r[4] ?? '');
        $pr  = parseCentavos($r[5] ?? '0');
        $av  = parseCentavos($r[6] ?? '0');
        $de  = round((float) str_replace(',', '.', trim($r[7] ?? '0')), 2);
        $finRaw = mb_strtolower(trim($r[8] ?? ''));
        $fi  = ($finRaw === 'sim') ? 1 : 0;
        $ds  = trim($r[9]  ?? '');
        $mo  = trim($r[10] ?? '');
        $li  = trim($r[11] ?? '');
        if (!preg_match('/^\d/', str_replace(' ', '', $num))) { $skip++; continue; }
        if ($pr === 0 && $av === 0) { $skip++; continue; }
        $hdn  = hdnFrom($li) ?: preg_replace('/\D/', '', $num);
        $tipo = inferTipo($ds);
        // FGTS não se aplica a terrenos, glebas ou lotes — nunca setar 1 nesses tipos
        $fg   = ($fi && !in_array($tipo, ['terreno','gleba','lote'])) ? 1 : 0;
        $ins->execute([
            ':h'  => $hdn, ':n' => $num, ':uf' => $ufR, ':ci' => $ci,
            ':ba' => $ba,  ':en' => $en,
            ':pr' => $pr,  ':av' => $av, ':de' => $de,
            ':fi' => $fi,  ':fg' => $fg, ':di' => inferDisp($mo),
            ':ti' => $tipo, ':mo' => normMod($mo),
            ':mr' => $mo,  ':ds' => $ds, ':li' => $li,
        ]);
        $ok++;
    }
    fclose($fh);
    logMsg("  {$uf}: importados até agora = {$ok}");
}

$db->commit();
logMsg("Import total: {$ok} imóveis | Pulados: {$skip}");

// Verifica integridade antes de substituir o banco atual
$count = (int) $db->query("SELECT COUNT(*) FROM imoveis")->fetchColumn();
$db    = null; // fecha conexão

if ($count < 1000) {
    logMsg("⚠️  Banco novo tem apenas {$count} imóveis — suspeito. Mantendo banco anterior.");
    unlink($dbTmp);
    exit(3);
}

// Tudo OK — substitui banco atual
rename($dbTmp, DB_PATH);
logMsg("✅ Banco atualizado: {$count} imóveis");

@chmod(DB_PATH, 0664);
@chown(DB_PATH, 'www-data');

// Limpa temporários
array_map('unlink', glob(CSV_TMP . '/*.csv'));

$db2 = new PDO('sqlite:' . DB_PATH);
$s   = $db2->query("SELECT COUNT(*) t, SUM(fgts) fg, SUM(financiamento) fi FROM imoveis")->fetch(PDO::FETCH_ASSOC);
logMsg("=== CONCLUÍDO === Total:{$s['t']} | FGTS:{$s['fg']} | Fin:{$s['fi']}");

if ($csvOnly) {
    logMsg("Modo --csv-only. Scraping sob demanda via caixa-scrape-detalhe.php");
    exit(0);
}
logMsg("Scraping em massa desabilitado (Radware). Enriquecimento sob demanda.");
