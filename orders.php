<?php
session_start();
if (!isset($_SESSION['admin'])) {
    header('Location: login.php');
    exit;
}

require_once '../db.php';

// تحديث حالة الطلب
if (isset($_GET['status']) && isset($_GET['id'])) {
    $id     = (int)$_GET['id'];
    $status = $_GET['status'] === 'done' ? 'done' : 'pending';
    $pdo->prepare("UPDATE orders SET status = ? WHERE id = ?")->execute([$status, $id]);
    header('Location: orders.php');
    exit;
}

// حذف طلب
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    $pdo->prepare("DELETE FROM orders WHERE id = ?")->execute([$id]);
    header('Location: orders.php');
    exit;
}

// جيب كل الطلبات
$orders = $pdo->query("SELECT * FROM orders ORDER BY created_at DESC")->fetchAll(PDO::FETCH_ASSOC);

// حساب الإحصائيات
$pending = $pdo->query("SELECT COUNT(*) FROM orders WHERE status = 'pending'")->fetchColumn();
$completed = $pdo->query("SELECT COUNT(*) FROM orders WHERE status = 'done'")->fetchColumn();
$totalRevenue = $pdo->query("SELECT SUM(total) FROM orders")->fetchColumn();
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
  <meta charset="UTF-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>الطلبات | PARFUM DS</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css"/>
  <style>
    *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
    body {
      font-family: 'Tajawal', sans-serif;
      background: #0d0a06; color: #d4c4a0; min-height: 100vh;
    }
    .sidebar {
      position: fixed; top: 0; right: 0;
      width: 220px; height: 100vh;
      background: #1a1409;
      border-left: 1px solid rgba(201,168,76,.15);
      padding: 30px 0;
      display: flex; flex-direction: column;
    }
    .sidebar-logo {
      text-align: center; color: #c9a84c;
      font-size: 1.2rem; font-weight: bold;
      padding: 0 20px 24px;
      border-bottom: 1px solid rgba(201,168,76,.15);
      margin-bottom: 16px;
    }
    .sidebar a {
      display: flex; align-items: center; gap: 10px;
      padding: 12px 24px; color: #d4c4a0;
      text-decoration: none; font-size: .95rem;
      transition: background .2s, color .2s;
    }
    .sidebar a:hover, .sidebar a.active {
      background: rgba(201,168,76,.1); color: #c9a84c;
    }
    .sidebar a i { width: 18px; }
    .sidebar .logout {
      margin-top: auto;
      border-top: 1px solid rgba(201,168,76,.15);
      padding-top: 16px;
    }
    .sidebar .logout a { color: #ff6b6b; }

    .main { margin-right: 220px; padding: 30px; }
    .page-title { font-size: 1.4rem; color: #f5edd8; margin-bottom: 24px; }

    .stats {
      display: grid;
      grid-template-columns: repeat(auto-fill, minmax(180px, 1fr));
      gap: 16px; margin-bottom: 28px;
    }
    .stat-card {
      background: #1a1409;
      border: 1px solid rgba(201,168,76,.15);
      border-radius: 12px; padding: 18px;
      text-align: center;
    }
    .stat-icon {
      font-size: 1.8rem; margin-bottom: 8px;
      color: #c9a84c;
    }
    .stat-label {
      font-size: .8rem; color: rgba(212,196,160,.5);
      margin-bottom: 6px;
    }
    .stat-value {
      font-size: 1.3rem; color: #f5edd8;
      font-weight: bold;
    }

    .table-wrap {
      background: #1a1409;
      border: 1px solid rgba(201,168,76,.15);
      border-radius: 12px; overflow: hidden;
    }
    table { width: 100%; border-collapse: collapse; }
    th {
      background: rgba(201,168,76,.08);
      color: #c9a84c; font-size: .82rem;
      padding: 12px 16px; text-align: right;
    }
    td {
      padding: 14px 16px; font-size: .88rem;
      border-top: 1px solid rgba(255,255,255,.04);
      color: #d4c4a0;
    }
    .badge {
      display: inline-block;
      padding: 4px 12px; border-radius: 20px;
      font-size: .75rem; font-weight: bold;
    }
    .badge.pending {
      background: rgba(255,193,7,.15);
      color: #ffc107;
    }
    .badge.done {
      background: rgba(37,211,102,.15);
      color: #25D366;
    }
    .items-list {
      max-height: 150px; overflow-y: auto;
      font-size: .8rem; line-height: 1.6;
    }
    .items-list ul {
      list-style: none; padding: 0;
    }
    .items-list li {
      padding: 4px 0;
      color: rgba(212,196,160,.7);
    }
    .btn-done {
      background: rgba(37,211,102,.15);
      border: 1px solid rgba(37,211,102,.3);
      color: #25D366; border-radius: 6px;
      padding: 5px 12px; font-size: .8rem;
      cursor: pointer; text-decoration: none;
      margin-left: 6px;
    }
    .btn-pending {
      background: rgba(255,193,7,.15);
      border: 1px solid rgba(255,193,7,.3);
      color: #ffc107; border-radius: 6px;
      padding: 5px 12px; font-size: .8rem;
      cursor: pointer; text-decoration: none;
      margin-left: 6px;
    }
    .btn-delete {
      background: rgba(255,80,80,.1);
      border: 1px solid rgba(255,80,80,.3);
      color: #ff6b6b; border-radius: 6px;
      padding: 5px 12px; font-size: .8rem;
      cursor: pointer; text-decoration: none;
    }
  </style>
</head>
<body>

<div class="sidebar">
  <div class="sidebar-logo">
    <i class="fa-solid fa-spray-can-sparkles"></i> PARFUM DS
  </div>
  <a href="dashboard.php"><i class="fa-solid fa-chart-line"></i> لوحة التحكم</a>
  <a href="products.php"><i class="fa-solid fa-box"></i> المنتجات</a>
  <a href="orders.php" class="active"><i class="fa-solid fa-bag-shopping"></i> الطلبات</a>
  <div class="logout">
    <a href="logout.php"><i class="fa-solid fa-right-from-bracket"></i> تسجيل الخروج</a>
  </div>
</div>

<div class="main">
  <h1 class="page-title"><i class="fa-solid fa-bag-shopping"></i> إدارة الطلبات</h1>

  <!-- الإحصائيات -->
  <div class="stats">
    <div class="stat-card">
      <div class="stat-icon"><i class="fa-solid fa-hourglass-end"></i></div>
      <div class="stat-label">قيد الانتظار</div>
      <div class="stat-value"><?= $pending ?></div>
    </div>
    <div class="stat-card">
      <div class="stat-icon"><i class="fa-solid fa-check-circle"></i></div>
      <div class="stat-label">مكتمل</div>
      <div class="stat-value"><?= $completed ?></div>
    </div>
    <div class="stat-card">
      <div class="stat-icon"><i class="fa-solid fa-coins"></i></div>
      <div class="stat-label">إجمالي المبيعات</div>
      <div class="stat-value"><?= number_format($totalRevenue, 0) ?> درهم</div>
    </div>
  </div>

  <!-- جدول الطلبات -->
  <div class="table-wrap">
    <table>
      <thead>
        <tr>
          <th>#</th>
          <th>العناصر</th>
          <th>المجموع</th>
          <th>الحالة</th>
          <th>التاريخ</th>
          <th>الإجراءات</th>
        </tr>
      </thead>
      <tbody>
        <?php if (empty($orders)): ?>
          <tr>
            <td colspan="6" style="text-align:center;color:gray;padding:24px">
              لا توجد طلبات حتى الآن
            </td>
          </tr>
        <?php else: ?>
          <?php foreach ($orders as $order): ?>
            <?php
              $items = json_decode($order['items'], true) ?? [];
              $itemsHTML = '';
              foreach ($items as $item) {
                $itemsHTML .= '<li>' . htmlspecialchars($item['name']) . ' (' . $item['qty'] . 'x)</li>';
              }
            ?>
            <tr>
              <td>#<?= $order['id'] ?></td>
              <td>
                <div class="items-list">
                  <ul>
                    <?= $itemsHTML ?>
                  </ul>
                </div>
              </td>
              <td><?= number_format($order['total'], 0) ?> درهم</td>
              <td>
                <span class="badge <?= $order['status'] ?>">
                  <?= $order['status'] === 'pending' ? 'قيد الانتظار' : 'مكتمل' ?>
                </span>
              </td>
              <td><?= date('d/m/Y H:i', strtotime($order['created_at'])) ?></td>
              <td>
                <?php if ($order['status'] === 'pending'): ?>
                  <a href="?status=done&id=<?= $order['id'] ?>" class="btn-done"
                     onclick="return confirm('تأكيد إتمام الطلب؟')">
                    <i class="fa-solid fa-check"></i> إتمام
                  </a>
                <?php else: ?>
                  <a href="?status=pending&id=<?= $order['id'] ?>" class="btn-pending">
                    <i class="fa-solid fa-hourglass"></i> قيد الانتظار
                  </a>
                <?php endif; ?>
                <a href="?delete=<?= $order['id'] ?>" class="btn-delete"
                   onclick="return confirm('هل أنت متأكد من حذف هذا الطلب؟')">
                  <i class="fa-solid fa-trash"></i> حذف
                </a>
              </td>
            </tr>
          <?php endforeach; ?>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>

</body>
</html>