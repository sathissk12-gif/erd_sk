<?php
include 'db_connect.php';

try {
    $sql = "CREATE TABLE IF NOT EXISTS user_tokens (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_email VARCHAR(100),
        fcm_token TEXT NOT NULL,
        platform VARCHAR(20) DEFAULT 'ANDROID',
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        UNIQUE(user_email, fcm_token(100))
    )";
    $conn->exec($sql);
    echo "Table 'user_tokens' created successfully!";
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}
?>
