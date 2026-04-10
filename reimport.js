/**
 * reimport.js — Cria script PHP na VPS e importa imoveis.csv para banco novo
 */
const { Client } = require('./node_modules/ssh2');

const HOST = 'lcmcreativestudio.vps-kinghost.net';
const USER = 'root';
const PASS = 'M@lu1710';

function sshExec(conn, cmd, timeout = 300000) {
  return new Promise((resolve, reject) => {
    let out = '', err = '';
    conn.exec(cmd, (e, stream) => {
      if (e) return reject(e);
      stream.on('data', d => { out += d; process.stdout.write(d.toString()); });
      stream.stderr.on('data', d => { err += d; process.stderr.write(d.toString()); });
      stream.on('close', code => resolve({ code, out, err }));
    });
    if (timeout) setTimeout(() => reject(new Error('timeout')), timeout);
  });
}

const SCRIPT = `<?php
/**
 * reimport_csv.php — Importa imoveis.csv (multi-estado) com inferTipo corrigido
 * v4 — ordena detecção: apartamento > casa > terreno (evita false-positive de 'área do terreno')
 */
set_time_limit(0);
ini_set('memory_limit','512M');
date_default_timezone_set('America/Sao_Paulo');

define('CSV_PATH', '/var/www/dados/imoveis.csv');
define('DB_PATH',  '/var/www/dados/imoveis.db');

function parseCentavos($s){$s=trim($s);if($s===''||$s==='0')return 0;$s=str_replace('.','',$s);$s=str_replace(',','.',$s);return(int)round((float)$s*100);}

function inferTipo($d){
    // Extrai só a PRIMEIRA PALAVRA da descrição (antes da primeira vírgula)
    // Isso evita false-positive de 'área do terreno' em casas e apartamentos
    $primPalavra = mb_strtolower(trim(explode(',', $d)[0]));
    $mapa = [
        'apartamento' => 'apartamento',
        'casa'        => 'casa',
        'terreno'     => 'terreno',
        'gleba'       => 'terreno',
        'lote'        => 'terreno',
        'loja'        => 'loja',
        'sala'        => 'sala',
        'comercial'   => 'comercial',
        'prédio'      => 'comercial',
        'predio'      => 'comercial',
        'galpão'      => 'comercial',
        'galpao'      => 'comercial',
        'imovel'      => 'imovel',
        'imóvel'      => 'imovel',
    ];
    foreach($mapa as $k => $v) if(strpos($primPalavra,$k)!==false) return $v;
    return 'imovel';
}

function normMod($m){
    $l=mb_strtolower(trim($m));
    if(strpos($l,'venda direta')!==false||strpos($l,'compra direta')!==false)return'Venda Direta Online';
    if(strpos($l,'leilão sfi')!==false||strpos($l,'leilao sfi')!==false)return'Leilão SFI';
    if(strpos($l,'licitação')!==false||strpos($l,'licitacao')!==false)return'Licitação Aberta';
    if(strpos($l,'venda online')!==false)return'Venda Online';
    return trim($m);
}

function hdnFrom($link){if(preg_match('/hdnimovel=(\\d+)/i',$link,$m))return $m[1];return '';}

echo "[".date('H:i:s')."] Iniciando reimport de ".CSV_PATH."\\n";

if(!file_exists(CSV_PATH)){echo "ERRO: CSV nao encontrado\\n";exit(1);}
echo "[".date('H:i:s')."] CSV: ".round(filesize(CSV_PATH)/1024/1024,1)."MB\\n";

// Backup do banco atual
if(file_exists(DB_PATH)&&filesize(DB_PATH)>0){
    copy(DB_PATH, DB_PATH.'.bak_reimport');
    echo "[".date('H:i:s')."] Backup criado\\n";
}

// Cria banco novo temporário
$dbTmp = DB_PATH.'.new';
if(file_exists($dbTmp))unlink($dbTmp);

$db = new PDO('sqlite:'.$dbTmp);
$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
$db->exec('PRAGMA journal_mode=WAL; PRAGMA synchronous=NORMAL;');
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

$fh = fopen(CSV_PATH, 'r');
$ln = 0;
while(($line=fgets($fh))!==false){
    $ln++;
    $line=trim($line);
    if($line===''||$ln<=2)continue;
    if(!mb_check_encoding($line,'UTF-8'))
        $line=@mb_convert_encoding($line,'UTF-8','ISO-8859-1');
    $r=str_getcsv($line,';');
    if(count($r)<8){$skip++;continue;}
    $num=trim($r[0]);$uf=strtoupper(trim($r[1]));
    $ci=mb_strtoupper(trim($r[2]));
    $ba=trim($r[3]??'');$en=trim($r[4]??'');
    $pr=parseCentavos($r[5]??'0');$av=parseCentavos($r[6]??'0');
    $de=round((float)str_replace(',','.',trim($r[7]??'0')),2);
    $finRaw=mb_strtolower(trim($r[8]??''));
    $fi=($finRaw==='sim')?1:0;
    $ds=trim($r[9]??'');$mo=trim($r[10]??'');$li=trim($r[11]??'');
    if(!preg_match('/^\\d/',str_replace(' ','',$num))){$skip++;continue;}
    if($pr===0&&$av===0){$skip++;continue;}
    $hdn=hdnFrom($li)?:preg_replace('/\\D/','',$num);
    $ins->execute([':h'=>$hdn,':n'=>$num,':uf'=>$uf,':ci'=>$ci,':ba'=>$ba,':en'=>$en,
        ':pr'=>$pr,':av'=>$av,':de'=>$de,':fi'=>$fi,':fg'=>$fi,
        ':di'=>(strpos(mb_strtolower($mo),'venda online')!==false&&strpos(mb_strtolower($mo),'direta')===false)?1:0,
        ':ti'=>inferTipo($ds),':mo'=>normMod($mo),':mr'=>$mo,':ds'=>$ds,':li'=>$li]);
    $ok++;
    if($ok%5000===0)echo "[".date('H:i:s')."] {$ok} importados...\\n";
}
fclose($fh);
$db->commit();

echo "[".date('H:i:s')."] Import: {$ok} ok | {$skip} pulados\\n";

// Verifica
$count=(int)$db->query("SELECT COUNT(*) FROM imoveis")->fetchColumn();
if($count < 1000){echo "ERRO: banco novo suspeito ({$count} registros)\\n";exit(1);}

// Distribuicao de tipos
$tipos=$db->query("SELECT tipo, COUNT(*) qt FROM imoveis GROUP BY tipo ORDER BY qt DESC")->fetchAll(PDO::FETCH_ASSOC);
echo "Tipos:\\n";
foreach($tipos as $t)echo "  {$t['tipo']}: {$t['qt']}\\n";

$db=null;

// Substitui banco
rename($dbTmp, DB_PATH);
chmod(DB_PATH,0664);
chown(DB_PATH,'www-data');

echo "[".date('H:i:s')."] Banco substituido: {$count} imoveis\\n";
echo "OK\\n";
`;

