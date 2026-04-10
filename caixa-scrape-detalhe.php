<?php
/**
 * caixa-scrape-detalhe.php — Enriquecimento sob demanda
 * 
 * Chamado via AJAX quando o usuário acessa a página do imóvel.
 * Faz scraping da Caixa UMA VEZ e cacheia no banco.
 * 
 * Regras de FGTS (da página da Caixa):
 *   "Permite utilização de FGTS" → fgts=1
 *   "Permite financiamento - somente SBPE" → financiamento=1, fgts=1 (SBPE permite FGTS)
 *   "Permite financiamento" (sem SBPE e sem FGTS) → financiamento=1, fgts=0
 *
 * Anti-bot: Limita 1 request por 3 segundos no servidor.
 */

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');

$hdn = preg_replace('/[^0-9]/', '', $_GET['hdnimovel'] ?? '');
if (!$hdn) {
    echo json_encode(['erro' => 'hdnimovel obrigatório']);
    exit;
}

define('DB_PATH', __DIR__ . '/../dados/imoveis.db');
$LOCK_FILE = sys_get_temp_dir() . '/arremate_scrape.lock';

/* ── Verificar se já foi scraped ── */
try {
    $db = new PDO('sqlite:' . DB_PATH);
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    $stmt = $db->prepare('SELECT scraped_at, fgts, financiamento, condominio, iptu, foto_url, data_encerramento, descricao FROM imoveis WHERE hdnimovel = :h LIMIT 1');
    $stmt->execute([':h' => $hdn]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$row) {
        echo json_encode(['erro' => 'Imóvel não encontrado no banco']);
        exit;
    }

    // Garante UTF-8 nos campos vindos do banco (CSV pode ter sido importado em ISO-8859-1)
    function toUtf8Scraper($s) {
        if (empty($s)) return '';
        return mb_check_encoding($s, 'UTF-8') ? $s : mb_convert_encoding($s, 'UTF-8', 'ISO-8859-1');
    }

    // Extrair áreas da descrição cacheada para incluir na resposta
    function parseCachedAreas($desc) {
        $a = ['privativa' => 0.0, 'total' => 0.0, 'terreno' => 0.0];
        if (empty($desc)) return $a;
        // Formato "45,16 de área privativa" ou "45,16M2 DE AREA PRIVATIVA"
        if (preg_match('/([\d]+[.,]?[\d]*)\s*(?:m[²2])?\s*de\s*[aá]rea\s*privativa/iu', $desc, $m))
            $a['privativa'] = (float) str_replace(',', '.', $m[1]);
        if (preg_match('/([\d]+[.,]?[\d]*)\s*(?:m[²2])?\s*de\s*[aá]rea\s*total/iu', $desc, $m))
            $a['total'] = (float) str_replace(',', '.', $m[1]);
        if (preg_match('/([\d]+[.,]?[\d]*)\s*(?:m[²2])?\s*de\s*[aá]rea\s*do\s*terreno/iu', $desc, $m))
            $a['terreno'] = (float) str_replace(',', '.', $m[1]);
        // Formato "Área Privativa: 45,16" (saída do scraper)
        if (!$a['privativa'] && preg_match('/[aá]rea\s*privativa[:\s]+([\d]+[.,]?[\d]*)/iu', $desc, $m))
            $a['privativa'] = (float) str_replace(',', '.', $m[1]);
        if (!$a['total'] && preg_match('/[aá]rea\s*total[:\s]+([\d]+[.,]?[\d]*)/iu', $desc, $m))
            $a['total'] = (float) str_replace(',', '.', $m[1]);
        if (!$a['terreno'] && preg_match('/[aá]rea\s*(?:do\s*)?terreno[:\s]+([\d]+[.,]?[\d]*)/iu', $desc, $m))
            $a['terreno'] = (float) str_replace(',', '.', $m[1]);
        return $a;
    }

    // Já scraped com sucesso? Retornar dados cacheados completos
    if ($row['scraped_at'] && strpos($row['scraped_at'], 'ERR') === false) {
        $desc_cache = toUtf8Scraper($row['descricao'] ?? '');
        $cached_areas = parseCachedAreas($desc_cache);
        echo json_encode([
            'sucesso'           => true,
            'cache'             => true,
            'fgts'              => (int)$row['fgts'],
            'financiamento'     => (int)$row['financiamento'],
            'condominio'        => toUtf8Scraper($row['condominio'] ?? ''),
            'iptu'              => $row['iptu'],
            'foto_url'          => $row['foto_url'],
            'data_encerramento' => $row['data_encerramento'] ?? '',
            'descricao'         => $desc_cache,
            'area_privativa'    => $cached_areas['privativa'],
            'area_total'        => $cached_areas['total'],
            'area_terreno'      => $cached_areas['terreno'],
        ]);
        exit;
    }
} catch (Exception $e) {
    echo json_encode(['erro' => 'Erro no banco: ' . $e->getMessage()]);
    exit;
}

