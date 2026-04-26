<?php
/**
 * api.php — Arremate Imóveis Online
 * API REST sobre SQLite. Suporta 40 k+ registros com paginação server-side.
 * Instalação: /var/www/arremate-br/api.php
 * Banco     : /var/www/dados/imoveis.db  (um nível acima do site)
 *
 * Ações disponíveis via ?acao=:
 *   buscar   — listagem paginada com filtros
 *   detalhe  — um imóvel pelo hdnimovel
 *   cidades  — lista de cidades (com contagem)
 *   stats    — totais gerais
 */

ob_start();                      // captura qualquer saída acidental (avisos PHP, BOM, etc.)
error_reporting(0);
ini_set('display_errors', 0);
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Cache-Control: public, max-age=300');   // 5 min cache

/* ── VERIFICAÇÃO: extensão PDO SQLite disponível ─────────── */
if (!extension_loaded('pdo_sqlite')) {
    ob_end_clean();
    http_response_code(503);
    echo json_encode(['erro' => 'Extensão pdo_sqlite não está habilitada no servidor PHP.']);
    exit;
}

/* ── CAMINHO DO BANCO ─────────────────────────────────────────
   Site em : /var/www/arremate-br/
   Banco em: /var/www/dados/imoveis.db
   ─────────────────────────────────────────────────────────── */
define('DB_PATH', __DIR__ . '/../dados/imoveis.db');

/* ── HELPERS ─────────────────────────────────────────────── */

function db(): PDO {
    static $pdo = null;
    if ($pdo) return $pdo;
    if (!file_exists(DB_PATH)) {
        http_response_code(503);
        echo json_encode(['erro' => 'Banco não encontrado. Execute json_to_sqlite.php primeiro.']);
        exit;
    }
    $pdo = new PDO('sqlite:' . DB_PATH);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->exec('PRAGMA journal_mode=WAL');
    $pdo->exec('PRAGMA query_only=1');
    return $pdo;
}

function g(string $k, string $default = ''): string {
    return trim($_GET[$k] ?? $default);
}
function gi(string $k, int $default = 0): int {
    $v = trim($_GET[$k] ?? '');
    return $v !== '' ? (int)$v : $default;
}

/** Formata centavos INTEGER → "R$ 250.000,00" (usado apenas em stats) */
function fmtBRL(int $c): string {
    return 'R$ ' . number_format($c / 100, 2, ',', '.');
}

/** Serializa uma linha do banco para o formato que o JS espera */
function row(array $r): array {
    return [
        /* Identificação */
        'hdnimovel'      => $r['hdnimovel']    ?? $r['numero'] ?? '',
        'numero'         => $r['numero']        ?? '',
        /* Localização */
        'uf'             => $r['uf']            ?? '',
        'cidade'         => $r['cidade']        ?? '',
        'bairro'         => $r['bairro']        ?? '',
        'endereco'       => $r['endereco']      ?? '',
        /* Valores — INTEGER centavos; JS faz ÷100 para exibição */
        'preco'          => (int)($r['preco']      ?? 0),
        'avaliacao'      => (int)($r['avaliacao']  ?? 0),
        'desconto'       => round((float)($r['desconto'] ?? 0), 2),
        /* Condições booleanas */
        'financiamento'  => (int)($r['financiamento'] ?? 0),
        'fgts'           => (int)($r['fgts']    ?? 0),
        'disputa'        => (int)($r['disputa'] ?? 0),
        /* Tipo e modalidade */
        'tipo'           => $r['tipo']          ?? '',
        /* descricao: necessário para imovel-chips.js extrair quartos/área/vagas */
        'descricao'      => $r['descricao']     ?? '',
        'modalidade'     => $r['modalidade']    ?? '',
        'modalidade_raw' => $r['modalidade_raw'] ?? '',
        /* Extras */
        'condominio'     => $r['condominio']    ?? '',
        'iptu'           => $r['iptu']          ?? '',
        'link'           => $r['link']          ?? '',
        'data_encerramento' => $r['data_encerramento'] ?? '',
        'data_leilao_1'  => $r['data_leilao_1'] ?? '',
        'foto_url'       => $r['foto_url']      ?? '',
        'area_privativa' => (float)($r['area_privativa'] ?? 0),
        'area_total'     => (float)($r['area_total']     ?? 0),
        'area_terreno'   => (float)($r['area_terreno']   ?? 0),
    ];
}

