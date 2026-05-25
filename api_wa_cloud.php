<?php
/**
 * Meta WhatsApp Cloud API - Serverless WhatsApp
 * ==============================================
 * No PC / VPS needed! Works directly from Hostinger.
 * Replaces the local gateway-based api_whatsapp_send.php
 */

header('Content-Type: application/json');
require_once __DIR__ . '/db_connect.php';
require_once __DIR__ . '/wa_cloud_config.php';

$action = $_REQUEST['action'] ?? '';

/**
 * 📤 Send WhatsApp Message via Meta Cloud API
 */
function waCloudSendMessage($to, $message, $conn = null) {
    global $wa_cloud_phone_number_id, $wa_cloud_access_token, $wa_cloud_api_url, $wa_cloud_log_file, $wa_cloud_enable_log, $wa_cloud_fallback_local;

    // If not configured, show error
    if (strpos($wa_cloud_phone_number_id, 'YOUR_') === 0) {
        return ['success' => false, 'error' => 'WhatsApp Cloud API not configured. Update wa_cloud_config.php'];
    }

    $url = str_replace('{phone-number-id}', $wa_cloud_phone_number_id, $wa_cloud_api_url);

    // Format number: remove any + or spaces
    $to = preg_replace('/[^0-9]/', '', $to);
    if (strlen($to) === 10) {
        $to = '91' . $to; // Default India country code
    }

    $payload = [
        'messaging_product' => 'whatsapp',
        'recipient_type' => 'individual',
        'to' => $to,
        'type' => 'text',
        'text' => [
            'preview_url' => false,
            'body' => $message
        ]
    ];

    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => json_encode($payload),
        CURLOPT_HTTPHEADER => [
            'Authorization: Bearer ' . $wa_cloud_access_token,
            'Content-Type: application/json'
        ],
        CURLOPT_TIMEOUT => 20,
        CURLOPT_SSL_VERIFYPEER => true
    ]);

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    curl_close($ch);

    // 📝 Log
    if ($wa_cloud_enable_log) {
        $log = date('Y-m-d H:i:s') . " | TO: $to | HTTP: $httpCode | ";
        if ($error) $log .= "CURL_ERR: $error | ";
        $log .= "RESPONSE: " . substr($response, 0, 500);
        file_put_contents($wa_cloud_log_file, $log . PHP_EOL, FILE_APPEND);
    }

    if ($error) {
        // Try fallback to local gateway
        if ($wa_cloud_fallback_local) {
            return fallbackToLocalGateway($to, $message, $conn);
        }
        return ['success' => false, 'error' => 'CURL Error: ' . $error];
    }

    $decoded = json_decode($response, true);

    if ($httpCode === 200 || $httpCode === 201) {
        $msgId = $decoded['messages'][0]['id'] ?? '';
        return [
            'success' => true,
            'message_id' => $msgId,
            'to' => $to,
            'via' => 'meta_cloud_api'
        ];
    }

    // API Error — try fallback
    $errorMsg = $decoded['error']['message'] ?? ($decoded['error'] ?? 'Unknown API error');
    
    if ($wa_cloud_fallback_local) {
        $fallback = fallbackToLocalGateway($to, $message, $conn);
        if ($fallback['success']) {
            return $fallback;
        }
    }

    return ['success' => false, 'error' => $errorMsg, 'http_code' => $httpCode];
}

/**
 * 🔄 Fallback to Local Gateway (if Meta fails)
 */
function fallbackToLocalGateway($to, $message, $conn) {
    try {
        $gatewayUrl = 'http://localhost:3000';
        
        // Check if running from server (not localhost)
        $isLocal = (php_sapi_name() === 'cli') || 
                    (isset($_SERVER['SERVER_ADDR']) && in_array($_SERVER['SERVER_ADDR'], ['127.0.0.1', '::1']));
        
        if (!$isLocal && $conn) {
            $q = $conn->query("SELECT key_value FROM system_settings WHERE key_name = 'whatsapp_gateway_url'");
            if ($q) {
                $val = $q->fetchColumn();
                if ($val) $gatewayUrl = rtrim($val, '/');
            }
        }

        $ch = curl_init($gatewayUrl . '/send');
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => json_encode(['number' => $to, 'message' => $message]),
            CURLOPT_HTTPHEADER => ['Content-Type: application/json'],
            CURLOPT_TIMEOUT => 10,
            CURLOPT_SSL_VERIFYPEER => false
        ]);
        $res = curl_exec($ch);
        $http = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($http === 200) {
            return ['success' => true, 'message_id' => 'fallback_local', 'to' => $to, 'via' => 'local_fallback'];
        }
    } catch (Exception $e) {}
    
    return ['success' => false, 'error' => 'Fallback also failed'];
}

