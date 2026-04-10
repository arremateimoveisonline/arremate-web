<?php
set_time_limit(300);
ini_set('memory_limit', '256M');

$CSV_PATH = '/var/www/dados/imoveis.csv';
$DB_PATH  = '/var/www/dados/imoveis.db';

function parseCentavos(string $s): int {
    $s = trim($s);
    if ($s === '' || $s === '0') return 0;
    $s = str_replace('.', '', $s);
    $s = str_replace(',', '.', $s);
    return (int) round((float)$s * 100);
}

function inferTipo(string $desc): string {
    $d = mb_strtolower($desc);
    if (strpos($d, 'apartamento')) return 'apartamento';
    if (strpos($d, 'casa'))        return 'casa';
    if (strpos($d, 'terreno'))     return 'terreno';
    if (strpos($d, 'comercial'))   return 'comercial';
    if (strpos($d, 'rural'))       return 'rural';
    return 'outro';
}

// Apaga banco antigo
if (file_exists($DB_PATH)) unlink($DB_PATH);

$db = new PDO("sqlite:{$DB_PATH}");
$db->exec('PRAGMA journal_mode=WAL');
$db->exec('PRAGMA synchronous=NORMAL');

$db->exec("CREATE TABLE imoveis (
    id               INTEGER PRIMARY KEY AUTOINCREMENT,
    hdnimovel        TEXT,
    numero           TEXT,
    uf               TEXT,
    cidade           TEXT,
    bairro           TEXT,
    endereco         TEXT,
    preco            INTEGER DEFAULT 0,
    avaliacao        INTEGER DEFAULT 0,
    desconto         REAL    DEFAULT 0,
    financiamento    INTEGER DEFAULT 0,
    fgts             INTEGER DEFAULT 0,
    disputa          INTEGER DEFAULT 0,
    tipo             TEXT    DEFAULT '',
    modalidade       TEXT    DEFAULT '',
    modalidade_raw   TEXT    DEFAULT '',
    descricao        TEXT    DEFAULT '',
    condominio       TEXT    DEFAULT '',
    iptu             TEXT    DEFAULT '',
    link             TEXT    DEFAULT '',
    data_encerramento TEXT   DEFAULT ''
)");

$db->exec("CREATE INDEX idx_uf       ON imoveis(uf)");
$db->exec("CREATE INDEX idx_cidade   ON imoveis(cidade)");
$db->exec("CREATE INDEX idx_preco    ON imoveis(preco)");
$db->exec("CREATE INDEX idx_tipo     ON imoveis(tipo)");
$db->exec("CREATE INDEX idx_desconto ON imoveis(desconto)");

$stmt = $db->prepare("INSERT INTO imoveis
    (hdnimovel,numero,uf,cidade,bairro,endereco,preco,avaliacao,desconto,
     financiamento,fgts,disputa,tipo,modalidade,modalidade_raw,descricao,link)
    VALUES
    (:hdnimovel,:numero,:uf,:cidade,:bairro,:endereco,:preco,:avaliacao,:desconto,
     :financiamento,:fgts,:disputa,:tipo,:modalidade,:modalidade_raw,:descricao,:link)
");

$handle = fopen($CSV_PATH, 'r');
$ok = 0;
$skip = 0;

while (($line = fgets($handle)) !== false) {
    $line = trim($line);
    if ($line === '') { $skip++; continue; }

    // Detecta separador
    $row = str_getcsv($line, ';');

    // Pula linhas que não têm pelo menos 8 colunas
    if (count($row) < 8) { $skip++; continue; }

    $numero    = trim($row[0]);
    $uf        = trim($row[1]);
    $cidade    = trim($row[2]);
    $bairro    = trim($row[3] ?? '');
    $endereco  = trim($row[4] ?? '');
    $preco     = parseCentavos($row[5] ?? '0');
    $avaliacao = parseCentavos($row[6] ?? '0');
    $desconto  = round((float)str_replace(',', '.', trim($row[7] ?? '0')), 2);
    $fin       = strtolower(trim($row[8] ?? '')) === 'sim' ? 1 : 0;
    $descricao = trim($row[9] ?? '');
    $modalidade= trim($row[10] ?? '');
    $link      = trim($row[11] ?? '');

    // Pula cabeçalho e linhas inválidas
    if (!is_numeric(str_replace(' ', '', $numero))) { $skip++; continue; }
    if ($preco === 0 && $avaliacao === 0) { $skip++; continue; }

    $hdnimovel = str_replace(' ', '', $numero);
    $tipo      = inferTipo($descricao);

    $stmt->execute([
        ':hdnimovel'    => $hdnimovel,
        ':numero'       => $numero,
        ':uf'           => $uf,
        ':cidade'       => $cidade,
        ':bairro'       => $bairro,
        ':endereco'     => $endereco,
        ':preco'        => $preco,
        ':avaliacao'    => $avaliacao,
        ':desconto'     => $desconto,
        ':financiamento'=> $fin,
        ':fgts'         => (strpos(strtolower($descricao . ' ' . $modalidade), 'fgts') !== false) ? 1 : 0,
        ':disputa'      => 0,
        ':tipo'         => $tipo,
        ':modalidade'   => $modalidade,
        ':modalidade_raw'=> $modalidade,
        ':descricao'    => $descricao,
        ':link'         => $link,
    ]);
    $ok++;
}

fclose($handle);
echo "✅ Importados: {$ok} registros. Pulados: {$skip}\n";
