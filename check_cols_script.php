<?php
include 'db_connect.php';
$stmt = $conn->query("DESCRIBE renewal_log");
print_r($stmt->fetchAll(PDO::FETCH_COLUMN));
?>
