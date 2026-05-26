<?php
/**
 * 🤖 FULLY AUTOMATED DAILY RENEWAL REMINDER ENGINE
 * Fetches all due and expired renewals, constructs bilingual (English + Tamil) messages,
 * and broadcasts them automatically using the local WhatsApp Gateway.
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once __DIR__ . '/db_connect.php';
require_once __DIR__ . '/api_whatsapp_send.php';

function normalizeMobileNumber($mobile) {
    $cleaned = preg_replace('/\D/', '', (string)$mobile);
    if (strlen($cleaned) === 10) return $cleaned;
    if (strlen($cleaned) === 12 && substr($cleaned, 0, 2) === '91') return substr($cleaned, -10);
    return null;
}

function normalizeCustomerName($name) {
    return preg_replace('/\s+/', ' ', strtoupper(trim((string)$name)));
}

function resolveSoftwareAmount($software) {
    $software = strtoupper(trim((string)$software));
    if (strpos($software, 'TRACK IN') !== false) return 1800;
    if (strpos($software, 'NAVILAP') !== false) return 1700;
    if (strpos($software, 'DO TRACK') !== false) return 1700;
    return 0;
}

// Helper to format messages (matches renewal_dashboard.php exactly)
function buildBilingualReminder($item, $num, $graceLimit) {
    $vehicle = $item['vehicle'] ?? 'N/A';
    $amount = $item['amount'] ?? 0;
    $expiry = $item['expiry'] ?? '';
    $customerName = $item['customerName'] ?? $item['customer'] ?? 'Customer';
    
    $header = "📌 *SK RENEWAL ALERT* 📌\n\nName: *{$customerName}*\nVehicle: *{$vehicle}*\nMobile: *{$num}*\n\n";

    if ($item['type'] === 'expired') {
        $diff = abs((int)$item['daysRemaining']);
        $graceLeft = max(0, (int)$graceLimit - $diff);
        
        $graceMsgEn = $graceLeft > 0 
            ? "Grace period: {$graceLeft} day" . ($graceLeft > 1 ? 's' : '') . " remaining."
            : "FINAL NOTICE: Today is the last day of your grace period.";
            
        $graceMsgTa = $graceLeft > 0
            ? "{$graceLeft} நாள் grace period உள்ளது."
            : "இறுதி எச்சரிக்கை: இன்று சலுகை காலத்தின் கடைசி நாள்.";

        return $header . "Important Notice:\n\nYour GPS service for vehicle {$vehicle} has expired.\n\nRenewal Amount: ₹{$amount}\n\n{$graceMsgEn}\nIf not renewed, SIM will be disconnected.\nReactivation charge ₹300 extra.\n\nPayment:\nGPay / PhonePe - 9750776198\n\n- SK ENTERPRISES\n\n_Note: This is an automated message._\n\n--------------------------------\n\nமுக்கிய அறிவிப்பு:\n\n{$vehicle} GPS சேவை காலாவதியாகியுள்ளது.\n\nபுதுப்பிப்பு தொகை: ₹{$amount}\n\n{$graceMsgTa}\nஅதற்குப் பிறகு SIM நிறுத்தப்படும்.\nமீண்டும் செயல்படுத்த ₹300 கூடுதல் கட்டணம்.\n\nபணம் செலுத்த:\n9750776198\n\n- SK ENTERPRISES\n\n_குறிப்பு: இது ஒரு தானியங்கி செய்தி._";
    }

    if ($item['type'] === 'today') {
        return $header . "Final Reminder:\n\nYour GPS service for vehicle {$vehicle}\nexpires TODAY ({$expiry}).\n\nRenewal Amount: ₹{$amount}\n\nPlease renew immediately.\n\nPayment:\nGPay / PhonePe - 9750776198\n\n- SK ENTERPRISES\n\n_Note: This is an automated message._\n\n--------------------------------\n\nஇறுதி நினைவூட்டல்:\n\n{$vehicle} GPS சேவை இன்று ({$expiry}) முடிவடைகிறது.\n\nபுதுப்பிப்பு தொகை: ₹{$amount}\n\nஉடனே புதுப்பிக்கவும்.\n\n- SK ENTERPRISES\n\n_குறிப்பு: இது ஒரு தானியங்கி செய்தி._";
    }

    if ($item['type'] === 'd1') {
        return $header . "Urgent Reminder:\n\nGPS service for vehicle {$vehicle}\nexpires tomorrow ({$expiry}).\n\nRenewal Amount: ₹{$amount}\n\nRenew immediately.\n\nPayment:\n9750776198\n\n- SK ENTERPRISES\n\n_Note: This is an automated message._\n\n--------------------------------\n\nஅவசர நினைவூட்டல்:\n\n{$vehicle} GPS சேவை நாளை (${expiry}) முடிவடைகிறது.\n\nபுதுப்பிப்பு தொகை: ₹{$amount}\n\nஉடனே புதுப்பிக்கவும்.\n\n- SK ENTERPRISES\n\n_குறிப்பு: இது ஒரு தானியங்கி செய்தி._";
    }

    // Default upcoming reminder
    return $header . "Reminder:\n\nGPS service for vehicle {$vehicle}\nwill expire on {$expiry}.\n\nRenewal Amount: ₹{$amount}\n\nPlease renew in time.\n\nPayment:\n9750776198\n\n- SK ENTERPRISES\n\n_Note: This is an automated message._\n\n--------------------------------\n\nநினைவூட்டல்:\n\n{$vehicle} GPS சேவை {$expiry} அன்று முடிவடைகிறது.\n\nபுதுப்பிப்பு தொகை: ₹{$amount}\n\nதயவுசெய்து புதுப்பிக்கவும்.\n\n- SK ENTERPRISES\n\n_குறிப்பு: இது ஒரு தானியங்கி செய்தி._";
}

try {
    // 1. Fetch due renewals using internal logic mirroring api_renewal_automation.php
    $tables = $conn->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
    $tableName = in_array('renewal_log', $tables) ? 'renewal_log' : (in_array('RENEWAL_LOG', $tables) ? 'RENEWAL_LOG' : 'renewal_log');
    
    $qCols = $conn->query("DESCRIBE `$tableName` ");
    $allCols = $qCols->fetchAll(PDO::FETCH_COLUMN);
    $expiryCol = in_array('valid_to', $allCols) ? 'valid_to' : (in_array('expiry_date', $allCols) ? 'expiry_date' : 'date');
    $idCol = in_array('id', $allCols) ? 'id' : (in_array('ID', $allCols) ? 'ID' : 'id');
    $statusCol = in_array('status', $allCols) ? 'status' : (in_array('STATUS', $allCols) ? 'STATUS' : null);

    if (!$statusCol) {
        throw new Exception("Missing status column in {$tableName}");
    }

    // Create log tables if not exists
    // 1. Dedup table to prevent duplicate sending on same day
    $conn->exec("CREATE TABLE IF NOT EXISTS reminder_sent_logs (
        renewal_id INT NOT NULL,
        sent_date DATE NOT NULL,
        PRIMARY KEY (renewal_id, sent_date)
    )");
    
    // 2. Detailed send log with status & error info for dashboard display
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
    
    // Fetch settings
    $settings = [];
    if (!in_array('system_settings', $tables)) {
        $conn->exec("CREATE TABLE IF NOT EXISTS system_settings (
            key_name VARCHAR(100) PRIMARY KEY,
            key_value TEXT
        )");
        $defaults = [
            'appt_reminder_time' => '10',
            'enable_payment_alerts' => '1',
            'payment_alert_time' => '09:00',
            'fcm_server_key' => '',
            'full_screen_notifications' => '1',
            'enable_renewal_alerts' => '1',
            'renewal_alert_days' => '7',
            'grace_period_days' => '5'
        ];
        foreach ($defaults as $k => $v) {
            $conn->prepare("INSERT IGNORE INTO system_settings (key_name, key_value) VALUES (?, ?)")->execute([$k, $v]);
        }
    }
    $resSettings = $conn->query("SELECT * FROM system_settings")->fetchAll(PDO::FETCH_ASSOC);
    foreach ($resSettings as $row) { $settings[$row['key_name']] = $row['key_value']; }

    $defaultDays = isset($settings['renewal_alert_days']) ? (int)$settings['renewal_alert_days'] : 7;
    $expiredGraceDays = isset($settings['grace_period_days']) ? (int)$settings['grace_period_days'] : 5;

    // Load customer master mobiles to match by name
    $customerMobiles = [];
    if (in_array('customerdatas', $tables)) {
        $customerCols = $conn->query("DESCRIBE `customerdatas`")->fetchAll(PDO::FETCH_COLUMN);
        if (in_array('name', $customerCols) && in_array('mobile', $customerCols)) {
            $customerRows = $conn->query("SELECT name, mobile FROM `customerdatas` WHERE mobile IS NOT NULL AND TRIM(mobile) <> ''")->fetchAll(PDO::FETCH_ASSOC);
            foreach ($customerRows as $customerRow) {
                $mobile = normalizeMobileNumber($customerRow['mobile'] ?? '');
                if (!$mobile) continue;
                $key = normalizeCustomerName($customerRow['name'] ?? '');
                if ($key && empty($customerMobiles[$key])) $customerMobiles[$key] = $mobile;
            }
        }
    }

    // Query active renewals due in grace period (-graceDays) to alert threshold (+alertDays)
    // Filter out already sent today to prevent accidental double-spamming
    $sql = "SELECT *, DATEDIFF(`$expiryCol`, CURDATE()) as diff FROM `$tableName`
            WHERE UPPER(TRIM(`$statusCol`)) IN ('PENDING', 'NO')
            AND `$expiryCol` IS NOT NULL
            AND `$idCol` NOT IN (SELECT renewal_id FROM reminder_sent_logs WHERE sent_date = CURDATE())
            AND DATEDIFF(`$expiryCol`, CURDATE()) BETWEEN -$expiredGraceDays AND $defaultDays
            ORDER BY diff ASC";
    
    $stmt = $conn->query($sql);
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $successCount = 0;
    $failureCount = 0;
    $reports = [];

    foreach ($rows as $row) {
        $r = []; foreach($row as $k=>$v) { $r[strtolower($k)] = $v; }
        
        $diff = (int)$r['diff'];
        $type = "upcoming";
        if ($diff < 0) $type = "expired";
        else if ($diff === 0) $type = "today";
        else if ($diff === 1) $type = "d1";

        $software = $r['software'] ?? $r['software_type'] ?? "N/A";
        $amount = (float)($r['amount'] ?? 0);
        if ($amount <= 0) {
            $amount = resolveSoftwareAmount($software);
        }

        $customerName = $r['customer_name'] ?? $r['customer'] ?? "Customer";
        $vehicle = $r['vehicle_no'] ?? $r['vehicle'] ?? "N/A";
        
        // Find best mobile number
        $mobiles = [];
        $rawMobiles = [
            $r['mobile1'] ?? $r['m1'] ?? null, 
            $r['mobile2'] ?? $r['m2'] ?? null, 
            $r['mobile3'] ?? $r['m3'] ?? null,
            $r['mobile_no'] ?? null,
            $r['mobile'] ?? null,
            $customerMobiles[normalizeCustomerName($customerName)] ?? null
        ];
        foreach ($rawMobiles as $m) {
            $mobile = normalizeMobileNumber($m);
            if ($mobile) $mobiles[] = $mobile;
        }
        $mobiles = array_values(array_unique(array_filter($mobiles)));
        $primaryMobile = $mobiles[0] ?? "";

        if (!$primaryMobile) {
            $reports[] = [
                'vehicle' => $vehicle,
                'customer' => $customerName,
                'status' => 'skipped',
                'reason' => 'No valid mobile number found'
            ];
            continue;
        }

        // Pack item detail for message formatting
        $itemDetail = [
            'vehicle' => $vehicle,
            'customer' => $customerName,
            'amount' => $amount,
            'expiry' => date('d-m-Y', strtotime($r[$expiryCol])),
            'daysRemaining' => $diff,
            'type' => $type
        ];

        $messageText = buildBilingualReminder($itemDetail, $primaryMobile, $expiredGraceDays);
        
        // Send via WhatsApp
        $result = sendWhatsAppMessage($primaryMobile, $messageText);

        $dbId = $row[$idCol] ?? $row['id'] ?? $row['ID'] ?? 0;

        // Log to detailed send_logs table (both success & failure with error info)
        try {
            $logStmt = $conn->prepare("INSERT INTO whatsapp_send_logs (renewal_id, vehicle, customer, mobile, status, error_message) VALUES (?, ?, ?, ?, ?, ?)");
            $logStmt->execute([
                $dbId,
                $vehicle,
                $customerName,
                $primaryMobile,
                $result['success'] ? 'sent' : 'failed',
                $result['success'] ? null : ($result['error'] ?? 'Unknown error')
            ]);
        } catch (Exception $logErr) {
            // Non-critical: don't stop processing if logging fails
            error_log("whatsapp_send_logs insert failed: " . $logErr->getMessage());
        }

        if ($result['success']) {
            // Log this renewal as sent today to prevent duplicate sending on same day
            if ($dbId > 0) {
                $conn->prepare("INSERT IGNORE INTO reminder_sent_logs (renewal_id, sent_date) VALUES (?, CURDATE())")->execute([$dbId]);
            }

            $successCount++;
            $reports[] = [
                'vehicle' => $vehicle,
                'customer' => $customerName,
                'mobile' => $primaryMobile,
                'status' => 'sent'
            ];
        } else {
            $failureCount++;
            $reports[] = [
                'vehicle' => $vehicle,
                'customer' => $customerName,
                'mobile' => $primaryMobile,
                'status' => 'failed',
                'reason' => $result['error'] ?? 'Unknown error'
            ];
        }
    }

    echo json_encode([
        'success' => true,
        'summary' => [
            'total_due_records' => count($rows),
            'sent_successfully' => $successCount,
            'failed' => $failureCount,
            'skipped_no_mobile' => count($rows) - ($successCount + $failureCount)
        ],
        'details' => $reports
    ], JSON_PRETTY_PRINT);

} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ], JSON_PRETTY_PRINT);
}
?>
