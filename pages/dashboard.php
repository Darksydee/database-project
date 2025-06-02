<?php
require_once __DIR__ . '/../api/config/database.php';

// Ambil event utama (bazaar terbaru)
$stmt = $pdo->query("SELECT * FROM Bazaar ORDER BY BazaarDate DESC LIMIT 1");
$bazaar = $stmt->fetch(PDO::FETCH_ASSOC);

// Ambil daftar tiket event ini
$tiketStmt = $pdo->prepare("SELECT * FROM Ticket WHERE BazaarID = ?");
$tiketStmt->execute([$bazaar['BazaarID']]);
$tickets = $tiketStmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Eventra - Dashboard</title>
  <link rel="stylesheet" href="../css/dashboard.css" />
  <link href="https://fonts.googleapis.com/css?family=Poppins:400,700&display=swap" rel="stylesheet">
</head>
<body>
  <!-- HEADER -->
  <header>
    <div class="logo">
      <img src="../public/logo eventra.jpg" alt="Logo">
      <span>Eventra</span>
    </div>
    <nav>
      <a href="dashboard.php" style="font-weight:bold">DASHBOARD</a>
      <a href="tenant.php">TENANT</a>
      <a href="scan_qr.php">SCAN QR</a>
      <a href="schedule.php">JADWAL</a>
      <a href="tiket.php">TIKET</a>
      <a href="produk.php">PRODUK</a>
      <a href="feedback.php">FEEDBACK</a>
      <a href="account.php">ACCOUNT</a>
    </nav>
  </header>

  <!-- POSTER & BUY TICKET -->
  <section class="poster-section">
    <div class="poster-wrapper">
      <!-- Poster utama, gunakan field gambar jika ada, jika tidak pakai default -->
      <img src="../public/event.png" alt="Poster Event" />
      <div class="timer-box"></div>
      <button class="buy-button" onclick="window.location.href='tiket.php'">BUY TICKET</button>
    </div>
  </section>

  <!-- TICKET TABLE (dinamis dari database) -->
  <section class="tiket-section" style="background:#fff;max-width:600px;margin:40px auto 16px auto;border-radius:14px;box-shadow:0 2px 14px rgba(0,0,0,0.09);padding:28px;">
    <h2 style="color:#185b35;font-size:1.5rem;margin-bottom:18px;">Daftar Tiket</h2>
    <table class="tiket-table" style="width:100%;border-radius:8px;overflow:hidden;">
      <thead>
        <tr>
          <th>Jenis Tiket</th>
          <th>Harga</th>
          <th>Status</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($tickets as $t): ?>
          <tr>
            <td><?= htmlspecialchars($t['TicketType']) ?></td>
            <td>Rp <?= number_format($t['TicketPrice'],0,',','.') ?></td>
            <td>
              <?php
                if ($t['TotalTicketsSold'] < 100) echo '<span style="color:#27ae60;font-weight:700;">Tersedia</span>';
                else echo '<span style="color:#db2425;font-weight:700;">Sold Out</span>';
              ?>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </section>

  <!-- EVENT INFO (Poster) -->
  <section class="event-info-section">
    <img src="../public/main_event.jpg" alt="Jakarta Fair Poster" class="jakarta-fair-poster" style="width:100%;max-width:900px;border-radius:18px;box-shadow:0 4px 28px rgba(0,0,0,0.17);margin:0 auto;display:block;" />
  </section>

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
