<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php?next=' . urlencode($_SERVER['REQUEST_URI']));
    exit;
}
require_once __DIR__ . '/../api/config/database.php';

$user_id = $_SESSION['user_id'];
// Ambil data user
$user_stmt = $pdo->prepare("SELECT Nama, Email FROM User WHERE UserID = ?");
$user_stmt->execute([$user_id]);
$user_data = $user_stmt->fetch(PDO::FETCH_ASSOC);

// Cek apakah tiket atau produk yang dibeli
$type = '';
$data = [];
$price = 0;
$name_label = '';

if (isset($_GET['ticket_id'])) {
    $type = 'ticket';
    $stmt = $pdo->prepare("SELECT TicketID, TicketType AS Name, TicketPrice AS Harga, TotalTicketsSold FROM Ticket WHERE TicketID = ?");
    $stmt->execute([$_GET['ticket_id']]);
    $data = $stmt->fetch(PDO::FETCH_ASSOC);
    $price = $data['Harga'] ?? 0;
    $name_label = $data['Name'] ?? '';
} elseif (isset($_GET['product_id'])) {
    $type = 'product';
    $stmt = $pdo->prepare("SELECT ProductID, Name, Harga, Stok FROM Product WHERE ProductID = ?");
    $stmt->execute([$_GET['product_id']]);
    $data = $stmt->fetch(PDO::FETCH_ASSOC);
    $price = $data['Harga'] ?? 0;
    $name_label = $data['Name'] ?? '';
}

$methods = $pdo->query("SELECT PaymentMethodID, MethodName FROM PaymentMethod")->fetchAll(PDO::FETCH_ASSOC);

$success_message = '';
$error_message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $selected_method = $_POST['payment_method'] ?? '';
    $nama = trim($_POST['nama'] ?? '');
    $email = trim($_POST['email'] ?? '');

    if (!$type || !$data || !$selected_method || !$nama || !$email) {
        $error_message = "Mohon lengkapi semua field!";
    } else {
        // Validasi stok sebelum insert!
        if ($type == 'ticket') {
            // Ambil stok tiket saat ini
            $ticketCheck = $pdo->prepare("SELECT TotalTicketsSold FROM Ticket WHERE TicketID = ?");
            $ticketCheck->execute([$data['TicketID']]);
            $ticket = $ticketCheck->fetch(PDO::FETCH_ASSOC);

            // Maksimal 100, silakan sesuaikan dengan limit real
            if ($ticket['TotalTicketsSold'] >= 100) {
                $error_message = "Maaf, tiket sudah habis!";
            } else {
                // 1. Insert transaksi tiket (PASTIKAN UserID masuk!)
                $trx_stmt = $pdo->prepare("INSERT INTO `Transaction` 
                    (TicketID, UserID, TransactionDate, Quantity, TotalAmount, PaymentMethodID, PaymentStatus)
                    VALUES (?, ?, NOW(), 1, ?, ?, 'Completed')");
                $trx_stmt->execute([
                    $data['TicketID'],
                    $user_id,
                    $price,
                    $selected_method
                ]);
                // 2. Update ticket sold
                $update_stmt = $pdo->prepare("UPDATE Ticket SET TotalTicketsSold = TotalTicketsSold + 1 WHERE TicketID = ?");
                $update_stmt->execute([$data['TicketID']]);
                $success_message = "Pembayaran berhasil! Transaksi sudah tercatat.";
            }
        } elseif ($type == 'product') {
            // Ambil stok produk saat ini
            $productCheck = $pdo->prepare("SELECT Stok FROM Product WHERE ProductID = ?");
            $productCheck->execute([$data['ProductID']]);
            $product = $productCheck->fetch(PDO::FETCH_ASSOC);

            if ($product['Stok'] <= 0) {
                $error_message = "Maaf, stok produk habis!";
            } else {
                // 1. Insert transaksi produk (PASTIKAN UserID masuk!)
                $trx_stmt = $pdo->prepare("INSERT INTO ProductTransaction 
                    (ProductID, UserID, TransactionDate, Quantity, TotalAmount, PaymentMethodID, PaymentStatus)
                    VALUES (?, ?, NOW(), 1, ?, ?, 'Completed')");
                $trx_stmt->execute([
                    $data['ProductID'],
                    $user_id,
                    $price,
                    $selected_method
                ]);
                // 2. Update stok produk
                $update_stmt = $pdo->prepare("UPDATE Product SET Stok = Stok - 1 WHERE ProductID = ? AND Stok > 0");
                $update_stmt->execute([$data['ProductID']]);
                $success_message = "Pembayaran berhasil! Transaksi sudah tercatat.";
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Pembayaran - Eventra</title>
  <link rel="stylesheet" href="../css/pembayaran.css" />
  <link href="https://fonts.googleapis.com/css?family=Poppins:700,400&display=swap" rel="stylesheet">
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
    </nav>
  </header>
  <main>
    <section class="pembayaran-section">
      <h1>PEMBAYARAN</h1>
      <?php if ($success_message): ?>
        <div class="alert alert-success" style="color:green; margin-bottom:14px;"><?= $success_message ?></div>
        <a href="account.php" class="btn" style="background:#27ae60;color:#fff;padding:10px 18px;border-radius:8px;text-decoration:none;">Lihat Riwayat</a>
      <?php else: ?>
        <?php if ($error_message): ?>
          <div class="alert alert-danger" style="color:#db2425; margin-bottom:14px;"><?= $error_message ?></div>
        <?php endif; ?>
        <form class="pembayaran-form" method="post" action="">
          <label><?= $type == 'ticket' ? 'Jenis Tiket' : 'Nama Produk' ?></label>
          <input type="text" value="<?= htmlspecialchars($name_label) ?>" readonly>
          <label>Harga</label>
          <input type="text" value="Rp <?= number_format($price, 0, ',', '.') ?>" readonly>
          <label>Metode Pembayaran</label>
          <select name="payment_method" required>
            <option value="">Pilih Metode Pembayaran</option>
            <?php foreach($methods as $m): ?>
              <option value="<?= $m['PaymentMethodID'] ?>"><?= htmlspecialchars($m['MethodName']) ?></option>
            <?php endforeach; ?>
          </select>
          <label>Nama Pemilik</label>
          <input type="text" name="nama" placeholder="Nama Anda" required value="<?= htmlspecialchars($user_data['Nama'] ?? '') ?>">
          <label>Email</label>
          <input type="email" name="email" placeholder="Email" required value="<?= htmlspecialchars($user_data['Email'] ?? '') ?>">
          <button type="submit">Bayar <?= $type == 'ticket' ? 'Tiket' : 'Produk' ?></button>
        </form>
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
