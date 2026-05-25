<?php
include 'db_connect.php';
header('Content-Type: text/plain');

echo "--- ERD DEVICE MASTER COLUMNS ---\n";
try {
    $st = $conn->query("DESCRIBE device_master");
    print_r($st->fetchAll(PDO::FETCH_ASSOC));
} catch(Exception $e) { echo "ERD Error: ".$e->getMessage()."\n"; }

echo "\n--- ERD DEALER LEDGER COLUMNS ---\n";
try {
    $st = $conn->query("DESCRIBE dealer_ledger");
    print_r($st->fetchAll(PDO::FETCH_ASSOC));
} catch(Exception $e) { echo "ERD Ledger Error: ".$e->getMessage()."\n"; }

echo "\n--- SAMPLE SOLD DEVICES (ERD) ---\n";
try {
    $st = $conn->query("SELECT * FROM device_master WHERE status='SOLD' LIMIT 3");
    print_r($st->fetchAll(PDO::FETCH_ASSOC));
} catch(Exception $e) { echo "ERD Sample Error: ".$e->getMessage()."\n"; }
?>
