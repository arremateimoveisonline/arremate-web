<?php
/**
 * caixa-scrape-detalhe.php — Enriquecimento sob demanda
 *
 * Chamado via AJAX quando o usuário acessa a página do imóvel.
 * Faz scraping da Caixa UMA VEZ e cacheia no banco.
 *
 * REGRAS (v5 — totalmente independentes):
 *   FGTS:           SOMENTE a frase "Permite utilização de FGTS" → fgts=1
 *   Financiamento:  "Permite financiamento" (qualquer variante) → financiamento=1
 *   Condomínio:     Frase exata "Sob responsabilidade do comprador, até o limite de 10%..."
 *                   → caixa_paga_excedente=1 (CAIXA paga o que exceder 10% da avaliação)
 *
 *   FGTS e Financiamento são INDEPENDENTES — nunca um deriva do outro.
 *
 * Datas:  Captura TODAS as DD/MM/YYYY HH:MM, valida e retorna a ÚLTIMA (encerramento real).
 *         Formato gravado: YYYY-MM-DD HH:MM:SS (timezone America/Sao_Paulo).
 *
 * Anti-bot: Limita 1 request por 3 segundos no servidor.
 */

date_default_timezone_set('America/Sao_Paulo');
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
    
    $stmt = $db->prepare('SELECT scraped_at, fgts, financiamento, condominio, caixa_paga_excedente, iptu, foto_url, data_leilao_1, data_encerramento, descricao, status_caixa FROM imoveis WHERE hdnimovel = :h LIMIT 1');
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
        // [aáý] cobre UTF-8 correto (á), ASCII (a) e corrupção ISO-8859-1 (ý = byte 0xFD lido como á)
        if (preg_match('/([\d]+[.,]?[\d]*)\s*(?:m[²2])?\s*de\s*[aáý]rea\s*privativa/iu', $desc, $m))
            $a['privativa'] = (float) str_replace(',', '.', $m[1]);
        if (preg_match('/([\d]+[.,]?[\d]*)\s*(?:m[²2])?\s*de\s*[aáý]rea\s*total/iu', $desc, $m))
            $a['total'] = (float) str_replace(',', '.', $m[1]);
        if (preg_match('/([\d]+[.,]?[\d]*)\s*(?:m[²2])?\s*de\s*[aáý]rea\s*do\s*terreno/iu', $desc, $m))
            $a['terreno'] = (float) str_replace(',', '.', $m[1]);
        // Formato "Área Privativa: 45,16" (saída do scraper)
        if (!$a['privativa'] && preg_match('/[aáý]rea\s*privativa[:\s]+([\d]+[.,]?[\d]*)/iu', $desc, $m))
            $a['privativa'] = (float) str_replace(',', '.', $m[1]);
        if (!$a['total'] && preg_match('/[aáý]rea\s*total[:\s]+([\d]+[.,]?[\d]*)/iu', $desc, $m))
            $a['total'] = (float) str_replace(',', '.', $m[1]);
        if (!$a['terreno'] && preg_match('/[aáý]rea\s*(?:do\s*)?terreno[:\s]+([\d]+[.,]?[\d]*)/iu', $desc, $m))
            $a['terreno'] = (float) str_replace(',', '.', $m[1]);
        return $a;
    }

    // Já scraped com sucesso? Retornar dados cacheados completos
    if ($row['scraped_at'] && strpos($row['scraped_at'], 'ERR') === false) {
        $desc_cache = toUtf8Scraper($row['descricao'] ?? '');
        $cached_areas = parseCachedAreas($desc_cache);
        echo json_encode([
            'sucesso'              => true,
            'cache'                => true,
            'fgts'                 => (int)$row['fgts'],
            'financiamento'        => (int)$row['financiamento'],
            'condominio'           => toUtf8Scraper($row['condominio'] ?? ''),
            'caixa_paga_excedente' => (int)($row['caixa_paga_excedente'] ?? 0),
            'iptu'                 => $row['iptu'],
            'foto_url'             => $row['foto_url'],
            'data_leilao_1'        => $row['data_leilao_1'] ?? '',
            'data_encerramento'    => $row['data_encerramento'] ?? '',
            'status_caixa'         => $row['status_caixa'] ?? '',
            'edital_url'           => $row['edital_url'] ?? '',
            'descricao'            => $desc_cache,
            'area_privativa'       => $cached_areas['privativa'],
            'area_total'           => $cached_areas['total'],
            'area_terreno'         => $cached_areas['terreno'],
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
        'sucesso'              => true,
        'cache'                => false,
        $extra_key             => true,
        'fgts'                 => (int)$row['fgts'],
        'financiamento'        => (int)$row['financiamento'],
        'condominio'           => toUtf8Scraper($row['condominio'] ?? ''),
        'caixa_paga_excedente' => (int)($row['caixa_paga_excedente'] ?? 0),
        'iptu'                 => $row['iptu'],
        'foto_url'             => $row['foto_url'],
        'data_leilao_1'        => $row['data_leilao_1'] ?? '',
        'data_encerramento'    => $row['data_encerramento'] ?? '',
        'status_caixa'         => $row['status_caixa'] ?? '',
        'edital_url'           => $row['edital_url'] ?? '',
        'descricao'            => $desc,
        'area_privativa'       => $areas['privativa'],
        'area_total'           => $areas['total'],
        'area_terreno'         => $areas['terreno'],
    ]);
}

