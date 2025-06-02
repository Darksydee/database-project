<?php
require_once __DIR__ . '/../api/config/database.php';

$success = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nama = trim($_POST['nama'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $komentar = trim($_POST['feedback'] ?? '');
    $rating = intval($_POST['rating'] ?? 5);

    if (!$nama || !$komentar) {
        $error = "Nama dan feedback wajib diisi!";
    } else {
        // Default BazaarID: 1 (atau bisa dinamis jika banyak event)
        $bazaarID = 1;
        $stmt = $pdo->prepare("INSERT INTO Feedback (BazaarID, VisitorName, Comments, Rating) VALUES (?, ?, ?, ?)");
        if ($stmt->execute([$bazaarID, $nama, $komentar, $rating])) {
            $success = "Terima kasih atas feedback Anda!";
        } else {
            $error = "Gagal mengirim feedback. Silakan coba lagi.";
        }
    }
}

// Ambil 5 feedback terbaru
$recent = $pdo->query("SELECT VisitorName, Comments, Rating, CreatedAt FROM Feedback ORDER BY FeedbackID DESC LIMIT 5")->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Feedback - Eventra</title>
  <link rel="stylesheet" href="../css/feedback.css" />
  <link href="https://fonts.googleapis.com/css?family=Poppins:700,400&display=swap" rel="stylesheet">
  <style>
    .recent-feedback {margin-top:30px;}
    .recent-feedback h3 {font-size:1.1rem;margin-bottom:8px;}
    .recent-feedback .fb {border-bottom:1px solid #eee;margin-bottom:10px;padding-bottom:7px;}
    .recent-feedback .fb b {font-size:1rem;}
    .recent-feedback .fb span {color:#ffe65d;font-size:1.1em;}
    .recent-feedback .fb p {margin:4px 0;}
    .recent-feedback .fb .tgl {font-size:0.88em;color:#777;}
    .alert {padding:8px 0;}
  </style>
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
      <a href="feedback.php" style="font-weight:bold">Feedback</a>
      <a href="account.php">Account</a>
    </nav>
  </header>

  <main>
    <section class="feedback-section">
      <h1>FEEDBACK</h1>
      <p class="feedback-desc">Kami sangat menghargai masukan dan saran Anda.<br>Silakan tinggalkan feedback untuk Eventra!</p>

      <?php if($success): ?>
        <div class="alert" style="color:green"><?= $success ?></div>
      <?php elseif($error): ?>
        <div class="alert" style="color:#db2425"><?= $error ?></div>
      <?php endif; ?>

      <form class="feedback-form" method="post" action="">
        <input type="text" name="nama" placeholder="Nama Anda" required>
        <input type="email" name="email" placeholder="Email (Opsional)">
        <textarea name="feedback" placeholder="Tulis feedback Anda di sini..." rows="5" required></textarea>
        <label for="rating" style="margin-top:10px;display:block;">Rating:</label>
        <select name="rating" id="rating" style="margin-bottom:10px;">
          <option value="5">⭐⭐⭐⭐⭐</option>
          <option value="4">⭐⭐⭐⭐</option>
          <option value="3">⭐⭐⭐</option>
          <option value="2">⭐⭐</option>
          <option value="1">⭐</option>
        </select>
        <button type="submit">Kirim Feedback</button>
      </form>

      <?php if($recent): ?>
      <div class="recent-feedback">
        <h3>Feedback Terbaru</h3>
        <?php foreach($recent as $f): ?>
          <div class="fb">
            <b><?= htmlspecialchars($f['VisitorName']) ?></b>
            <span><?= str_repeat("★", (int)$f['Rating']) ?></span>
            <p><?= nl2br(htmlspecialchars($f['Comments'])) ?></p>
            <span class="tgl"><?= date('d/m/Y H:i', strtotime($f['CreatedAt'] ?? '')) ?></span>
          </div>
        <?php endforeach; ?>
      </div>
      <?php endif; ?>
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
