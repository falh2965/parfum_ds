<?php
// نجيبو db.php باش نقدرو نتواصلو مع قاعدة البيانات
require_once 'db.php';

// نقبلو غير طلبات POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    die(json_encode(['error' => 'Method not allowed']));
}

// نجيبو البيانات اللي جات من JavaScript
$data = json_decode(file_get_contents('php://input'), true);

// تحقق واش البيانات موجودة
if (empty($data['items']) || empty($data['total'])) {
    http_response_code(400);
    die(json_encode(['error' => 'بيانات ناقصة']));
}

try {
    // نحفظو الطلب في قاعدة البيانات
    $stmt = $pdo->prepare("
        INSERT INTO orders (items, total, status)
        VALUES (:items, :total, 'pending')
    ");

    $stmt->execute([
        ':items' => json_encode($data['items'], JSON_UNESCAPED_UNICODE),
        ':total' => $data['total']
    ]);

    // نرجعو رد للـ JavaScript
    echo json_encode([
        'success' => true,
        'order_id' => $pdo->lastInsertId()
    ]);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'فشل الحفظ']);
}
?>