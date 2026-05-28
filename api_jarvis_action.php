<?php
/**
 * 🧠 SK JARVIS — Action Executor API
 * ====================================
 * Jarvis uses this to perform real business actions.
 * All actions require explicit user confirmation (frontend handles that).
 * 
 * Endpoints:
 *   action=daily_briefing    → Today's business summary
 *   action=send_whatsapp     → Send WhatsApp via api_wa_cloud.php
 *   action=check_renewals    → Count pending/due renewals
 *   action=send_push         → Send FCM push notification
 *   action=open_page         → Return redirect URL for a module
 *   action=remind_renewals   → Send WhatsApp reminders for pending renewals
 *   action=get_stats         → Quick business stats
 */

header('Content-Type: application/json');
include 'db_connect.php';
date_default_timezone_set('Asia/Kolkata');

$action = $_REQUEST['action'] ?? '';

if (!$action) {
    echo json_encode(['success' => false, 'error' => 'No action specified']);
    exit;
}

try {
    switch ($action) {
        // =============================================
        // 📊 DAILY BRIEFING — Morning business summary
        // =============================================
        case 'daily_briefing':
            $today = date('Y-m-d');
            $monthStart = date('Y-m-01');
            $yesterday = date('Y-m-d', strtotime('-1 day'));

            // Today's sales
            $todaySales = $conn->query("SELECT COUNT(*) as c, COALESCE(SUM(received_amount), 0) as s FROM sales_log WHERE sale_date = '$today'")->fetch(PDO::FETCH_ASSOC);
            
            // Yesterday's sales
            $yestSales = $conn->query("SELECT COUNT(*) as c, COALESCE(SUM(received_amount), 0) as s FROM sales_log WHERE sale_date = '$yesterday'")->fetch(PDO::FETCH_ASSOC);
            
            // Month sales
            $monthSales = $conn->query("SELECT COUNT(*) as c, COALESCE(SUM(received_amount), 0) as s, COALESCE(SUM(profit), 0) as p FROM sales_log WHERE sale_date >= '$monthStart'")->fetch(PDO::FETCH_ASSOC);
            
            // Pending renewals
            $pendingRenewals = $conn->query("SELECT COUNT(*) as c, COALESCE(SUM(amount), 0) as a FROM renewal_log WHERE UPPER(COALESCE(status, '')) IN ('PENDING', 'NO') OR UPPER(COALESCE(processed, '')) = 'PENDING'")->fetch(PDO::FETCH_ASSOC);
            
            // Due today
            $dueToday = $conn->query("SELECT COUNT(*) as c FROM renewal_log WHERE valid_to = '$today' AND (UPPER(COALESCE(status, '')) IN ('PENDING', 'NO') OR UPPER(COALESCE(processed, '')) = 'PENDING')")->fetch(PDO::FETCH_ASSOC);
            
            // Due tomorrow
            $dueTomorrow = $conn->query("SELECT COUNT(*) as c FROM renewal_log WHERE valid_to = '" . date('Y-m-d', strtotime('+1 day')) . "' AND (UPPER(COALESCE(status, '')) IN ('PENDING', 'NO') OR UPPER(COALESCE(processed, '')) = 'PENDING')")->fetch(PDO::FETCH_ASSOC);
            
            // Stock count
            $stockCount = (int)$conn->query("SELECT COUNT(*) FROM device_master WHERE status = 'In Stock'")->fetchColumn();
            
            // Top dealer
            $topDealer = $conn->query("SELECT holder, COUNT(*) as c FROM device_master WHERE status = 'SOLD' AND holder IS NOT NULL AND holder != '' AND holder != 'OFFICE' GROUP BY holder ORDER BY c DESC LIMIT 1")->fetch(PDO::FETCH_ASSOC);

            echo json_encode([
                'success' => true,
                'action' => 'daily_briefing',
                'data' => [
                    'date' => $today,
                    'day_name' => date('l'),
                    'today_sales_count' => (int)$todaySales['c'],
                    'today_sales_amount' => (float)$todaySales['s'],
                    'yesterday_sales_count' => (int)$yestSales['c'],
                    'yesterday_sales_amount' => (float)$yestSales['s'],
                    'month_sales_count' => (int)$monthSales['c'],
                    'month_sales_amount' => (float)$monthSales['s'],
                    'month_profit' => (float)$monthSales['p'],
                    'pending_renewals_count' => (int)$pendingRenewals['c'],
                    'pending_renewals_amount' => (float)$pendingRenewals['a'],
                    'due_today' => (int)$dueToday['c'],
                    'due_tomorrow' => (int)$dueTomorrow['c'],
                    'stock_count' => $stockCount,
                    'top_dealer' => $topDealer ? $topDealer['holder'] : 'N/A',
                    'top_dealer_count' => $topDealer ? (int)$topDealer['c'] : 0,
                ]
            ]);
            break;

        // =============================================
        // 📱 SEND WHATSAPP — via api_wa_cloud.php
        // =============================================
        case 'send_whatsapp':
            $to = $_REQUEST['to'] ?? '';
            $message = $_REQUEST['message'] ?? '';
            
            if (!$to || !$message) {
                echo json_encode(['success' => false, 'error' => 'Missing "to" or "message" parameter']);
                break;
            }

            // Normalize mobile number
            $to = preg_replace('/[^0-9]/', '', $to);
            if (strlen($to) === 10) $to = '91' . $to;

            // Call wa_cloud send function
            require_once __DIR__ . '/wa_cloud_config.php';
            
            if (function_exists('waCloudSendMessage')) {
                $result = waCloudSendMessage($to, $message, $conn);
            } else {
                // Inline fallback
                $result = sendWhatsAppDirect($to, $message);
            }

            echo json_encode([
                'success' => !empty($result['success']),
                'action' => 'send_whatsapp',
                'to' => $to,
                'result' => $result
            ]);
            break;

        // =============================================
        // 🔔 CHECK RENEWALS — Pending/Due status
        // =============================================
        case 'check_renewals':
            $days = max(1, min(30, (int)($_REQUEST['days'] ?? 7)));
            $futureDate = date('Y-m-d', strtotime("+$days days"));

            $stmt = $conn->prepare("SELECT id, vehicle_no, customer_name, imei, amount, valid_to, mobile_no, status 
                FROM renewal_log 
                WHERE valid_to BETWEEN CURDATE() AND ? 
                AND (UPPER(COALESCE(status, '')) IN ('PENDING', 'NO') OR UPPER(COALESCE(processed, '')) = 'PENDING')
                ORDER BY valid_to ASC LIMIT 30");
            $stmt->execute([$futureDate]);
            $renewals = $stmt->fetchAll(PDO::FETCH_ASSOC);

            echo json_encode([
                'success' => true,
                'action' => 'check_renewals',
                'count' => count($renewals),
                'days' => $days,
                'data' => $renewals
            ]);
            break;

        // =============================================
        // 🔔 REMIND RENEWALS — Send WhatsApp reminders
        // =============================================
        case 'remind_renewals':
            $days = max(1, min(7, (int)($_REQUEST['days'] ?? 3)));
            $futureDate = date('Y-m-d', strtotime("+$days days"));
            $today = date('Y-m-d');

            $stmt = $conn->prepare("SELECT id, vehicle_no, customer_name, mobile_no, amount, valid_to 
                FROM renewal_log 
                WHERE valid_to BETWEEN ? AND ? 
                AND mobile_no IS NOT NULL AND mobile_no != ''
                AND (UPPER(COALESCE(status, '')) IN ('PENDING', 'NO') OR UPPER(COALESCE(processed, '')) = 'PENDING')
                LIMIT 20");
            $stmt->execute([$today, $futureDate]);
            $renewals = $stmt->fetchAll(PDO::FETCH_ASSOC);

            $sent = 0;
            $failed = 0;
            $errors = [];

            foreach ($renewals as $r) {
                $mobile = preg_replace('/[^0-9]/', '', $r['mobile_no']);
                if (strlen($mobile) === 10) $mobile = '91' . $mobile;
                elseif (strlen($mobile) < 10) { $failed++; continue; }

                $msg = "SK LOGIC Reminder ⏰\n\n";
                $msg .= "Dear {$r['customer_name']},\n";
                $msg .= "Your device {$r['vehicle_no']} renewal amount ₹" . number_format($r['amount']) . " is due on {$r['valid_to']}.\n\n";
                $msg .= "Please renew to continue service.\n";
                $msg .= "Contact: SK ENTERPRISES\n";
                $msg .= "Thank you 🤝";

                try {
                    require_once __DIR__ . '/wa_cloud_config.php';
                    if (function_exists('waCloudSendMessage')) {
                        $res = waCloudSendMessage($mobile, $msg, $conn);
                        if (!empty($res['success'])) $sent++;
                        else $failed++;
                    }
                } catch (Exception $e) {
                    $failed++;
                    $errors[] = $e->getMessage();
                }
            }

            echo json_encode([
                'success' => true,
                'action' => 'remind_renewals',
                'total_found' => count($renewals),
                'sent' => $sent,
                'failed' => $failed,
                'errors' => $errors
            ]);
            break;

        // =============================================
        // 🔔 SEND PUSH NOTIFICATION — via FCM
        // =============================================
        case 'send_push':
            $title = $_REQUEST['title'] ?? 'SK JARVIS';
            $body = $_REQUEST['body'] ?? '';
            $target = $_REQUEST['target'] ?? '/topics/all';

            if (!$body) {
                echo json_encode(['success' => false, 'error' => 'Missing "body" parameter']);
                break;
            }

            require_once __DIR__ . '/api_fcm.php';
            
            // Read notification type from request, default to 'default' (uses global sound setting)
            $notifyType = $_REQUEST['notify_type'] ?? 'default';
            
            if (function_exists('sendPushNotification')) {
                $result = sendPushNotification($title, $body, $target, ['source' => 'jarvis'], $notifyType);
            } else {
                $result = sendFcmDirect($title, $body, $target);
            }

            echo json_encode([
                'success' => !empty($result['success']),
                'action' => 'send_push',
                'result' => $result
            ]);
            break;

        // =============================================
        // 🔗 OPEN PAGE — Redirect to a system module
        // =============================================
        case 'open_page':
            $page = $_REQUEST['page'] ?? '';
            $id = $_REQUEST['id'] ?? '';

            $pages = [
                'dashboard' => 'index.html',
                'sales' => 'sales_invoice.php',
                'renewal' => 'renewal_invoice.php',
                'stock' => 'live_stock.php',
                'dealers' => 'dealer_manager.php',
                'reports' => 'report_center.php',
                'ai_chat' => 'ai_chat.php',
                'inventory' => 'inventory_scanner.php',
                'expense' => 'expense_manager.php',
                'devices' => 'master_device.php',
                'software' => 'master_software.php',
            ];

            $url = $pages[$page] ?? 'index.html';
            if ($id) {
                $url .= (strpos($url, '?') !== false ? '&' : '?') . 'q=' . urlencode($id);
            }

            echo json_encode([
                'success' => true,
                'action' => 'open_page',
                'url' => $url,
                'page' => $page
            ]);
            break;

        // =============================================
        // 📈 QUICK STATS — Simple business numbers
        // =============================================
        case 'get_stats':
            $today = date('Y-m-d');
            $monthStart = date('Y-m-01');

            $todayAmount = (float)$conn->query("SELECT COALESCE(SUM(received_amount), 0) FROM sales_log WHERE sale_date = '$today'")->fetchColumn();
            $monthAmount = (float)$conn->query("SELECT COALESCE(SUM(received_amount), 0) FROM sales_log WHERE sale_date >= '$monthStart'")->fetchColumn();
            $monthProfit = (float)$conn->query("SELECT COALESCE(SUM(profit), 0) FROM sales_log WHERE sale_date >= '$monthStart'")->fetchColumn();
            $pendingCount = (int)$conn->query("SELECT COUNT(*) FROM renewal_log WHERE UPPER(COALESCE(status, '')) IN ('PENDING', 'NO')")->fetchColumn();
            $stockCount = (int)$conn->query("SELECT COUNT(*) FROM device_master WHERE status = 'In Stock'")->fetchColumn();

            echo json_encode([
                'success' => true,
                'action' => 'get_stats',
                'data' => [
                    'today_collection' => $todayAmount,
                    'month_collection' => $monthAmount,
                    'month_profit' => $monthProfit,
                    'pending_renewals' => $pendingCount,
                    'stock_count' => $stockCount,
                ]
            ]);
            break;

        default:
            echo json_encode(['success' => false, 'error' => "Unknown action: $action"]);
    }
} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}

// =============================================
// 📤 Direct WhatsApp send (if wa_cloud_config function not available)
// =============================================
function sendWhatsAppDirect($to, $message) {
    $configFile = __DIR__ . DIRECTORY_SEPARATOR . 'wa_cloud_config.php';
    if (!is_file($configFile)) {
        return ['success' => false, 'error' => 'WhatsApp not configured'];
    }
    
    $config = include $configFile;
    $phoneNumberId = $config['phone_number_id'] ?? '';
    $accessToken = $config['access_token'] ?? '';
    
    if (!$phoneNumberId || !$accessToken) {
        return ['success' => false, 'error' => 'WhatsApp API credentials missing'];
    }

    $url = "https://graph.facebook.com/v18.0/$phoneNumberId/messages";
    $payload = [
        'messaging_product' => 'whatsapp',
        'recipient_type' => 'individual',
        'to' => $to,
        'type' => 'text',
        'text' => ['preview_url' => false, 'body' => $message]
    ];

    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => json_encode($payload),
        CURLOPT_HTTPHEADER => [
            'Authorization: Bearer ' . $accessToken,
            'Content-Type: application/json'
        ],
        CURLOPT_TIMEOUT => 15
    ]);
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    return [
        'success' => $httpCode >= 200 && $httpCode < 300,
        'http_code' => $httpCode,
        'response' => json_decode($response, true)
    ];
}

// =============================================
// 🔔 Direct FCM send (if api_fcm.php function not available)
// =============================================
function sendFcmDirect($title, $body, $target = '/topics/all') {
    $keyRes = $GLOBALS['conn']->query("SELECT key_value FROM system_settings WHERE key_name = 'fcm_server_key'")->fetch();
    $fcmKey = $keyRes ? $keyRes['key_value'] : '';
    
    if (!$fcmKey) {
        return ['success' => false, 'message' => 'FCM key not configured'];
    }

    $payload = [
        'to' => $target,
        'notification' => [
            'title' => $title,
            'body' => $body,
            'sound' => 'default',
            'badge' => '1'
        ],
        'priority' => 'high',
        'data' => [
            'source' => 'jarvis',
            'timestamp' => date('Y-m-d H:i:s')
        ]
    ];

    $ch = curl_init('https://fcm.googleapis.com/fcm/send');
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => json_encode($payload),
        CURLOPT_HTTPHEADER => [
            'Authorization: key=' . $fcmKey,
            'Content-Type: application/json'
        ],
        CURLOPT_TIMEOUT => 10
    ]);
    $response = curl_exec($ch);
    curl_close($ch);

    return ['success' => true, 'response' => json_decode($response, true)];
}
