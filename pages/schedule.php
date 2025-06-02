<?php
session_start();
require_once __DIR__ . '/../api/config/database.php';
$role = $_SESSION['role'] ?? 'user';

function refresh() { header("Location: schedule.php"); exit; }

// --- Data for Table ---
$organizers = $pdo->query("SELECT * FROM Organizer ORDER BY OrganizerName")->fetchAll(PDO::FETCH_ASSOC);
$locations  = $pdo->query("SELECT * FROM Location ORDER BY LocationName")->fetchAll(PDO::FETCH_ASSOC);
$bazaars    = $pdo->query("SELECT b.*, o.OrganizerName FROM Bazaar b JOIN Organizer o ON b.OrganizerID=o.OrganizerID ORDER BY b.BazaarName")->fetchAll(PDO::FETCH_ASSOC);

// ==== Organizer CRUD ====
$edit_organizer_id = isset($_GET['edit_organizer']) ? (int)$_GET['edit_organizer'] : null;
$organizer_edit = null;
if ($edit_organizer_id) {
    $stmt = $pdo->prepare("SELECT * FROM Organizer WHERE OrganizerID=?");
    $stmt->execute([$edit_organizer_id]);
    $organizer_edit = $stmt->fetch(PDO::FETCH_ASSOC);
}
if ($role === 'admin' && isset($_POST['add_organizer'])) {
    $name = trim($_POST['organizer_name'] ?? '');
    $contact = trim($_POST['organizer_contact'] ?? '');
    if ($name) {
        $pdo->prepare("INSERT INTO Organizer (OrganizerName, OrganizerContact) VALUES (?,?)")->execute([$name, $contact]);
        refresh();
    }
}
if ($role === 'admin' && isset($_POST['update_organizer'])) {
    $oid = $_POST['organizer_id'];
    $name = $_POST['organizer_name'];
    $contact = $_POST['organizer_contact'];
    $pdo->prepare("UPDATE Organizer SET OrganizerName=?, OrganizerContact=? WHERE OrganizerID=?")
        ->execute([$name, $contact, $oid]);
    refresh();
}
if ($role === 'admin' && isset($_GET['delete_organizer'])) {
    $oid = (int)$_GET['delete_organizer'];
    $pdo->prepare("DELETE FROM Organizer WHERE OrganizerID=?")->execute([$oid]);
    refresh();
}

// ==== Location CRUD ====
$edit_location_id = isset($_GET['edit_location']) ? (int)$_GET['edit_location'] : null;
$location_edit = null;
if ($edit_location_id) {
    $stmt = $pdo->prepare("SELECT * FROM Location WHERE LocationID=?");
    $stmt->execute([$edit_location_id]);
    $location_edit = $stmt->fetch(PDO::FETCH_ASSOC);
}
if ($role === 'admin' && isset($_POST['add_location'])) {
    $name = trim($_POST['location_name'] ?? '');
    $address = trim($_POST['location_address'] ?? '');
    if ($name) {
        $pdo->prepare("INSERT INTO Location (LocationName, LocationAddress) VALUES (?,?)")->execute([$name, $address]);
        refresh();
    }
}
if ($role === 'admin' && isset($_POST['update_location'])) {
    $lid = $_POST['location_id'];
    $name = $_POST['location_name'];
    $address = $_POST['location_address'];
    $pdo->prepare("UPDATE Location SET LocationName=?, LocationAddress=? WHERE LocationID=?")
        ->execute([$name, $address, $lid]);
    refresh();
}
if ($role === 'admin' && isset($_GET['delete_location'])) {
    $lid = (int)$_GET['delete_location'];
    $pdo->prepare("DELETE FROM Location WHERE LocationID=?")->execute([$lid]);
    refresh();
}

// ==== Bazaar CRUD ====
$edit_bazaar_id = isset($_GET['edit_bazaar']) ? (int)$_GET['edit_bazaar'] : null;
$bazaar_edit = null;
if ($edit_bazaar_id) {
    $stmt = $pdo->prepare("SELECT * FROM Bazaar WHERE BazaarID=?");
    $stmt->execute([$edit_bazaar_id]);
    $bazaar_edit = $stmt->fetch(PDO::FETCH_ASSOC);
}
if ($role === 'admin' && isset($_POST['add_bazaar'])) {
    $name = trim($_POST['bazaar_name'] ?? '');
    $org = $_POST['organizer_id'] ?? null;
    $date = $_POST['bazaar_date'] ?? '';
    $price = $_POST['booth_price'] ?? 0;
    $sold = $_POST['total_booths_sold'] ?? 0;
    if ($name && $org && $date) {
        $pdo->prepare("INSERT INTO Bazaar (BazaarName, OrganizerID, BazaarDate, BoothPrice, TotalBoothsSold) VALUES (?, ?, ?, ?, ?)")
            ->execute([$name, $org, $date, $price, $sold]);
        refresh();
    }
}
if ($role === 'admin' && isset($_POST['update_bazaar'])) {
    $bid = $_POST['bazaar_id'];
    $name = $_POST['bazaar_name'];
    $organizer_id = $_POST['organizer_id'];
    $date = $_POST['bazaar_date'];
    $price = $_POST['booth_price'];
    $sold = $_POST['total_booths_sold'];
    $pdo->prepare("UPDATE Bazaar SET BazaarName=?, OrganizerID=?, BazaarDate=?, BoothPrice=?, TotalBoothsSold=? WHERE BazaarID=?")
        ->execute([$name, $organizer_id, $date, $price, $sold, $bid]);
    refresh();
}
if ($role === 'admin' && isset($_GET['delete_bazaar'])) {
    $bid = (int)$_GET['delete_bazaar'];
    $pdo->prepare("DELETE FROM Bazaar WHERE BazaarID=?")->execute([$bid]);
    refresh();
}