// Resposta parcial com dados do banco (sem scraping ou com Radware bloqueado)
function dbFallbackResponse(array $row, string $extra_key): string {
    $desc = toUtf8Scraper($row['descricao'] ?? '');
    $areas = parseCachedAreas($desc);
    return json_encode([
        'sucesso'           => true,
        'cache'             => false,
        $extra_key          => true,
        'fgts'              => (int)$row['fgts'],
        'financiamento'     => (int)$row['financiamento'],
        'condominio'        => toUtf8Scraper($row['condominio'] ?? ''),
        'iptu'              => $row['iptu'],
        'foto_url'          => $row['foto_url'],
        'data_encerramento' => $row['data_encerramento'] ?? '',
        'descricao'         => $desc,
        'area_privativa'    => $areas['privativa'],
        'area_total'        => $areas['total'],
        'area_terreno'      => $areas['terreno'],
    ]);
}

/* ── Rate limiting: máximo 1 scrape a cada 3 segundos ── */
if (file_exists($LOCK_FILE) && (time() - filemtime($LOCK_FILE)) < 3) {
    echo dbFallbackResponse($row, 'pending');
    exit;
}
@touch($LOCK_FILE);

/* ── Scraping via curl ── */
$url = "https://venda-imoveis.caixa.gov.br/sistema/detalhe-imovel.asp?hdnimovel=" . $hdn;

$ch = curl_init($url);
curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_FOLLOWLOCATION => true,
    CURLOPT_MAXREDIRS      => 5,
    CURLOPT_TIMEOUT        => 12,
    CURLOPT_USERAGENT      => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36',
    CURLOPT_SSL_VERIFYPEER => false,
    CURLOPT_SSL_VERIFYHOST => 0,
    CURLOPT_ENCODING       => '',
    CURLOPT_HTTPHEADER     => [
        'Accept: text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',
        'Accept-Language: pt-BR,pt;q=0.9,en-US;q=0.8,en;q=0.7',
        'Referer: https://venda-imoveis.caixa.gov.br/sistema/busca-imovel.asp',
    ],
]);
$html = curl_exec($ch);
$httpCode = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

/* ── Verificar se veio CAPTCHA/bloqueio (Radware, hCaptcha, etc.) ── */
$html_lower = strtolower(substr($html, 0, 2000)); // só checar início
if ($httpCode !== 200 || strlen($html) < 1000 || strpos($html_lower, 'bot manager') !== false || strpos($html_lower, 'captcha') !== false || strpos($html_lower, 'radware') !== false) {
    echo dbFallbackResponse($row, 'blocked');
    exit;
}

/* ── Parse do HTML ── */
$fgts = 0;
$financiamento = 0;
$condominio = '';
$iptu = '';
$foto_url = '';
$edital_url = '';
$matricula_url = '';

// FGTS: "Permite utilização de FGTS" (texto exato da Caixa)
if (strpos($html, 'FGTS') !== false && strpos($html, 'tiliza') !== false) {
    $fgts = 1; // "Permite utilização de FGTS"
}

// Financiamento: "Permite financiamento"
if (strpos($html, 'Permite financiamento') !== false) {
    $financiamento = 1;
    // Se tem "somente SBPE", SBPE permite uso de FGTS como entrada
    if (strpos($html, 'somente SBPE') !== false) {
        $fgts = 1;
    }
}

// Condomínio
$hl = mb_strtolower($html);
if (strpos($hl, 'condom') !== false) {
    if (strpos($hl, 'limite de 10%') !== false) {
        $condominio = 'limitada';
    } elseif (strpos($hl, 'responsabilidade do comprador') !== false) {
        $condominio = 'comprador';
    }
}

// Tributos/IPTU
if (preg_match('/Tributos:\s*Sob responsabilidade d[oa]\s*(comprador|arrematante|CAIXA)/i', $html, $m)) {
    $iptu = (strtolower($m[1]) === 'caixa') ? 'caixa' : 'comprador';
}

// Foto
if (preg_match('/src=[\'"][^"\']*?(\/fotos\/F[^"\']+\.jpg)[\'"]/', $html, $m)) {
    $foto_url = 'https://venda-imoveis.caixa.gov.br' . $m[1];
}

// Edital PDF (pattern: editais/EL00170226CPARE.PDF)
if (preg_match('/editais\/E[^"\'<\s]+\.PDF/i', $html, $m)) {
    $edital_url = 'https://venda-imoveis.caixa.gov.br/' . $m[0];
}

// Matrícula PDF (pattern: editais/matricula/UF/hdnimovel.pdf)
if (preg_match('/editais\/matricula\/[A-Z]{2}\/\d+\.pdf/i', $html, $m)) {
    $matricula_url = 'https://venda-imoveis.caixa.gov.br/' . $m[0];
}

