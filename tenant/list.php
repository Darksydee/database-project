<?php
require_once '../config/database.php';

$sql = "SELECT Nama, Description, Kategori, Contact FROM Tenant";
echo json_encode($pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC));
