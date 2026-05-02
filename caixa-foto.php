<?php
/**
 * caixa-foto.php — Proxy de imagens da Caixa
 * 
 * Padrão REAL da Caixa (descoberto via scraping):
 *   URL: /fotos/F{numero_formatado_14_digitos}{sufixo}.jpg
 *   Exemplo: hdnimovel=10206716 → F000001020671621.jpg
 *
 * Também consulta foto_url do banco se disponível (scraping prévio).
 */

$hdn = preg_replace('/[^0-9]/', '', $_GET['hdnimovel'] ?? '');
if (!$hdn) { http_response_code(404); exit; }

$cacheDir = sys_get_temp_dir() . '/arremate-fotos';
if (!is_dir($cacheDir)) @mkdir($cacheDir, 0755, true);

$cachePath = $cacheDir . '/' . $hdn . '.jpg';

/* ── Se cache local existe e é recente (<24h), servir direto ── */
if (file_exists($cachePath) && (time() - filemtime($cachePath)) < 86400) {
    header('Content-Type: image/jpeg');
    header('Cache-Control: public, max-age=86400');
    readfile($cachePath);
    exit;
}

$ctx = stream_context_create([
    'http' => [
        'timeout' => 8,
        'user_agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36',
        'follow_location' => 1,
    ],
    'ssl' => ['verify_peer' => false, 'verify_peer_name' => false]
]);

$img = null;
$foundUrl = '';

/* ── 1. Tentar foto_url do banco (mais confiável) ── */
$dbPath = __DIR__ . '/../dados/imoveis.db';
if (file_exists($dbPath)) {
    try {
        $db = new PDO('sqlite:' . $dbPath);
        $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $stmt = $db->prepare('SELECT foto_url, status_caixa FROM imoveis WHERE hdnimovel = :h LIMIT 1');
        $stmt->execute([':h' => $hdn]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        // Rejeita imóveis encerrados ou removidos (espelha comportamento da CAIXA)
        if ($row && ($row['status_caixa'] === 'encerrado' || $row['status_caixa'] === 'removido')) {
            http_response_code(404);
            exit;
        }

        $fotoUrl = $row['foto_url'] ?? null;
        if ($fotoUrl) {
            $img = @file_get_contents($fotoUrl, false, $ctx);
            if ($img && strlen($img) > 1000) {
                $foundUrl = $fotoUrl;
            } else {
                $img = null;
            }
        }
    } catch (Exception $e) {
        // silêncio
    }
}

/* ── 2. Tentar padrões conhecidos ── */
if (!$img) {
    $numFmt = str_pad($hdn, 14, '0', STR_PAD_LEFT);
    
    $tentativas = [
        "https://venda-imoveis.caixa.gov.br/fotos/F{$numFmt}21.jpg",
        "https://venda-imoveis.caixa.gov.br/fotos/F{$numFmt}01.jpg",
        "https://venda-imoveis.caixa.gov.br/fotos/F{$numFmt}00.jpg",
        "https://venda-imoveis.caixa.gov.br/fotos/F{$hdn}21.jpg",
        "https://venda-imoveis.caixa.gov.br/fotos/F{$hdn}01.jpg",
        "https://venda-imoveis.caixa.gov.br/fotos/F{$hdn}00.jpg",
    ];
    
    foreach ($tentativas as $url) {
        $img = @file_get_contents($url, false, $ctx);
        if ($img && strlen($img) > 1000) {
            $foundUrl = $url;
            break;
        }
        $img = null;
    }
}

/* ── 3. Scraping direto da página de detalhe como último recurso ── */
if (!$img) {
    $detUrl = "https://venda-imoveis.caixa.gov.br/sistema/detalhe-imovel.asp?hdnimovel={$hdn}";
    $html = @file_get_contents($detUrl, false, $ctx);
    if ($html && preg_match('/src=[\'"]([^"\']*\/fotos\/F[^"\']+\.jpg)[\'"]/', $html, $m)) {
        $fotoPath = $m[1];
        if (strpos($fotoPath, 'http') !== 0) {
            $fotoPath = 'https://venda-imoveis.caixa.gov.br' . $fotoPath;
        }
        $img = @file_get_contents($fotoPath, false, $ctx);
        if ($img && strlen($img) > 1000) {
            $foundUrl = $fotoPath;
            
            // Atualizar banco com foto encontrada
            if (file_exists($dbPath)) {
                try {
                    $db2 = new PDO('sqlite:' . $dbPath);
                    $db2->exec("UPDATE imoveis SET foto_url = " . $db2->quote($foundUrl) . " WHERE hdnimovel = " . $db2->quote($hdn));
                } catch (Exception $e) {}
            }
        } else {
            $img = null;
        }
    }
}

if (!$img) {
    http_response_code(404);
    exit;
}

/* ── Cachear e servir ── */
@file_put_contents($cachePath, $img);

header('Content-Type: image/jpeg');
header('Cache-Control: public, max-age=86400');
header('X-Foto-Source: ' . $foundUrl);
echo $img;
