#!/usr/bin/env php
<?php
/**
 * scraper_caixa.php v3 — Arremate Imóveis Online
 * 
 * ESTRATÉGIA (anti-bot da Caixa com Radware Bot Manager):
 *   FASE 1: Download CSVs por estado → SQLite (isso NÃO é bloqueado)
 *   FASE 2: Scraping de detalhes em lotes LENTOS (5 por minuto, com pausa longa)
 *           OU usar --csv-only para pular scraping
 *
 * O enriquecimento real (FGTS, foto, condomínio) é feito sob demanda
 * pelo caixa-scrape-detalhe.php quando o usuário acessa a página.
 *
 * Uso:
 *   php scraper_caixa.php                 # CSVs de todos os estados + scraping lento
 *   php scraper_caixa.php SP RJ           # apenas esses estados
 *   php scraper_caixa.php --csv-only      # só importar CSVs, sem scraping
 *   php scraper_caixa.php --csv-only SP   # CSVs só de SP
 */

set_time_limit(0);
ini_set('memory_limit','512M');
date_default_timezone_set('America/Sao_Paulo');

define('DB_PATH','/var/www/dados/imoveis.db');
define('CSV_DIR','/var/www/dados/csv');
define('LOG_FILE','/var/log/arremate_scraper.log');

$UFS=['AC','AL','AM','AP','BA','CE','DF','ES','GO','MA','MG','MS','MT',
      'PA','PB','PE','PI','PR','RJ','RN','RO','RR','RS','SC','SE','SP','TO'];

$args=array_slice($argv,1);
$csvOnly=in_array('--csv-only',$args);
$importOnly=in_array('--import-only',$args); // pula download, só importa CSVs já em disco
$args=array_filter($args,function($a){return!in_array($a,['--csv-only','--import-only']);});
$ufsAlvo=$args?array_map('strtoupper',$args):$UFS;

function logMsg($m){$l='['.date('Y-m-d H:i:s').'] '.$m."\n";echo $l;@file_put_contents(LOG_FILE,$l,FILE_APPEND);}

define('COOKIE_JAR','/tmp/arremate_caixa_cookies.txt');
define('BASE_URL','https://venda-imoveis.caixa.gov.br/');
define('UA','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/124.0.0.0 Safari/537.36');

/**
 * Usa curl CLI (não libcurl PHP) para contornar bloqueio Radware por TLS fingerprint.
 * O curl de linha de comando passa pelo Radware; o libcurl do PHP é bloqueado.
 */
function curlGet($url,$timeout=30,$referer=''){
    $jar=escapeshellarg(COOKIE_JAR);
    $ua=escapeshellarg(UA);
    $urlE=escapeshellarg($url);
    $ref=$referer?"-H ".escapeshellarg("Referer: {$referer}"):'';
    $sentinel='__HTTP_CODE__';
    $cmd="curl -s -L --max-redirs 5 --max-time {$timeout} "
        ."-c {$jar} -b {$jar} -A {$ua} "
        ."-H 'Accept: text/html,application/xhtml+xml,*/*;q=0.8' "
        ."-H 'Accept-Language: pt-BR,pt;q=0.9' "
        ."-H 'Connection: keep-alive' "
        ."{$ref} "
        ."-w '\\n{$sentinel}%{http_code}' "
        ."{$urlE} 2>/dev/null";
    $out=(string)shell_exec($cmd);
    $pos=strrpos($out,"\n{$sentinel}");
    if($pos!==false){
        $code=(int)substr($out,$pos+strlen("\n{$sentinel}"));
        $body=substr($out,0,$pos);
    }else{$code=0;$body=$out;}
    return['body'=>$body,'code'=>$code];
}

function initSession(){
    // Visita a homepage para obter cookies de sessão do Radware
    curlGet(BASE_URL,15);
    sleep(2);
}

