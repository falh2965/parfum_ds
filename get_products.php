<?php
require_once 'db.php';

header('Content-Type: application/json');

$products = $pdo->query("SELECT * FROM products ORDER BY id DESC")->fetchAll(PDO::FETCH_ASSOC);

echo json_encode($products, JSON_UNESCAPED_UNICODE);
?>