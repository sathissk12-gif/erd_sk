<?php
/**
 * 🚀 GLOBAL NOTIFICATION ENGINE (FCM)
 * Use this file to manage app tokens and trigger native push notifications.
 */

require_once 'db_connect.php';

require_once 'db_connect.php';

// Fetch FCM Key from database settings
$fcmKeyRes = $conn->query("SELECT key_value FROM system_settings WHERE key_name = 'fcm_server_key'")->fetch();
$fcmKey = $fcmKeyRes ? $fcmKeyRes['key_value'] : '';
define('FCM_SERVER_KEY', $fcmKey); 

/**
 * Sends a native notification to specific tokens or a topic.
 */
function sendPushNotification($title, $body, $target = '/topics/all', $extraData = []) {
    if (FCM_SERVER_KEY === 'YOUR_FCM_SERVER_KEY_HERE') {
        return ['success' => false, 'message' => 'FCM Server Key not configured'];
    }

    $url = 'https://fcm.googleapis.com/fcm/send';

    $notification = [
        'title' => $title,
        'body' => $body,
        'sound' => 'default',
        'badge' => '1',
        'click_action' => 'OPEN_ACTIVITY_1', // Match this in Android manifest
        'icon' => 'ic_launcher'
    ];

    $payload = [
        'to' => $target,
        'notification' => $notification,
        'priority' => 'high',
        'data' => array_merge($extraData, [
            'click_action' => 'FLUTTER_NOTIFICATION_CLICK',
            'timestamp' => date('Y-m-d H:i:s')
        ])
    ];

    $headers = [
        'Authorization: key=' . FCM_SERVER_KEY,
        'Content-Type: application/json'
    ];

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    // Log notification to database for history
    global $conn;
    try {
        $stmt = $conn->prepare("INSERT INTO notification_logs (title, message, target) VALUES (?, ?, ?)");
        $stmt->execute([$title, $body, $target]);
    } catch (Exception $e) { /* Table might not exist yet */ }

    return [
        'http_code' => $httpCode,
        'response' => json_decode($response, true)
    ];
}

// --- 🛠️ API ENDPOINTS ---
if (isset($_REQUEST['action'])) {
    header('Content-Type: application/json');
    $action = $_REQUEST['action'];

    switch ($action) {
        case 'register_token':
            // Called by Android App on startup
            $token = $_POST['token'] ?? '';
            $email = $_POST['email'] ?? 'guest';
            if (!$token) {
                echo json_encode(['success' => false, 'message' => 'Token required']);
                exit;
            }

            try {
                $sql = "INSERT INTO user_tokens (user_email, fcm_token, platform) 
                        VALUES (?, ?, 'ANDROID') 
                        ON DUPLICATE KEY UPDATE updated_at = CURRENT_TIMESTAMP";
                $stmt = $conn->prepare($sql);
                $stmt->execute([$email, $token]);
                echo json_encode(['success' => true, 'message' => 'Token registered']);
            } catch (Exception $e) {
                echo json_encode(['success' => false, 'message' => $e->getMessage()]);
            }
            break;

        case 'test_push':
            // Simple manual test
            $title = $_POST['title'] ?? 'Test Notification';
            $message = $_POST['message'] ?? 'This is a sample alert from SK Logic Web.';
            $res = sendPushNotification($title, $message);
            echo json_encode($res);
            break;
            
        case 'get_history':
            // Fetch recent notifications for app inbox
            try {
                $stmt = $conn->query("SELECT * FROM notification_logs ORDER BY id DESC LIMIT 20");
                echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
            } catch (Exception $e) {
                echo json_encode([]);
            }
            break;
    }
}
?>
