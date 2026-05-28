<?php
/**
 * 🚀 SMART APPOINTMENT TABLE SETUP
 * Full schema with multi-level reminders, acknowledgements, and notification channels
 */
require_once 'db_connect.php';

$sql = "
CREATE TABLE IF NOT EXISTS appointment_log (
    id INT AUTO_INCREMENT PRIMARY KEY,
    customer_name VARCHAR(255) NOT NULL,
    mobile_number VARCHAR(20),
    vehicle_no VARCHAR(50),
    appointment_date DATE NOT NULL,
    appointment_time TIME NOT NULL,
    purpose TEXT,
    notes TEXT,
    status ENUM('Pending','Completed','Cancelled','Missed') DEFAULT 'Pending',
    reminder_minutes INT DEFAULT 10,
    notify_methods VARCHAR(100) DEFAULT 'push',
    reminder_sent TINYINT(1) DEFAULT 0,
    reminder_level INT DEFAULT 0 COMMENT '0=day_before, 1=upcoming, 2=due_now, 3=overdue',
    acknowledged_at DATETIME DEFAULT NULL,
    sms_sent TINYINT(1) DEFAULT 0,
    whatsapp_sent TINYINT(1) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
";

try {
    $conn->exec($sql);
    echo "✅ Smart appointment table created successfully.\n";
    
    // Add indexes for performance
    try {
        $conn->exec("CREATE INDEX idx_appt_date ON appointment_log(appointment_date)");
        $conn->exec("CREATE INDEX idx_appt_status ON appointment_log(status)");
        $conn->exec("CREATE INDEX idx_appt_reminder ON appointment_log(reminder_sent, acknowledged_at)");
        echo "✅ Performance indexes created.\n";
    } catch (Exception $e) {
        echo "ℹ️ Indexes may already exist.\n";
    }
    
} catch (PDOException $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}
?>
