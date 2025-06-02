<?php
require_once '../config/database.php';

$sql = "SELECT ti.TicketType, SUM(t.Quantity) AS TotalTicketsSold, SUM(t.TotalAmount) AS TotalRevenue
        FROM Transaction t
        JOIN Ticket ti ON t.TicketID = ti.TicketID
        WHERE t.PaymentStatus = 'Completed'
        GROUP BY ti.TicketType";

echo json_encode($pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC));