// ==== Data for Schedule Table ====
$query = "
SELECT 
  s.JadwalID, b.BazaarName, s.NamaEvent, s.WaktuMulai, s.WaktuSelesai, s.Deskripsi,
  l.LocationName, l.LocationAddress, o.OrganizerName, o.OrganizerContact
FROM Schedule s
LEFT JOIN Bazaar b ON s.BazaarID = b.BazaarID
LEFT JOIN Location l ON s.LokasiID = l.LocationID
LEFT JOIN Organizer o ON b.OrganizerID = o.OrganizerID
ORDER BY s.WaktuMulai ASC
";
$jadwal = $pdo->query($query)->fetchAll(PDO::FETCH_ASSOC);

?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Jadwal Bazaar - Eventra</title>
  <link rel="stylesheet" href="../css/schedule.css" />
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
      <a href="schedule.php" style="font-weight:bold">Jadwal</a>
      <a href="tiket.php">Tiket</a>
      <a href="produk.php">Produk</a>
      <a href="feedback.php">Feedback</a>
      <a href="account.php">Account</a>
    </nav>
  </header>
  <main>

  <?php if ($role === 'admin'): ?>
  <!-- ==== ORGANIZER CRUD ==== -->
  <section class="crud-section">
    <h2>Data Organizer</h2>
    <div class="table-wrap">
      <table>
        <thead>
          <tr>
            <th>Nama Organizer</th>
            <th>Kontak Organizer</th>
            <?php if($role==='admin'): ?><th>Aksi</th><?php endif; ?>
          </tr>
        </thead>
        <tbody>
          <?php foreach($organizers as $o): ?>
          <tr>
            <td><?= htmlspecialchars($o['OrganizerName']) ?></td>
            <td><?= htmlspecialchars($o['OrganizerContact']) ?></td>
            <?php if($role==='admin'): ?>
            <td>
              <a href="schedule.php?edit_organizer=<?= $o['OrganizerID'] ?>" class="btn-edit">Edit</a>
              <a href="schedule.php?delete_organizer=<?= $o['OrganizerID'] ?>" class="btn-delete"
                onclick="return confirm('Hapus organizer ini?')">Hapus</a>
            </td>
            <?php endif; ?>
          </tr>
          <?php if($role==='admin' && $edit_organizer_id==$o['OrganizerID']): ?>
          <tr>
            <td colspan="3" style="background:#f6faf7;">
              <form method="POST">
                <input type="hidden" name="update_organizer" value="1">
                <input type="hidden" name="organizer_id" value="<?= $o['OrganizerID'] ?>">
                <input type="text" name="organizer_name" value="<?= htmlspecialchars($organizer_edit['OrganizerName']) ?>" required>
                <input type="text" name="organizer_contact" value="<?= htmlspecialchars($organizer_edit['OrganizerContact']) ?>">
                <button type="submit" class="btn-edit">Simpan</button>
                <a href="schedule.php" class="btn-delete">Batal</a>
              </form>
            </td>
          </tr>
          <?php endif; ?>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
    <!-- Tambah Baru -->
    <?php if($role==='admin'): ?>
    <form method="POST" style="margin-top:10px;">
      <input type="hidden" name="add_organizer" value="1">
      <input type="text" name="organizer_name" placeholder="Nama Organizer" required>
      <input type="text" name="organizer_contact" placeholder="Kontak Organizer">
      <button type="submit">Tambah Organizer</button>
    </form>
    <?php endif; ?>
  </section>

  <!-- ==== LOCATION CRUD ==== -->
  <section class="crud-section">
    <h2>Data Lokasi</h2>
    <div class="table-wrap">
      <table>
        <thead>
          <tr>
            <th>Nama Lokasi</th>
            <th>Alamat Lokasi</th>
            <?php if($role==='admin'): ?><th>Aksi</th><?php endif; ?>
          </tr>
        </thead>
        <tbody>
          <?php foreach($locations as $l): ?>
          <tr>
            <td><?= htmlspecialchars($l['LocationName']) ?></td>
            <td><?= htmlspecialchars($l['LocationAddress']) ?></td>
            <?php if($role==='admin'): ?>
            <td>
              <a href="schedule.php?edit_location=<?= $l['LocationID'] ?>" class="btn-edit">Edit</a>
              <a href="schedule.php?delete_location=<?= $l['LocationID'] ?>" class="btn-delete"
                onclick="return confirm('Hapus lokasi ini?')">Hapus</a>
            </td>
            <?php endif; ?>
          </tr>
          <?php if($role==='admin' && $edit_location_id==$l['LocationID']): ?>
          <tr>
            <td colspan="3" style="background:#f6faf7;">
              <form method="POST">
                <input type="hidden" name="update_location" value="1">
                <input type="hidden" name="location_id" value="<?= $l['LocationID'] ?>">
                <input type="text" name="location_name" value="<?= htmlspecialchars($location_edit['LocationName']) ?>" required>
                <input type="text" name="location_address" value="<?= htmlspecialchars($location_edit['LocationAddress']) ?>">
                <button type="submit" class="btn-edit">Simpan</button>
                <a href="schedule.php" class="btn-delete">Batal</a>
              </form>
            </td>
          </tr>
          <?php endif; ?>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
    <!-- Tambah Baru -->
    <?php if($role==='admin'): ?>
    <form method="POST" style="margin-top:10px;">
      <input type="hidden" name="add_location" value="1">
      <input type="text" name="location_name" placeholder="Nama Lokasi" required>
      <input type="text" name="location_address" placeholder="Alamat Lokasi">
      <button type="submit">Tambah Lokasi</button>
    </form>
    <?php endif; ?>
  </section>

