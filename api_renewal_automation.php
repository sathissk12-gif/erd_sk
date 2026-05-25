<?php
/**
 * RENEWAL AUTOMATION API v2.5
 * Mirrors Google Apps Script renewal reminder rules.
 */
ob_start();
error_reporting(0);
include 'db_connect.php';
ob_clean();
header('Content-Type: application/json');

$tables = $conn->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
$tableName = in_array('renewal_log', $tables) ? 'renewal_log' : (in_array('RENEWAL_LOG', $tables) ? 'RENEWAL_LOG' : 'renewal_log');

function getSoftwareAmount($software) {
    $s = strtoupper(trim($software));
    if (strpos($s, 'TRACK IN') !== false) return 1800;
    if (strpos($s, 'NAVILAP') !== false) return 1700;
    if (strpos($s, 'DO TRACK') !== false) return 1700;
    return 0;
}

function normalizeMobile($mobile) {
    $cleaned = preg_replace('/\D/', '', (string)$mobile);
    if(strlen($cleaned) === 10) return $cleaned;
    if(strlen($cleaned) === 12 && substr($cleaned, 0, 2) === '91') return substr($cleaned, -10);
    return null;
}

function normalizeName($name) {
    return preg_replace('/\s+/', ' ', strtoupper(trim((string)$name)));
}

try {
    $qCols = $conn->query("DESCRIBE `$tableName` ");
    $allCols = $qCols->fetchAll(PDO::FETCH_COLUMN);
    $expiryCol = in_array('valid_to', $allCols) ? 'valid_to' : (in_array('expiry_date', $allCols) ? 'expiry_date' : 'date');
    
    $statusCol = in_array('status', $allCols) ? 'status' : null;
    if (!$statusCol) throw new Exception("Missing status column in $tableName");

    // Fetch settings
    $settings = [];
    $res = $conn->query("SELECT * FROM system_settings")->fetchAll(PDO::FETCH_ASSOC);
    foreach ($res as $row) { $settings[$row['key_name']] = $row['key_value']; }

    $defaultDays = isset($settings['renewal_alert_days']) ? (int)$settings['renewal_alert_days'] : 7;
    $days = isset($_GET['days']) ? (int)$_GET['days'] : $defaultDays;
    if($days < 0) $days = 7;
    if($days > 30) $days = 30;
    
    $expiredGraceDays = isset($settings['grace_period_days']) ? (int)$settings['grace_period_days'] : 5;

    $customerMobiles = [];
    if(in_array('customerdatas', $tables)) {
        $customerCols = $conn->query("DESCRIBE `customerdatas`")->fetchAll(PDO::FETCH_COLUMN);
        if(in_array('name', $customerCols) && in_array('mobile', $customerCols)) {
            $customerRows = $conn->query("SELECT name, mobile FROM `customerdatas` WHERE mobile IS NOT NULL AND TRIM(mobile) <> ''")->fetchAll(PDO::FETCH_ASSOC);
            foreach($customerRows as $customerRow) {
                $mobile = normalizeMobile($customerRow['mobile'] ?? '');
                if(!$mobile) continue;
                $key = normalizeName($customerRow['name'] ?? '');
                if($key && empty($customerMobiles[$key])) $customerMobiles[$key] = $mobile;
            }
        }
    }

    $sql = "SELECT *, DATEDIFF(`$expiryCol`, CURDATE()) as diff FROM `$tableName`
            WHERE UPPER(TRIM(`$statusCol`)) IN ('PENDING', 'NO')
            AND `$expiryCol` IS NOT NULL
            AND DATEDIFF(`$expiryCol`, CURDATE()) BETWEEN -$expiredGraceDays AND $days
            ORDER BY diff ASC";
    
    $stmt = $conn->query($sql);
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Fetch today's sent logs to check which customers already received reminders today
    $sentTodayIds = [];
    try {
        $conn->exec("CREATE TABLE IF NOT EXISTS reminder_sent_logs (
            renewal_id INT NOT NULL,
            sent_date DATE NOT NULL,
            PRIMARY KEY (renewal_id, sent_date)
        )");
        $sentTodayIds = $conn->query("SELECT renewal_id FROM reminder_sent_logs WHERE sent_date = CURDATE()")->fetchAll(PDO::FETCH_COLUMN);
    } catch (Exception $e) {}
    
    $result = [];
    foreach($rows as $row) {
        $r = []; foreach($row as $k=>$v) { $r[strtolower($k)] = $v; }
        
        $diff = (int)$r['diff'];
        $type = "";
        if($diff > 0) $type = "d" . $diff;
        else if($diff === 0) $type = "today";
        else if($diff < 0 && $diff >= -5) $type = "expired";
        else $type = "upcoming";

        $software = $r['software'] ?? $r['software_type'] ?? "";
        $amount = (float)($r['amount'] ?? 0);
        if($amount <= 0) $amount = getSoftwareAmount($software);

        $customerName = $r['customer_name'] ?? $r['customer'] ?? "Customer";
        $vehicle = $r['vehicle_no'] ?? $r['vehicle'] ?? "N/A";
        
        $mobiles = [];
        $rawMobiles = [
            $r['mobile1'] ?? $r['m1'] ?? null, 
            $r['mobile2'] ?? $r['m2'] ?? null, 
            $r['mobile3'] ?? $r['m3'] ?? null,
            $r['mobile_no'] ?? null,
            $r['mobile'] ?? null,
            $customerMobiles[normalizeName($customerName)] ?? null
        ];
        foreach($rawMobiles as $m) {
            $mobile = normalizeMobile($m);
            if($mobile) $mobiles[] = $mobile;
        }
        $mobiles = array_values(array_unique(array_filter($mobiles)));

        $expiry = date('d-m-Y', strtotime($r[$expiryCol]));
        $primaryMobile = $mobiles[0] ?? "";

        $dbId = $r['id'] ?? null;
        $sentToday = in_array($dbId, $sentTodayIds);

        $result[] = [
            'id' => $dbId,
            'customerName' => $customerName,
            'customer' => $customerName,
            'vehicle' => $vehicle,
            'software' => $software,
            'expiry' => $expiry,
            'daysRemaining' => $diff,
            'amount' => $amount,
            'type' => $type,
            'mobiles' => $mobiles,
            'mobile' => $primaryMobile,
            'hasMobile' => count($mobiles) > 0,
            'wa_link' => $primaryMobile ? 'https://wa.me/91' . $primaryMobile : '',
            'sent_today' => $sentToday
        ];
    }
    echo json_encode(['success' => true, 'data' => $result, 'graceTotal' => $expiredGraceDays]);
} catch (Exception $e) { echo json_encode(['success' => false, 'message' => $e->getMessage()]); }
?>
