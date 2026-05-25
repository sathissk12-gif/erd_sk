<?php
// Schema Doctor v7.0 - THE FINANCIAL & AUTOMATION ENGINE
include 'db_connect.php';
header('Content-Type: text/plain');

try {
    echo "🏗️ Starting v7.0 System Expansion...\n";

    // 1. Create Expenses Table (Feature 5)
    $conn->exec("CREATE TABLE IF NOT EXISTS `expenses` (
        `id` INT AUTO_INCREMENT PRIMARY KEY,
        `date` DATE NOT NULL,
        `category` VARCHAR(50) NOT NULL,
        `amount` DECIMAL(10,2) NOT NULL,
        `remark` TEXT,
        `payment_mode` VARCHAR(20) DEFAULT 'CASH',
        `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )");
    echo "✅ Expense Management Table Ready\n";

    // 2. Add Threshold to Settings (Feature 7)
    $stmt = $conn->query("DESCRIBE settings");
    $cols = $stmt->fetchAll(PDO::FETCH_COLUMN);
    if (!in_array('low_stock_threshold', $cols)) {
        $conn->exec("ALTER TABLE settings ADD `low_stock_threshold` INT DEFAULT 5");
        echo "✅ Stock Alert Settings Added\n";
    }

    // 3. Automation Task Queue (Feature 2)
    $conn->exec("CREATE TABLE IF NOT EXISTS `automation_queue` (
        `id` INT AUTO_INCREMENT PRIMARY KEY,
        `type` VARCHAR(20) NOT NULL, /* RENEWAL, SERVICE */
        `target_mobile` VARCHAR(15) NOT NULL,
        `message` TEXT NOT NULL,
        `status` VARCHAR(20) DEFAULT 'PENDING', /* PENDING, SENT, FAILED */
        `scheduled_at` DATE,
        `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )");
    echo "✅ Automation Queue Initialized\n";

    echo "\n🏆 v7.0 CORE ENGINE UPGRADED! Ready for Expenses, Alerts, and Automation.\n";

} catch (Exception $e) {
    echo "❌ ERROR: " . $e->getMessage();
}
?>
