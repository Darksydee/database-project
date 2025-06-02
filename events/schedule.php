<?php
require_once '../config/database.php';

$sql = "SELECT s.NamaEvent, s.WaktuMulai, s.WaktuSelesai, l.LocationName, s.Deskripsi
        FROM Schedule s
        JOIN Location l ON s.LokasiID = l.LocationID";

echo json_encode($pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC));
