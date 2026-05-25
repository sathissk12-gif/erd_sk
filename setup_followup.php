<?php
include 'db_connect.php';

try {
    $sql = "CREATE TABLE IF NOT EXISTS payment_followups (
        id INT AUTO_INCREMENT PRIMARY KEY,
        uid VARCHAR(50) UNIQUE,
        customer_name VARCHAR(100) NOT NULL,
        mobile_no VARCHAR(20) NOT NULL,
        vehicle_no VARCHAR(20),
        software VARCHAR(100),
        amount_due DECIMAL(10, 2) DEFAULT 0,
        followup_date DATE,
        remark TEXT,
        status ENUM('PENDING', 'PAID') DEFAULT 'PENDING',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )";
    $conn->exec($sql);
    echo "Table 'payment_followups' created successfully!";
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}
?>
