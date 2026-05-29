<?php
/**
 * 🚀 GLOBAL NOTIFICATION ENGINE (FCM v3)
 * Use this file to manage app tokens and trigger native push notifications.
 * Upgraded with Notification Inbox, Read/Unread tracking, and more.
 */

require_once 'db_connect.php';

// Fetch FCM Key from database settings
$fcmKeyRes = $conn->query("SELECT key_value FROM system_settings WHERE key_name = 'fcm_server_key'")->fetch();
$fcmKey = $fcmKeyRes ? $fcmKeyRes['key_value'] : '';
define('FCM_SERVER_KEY', $fcmKey); 

/**
 * 🎵 Resolve notification sound from settings with per-type fallback
 */
function resolveNotificationSound($type = 'default') {
    global $conn;
    static $settingsCache = null;
    
    if ($settingsCache === null) {
        $settingsCache = [];
        try {
            $res = $conn->query("SELECT key_name, key_value FROM system_settings WHERE key_name IN ('notification_sound','notification_sound_appt','notification_sound_renewal','notification_sound_payment','notification_sound_lead','notification_custom_sound','appt_sound_enabled')");
            foreach ($res->fetchAll(PDO::FETCH_ASSOC) as $row) {
                $settingsCache[$row['key_name']] = $row['key_value'];
            }
        } catch (Exception $e) {}
    }
    
    // If sound alerts globally disabled, return empty
    if (($settingsCache['appt_sound_enabled'] ?? '1') === '0') {
        return 'disabled';
    }
    
    // Per-type sound mapping
    $typeKeyMap = [
        'appointment' => 'notification_sound_appt',
        'renewal'     => 'notification_sound_renewal',
        'payment'     => 'notification_sound_payment',
        'lead'        => 'notification_sound_lead'
    ];
    
    $sound = '';
    if (isset($typeKeyMap[$type])) {
        $sound = $settingsCache[$typeKeyMap[$type]] ?? '';
    }
    
    // Fallback to global default
    if (empty($sound)) {
        $sound = $settingsCache['notification_sound'] ?? 'default';
    }
    
    // If custom, use custom sound filename
    if ($sound === 'custom') {
        $custom = $settingsCache['notification_custom_sound'] ?? '';
        if (!empty($custom)) {
            return $custom;
        }
        $sound = 'default'; // fallback
    }
    
    return $sound;
}

/**
 * 📳 Resolve vibration pattern from settings
 */
function resolveVibrationPattern() {
    global $conn;
    static $pattern = null;
    if ($pattern !== null) return $pattern;
    
    try {
        $enabled = $conn->query("SELECT key_value FROM system_settings WHERE key_name = 'notification_vibration'")->fetchColumn();
        if ($enabled === '0') {
            $pattern = 'disabled';
            return $pattern;
        }
        $p = $conn->query("SELECT key_value FROM system_settings WHERE key_name = 'notification_vibration_pattern'")->fetchColumn();
        $pattern = $p ?: 'standard';
    } catch (Exception $e) {
        $pattern = 'standard';
    }
    return $pattern;
}

/**
 * Sends a native notification to specific tokens or a topic.
 * @param string $title - Notification title
 * @param string $body - Notification body
 * @param string $target - FCM topic or token
 * @param array $extraData - Additional data payload
 * @param string $type - Notification type (appointment/renewal/payment/lead) for sound resolution
 * @param int|null $relatedId - Related record ID (appointment_id, etc.)
 */
