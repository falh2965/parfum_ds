<?php
session_start();

// إلا كان مسجل دخول ردو للداشبورد
if (isset($_SESSION['admin'])) {
    header('Location: dashboard.php');
    exit;
}

$error = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require_once '../db.php';

    $username = trim($_POST['username']);
    $password = trim($_POST['password']);

    // نجيبو الأدمين من قاعدة البيانات
    $stmt = $pdo->prepare("SELECT * FROM admins WHERE username = :username");
    $stmt->execute([':username' => $username]);
    $admin = $stmt->fetch(PDO::FETCH_ASSOC);

    // تحقق من الباسوورد
    if ($admin && password_verify($password, $admin['password'])) {
        $_SESSION['admin'] = $admin['username'];
        header('Location: dashboard.php');
        exit;
    } else {
        $error = "اسم المستخدم أو كلمة السر خاطئة";
    }
}
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
  <meta charset="UTF-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>دخول الأدمين | PARFUM DS</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css"/>
  <style>
    *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

    body {
      font-family: 'Tajawal', sans-serif;
      background: #0d0a06;
      color: #d4c4a0;
      min-height: 100vh;
      display: flex;
      align-items: center;
      justify-content: center;
    }

    .login-box {
      background: #1a1409;
      border: 1px solid rgba(201,168,76,.2);
      border-radius: 16px;
      padding: 40px;
      width: 100%;
      max-width: 380px;
    }

    .login-box h1 {
      text-align: center;
      color: #c9a84c;
      font-size: 1.5rem;
      margin-bottom: 8px;
    }

    .login-box p {
      text-align: center;
      font-size: .85rem;
      color: rgba(212,196,160,.5);
      margin-bottom: 28px;
    }

    .form-group {
      margin-bottom: 16px;
    }

    .form-group label {
      display: block;
      font-size: .85rem;
      color: #c9a84c;
      margin-bottom: 6px;
    }

    .form-group input {
      width: 100%;
      background: rgba(255,255,255,.04);
      border: 1px solid rgba(201,168,76,.25);
      border-radius: 8px;
      color: #f5edd8;
      font-size: .95rem;
      padding: 11px 14px;
      outline: none;
      transition: border-color .2s;
    }

    .form-group input:focus {
      border-color: #c9a84c;
    }

    .error {
      background: rgba(255,80,80,.1);
      border: 1px solid rgba(255,80,80,.3);
      border-radius: 8px;
      color: #ff6b6b;
      font-size: .85rem;
      padding: 10px 14px;
      margin-bottom: 16px;
      text-align: center;
    }

    .btn-login {
      width: 100%;
      background: linear-gradient(135deg, #c9a84c, #e8cc80);
      color: #0d0a06;
      border: none;
      border-radius: 10px;
      padding: 13px;
      font-size: 1rem;
      font-weight: bold;
      cursor: pointer;
      margin-top: 8px;
      transition: opacity .2s, transform .2s;
    }

    .btn-login:hover {
      opacity: .9;
      transform: scale(1.02);
    }
  </style>
</head>
<body>

<div class="login-box">
  <h1><i class="fa-solid fa-lock"></i> لوحة الإدارة</h1>
  <p>PARFUM DS · Admin Panel</p>

  <?php if ($error): ?>
    <div class="error"><?= $error ?></div>
  <?php endif; ?>

  <form method="POST">
    <div class="form-group">
      <label>اسم المستخدم</label>
      <input type="text" name="username" placeholder="admin" required/>
    </div>
    <div class="form-group">
      <label>كلمة السر</label>
      <input type="password" name="password" placeholder="••••••••" required/>
    </div>
    <button type="submit" class="btn-login">
      <i class="fa-solid fa-right-to-bracket"></i> دخول
    </button>
  </form>
</div>

</body>
</html>