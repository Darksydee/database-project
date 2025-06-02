<?php
session_start();
require_once __DIR__ . '/../api/config/database.php';

// Hanya admin yang boleh akses
if (!isset($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'admin') {
    header('Location: login.php');
    exit;
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama = trim($_POST['nama'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $notelp = trim($_POST['notelp'] ?? '');

    if (!$nama || !$email || !$password || !$notelp) {
        $error = 'Semua field wajib diisi!';
    } else {
        $cek = $pdo->prepare("SELECT * FROM User WHERE Email = ?");
        $cek->execute([$email]);
        if ($cek->fetch()) {
            $error = 'Email sudah terdaftar!';
        } else {
            $stmt = $pdo->prepare("INSERT INTO User (Nama, Email, Password, NoHandphone, Role) VALUES (?, ?, ?, ?, 'admin')");
            $stmt->execute([$nama, $email, $password, $notelp]);
            $success = 'Admin baru berhasil dibuat!';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Tambah Admin Baru - Eventra</title>
  <link rel="stylesheet" href="../css/add_admin.css" />
  <link href="https://fonts.googleapis.com/css?family=Poppins:400,700&display=swap" rel="stylesheet">
</head>
<body>
  <header>
    <div class="logo">
      <img src="../public/logo eventra.jpg" alt="Logo">
      <span>Eventra</span>
    </div>
    <nav>
      <a href="dashboard.php">Dashboard</a>
      <a href="tenant.php">Tenant</a>
      <a href="scan_qr.php">Scan QR</a>
      <a href="schedule.php">Jadwal</a>
      <a href="tiket.php">Tiket</a>
      <a href="produk.php">Produk</a>
      <a href="feedback.php">Feedback</a>
      <a href="account.php">Account</a>
      <a href="staff.php">Staff</a>
    </nav>
  </header>
  <main>
    <section class="add-admin-section">
      <h2>Tambah Admin Baru</h2>
      <?php if ($error): ?><div class="alert-danger"><?= $error ?></div><?php endif; ?>
      <?php if ($success): ?><div class="alert-success"><?= $success ?></div><?php endif; ?>
      <form method="post" class="form-admin" autocomplete="off">
        <label>Nama Lengkap</label>
        <input type="text" name="nama" required>
        <label>Email</label>
        <input type="email" name="email" required>
        <label>Password</label>
        <input type="password" name="password" required>
        <label>No. Handphone</label>
        <input type="text" name="notelp" required>
        <button type="submit">Tambah Admin</button>
      </form>
      <div style="margin-top:18px; text-align:center;">
        <a href="account.php" style="color:#000000;text-decoration:underline;font-weight:500;">&larr; Kembali ke Account</a>
      </div>
    </section>
  </main>
    <!-- FOOTER -->
  <footer class="footer-section">
    <div class="footer-top">
      <h2>NEED ASSISTANCE FOR YOUR EVENT?</h2>
      <div class="footer-logo-contact">
        <div class="footer-logo">
          <img src="../public/logo eventra.jpg" alt="Eventra Logo">
          <h3>EVENTRA<br><span>Event Management</span></h3>
        </div>
        <div class="footer-contact">
          <p><strong>CALL US THROUGH:</strong></p>
          <p>WHATSAPP NUMBER: 081234567890 (DINDA)</p>
          <p>EMAIL: eventra.management@gmail.com</p>
          <p>
            PRADITA UNIVERSITY, Scientia Business Park,<br>
            Jl. Gading Serpong Boulevard No.1 Tower 1,<br>
            Curug Sangereng, Kec. Klp. Dua,<br>
            Kabupaten Tangerang, Banten 15810
          </p>
        </div>
      </div>
    </div>
    <div class="footer-bottom">
      <p>&copy; 2025 Eventra. All rights reserved.</p>
    </div>
  </footer>
</body>
</html>
