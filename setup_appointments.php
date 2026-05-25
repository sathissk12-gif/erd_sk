<?php
require_once 'db_connect.php';

$sql = "
CREATE TABLE IF NOT EXISTS appointment_log (
    id INT AUTO_INCREMENT PRIMARY KEY,
    customer_name VARCHAR(255),
    mobile_number VARCHAR(20),
    vehicle_no VARCHAR(50),
    appointment_date DATE,
    appointment_time TIME,
    purpose TEXT,
    status ENUM('Pending', 'Completed', 'Cancelled') DEFAULT 'Pending',
    reminder_sent TINYINT(1) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);";

try {
    $conn->exec($sql);
    echo "Appointment table created successfully.";
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}
?>
