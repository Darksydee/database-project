<?php
require_once '../config/database.php';

$userEmail = $_GET['email'];

$sql = "SELECT u.Email, t.TransactionDate, ti.TicketType, t.Quantity, t.TotalAmount, t.PaymentStatus
        FROM Transaction t
        JOIN Ticket ti ON t.TicketID = ti.TicketID
        JOIN User u ON u.Email = :email";

$stmt = $pdo->prepare($sql);
$stmt->execute(["email" => $userEmail]);

echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
