<?php
include 'db_connect.php';

echo "<h2>🚀 Notification System Upgrade Setup</h2>";

try {
    // 1. Tokens Table
    $conn->exec("CREATE TABLE IF NOT EXISTS user_tokens (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_email VARCHAR(255),
        fcm_token TEXT NOT NULL,
        platform VARCHAR(20) DEFAULT 'ANDROID',
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        UNIQUE KEY (fcm_token(255))
    )");
    echo "✅ user_tokens table ready<br>";

    // 2. Logs Table with enhanced columns
    $conn->exec("CREATE TABLE IF NOT EXISTS notification_logs (
        id INT AUTO_INCREMENT PRIMARY KEY,
        title VARCHAR(255),
        message TEXT,
        type VARCHAR(50) DEFAULT 'general',
        target VARCHAR(255),
        is_read TINYINT(1) DEFAULT 0,
        related_id INT DEFAULT NULL,
        notification_data TEXT DEFAULT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        INDEX idx_type (type),
        INDEX idx_read (is_read),
        INDEX idx_created (created_at)
    )");
    echo "✅ notification_logs table upgraded with enhanced columns<br>";

    // 3. Add columns if they don't exist (for existing tables)
    try {
        $conn->exec("ALTER TABLE notification_logs ADD COLUMN type VARCHAR(50) DEFAULT 'general' AFTER target");
    } catch (Exception $e) { echo "ℹ️ type column already exists<br>"; }
    
    try {
        $conn->exec("ALTER TABLE notification_logs ADD COLUMN is_read TINYINT(1) DEFAULT 0 AFTER target");
    } catch (Exception $e) { echo "ℹ️ is_read column already exists<br>"; }
    
    try {
        $conn->exec("ALTER TABLE notification_logs ADD COLUMN related_id INT DEFAULT NULL AFTER is_read");
    } catch (Exception $e) { echo "ℹ️ related_id column already exists<br>"; }

    try {
        $conn->exec("ALTER TABLE notification_logs ADD COLUMN notification_data TEXT DEFAULT NULL AFTER related_id");
    } catch (Exception $e) { echo "ℹ️ notification_data column already exists<br>"; }

    // 4. Add indexes
    try { $conn->exec("CREATE INDEX idx_type ON notification_logs(type)"); } catch (Exception $e) {}
    try { $conn->exec("CREATE INDEX idx_read ON notification_logs(is_read)"); } catch (Exception $e) {}
    try { $conn->exec("CREATE INDEX idx_created ON notification_logs(created_at)"); } catch (Exception $e) {}

    echo "<br><b>✅ Setup Complete!</b><br>";
    echo "<a href='notification_center.php' style='display:inline-block;margin-top:10px;padding:10px 20px;background:#8b5cf6;color:white;text-decoration:none;border-radius:10px;'>📬 Open Notification Center</a>";

} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage();
}
?>
