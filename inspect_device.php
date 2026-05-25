<?php
include 'db_connect.php';
header('Content-Type: application/json');
$q = $conn->query("DESCRIBE device_master");
echo json_encode($q->fetchAll(PDO::FETCH_ASSOC));
?>
