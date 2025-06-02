<?php
session_start();
require_once __DIR__ . '/../api/config/database.php';

$role = $_SESSION['role'] ?? 'user';

// Ambil data bazaar (untuk dropdown pilihan di form tiket)
$bazaars = $pdo->query("SELECT BazaarID, BazaarName FROM Bazaar ORDER BY BazaarName")->fetchAll(PDO::FETCH_ASSOC);

// ==== CRUD TIKET ====

// --- Tambah Tiket ---
if ($role === 'admin' && isset($_POST['add_ticket'])) {
    $bazaar_id = (int)($_POST['bazaar_id'] ?? 0);
    $type      = trim($_POST['ticket_type'] ?? '');
    $price     = (int)($_POST['ticket_price'] ?? 0);
    $sold      = (int)($_POST['total_tickets_sold'] ?? 0);
    if ($bazaar_id && $type && $price >= 0) {
        $stmt = $pdo->prepare("INSERT INTO Ticket (BazaarID, TicketType, TicketPrice, TotalTicketsSold) VALUES (?,?,?,?)");
        $stmt->execute([$bazaar_id, $type, $price, $sold]);
        header("Location: tiket.php"); exit;
    }
}

// --- Edit Tiket ---
$edit_ticket = null;
if ($role === 'admin' && isset($_GET['edit_ticket'])) {
    $tid = (int)$_GET['edit_ticket'];
    $stmt = $pdo->prepare(
        "SELECT t.*, b.BazaarName FROM Ticket t LEFT JOIN Bazaar b ON t.BazaarID = b.BazaarID WHERE TicketID=?"
    );
    $stmt->execute([$tid]);
    $edit_ticket = $stmt->fetch(PDO::FETCH_ASSOC);
}
if ($role === 'admin' && isset($_POST['update_ticket'])) {
    $tid       = (int)$_POST['ticket_id'];
    $bazaar_id = (int)($_POST['bazaar_id'] ?? 0);
    $type      = trim($_POST['ticket_type'] ?? '');
    $price     = (int)($_POST['ticket_price'] ?? 0);
    $sold      = (int)($_POST['total_tickets_sold'] ?? 0);
    $stmt = $pdo->prepare("UPDATE Ticket SET BazaarID=?, TicketType=?, TicketPrice=?, TotalTicketsSold=? WHERE TicketID=?");
    $stmt->execute([$bazaar_id, $type, $price, $sold, $tid]);
    header("Location: tiket.php"); exit;
}

// --- Hapus Tiket ---
if ($role === 'admin' && isset($_GET['delete_ticket'])) {
    $tid = (int)$_GET['delete_ticket'];
    $stmt = $pdo->prepare("DELETE FROM Ticket WHERE TicketID=?");
    $stmt->execute([$tid]);
    header("Location: tiket.php"); exit;
}

