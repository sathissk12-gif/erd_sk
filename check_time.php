<?php
require_once 'db_connect.php';
echo "PHP date: " . date('Y-m-d H:i:s') . "\n";
echo "PHP timezone: " . date_default_timezone_get() . "\n";
$dbTime = $conn->query("SELECT NOW(), CURDATE()")->fetch(PDO::FETCH_ASSOC);
echo "DB NOW(): " . $dbTime['NOW()'] . "\n";
echo "DB CURDATE(): " . $dbTime['CURDATE()'] . "\n";
?>
