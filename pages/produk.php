<?php
session_start();
require_once __DIR__ . '/../api/config/database.php';

$role = $_SESSION['role'] ?? 'user';

// --- Ambil data Bazaar untuk select box
$bazaar_list = $pdo->query("SELECT BazaarID, BazaarName FROM Bazaar ORDER BY BazaarName")->fetchAll(PDO::FETCH_ASSOC);

// --- CRUD Tenant ---
if ($role === 'admin' && isset($_POST['add_tenant'])) {
    $nama = trim($_POST['nama'] ?? '');
    $bazaar = (int)($_POST['bazaar_id'] ?? 0);
    $kategori = trim($_POST['kategori'] ?? '');
    $contact = trim($_POST['contact'] ?? '');
    $desc = trim($_POST['description'] ?? '');
    if ($nama && $bazaar && $kategori && $contact) {
        $stmt = $pdo->prepare("INSERT INTO Tenant (Nama, BazaarID, Kategori, Contact, Description) VALUES (?,?,?,?,?)");
        $stmt->execute([$nama, $bazaar, $kategori, $contact, $desc]);
        header("Location: produk.php"); exit;
    }
}

$edit_tenant = null;
if ($role === 'admin' && isset($_GET['edit_tenant'])) {
    $tid = (int)$_GET['edit_tenant'];
    $stmt = $pdo->prepare("SELECT * FROM Tenant WHERE TenantID=?");
    $stmt->execute([$tid]);
    $edit_tenant = $stmt->fetch(PDO::FETCH_ASSOC);
}
if ($role === 'admin' && isset($_POST['update_tenant'])) {
    $tid = (int)$_POST['tenant_id'];
    $nama = trim($_POST['nama'] ?? '');
    $bazaar = (int)($_POST['bazaar_id'] ?? 0);
    $kategori = trim($_POST['kategori'] ?? '');
    $contact = trim($_POST['contact'] ?? '');
    $desc = trim($_POST['description'] ?? '');
    $stmt = $pdo->prepare("UPDATE Tenant SET Nama=?, BazaarID=?, Kategori=?, Contact=?, Description=? WHERE TenantID=?");
    $stmt->execute([$nama, $bazaar, $kategori, $contact, $desc, $tid]);
    header("Location: produk.php"); exit;
}
if ($role === 'admin' && isset($_GET['delete_tenant'])) {
    $tid = (int)$_GET['delete_tenant'];
    $pdo->prepare("DELETE FROM Tenant WHERE TenantID=?")->execute([$tid]);
    header("Location: produk.php"); exit;
}

// --- CRUD Produk ---
$tenants = $pdo->query("SELECT TenantID, Nama AS TenantName FROM Tenant ORDER BY Nama")->fetchAll(PDO::FETCH_ASSOC);

if ($role === 'admin' && isset($_POST['add_product'])) {
    $name   = trim($_POST['name'] ?? '');
    $harga  = (int)($_POST['harga'] ?? 0);
    $stok   = (int)($_POST['stok'] ?? 0);
    $tenant = (int)($_POST['tenant_id'] ?? 0);
    $gambar = null;

    if ($name && $tenant) {
        // Proses upload gambar
        if (isset($_FILES['gambar']) && $_FILES['gambar']['error'] === UPLOAD_ERR_OK) {
            $ext = strtolower(pathinfo($_FILES['gambar']['name'], PATHINFO_EXTENSION));
            $gambar = uniqid('prd_') . '.' . $ext;
            move_uploaded_file($_FILES['gambar']['tmp_name'], "../public/produk/$gambar");
        }
        $stmt = $pdo->prepare("INSERT INTO Product (Name, Harga, Stok, TenantID, gambar) VALUES (?,?,?,?,?)");
        $stmt->execute([$name, $harga, $stok, $tenant, $gambar]);
        header("Location: produk.php"); exit;
    }
}

