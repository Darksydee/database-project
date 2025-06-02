<?php
require_once __DIR__ . '/../api/config/database.php';

$error = '';
$success = '';
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nama = trim($_POST['fullname'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    $nohp = trim($_POST['nohp'] ?? '');

    if ($password !== $confirm_password) {
        $error = "Password dan konfirmasi tidak sama!";
    } elseif (empty($nohp)) {
        $error = "No. Handphone harus diisi!";
    } else {
        // Cek apakah email sudah ada
        $cek = $pdo->prepare("SELECT * FROM User WHERE Email = ?");
        $cek->execute([$email]);
        if ($cek->fetch()) {
            $error = "Email sudah terdaftar!";
        } else {
            // (Demo: simpan plain, produksi wajib hash)
            $stmt = $pdo->prepare("INSERT INTO User (Nama, Email, Password, NoHandphone) VALUES (?, ?, ?, ?)");
            $stmt->execute([$nama, $email, $password, $nohp]);
            header("Location: login.php?success=1");
            exit();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Sign Up - Eventra</title>
  <link rel="stylesheet" href="../css/sign_up.css" />
</head>
<body>
  <div class="signup-header">
    <img src="../public/logo eventra.jpg" class="eventra-logo" alt="Logo Eventra">
    <div>
      <span class="eventra-title">EVENTRA</span><br>
      <span class="eventra-subtitle">Event Management</span>
    </div>
  </div>
  <p class="signup-instructions"><b>Please fill this registration form to continue</b></p>
  <?php if($error): ?><div class="error-msg" style="color:#db2425;margin-bottom:12px"><?= $error ?></div><?php endif; ?>
  <?php if($success): ?><div class="success-msg" style="color:green;margin-bottom:12px"><?= $success ?></div><?php endif; ?>
  <form class="signup-form" method="POST">
    <label for="fullname">Full Name:</label>
    <input type="text" name="fullname" id="fullname" required />
    <label for="email">Email:</label>
    <input type="email" name="email" id="email" required />
    <label for="password">Password:</label>
    <input type="password" name="password" id="password" required />
    <label for="confirm_password">Confirm Password:</label>
    <input type="password" name="confirm_password" id="confirm_password" required />
    <label for="nohp">No. Handphone:</label>
    <input type="text" name="nohp" id="nohp" required />
    <button type="submit" class="signup-btn">SUBMIT REGISTRATION FORM</button>
  </form>
      <div class="signin-link">
      Sudah punya akun? <a href="signin.php">Login</a>
    </div>
      <div style="text-align:left; margin-top:8px;">
    <a href="dashboard.php" class="back-btn">← Kembali ke Dashboard</a>
  </div>
  <img src="../public/dekorasi-kanan-atas.png" class="decor-top-right" alt="" />
  <img src="../public/dekorasi-kanan-bawah.png" class="decor-bottom-right" alt="" />
</body>
</html>
