<?php
// upgrade_v8.php - CRM & Google Business Integration
include 'db_connect.php';
header('Content-Type: text/plain');

try {
    echo "🏗️ Starting v8.0 CRM & Google Integration Upgrade...\n";

    // 1. Create CRM Leads Table
    $conn->exec("CREATE TABLE IF NOT EXISTS `crm_leads` (
        `id` INT AUTO_INCREMENT PRIMARY KEY,
        `customer_name` VARCHAR(255) NOT NULL,
        `mobile_number` VARCHAR(20) NOT NULL,
        `location` VARCHAR(255),
        `interest` VARCHAR(100),
        `status` ENUM('NEW', 'INTERESTED', 'HOT', 'FOLLOWUP', 'CONVERTED', 'CLOSED') DEFAULT 'NEW',
        `source` VARCHAR(50) DEFAULT 'DIRECT',
        `followup_date` DATE,
        `last_remark` TEXT,
        `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )");
    echo "✅ CRM Lead Management Table Ready\n";

    // 2. Add Google Review URL to Settings
    $stmt = $conn->query("DESCRIBE settings");
    $cols = $stmt->fetchAll(PDO::FETCH_COLUMN);
    if (!in_array('google_review_url', $cols)) {
        $conn->exec("ALTER TABLE settings ADD `google_review_url` TEXT");
        echo "✅ Google Review Link Setting Added\n";
    }

    echo "\n🏆 v8.0 UPGRADE COMPLETE! CRM and Google Integration systems are ready.\n";

} catch (Exception $e) {
    echo "❌ ERROR: " . $e->getMessage();
}
?>
