<?php
include 'db_connect.php'; // In ERD
header('Content-Type: text/plain');

echo "--- ERD LEDGER REPAIR START ---\n";
try {
    $conn->exec("CREATE TABLE IF NOT EXISTS dealer_ledger (id INT AUTO_INCREMENT PRIMARY KEY)");
    
    $colsToAdd = [
        "dealer_name" => "VARCHAR(255)",
        "imei" => "VARCHAR(50)",
        "software" => "VARCHAR(100)",
        "sim_no" => "VARCHAR(50)",
        "date" => "DATE",
        "selling_price" => "DECIMAL(10,2)",
        "profit" => "DECIMAL(10,2)",
        "office" => "VARCHAR(50)",
        "txn_id" => "VARCHAR(100)"
    ];

    $st = $conn->query("DESCRIBE dealer_ledger");
    $existing = $st->fetchAll(PDO::FETCH_COLUMN);

    foreach ($colsToAdd as $col => $type) {
        if (!in_array($col, $existing)) {
            echo "Adding $col...\n";
            $conn->exec("ALTER TABLE dealer_ledger ADD COLUMN `$col` $type");
        } else {
            echo "$col already exists.\n";
        }
    }
    echo "ERD SUCCESS!\n";
} catch(Exception $e) { echo "ERD ERROR: " . $e->getMessage() . "\n"; }
?>
