<?php
session_start();
require_once __DIR__ . '/../api/config/database.php';

// === Hanya admin yang bisa akses ===
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit;
}

// ===== CRUD Tambah Staff =====
if (isset($_POST['add_staff'])) {
    $nama = trim($_POST['nama']);
    $posisi = trim($_POST['posisi']);
    $kontak = trim($_POST['kontak']);
    $shift = trim($_POST['shifttime']);
    $organizer = 1; // ganti kalau ada select organizer
    $gambar = null;
    if (isset($_FILES['gambar']) && $_FILES['gambar']['error'] === UPLOAD_ERR_OK) {
        $ext = strtolower(pathinfo($_FILES['gambar']['name'], PATHINFO_EXTENSION));
        $gambar = uniqid('staff_') . '.' . $ext;
        move_uploaded_file($_FILES['gambar']['tmp_name'], "../public/staff/$gambar");
    }
    $stmt = $pdo->prepare("INSERT INTO Staff (Nama, Posisi, Kontak, ShiftTime, OrganizerID, gambar) VALUES (?,?,?,?,?,?)");
    $stmt->execute([$nama, $posisi, $kontak, $shift, $organizer, $gambar]);
    header("Location: staff.php"); exit;
}

// ===== CRUD Edit/Update Staff =====
$editStaff = null;
if (isset($_GET['edit_staff'])) {
    $sid = (int)$_GET['edit_staff'];
    $stmt = $pdo->prepare("SELECT * FROM Staff WHERE StaffID=?");
    $stmt->execute([$sid]);
    $editStaff = $stmt->fetch(PDO::FETCH_ASSOC);
}
if (isset($_POST['update_staff'])) {
    $sid = (int)$_POST['staff_id'];
    $nama = trim($_POST['nama']);
    $posisi = trim($_POST['posisi']);
    $kontak = trim($_POST['kontak']);
    $shift = trim($_POST['shifttime']);
    $organizer = 1;
    if (isset($_FILES['gambar']) && $_FILES['gambar']['error'] === UPLOAD_ERR_OK) {
        $ext = strtolower(pathinfo($_FILES['gambar']['name'], PATHINFO_EXTENSION));
        $gambar = uniqid('staff_') . '.' . $ext;
        move_uploaded_file($_FILES['gambar']['tmp_name'], "../public/staff/$gambar");
        $stmt = $pdo->prepare("UPDATE Staff SET Nama=?, Posisi=?, Kontak=?, ShiftTime=?, OrganizerID=?, gambar=? WHERE StaffID=?");
        $stmt->execute([$nama, $posisi, $kontak, $shift, $organizer, $gambar, $sid]);
    } else {
        $stmt = $pdo->prepare("UPDATE Staff SET Nama=?, Posisi=?, Kontak=?, ShiftTime=?, OrganizerID=? WHERE StaffID=?");
        $stmt->execute([$nama, $posisi, $kontak, $shift, $organizer, $sid]);
    }
    header("Location: staff.php"); exit;
}

// ===== CRUD Hapus Staff =====
if (isset($_GET['delete_staff'])) {
    $sid = (int)$_GET['delete_staff'];
    $stmt = $pdo->prepare("DELETE FROM Staff WHERE StaffID=?");
    $stmt->execute([$sid]);
    header("Location: staff.php"); exit;
}