/* ── ROTEADOR ────────────────────────────────────────────── */
try {
    $acao = g('acao', 'buscar');

    /* ══════════════════════════════════════════════════════
       BUSCAR — listagem paginada com filtros
       ══════════════════════════════════════════════════════ */
    if ($acao === 'buscar') {

        $limit  = min(max(gi('limit', 100), 1), 500);
        $offset = max(gi('offset', 0), 0);
        $ordem  = g('ordem', 'preco_asc');

        $where  = [];
        $params = [];

        /* UF — padrão SP quando não informado */
        $uf = strtoupper(g('uf'));
        if ($uf !== '' && $uf !== 'TODOS') {
            $where[] = 'UPPER(uf) = :uf';
            $params[':uf'] = $uf;
        }

        /* Cidades (múltiplas separadas por vírgula) */
        $cidades = array_filter(array_map('trim', explode(',', strtoupper(g('cidades')))));
        if ($cidades) {
            $ph = implode(',', array_map(function($i) { return ':c' . $i; }, array_keys($cidades)));
            $where[] = "UPPER(cidade) IN ({$ph})";
            foreach ($cidades as $i => $c) $params[':c' . $i] = $c;
        }

        /* Preço — filtro vem em reais → converter para centavos */
        $pMin = g('preco_min');
        $pMax = g('preco_max');
        if ($pMin !== '') {
            $where[] = 'preco >= :pmin';
            $params[':pmin'] = (int)round((float)str_replace(',', '.', $pMin) * 100);
        }
        if ($pMax !== '') {
            $where[] = 'preco <= :pmax';
            $params[':pmax'] = (int)round((float)str_replace(',', '.', $pMax) * 100);
        }

        /* Preço rápido (dropdown do hero) — já em reais */
        $pRap = g('preco_max_rapido');
        if ($pRap !== '') {
            $where[] = 'preco <= :prap';
            $params[':prap'] = (int)round((float)$pRap * 100);
        }

        /* Desconto */
        $dMin = g('desc_min');
        $dMax = g('desc_max');
        if ($dMin !== '') { $where[] = 'desconto >= :dmin'; $params[':dmin'] = (float)$dMin; }
        if ($dMax !== '') { $where[] = 'desconto <= :dmax'; $params[':dmax'] = (float)$dMax; }

        /* Tipo (múltiplos) — busca por início da descricao */
        $tipos = array_filter(array_map('trim', explode(',', strtolower(g('tipos')))));
        if ($tipos) {
            $or = [];
            foreach ($tipos as $i => $t) {
                $or[] = "LOWER(descricao) LIKE :t{$i}";
                $params[":t{$i}"] = $t . '%';
            }
            $where[] = '(' . implode(' OR ', $or) . ')';
        }

        /* Tipo simples (parâmetro único legado) */
        $tipoSimples = strtolower(g('tipo'));
        if ($tipoSimples !== '' && !$tipos) {
            $where[] = 'LOWER(descricao) LIKE :tsimples';
            $params[':tsimples'] = '%' . $tipoSimples . '%';
        }

        /* Modalidades (múltiplas) — mapeadas para padrões ASCII que funcionam
           com o encoding Windows-1252 que o banco armazena                    */
        $mods = array_values(array_filter(array_map('trim', explode(',', g('modalidades')))));
        if ($mods) {
            $or = [];
            $mi = 0;
            foreach ($mods as $m) {
                $mn = strtolower($m);
                if (strpos($mn, 'sfi') !== false || strpos($mn, 'leil') !== false) {
                    $or[] = "modalidade LIKE :m{$mi}";
                    $params[":m{$mi}"] = '%SFI%';
                } elseif (strpos($mn, 'licita') !== false || strpos($mn, 'aberta') !== false) {
                    $or[] = "modalidade LIKE :m{$mi}";
                    $params[":m{$mi}"] = '%Licita%';
                } elseif (strpos($mn, 'direta') !== false) {
                    $or[] = "modalidade LIKE :m{$mi}";
                    $params[":m{$mi}"] = '%Direta%';
                } elseif (strpos($mn, 'online') !== false || strpos($mn, 'venda') !== false) {
                    /* "Venda Online" NÃO deve capturar "Venda Direta Online" */
                    $or[] = "(modalidade LIKE :m{$mi} AND modalidade NOT LIKE :mx{$mi})";
                    $params[":m{$mi}"]  = '%Online%';
                    $params[":mx{$mi}"] = '%Direta%';
                } else {
                    $or[] = "modalidade LIKE :m{$mi}";
                    $params[":m{$mi}"] = '%' . $m . '%';
                }
                $mi++;
            }
            $where[] = '(' . implode(' OR ', $or) . ')';
        }

        /* Modalidade simples */
        $modSimples = strtolower(g('modalidade'));
        if ($modSimples !== '') {
            if (strpos($modSimples, 'sfi') !== false || strpos($modSimples, 'leil') !== false) {
                $where[] = 'modalidade LIKE :modsimples';
                $params[':modsimples'] = '%SFI%';
            } elseif (strpos($modSimples, 'licita') !== false) {
                $where[] = 'modalidade LIKE :modsimples';
                $params[':modsimples'] = '%Licita%';
            } elseif (strpos($modSimples, 'direta') !== false) {
                $where[] = 'modalidade LIKE :modsimples';
                $params[':modsimples'] = '%Direta%';
            } elseif (strpos($modSimples, 'online') !== false) {
                $where[] = '(modalidade LIKE :modsimples AND modalidade NOT LIKE :modsimd)';
                $params[':modsimples'] = '%Online%';
                $params[':modsimd']    = '%Direta%';
            } else {
                $where[] = 'modalidade LIKE :modsimples';
                $params[':modsimples'] = '%' . g('modalidade') . '%';
            }
        }

        /* Condições booleanas */
        if (g('fgts')    === '1') $where[] = 'fgts = 1';
        if (g('fin')     === '1') $where[] = 'financiamento = 1';
        if (g('disputa') === '1') $where[] = 'disputa = 1';

        /* Código / hdnimovel */
        $cod = g('codigo');
        if ($cod !== '') {
            $where[] = '(numero LIKE :cod OR hdnimovel LIKE :cod)';
            $params[':cod'] = '%' . $cod . '%';
        }

        /* Cidade simples */
        $cidadeSimples = strtoupper(g('cidade'));
        if ($cidadeSimples !== '') {
            $where[] = 'UPPER(cidade) LIKE :cidadesimples';
            $params[':cidadesimples'] = '%' . $cidadeSimples . '%';
        }

        /* Condomínio */
        $rCond = g('r_cond');
        if ($rCond !== '') {
            $where[] = 'condominio = :rcond';
            $params[':rcond'] = $rCond;
        }

        /* IPTU/Tributos */
        $rIptu = g('r_iptu');
        if ($rIptu !== '') {
            $where[] = 'iptu = :riptu';
            $params[':riptu'] = $rIptu;
        }

        /* Data de encerramento (até) */
        $dataAte = g('data_ate');
        if ($dataAte !== '') {
            $where[] = 'data_encerramento <= :dataate';
            $params[':dataate'] = $dataAte;
        }

        /* Área — filtra por colunas numéricas reais (area_privativa, area_total, area_terreno) */
        $areaTipo = strtolower(trim(g('area_tipo')));
        $areaMin  = g('area_min');
        $areaMax  = g('area_max');
        if ($areaMin !== '' || $areaMax !== '') {
            /* Mapeia tipo para coluna numérica */
            $areaCol = 'area_total'; // padrão
            if ($areaTipo === 'privativa') $areaCol = 'area_privativa';
            elseif ($areaTipo === 'terreno') $areaCol = 'area_terreno';
            /* Só filtra imóveis que têm o tipo de área preenchido */
            $where[] = "{$areaCol} > 0";
            if ($areaMin !== '') {
                $where[] = "{$areaCol} >= :areamin";
                $params[':areamin'] = (float)str_replace(',', '.', $areaMin);
            }
            if ($areaMax !== '') {
                $where[] = "{$areaCol} <= :areamax";
                $params[':areamax'] = (float)str_replace(',', '.', $areaMax);
            }
        }

        $whereSQL = $where ? 'WHERE ' . implode(' AND ', $where) : '';

        $orderMap = [
            'preco_asc'      => 'preco ASC',
            'preco_desc'     => 'preco DESC',
            'desconto_desc'  => 'desconto DESC',
            'desconto_asc'   => 'desconto ASC',
            'cidade_asc'     => 'cidade ASC',
            'data_asc'       => 'data_encerramento ASC',
            'data_desc'      => 'data_encerramento DESC',
        ];
        $orderSQL = 'ORDER BY ' . ($orderMap[$ordem] ?? 'preco ASC');

        /* Contagem total para paginação */
        $cntStmt = db()->prepare("SELECT COUNT(*) FROM imoveis {$whereSQL}");
        $cntStmt->execute($params);
        $totalFiltrado = (int)$cntStmt->fetchColumn();

        /* Dados paginados */
        $sql  = "SELECT * FROM imoveis {$whereSQL} {$orderSQL} LIMIT :limit OFFSET :offset";
        $stmt = db()->prepare($sql);
        foreach ($params as $k => $v) $stmt->bindValue($k, $v);
        $stmt->bindValue(':limit',  $limit,  PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();
        $rows = array_map('row', $stmt->fetchAll(PDO::FETCH_ASSOC));

        echo json_encode([
            'sucesso' => true,
            'total'   => $totalFiltrado,
            'limit'   => $limit,
            'offset'  => $offset,
            'paginas' => (int)ceil($totalFiltrado / max($limit, 1)),
            'imoveis' => $rows,
        ], JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_SUBSTITUTE);

    /* ══════════════════════════════════════════════════════
       DETALHE — um imóvel pelo hdnimovel
       ══════════════════════════════════════════════════════ */
    } elseif ($acao === 'detalhe') {

        $hdn = g('hdnimovel');
        if ($hdn === '') {
            echo json_encode(['erro' => 'hdnimovel obrigatório']);
            exit;
        }
        $stmt = db()->prepare(
            'SELECT * FROM imoveis WHERE hdnimovel = :h OR numero = :h LIMIT 1'
        );
        $stmt->execute([':h' => $hdn]);
        $r = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$r) {
            http_response_code(404);
            echo json_encode(['erro' => 'Não encontrado', 'nao_encontrado' => true]);
            exit;
        }
        echo json_encode(['sucesso' => true, 'imovel' => row($r)], JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_SUBSTITUTE);

    /* ══════════════════════════════════════════════════════
       CIDADES — lista por UF com contagem
       ══════════════════════════════════════════════════════ */
    } elseif ($acao === 'cidades') {

        $uf  = strtoupper(g('uf'));
        $sql = 'SELECT cidade, uf, COUNT(*) as total FROM imoveis';
        $p   = [];
        if ($uf) { $sql .= ' WHERE UPPER(uf) = :uf'; $p[':uf'] = $uf; }
        $sql .= ' GROUP BY cidade ORDER BY cidade ASC';
        $stmt = db()->prepare($sql);
        $stmt->execute($p);
        echo json_encode([
            'sucesso'  => true,
            'cidades'  => $stmt->fetchAll(PDO::FETCH_ASSOC),
        ], JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_SUBSTITUTE);

    /* ══════════════════════════════════════════════════════
       STATS — totais gerais
       ══════════════════════════════════════════════════════ */
    } elseif ($acao === 'stats') {

        $total = (int)db()->query('SELECT COUNT(*) FROM imoveis')->fetchColumn();
        $sp    = (int)db()->query("SELECT COUNT(*) FROM imoveis WHERE uf='SP'")->fetchColumn();
        $ufs   = db()->query("SELECT DISTINCT uf FROM imoveis ORDER BY uf")->fetchAll(PDO::FETCH_COLUMN);
        $minP  = (int)db()->query('SELECT MIN(preco) FROM imoveis WHERE preco > 0')->fetchColumn();
        $maxP  = (int)db()->query('SELECT MAX(preco) FROM imoveis')->fetchColumn();

        echo json_encode([
            'sucesso'       => true,
            'total_imoveis' => $total,
            'total_sp'      => $sp,
            'estados'       => $ufs,
            'preco_min'     => $minP,
            'preco_max'     => $maxP,
        ], JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_SUBSTITUTE);

    } else {
        http_response_code(400);
        echo json_encode(['erro' => 'Ação inválida. Use: buscar | detalhe | cidades | stats']);
    }

} catch (Exception $e) {
    ob_end_clean();
    http_response_code(500);
    echo json_encode([
        'erro'    => 'Erro interno do servidor',
        'detalhe' => $e->getMessage(),
    ], JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_SUBSTITUTE);
}