$edit_product = null;
if ($role === 'admin' && isset($_GET['edit_product'])) {
    $pid = (int)$_GET['edit_product'];
    $stmt = $pdo->prepare("SELECT * FROM Product WHERE ProductID=?");
    $stmt->execute([$pid]);
    $edit_product = $stmt->fetch(PDO::FETCH_ASSOC);
}
if ($role === 'admin' && isset($_POST['update_product'])) {
    $pid    = (int)$_POST['product_id'];
    $name   = trim($_POST['name'] ?? '');
    $harga  = (int)($_POST['harga'] ?? 0);
    $stok   = (int)($_POST['stok'] ?? 0);
    $tenant = (int)($_POST['tenant_id'] ?? 0);

    if (isset($_FILES['gambar']) && $_FILES['gambar']['error'] === UPLOAD_ERR_OK) {
        $ext = strtolower(pathinfo($_FILES['gambar']['name'], PATHINFO_EXTENSION));
        $gambar = uniqid('prd_') . '.' . $ext;
        move_uploaded_file($_FILES['gambar']['tmp_name'], "../public/produk/$gambar");
        $stmt = $pdo->prepare("UPDATE Product SET Name=?, Harga=?, Stok=?, TenantID=?, gambar=? WHERE ProductID=?");
        $stmt->execute([$name, $harga, $stok, $tenant, $gambar, $pid]);
    } else {
        $stmt = $pdo->prepare("UPDATE Product SET Name=?, Harga=?, Stok=?, TenantID=? WHERE ProductID=?");
        $stmt->execute([$name, $harga, $stok, $tenant, $pid]);
    }
    header("Location: produk.php"); exit;
}
if ($role === 'admin' && isset($_GET['delete_product'])) {
    $pid = (int)$_GET['delete_product'];
    $pdo->prepare("DELETE FROM Product WHERE ProductID=?")->execute([$pid]);
    header("Location: produk.php"); exit;
}

// --- Handler Hapus Gambar Produk ---
if ($role === 'admin' && isset($_GET['delete_image'])) {
    $pid = (int)$_GET['delete_image'];
    $stmt = $pdo->prepare("SELECT gambar FROM Product WHERE ProductID=?");
    $stmt->execute([$pid]);
    $gambar = $stmt->fetchColumn();
    if ($gambar && file_exists("../public/produk/$gambar")) {
        unlink("../public/produk/$gambar");
    }
    $pdo->prepare("UPDATE Product SET gambar=NULL WHERE ProductID=?")->execute([$pid]);
    header("Location: produk.php");
    exit;
}

// --- Ambil data produk (join tenant) dan tenant (join bazaar)
$products = $pdo->query(
    "SELECT p.*, t.Nama AS TenantName FROM Product p LEFT JOIN Tenant t ON p.TenantID = t.TenantID ORDER BY p.ProductID DESC"
)->fetchAll(PDO::FETCH_ASSOC);