// --- Data Tiket untuk Tabel (JOIN ke Bazaar) ---
$tickets = $pdo->query(
    "SELECT t.*, b.BazaarName FROM Ticket t LEFT JOIN Bazaar b ON t.BazaarID = b.BazaarID ORDER BY t.TicketID DESC"
)->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Tiket - Eventra</title>
  <link rel="stylesheet" href="../css/tiket.css" />
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
      <a href="tiket.php" style="font-weight:bold;">Tiket</a>
      <a href="produk.php">Produk</a>
      <a href="feedback.php">Feedback</a>
      <a href="account.php">Account</a>
    </nav>
  </header>
  <main>
    <section class="tiket-section">
      <h1>DAFTAR TIKET</h1>
      <table class="tiket-table">
        <thead>
          <tr>
            <th>Bazaar</th>
            <th>Jenis Tiket</th>
            <th>Harga</th>
            <th>Terjual</th>
            <?php if($role==='admin'): ?><th>Aksi</th><?php endif; ?>
            <th>Status</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach($tickets as $ticket): ?>
            <?php if($role==='admin' && isset($_GET['edit_ticket']) && $edit_ticket && $edit_ticket['TicketID']==$ticket['TicketID']): ?>
              <!-- EDIT FORM ROW -->
              <tr>
                <form method="POST" style="display:contents">
                  <input type="hidden" name="update_ticket" value="1">
                  <input type="hidden" name="ticket_id" value="<?= $ticket['TicketID'] ?>">
                  <td>
                    <select name="bazaar_id" required>
                      <option value="">Pilih Bazaar</option>
                      <?php foreach($bazaars as $b): ?>
                        <option value="<?= $b['BazaarID'] ?>" <?= $edit_ticket['BazaarID']==$b['BazaarID']?'selected':'' ?>>
                          <?= htmlspecialchars($b['BazaarName']) ?>
                        </option>
                      <?php endforeach; ?>
                    </select>
                  </td>
                  <td><input type="text" name="ticket_type" value="<?= htmlspecialchars($edit_ticket['TicketType']) ?>" required></td>
                  <td><input type="number" name="ticket_price" value="<?= (int)$edit_ticket['TicketPrice'] ?>" min="0" required></td>
                  <td><input type="number" name="total_tickets_sold" value="<?= (int)$edit_ticket['TotalTicketsSold'] ?>" min="0" required></td>
                  <td>
                    <button type="submit" class="buy-btn">Simpan</button>
                    <a href="tiket.php" class="buy-btn" style="background:#db2425;">Batal</a>
                  </td>
                  <td>
                    <?= ($edit_ticket['TotalTicketsSold'] < 100) ? '<span class="tersedia">Tersedia</span>' : '<span class="soldout">Sold Out</span>' ?>
                  </td>
                </form>
              </tr>
            <?php else: ?>
            <tr>
              <td><?= htmlspecialchars($ticket['BazaarName'] ?? '-') ?></td>
              <td><?= htmlspecialchars($ticket['TicketType']) ?></td>
              <td>Rp <?= number_format($ticket['TicketPrice'], 0, ',', '.') ?></td>
              <td><?= (int)$ticket['TotalTicketsSold'] ?></td>
              <?php if($role==='admin'): ?>
                <td>
                  <a href="tiket.php?edit_ticket=<?= $ticket['TicketID'] ?>" class="buy-btn">Edit</a>
                  <a href="tiket.php?delete_ticket=<?= $ticket['TicketID'] ?>" class="buy-btn" style="background:#db2425;" onclick="return confirm('Hapus tiket ini?')">Hapus</a>
                </td>
              <?php endif; ?>
              <td>
                <?php if ($ticket['TotalTicketsSold'] < 100): ?>
                  <span class="tersedia">Tersedia</span>
                  <?php if ($role !== 'admin' && isset($_SESSION['user_id'])): ?>
                    <form action="pembayaran.php" method="get" style="display:inline;">
                      <input type="hidden" name="ticket_id" value="<?= $ticket['TicketID'] ?>">
                      <button type="submit" class="buy-btn">Beli</button>
                    </form>
                  <?php elseif($role !== 'admin'): ?>
                    <a href="login.php" class="buy-btn">Login untuk Beli</a>
                  <?php endif; ?>
                <?php else: ?>
                  <span class="soldout">Sold Out</span>
                <?php endif; ?>
              </td>
            </tr>
            <?php endif; ?>
          <?php endforeach; ?>
        </tbody>
      </table>

      <!-- TAMBAH TIKET FORM (ADMIN ONLY) -->
      <?php if($role==='admin'): ?>
      <form method="POST" class="form-tambah-tiket" style="margin-top:30px;display:flex;gap:8px;align-items:center;justify-content:center;">
        <input type="hidden" name="add_ticket" value="1">
        <select name="bazaar_id" required style="padding:8px 12px;border-radius:6px;">
          <option value="">Pilih Bazaar</option>
          <?php foreach($bazaars as $b): ?>
            <option value="<?= $b['BazaarID'] ?>"><?= htmlspecialchars($b['BazaarName']) ?></option>
          <?php endforeach; ?>
        </select>
        <input type="text" name="ticket_type" placeholder="Jenis Tiket" required>
        <input type="number" name="ticket_price" placeholder="Harga Tiket" min="0" required>
        <input type="number" name="total_tickets_sold" placeholder="Total Terjual" min="0" required>
        <button type="submit" class="buy-btn">Tambah Tiket</button>
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