/**
 * Extrai as datas de leilão/encerramento da página da Caixa.
 * Retorna: ['data_leilao_1' => 'YYYY-MM-DD HH:MM:SS', 'data_encerramento' => 'YYYY-MM-DD HH:MM:SS']
 *
 * Regras:
 *   SFI (2 leilões): data_leilao_1 = 1º, data_encerramento = 2º
 *   Licitação/Venda Online (1 data): data_leilao_1 = '', data_encerramento = única
 *   Formato: YYYY-MM-DD HH:MM:SS (timezone America/Sao_Paulo, sem DST desde 2019)
 */
function extrairDatasLeilao(string $htmlDec): array {
    $d1 = '';
    $d2 = '';

    // Formato CAIXA: "DD/MM/YYYY - HHhMM" ou "DD/MM/YYYY às HH:MM"
    $RE_SEP = '(?:\s*[-–]\s*|\s+(?:[àa]s\s+)?)';

    // 1º Leilão explícito (SFI)
    if (preg_match('/(?:Data\s+d[oa]\s+)?1[ºo°]?\s*Leil[aã]o[^\d]{0,80}(\d{2})\/(\d{2})\/(\d{4})(?:\s*[-–]\s*|\s+(?:[àa]s\s+)?)(\d{2})[h:](\d{2})/iu', $htmlDec, $m)) {
        $d1 = validarData($m[3], $m[2], $m[1], $m[4], $m[5]);
    }
    // 2º Leilão explícito (SFI)
    if (preg_match('/(?:Data\s+d[oa]\s+)?2[ºo°]?\s*Leil[aã]o[^\d]{0,80}(\d{2})\/(\d{2})\/(\d{4})(?:\s*[-–]\s*|\s+(?:[àa]s\s+)?)(\d{2})[h:](\d{2})/iu', $htmlDec, $m)) {
        $d2 = validarData($m[3], $m[2], $m[1], $m[4], $m[5]);
    }

    // Se não achou "Nº Leilão" explícito, captura todas DD/MM/YYYY com hora e ordena
    if (!$d1 && !$d2) {
        preg_match_all('/(\d{2})\/(\d{2})\/(\d{4})(?:\s*[-–]\s*|\s+(?:[àa]s\s+)?)(\d{2})[h:](\d{2})/iu', $htmlDec, $all, PREG_SET_ORDER);
        $datas = [];
        foreach ($all as $mm) {
            $v = validarData($mm[3], $mm[2], $mm[1], $mm[4], $mm[5]);
            if ($v) $datas[] = $v;
        }
        $datas = array_values(array_unique($datas));
        sort($datas);
        if (count($datas) >= 2) {
            $d1 = $datas[0];
            $d2 = end($datas);
        } elseif (count($datas) === 1) {
            $d2 = $datas[0]; // única = encerramento
        }
    }

    // Fallback: apenas DD/MM/YYYY sem hora
    if (!$d1 && !$d2) {
        if (preg_match('/(?:Encerramento|Prazo|Data)[^\d]*(\d{2})\/(\d{2})\/(20\d{2})/i', $htmlDec, $m)) {
            $d2 = validarData($m[3], $m[2], $m[1], '23', '59');
        } elseif (preg_match('/(\d{2})\/(\d{2})\/(20\d{2})/', $htmlDec, $m)) {
            $d2 = validarData($m[3], $m[2], $m[1], '23', '59');
        }
    }

    // Se só tem um achado (d1 sem d2), promove para encerramento
    if ($d1 && !$d2) { $d2 = $d1; $d1 = ''; }

    return ['data_leilao_1' => $d1, 'data_encerramento' => $d2];
}

function validarData(string $Y, string $M, string $D, string $h, string $min): string {
    $iso = sprintf('%04d-%02d-%02d %02d:%02d:00', (int)$Y, (int)$M, (int)$D, (int)$h, (int)$min);
    $dt  = DateTime::createFromFormat('Y-m-d H:i:s', $iso, new DateTimeZone('America/Sao_Paulo'));
    if (!$dt) return '';
    if ($dt->format('Y-m-d H:i:s') !== $iso) return ''; // rejeita 31/02 etc
    return $iso;
}

/**
 * Detecta se a CAIXA paga o excedente de 10% do condomínio.
 * Procura a frase exata na página (com normalização de espaços).
 */