$tenants_table = $pdo->query("
  SELECT t.*, b.BazaarName AS BazaarNama 
  FROM Tenant t 
  LEFT JOIN Bazaar b ON t.BazaarID = b.BazaarID
  ORDER BY t.TenantID DESC
")->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Produk & Tenant - Eventra</title>
  <link rel="stylesheet" href="../css/produk.css" />
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
    <a href="produk.php" style="font-weight:bold">Produk</a>
    <a href="feedback.php">Feedback</a>
    <a href="account.php">Account</a>
  </nav>
</header>
<main>
  <div class="section-wrap">
    <!-- ===== TENANT SECTION ===== -->
    <section>
      <h1>Daftar Tenant</h1>
      <?php if($role==='admin'): ?>
      <!-- Form tambah/edit Tenant -->
      <?php if($edit_tenant): ?>
      <form method="POST" class="form-tambah-tenant">
        <input type="hidden" name="update_tenant" value="1">
        <input type="hidden" name="tenant_id" value="<?= $edit_tenant['TenantID'] ?>">
        <input type="text" name="nama" placeholder="Nama Tenant" required value="<?= htmlspecialchars($edit_tenant['Nama']) ?>">
        <select name="bazaar_id" required>
          <option value="">Pilih Bazaar</option>
          <?php foreach($bazaar_list as $b): ?>
            <option value="<?= $b['BazaarID'] ?>" <?= ($edit_tenant['BazaarID']==$b['BazaarID'])?'selected':'' ?>>
              <?= htmlspecialchars($b['BazaarName']) ?>
            </option>
          <?php endforeach; ?>
        </select>
        <input type="text" name="kategori" placeholder="Kategori" required value="<?= htmlspecialchars($edit_tenant['Kategori']) ?>">
        <input type="text" name="contact" placeholder="Kontak" required value="<?= htmlspecialchars($edit_tenant['Contact']) ?>">
        <input type="text" name="description" placeholder="Deskripsi" value="<?= htmlspecialchars($edit_tenant['Description']) ?>">
        <button type="submit" class="btn-action btn-edit">Update</button>
        <a href="produk.php" class="btn-action btn-delete">Batal</a>
      </form>
      <?php else: ?>
      <form method="POST" class="form-tambah-tenant">
        <input type="hidden" name="add_tenant" value="1">
        <input type="text" name="nama" placeholder="Nama Tenant" required>
        <select name="bazaar_id" required>
          <option value="">Pilih Bazaar</option>
          <?php foreach($bazaar_list as $b): ?>
            <option value="<?= $b['BazaarID'] ?>"><?= htmlspecialchars($b['BazaarName']) ?></option>
          <?php endforeach; ?>
        </select>
        <input type="text" name="kategori" placeholder="Kategori" required>
        <input type="text" name="contact" placeholder="Kontak" required>
        <input type="text" name="description" placeholder="Deskripsi">
        <button type="submit" class="btn-action btn-edit">Tambah Tenant</button>
      </form>
      <?php endif; ?>
      <?php endif; ?>

      <table class="tenant-table">
        <thead>
          <tr>
            <th>Nama</th>
            <th>Bazaar</th>
            <th>Kategori</th>
            <th>Kontak</th>
            <th>Deskripsi</th>
            <?php if($role==='admin'): ?><th>Aksi</th><?php endif; ?>
          </tr>
        </thead>
        <tbody>
          <?php foreach($tenants_table as $tenant): ?>
          <tr>
            <td><?= htmlspecialchars($tenant['Nama']) ?></td>
            <td><?= htmlspecialchars($tenant['BazaarNama'] ?? '-') ?></td>
            <td><?= htmlspecialchars($tenant['Kategori']) ?></td>
            <td><?= htmlspecialchars($tenant['Contact']) ?></td>
            <td><?= htmlspecialchars($tenant['Description']) ?></td>
            <?php if($role==='admin'): ?>
            <td>
              <div class="action-buttons-tenant">
                <a href="produk.php?edit_tenant=<?= $tenant['TenantID'] ?>" class="btn-action btn-edit">Edit</a>
                <a href="produk.php?delete_tenant=<?= $tenant['TenantID'] ?>" class="btn-action btn-delete" onclick="return confirm('Hapus tenant ini?')">Hapus</a>
              </div>
            </td>
            <?php endif; ?>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </section>

    <!-- ===== PRODUK SECTION ===== -->
    <section>
  <h1>Daftar Produk</h1>
  <?php
    $is_edit = ($role === 'admin' && isset($_GET['edit_product']) && $edit_product);
  ?>
  <?php if($role==='admin'): ?>
    <!-- Form tambah/edit Produk -->
    <form method="POST" enctype="multipart/form-data" class="form-tambah-produk">
      <?php if ($is_edit): ?>
        <input type="hidden" name="update_product" value="1">
        <input type="hidden" name="product_id" value="<?= $edit_product['ProductID'] ?>">
      <?php else: ?>
        <input type="hidden" name="add_product" value="1">
      <?php endif; ?>

      <input type="text" name="name" placeholder="Nama Produk" required
             value="<?= $is_edit ? htmlspecialchars($edit_product['Name']) : '' ?>">
      <input type="number" name="harga" placeholder="Harga" min="0" required
             value="<?= $is_edit ? (int)$edit_product['Harga'] : '' ?>">
      <input type="number" name="stok" placeholder="Stok" min="0" required
             value="<?= $is_edit ? (int)$edit_product['Stok'] : '' ?>">
      <select name="tenant_id" required>
        <option value="">Pilih Tenant</option>
        <?php foreach($tenants as $t): ?>
          <option value="<?= $t['TenantID'] ?>"
            <?= ($is_edit && $edit_product['TenantID'] == $t['TenantID']) ? 'selected' : '' ?>>
            <?= htmlspecialchars($t['TenantName']) ?>
          </option>
        <?php endforeach; ?>
      </select>
      <input type="file" name="gambar" accept="image/*" <?= $is_edit ? '' : 'required' ?>>

      <button type="submit" class="btn-action btn-edit" style="font-weight:bold;">
        <?= $is_edit ? 'Simpan' : 'Tambah Produk' ?>
      </button>
      <?php if ($is_edit): ?>
        <a href="produk.php" class="btn-action btn-delete">Batal</a>
      <?php endif; ?>
    </form>

    <table class="produk-table">
      <thead>
        <tr>
          <th>Nama Produk</th>
          <th>Harga</th>
          <th>Stok</th>
          <th>Tenant</th>
          <th>Aksi</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach($products as $prod): ?>
        <tr>
          <td><?= htmlspecialchars($prod['Name']) ?></td>
          <td>Rp <?= number_format($prod['Harga'], 0, ',', '.') ?></td>
          <td><?= (int)$prod['Stok'] ?></td>
          <td><?= htmlspecialchars($prod['TenantName'] ?? '-') ?></td>
          <td>
            <div class="aksi-buttons">
              <a href="produk.php?edit_product=<?= $prod['ProductID'] ?>" class="btn-action btn-edit">Edit</a>
              <a href="produk.php?delete_product=<?= $prod['ProductID'] ?>" class="btn-action btn-delete" onclick="return confirm('Hapus produk ini?')">Hapus</a>
              <?php if($role==='admin' && $prod['gambar']): ?>
                <form method="post" action="produk.php?delete_image=<?= $prod['ProductID'] ?>" style="display:inline;">
                  <button type="submit" class="btn-action btn-delete"
                    onclick="return confirm('Hapus gambar produk ini?')">
                    Hapus Gambar 
                  </button>
                </form>
              <?php endif; ?>
            </div>
          </td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  <?php endif; ?>

    <!-- Produk Card (untuk user) -->
<div class="produk-list">
  <?php foreach($products as $prod): ?>
  <div class="produk-card">
    <img src="../public/produk/<?= $prod['gambar'] ? htmlspecialchars($prod['gambar']) : 'no-image.png' ?>" alt="<?= htmlspecialchars($prod['Name']) ?>">
    <h3><?= htmlspecialchars($prod['Name']) ?></h3>
    <p>Rp <?= number_format($prod['Harga'], 0, ',', '.') ?></p>
    <span style="color:#888;">Stok: <?= $prod['Stok'] ?></span>
    <span class="tenant-info">
      <strong>Tenant:</strong> <?= htmlspecialchars($prod['TenantName'] ?? '-') ?>
    </span>
    <?php if ($prod['Stok'] < 1): ?>
      <button class="buy-btn" disabled style="background:#aaa;">Habis</button>
    <?php else: ?>
      <?php if (isset($_SESSION['user_id'])): ?>
        <form action="pembayaran.php" method="get" style="margin-top:8px;">
          <input type="hidden" name="product_id" value="<?= $prod['ProductID'] ?>">
          <input type="number" name="quantity" min="1" max="<?= $prod['Stok'] ?>" value="1" style="width:54px;" required>
          <button type="submit" class="buy-btn">Beli</button>
        </form>
      <?php else: ?>
        <a href="login.php" class="buy-btn" style="display:inline-block;">Login untuk Beli</a>
      <?php endif; ?>
    <?php endif; ?>
  </div>
  <?php endforeach; ?>
</div>
    </section>
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
