<?php
/**
 * 🛠️ UTILITY TO UPDATE ONLINE HOSTINGER SETTINGS
 * Run this by uploading to Hostinger and visiting:
 * https://erd.traxengps.in/update_online_settings.php
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once 'db_connect.php';

try {
    // 1. Ensure system_settings table exists
    $conn->exec("CREATE TABLE IF NOT EXISTS system_settings (
        key_name VARCHAR(100) PRIMARY KEY,
        key_value TEXT
    )");

    // 2. Update the WhatsApp Gateway URL to the provided Localtunnel URL
    $requestedUrl = trim((string)($_GET['url'] ?? $_POST['url'] ?? ''));
    $tunnelUrl = $requestedUrl ?: 'https://great-hoops-lick.loca.lt';

    if (!preg_match('~^https://[a-z0-9-]+\.loca\.lt/?$~i', $tunnelUrl)) {
        throw new Exception('Invalid tunnel URL supplied.');
    }
    $tunnelUrl = rtrim($tunnelUrl, '/');
    
    $stmt = $conn->prepare("INSERT INTO system_settings (key_name, key_value) VALUES ('whatsapp_gateway_url', ?) 
                            ON DUPLICATE KEY UPDATE key_value = ?");
    $stmt->execute([$tunnelUrl, $tunnelUrl]);

    echo "<h2>🎉 SUCCESS! Hostinger Database Updated!</h2>";
    echo "<p>Your Live Dashboard is now linked to your local WhatsApp Gateway at: <b>{$tunnelUrl}</b></p>";
    echo "<p>You can now close this tab and run your broadcast from the Live Dashboard!</p>";

} catch (Exception $e) {
    echo "<h2>❌ ERROR:</h2><p>" . $e->getMessage() . "</p>";
}
?>
