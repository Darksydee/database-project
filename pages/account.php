<?php
session_start();
require_once __DIR__ . '/../api/config/database.php';

$user_id = $_SESSION['user_id'] ?? null;
$role = $_SESSION['role'] ?? 'user';

if (!$user_id) {
    header('Location: login.php');
    exit;
}

// Ambil info user
$user_stmt = $pdo->prepare("SELECT Nama, Email, NoHandphone, CreatedAt, Role, Password FROM User WHERE UserID = ?");
$user_stmt->execute([$user_id]);
$user_data = $user_stmt->fetch(PDO::FETCH_ASSOC);

// ==== EDIT PROFILE (ADMIN ATAU USER BIASA) ====
$edit_msg = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['edit_profil'])) {
    $new_nama = trim($_POST['nama'] ?? '');
    $new_email = trim($_POST['email'] ?? '');
    $new_nohp = trim($_POST['nohp'] ?? '');
    $new_pass = $_POST['password'] ?? '';

    // Validasi email unik
    $cek_email = $pdo->prepare("SELECT UserID FROM User WHERE Email = ? AND UserID != ?");
    $cek_email->execute([$new_email, $user_id]);
    if ($cek_email->fetch()) {
        $edit_msg = "Email sudah digunakan!";
    } else {
        if ($new_pass) {
            $update = $pdo->prepare("UPDATE User SET Nama=?, Email=?, NoHandphone=?, Password=? WHERE UserID=?");
            $update->execute([$new_nama, $new_email, $new_nohp, $new_pass, $user_id]);
        } else {
            $update = $pdo->prepare("UPDATE User SET Nama=?, Email=?, NoHandphone=? WHERE UserID=?");
            $update->execute([$new_nama, $new_email, $new_nohp, $user_id]);
        }
        $edit_msg = "Profil berhasil diupdate!";
        $_SESSION['user_nama'] = $new_nama;
    }
    // Refresh data user setelah update
    $user_stmt->execute([$user_id]);
    $user_data = $user_stmt->fetch(PDO::FETCH_ASSOC);
}

// --- CRUD ADMIN SECTION ---
$admin_msg = '';
$edit_admin_id = $_GET['edit_admin'] ?? null;

// Edit admin dari list
if ($role === 'admin' && $_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['edit_admin_id'])) {
    $eid = $_POST['edit_admin_id'];
    $nama = trim($_POST['edit_nama_admin'] ?? '');
    $email = trim($_POST['edit_email_admin'] ?? '');
    $nohp = trim($_POST['edit_phone_admin'] ?? '');
    $pw = $_POST['edit_password_admin'] ?? '';
    if ($nama && $email) {
        if ($pw) {
            $stmt = $pdo->prepare("UPDATE User SET Nama=?, Email=?, NoHandphone=?, Password=? WHERE UserID=? AND Role='admin'");
            $stmt->execute([$nama, $email, $nohp, $pw, $eid]);
        } else {
            $stmt = $pdo->prepare("UPDATE User SET Nama=?, Email=?, NoHandphone=? WHERE UserID=? AND Role='admin'");
            $stmt->execute([$nama, $email, $nohp, $eid]);
        }
        $admin_msg = "Data admin berhasil diupdate!";
        header("Location: account.php"); exit;
    }
}

// Hapus admin
if ($role === 'admin' && isset($_GET['delete_admin'])) {
    $del_id = $_GET['delete_admin'];
    $count_stmt = $pdo->query("SELECT COUNT(*) FROM User WHERE Role='admin'");
    $admin_count = $count_stmt->fetchColumn();
    if ($admin_count > 1 && $del_id != $user_id) { // tidak bisa hapus diri sendiri
        $del_stmt = $pdo->prepare("DELETE FROM User WHERE UserID=? AND Role='admin'");
        $del_stmt->execute([$del_id]);
        $admin_msg = "Admin berhasil dihapus.";
        header("Location: account.php"); exit;
    } else {
        $admin_msg = "Tidak bisa menghapus admin terakhir atau diri sendiri!";
    }
}

// List admin
$admins = [];
if ($role === 'admin') {
    $admins = $pdo->query("SELECT * FROM User WHERE Role='admin' ORDER BY CreatedAt DESC")->fetchAll(PDO::FETCH_ASSOC);
}