function sendPushNotification($title, $body, $target = '/topics/all', $extraData = [], $type = 'default', $relatedId = null) {
    if (FCM_SERVER_KEY === 'YOUR_FCM_SERVER_KEY_HERE') {
        // Still log locally even if FCM not configured
        logNotification($title, $body, $type, $target, $relatedId, $extraData);
        return ['success' => false, 'message' => 'FCM Server Key not configured'];
    }

    $url = 'https://fcm.googleapis.com/fcm/send';
    
    // Resolve sound from settings
    $soundName = resolveNotificationSound($type);
    $vibrationPattern = resolveVibrationPattern();
    
    // Build vibration array for Android
    $vibrationArray = [];
    if ($vibrationPattern !== 'disabled') {
        switch ($vibrationPattern) {
            case 'double':   $vibrationArray = [100, 100, 100]; break;
            case 'long':     $vibrationArray = [500]; break;
            case 'rapid':    $vibrationArray = [200, 100, 200, 100, 200, 100, 500]; break;
            case 'heartbeat': $vibrationArray = [100, 200, 100, 500]; break;
            default:         $vibrationArray = [200, 100, 200]; break; // standard
        }
    }
    
    // If sound is disabled, omit from notification payload
    $notification = [
        'title' => $title,
        'body' => $body,
        'badge' => '1',
        'click_action' => 'OPEN_ACTIVITY_1',
        'icon' => 'ic_launcher'
    ];
    
    // Only add sound if enabled
    if ($soundName !== 'disabled') {
        $notification['sound'] = $soundName;
    }
    
    if (!empty($vibrationArray)) {
        $notification['vibrate'] = $vibrationArray;
    }

    $payload = [
        'to' => $target,
        'notification' => $notification,
        'priority' => 'high',
        'data' => array_merge($extraData, [
            'click_action' => 'FLUTTER_NOTIFICATION_CLICK',
            'timestamp' => date('Y-m-d H:i:s'),
            'sound' => $soundName,
            'vibration' => $vibrationPattern,
            'type' => $type,
            'related_id' => $relatedId
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
    
    // Log notification to database with enhanced fields
    logNotification($title, $body, $type, $target, $relatedId, $extraData);

    return [
        'http_code' => $httpCode,
        'response' => json_decode($response, true)
    ];
}

/**
 * 📝 Enhanced notification logging with type and read status
 */
function logNotification($title, $body, $type = 'general', $target = '', $relatedId = null, $extraData = []) {
    global $conn;
    try {
        $notifData = json_encode($extraData);
        $stmt = $conn->prepare("INSERT INTO notification_logs (title, message, type, target, related_id, notification_data, is_read) VALUES (?, ?, ?, ?, ?, ?, 0)");
        $stmt->execute([$title, $body, $type, $target, $relatedId, $notifData]);
        return $conn->lastInsertId();
    } catch (Exception $e) { 
        // Fallback to simple insert if columns don't exist yet
        try {
            $stmt = $conn->prepare("INSERT INTO notification_logs (title, message, target) VALUES (?, ?, ?)");
            $stmt->execute([$title, $body, $target]);
        } catch (Exception $e2) {}
        return null;
    }
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
            $sound = $_POST['sound'] ?? '';
            $type = $_POST['type'] ?? 'default';
            $res = sendPushNotification($title, $message, '/topics/all', ['type' => 'TEST'], $type);
            echo json_encode($res);
            break;
            
        case 'test_sound':
            // Test configured sound
            $sound = $_POST['sound'] ?? 'default';
            $vibration = $_POST['vibration'] ?? 'standard';
            $title = "🎵 Sound Test: " . strtoupper($sound);
            $body = "Testing notification sound: {$sound}";
            $res = sendPushNotification($title, $body, '/topics/all', [
                'type' => 'SOUND_TEST',
                'test_sound' => $sound
            ], 'appointment');
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

        // === 📬 NEW: Notification Inbox Endpoints ===
        
        case 'get_inbox':
            // Get notification inbox with pagination & type filter
            $page = max(1, (int)($_GET['page'] ?? 1));
            $limit = min(50, max(10, (int)($_GET['limit'] ?? 30)));
            $offset = ($page - 1) * $limit;
            $typeFilter = $_GET['type'] ?? '';
            
            try {
                $where = '';
                $params = [];
                if (!empty($typeFilter) && $typeFilter !== 'all') {
                    $where = "WHERE type = ?";
                    $params[] = $typeFilter;
                }
                
                // Get total count
                $countStmt = $conn->prepare("SELECT COUNT(*) FROM notification_logs $where");
                $countStmt->execute($params);
                $total = (int)$countStmt->fetchColumn();
                
                // Get records
                $sql = "SELECT * FROM notification_logs $where ORDER BY created_at DESC LIMIT ? OFFSET ?";
                $allParams = array_merge($params, [$limit, $offset]);
                $stmt = $conn->prepare($sql);
                $stmt->execute($allParams);
                $notifications = $stmt->fetchAll(PDO::FETCH_ASSOC);
                
                // Decode JSON data for each notification
                foreach ($notifications as &$notif) {
                    if (!empty($notif['notification_data'])) {
                        $notif['data'] = json_decode($notif['notification_data'], true);
                    } else {
                        $notif['data'] = null;
                    }
                    unset($notif['notification_data']);
                }
                
                echo json_encode([
                    'success' => true,
                    'notifications' => $notifications,
                    'total' => $total,
                    'page' => $page,
                    'limit' => $limit,
                    'total_pages' => ceil($total / $limit)
                ]);
            } catch (Exception $e) {
                echo json_encode(['success' => false, 'message' => $e->getMessage()]);
            }
            break;
            
        case 'get_unread_count':
            // Get unread notification count
            try {
                $typeFilter = $_GET['type'] ?? '';
                $where = 'WHERE is_read = 0';
                $params = [];
                if (!empty($typeFilter) && $typeFilter !== 'all') {
                    $where .= " AND type = ?";
                    $params[] = $typeFilter;
                }
                $stmt = $conn->prepare("SELECT COUNT(*) FROM notification_logs $where");
                $stmt->execute($params);
                $count = (int)$stmt->fetchColumn();
                echo json_encode(['success' => true, 'unread_count' => $count]);
            } catch (Exception $e) {
                echo json_encode(['success' => false, 'unread_count' => 0, 'message' => $e->getMessage()]);
            }
            break;
            
        case 'mark_read':
            // Mark a single notification as read
            $id = (int)($_GET['id'] ?? $_POST['id'] ?? 0);
            if (!$id) {
                echo json_encode(['success' => false, 'message' => 'Notification ID required']);
                exit;
            }
            try {
                $stmt = $conn->prepare("UPDATE notification_logs SET is_read = 1 WHERE id = ?");
                $stmt->execute([$id]);
                echo json_encode(['success' => true, 'message' => 'Marked as read']);
            } catch (Exception $e) {
                echo json_encode(['success' => false, 'message' => $e->getMessage()]);
            }
            break;
            
        case 'mark_all_read':
            // Mark all notifications as read
            try {
                $typeFilter = $_GET['type'] ?? '';
                $sql = "UPDATE notification_logs SET is_read = 1 WHERE is_read = 0";
                $params = [];
                if (!empty($typeFilter) && $typeFilter !== 'all') {
                    $sql .= " AND type = ?";
                    $params[] = $typeFilter;
                }
                $stmt = $conn->prepare($sql);
                $stmt->execute($params);
                echo json_encode(['success' => true, 'updated' => $stmt->rowCount()]);
            } catch (Exception $e) {
                echo json_encode(['success' => false, 'message' => $e->getMessage()]);
            }
            break;
            
        case 'delete_notification':
            // Delete a specific notification
            $id = (int)($_GET['id'] ?? $_POST['id'] ?? 0);
            if (!$id) {
                echo json_encode(['success' => false, 'message' => 'Notification ID required']);
                exit;
            }
            try {
                $stmt = $conn->prepare("DELETE FROM notification_logs WHERE id = ?");
                $stmt->execute([$id]);
                echo json_encode(['success' => true, 'message' => 'Notification deleted']);
            } catch (Exception $e) {
                echo json_encode(['success' => false, 'message' => $e->getMessage()]);
            }
            break;
            
        case 'clear_all':
            // Clear all notifications (or by type)
            try {
                $typeFilter = $_GET['type'] ?? '';
                $sql = "DELETE FROM notification_logs";
                $params = [];
                if (!empty($typeFilter) && $typeFilter !== 'all') {
                    $sql .= " WHERE type = ?";
                    $params[] = $typeFilter;
                }
                $stmt = $conn->prepare($sql);
                $stmt->execute($params);
                echo json_encode(['success' => true, 'deleted' => $stmt->rowCount()]);
            } catch (Exception $e) {
                echo json_encode(['success' => false, 'message' => $e->getMessage()]);
            }
            break;

        case 'get_types':
            // Get distinct notification types for filtering
            try {
                $stmt = $conn->query("SELECT DISTINCT type FROM notification_logs WHERE type IS NOT NULL AND type != '' ORDER BY type ASC");
                $types = $stmt->fetchAll(PDO::FETCH_COLUMN);
                echo json_encode(['success' => true, 'types' => $types]);
            } catch (Exception $e) {
                echo json_encode(['success' => false, 'types' => []]);
            }
            break;
    }
}
?>