function detectarCaixaPagaExcedente(string $html): int {
    $htmlDec   = html_entity_decode($html, ENT_QUOTES | ENT_HTML5, 'UTF-8');
    $htmlClean = preg_replace('/\s+/', ' ', strip_tags($htmlDec));
    // Frase de referência (match em partes-chave para tolerar variações de pontuação/acentos)
    $p1 = stripos($htmlClean, 'Sob responsabilidade do comprador') !== false;
    $p2 = stripos($htmlClean, 'limite de 10%') !== false;
    $p3 = stripos($htmlClean, 'CAIXA realizar') !== false && stripos($htmlClean, 'exceder') !== false;
    return ($p1 && $p2 && $p3) ? 1 : 0;
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
$fgts                 = 0;
$financiamento        = 0;
$condominio           = '';
$caixa_paga_excedente = 0;
$iptu                 = '';
$foto_url             = '';
$edital_url           = '';
$matricula_url        = '';

// FGTS (INDEPENDENTE): somente a frase "Permite utilização de FGTS"
// NUNCA derivar FGTS de financiamento/SBPE — são campos independentes.
if (stripos($html, 'Permite utiliza') !== false && stripos($html, 'FGTS') !== false) {
    $fgts = 1;
}

// Financiamento (INDEPENDENTE): "Permite financiamento" em qualquer variante
if (stripos($html, 'Permite financiamento') !== false) {
    $financiamento = 1;
}

// Condomínio — frase exata da CAIXA assume o excedente de 10%
$caixa_paga_excedente = detectarCaixaPagaExcedente($html);
if ($caixa_paga_excedente === 1) {
    $condominio = 'limitada'; // retrocompatibilidade com filtros antigos
} elseif (stripos($html, 'responsabilidade do comprador') !== false && stripos($html, 'condom') !== false) {
    $condominio = 'comprador';
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

// Status da CAIXA — mensagens especiais exibidas na página
$html_decoded = html_entity_decode($html, ENT_QUOTES | ENT_HTML5, 'UTF-8');
$html_txt     = preg_replace('/\s+/', ' ', strip_tags($html_decoded));
$status_caixa = '';
if (preg_match('/Venda\s+online\s+encerrada\s+em\s+([\d]{2}\/[\d]{2}\/[\d]{4}\s+[\d]{2}:[\d]{2}:[\d]{2})/i', $html_txt, $mst)) {
    $status_caixa = 'encerrada:' . $mst[1];
} elseif (
    preg_match('/n[aã]o\s+est[aá]\s+mais\s+dispon[ií]vel\s+para\s+venda/i', $html_txt) ||
    preg_match('/im[oó]vel\s+que\s+voc[eê]\s+procura\s+n[aã]o\s+est[aá]/i', $html_txt) ||
    preg_match('/ocorreu\s+um\s+erro\s+ao\s+tentar\s+recuperar\s+os\s+dados/i', $html_txt)
) {
    $status_caixa = 'removido';
}

// Data e horário do leilão/encerramento (formato: YYYY-MM-DD HH:MM:SS)
// Captura 1º Leilão + 2º Leilão separadamente quando SFI; data única para Licitação/Venda Online
$datas = extrairDatasLeilao($html_decoded);
$data_leilao_1     = $datas['data_leilao_1'];
$data_encerramento = $datas['data_encerramento'];

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
    $upd = $db->prepare(
        "UPDATE imoveis SET
            fgts = :fg,
            financiamento = :fi,
            condominio = :co,
            caixa_paga_excedente = :cpe,
            iptu = :ip,
            foto_url = :fo,
            data_leilao_1 = :d1,
            data_encerramento = :de,
            status_caixa = :sc,
            descricao = :desc,
            area_privativa = :ap,
            area_total = :at,
            area_terreno = :ater,
            edital_url = :eu,
            scraped_at = :ts
         WHERE hdnimovel = :h"
    );
    $upd->execute([
        ':fg'   => $fgts,
        ':fi'   => $financiamento,
        ':co'   => $condominio,
        ':cpe'  => $caixa_paga_excedente,
        ':ip'   => $iptu,
        ':fo'   => $foto_url,
        ':d1'   => $data_leilao_1,
        ':de'   => $data_encerramento,
        ':sc'   => $status_caixa,
        ':desc' => $descricao_nova,
        ':ap'   => $area_privativa,
        ':at'   => $area_total,
        ':ater' => $area_terreno,
        ':eu'   => $edital_url,
        ':ts'   => date('Y-m-d H:i:s'),
        ':h'    => $hdn,
    ]);
} catch (Exception $e) {
    // Log silencioso — dados ainda são retornados ao cliente
    @error_log('[caixa-scrape-detalhe] UPDATE falhou para hdn=' . $hdn . ': ' . $e->getMessage());
}

echo json_encode([
    'sucesso'              => true,
    'cache'                => false,
    'fgts'                 => $fgts,
    'financiamento'        => $financiamento,
    'condominio'           => $condominio,
    'caixa_paga_excedente' => $caixa_paga_excedente,
    'iptu'                 => $iptu,
    'foto_url'             => $foto_url,
    'edital_url'           => $edital_url,
    'matricula_url'        => $matricula_url,
    'data_leilao_1'        => $data_leilao_1,
    'data_encerramento'    => $data_encerramento,
    'status_caixa'         => $status_caixa,
    'descricao'            => $descricao_nova,
    'area_privativa'       => $area_privativa,
    'area_total'           => $area_total,
    'area_terreno'         => $area_terreno,
]);