<!-- ==== BAZAAR CRUD ==== -->
<section class="crud-section">
  <h2>Data Bazaar</h2>
  <div class="table-wrap">
    <table>
      <thead>
        <tr>
          <th>Nama Bazaar</th>
          <th>Tanggal Bazaar</th>
          <th>Harga Booth</th>
          <th>Booth Terjual</th>
          <th>Organizer</th>
          <?php if($role==='admin'): ?><th>Aksi</th><?php endif; ?>
        </tr>
      </thead>
      <tbody>
        <?php foreach($bazaars as $b): ?>
        <tr>
          <td><?= htmlspecialchars($b['BazaarName']) ?></td>
          <td><?= htmlspecialchars($b['BazaarDate'] ?? '-') ?></td>
          <td><?= isset($b['BoothPrice']) ? 'Rp '.number_format($b['BoothPrice'],0,',','.') : '-' ?></td>
          <td><?= (int)($b['TotalBoothsSold'] ?? 0) ?></td>
          <td><?= htmlspecialchars($b['OrganizerName']) ?></td>
          <?php if($role==='admin'): ?>
          <td>
            <a href="schedule.php?edit_bazaar=<?= $b['BazaarID'] ?>" class="btn-edit">Edit</a>
            <a href="schedule.php?delete_bazaar=<?= $b['BazaarID'] ?>" class="btn-delete"
              onclick="return confirm('Hapus bazaar ini?')">Hapus</a>
          </td>
          <?php endif; ?>
        </tr>
        <?php if($role==='admin' && $edit_bazaar_id==$b['BazaarID']): ?>
        <tr>
          <td colspan="6" style="background:#f6faf7;">
            <form method="POST">
              <input type="hidden" name="update_bazaar" value="1">
              <input type="hidden" name="bazaar_id" value="<?= $b['BazaarID'] ?>">
              <input type="text" name="bazaar_name" value="<?= htmlspecialchars($bazaar_edit['BazaarName']) ?>" required placeholder="Nama Bazaar">
              <input type="date" name="bazaar_date" value="<?= htmlspecialchars($bazaar_edit['BazaarDate']) ?>" required>
              <input type="number" name="booth_price" value="<?= (float)($bazaar_edit['BoothPrice'] ?? 0) ?>" min="0" step="1000" placeholder="Harga Booth">
              <input type="number" name="total_booths_sold" value="<?= (int)($bazaar_edit['TotalBoothsSold'] ?? 0) ?>" min="0" placeholder="Booth Terjual">
              <select name="organizer_id" required>
                <?php foreach($organizers as $o): ?>
                  <option value="<?= $o['OrganizerID'] ?>" <?= ($bazaar_edit['OrganizerID'] ?? $b['OrganizerID']) == $o['OrganizerID'] ? 'selected' : '' ?>>
                    <?= htmlspecialchars($o['OrganizerName']) ?>
                  </option>
                <?php endforeach; ?>
              </select>
              <button type="submit" class="btn-edit">Simpan</button>
              <a href="schedule.php" class="btn-delete">Batal</a>
            </form>
          </td>
        </tr>
        <?php endif; ?>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
  <!-- Tambah Baru -->
  <?php if($role==='admin'): ?>
  <form method="POST" style="margin-top:10px;">
    <input type="hidden" name="add_bazaar" value="1">
    <input type="text" name="bazaar_name" placeholder="Nama Bazaar" required>
    <input type="date" name="bazaar_date" placeholder="Tanggal Bazaar" required>
    <input type="number" name="booth_price" placeholder="Harga Booth" min="0" step="1000" required>
    <input type="number" name="total_booths_sold" placeholder="Booth Terjual" min="0" required>
    <select name="organizer_id" required>
      <option value="">Pilih Organizer</option>
      <?php foreach($organizers as $o): ?>
        <option value="<?= $o['OrganizerID'] ?>"><?= htmlspecialchars($o['OrganizerName']) ?></option>
      <?php endforeach; ?>
    </select>
    <button type="submit">Tambah Bazaar</button>
  </form>
  <?php endif; ?>
