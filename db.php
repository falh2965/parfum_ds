<?php
// معلومات الكونيكشن
$host     = "localhost";
$dbname   = "parfum_ds";
$username = "root";       // افتراضي في XAMPP
$password = "";           // افتراضي في XAMPP فارغ

try {
    $pdo = new PDO(
        "mysql:host=$host;dbname=$dbname;charset=utf8",
        $username,
        $password
    );
    // إلا كاين error كيبانو واضح
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

} catch (PDOException $e) {
    die("فشل الكونيكشن: " . $e->getMessage());
}
?>
