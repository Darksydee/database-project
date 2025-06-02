<?php
session_start();
session_destroy();
// Bisa redirect ke login.php (atau dashboard.php jika guest view)
header('Location: login.php');
exit;
?>
