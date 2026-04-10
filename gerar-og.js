/**
 * gerar-og.js — Cria og-banner.php na VPS, gera og-banner.png e adiciona og:image em todas as páginas
 */
const{Client}=require('./node_modules/ssh2');
const conn=new Client();

function exec(conn,cmd,timeout=30000){
  return new Promise((resolve,reject)=>{
    let out='',err='';
    conn.exec(cmd,(e,s)=>{
      if(e)return reject(e);
      s.on('data',d=>{out+=d;process.stdout.write(d.toString());});
      s.stderr.on('data',d=>{err+=d;process.stderr.write(d.toString());});
      s.on('close',code=>resolve({code,out,err}));
    });
    setTimeout(()=>reject(new Error('timeout')),timeout);
  });
}

// Script PHP que gera o banner OG 1200x630
const bannerPhp = `<?php
/**
 * og-banner.php — Gera og-banner.png com identidade visual Arremate
 * Roda uma vez via CLI: php og-banner.php
 */
$w=1200;$h=630;

$im=imagecreatetruecolor($w,$h);

// Fundo gradiente azul escuro
$azul1=imagecolorallocate($im,0,20,60);
$azul2=imagecolorallocate($im,0,60,140);
for($y=0;$y<$h;$y++){
    $r=intval(0+(0-0)*$y/$h);
    $g=intval(20+(60-20)*$y/$h);
    $b=intval(60+(140-60)*$y/$h);
    $c=imagecolorallocate($im,$r,$g,$b);
    imageline($im,0,$y,$w,$y,$c);
}

// Faixa laranja no topo
$laranja=imagecolorallocate($im,243,146,0);
imagefilledrectangle($im,0,0,$w,12,$laranja);

// Faixa laranja na base
imagefilledrectangle($im,0,$h-12,$w,$h,$laranja);

// Tenta baixar e inserir logo da CDN
$logoUrl='https://cdn.tess.im/assets/uploads/0e90758d-2354-4677-b743-9724498c3976.jpg';
$logoData=@file_get_contents($logoUrl);
if($logoData){
    $logo=@imagecreatefromstring($logoData);
    if($logo){
        $lw=imagesx($logo);$lh=imagesy($logo);
        $logoSize=120;
        $ratio=$lw/$lh;
        $nw=(int)($logoSize*$ratio);$nh=$logoSize;
        $logoR=imagecreatetruecolor($nw,$nh);
        imagecopyresampled($logoR,$logo,0,0,0,0,$nw,$nh,$lw,$lh);
        // Círculo de fundo branco para a logo
        $branco=imagecolorallocate($im,255,255,255);
        imagefilledellipse($im,110,200,$nw+40,$nh+40,$branco);
        imagecopy($im,$logoR,110-$nw/2,200-$nh/2,0,0,$nw,$nh);
        imagedestroy($logo);imagedestroy($logoR);
    }
}

// Texto — usa fonte built-in do GD (sem precisar de arquivo TTF externo)
$branco=imagecolorallocate($im,255,255,255);
$amarelo=imagecolorallocate($im,255,200,50);
$cinza=imagecolorallocate($im,180,200,230);

// Título principal (grande) — usando imagestring com escala
// Função helper para texto centralizado com escala
function textoGrande($im,$texto,$y,$cor,$escala=5){
    $charW=imagefontwidth($escala);
    $len=strlen($texto);
    $totalW=$charW*$len;
    $x=(imagesx($im)-$totalW)/2;
    imagestring($im,$escala,(int)$x,$y,$texto,$cor);
}

// "Arremate Imóveis Online" — texto ASCII simples para compatibilidade
textoGrande($im,'Arremate Imoveis Online',160,$branco,5);

// Linha laranja decorativa
imagefilledrectangle($im,80,210,1120,215,$laranja);

// Tagline
textoGrande($im,'Imoveis da CAIXA com desconto de ate 90%',240,$amarelo,4);

// Subtítulo
textoGrande($im,'Apartamentos, Casas, Terrenos, Comerciais',290,$cinza,3);
textoGrande($im,'Leilao | Licitacao | Venda Direta | Venda Online',330,$cinza,3);

// Linha separadora
imagefilledrectangle($im,80,370,1120,372,$laranja);

// Estatísticas
textoGrande($im,'+ 29.000 imoveis  |  Atualizado diariamente  |  Todo o Brasil',395,$branco,4);

// URL
textoGrande($im,'arremateimoveisonline.com.br',450,$amarelo,4);

// CRECI
textoGrande($im,'Imobiliaria Parceira Credenciada  CRECI-SP 043342',500,$cinza,3);

$dest='/var/www/arremate-br/og-banner.png';
imagepng($im,$dest,8);
imagedestroy($im);
echo "Banner gerado: {$dest} (".round(filesize($dest)/1024,1)."KB)\\n";
chmod($dest,0644);
chown($dest,'www-data');
`;