function parseCentavos($s){$s=trim($s);if($s===''||$s==='0')return 0;$s=str_replace('.','',$s);$s=str_replace(',','.',$s);return(int)round((float)$s*100);}
function inferTipo($d){
    // Usa apenas a primeira palavra (antes da 1ª vírgula) para evitar
    // false-positive: "área do terreno" em casas/apartamentos/lojas/etc.
    $prim=mb_strtolower(trim(explode(',',$d)[0]));
    $mapa=['apartamento'=>'apartamento','casa'=>'casa','terreno'=>'terreno',
           'gleba'=>'gleba','lote'=>'lote','loja'=>'loja','sala'=>'sala',
           'comercial'=>'comercial','prédio'=>'comercial','predio'=>'comercial',
           'galpão'=>'comercial','galpao'=>'comercial',
           'imóvel rural'=>'imovel','imovel rural'=>'imovel','imovel'=>'imovel','imóvel'=>'imovel'];
    foreach($mapa as $k=>$v) if(strpos($prim,$k)!==false) return $v;
    return'imovel';
}
function normMod($m){$l=mb_strtolower(trim($m));if(strpos($l,'venda direta')!==false||strpos($l,'compra direta')!==false)return'Venda Direta Online';if(strpos($l,'leilão sfi')!==false||strpos($l,'leilao sfi')!==false)return'Leilão SFI';if(strpos($l,'licitação')!==false||strpos($l,'licitacao')!==false)return'Licitação Aberta';if(strpos($l,'venda online')!==false)return'Venda Online';return trim($m);}
function inferDisp($m){$l=mb_strtolower($m);return(strpos($l,'venda online')!==false&&strpos($l,'direta')===false)?1:0;}
function hdnFrom($link){if(preg_match('/hdnimovel=(\d+)/i',$link,$m))return $m[1];return'';}

/* ═══ FASE 1: CSVs ═══ */
logMsg("=== PIPELINE v4 ===");
logMsg("Estados: ".implode(',',$ufsAlvo).($importOnly?' [IMPORT-ONLY]':($csvOnly?' [CSV-ONLY]':'')));

if(!is_dir(CSV_DIR))mkdir(CSV_DIR,0775,true);

if($importOnly){
    // Pula download — lê CSVs já existentes em disco
    $csvFiles=[];
    foreach($ufsAlvo as $uf){
        $f=CSV_DIR."/Lista_imoveis_{$uf}.csv";
        if(file_exists($f)&&filesize($f)>500){$csvFiles[]=$f;}
    }
    logMsg("Modo --import-only: ".count($csvFiles)." CSVs em disco.");
}else{
    // Inicializa sessão — obtém cookies do Radware visitando a homepage
    logMsg("Iniciando sessão na CAIXA...");
    initSession();
    logMsg("Sessão iniciada.");

    $csvFiles=[];$bloqueados=0;
    foreach($ufsAlvo as $uf){
        logMsg("CSV {$uf}...");
        $r=curlGet(
            "https://venda-imoveis.caixa.gov.br/listaweb/Lista_imoveis_{$uf}.csv",
            30,BASE_URL
        );
        $body=$r['body']??'';
        $isBlocked=stripos($body,'radware')!==false||stripos($body,'captcha')!==false||stripos($body,'perfdrive')!==false||stripos($body,'shieldsquare')!==false;
        if(!$isBlocked&&$r['code']>=200&&$r['code']<400&&strlen($body)>500){
            $dest=CSV_DIR."/Lista_imoveis_{$uf}.csv";
            file_put_contents($dest,$body);
            $csvFiles[]=$dest;
            logMsg("  ✓ OK: ".substr_count($body,"\n")." linhas");
        }elseif($isBlocked){
            $bloqueados++;
            logMsg("  ✗ BLOQUEADO por Radware/captcha");
        }else{
            logMsg("  ✗ FALHA: HTTP={$r['code']} len=".strlen($body));
        }
        usleep(800000);
    }
    logMsg("CSVs válidos: ".count($csvFiles)." | Bloqueados: {$bloqueados} | Falhou: ".(count($ufsAlvo)-count($csvFiles)-$bloqueados));
}

// Aborta se nenhum CSV disponível
if(count($csvFiles)===0){
    logMsg("⚠️  ATENÇÃO: 0 CSVs válidos — Banco NÃO atualizado.");
    exit(2);
}
if(!$importOnly&&count($ufsAlvo)>=15&&count($csvFiles)<15){
    logMsg("⚠️  ATENÇÃO: apenas ".count($csvFiles)." CSVs válidos (mínimo: 15).");
    logMsg("⚠️  Banco NÃO atualizado — mantendo dados existentes para não perder imóveis.");
    exit(2);
}

/* ═══ FASE 2: CSV → SQLite ═══ */
logMsg("Recriando banco...");
if(file_exists(DB_PATH))copy(DB_PATH,DB_PATH.'.bak');
if(file_exists(DB_PATH))unlink(DB_PATH);

$db=new PDO('sqlite:'.DB_PATH);
$db->setAttribute(PDO::ATTR_ERRMODE,PDO::ERRMODE_EXCEPTION);
$db->exec('PRAGMA journal_mode=WAL;PRAGMA synchronous=NORMAL');

