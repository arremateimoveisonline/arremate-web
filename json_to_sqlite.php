<?php
/**
 * json_to_sqlite.php
 * Arremate Imóveis Online — Migração única: imoveis.json → imoveis.db
 * Coloque este arquivo na raiz pública e acesse via navegador ou CLI:
 *   php json_to_sqlite.php
 * Após rodar com sucesso, DELETE este arquivo do servidor.
 */

set_time_limit(300);
ini_set('memory_limit', '256M');

$JSON_PATH = __DIR__ . '/../dados/imoveis.json';
$DB_PATH   = __DIR__ . '/../dados/imoveis.db';

/* ─── helpers ─────────────────────────────────────────────── */

/** "378.418,91" → 37841891  (centavos INTEGER) */
function parseCentavos(string $s): int {
    $s = trim($s);
    if ($s === '' || $s === '0') return 0;
    // remove pontos de milhar, troca vírgula decimal por ponto
    $s = str_replace('.', '', $s);
    $s = str_replace(',', '.', $s);
    return (int) round((float)$s * 100);
}

function inferTipo(string $desc): string {
    $d = mb_strtolower($desc);
    if (str_contains($d, 'apartamento')) return 'apartamento';
    if (str_contains($d, 'casa'))        return 'casa';
    if (str_contains($d, 'terreno'))     return 'terreno';
    if (str_contains($d, 'gleba'))       return 'gleba';
    if (str_contains($d, 'loja'))        return 'loja';
    if (str_contains($d, 'prédio') || str_contains($d, 'predio')) return 'predio';
    if (str_contains($d, 'sala'))        return 'sala';
    if (str_contains($d, 'lote'))        return 'lote';
    if (str_contains($d, 'comercial'))   return 'comercial';
    return 'imovel';
}

function inferFgts(string $desc, string $fin): int {
    $d = mb_strtolower($desc . ' ' . $fin);
    return (str_contains($d, 'fgts')) ? 1 : 0;
}

function inferFinanciamento(string $desc, string $fin): int {
    if (mb_strtolower(trim($fin)) === 'sim') return 1;
    $d = mb_strtolower($desc);
    return (str_contains($d, 'financiamento') || str_contains($d, 'sfh')) ? 1 : 0;
}

function inferDisputa(string $modalidade): int {
    $m = mb_strtolower($modalidade);
    return (str_contains($m, 'venda online') && !str_contains($m, 'direta')) ? 1 : 0;
}

function hdnFromLink(string $link): string {
    $m = [];
    preg_match('/hdnimovel=(\d+)/i', $link, $m);
    return $m[1] ?? '';
}

/* ─── main ─────────────────────────────────────────────────── */

if (!file_exists($JSON_PATH)) {
    die("ERRO: {$JSON_PATH} não encontrado.\n");
}

echo "Lendo JSON...\n";
$raw   = file_get_contents($JSON_PATH);
$dados = json_decode($raw, true);
if (!$dados || empty($dados['imoveis'])) {
    die("ERRO: JSON inválido ou vazio.\n");
}
$lista = $dados['imoveis'];
$total = count($lista);
echo "Total de registros: {$total}\n";

/* Recria o banco sempre do zero */
if (file_exists($DB_PATH)) unlink($DB_PATH);

$db = new PDO("sqlite:{$DB_PATH}");
$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
$db->exec('PRAGMA journal_mode=WAL');
$db->exec('PRAGMA synchronous=NORMAL');

$db->exec("
CREATE TABLE imoveis (
  id            INTEGER PRIMARY KEY AUTOINCREMENT,
  numero        TEXT,
  hdnimovel     TEXT,
  uf            TEXT,
  cidade        TEXT,
  bairro        TEXT,
  endereco      TEXT,
  preco         INTEGER,
  avaliacao     INTEGER,
  desconto      REAL,
  financiamento INTEGER DEFAULT 0,
  fgts          INTEGER DEFAULT 0,
  disputa       INTEGER DEFAULT 0,
  tipo          TEXT,
  descricao     TEXT,
  modalidade    TEXT,
  modalidade_raw TEXT,
  link          TEXT
);
CREATE INDEX idx_uf       ON imoveis(uf);
CREATE INDEX idx_cidade   ON imoveis(cidade);
CREATE INDEX idx_preco    ON imoveis(preco);
CREATE INDEX idx_desconto ON imoveis(desconto);
CREATE INDEX idx_tipo     ON imoveis(tipo);
CREATE INDEX idx_mod      ON imoveis(modalidade);
");

$sql = "INSERT INTO imoveis
  (numero,hdnimovel,uf,cidade,bairro,endereco,
   preco,avaliacao,desconto,financiamento,fgts,disputa,
   tipo,descricao,modalidade,modalidade_raw,link)
VALUES
  (:numero,:hdnimovel,:uf,:cidade,:bairro,:endereco,
   :preco,:avaliacao,:desconto,:financiamento,:fgts,:disputa,
   :tipo,:descricao,:modalidade,:modalidade_raw,:link)";
$stmt = $db->prepare($sql);

$db->beginTransaction();
$ok = 0;
foreach ($lista as $i) {
    $desc    = $i['descricao']  ?? '';
    $fin_raw = $i['financiamento'] ?? '';
    $mod_raw = $i['modalidade'] ?? '';

    /* Normaliza modalidade para valor canônico */
    $mod_low = mb_strtolower($mod_raw);
    if (str_contains($mod_low, 'compra direta') || str_contains($mod_low, 'venda direta')) {
        $mod = 'Venda Direta Online';
    } elseif (str_contains($mod_low, 'leilão sfi') || str_contains($mod_low, 'leilao sfi') || str_contains($mod_low, 'edital')) {
        $mod = 'Leilão SFI';
    } elseif (str_contains($mod_low, 'licitação') || str_contains($mod_low, 'licitacao')) {
        $mod = 'Licitação Aberta';
    } elseif (str_contains($mod_low, 'venda online')) {
        $mod = 'Venda Online';
    } else {
        $mod = $mod_raw;
    }

    $stmt->execute([
        ':numero'        => $i['numero'] ?? '',
        ':hdnimovel'     => hdnFromLink($i['link'] ?? '') ?: ($i['numero'] ?? ''),
        ':uf'            => strtoupper(trim($i['uf'] ?? '')),
        ':cidade'        => mb_strtoupper(trim($i['cidade'] ?? '')),
        ':bairro'        => trim($i['bairro'] ?? ''),
        ':endereco'      => trim($i['endereco'] ?? ''),
        ':preco'         => parseCentavos($i['preco'] ?? '0'),
        ':avaliacao'     => parseCentavos($i['avaliacao'] ?? '0'),
        ':desconto'      => (float)str_replace(',', '.', $i['desconto'] ?? '0'),
        ':financiamento' => inferFinanciamento($desc, $fin_raw),
        ':fgts'          => inferFgts($desc, $fin_raw),
        ':disputa'       => inferDisputa($mod_raw),
        ':tipo'          => inferTipo($desc),
        ':descricao'     => $desc,
        ':modalidade'    => $mod,
        ':modalidade_raw'=> $mod_raw,
        ':link'          => $i['link'] ?? '',
    ]);
    $ok++;
    if ($ok % 1000 === 0) echo "  {$ok}/{$total} inseridos...\n";
}
$db->commit();

echo "\n✅ Migração concluída: {$ok} registros em {$DB_PATH}\n";
echo "IMPORTANTE: Delete este arquivo do servidor agora!\n";