</section>

<!-- CRUD JADWAL -->
<section class="crud-section">
  <h2>Tambah Jadwal Bazaar Baru</h2>
  <form method="POST">
    <input type="hidden" name="add_jadwal" value="1">
    <label>Pilih Bazaar
      <small class="form-desc">Pilih bazaar yang ingin dijadwalkan event-nya</small>
    </label>
    <select name="bazaar_id" required>
      <option value="">Pilih Bazaar</option>
      <?php foreach($bazaars as $b): ?>
        <option value="<?= $b['BazaarID'] ?>">
          <?= htmlspecialchars($b['BazaarName']." (".$b['BazaarDate'].", ".$b['OrganizerName'].")") ?>
        </option>
      <?php endforeach; ?>
    </select>
    <label>Nama Event
      <small class="form-desc">Nama event atau sub-acara dalam bazaar</small>
    </label>
    <input type="text" name="event_name" placeholder="Nama Event" required>
    <label>Pilih Lokasi
      <small class="form-desc">Pilih lokasi/gedung/tempat event dilaksanakan</small>
    </label>
    <select name="location_id" required>
      <option value="">Pilih Lokasi</option>
      <?php foreach($locations as $l): ?>
        <option value="<?= $l['LocationID'] ?>"><?= htmlspecialchars($l['LocationName']) ?></option>
      <?php endforeach; ?>
    </select>
    <label>Waktu Mulai
      <small class="form-desc">Tentukan tanggal dan jam mulai event</small>
    </label>
    <input type="datetime-local" name="waktu_mulai" required>
    <label>Waktu Selesai
      <small class="form-desc">Tentukan tanggal dan jam selesai event</small>
    </label>
    <input type="datetime-local" name="waktu_selesai" required>
    <label>Deskripsi Acara
      <small class="form-desc">Penjelasan/notes singkat tentang event (opsional)</small>
    </label>
    <textarea name="deskripsi" placeholder="Deskripsi acara (opsional)" style="flex:1;min-width:140px;"></textarea>
    <button type="submit">Tambah Jadwal</button>
  </form>
</section>
<?php endif; ?>

  <!-- ==== TABEL JADWAL ==== -->
  <section class="jadwal-section">
    <h1>Jadwal Bazaar</h1>
    <div class="table-wrap">
      <table>
        <thead>
          <tr>
            <th>Nama Bazaar</th>
            <th>Nama Event</th>
            <th>Deskripsi</th>
            <th>Lokasi</th>
            <th>Alamat Lokasi</th>
            <th>Organizer</th>
            <th>Kontak Organizer</th>
            <th>Mulai</th>
            <th>Selesai</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($jadwal as $j): ?>
            <tr>
              <td><?= htmlspecialchars($j['BazaarName']) ?></td>
              <td><?= htmlspecialchars($j['NamaEvent']) ?></td>
              <td class="desc-schedule"><?= htmlspecialchars($j['Deskripsi']) ?></td>
              <td><?= htmlspecialchars($j['LocationName']) ?></td>
              <td><?= htmlspecialchars($j['LocationAddress']) ?></td>
              <td><?= htmlspecialchars($j['OrganizerName']) ?></td>
              <td><?= htmlspecialchars($j['OrganizerContact']) ?></td>
              <td><?= date('d/m/Y H:i', strtotime($j['WaktuMulai'])) ?></td>
              <td><?= date('d/m/Y H:i', strtotime($j['WaktuSelesai'])) ?></td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
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
      <<p>&copy; 2025 Eventra. All rights reserved.</p>
    </div>
  </footer>
</body>
</html>
