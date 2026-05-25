<?php
require_once 'db_connect.php';

try {
    $sql = "CREATE TABLE IF NOT EXISTS meta_leads (
        id INT AUTO_INCREMENT PRIMARY KEY,
        lead_id VARCHAR(100) UNIQUE NOT NULL,
        form_id VARCHAR(100) NOT NULL,
        page_id VARCHAR(100) NOT NULL,
        full_name VARCHAR(255) NULL,
        phone_number VARCHAR(100) NULL,
        email VARCHAR(255) NULL,
        lead_data JSON NULL,
        created_time DATETIME NOT NULL,
        is_processed TINYINT(1) DEFAULT 0
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";

    $conn->exec($sql);
    echo "Meta Leads table created successfully! You can now receive leads.";
} catch (PDOException $e) {
    die("Error creating table: " . $e->getMessage());
}
?>
