<?php
session_start();

// حماية — إلا ما دخلش يرجع للوجين
if (!isset($_SESSION['admin'])) {
    header('Location: login.php');
    exit;
}

require_once '../db.php';

// جيب عدد الطلبات
$totalOrders = $pdo->query("SELECT COUNT(*) FROM orders")->fetchColumn();

// جيب عدد المنتجات
$totalProducts = $pdo->query("SELECT COUNT(*) FROM products")->fetchColumn();

// جيب مجموع المبيعات
$totalRevenue = $pdo->query("SELECT SUM(total) FROM orders")->fetchColumn();

// جيب آخر 5 طلبات
$orders = $pdo->query("SELECT * FROM orders ORDER BY created_at DESC LIMIT 5")->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
  <meta charset="UTF-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>الداشبورد | PARFUM DS</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css"/>
  <style>
    *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
    body {
      font-family: 'Tajawal', sans-serif;
      background: #0d0a06;
      color: #d4c4a0;
      min-height: 100vh;
    }

    /* ── Sidebar ── */
    .sidebar {
      position: fixed;
      top: 0; right: 0;
      width: 220px;
      height: 100vh;
      background: #1a1409;
      border-left: 1px solid rgba(201,168,76,.15);
      padding: 30px 0;
      display: flex;
      flex-direction: column;
    }
    .sidebar-logo {
      text-align: center;
      color: #c9a84c;
      font-size: 1.2rem;
      font-weight: bold;
      padding: 0 20px 24px;
      border-bottom: 1px solid rgba(201,168,76,.15);
      margin-bottom: 16px;
    }
    .sidebar a {
      display: flex;
      align-items: center;
      gap: 10px;
      padding: 12px 24px;
      color: #d4c4a0;
      text-decoration: none;
      font-size: .95rem;
      transition: background .2s, color .2s;
    }
    .sidebar a:hover, .sidebar a.active {
      background: rgba(201,168,76,.1);
      color: #c9a84c;
    }
    .sidebar a i { width: 18px; }
    .sidebar .logout {
      margin-top: auto;
      border-top: 1px solid rgba(201,168,76,.15);
      padding-top: 16px;
    }
    .sidebar .logout a { color: #ff6b6b; }

    /* ── Main ── */
    .main {
      margin-right: 220px;
      padding: 30px;
    }
    .page-title {
      font-size: 1.4rem;
      color: #f5edd8;
      margin-bottom: 24px;
    }
    .page-title span { color: #c9a84c; }

    /* ── Stats ── */
    .stats {
      display: grid;
      grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
      gap: 16px;
      margin-bottom: 30px;
    }
    .stat-card {
      background: #1a1409;
      border: 1px solid rgba(201,168,76,.15);
      border-radius: 12px;
      padding: 20px;
      display: flex;
      align-items: center;
      gap: 16px;
    }
    .stat-icon {
      width: 48px; height: 48px;
      background: rgba(201,168,76,.1);
      border-radius: 10px;
      display: flex; align-items: center; justify-content: center;
      font-size: 1.3rem;
      color: #c9a84c;
    }
    .stat-info p {
      font-size: .8rem;
      color: rgba(212,196,160,.5);
      margin-bottom: 4px;
    }
    .stat-info h3 {
      font-size: 1.4rem;
      color: #f5edd8;
    }

    /* ── Table ── */
    .section-title {
      font-size: 1rem;
      color: #c9a84c;
      margin-bottom: 14px;
    }
    .table-wrap {
      background: #1a1409;
      border: 1px solid rgba(201,168,76,.15);
      border-radius: 12px;
      overflow: hidden;
    }
    table {
      width: 100%;
      border-collapse: collapse;
    }
    th {
      background: rgba(201,168,76,.08);
      color: #c9a84c;
      font-size: .82rem;
      padding: 12px 16px;
      text-align: right;
    }
    td {
      padding: 12px 16px;
      font-size: .88rem;
      border-top: 1px solid rgba(255,255,255,.04);
      color: #d4c4a0;
    }
    .badge {
      display: inline-block;
      padding: 3px 10px;
      border-radius: 20px;
      font-size: .75rem;
      font-weight: bold;
    }
    .badge.pending {
      background: rgba(255,193,7,.15);
      color: #ffc107;
    }
    .badge.done {
      background: rgba(37,211,102,.15);
      color: #25D366;
    }
  </style>
</head>
<body>

<!-- Sidebar -->
<div class="sidebar">
  <div class="sidebar-logo">
    <i class="fa-solid fa-spray-can-sparkles"></i> PARFUM DS
  </div>
  <a href="dashboard.php" class="active">
    <i class="fa-solid fa-chart-line"></i> الداشبورد
  </a>
  <a href="products.php">
    <i class="fa-solid fa-box"></i> المنتجات
  </a>
  <a href="orders.php">
    <i class="fa-solid fa-bag-shopping"></i> الطلبات
  </a>
  <div class="logout">
    <a href="logout.php">
      <i class="fa-solid fa-right-from-bracket"></i> خروج
    </a>
  </div>
</div>

<!-- Main -->
<div class="main">
  <h1 class="page-title">مرحباً <span><?= $_SESSION['admin'] ?></span> 👋</h1>

  <!-- Stats -->
  <div class="stats">
    <div class="stat-card">
      <div class="stat-icon"><i class="fa-solid fa-bag-shopping"></i></div>
      <div class="stat-info">
        <p>إجمالي الطلبات</p>
        <h3><?= $totalOrders ?></h3>
      </div>
    </div>
    <div class="stat-card">
      <div class="stat-icon"><i class="fa-solid fa-box"></i></div>
      <div class="stat-info">
        <p>المنتجات</p>
        <h3><?= $totalProducts ?></h3>
      </div>
    </div>
    <div class="stat-card">
      <div class="stat-icon"><i class="fa-solid fa-coins"></i></div>
      <div class="stat-info">
        <p>إجمالي المبيعات</p>
        <h3><?= number_format($totalRevenue, 0) ?> درهم</h3>
      </div>
    </div>
  </div>

  <!-- آخر الطلبات -->
  <p class="section-title"><i class="fa-solid fa-clock-rotate-left"></i> آخر الطلبات</p>
  <div class="table-wrap">
    <table>
      <thead>
        <tr>
          <th>#</th>
          <th>المجموع</th>
          <th>الحالة</th>
          <th>التاريخ</th>
        </tr>
      </thead>
      <tbody>
        <?php if (empty($orders)): ?>
          <tr>
            <td colspan="4" style="text-align:center;color:gray;padding:24px">
              ما كاين والو دبا
            </td>
          </tr>
        <?php else: ?>
          <?php foreach ($orders as $order): ?>
            <tr>
              <td>#<?= $order['id'] ?></td>
              <td><?= number_format($order['total'], 0) ?> درهم</td>
              <td>
                <span class="badge <?= $order['status'] ?>">
                  <?= $order['status'] === 'pending' ? 'في الانتظار' : 'مكتمل' ?>
                </span>
              </td>
              <td><?= date('d/m/Y H:i', strtotime($order['created_at'])) ?></td>
            </tr>
          <?php endforeach; ?>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>

</body>
</html>