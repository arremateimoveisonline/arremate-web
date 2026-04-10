<?php
/**
 * Debug: Verificar dados no banco para o imóvel específico
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

define('DB_PATH', __DIR__ . '/../dados/imoveis.db');

$hdnimovel = '8555532857111';

echo "=== DEBUG IMÓVEL " . htmlspecialchars($hdnimovel) . " ===\n\n";
echo "DB_PATH: " . DB_PATH . "\n";
echo "Existe: " . (file_exists(DB_PATH) ? "SIM ✓" : "NÃO ✗") . "\n\n";

if (!file_exists(DB_PATH)) {
    echo "❌ Banco de dados não encontrado em: " . DB_PATH . "\n";
    exit(1);
}

try {
    $db = new PDO('sqlite:' . DB_PATH);
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Verificar tabela
    $tables = $db->query("SELECT name FROM sqlite_master WHERE type='table'")->fetchAll(PDO::FETCH_COLUMN);
    echo "Tabelas encontradas: " . implode(', ', $tables) . "\n\n";

    // Buscar o imóvel
    $stmt = $db->prepare('SELECT * FROM imoveis WHERE hdnimovel = :h OR numero = :h LIMIT 1');
    $stmt->execute([':h' => $hdnimovel]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$row) {
        echo "❌ Imóvel NÃO encontrado no banco\n";

        // Listar alguns imóveis para diagnóstico
        echo "\nÚltimos 3 imóveis no banco:\n";
        $recent = $db->query("SELECT hdnimovel, numero, cidade, descricao, data_encerramento FROM imoveis ORDER BY id DESC LIMIT 3")->fetchAll(PDO::FETCH_ASSOC);
        foreach ($recent as $i) {
            echo "  - HDN: {$i['hdnimovel']}, Num: {$i['numero']}, Cidade: {$i['cidade']}\n";
            echo "    Descricao: " . substr($i['descricao'], 0, 60) . "...\n";
            echo "    Data Enc: {$i['data_encerramento']}\n";
        }
    } else {
        echo "✓ Imóvel encontrado! Dados:\n";
        echo json_encode($row, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "\n";

        echo "\n=== PROBLEMAS IDENTIFICADOS ===\n";

        if (empty($row['data_encerramento'])) {
            echo "❌ data_encerramento VAZIO (deveria ser: 2026-04-06 10:00)\n";
        }

        if (empty($row['descricao'])) {
            echo "❌ descricao VAZIA\n";
        } else {
            if (strpos($row['descricao'], 'm²') === false && strpos($row['descricao'], 'área') === false) {
                echo "⚠️  descricao não contém informações de ÁREA\n";
                echo "   Conteúdo: " . substr($row['descricao'], 0, 100) . "...\n";
            }
        }

        if (empty($row['modalidade'])) {
            echo "❌ modalidade VAZIA (deveria ser: Licitação Aberta)\n";
        }
    }

} catch (Exception $e) {
    echo "❌ Erro: " . $e->getMessage() . "\n";
    exit(1);
}