// -- RIWAYAT PEMBELIAN
$tiketStmt = $pdo->prepare("SELECT pm.MethodName, t2.TicketType, tr.PaymentStatus, tr.TransactionDate
    FROM `Transaction` tr
    JOIN Ticket t2 ON tr.TicketID = t2.TicketID
    JOIN PaymentMethod pm ON tr.PaymentMethodID = pm.PaymentMethodID
    WHERE tr.UserID = ?
    ORDER BY tr.TransactionDate DESC
");
$tiketStmt->execute([$user_id]);
$riwayatTiket = $tiketStmt->fetchAll(PDO::FETCH_ASSOC);

$prodStmt = $pdo->prepare("SELECT p.Name AS ProductName, pt.Quantity, pt.TotalAmount, pt.PaymentStatus, pt.TransactionDate
    FROM ProductTransaction pt
    JOIN Product p ON pt.ProductID = p.ProductID
    WHERE pt.UserID = ?
    ORDER BY pt.TransactionDate DESC
");
$prodStmt->execute([$user_id]);
$riwayatProduk = $prodStmt->fetchAll(PDO::FETCH_ASSOC);

?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Account - Eventra</title>
  <link rel="stylesheet" href="../css/account.css" />
  <link href="https://fonts.googleapis.com/css?family=Poppins:400,700&display=swap" rel="stylesheet">
</head>
<body>
  <header>
    <div class="logo">
      <img src="../public/logo eventra.jpg" alt="Logo"><span>Eventra</span>
    </div>
    <nav>
      <a href="dashboard.php">Dashboard</a>
      <a href="tenant.php">Tenant</a>
      <a href="scan_qr.php">Scan QR</a>
      <a href="schedule.php">Jadwal</a>
      <a href="tiket.php">Tiket</a>
      <a href="produk.php">Produk</a>
      <a href="feedback.php">Feedback</a>
      <a href="account.php" style="font-weight:bold;">Account</a>
      <?php if ($role === 'admin'): ?>
        <a href="staff.php">Staff</a>
        <a href="add_admin.php">Add Admin</a>
      <?php endif; ?>
      <a href="logout.php" class="logout-btn" style="color:#db2425;font-weight:600;margin-left:16px;">Sign Out</a>
    </nav>
  </header>
  <main class="main-content">

    <!-- ===== ADMIN CRUD SECTION ===== -->
    <?php if ($role === 'admin'): ?>
      <div class="admin-section" id="crud_admin">
        <h2>Daftar Admin Terdaftar</h2>
        <?php if ($admin_msg): ?><div class="alert-success"><?= htmlspecialchars($admin_msg) ?></div><?php endif; ?>
        <table>
          <thead>
            <tr>
              <th>Nama</th>
              <th>Email</th>
              <th>No. HP</th>
              <th>Tanggal Daftar</th>
              <th>Aksi</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach($admins as $a): ?>
            <tr>
              <?php if ($edit_admin_id == $a['UserID']): ?>
                <form method="POST">
                  <input type="hidden" name="edit_admin_id" value="<?= $a['UserID'] ?>">
                  <td><input type="text" name="edit_nama_admin" value="<?= htmlspecialchars($a['Nama']) ?>" required></td>
                  <td><input type="email" name="edit_email_admin" value="<?= htmlspecialchars($a['Email']) ?>" required></td>
                  <td><input type="text" name="edit_phone_admin" value="<?= htmlspecialchars($a['NoHandphone'] ?? '') ?>" required></td>
                  <td><?= htmlspecialchars($a['CreatedAt']) ?></td>
                  <td>
                    <input type="password" name="edit_password_admin" placeholder="Password baru (kosongkan jika tidak ganti)">
                    <button type="submit" class="btn-admin">Simpan</button>
                    <a href="account.php" class="btn-admin-red">Batal</a>
                  </td>
                </form>
              <?php else: ?>
                <td><?= htmlspecialchars($a['Nama']) ?></td>
                <td><?= htmlspecialchars($a['Email']) ?></td>
                <td><?= htmlspecialchars($a['NoHandphone'] ?? '-') ?></td>
                <td><?= htmlspecialchars($a['CreatedAt']) ?></td>
                <td>
                  <?php if ($a['UserID'] != $user_id): ?>
                    <a href="account.php?edit_admin=<?= $a['UserID'] ?>" class="btn-admin">Edit</a>
                    <?php if (count($admins) > 1): ?>
                      <a href="account.php?delete_admin=<?= $a['UserID'] ?>" class="btn-admin btn-admin-red" onclick="return confirm('Yakin hapus admin ini?')">Hapus</a>
                    <?php endif; ?>
                  <?php else: ?>
                    <span style="color:#888;font-size:0.95em;">(Anda)</span>
                  <?php endif; ?>
                </td>
              <?php endif; ?>
            </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    <?php endif; ?>

    <h1 class="username"><?= htmlspecialchars($user_data['Nama']) ?></h1>
    <div class="table-container" style="margin-bottom:24px;">
      <table class="user-info-table">
        <tr><td><strong>Nama</strong></td><td><?= htmlspecialchars($user_data['Nama']) ?></td></tr>
        <tr><td><strong>Email</strong></td><td><?= htmlspecialchars($user_data['Email']) ?></td></tr>
        <tr><td><strong>No. Handphone</strong></td><td><?= htmlspecialchars($user_data['NoHandphone'] ?? '-') ?></td></tr>
        <tr><td><strong>Tanggal Daftar</strong></td>
            <td><?= isset($user_data['CreatedAt']) ? date('d/m/Y H:i', strtotime($user_data['CreatedAt'])) : '-' ?></td></tr>
        <tr><td><strong>Role</strong></td><td><?= htmlspecialchars($user_data['Role']) ?></td></tr>
      </table>
    </div>
    <!-- FORM EDIT PROFIL: user/admin -->
    <div class="edit-user-section">
      <h3><?= ($role === 'admin' ? "Edit Profil Admin" : "Edit Profil Anda") ?></h3>
      <?php if ($edit_msg): ?><div class="alert-success"><?= htmlspecialchars($edit_msg) ?></div><?php endif; ?>
      <form method="POST" class="form-edit-profil" style="margin-bottom:24px;">
        <input type="hidden" name="edit_profil" value="1">
        <label>Nama</label>
        <input type="text" name="nama" value="<?= htmlspecialchars($user_data['Nama']) ?>" required>
        <label>Email</label>
        <input type="email" name="email" value="<?= htmlspecialchars($user_data['Email']) ?>" required>
        <label>No. Handphone</label>
        <input type="text" name="nohp" value="<?= htmlspecialchars($user_data['NoHandphone'] ?? '') ?>">
        <label>Password Baru (kosongkan jika tidak ingin ganti)</label>
        <input type="password" name="password">
        <button type="submit">Update Profil</button>
        <button type="button" onclick="window.location.href='account.php';" style="margin-left:12px;background:#db2425;color:#fff;">Cancel</button>
      </form>
    </div>
    <!-- ===== RIWAYAT PEMBELIAN TIKET ===== -->
    <h2 class="subtitle">Riwayat Pembelian Tiket</h2>
    <div class="table-container">
      <table>
        <thead>
          <tr>
            <th>Jenis Pembayaran</th>
            <th>Jenis Tiket</th>
            <th>Status Pembayaran</th>
            <th>Tanggal Pembayaran</th>
          </tr>
        </thead>
        <tbody>
          <?php if ($riwayatTiket && count($riwayatTiket) > 0): ?>
            <?php foreach ($riwayatTiket as $row): ?>
              <tr>
                <td><?= htmlspecialchars($row['MethodName']) ?></td>
                <td><?= htmlspecialchars($row['TicketType']) ?></td>
                <td class="<?= strtolower($row['PaymentStatus']) == 'completed' ? 'berhasil' : 'gagal' ?>">
                  <?= strtolower($row['PaymentStatus']) == 'completed' ? 'Berhasil' : 'Gagal' ?>
                </td>
                <td><?= date('d/m/Y H:i:s', strtotime($row['TransactionDate'])) ?></td>
              </tr>
            <?php endforeach; ?>
          <?php else: ?>
            <tr>
              <td colspan="4" style="text-align:center;">Belum ada riwayat pembelian tiket.</td>
            </tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
    <!-- ===== RIWAYAT PEMBELIAN PRODUK ===== -->
    <h2 class="subtitle">Riwayat Pembelian Produk</h2>
    <div class="table-container">
      <table>
        <thead>
          <tr>
            <th>Nama Produk</th>
            <th>Jumlah</th>
            <th>Total Harga</th>
            <th>Status Pembayaran</th>
            <th>Tanggal Pembelian</th>
          </tr>
        </thead>
        <tbody>
          <?php if ($riwayatProduk && count($riwayatProduk) > 0): ?>
            <?php foreach ($riwayatProduk as $row): ?>
              <tr>
                <td><?= htmlspecialchars($row['ProductName']) ?></td>
                <td><?= (int)$row['Quantity'] ?></td>
                <td>Rp <?= number_format($row['TotalAmount'], 0, ',', '.') ?></td>
                <td class="<?= strtolower($row['PaymentStatus']) == 'completed' ? 'berhasil' : 'gagal' ?>">
                  <?= strtolower($row['PaymentStatus']) == 'completed' ? 'Berhasil' : 'Gagal' ?>
                </td>
                <td><?= date('d/m/Y H:i:s', strtotime($row['TransactionDate'])) ?></td>
              </tr>
            <?php endforeach; ?>
          <?php else: ?>
            <tr>
              <td colspan="5" style="text-align:center;">Belum ada riwayat pembelian produk.</td>
            </tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </main>
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
