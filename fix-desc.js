const{Client}=require('./node_modules/ssh2');
const fs=require('fs');

const phpScript = `<?php
$novaDesc = 'A plataforma mais completa para buscar imóveis da CAIXA. Filtros por estado, cidade, tipo, modalidade e desconto. Dados atualizados diariamente. Imobiliária parceira credenciada para o estado de São Paulo. CRECI: 043342.';

$arquivos = [
  '/var/www/arremate-br/index.php',
  '/var/www/arremate-br/resultados.html',
  '/var/www/arremate-br/blog.html',
  '/var/www/arremate-br/favoritos.html',
];

foreach($arquivos as $f){
  if(!file_exists($f)){echo "NAO ENCONTRADO: $f\\n";continue;}
  $c = file_get_contents($f);
  $c = preg_replace(
    '/<meta property="og:description" content="[^"]*"/',
    '<meta property="og:description" content="' . $novaDesc . '"',
    $c
  );
  file_put_contents($f, $c);
  echo "OK: $f\\n";
}

echo "\\n=== Verificando index.php ===\\n";
preg_match('/<meta property="og:description"[^>]*>/', file_get_contents('/var/www/arremate-br/index.php'), $m);
echo $m[0] . "\\n";
`;

fs.writeFileSync('C:/Users/César/Downloads/arremate-br/_fix_desc.php', phpScript);

function getSftp(conn){return new Promise((r,j)=>conn.sftp((e,s)=>e?j(e):r(s)));}
function sftpPut(sftp,l,r){return new Promise((res,rej)=>sftp.fastPut(l,r,{},e=>e?rej(e):res()));}
function exec(conn,cmd){return new Promise((r,j)=>{let o='';conn.exec(cmd,(e,s)=>{if(e)return j(e);s.on('data',d=>{o+=d;process.stdout.write(d.toString());});s.stderr.on('data',d=>process.stderr.write(d.toString()));s.on('close',c=>r({code:c,out:o}));});});}

const conn=new Client();
conn.on('ready',async()=>{
  const sftp=await getSftp(conn);
  await sftpPut(sftp,'C:/Users/César/Downloads/arremate-br/_fix_desc.php','/tmp/_fix_desc.php');
  await exec(conn,'php /tmp/_fix_desc.php');
  conn.end();
}).on('error',e=>console.error(e))
.connect({host:'lcmcreativestudio.vps-kinghost.net',port:22,username:'root',password:'M@lu1710',readyTimeout:15000});
