<?php
require_once '../config/database.php';

$sql = "SELECT TicketID, TicketType, TicketPrice FROM Ticket";
$result = $pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC);

echo json_encode($result);
