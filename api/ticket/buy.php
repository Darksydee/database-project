<?php
require_once '../config/database.php';

$data = json_decode(file_get_contents("php://input"));

$sql = "INSERT INTO Transaction (TicketID, TransactionDate, Quantity, TotalAmount, PaymentMethodID, PaymentStatus)
        VALUES (?, NOW(), ?, ?, ?, 'Pending')";
$stmt = $pdo->prepare($sql);
$success = $stmt->execute([
    $data->TicketID,
    $data->Quantity,
    $data->TotalAmount,
    $data->PaymentMethodID
]);

echo json_encode(["success" => $success]);
