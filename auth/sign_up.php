<?php
require_once '../config/database.php';

$data = json_decode(file_get_contents("php://input"));

if (!empty($data->Nama) && !empty($data->Email) && !empty($data->Password)) {
    $sql = "INSERT INTO User (Nama, Email, Password, NoHandphone) VALUES (?, ?, ?, '')";
    $stmt = $pdo->prepare($sql);
    $success = $stmt->execute([$data->Nama, $data->Email, password_hash($data->Password, PASSWORD_BCRYPT)]);

    echo json_encode(["success" => $success]);
} else {
    http_response_code(400);
    echo json_encode(["error" => "Invalid input"]);
}
