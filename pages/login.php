<?php
session_start();
require_once __DIR__ . '/../api/config/database.php';

$error = '';
  if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $stmt = $pdo->prepare("SELECT * FROM User WHERE Email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    // Untuk demo, password masih plain (harusnya pakai hash)
  if ($user && $user['Password'] == $password) {
    $_SESSION['user_id'] = $user['UserID'];
    $_SESSION['user_nama'] = $user['Nama'];
    $_SESSION['role'] = $user['Role'];
    header("Location: dashboard.php"); // SEMUA ROLE MASUK KE ACCOUNT.PHP
    exit();
  } else {
    $error = "Email atau password salah.";
  }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Login - Eventra</title>
  <link rel="stylesheet" href="../css/login.css" />
</head>
<?php if(isset($_GET['success'])): ?>
<script>
  window.onload = function() {
    alert('Registrasi berhasil! Silakan login.');
  }
</script>
<?php endif; ?>
<body>
  <div class="login-container">
    <div class="login-header">
      <img src="../public/logo eventra.jpg" alt="Eventra Logo" class="eventra-logo" />
      <div>
        <span class="eventra-title">EVENTRA</span><br>
        <span class="eventra-subtitle">Event Management</span>
      </div>
    </div>
    <p class="login-instructions">Please fill this registration form to continue</p>
    <?php if($error): ?><div class="error-msg" style="color:#db2425;margin-bottom:12px"><?= $error ?></div><?php endif; ?>
    <form class="login-form" method="POST">
      <label for="email">Email:</label>
      <input type="email" name="email" id="email" required />
      <label for="password">Password:</label>
      <input type="password" name="password" id="password" required />
      <div class="remember-row">
        <input type="checkbox" id="remember" />
        <label for="remember">Remember me</label>
      </div>
      <button type="submit" class="login-btn">LOGIN ACCOUNT</button>
    </form>
    <div class="signup-link">
      Belum punya akun? <a href="signup.php">Daftar di sini</a>
    </div>
      <div style="text-align:center; margin-top:8px;">
    <a href="dashboard.php" class="back-btn">← Kembali ke Dashboard</a>
  </div>
  </div>
  <img src="../public/dekorasi-kanan-atas.png" alt="" class="decor-top-right" />
  <img src="../public/dekorasi-kanan-bawah.png" alt="" class="decor-bottom-right" />
</body>
</html>
