<?php
/**
 * foto-proxy.php — Proxy de imagem para OG tags (WhatsApp/redes sociais)
 * Busca a foto do imóvel na CAIXA e serve pelo nosso domínio.
 * Cache em /tmp por 24h para não sobrecarregar a VPS.
 */

$hdn = preg_replace('/[^0-9]/', '', $_GET['h'] ?? '');
if (!$hdn || strlen($hdn) < 5) {
    http_response_code(400);
    exit;
}

$hdn       = str_pad($hdn, 14, '0', STR_PAD_LEFT);
$cacheFile = sys_get_temp_dir() . '/arremate_foto_' . $hdn . '.jpg';
$cacheTTL  = 86400; // 24 horas

/* Serve do cache se ainda válido */
if (file_exists($cacheFile) && (time() - filemtime($cacheFile)) < $cacheTTL) {
    header('Content-Type: image/jpeg');
    header('Cache-Control: public, max-age=86400');
    header('X-Cache: HIT');
    readfile($cacheFile);
    exit;
}

/* Busca da CAIXA */
$url = "https://venda-imoveis.caixa.gov.br/fotos/F{$hdn}21.jpg";
$ch  = curl_init($url);
curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_FOLLOWLOCATION => true,
    CURLOPT_TIMEOUT        => 10,
    CURLOPT_USERAGENT      => 'Mozilla/5.0 (compatible; Googlebot/2.1; +http://www.google.com/bot.html)',
    CURLOPT_SSL_VERIFYPEER => false,
    CURLOPT_HTTPHEADER     => ['Accept: image/jpeg,image/*'],
]);
$data = curl_exec($ch);
$info = curl_getinfo($ch);
curl_close($ch);

if (!$data || $info['http_code'] !== 200 || $info['size_download'] < 1000) {
    http_response_code(404);
    exit;
}

/* Salva no cache */
file_put_contents($cacheFile, $data);

header('Content-Type: image/jpeg');
header('Cache-Control: public, max-age=86400');
header('X-Cache: MISS');
echo $data;
