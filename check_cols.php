<?php
include 'db_connect.php';
$q = $conn->query("DESCRIBE renewal_log");
echo json_encode($q->fetchAll(PDO::FETCH_COLUMN));
