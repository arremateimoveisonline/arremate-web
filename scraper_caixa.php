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

function parseAreas(string $ds): array {
    $a = ['area_total' => 0.0, 'area_privativa' => 0.0, 'area_terreno' => 0.0];
    // Suporta 'área', 'area', 'ýrea' (corrupção de encoding do CSV da CAIXA)
    if (preg_match('/(\d+[\.,]\d+)\s+de\s+[aáý]rea\s+total/iu', $ds, $m))
        $a['area_total'] = (float) str_replace(',', '.', $m[1]);
    if (preg_match('/(\d+[\.,]\d+)\s+de\s+[aáý]rea\s+privativa/iu', $ds, $m))
        $a['area_privativa'] = (float) str_replace(',', '.', $m[1]);
    if (preg_match('/(\d+[\.,]\d+)\s+de\s+[aáý]rea\s+d[oa]\s+terreno/iu', $ds, $m))
        $a['area_terreno'] = (float) str_replace(',', '.', $m[1]);
    return $a;
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
    caixa_paga_excedente INTEGER DEFAULT 0,
    tipo TEXT DEFAULT '', modalidade TEXT DEFAULT '', modalidade_raw TEXT DEFAULT '',
    descricao TEXT DEFAULT '', condominio TEXT DEFAULT '', iptu TEXT DEFAULT '',
    link TEXT DEFAULT '', data_leilao_1 TEXT DEFAULT '', data_encerramento TEXT DEFAULT '',
    foto_url TEXT DEFAULT '', scraped_at TEXT DEFAULT '', csv_updated_at TEXT DEFAULT '',
    area_privativa REAL DEFAULT 0, area_total REAL DEFAULT 0, area_terreno REAL DEFAULT 0,
    status_caixa TEXT DEFAULT '', edital_url TEXT DEFAULT ''
)");
foreach (['uf','cidade','preco','tipo','desconto','modalidade','hdnimovel','fgts','financiamento'] as $idx)
    $db->exec("CREATE INDEX idx_{$idx} ON imoveis({$idx})");

$ins = $db->prepare(
    "INSERT INTO imoveis(hdnimovel,numero,uf,cidade,bairro,endereco,preco,avaliacao,desconto,
     financiamento,fgts,disputa,tipo,modalidade,modalidade_raw,descricao,link,
     area_total,area_privativa,area_terreno)
     VALUES(:h,:n,:uf,:ci,:ba,:en,:pr,:av,:de,:fi,:fg,:di,:ti,:mo,:mr,:ds,:li,
     :at,:ap,:atr)"
);

$ok = 0; $skip = 0;
$db->beginTransaction();

