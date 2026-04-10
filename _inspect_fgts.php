<?php
/**
 * Inspeciona HTML da CAIXA para entender os padrões de FGTS/financiamento
 * Testa imóveis com financiamento=1 e financiamento=0 no banco
 */
$db = new PDO('sqlite:/var/www/dados/imoveis.db');

// Pegar 3 com financiamento=1 e 2 com financiamento=0 (ainda sem scraped_at)
$com    = $db->query("SELECT hdnimovel, uf, descricao FROM imoveis WHERE financiamento=1 AND (scraped_at IS NULL OR scraped_at='') LIMIT 3")->fetchAll(PDO::FETCH_ASSOC);
$sem    = $db->query("SELECT hdnimovel, uf, descricao FROM imoveis WHERE financiamento=0 AND (scraped_at IS NULL OR scraped_at='') LIMIT 2")->fetchAll(PDO::FETCH_ASSOC);

function fetchCaixa($hdn) {
    $url = "https://venda-imoveis.caixa.gov.br/sistema/detalhe-imovel.asp?hdnimovel=$hdn";
    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_TIMEOUT        => 15,
        CURLOPT_USERAGENT      => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 Chrome/124.0.0.0 Safari/537.36',
        CURLOPT_SSL_VERIFYPEER => false,
        CURLOPT_ENCODING       => '',
        CURLOPT_HTTPHEADER     => ['Accept-Language: pt-BR,pt;q=0.9'],
    ]);
    $html = curl_exec($ch);
    $code = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    return ['html' => $html, 'code' => $code];
}

function extrairContexto($html, $palavras, $janela = 200) {
    $html_decoded = html_entity_decode($html, ENT_QUOTES, 'UTF-8');
    $resultados = [];
    foreach ($palavras as $p) {
        $pos = stripos($html_decoded, $p);
        if ($pos !== false) {
            $inicio = max(0, $pos - 80);
            $trecho = substr($html_decoded, $inicio, $janela);
            $resultados[$p] = strip_tags($trecho);
        } else {
            $resultados[$p] = '*** NÃO ENCONTRADO ***';
        }
    }
    return $resultados;
}

$palavras = ['FGTS', 'Permite utiliza', 'Permite financiamento', 'somente SBPE', 'SBPE', 'Financiamento', 'financiamento', 'Condição de venda', 'condi'];

echo "============================================================\n";
echo "IMÓVEIS COM financiamento=1 NO CSV\n";
echo "============================================================\n";
foreach ($com as $im) {
    echo "\nHDN={$im['hdnimovel']} UF={$im['uf']}\n";
    echo "Desc: " . substr($im['descricao'], 0, 60) . "\n";
    sleep(2); // respeitar rate limit
    $r = fetchCaixa($im['hdnimovel']);
    echo "HTTP: {$r['code']} | Size: " . strlen($r['html']) . "\n";
    if ($r['code'] === 200 && strlen($r['html']) > 2000) {
        $ctx = extrairContexto($r['html'], $palavras);
        foreach ($ctx as $p => $t) {
            if ($t !== '*** NÃO ENCONTRADO ***') echo "  [$p]: " . trim($t) . "\n";
        }
        // Mostrar bloco completo de condições de venda
        if (preg_match('/<[^>]*class[^>]*condi[^>]*>(.*?)<\/[^>]+>/is', $r['html'], $m)) {
            echo "  BLOCO CONDICOES: " . strip_tags($m[1]) . "\n";
        }
        // Buscar por padrão de checkbox/lista de condições
        preg_match_all('/<li[^>]*>([^<]*(?:FGTS|financiamento|SBPE|Permite)[^<]*)<\/li>/i', $r['html'], $ms);
        if ($ms[1]) echo "  LIs com condicoes: " . implode(' | ', array_map('strip_tags', $ms[1])) . "\n";
    } else {
        echo "  BLOQUEADO OU ERRO\n";
    }
}

echo "\n============================================================\n";
echo "IMÓVEIS COM financiamento=0 NO CSV\n";
echo "============================================================\n";
foreach ($sem as $im) {
    echo "\nHDN={$im['hdnimovel']} UF={$im['uf']}\n";
    echo "Desc: " . substr($im['descricao'], 0, 60) . "\n";
    sleep(2);
    $r = fetchCaixa($im['hdnimovel']);
    echo "HTTP: {$r['code']} | Size: " . strlen($r['html']) . "\n";
    if ($r['code'] === 200 && strlen($r['html']) > 2000) {
        $ctx = extrairContexto($r['html'], $palavras);
        foreach ($ctx as $p => $t) {
            if ($t !== '*** NÃO ENCONTRADO ***') echo "  [$p]: " . trim($t) . "\n";
        }
        preg_match_all('/<li[^>]*>([^<]*(?:FGTS|financiamento|SBPE|Permite)[^<]*)<\/li>/i', $r['html'], $ms);
        if ($ms[1]) echo "  LIs com condicoes: " . implode(' | ', array_map('strip_tags', $ms[1])) . "\n";
    } else {
        echo "  BLOQUEADO OU ERRO\n";
    }
}
