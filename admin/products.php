<?php
session_start();
if (!isset($_SESSION['admin'])) {
    header('Location: login.php');
    exit;
}

require_once '../db.php';

$success = "";

// ── حذف منتج ──
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    $stmt = $pdo->prepare("SELECT image FROM products WHERE id = ?");
    $stmt->execute([$id]);
    $prod = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($prod['image'] && file_exists('../imgs/' . $prod['image'])) {
        unlink('../imgs/' . $prod['image']);
    }
    $pdo->prepare("DELETE FROM products WHERE id = ?")->execute([$id]);
    $success = "تم حذف المنتج بنجاح ✅";
}

// ── إضافة / تعديل ──
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id       = !empty($_POST['id']) ? (int)$_POST['id'] : null;
    $name     = trim($_POST['name']);
    $category = trim($_POST['category']);
    $price    = (float)$_POST['price'];
    $unit     = trim($_POST['unit']);
    $desc     = trim($_POST['description']);
    $image    = $_POST['current_image'] ?? null;

    if (!empty($_FILES['image']['name'])) {
        $ext      = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
        $filename = uniqid('prod_') . '.' . $ext;
        $dest     = '../imgs/' . $filename;
        if (!is_dir('../imgs')) mkdir('../imgs', 0755, true);
        if (move_uploaded_file($_FILES['image']['tmp_name'], $dest)) {
            if ($image && file_exists('../imgs/' . $image)) unlink('../imgs/' . $image);
            $image = $filename;
        }
    }

    if ($id) {
        $stmt = $pdo->prepare("
            UPDATE products
            SET name=?, category=?, price=?, unit=?, description=?, image=?
            WHERE id=?
        ");
        $stmt->execute([$name, $category, $price, $unit, $desc, $image, $id]);
        $success = "تم تعديل المنتج بنجاح ✅";
    } else {
        $stmt = $pdo->prepare("
            INSERT INTO products (name, category, price, unit, description, image)
            VALUES (?, ?, ?, ?, ?, ?)
        ");
        $stmt->execute([$name, $category, $price, $unit, $desc, $image]);
        $success = "تمت إضافة المنتج بنجاح ✅";
    }
}

$products = $pdo->query("SELECT * FROM products ORDER BY id DESC")->fetchAll(PDO::FETCH_ASSOC);

$editProduct = null;
if (isset($_GET['edit'])) {
    $stmt = $pdo->prepare("SELECT * FROM products WHERE id = ?");
    $stmt->execute([(int)$_GET['edit']]);
    $editProduct = $stmt->fetch(PDO::FETCH_ASSOC);
}
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
  <meta charset="UTF-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>المنتجات | PARFUM DS</title>
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
    .alert-success {
      background: rgba(37,211,102,.1);
      border: 1px solid rgba(37,211,102,.3);
      border-radius: 8px; color: #25D366;
      padding: 10px 16px; margin-bottom: 18px;
    }
    .form-box {
      background: #1a1409;
      border: 1px solid rgba(201,168,76,.15);
      border-radius: 12px; padding: 24px; margin-bottom: 28px;
    }
    .form-box h2 { color: #c9a84c; font-size: 1rem; margin-bottom: 18px; }
    .form-grid {
      display: grid;
      grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
      gap: 14px;
    }
    .form-group { display: flex; flex-direction: column; gap: 6px; }
    .form-group label { font-size: .82rem; color: #c9a84c; }
    .form-group input,
    .form-group select,
    .form-group textarea {
      background: rgba(255,255,255,.04);
      border: 1px solid rgba(201,168,76,.25);
      border-radius: 8px; color: #f5edd8;
      font-size: .9rem; padding: 10px 12px;
      outline: none; font-family: 'Tajawal', sans-serif;
      transition: border-color .2s;
    }
    .form-group input:focus,
    .form-group select:focus,
    .form-group textarea:focus { border-color: #c9a84c; }
    .form-group textarea { resize: vertical; min-height: 70px; }
    .form-group select option { background: #1a1409; }
    .img-preview {
      width: 80px; height: 80px; object-fit: cover;
      border-radius: 8px; border: 1px solid rgba(201,168,76,.2);
      margin-top: 6px;
    }
    .form-actions { margin-top: 16px; display: flex; gap: 10px; }
    .btn-save {
      background: linear-gradient(135deg, #c9a84c, #e8cc80);
      color: #0d0a06; border: none; border-radius: 8px;
      padding: 10px 24px; font-size: .95rem; font-weight: bold;
      cursor: pointer; transition: opacity .2s;
    }
    .btn-save:hover { opacity: .85; }
    .btn-cancel {
      background: transparent;
      border: 1px solid rgba(201,168,76,.3);
      color: #d4c4a0; border-radius: 8px;
      padding: 10px 20px; font-size: .95rem;
      cursor: pointer; text-decoration: none;
      display: flex; align-items: center;
    }
    .table-wrap {
      background: #1a1409;
      border: 1px solid rgba(201,168,76,.15);
      border-radius: 12px; overflow: hidden;
    }
    table { width: 100%; border-collapse: collapse; }
    th {
      background: rgba(201,168,76,.08); color: #c9a84c;
      font-size: .82rem; padding: 12px 16px; text-align: right;
    }
    td {
      padding: 12px 16px; font-size: .88rem;
      border-top: 1px solid rgba(255,255,255,.04); color: #d4c4a0;
    }
    .prod-img {
      width: 50px; height: 50px; object-fit: cover;
      border-radius: 8px; border: 1px solid rgba(201,168,76,.2);
    }
    .no-img {
      width: 50px; height: 50px; background: #241d0e;
      border-radius: 8px; display: flex;
      align-items: center; justify-content: center; font-size: 1.4rem;
    }
    .btn-edit {
      background: rgba(201,168,76,.15);
      border: 1px solid rgba(201,168,76,.3);
      color: #c9a84c; border-radius: 6px;
      padding: 5px 12px; font-size: .8rem;
      cursor: pointer; text-decoration: none; margin-left: 6px;
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
  <a href="products.php" class="active"><i class="fa-solid fa-box"></i> المنتجات</a>
  <a href="orders.php"><i class="fa-solid fa-bag-shopping"></i> الطلبات</a>
  <div class="logout">
    <a href="logout.php"><i class="fa-solid fa-right-from-bracket"></i> تسجيل الخروج</a>
  </div>
</div>

<div class="main">
  <h1 class="page-title"><i class="fa-solid fa-box"></i> إدارة المنتجات</h1>

  <?php if ($success): ?>
    <div class="alert-success"><?= $success ?></div>
  <?php endif; ?>

  <div class="form-box">
    <h2>
      <i class="fa-solid fa-<?= $editProduct ? 'pen' : 'plus' ?>"></i>
      <?= $editProduct ? 'تعديل المنتج' : 'إضافة منتج جديد' ?>
    </h2>
    <form method="POST" enctype="multipart/form-data">
      <?php if ($editProduct): ?>
        <input type="hidden" name="id" value="<?= $editProduct['id'] ?>"/>
        <input type="hidden" name="current_image" value="<?= $editProduct['image'] ?>"/>
      <?php endif; ?>

      <div class="form-grid">
        <div class="form-group">
          <label>اسم المنتج</label>
          <input type="text" name="name" required
            placeholder="مثال: عود ملكي"
            value="<?= $editProduct['name'] ?? '' ?>"/>
        </div>
        <div class="form-group">
          <label>الفئة</label>
          <select name="category">
            <option value="رجالي" <?= ($editProduct['category'] ?? '') === 'رجالي' ? 'selected' : '' ?>>رجالي</option>
            <option value="نسائي" <?= ($editProduct['category'] ?? '') === 'نسائي' ? 'selected' : '' ?>>نسائي</option>
            <option value="مختلط" <?= ($editProduct['category'] ?? '') === 'مختلط' ? 'selected' : '' ?>>مختلط</option>
          </select>
        </div>
        <div class="form-group">
          <label>السعر (درهم)</label>
          <input type="number" name="price" required
            placeholder="مثال: 350"
            value="<?= $editProduct['price'] ?? '' ?>"/>
        </div>
        <div class="form-group">
          <label>الحجم</label>
          <input type="text" name="unit"
            placeholder="مثال: 100ml"
            value="<?= $editProduct['unit'] ?? '' ?>"/>
        </div>
        <div class="form-group" style="grid-column: 1/-1">
          <label>الوصف</label>
          <textarea name="description"
            placeholder="وصف مختصر للعطر..."><?= $editProduct['description'] ?? '' ?></textarea>
        </div>
        <div class="form-group" style="grid-column: 1/-1">
          <label>صورة المنتج</label>
          <input type="file" name="image" accept="image/*"/>
          <?php if (!empty($editProduct['image'])): ?>
            <img src="../imgs/<?= $editProduct['image'] ?>"
                 class="img-preview" alt="صورة المنتج"/>
          <?php endif; ?>
        </div>
      </div>

      <div class="form-actions">
        <button type="submit" class="btn-save">
          <i class="fa-solid fa-floppy-disk"></i>
          <?= $editProduct ? 'حفظ التعديلات' : 'إضافة المنتج' ?>
        </button>
        <?php if ($editProduct): ?>
          <a href="products.php" class="btn-cancel">إلغاء</a>
        <?php endif; ?>
      </div>
    </form>
  </div>

  <div class="table-wrap">
    <table>
      <thead>
        <tr>
          <th>الصورة</th>
          <th>اسم المنتج</th>
          <th>الفئة</th>
          <th>السعر</th>
          <th>الحجم</th>
          <th>الإجراءات</th>
        </tr>
      </thead>
      <tbody>
        <?php if (empty($products)): ?>
          <tr>
            <td colspan="6" style="text-align:center;color:gray;padding:24px">
              لا توجد منتجات — أضف منتجاً جديداً من الأعلى
            </td>
          </tr>
        <?php else: ?>
          <?php foreach ($products as $p): ?>
            <tr>
              <td>
                <?php if ($p['image']): ?>
                  <img src="../imgs/<?= $p['image'] ?>" class="prod-img" alt=""/>
                <?php else: ?>
                  <div class="no-img">🏺</div>
                <?php endif; ?>
              </td>
              <td><?= htmlspecialchars($p['name']) ?></td>
              <td><?= $p['category'] ?></td>
              <td><?= number_format($p['price'], 0) ?> درهم</td>
              <td><?= $p['unit'] ?></td>
              <td>
                <a href="?edit=<?= $p['id'] ?>" class="btn-edit">
                  <i class="fa-solid fa-pen"></i> تعديل
                </a>
                <a href="?delete=<?= $p['id'] ?>"
                   onclick="return confirm('هل أنت متأكد من حذف هذا المنتج؟')"
                   class="btn-delete">
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