<?php
// Verifica leitura do campo Financiamento no CSV vs banco
$csvSP = '/var/www/dados/csv/Lista_imoveis_SP.csv';
$db    = new PDO('sqlite:/var/www/dados/imoveis.db');

// --- Contar Sim/Não no CSV de SP ---
$fh = fopen($csvSP, 'r');
$ln = 0; $sim = 0; $nao = 0; $outro = 0;
while (($line = fgets($fh)) !== false) {
    $ln++;
    $line = trim($line);
    if ($line === '' || $ln <= 2) continue;
    if (!mb_check_encoding($line, 'UTF-8'))
        $line = mb_convert_encoding($line, 'UTF-8', 'ISO-8859-1');
    $r = str_getcsv($line, ';');
    if (count($r) < 9) continue;
    $fin = mb_strtolower(trim($r[8] ?? ''));
    if ($fin === 'sim')  $sim++;
    elseif ($fin === 'não' || $fin === 'nao') $nao++;
    else { $outro++; echo "  outro[$ln]: '".htmlspecialchars($r[8])."' raw=".bin2hex(substr($r[8],0,6))."\n"; }
}
fclose($fh);
echo "=== CSV SP ===\n";
echo "  Sim: $sim\n  Não: $nao\n  Outro/inválido: $outro\n\n";

// --- Banco: financiamento 0/1 para SP ---
$rows = $db->query("SELECT financiamento, COUNT(*) qt FROM imoveis WHERE uf='SP' GROUP BY financiamento")->fetchAll(PDO::FETCH_ASSOC);
echo "=== Banco SP ===\n";
foreach ($rows as $row) echo "  financiamento={$row['financiamento']}: {$row['qt']}\n";

// --- Total geral no banco ---
$rows = $db->query("SELECT financiamento, COUNT(*) qt FROM imoveis GROUP BY financiamento")->fetchAll(PDO::FETCH_ASSOC);
echo "\n=== Banco TOTAL ===\n";
foreach ($rows as $row) echo "  financiamento={$row['financiamento']}: {$row['qt']}\n";

// --- Confirmar: amostra imóvel com Sim no CSV vs banco ---
echo "\n=== Amostra: imóvel 10206716 (SP, deveria ter financiamento=1) ===\n";
$r = $db->query("SELECT hdnimovel, uf, financiamento, fgts, descricao FROM imoveis WHERE hdnimovel='10206716'")->fetch(PDO::FETCH_ASSOC);
print_r($r);

// --- Verificar outros estados ---
echo "\n=== CSV todos os estados: total Sim/Não ===\n";
$csvDir = '/var/www/dados/csv/';
$simTotal = 0; $naoTotal = 0;
foreach (glob($csvDir . '*.csv') as $csv) {
    $ufName = basename($csv, '.csv');
    $fh2 = fopen($csv, 'r'); $ln2 = 0; $s = 0; $n = 0;
    while (($line = fgets($fh2)) !== false) {
        $ln2++;
        $line = trim($line);
        if ($line === '' || $ln2 <= 2) continue;
        if (!mb_check_encoding($line, 'UTF-8'))
            $line = mb_convert_encoding($line, 'UTF-8', 'ISO-8859-1');
        $r2 = str_getcsv($line, ';');
        if (count($r2) < 9) continue;
        $fin = mb_strtolower(trim($r2[8] ?? ''));
        if ($fin === 'sim') $s++;
        elseif ($fin === 'não' || $fin === 'nao') $n++;
    }
    fclose($fh2);
    $simTotal += $s; $naoTotal += $n;
    echo "  $ufName: Sim=$s Não=$n\n";
}
echo "\n  TOTAL CSV: Sim=$simTotal | Não=$naoTotal\n";
