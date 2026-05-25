<?php
include 'db_connect.php';
try {
    echo "--- DEVICE MASTER ---\n";
    $stmt = $conn->query("DESCRIBE device_master");
    $cols = $stmt->fetchAll(PDO::FETCH_ASSOC);
    foreach($cols as $c) {
        echo $c['Field'] . " (" . $c['Type'] . ")\n";
    }
    echo "\n--- DEALER_LEDGER SCHEMA ---\n";
    $stmt = $conn->query("DESCRIBE dealer_ledger");
    $cols = $stmt->fetchAll(PDO::FETCH_ASSOC);
    foreach($cols as $c) {
        echo $c['Field'] . " (" . $c['Type'] . ")\n";
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
?>
