<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Scan QR Code - Eventra</title>
  <link rel="stylesheet" href="../css/scan_qr.css" />
  <!-- Google Fonts: Poppins -->
  <link href="https://fonts.googleapis.com/css?family=Poppins:700,400&display=swap" rel="stylesheet">
  <script src="https://unpkg.com/html5-qrcode" type="text/javascript"></script>
</head>
<body>

  <!-- Header -->
  <header>
    <div class="logo">
      <img src="../public/logo eventra.jpg" alt="Logo">
      <span>Eventra</span>
    </div>
    <nav>
      <a href="dashboard.php">DASHBOARD</a>
      <a href="tenant.php">TENANT</a>
      <a href="scan_qr.php" style="font-weight:bold">SCAN QR</a>
      <a href="schedule.php">JADWAL</a>
      <a href="tiket.php">TIKET</a>
      <a href="produk.php">PRODUK</a>
      <a href="feedback.php">FEEDBACK</a>
      <a href="account.php">ACCOUNT</a>
    </nav>
  </header>

  <!-- SCAN QR SECTION -->
  <main>
    <div class="scan-container">
      <h1>SCAN QR CODE.</h1>
      <p class="scan-desc">Silahkan Scan Tiket QR Code yang sudah<br> dilakukan Pembelian</p>
      <button class="scan-btn" id="startBtn">SCAN QR CODE</button>
      <!-- Tempat hasil scan QR -->
      <div id="reader" style="display:none; margin: 32px auto;"></div>
      <p id="result" class="scan-result"></p>
    </div>
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

  <script>
    const startBtn = document.getElementById("startBtn");
    const resultEl = document.getElementById("result");
    let html5QrCode;

    startBtn.addEventListener("click", () => {
      const qrRegion = document.getElementById("reader");
      qrRegion.style.display = "block";
      startBtn.style.display = "none";

      html5QrCode = new Html5Qrcode("reader");
      Html5Qrcode.getCameras().then(cameras => {
        if (cameras && cameras.length) {
          html5QrCode.start(
            { facingMode: "environment" },
            { fps: 10, qrbox: 250 },
            qrCodeMessage => {
              resultEl.innerText = `QR Terdeteksi: ${qrCodeMessage}`;
              html5QrCode.stop();
              qrRegion.style.display = "none";
              startBtn.style.display = "block";
            },
            errorMessage => {
              // silent error
            }
          );
        }
      }).catch(err => {
        resultEl.innerText = "Tidak dapat mengakses kamera: " + err;
        qrRegion.style.display = "none";
        startBtn.style.display = "block";
      });
    });
  </script>
</body>
</html>
