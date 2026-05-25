<?php
// fix_renewal_schema.php - Database Patch for Renewal Module
include 'db_connect.php';
header('Content-Type: text/plain');

echo "🛠️ Starting Renewal Module Schema Patch...\n";

try {
    // 1. Check/Add 'uid' & 'mobile_no' to renewal_log
    $stmt = $conn->query("DESCRIBE renewal_log");
    $cols = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    if (!in_array('expiry_date', $cols)) {
        $conn->exec("ALTER TABLE renewal_log ADD `expiry_date` DATE DEFAULT NULL");
        echo "✅ Column 'expiry_date' added to renewal_log\n";
    }
    if (!in_array('processed', $cols)) {
        $conn->exec("ALTER TABLE renewal_log ADD `processed` VARCHAR(10) DEFAULT 'NO'");
        echo "✅ Column 'processed' added to renewal_log\n";
    }
    if (!in_array('office_settled', $cols)) {
        $conn->exec("ALTER TABLE renewal_log ADD `office_settled` VARCHAR(10) DEFAULT NULL");
        echo "✅ Column 'office_settled' added to renewal_log\n";
    }
    if (!in_array('uid', $cols)) {
        $conn->exec("ALTER TABLE renewal_log ADD `uid` VARCHAR(50) DEFAULT NULL");
        echo "✅ Column 'uid' added to renewal_log\n";
    }
    if (!in_array('mobile_no', $cols)) {
        $conn->exec("ALTER TABLE renewal_log ADD `mobile_no` VARCHAR(20) DEFAULT NULL");
        echo "✅ Column 'mobile_no' added to renewal_log\n";
    }
    if (!in_array('mobile1', $cols)) {
        $conn->exec("ALTER TABLE renewal_log ADD `mobile1` VARCHAR(20) DEFAULT NULL");
        echo "✅ Column 'mobile1' added to renewal_log\n";
    }
    if (!in_array('mobile2', $cols)) {
        $conn->exec("ALTER TABLE renewal_log ADD `mobile2` VARCHAR(20) DEFAULT NULL");
        echo "✅ Column 'mobile2' added to renewal_log\n";
    }
    if (!in_array('mobile3', $cols)) {
        $conn->exec("ALTER TABLE renewal_log ADD `mobile3` VARCHAR(20) DEFAULT NULL");
        echo "✅ Column 'mobile3' added to renewal_log\n";
    }
    if (!in_array('mobile_no', $cols)) {
        $conn->exec("ALTER TABLE renewal_log ADD `mobile_no` VARCHAR(20) DEFAULT NULL");
        echo "✅ Column 'mobile_no' added to renewal_log\n";
    }
    if (!in_array('profit', $cols)) {
        $conn->exec("ALTER TABLE renewal_log ADD `profit` DECIMAL(10,2) DEFAULT 0");
        echo "✅ Column 'profit' added to renewal_log\n";
    }

    // 2. Ensure renewal_invoice_log exists with all required columns
    $conn->exec("CREATE TABLE IF NOT EXISTS `renewal_invoice_log` (
        `id` INT AUTO_INCREMENT PRIMARY KEY,
        `uid` VARCHAR(50) NOT NULL,
        `date` DATE NOT NULL,
        `invoice_num` VARCHAR(50) NOT NULL,
        `customer_name` VARCHAR(255),
        `vehicle_num` VARCHAR(50),
        `software_type` VARCHAR(100),
        `amount` DECIMAL(10,2),
        `received_amount` DECIMAL(10,2),
        `mobile_no` VARCHAR(20),
        `amount_words` TEXT,
        `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB");
    echo "✅ Table 'renewal_invoice_log' verified/created\n";

    // 3. Confirm all columns in renewal_invoice_log
    $stmt = $conn->query("DESCRIBE renewal_invoice_log");
    $cols = $stmt->fetchAll(PDO::FETCH_COLUMN);
    if (!in_array('amount_words', $cols)) {
        $conn->exec("ALTER TABLE renewal_invoice_log ADD `amount_words` TEXT DEFAULT NULL");
        echo "✅ Column 'amount_words' added to renewal_invoice_log\n";
    }
    if (!in_array('mobile_no', $cols)) {
        $conn->exec("ALTER TABLE renewal_invoice_log ADD `mobile_no` VARCHAR(20) DEFAULT NULL");
        echo "✅ Column 'mobile_no' added to renewal_invoice_log\n";
    }

    echo "\n🏆 SUCCESS! Renewal module database is now up to date.\n";
    echo "You can now go back to Renewal Console and update records.\n";

} catch (Exception $e) {
    echo "❌ ERROR: " . $e->getMessage();
}
?>
