const{Client}=require('./node_modules/ssh2');

const phpScript = `<?php
$w=1200;$h=630;
$im=imagecreatetruecolor($w,$h);
for($y=0;$y<$h;$y++){
  $g=(int)(20+(60-20)*$y/$h);
  $b=(int)(60+(140-60)*$y/$h);
  $c=imagecolorallocate($im,0,$g,$b);
  imageline($im,0,$y,$w,$y,$c);
}
$laranja=imagecolorallocate($im,243,146,0);
imagefilledrectangle($im,0,0,$w,14,$laranja);
imagefilledrectangle($im,0,$h-14,$w,$h,$laranja);

$logoUrl='https://cdn.tess.im/assets/uploads/0e90758d-2354-4677-b743-9724498c3976.jpg';
$logoData=@file_get_contents($logoUrl);
if($logoData){
  $logo=@imagecreatefromstring($logoData);
  if($logo){
    $lw=imagesx($logo);$lh=imagesy($logo);
    $s=130;$nw=(int)($s*$lw/$lh);
    $lr=imagecreatetruecolor($nw,$s);
    $branco=imagecolorallocate($lr,255,255,255);
    imagefilledrectangle($lr,0,0,$nw,$s,$branco);
    imagecopyresampled($lr,$logo,0,0,0,0,$nw,$s,$lw,$lh);
    $bkg=imagecolorallocate($im,255,255,255);
    imagefilledellipse($im,110,195,$nw+50,$s+50,$bkg);
    imagecopy($im,$lr,(int)(110-$nw/2),(int)(195-$s/2),0,0,$nw,$s);
    imagedestroy($logo);imagedestroy($lr);
  }
}

$w2=imagecolorallocate($im,255,255,255);
$am=imagecolorallocate($im,255,200,50);
$cz=imagecolorallocate($im,180,200,230);

function tg($im,$t,$y,$c,$f=5){
  $cw=imagefontwidth($f);
  $x=(imagesx($im)-$cw*strlen($t))/2;
  imagestring($im,$f,(int)$x,$y,$t,$c);
}

tg($im,'Arremate Imoveis Online',155,$w2,5);
imagefilledrectangle($im,80,200,1120,205,$laranja);
tg($im,'Imoveis da CAIXA com desconto de ate 90%',225,$am,4);
tg($im,'Apartamentos, Casas, Terrenos, Comerciais',268,$cz,3);
tg($im,'Leilao | Licitacao | Venda Direta | Venda Online',305,$cz,3);
imagefilledrectangle($im,80,345,1120,348,$laranja);
tg($im,'+ 29.000 imoveis  |  Atualizado diariamente  |  Brasil',370,$w2,4);
tg($im,'arremateimoveisonline.com.br',420,$am,4);
tg($im,'Imobiliaria Parceira Credenciada  CRECI-SP 043342',468,$cz,3);

imagejpeg($im,'/var/www/arremate-br/og-banner.jpg',92);
imagedestroy($im);
chmod('/var/www/arremate-br/og-banner.jpg',0644);
chown('/var/www/arremate-br/og-banner.jpg','www-data');
echo "JPEG gerado: ".round(filesize('/var/www/arremate-br/og-banner.jpg')/1024,1)."KB\\n";
`;

function exec(conn,cmd,t=30000){
  return new Promise((resolve,reject)=>{
    let out='';
    conn.exec(cmd,(e,s)=>{
      if(e)return reject(e);
      s.on('data',d=>{out+=d;process.stdout.write(d.toString());});
      s.stderr.on('data',d=>process.stderr.write(d.toString()));
      s.on('close',code=>resolve({code,out}));
    });
    setTimeout(()=>reject(new Error('timeout')),t);
  });
}

function getSftp(conn){return new Promise((r,j)=>conn.sftp((e,s)=>e?j(e):r(s)));}
function sftpPut(sftp,local,remote){return new Promise((r,j)=>sftp.fastPut(local,remote,{},e=>e?j(e):r()));}

const fs=require('fs');
const conn=new Client();
conn.on('ready',async()=>{
  // Salva script localmente e faz upload
  fs.writeFileSync('C:/Users/César/Downloads/arremate-br/_gerar_jpeg.php', phpScript);
  const sftp=await getSftp(conn);
  await sftpPut(sftp,'C:/Users/César/Downloads/arremate-br/_gerar_jpeg.php','/tmp/_gerar_jpeg.php');
  console.log('Script enviado, executando...');
  await exec(conn,'php /tmp/_gerar_jpeg.php');

  // Agora atualiza og:image para .jpg em todas as páginas
  await exec(conn,`
for f in /var/www/arremate-br/index.php /var/www/arremate-br/resultados.html /var/www/arremate-br/blog.html /var/www/arremate-br/favoritos.html; do
  # Troca .png por .jpg no og:image
  sed -i 's|og-banner\\.png|og-banner.jpg|g' $f
  # Adiciona og:url se não existir
  if ! grep -q 'og:url' $f; then
    sed -i 's|<meta property="og:image:height"|<meta property="og:url" content="https://arremateimoveisonline.com.br/">\\n  <meta property="og:image:height"|' $f
  fi
  echo "OK: $f"
done
`);

  // Verifica resultado final
  console.log('\n=== Tags na index.php ===');
  await exec(conn,"grep -n 'og:' /var/www/arremate-br/index.php | head -10");

  // Verifica imagem
  console.log('\n=== Imagem JPEG ===');
  await exec(conn,'ls -lh /var/www/arremate-br/og-banner.jpg && file /var/www/arremate-br/og-banner.jpg');

  conn.end();
  console.log('\n✅ Pronto!');
}).on('error',e=>console.error(e))
.connect({host:'lcmcreativestudio.vps-kinghost.net',port:22,username:'root',password:'M@lu1710',readyTimeout:15000});