/**
 * 📋 Check API Health / Account Info
 */
function waCloudCheckHealth() {
    global $wa_cloud_phone_number_id, $wa_cloud_access_token;

    if (strpos($wa_cloud_phone_number_id, 'YOUR_') === 0) {
        return ['status' => 'not_configured', 'message' => 'API not configured'];
    }

    $url = "https://graph.facebook.com/v22.0/$wa_cloud_phone_number_id";
    
    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HTTPHEADER => ['Authorization: Bearer ' . $wa_cloud_access_token],
        CURLOPT_TIMEOUT => 10
    ]);
    $res = curl_exec($ch);
    $http = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($http === 200) {
        return ['status' => 'connected', 'data' => json_decode($res, true)];
    }
    return ['status' => 'error', 'http' => $http, 'response' => substr($res, 0, 300)];
}

// ============ HANDLE REQUESTS ============

switch ($action) {
    case 'send':
        $number = $_POST['number'] ?? $_GET['number'] ?? '';
        $message = $_POST['message'] ?? $_GET['message'] ?? '';

        if (!$number || !$message) {
            echo json_encode(['success' => false, 'error' => 'Missing number or message']);
            break;
        }

        echo json_encode(waCloudSendMessage($number, $message, $conn));
        break;

    case 'health':
        echo json_encode(waCloudCheckHealth());
        break;

    case 'send_bulk':
        $numbers = $_POST['numbers'] ?? $_GET['numbers'] ?? '';
        $message = $_POST['message'] ?? $_GET['message'] ?? '';
        $numbers = json_decode($numbers, true);

        if (!$numbers || !is_array($numbers) || !$message) {
            echo json_encode(['success' => false, 'error' => 'Invalid parameters. numbers[] array and message required']);
            break;
        }

        $results = [];
        foreach ($numbers as $num) {
            $results[] = waCloudSendMessage(trim($num), $message, $conn);
            usleep(200000); // 200ms delay to avoid rate limit
        }

        echo json_encode(['success' => true, 'results' => $results]);
        break;

    case 'save_config':
        // Save config via API (so settings can be updated from UI)
        $phoneId = $_POST['phone_number_id'] ?? '';
        $token = $_POST['access_token'] ?? '';
        $businessPhone = $_POST['business_phone'] ?? '';

        if (!$phoneId || !$token) {
            echo json_encode(['success' => false, 'error' => 'Phone Number ID and Access Token required']);
            break;
        }

        try {
            $configContent = '<?php
/**
 * Meta WhatsApp Cloud API Configuration
 * Auto-generated from wa_cloud_manager.php
 * Last updated: ' . date('Y-m-d H:i:s') . '
 */

$wa_cloud_phone_number_id = "' . addslashes($phoneId) . '";
$wa_cloud_access_token    = "' . addslashes($token) . '";
$wa_cloud_business_phone   = "' . addslashes($businessPhone) . '";
$wa_cloud_api_url = "https://graph.facebook.com/v22.0/{phone-number-id}/messages";
$wa_cloud_log_file = __DIR__ . "/wa_cloud_log.txt";
$wa_cloud_enable_log = true;
$wa_cloud_fallback_local = true;
?>';
            file_put_contents(__DIR__ . '/wa_cloud_config.php', $configContent);
            echo json_encode(['success' => true, 'message' => 'Configuration saved!']);
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'error' => $e->getMessage()]);
        }
        break;

    case 'get_logs':
        try {
            $lines = 50;
            $file = $wa_cloud_log_file;
            if (!file_exists($file)) {
                echo json_encode(['success' => true, 'logs' => []]);
                break;
            }
            $content = file($file);
            $content = array_slice($content, -$lines);
            echo json_encode(['success' => true, 'logs' => $content]);
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'error' => $e->getMessage()]);
        }
        break;

    case 'clear_logs':
        file_put_contents($wa_cloud_log_file, '');
        echo json_encode(['success' => true, 'message' => 'Logs cleared']);
        break;

    default:
        echo json_encode(['success' => false, 'error' => 'Unknown action. Try: send, health, send_bulk, save_config, get_logs']);
}
?>
