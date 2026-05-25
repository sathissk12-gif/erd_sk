<?php
include 'db_connect.php';

echo "<h2>Notification System Setup</h2>";

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

    // 2. Logs Table
    $conn->exec("CREATE TABLE IF NOT EXISTS notification_logs (
        id INT AUTO_INCREMENT PRIMARY KEY,
        title VARCHAR(255),
        message TEXT,
        target VARCHAR(255),
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )");
    echo "✅ notification_logs table ready<br>";

    echo "<br><b>Setup Complete!</b><br>";
    echo "<a href='api_fcm.php?action=test_push&title=Welcome&message=System setup complete'>Click here to send a Test Push</a>";

} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage();
}
?>
