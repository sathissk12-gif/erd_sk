<?php
include 'db_config.php';

$sql = "CREATE TABLE IF NOT EXISTS sim_settlement_log (
    id INT AUTO_INCREMENT PRIMARY KEY,
    settle_date DATETIME DEFAULT CURRENT_TIMESTAMP,
    sales_amount DECIMAL(10,2) DEFAULT 0,
    sales_vehicles TEXT,
    renewal_amount DECIMAL(10,2) DEFAULT 0,
    renewal_vehicles TEXT,
    total_amount DECIMAL(10,2) DEFAULT 0,
    status VARCHAR(20) DEFAULT 'PENDING',
    txn_id VARCHAR(100)
)";

if ($conn->query($sql) === TRUE) {
    echo "Table sim_settlement_log created successfully";
} else {
    echo "Error creating table: " . $conn->error;
}
?>