// ===== CRUD Hapus Gambar Staff =====
if (isset($_GET['delete_image'])) {
    $sid = (int)$_GET['delete_image'];
    // Ambil nama file gambar staff dari DB
    $stmt = $pdo->prepare("SELECT gambar FROM Staff WHERE StaffID=?");
    $stmt->execute([$sid]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($row && $row['gambar']) {
        $img_path = "../public/staff/" . $row['gambar'];
        if (is_file($img_path)) unlink($img_path);
    }
    // Update kolom gambar jadi NULL
    $pdo->prepare("UPDATE Staff SET gambar=NULL WHERE StaffID=?")->execute([$sid]);
    header("Location: staff.php"); exit;
}

// ===== Ambil daftar organizer untuk select box =====
$organizerList = $pdo->query("SELECT OrganizerID, OrganizerName FROM Organizer ORDER BY OrganizerName")->fetchAll(PDO::FETCH_ASSOC);

// ===== Ambil data staff + organizer =====
$stmt = $pdo->query("
  SELECT s.StaffID, s.Nama, s.Posisi, s.Kontak, s.ShiftTime, s.gambar, s.OrganizerID, o.OrganizerName
  FROM Staff s
  LEFT JOIN Organizer o ON s.OrganizerID = o.OrganizerID
  ORDER BY s.StaffID DESC
");
$staffList = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Staff - Eventra</title>
  <link rel="stylesheet" href="../css/staff.css" />
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
      <a href="staff.php" style="font-weight:bold">Staff</a>
    </nav>
  </header>
  <main>
    <section class="staff-section">
      <h1>DAFTAR STAFF</h1>
      <!-- ===== Form Tambah/Edit Staff ===== -->
      <div class="form-container">
  <form method="POST" enctype="multipart/form-data" class="form-tambah-staff">
    <?php if ($editStaff): ?>
      <input type="hidden" name="update_staff" value="1">
      <input type="hidden" name="staff_id" value="<?= $editStaff['StaffID'] ?>">
    <?php else: ?>
      <input type="hidden" name="add_staff" value="1">
    <?php endif; ?>
    <div class="form-row">
      <input type="text" name="nama" placeholder="Nama Staff" required value="<?= $editStaff ? htmlspecialchars($editStaff['Nama']) : '' ?>">
      <input type="text" name="posisi" placeholder="Posisi" required value="<?= $editStaff ? htmlspecialchars($editStaff['Posisi']) : '' ?>">
    </div>
    <div class="form-row">
      <input type="text" name="kontak" placeholder="Kontak" required value="<?= $editStaff ? htmlspecialchars($editStaff['Kontak']) : '' ?>">
      <input type="text" name="shifttime" placeholder="Shift" required value="<?= $editStaff ? htmlspecialchars($editStaff['ShiftTime']) : '' ?>">
    </div>
    <div class="form-row">
      <select name="organizer_id" required>
        <option value="">Pilih Organizer</option>
        <?php foreach($organizerList as $o): ?>
          <option value="<?= $o['OrganizerID'] ?>"
            <?= ($editStaff && $editStaff['OrganizerID'] == $o['OrganizerID']) ? 'selected' : '' ?>>
            <?= htmlspecialchars($o['OrganizerName']) ?>
          </option>
        <?php endforeach; ?>
      </select>
    </div>
    <div class="form-row">
      <input type="file" name="gambar" accept="image/*" <?= $editStaff ? "" : "required" ?>>
    </div>
    <div class="form-row form-row-actions">
      <div style="flex:1"></div>
      <button type="submit" class="btn-action btn-edit"><?= $editStaff ? "Simpan" : "Tambah Staff" ?></button>
      <?php if ($editStaff): ?>
        <a href="staff.php" class="btn-action btn-delete" style="margin-left:8px;">Batal</a>
      <?php endif; ?>
    </div>
  </form>
</div>
      <!-- ===== List Staff ===== -->
     <div class="staff-list">
  <?php foreach($staffList as $staff): ?>
    <div class="staff-card">
      <img src="../public/staff/<?= $staff['gambar'] ? htmlspecialchars($staff['gambar']) : 'user-default.png' ?>"
           alt="<?= htmlspecialchars($staff['Nama']) ?>">
      <?php if ($staff['gambar']): ?>
        <form method="post" action="staff.php?delete_image=<?= $staff['StaffID'] ?>" style="margin-bottom: 10px;">
          <button type="submit" class="btn-action btn-delete"
            style="background:#f44336; color:#fff; font-size:0.93rem; padding:4px 13px;"
            onclick="return confirm('Hapus foto profile staff ini?')">
            Hapus Gambar
          </button>
        </form>
      <?php endif; ?>
      <h3><?= htmlspecialchars($staff['Nama']) ?></h3>
      <p><?= htmlspecialchars($staff['Posisi']) ?></p>
      <p style="font-size:0.94rem; color:#444;">Kontak: <?= htmlspecialchars($staff['Kontak']) ?></p>
      <p style="font-size:0.94rem; color:#185b35;">Shift: <?= htmlspecialchars($staff['ShiftTime']) ?></p>
      <p style="font-size:0.92rem; color:#666;">Organizer: <?= htmlspecialchars($staff['OrganizerName']) ?></p>
      <div class="aksi-buttons" style="margin-top:10px;">
        <a href="staff.php?edit_staff=<?= $staff['StaffID'] ?>" class="btn-action btn-edit">Edit</a>
        <a href="staff.php?delete_staff=<?= $staff['StaffID'] ?>" class="btn-action btn-delete" onclick="return confirm('Hapus staff ini?')">Hapus</a>
      </div>
    </div>
  <?php endforeach; ?>
</div>
    </section>
  </main>
  <footer class="footer-section">
    <!-- Footer sesuai website -->
    <div class="footer-top">
      <h2>NEED ASSISTANCE FOR YOUR EVENT?</h2>
      <div class="footer-logo-contact">
        <div class="footer-logo">
          <img src="../public/logo eventra.jpg" alt="Eventra Logo">
          <h3>EVENTRA<br><span>Event Management</span></h3>
        </div>
        <div class="footer-contact">
          <p><span>CALL US THROUGH:</span></p>
          <p>WHATSAPP NUMBER: 081234567890 (DINDA)</p>
          <p>EMAIL: eventra.management@gmail.com</p>
          <p>PRADITA UNIVERSITY, Scientia Business Park,<br>
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