foreach ($csvValidos as $uf => $csv) {
    $fh = @fopen($csv, 'r');
    if (!$fh) { logMsg("  Não abre: {$csv}"); continue; }

    // Mapa padrão de colunas (fallback se cabeçalho não encontrado)
    $colMap = [
        'num'=>0,'uf'=>1,'cidade'=>2,'bairro'=>3,'endereco'=>4,
        'preco'=>5,'avaliacao'=>6,'desconto'=>7,'financiamento'=>8,
        'descricao'=>9,'modalidade'=>10,'link'=>11,
    ];

    $ln = 0;
    $headerLido = false;
    while (($line = fgets($fh)) !== false) {
        $ln++;
        $line = trim($line);
        if ($line === '') continue;

        // Linha 1: título/metadado — ignora
        if ($ln === 1) continue;

        // Linha 2: cabeçalho das colunas — lê e mapeia dinamicamente
        if ($ln === 2 && !$headerLido) {
            if (!mb_check_encoding($line, 'UTF-8'))
                $line = @mb_convert_encoding($line, 'UTF-8', 'ISO-8859-1');
            $headers = str_getcsv($line, ';');
            foreach ($headers as $i => $h) {
                $hNorm = mb_strtolower(trim($h));
                if (strpos($hNorm, 'financiamento') !== false) $colMap['financiamento'] = $i;
                elseif (strpos($hNorm, 'avalia')       !== false) $colMap['avaliacao']    = $i;
                elseif (strpos($hNorm, 'desconto')     !== false) $colMap['desconto']     = $i;
                elseif (strpos($hNorm, 'modalidade')   !== false) $colMap['modalidade']   = $i;
                elseif (strpos($hNorm, 'descri')       !== false) $colMap['descricao']    = $i;
                elseif (strpos($hNorm, 'bairro')       !== false) $colMap['bairro']       = $i;
                elseif (strpos($hNorm, 'endere')       !== false) $colMap['endereco']     = $i;
                elseif (strpos($hNorm, 'cidade')       !== false) $colMap['cidade']       = $i;
                elseif ($hNorm === 'uf')                          $colMap['uf']           = $i;
                elseif (strpos($hNorm, 'link')         !== false) $colMap['link']         = $i;
                elseif (strpos($hNorm, 'pre')          !== false && strpos($hNorm, 'venda') !== false) $colMap['preco'] = $i;
                elseif (strpos($hNorm, 'n')            === 0 && strpos($hNorm, 'im') !== false) $colMap['num'] = $i;
            }
            $headerLido = true;
            continue;
        }

        if (!mb_check_encoding($line, 'UTF-8'))
            $line = @mb_convert_encoding($line, 'UTF-8', 'ISO-8859-1');
        $r = str_getcsv($line, ';');
        if (count($r) < 8) { $skip++; continue; }
        $num = trim($r[$colMap['num']] ?? '');
        $ufR = strtoupper(trim($r[$colMap['uf']] ?? ''));
        $ci  = mb_strtoupper(trim($r[$colMap['cidade']] ?? ''));
        $ba  = trim($r[$colMap['bairro']] ?? '');
        $en  = trim($r[$colMap['endereco']] ?? '');
        $pr  = parseCentavos($r[$colMap['preco']] ?? '0');
        $av  = parseCentavos($r[$colMap['avaliacao']] ?? '0');
        $de  = round((float) str_replace(',', '.', trim($r[$colMap['desconto']] ?? '0')), 2);
        $finRaw = mb_strtolower(trim($r[$colMap['financiamento']] ?? ''));
        $fi  = ($finRaw === 'sim') ? 1 : 0;
        $ds  = trim($r[$colMap['descricao']]  ?? '');
        $mo  = trim($r[$colMap['modalidade']] ?? '');
        $li  = trim($r[$colMap['link']]       ?? '');
        if (!preg_match('/^\d/', str_replace(' ', '', $num))) { $skip++; continue; }
        if ($pr === 0 && $av === 0) { $skip++; continue; }
        $hdn  = hdnFrom($li) ?: preg_replace('/\D/', '', $num);
        $tipo  = inferTipo($ds);
        $areas = parseAreas($ds);
        // FGTS é INDEPENDENTE de financiamento — jamais derivado do CSV.
        $fg   = 0;
        $ins->execute([
            ':h'  => $hdn, ':n' => $num, ':uf' => $ufR, ':ci' => $ci,
            ':ba' => $ba,  ':en' => $en,
            ':pr' => $pr,  ':av' => $av, ':de' => $de,
            ':fi' => $fi,  ':fg' => $fg, ':di' => inferDisp($mo),
            ':ti' => $tipo, ':mo' => normMod($mo),
            ':mr' => $mo,  ':ds' => $ds, ':li' => $li,
            ':at' => $areas['area_total'],
            ':ap' => $areas['area_privativa'],
            ':atr'=> $areas['area_terreno'],
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

if ($count < 1000) {
    logMsg("⚠️  Banco novo tem apenas {$count} imóveis — suspeito. Mantendo banco anterior.");
    $db = null; // fecha conexão
    unlink($dbTmp);
    exit(3);
}

// Preserva dados do detail scraper: copia do banco antigo para o novo antes de substituir
if (file_exists(DB_PATH)) {
    try {
        $dbOld = DB_PATH;
        $db->exec("ATTACH DATABASE " . $db->quote($dbOld) . " AS old");
        $copied = $db->exec("
            UPDATE imoveis SET
                data_leilao_1       = COALESCE((SELECT o.data_leilao_1 FROM old.imoveis o WHERE o.hdnimovel = imoveis.hdnimovel AND o.data_leilao_1 != ''), ''),
                data_encerramento   = COALESCE((SELECT o.data_encerramento FROM old.imoveis o WHERE o.hdnimovel = imoveis.hdnimovel AND o.data_encerramento != ''), ''),
                foto_url            = COALESCE((SELECT o.foto_url FROM old.imoveis o WHERE o.hdnimovel = imoveis.hdnimovel AND o.foto_url != ''), ''),
                condominio          = COALESCE((SELECT o.condominio FROM old.imoveis o WHERE o.hdnimovel = imoveis.hdnimovel AND o.condominio != ''), ''),
                iptu                = COALESCE((SELECT o.iptu FROM old.imoveis o WHERE o.hdnimovel = imoveis.hdnimovel AND o.iptu != ''), ''),
                caixa_paga_excedente = COALESCE((SELECT o.caixa_paga_excedente FROM old.imoveis o WHERE o.hdnimovel = imoveis.hdnimovel AND o.scraped_at != ''), 0),
                fgts                = COALESCE((SELECT o.fgts FROM old.imoveis o WHERE o.hdnimovel = imoveis.hdnimovel AND o.scraped_at != ''), imoveis.fgts),
                financiamento       = COALESCE((SELECT o.financiamento FROM old.imoveis o WHERE o.hdnimovel = imoveis.hdnimovel AND o.scraped_at != ''), imoveis.financiamento),
                area_privativa      = COALESCE((SELECT o.area_privativa FROM old.imoveis o WHERE o.hdnimovel = imoveis.hdnimovel AND o.scraped_at != '' AND o.area_privativa > 0), imoveis.area_privativa),
                area_total          = COALESCE((SELECT o.area_total FROM old.imoveis o WHERE o.hdnimovel = imoveis.hdnimovel AND o.scraped_at != '' AND o.area_total > 0), imoveis.area_total),
                area_terreno        = COALESCE((SELECT o.area_terreno FROM old.imoveis o WHERE o.hdnimovel = imoveis.hdnimovel AND o.scraped_at != '' AND o.area_terreno > 0), imoveis.area_terreno),
                status_caixa        = COALESCE((SELECT o.status_caixa FROM old.imoveis o WHERE o.hdnimovel = imoveis.hdnimovel AND o.scraped_at != ''), ''),
                edital_url          = COALESCE((SELECT o.edital_url FROM old.imoveis o WHERE o.hdnimovel = imoveis.hdnimovel AND o.scraped_at != ''), ''),
                scraped_at          = COALESCE((SELECT o.scraped_at FROM old.imoveis o WHERE o.hdnimovel = imoveis.hdnimovel AND o.scraped_at != ''), '')
            WHERE EXISTS (SELECT 1 FROM old.imoveis o WHERE o.hdnimovel = imoveis.hdnimovel AND o.scraped_at != '')
        ");

        // Copia csv_updated_at do banco antigo (preserva histórico)
        $db->exec("
            UPDATE imoveis
            SET csv_updated_at = (SELECT o.csv_updated_at FROM old.imoveis o WHERE o.hdnimovel = imoveis.hdnimovel AND o.csv_updated_at != '')
            WHERE EXISTS (SELECT 1 FROM old.imoveis o WHERE o.hdnimovel = imoveis.hdnimovel AND o.csv_updated_at != '')
        ");

        // Marca novos ou com dados CSV alterados para re-scraping prioritário
        $changed = $db->exec("
            UPDATE imoveis SET csv_updated_at = datetime('now')
            WHERE NOT EXISTS (SELECT 1 FROM old.imoveis o WHERE o.hdnimovel = imoveis.hdnimovel)
               OR (SELECT o.preco     FROM old.imoveis o WHERE o.hdnimovel = imoveis.hdnimovel) != imoveis.preco
               OR (SELECT o.modalidade FROM old.imoveis o WHERE o.hdnimovel = imoveis.hdnimovel) != imoveis.modalidade
        ");
        logMsg("📌 {$changed} imóveis marcados para re-scraping prioritário (novos ou alterados no CSV)");

        // Marca imóveis que sumiram do CSV como "encerrado" (espelha comportamento da CAIXA)
        $db->exec("ATTACH DATABASE " . $db->quote($dbOld) . " AS old");
        $encerrado = $db->exec("
            UPDATE imoveis
            SET status_caixa = 'encerrado'
            WHERE hdnimovel IN (
                SELECT o.hdnimovel FROM old.imoveis o
                WHERE NOT EXISTS (SELECT 1 FROM imoveis i WHERE i.hdnimovel = o.hdnimovel)
            )
        ");
        $db->exec("DETACH DATABASE old");
        if ($encerrado > 0) {
            logMsg("🔴 {$encerrado} imóveis marcados como encerrado (não constam mais no CSV da CAIXA)");
        }

        logMsg("🔄 Dados de detalhe preservados de {$copied} imóveis scraped anteriormente");
    } catch (Exception $e) {
        logMsg("⚠️ Não foi possível preservar dados anteriores: " . $e->getMessage());
    }
}

$db = null; // fecha conexão antes de substituir banco

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
