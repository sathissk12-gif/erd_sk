<?php
/**
 * 🤖 DYNAMIC AUTOMATION TRIGGER ENGINE (v2)
 * Smart Appointment Reminders with Multi-Level Sequence
 * Instant + Full-Screen + Voice + Sequential Reminders
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
    // ─── SMART APPOINTMENT CHECK (Multi-Level) ───
    case 'check_appointments':
        $leadMinutes = (int)($allSettings['appt_reminder_time'] ?? 10);
        $fullScreen = ($allSettings['full_screen_notifications'] ?? '1') === '1';
        $today = date('Y-m-d');
        $now = date('H:i:s');
        
        // 🎯 Level 0: Overdue appointments (not acknowledged)
        $q0 = "SELECT *, 'overdue' as alert_level FROM appointment_log 
               WHERE appointment_date < '$today' 
               AND status = 'Pending' 
               AND acknowledged_at IS NULL
               AND reminder_level < 3";
        
        // 🎯 Level 1: Appointment NOW (time has passed)
        $q1 = "SELECT *, 'now' as alert_level FROM appointment_log 
               WHERE appointment_date = '$today' 
               AND appointment_time <= '$now'
               AND status = 'Pending'
               AND acknowledged_at IS NULL
               AND reminder_level < 2";
        
        // 🎯 Level 2: Upcoming within reminder window (e.g., 10 min before)
        $targetTime = date('H:i:s', strtotime("+$leadMinutes minutes"));
        $q2 = "SELECT *, 'upcoming' as alert_level FROM appointment_log 
               WHERE appointment_date = '$today' 
               AND appointment_time BETWEEN '$now' AND '$targetTime'
               AND status = 'Pending'
               AND reminder_sent = 0";
        
        // 🎯 Level 3: Future appointments (send silent notification)
        $q3 = "SELECT *, 'future' as alert_level FROM appointment_log 
               WHERE appointment_date > '$today' 
               AND status = 'Pending'
               AND reminder_sent = 0
               AND DATEDIFF(appointment_date, '$today') <= 1";
        
        $count = ['overdue' => 0, 'now' => 0, 'upcoming' => 0, 'future' => 0];
        
        // Process Overdue (Level 3 - highest urgency)
        $stmt = $conn->query($q0);
        while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $title = "🚨 OVERDUE APPOINTMENT!";
            $msg = $row['customer_name'] . " was scheduled for " . date('h:i A', strtotime($row['appointment_time'])) . ". Not attended yet!";
            
            $extraData = [
                'full_screen' => 'true',
                'sound' => 'true',
                'type' => 'APPOINTMENT_OVERDUE',
                'appointment_id' => $row['id'],
                'customer_name' => $row['customer_name'],
                'vehicle_no' => $row['vehicle_no'] ?? '',
                'mobile' => $row['mobile_number'] ?? '',
                'purpose' => $row['purpose'] ?? ''
            ];
            
            sendPushNotification($title, $msg, '/topics/all', $extraData);
            $conn->prepare("UPDATE appointment_log SET reminder_level = 3 WHERE id = ?")->execute([$row['id']]);
            $count['overdue']++;
        }
        
        // Process NOW appointments (Level 2)
        $stmt = $conn->query($q1);
        while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $title = "🔴 APPOINTMENT NOW!";
            $msg = $row['customer_name'] . " - " . ($row['vehicle_no'] ?: 'No vehicle') . " - " . ($row['purpose'] ?: 'General');
            
            $extraData = [
                'full_screen' => 'true',
                'sound' => 'true',
                'type' => 'APPOINTMENT_NOW',
                'appointment_id' => $row['id'],
                'customer_name' => $row['customer_name'],
                'vehicle_no' => $row['vehicle_no'] ?? '',
                'mobile' => $row['mobile_number'] ?? '',
                'purpose' => $row['purpose'] ?? ''
            ];
            
            sendPushNotification($title, $msg, '/topics/all', $extraData);
            $conn->prepare("UPDATE appointment_log SET reminder_level = 2, reminder_sent = 1 WHERE id = ?")->execute([$row['id']]);
            $count['now']++;
        }
        
        // Process Upcoming reminders (Level 1)
        $stmt = $conn->query($q2);
        while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $title = "📅 UPCOMING APPOINTMENT";
            $msg = $row['customer_name'] . " at " . date('h:i A', strtotime($row['appointment_time'])) . " (" . $leadMinutes . " min notice)";
            
            $extraData = [
                'full_screen' => $fullScreen ? 'true' : 'false',
                'sound' => 'true',
                'type' => 'APPOINTMENT_REMINDER',
                'appointment_id' => $row['id'],
                'customer_name' => $row['customer_name'],
                'vehicle_no' => $row['vehicle_no'] ?? ''
            ];
            
            sendPushNotification($title, $msg, '/topics/all', $extraData);
            $conn->prepare("UPDATE appointment_log SET reminder_sent = 1, reminder_level = 1 WHERE id = ?")->execute([$row['id']]);
            $count['upcoming']++;
        }
        
        // Process Future Day-before reminders (Level 0)
        $stmt = $conn->query($q3);
        while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $title = "📌 TOMORROW'S APPOINTMENT";
            $tomorrow = date('d M', strtotime($row['appointment_date']));
            $msg = $row['customer_name'] . " on " . $tomorrow . " at " . date('h:i A', strtotime($row['appointment_time']));
            
            $extraData = [
                'full_screen' => 'false',
                'sound' => 'false',
                'type' => 'APPOINTMENT_REMINDER',
                'appointment_id' => $row['id']
            ];
            
            sendPushNotification($title, $msg, '/topics/all', $extraData);
            $conn->prepare("UPDATE appointment_log SET reminder_sent = 1, reminder_level = 0 WHERE id = ?")->execute([$row['id']]);
            $count['future']++;
        }
        
        echo json_encode([
            'success' => true, 
            'alerts_sent' => $count,
            'total' => array_sum($count),
            'target_time' => $targetTime,
            'lead_minutes' => $leadMinutes,
            'full_screen' => $fullScreen
        ]);
        break;

    // ─── EXISTING: check_payments ───
    case 'check_payments':
        if (($allSettings['enable_payment_alerts'] ?? '0') == '0') {
            echo json_encode(['success' => false, 'message' => 'Payment alerts disabled']);
            exit;
        }

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

    // ─── EXISTING: check_renewals ───
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
