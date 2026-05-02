<?php
/**
 * foto-proxy.php — Proxy de imagem para OG tags (WhatsApp/redes sociais)
 * Busca a foto_url real do banco e serve pelo nosso domínio com cache 24h.
 */

$hdn = preg_replace('/[^0-9a-zA-Z]/', '', $_GET['h'] ?? '');
if (!$hdn) { http_response_code(400); exit; }

$cacheFile = sys_get_temp_dir() . '/arremate_foto_' . $hdn . '.jpg';
$cacheTTL  = 86400;

/* Serve do cache se ainda válido */
if (file_exists($cacheFile) && (time() - filemtime($cacheFile)) < $cacheTTL) {
    header('Content-Type: image/jpeg');
    header('Cache-Control: public, max-age=86400');
    readfile($cacheFile);
    exit;
}

/* Busca foto_url no banco */
$dbPath = getenv('DB_PATH') ?: '/var/www/dados/imoveis.db';
try {
    $db   = new PDO('sqlite:' . $dbPath);
    $stmt = $db->prepare('SELECT foto_url, hdnimovel, status_caixa FROM imoveis WHERE hdnimovel = :h OR numero = :h LIMIT 1');
    $stmt->execute([':h' => $hdn]);
    $row  = $stmt->fetch(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    http_response_code(500); exit;
}

if (!$row || empty($row['foto_url'])) {
    http_response_code(404); exit;
}

// Rejeita imóveis encerrados ou removidos (espelha comportamento da CAIXA)
if ($row['status_caixa'] === 'encerrado' || $row['status_caixa'] === 'removido') {
    http_response_code(404); exit;
}

$url = $row['foto_url'];

/* Busca da CAIXA */
$ch = curl_init($url);
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

if (!$data || $info['http_code'] !== 200 || $info['size_download'] < 500) {
    http_response_code(404); exit;
}

file_put_contents($cacheFile, $data);

header('Content-Type: image/jpeg');
header('Cache-Control: public, max-age=86400');
echo $data;