async function main() {
  const conn = new Client();
  await new Promise((resolve, reject) =>
    conn.on('ready', resolve).on('error', reject)
        .connect({ host: HOST, port: 22, username: USER, password: PASS, readyTimeout: 15000 })
  );
  console.log('✅ Conectado\n');

  // Escreve o script PHP na VPS
  console.log('📝 Criando script de reimport...');
  // Escapar para uso em comando shell
  const escapedScript = SCRIPT.replace(/\\/g, '\\\\').replace(/'/g, "'\"'\"'");
  await sshExec(conn, `cat > /tmp/reimport_csv.php << 'PHPEOF'\n${SCRIPT}\nPHPEOF`, 10000);

  console.log('🗄️  Executando reimport (aguarde ~1-2 min)...');
  await sshExec(conn, 'php /tmp/reimport_csv.php', 300000);

  console.log('\n📊 Verificando banco final:');
  await sshExec(conn, `sqlite3 /var/www/dados/imoveis.db "SELECT tipo, COUNT(*) qt FROM imoveis GROUP BY tipo ORDER BY qt DESC;"`, 10000);

  await sshExec(conn, `sqlite3 /var/www/dados/imoveis.db "SELECT uf, COUNT(*) qt FROM imoveis GROUP BY uf ORDER BY qt DESC LIMIT 10;"`, 10000);

  conn.end();
  console.log('\n✅ Reimport concluído!');
}

main().catch(e => { console.error('ERRO:', e); process.exit(1); });