// Data e horário do leilão/encerramento
// A Caixa usa vários formatos: "06/04/2026 às 10:00", "06/04/2026 as 10:00",
// "Encerramento: 06/04/2026", "1º Leilão: 06/04/2026 às 10:00", etc.
$data_encerramento = '';
$html_decoded = html_entity_decode($html, ENT_QUOTES | ENT_HTML5, 'UTF-8');

// 1) DD/MM/YYYY HH:MM (com "às", "as", ou separador)
if (preg_match('/(\d{2})\/(\d{2})\/(\d{4})\s+[àa]s\s+(\d{2}:\d{2})/iu', $html_decoded, $m)) {
    $data_encerramento = $m[3] . '-' . $m[2] . '-' . $m[1] . ' ' . $m[4];
// 2) Encerramento/Data/Leilão seguido de DD/MM/YYYY HH:MM (sem "às")
} elseif (preg_match('/(?:Encerramento|Prazo|1[ºo°]\s*Leil[aã]o|Data)[^\d]*(\d{2})\/(\d{2})\/(\d{4})(?:\s+(\d{2}:\d{2}))?/i', $html_decoded, $m)) {
    $data_encerramento = $m[3] . '-' . $m[2] . '-' . $m[1];
    if (!empty($m[4])) $data_encerramento .= ' ' . $m[4];
// 3) Qualquer DD/MM/YYYY isolado (fallback menos preciso)
} elseif (preg_match('/(\d{2})\/(\d{2})\/(20\d{2})/', $html_decoded, $m)) {
    $data_encerramento = $m[3] . '-' . $m[2] . '-' . $m[1];
}

// Áreas individuais extraídas do HTML
$area_privativa = 0.0;
$area_total     = 0.0;
$area_terreno   = 0.0;

// Padrão da Caixa: "Área Privativa: 45,16 m²" em células de tabela
if (preg_match('/[aá]rea\s*privativa[:\s]+([\d]+[.,]?[\d]*)\s*m/iu', $html_decoded, $m))
    $area_privativa = (float) str_replace(',', '.', $m[1]);
if (preg_match('/[aá]rea\s*total[:\s]+([\d]+[.,]?[\d]*)\s*m/iu', $html_decoded, $m))
    $area_total = (float) str_replace(',', '.', $m[1]);
if (preg_match('/[aá]rea\s*(?:do\s*)?terreno[:\s]+([\d]+[.,]?[\d]*)\s*m/iu', $html_decoded, $m))
    $area_terreno = (float) str_replace(',', '.', $m[1]);

// Descrição legível para armazenar no banco (usada como fallback pelo JS)
$descricao_nova = '';

// Montar descrição a partir das áreas encontradas
$partes = [];
if ($area_privativa > 0) $partes[] = number_format($area_privativa, 2, ',', '.') . ' de área privativa';
if ($area_total > 0)     $partes[] = number_format($area_total, 2, ',', '.') . ' de área total';
if ($area_terreno > 0)   $partes[] = number_format($area_terreno, 2, ',', '.') . ' de área do terreno';
if ($partes) $descricao_nova = implode(' | ', $partes);

// Fallback: extrair texto de célula de tabela com m²
if (empty($descricao_nova)) {
    if (preg_match('/([^<]{20,300}(?:\d+[.,]\d+|\d+)\s*m²[^<]{0,100})/iu', $html_decoded, $m)) {
        $descricao_nova = strip_tags(trim($m[1]));
    }
}

// Fallback final: qualquer célula descritiva longa
if (empty($descricao_nova)) {
    if (preg_match('/<td[^>]*>([^<]{50,300})<\/td>/i', $html, $m)) {
        $t = strip_tags(html_entity_decode(trim($m[1])));
        if (strlen($t) > 20) $descricao_nova = $t;
    }
}

/* ── Atualizar banco ── */
try {
    $upd = $db->prepare("UPDATE imoveis SET fgts=:fg, financiamento=:fi, condominio=:co, iptu=:ip, foto_url=:fo, data_encerramento=:de, descricao=:desc, scraped_at=:ts WHERE hdnimovel=:h");
    $upd->execute([
        ':fg' => $fgts,
        ':fi' => $financiamento,
        ':co' => $condominio,
        ':ip' => $iptu,
        ':fo' => $foto_url,
        ':de' => $data_encerramento,
        ':desc' => $descricao_nova,
        ':ts' => date('Y-m-d H:i:s'),
        ':h'  => $hdn,
    ]);
} catch (Exception $e) {
    // silêncio — dados ainda são retornados
}

echo json_encode([
    'sucesso'           => true,
    'cache'             => false,
    'fgts'              => $fgts,
    'financiamento'     => $financiamento,
    'condominio'        => $condominio,
    'iptu'              => $iptu,
    'foto_url'          => $foto_url,
    'edital_url'        => $edital_url,
    'matricula_url'     => $matricula_url,
    'data_encerramento' => $data_encerramento,
    'descricao'         => $descricao_nova,
    'area_privativa'    => $area_privativa,
    'area_total'        => $area_total,
    'area_terreno'      => $area_terreno,
]);
