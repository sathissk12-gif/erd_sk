<?php
/**
 * SK WHATSAPP API WRAPPER v1.0
 * Wraps local Node.js WhatsApp Gateway calls.
 */

function sendWhatsAppMessage($number, $message) {
    global $conn;
    
    if (!isset($conn)) {
        try {
            @include_once __DIR__ . '/db_connect.php';
        } catch (Exception $e) {}
    }
    
    // Fetch dynamic gateway URL from system_settings if exists
    $gatewayUrl = 'http://localhost:3000';
    
    // Check if running locally (CLI or local web server)
    $isLocal = (php_sapi_name() === 'cli') || 
                (isset($_SERVER['SERVER_ADDR']) && in_array($_SERVER['SERVER_ADDR'], ['127.0.0.1', '::1'])) ||
                (isset($_SERVER['HTTP_HOST']) && (strpos($_SERVER['HTTP_HOST'], 'localhost') !== false || strpos($_SERVER['HTTP_HOST'], '127.0.0.1') !== false));
                
    if (!$isLocal && isset($conn)) {
        try {
            $q = $conn->query("SELECT key_value FROM system_settings WHERE key_name = 'whatsapp_gateway_url'");
            if ($q) {
                $val = $q->fetchColumn();
                if ($val) $gatewayUrl = rtrim($val, '/');
            }
        } catch (Exception $e) {}
    }
    
    $url = $gatewayUrl . '/send';
    
    $data = [
        'number' => $number,
        'message' => $message
    ];
    
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json',
        'Accept: application/json',
        'Bypass-Tunnel-Reminder: true'
    ]);
    curl_setopt($ch, CURLOPT_TIMEOUT, 15);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    
    $response = curl_exec($ch);
    $error = curl_error($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    if ($error) {
        return [
            'success' => false,
            'error' => 'Gateway request failed: ' . $error
        ];
    }
    
    $decoded = json_decode($response, true);
    if ($httpCode !== 200) {
        return [
            'success' => false,
            'error' => $decoded['error'] ?? 'Unknown gateway error'
        ];
    }
    
    return [
        'success' => true,
        'message_id' => $decoded['messageId'] ?? '',
        'to' => $decoded['to'] ?? $number
    ];
}

// Handle Direct AJAX API calls
if (basename($_SERVER['PHP_SELF']) === 'api_whatsapp_send.php') {
    header('Content-Type: application/json');
    
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        echo json_encode(['success' => false, 'error' => 'Only POST requests allowed']);
        exit;
    }
    
    $number = $_POST['number'] ?? '';
    $message = $_POST['message'] ?? '';
    $renewalId = isset($_POST['renewal_id']) ? (int)$_POST['renewal_id'] : 0;
    
    if (!$number || !$message) {
        echo json_encode(['success' => false, 'error' => 'Missing number or message parameters']);
        exit;
    }
    
    // Auto-include db_connect.php if not already done to have $conn
    if (!isset($conn)) {
        try {
            @include_once __DIR__ . '/db_connect.php';
        } catch (Exception $e) {}
    }
    
    // Ensure log tables exist
    if (isset($conn)) {
        try {
            $conn->exec("CREATE TABLE IF NOT EXISTS reminder_sent_logs (
                renewal_id INT NOT NULL,
                sent_date DATE NOT NULL,
                PRIMARY KEY (renewal_id, sent_date)
            )");
            $conn->exec("CREATE TABLE IF NOT EXISTS whatsapp_send_logs (
                id INT AUTO_INCREMENT PRIMARY KEY,
                renewal_id INT NOT NULL DEFAULT 0,
                vehicle VARCHAR(100) DEFAULT '',
                customer VARCHAR(200) DEFAULT '',
                mobile VARCHAR(20) DEFAULT '',
                status ENUM('sent','failed') NOT NULL DEFAULT 'failed',
                error_message TEXT DEFAULT NULL,
                sent_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                INDEX idx_renewal (renewal_id),
                INDEX idx_sent_at (sent_at),
                INDEX idx_status (status)
            )");
        } catch (Exception $e) {}
    }
    
    // Check if already sent today to block double sending
    if ($renewalId > 0 && isset($conn)) {
        try {
            $check = $conn->prepare("SELECT COUNT(*) FROM reminder_sent_logs WHERE renewal_id = ? AND sent_date = CURDATE()");
            $check->execute([$renewalId]);
            if ($check->fetchColumn() > 0) {
                echo json_encode(['success' => false, 'error' => 'This renewal reminder was already sent today!']);
                exit;
            }
        } catch (Exception $e) {}
    }
    
    // Look up vehicle/customer info for logging
    $vehicleName = '';
    $customerName = '';
    if ($renewalId > 0 && isset($conn)) {
        try {
            $tables = $conn->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
            $tableName = in_array('renewal_log', $tables) ? 'renewal_log' : (in_array('RENEWAL_LOG', $tables) ? 'RENEWAL_LOG' : '');
            if ($tableName) {
                $infoStmt = $conn->prepare("SELECT vehicle_no, customer_name, customer FROM `$tableName` WHERE id = ?");
                $infoStmt->execute([$renewalId]);
                $infoRow = $infoStmt->fetch(PDO::FETCH_ASSOC);
                if ($infoRow) {
                    $vehicleName = $infoRow['vehicle_no'] ?? '';
                    $customerName = $infoRow['customer_name'] ?? $infoRow['customer'] ?? '';
                }
            }
        } catch (Exception $e) {}
    }
    
    $res = sendWhatsAppMessage($number, $message);
    
    // Log to detailed whatsapp_send_logs (both success & failure)
    if (isset($conn)) {
        try {
            $logStmt = $conn->prepare("INSERT INTO whatsapp_send_logs (renewal_id, vehicle, customer, mobile, status, error_message) VALUES (?, ?, ?, ?, ?, ?)");
            $logStmt->execute([
                $renewalId,
                $vehicleName,
                $customerName,
                $number,
                $res['success'] ? 'sent' : 'failed',
                $res['success'] ? null : ($res['error'] ?? 'Unknown error')
            ]);
        } catch (Exception $e) {}
    }
    
    // Log successful send to reminder_sent_logs (dedup)
    if ($res['success'] && $renewalId > 0 && isset($conn)) {
        try {
            $conn->prepare("INSERT IGNORE INTO reminder_sent_logs (renewal_id, sent_date) VALUES (?, CURDATE())")->execute([$renewalId]);
        } catch (Exception $e) {}
    }
    
    echo json_encode($res);
}
?>
