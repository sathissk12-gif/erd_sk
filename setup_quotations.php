<?php
require_once 'db_connect.php';

$sql = "
CREATE TABLE IF NOT EXISTS quotation_log (
    id INT AUTO_INCREMENT PRIMARY KEY,
    uid VARCHAR(50) UNIQUE,
    quotation_no VARCHAR(50),
    quotation_date DATE,
    customer_name VARCHAR(255),
    mobile_number VARCHAR(20),
    location VARCHAR(255),
    device_model VARCHAR(100),
    software_name VARCHAR(100),
    software_duration VARCHAR(50),
    sim_type VARCHAR(50),
    relay VARCHAR(10) DEFAULT 'NO',
    total_amount DECIMAL(10,2),
    discount_amount DECIMAL(10,2) DEFAULT 0,
    valid_until DATE,
    sales_person VARCHAR(100),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);";

try {
    $conn->exec($sql);
    echo "Quotation table created successfully.";
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}
?>
