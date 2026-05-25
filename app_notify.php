<?php
/**
 * 🤖 DYNAMIC AUTOMATION TRIGGER ENGINE
 */
error_reporting(E_ALL);
ini_set('display_errors', 1);
require_once 'api_fcm.php'; 

// Load Global Settings
$allSettings = [];
$res = $conn->query("SELECT * FROM system_settings")->fetchAll(PDO::FETCH_ASSOC);
foreach ($res as $row) { $allSettings[$row['key_name']] = $row['key_value']; }

$notifyAction = $_GET['action'] ?? '';

switch ($notifyAction) {
    case 'check_appointments':
        // Get reminder lead time from settings (e.g., 0, 5, 10, 30, 60 min)
        $leadMinutes = (int)($allSettings['appt_reminder_time'] ?? 0);
        $targetTime = date('H:i', strtotime("+$leadMinutes minutes"));
        $today = date('Y-m-d');
        
        $q = "SELECT * FROM appointment_log 
              WHERE appointment_date = '$today' 
              AND DATE_FORMAT(appointment_time, '%H:%i') = '$targetTime'
              AND reminder_sent = 0 
              AND status = 'Pending'";
              
        $stmt = $conn->query($q);
        $count = 0;
        while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $title = ($leadMinutes == 0) ? "🚨 APPOINTMENT NOW!" : "📅 UPCOMING APPOINTMENT";
            $msg = $row['customer_name'] . " scheduled for " . date('h:i A', strtotime($row['appointment_time']));
            
            $extraData = [
                'full_screen' => ($allSettings['full_screen_notifications'] == '1') ? 'true' : 'false',
                'type' => 'APPOINTMENT_REMINDER'
            ];
            
            sendPushNotification($title, $msg, '/topics/all', $extraData);
            
            $conn->prepare("UPDATE appointment_log SET reminder_sent = 1 WHERE id = ?")->execute([$row['id']]);
            $count++;
        }
        echo json_encode(['success' => true, 'alerts_sent' => $count, 'target_time' => $targetTime]);
        break;

    case 'check_payments':
        if (($allSettings['enable_payment_alerts'] ?? '0') == '0') {
            echo json_encode(['success' => false, 'message' => 'Payment alerts disabled']);
            exit;
        }

        // Check if current time matches the scheduled alert time
        $alertTime = $allSettings['payment_alert_time'] ?? '09:00';
        if (date('H:i') !== $alertTime) {
            echo json_encode(['success' => false, 'message' => 'Not the scheduled time yet']);
            exit;
        }

        $today = date('Y-m-d');
        $q = "SELECT COUNT(*) as count FROM payment_followups WHERE followup_date = '$today' AND status = 'PENDING'";
        $stmt = $conn->query($q);
        $res = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($res['count'] > 0) {
            $title = "💰 PAYMENT COLLECTIONS";
            $msg = "You have " . $res['count'] . " payments to collect today.";
            
            $extraData = [
                'full_screen' => ($allSettings['full_screen_notifications'] == '1') ? 'true' : 'false',
                'type' => 'PAYMENT_REMINDER'
            ];
            
            sendPushNotification($title, $msg, '/topics/all', $extraData);
        }
        echo json_encode(['success' => true, 'payments_due' => $res['count']]);
        break;

    case 'check_renewals':
        try {
            if (($allSettings['enable_renewal_alerts'] ?? '0') == '0') {
                echo json_encode(['success' => false, 'message' => 'Renewal alerts disabled']);
                exit;
            }

            $days = (int)($allSettings['renewal_alert_days'] ?? 7);
            $graceDays = 5; 
            
            $tables = $conn->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
            $tableName = in_array('renewal_log', $tables) ? 'renewal_log' : (in_array('RENEWAL_LOG', $tables) ? 'RENEWAL_LOG' : 'renewal_log');
            
            $qCols = $conn->query("DESCRIBE `$tableName` ");
            $allCols = $qCols->fetchAll(PDO::FETCH_COLUMN);
            $expiryCol = in_array('valid_to', $allCols) ? 'valid_to' : (in_array('expiry_date', $allCols) ? 'expiry_date' : 'date');
            
            // Logic mirrored from api_renewal_automation.php
            $q = "SELECT *, DATEDIFF(`$expiryCol`, CURDATE()) as diff FROM `$tableName` 
                  WHERE UPPER(TRIM(status)) IN ('PENDING', 'NO') 
                  AND `$expiryCol` IS NOT NULL
                  AND DATEDIFF(`$expiryCol`, CURDATE()) BETWEEN -$graceDays AND $days
                  ORDER BY diff ASC";
                  
            $stmt = $conn->query($q);
            $count = 0;
            
            while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $vehicle = $row['vehicle_no'] ?? $row['vehicle'] ?? $row['vehicle_num'] ?? "N/A";
                $customer = $row['customer_name'] ?? $row['customer'] ?? "Customer";
                $software = $row['software'] ?? $row['software_type'] ?? "N/A";
                $amount = $row['amount'] ?? 0;
                $diff = (int)$row['diff'];
                
                // Construct detailed message like automation report
                if ($diff < 0) {
                    $title = "🚨 EXPIRED: $vehicle";
                    $statusTxt = "Expired " . abs($diff) . " days ago";
                } else if ($diff == 0) {
                    $title = "🔥 EXPIRING TODAY: $vehicle";
                    $statusTxt = "Expires Today!";
                } else {
                    $title = "⚠️ RENEWAL DUE: $vehicle";
                    $statusTxt = "Expires in $diff days";
                }
                
                $msg = "👤 $customer\n📦 $software\n💰 ₹$amount\n📅 $statusTxt";
                
                $extraData = [
                    'full_screen' => ($allSettings['full_screen_notifications'] == '1') ? 'true' : 'false',
                    'type' => 'RENEWAL_REMINDER',
                    'vehicle' => $vehicle,
                    'customer' => $customer,
                    'amount' => $amount,
                    'software' => $software
                ];
                
                sendPushNotification($title, $msg, '/topics/all', $extraData);
                $count++;
            }
            echo json_encode(['success' => true, 'renewals_notified' => $count]);
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'error' => $e->getMessage()]);
        }
        exit;
        break;
}
?>
