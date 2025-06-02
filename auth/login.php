<?php
require_once '../config/database.php';

$data = json_decode(file_get_contents("php://input"));

$sql = "SELECT * FROM User WHERE Email = ?";
$stmt = $pdo->prepare($sql);
$stmt->execute([$data->Email]);

$user = $stmt->fetch(PDO::FETCH_ASSOC);

if ($user && password_verify($data->Password, $user['Password'])) {
    echo json_encode(["success" => true, "user" => $user]);
} else {
    echo json_encode(["success" => false, "message" => "Invalid credentials"]);
}
