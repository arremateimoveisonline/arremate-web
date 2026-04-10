<?php
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
echo "JPEG gerado: ".round(filesize('/var/www/arremate-br/og-banner.jpg')/1024,1)."KB\n";
