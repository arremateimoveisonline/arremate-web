<?php
$pdo = new PDO('sqlite:/var/www/dados/imoveis.db');
$r = $pdo->query('SELECT COUNT(*) as total FROM imoveis')->fetch(PDO::FETCH_ASSOC);
echo json_encode($r);
