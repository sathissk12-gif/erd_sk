<?php
require_once 'db_connect.php';
try {
    echo "--- DEALER_LEDGER COLUMNS ---\n";
    $stmt = $conn->query("DESCRIBE dealer_ledger");
    $cols = $stmt->fetchAll(PDO::FETCH_ASSOC);
    foreach($cols as $c) {
        echo "Field: " . $c['Field'] . " | Type: " . $c['Type'] . "\n";
    }
    
    echo "\n--- LATEST 10 ENTRIES ---\n";
    $stmt = $conn->query("SELECT * FROM dealer_ledger ORDER BY id DESC LIMIT 10");
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
    foreach($rows as $r) {
        print_r($r);
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
?>