$db->exec("CREATE TABLE imoveis(
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    hdnimovel TEXT,numero TEXT,uf TEXT,cidade TEXT,bairro TEXT,endereco TEXT,
    preco INTEGER DEFAULT 0,avaliacao INTEGER DEFAULT 0,desconto REAL DEFAULT 0,
    financiamento INTEGER DEFAULT 0,fgts INTEGER DEFAULT 0,disputa INTEGER DEFAULT 0,
    tipo TEXT DEFAULT '',modalidade TEXT DEFAULT '',modalidade_raw TEXT DEFAULT '',
    descricao TEXT DEFAULT '',condominio TEXT DEFAULT '',iptu TEXT DEFAULT '',
    link TEXT DEFAULT '',data_encerramento TEXT DEFAULT '',
    foto_url TEXT DEFAULT '',scraped_at TEXT DEFAULT ''
)");
foreach(['uf','cidade','preco','tipo','desconto','modalidade','hdnimovel','fgts','financiamento'] as $idx)
    $db->exec("CREATE INDEX idx_{$idx} ON imoveis({$idx})");

$ins=$db->prepare("INSERT INTO imoveis(hdnimovel,numero,uf,cidade,bairro,endereco,preco,avaliacao,desconto,financiamento,fgts,disputa,tipo,modalidade,modalidade_raw,descricao,link)VALUES(:h,:n,:uf,:ci,:ba,:en,:pr,:av,:de,:fi,:fg,:di,:ti,:mo,:mr,:ds,:li)");

$ok=0;$skip=0;
$db->beginTransaction();
foreach($csvFiles as $csv){
    $fh=@fopen($csv,'r');if(!$fh)continue;
    $ln=0;
    while(($line=fgets($fh))!==false){
        $ln++;$line=trim($line);
        if($line===''||$ln<=2)continue;
        if(!mb_check_encoding($line,'UTF-8'))$line=@mb_convert_encoding($line,'UTF-8','ISO-8859-1');
        $r=str_getcsv($line,';');
        if(count($r)<8){$skip++;continue;}
        $num=trim($r[0]);$uf=strtoupper(trim($r[1]));$ci=mb_strtoupper(trim($r[2]));
        $ba=trim($r[3]??'');$en=trim($r[4]??'');
        $pr=parseCentavos($r[5]??'0');$av=parseCentavos($r[6]??'0');
        $de=round((float)str_replace(',','.',trim($r[7]??'0')),2);
        $finRaw=mb_strtolower(trim($r[8]??''));
        $fi=($finRaw==='sim')?1:0;
        $ds=trim($r[9]??'');$mo=trim($r[10]??'');$li=trim($r[11]??'');
        if(!preg_match('/^\d/',str_replace(' ','',$num))){$skip++;continue;}
        if($pr===0&&$av===0){$skip++;continue;}
        $hdn=hdnFrom($li)?:preg_replace('/\D/','',$num);

        // FGTS inferência do CSV: campo financiamento=Sim → provavelmente tem SBPE → fgts=1
        $fgts_csv=($fi===1)?1:0;

        $ins->execute([':h'=>$hdn,':n'=>$num,':uf'=>$uf,':ci'=>$ci,':ba'=>$ba,':en'=>$en,
            ':pr'=>$pr,':av'=>$av,':de'=>$de,':fi'=>$fi,':fg'=>$fgts_csv,':di'=>inferDisp($mo),
            ':ti'=>inferTipo($ds),':mo'=>normMod($mo),':mr'=>$mo,':ds'=>$ds,':li'=>$li]);
        $ok++;
    }
    fclose($fh);
}
$db->commit();
logMsg("Importados: {$ok} | Pulados: {$skip}");

@chmod(DB_PATH,0664);
@chown(DB_PATH,'www-data');

$s=$db->query("SELECT COUNT(*) t,SUM(fgts) fg,SUM(financiamento) fi FROM imoveis")->fetch(PDO::FETCH_ASSOC);
logMsg("=== CSV IMPORT CONCLUÍDO === Total:{$s['t']} FGTS:{$s['fg']} Fin:{$s['fi']}");

if($csvOnly){
    logMsg("Modo --csv-only. Scraping será feito sob demanda.");
    exit(0);
}

logMsg("NOTA: Scraping em massa desabilitado (Caixa usa Radware anti-bot).");
logMsg("O enriquecimento (FGTS real, foto, condomínio) é feito sob demanda via caixa-scrape-detalhe.php");
