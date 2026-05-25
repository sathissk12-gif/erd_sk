<?php
require_once 'db_connect.php';
try {
    $table = 'dealer_ledger';
    $stmt = $conn->query("DESCRIBE `$table` ");
    $cols = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    $priority = ['txn_id', 'transection_id', 'transaction_id'];
    $found = false;
    foreach ($priority as $p) {
        if (in_array($p, $cols)) {
            $found = $p;
            break;
        }
    }
    
    if ($found) {
        echo "✅ Column '$found' already exists in $table.\n";
    } else {
        echo "🏗️ Adding 'txn_id' column to $table...\n";
        $conn->exec("ALTER TABLE `$table` ADD `txn_id` VARCHAR(100) DEFAULT NULL AFTER `profit` ");
        echo "✅ 'txn_id' column added successfully.\n";
    }
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage();
}
?>