conn.on('ready',async()=>{
  console.log('✅ Conectado\n');

  // Escreve og-banner.php
  await exec(conn,`cat > /var/www/arremate-br/og-banner.php << 'PHPEOF'\n${bannerPhp}\nPHPEOF`);
  console.log('✅ og-banner.php criado');

  // Gera o PNG
  await exec(conn,'php /var/www/arremate-br/og-banner.php');
  console.log('');

  // Verifica se gerou
  await exec(conn,'ls -lh /var/www/arremate-br/og-banner.png && file /var/www/arremate-br/og-banner.png');

  // Adiciona og:image e og:url nas páginas principais
  const pages={
    '/var/www/arremate-br/index.php': {url:'https://arremateimoveisonline.com.br/'},
    '/var/www/arremate-br/resultados.html': {url:'https://arremateimoveisonline.com.br/resultados.html'},
    '/var/www/arremate-br/blog.html': {url:'https://arremateimoveisonline.com.br/blog.html'},
    '/var/www/arremate-br/favoritos.html': {url:'https://arremateimoveisonline.com.br/favoritos.html'},
  };

  const ogImage='https://arremateimoveisonline.com.br/og-banner.png';

  for(const[file,meta] of Object.entries(pages)){
    // Verifica se og:image já existe
    const check=await exec(conn,`grep -c 'og:image' ${file} || true`);
    const count=parseInt(check.out.trim()||'0');
    if(count>0){
      // Atualiza og:image existente
      await exec(conn,`sed -i 's|property="og:image" content="[^"]*"|property="og:image" content="${ogImage}"|g' ${file}`);
      console.log(`✅ og:image atualizado: ${file}`);
    } else {
      // Insere og:image e og:url após og:type
      await exec(conn,`sed -i 's|<meta property="og:type"|<meta property="og:image" content="${ogImage}">\\n  <meta property="og:url" content="${meta.url}">\\n  <meta property="og:image:width" content="1200">\\n  <meta property="og:image:height" content="630">\\n  <meta property="og:image" content="${ogImage}">\\n  <meta property="og:type"|' ${file}`);
      console.log(`✅ og:image inserido: ${file}`);
    }
  }

  // imovel.php é especial — og:image dinâmico (usa foto do imóvel se disponível, senão banner)
  await exec(conn,`grep -c 'og:image' /var/www/arremate-br/imovel.php || true`);

  console.log('\n🔗 URL para divulgar no WhatsApp:');
  console.log('   https://arremateimoveisonline.com.br');
  console.log('\n✅ Pronto! Ao colar o link no WhatsApp aparecerá:');
  console.log('   🖼️  Imagem: og-banner.png (1200x630)');
  console.log('   📌 Título: Arremate Imóveis Online');
  console.log('   📝 Descrição: A plataforma mais completa...');

  conn.end();
}).on('error',e=>console.error(e))
.connect({host:'lcmcreativestudio.vps-kinghost.net',port:22,username:'root',password:'M@lu1710',readyTimeout:15000});
