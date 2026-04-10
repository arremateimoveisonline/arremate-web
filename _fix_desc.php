<?php
$novaDesc = 'A plataforma mais completa para buscar imóveis da CAIXA. Filtros por estado, cidade, tipo, modalidade e desconto. Dados atualizados diariamente. Imobiliária parceira credenciada para o estado de São Paulo. CRECI: 043342.';

$arquivos = [
  '/var/www/arremate-br/index.php',
  '/var/www/arremate-br/resultados.html',
  '/var/www/arremate-br/blog.html',
  '/var/www/arremate-br/favoritos.html',
];

foreach($arquivos as $f){
  if(!file_exists($f)){echo "NAO ENCONTRADO: $f\n";continue;}
  $c = file_get_contents($f);
  $c = preg_replace(
    '/<meta property="og:description" content="[^"]*"/',
    '<meta property="og:description" content="' . $novaDesc . '"',
    $c
  );
  file_put_contents($f, $c);
  echo "OK: $f\n";
}

echo "\n=== Verificando index.php ===\n";
preg_match('/<meta property="og:description"[^>]*>/', file_get_contents('/var/www/arremate-br/index.php'), $m);
echo $m[0] . "\n";
