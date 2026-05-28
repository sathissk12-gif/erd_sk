<?php
// api_appointments.php — 🚀 SMART APPOINTMENT ENGINE
// Instant + Full-Screen + Multi-Stage Reminders
require_once 'db_connect.php';
require_once 'api_fcm.php';
header('Content-Type: application/json');

$action = $_REQUEST['action'] ?? '';

// ──────────────────────────────────────────────
// 🛡️ AUTO-HEALING: Smart Schema
// ──────────────────────────────────────────────
$conn->exec("CREATE TABLE IF NOT EXISTS appointment_log (
    id INT AUTO_INCREMENT PRIMARY KEY,
    customer_name VARCHAR(255) NOT NULL,
    mobile_number VARCHAR(20),
    vehicle_no VARCHAR(50),
    appointment_date DATE NOT NULL,
    appointment_time TIME NOT NULL,
    purpose TEXT,
    notes TEXT,
    status ENUM('Pending','Completed','Cancelled','Missed') DEFAULT 'Pending',
    reminder_minutes INT DEFAULT 10,
    notify_methods VARCHAR(100) DEFAULT 'push',
    reminder_sent TINYINT(1) DEFAULT 0,
    reminder_level INT DEFAULT 0,
    acknowledged_at DATETIME DEFAULT NULL,
    sms_sent TINYINT(1) DEFAULT 0,
    whatsapp_sent TINYINT(1) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)");

// Ensure new columns exist (safe migration)
try {
    $conn->exec("ALTER TABLE appointment_log ADD COLUMN notes TEXT AFTER purpose");
} catch (Exception $e) {}
try {
    $conn->exec("ALTER TABLE appointment_log ADD COLUMN reminder_minutes INT DEFAULT 10 AFTER status");
} catch (Exception $e) {}
try {
    $conn->exec("ALTER TABLE appointment_log ADD COLUMN notify_methods VARCHAR(100) DEFAULT 'push' AFTER reminder_minutes");
} catch (Exception $e) {}
try {
    $conn->exec("ALTER TABLE appointment_log ADD COLUMN reminder_level INT DEFAULT 0 AFTER reminder_sent");
} catch (Exception $e) {}
try {
    $conn->exec("ALTER TABLE appointment_log ADD COLUMN acknowledged_at DATETIME DEFAULT NULL AFTER reminder_level");
} catch (Exception $e) {}
try {
    $conn->exec("ALTER TABLE appointment_log ADD COLUMN sms_sent TINYINT(1) DEFAULT 0 AFTER acknowledged_at");
} catch (Exception $e) {}
try {
    $conn->exec("ALTER TABLE appointment_log ADD COLUMN whatsapp_sent TINYINT(1) DEFAULT 0 AFTER sms_sent");
} catch (Exception $e) {}

// Ensure system_settings defaults
$conn->exec("CREATE TABLE IF NOT EXISTS system_settings (
    key_name VARCHAR(100) PRIMARY KEY,
    key_value TEXT
)");
$defaults = [
    'appt_reminder_time' => '10',
    'full_screen_notifications' => '1',
    'appt_sound_enabled' => '1',
    'appt_voice_announce' => '1',
    'appt_instant_check_interval' => '10',
    'enable_appt_whatsapp' => '1',
    'enable_appt_sms' => '0'
];
foreach ($defaults as $k => $v) {
    $conn->prepare("INSERT IGNORE INTO system_settings (key_name, key_value) VALUES (?, ?)")->execute([$k, $v]);
}

// ──────────────────────────────────────────────
// 🧠 SMART ROUTER
// ──────────────────────────────────────────────
switch ($action) {

    // ─── SAVE / CREATE (Enhanced) ───
    case 'save':
        try {
            $name = $_POST['customer_name'] ?? '';
            $mobile = $_POST['mobile_number'] ?? '';
            $vehicle = $_POST['vehicle_no'] ?? '';
            $date = $_POST['appointment_date'] ?? '';
            $time = $_POST['appointment_time'] ?? '';
            $purpose = $_POST['purpose'] ?? '';
            $notes = $_POST['notes'] ?? '';
            $reminderMin = (int)($_POST['reminder_minutes'] ?? 10);
            $methods = $_POST['notify_methods'] ?? 'push';

            if (!$name || !$date || !$time) {
                echo json_encode(['success' => false, 'message' => 'Missing required fields']);
                exit;
            }

            $stmt = $conn->prepare("INSERT INTO appointment_log 
                (customer_name, mobile_number, vehicle_no, appointment_date, appointment_time, purpose, notes, reminder_minutes, notify_methods) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([$name, $mobile, $vehicle, $date, $time, $purpose, $notes, $reminderMin, $methods]);
            $newId = $conn->lastInsertId();

            // 🔥 Send instant push to connected devices
            $msg = "📅 New appointment: $name at " . date('h:i A', strtotime($time));
            sendPushNotification("📌 Appointment Scheduled", $msg, '/topics/all', [
                'type' => 'APPOINTMENT_CREATED',
                'appointment_id' => $newId,
                'full_screen' => 'false'
            ], 'appointment');

            echo json_encode(['success' => true, 'message' => 'Appointment saved', 'id' => $newId]);
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
        break;

    // ─── LIST (Smart filters) ───
    case 'list':
        try {
            $filter = $_GET['filter'] ?? 'upcoming';
            $date = $_GET['date'] ?? date('Y-m-d');
            $limit = min((int)($_GET['limit'] ?? 100), 500);

            $where = "appointment_date >= CURDATE()";
            $order = "appointment_date ASC, appointment_time ASC";

            switch ($filter) {
                case 'today':
                    $where = "appointment_date = '$date'";
                    $order = "appointment_time ASC";
                    break;
                case 'pending':
                    $where = "appointment_date >= CURDATE() AND status = 'Pending'";
                    break;
                case 'overdue':
                    $where = "(appointment_date < CURDATE() OR (appointment_date = CURDATE() AND appointment_time < CURTIME())) AND status = 'Pending'";
                    $order = "appointment_date ASC, appointment_time ASC";
                    break;
                case 'completed':
                    $where = "status = 'Completed'";
                    $order = "appointment_date DESC";
                    break;
                case 'date_range':
                    $from = $_GET['from'] ?? date('Y-m-d');
                    $to = $_GET['to'] ?? date('Y-m-d');
                    $where = "appointment_date BETWEEN '$from' AND '$to'";
                    break;
                default: // upcoming
                    $where = "appointment_date >= CURDATE()";
            }

            $stmt = $conn->query("SELECT *, 
                TIMESTAMPDIFF(MINUTE, CONCAT(appointment_date, ' ', appointment_time), NOW()) as minutes_past,
                CASE 
                    WHEN appointment_date = CURDATE() AND TIMESTAMPDIFF(MINUTE, CONCAT(appointment_date, ' ', appointment_time), NOW()) BETWEEN 0 AND 5 THEN 'now'
                    WHEN appointment_date = CURDATE() AND TIMESTAMPDIFF(MINUTE, CONCAT(appointment_date, ' ', appointment_time), NOW()) BETWEEN -30 AND 0 THEN 'soon'
                    WHEN appointment_date = CURDATE() THEN 'today'
                    ELSE 'upcoming'
                END as urgency
                FROM appointment_log WHERE $where ORDER BY $order LIMIT $limit");
            $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
            echo json_encode($data);
        } catch (Exception $e) {
            echo json_encode([]);
        }
        break;

    // ─── UPDATE STATUS ───
    case 'update_status':
        try {
            $id = $_POST['id'] ?? '';
            $status = $_POST['status'] ?? 'Completed';
            $stmt = $conn->prepare("UPDATE appointment_log SET status = ? WHERE id = ?");
            $stmt->execute([$status, $id]);
            echo json_encode(['success' => true]);
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
        break;

    // ─── ACKNOWLEDGE (User saw the notification) ───
    case 'acknowledge':
        try {
            $id = $_POST['id'] ?? 0;
            $stmt = $conn->prepare("UPDATE appointment_log SET acknowledged_at = NOW() WHERE id = ?");
            $stmt->execute([$id]);
            
            // Also send to FCM so Android app can dismiss full-screen
            $appt = $conn->query("SELECT * FROM appointment_log WHERE id = $id")->fetch(PDO::FETCH_ASSOC);
            if ($appt) {
                sendPushNotification("✅ Acknowledged", "{$appt['customer_name']} acknowledged", '/topics/all', [
                    'type' => 'APPOINTMENT_ACKNOWLEDGED',
                    'appointment_id' => $id,
                    'full_screen' => 'false'
                ], 'appointment');
            }
            
            echo json_encode(['success' => true, 'message' => 'Acknowledged']);
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
        break;

    // ─── CHECK INSTANT ALERTS (🔴 Polled by frontend every N seconds) ───
    case 'check_instant':
        try {
            $settings = [];
            $res = $conn->query("SELECT * FROM system_settings")->fetchAll(PDO::FETCH_ASSOC);
            foreach ($res as $row) { $settings[$row['key_name']] = $row['key_value']; }

            $leadMinutes = (int)($settings['appt_reminder_time'] ?? 10);
            $fullScreen = ($settings['full_screen_notifications'] ?? '1') === '1';
            $soundEnabled = ($settings['appt_sound_enabled'] ?? '1') === '1';
            $voiceEnabled = ($settings['appt_voice_announce'] ?? '1') === '1';

            $now = date('Y-m-d H:i:s');
            $targetTime = date('H:i:00', strtotime("+$leadMinutes minutes"));

            // 🔴 Appointment that is NOW or within the reminder window
            $stmt = $conn->query("SELECT *, 
                TIMESTAMPDIFF(SECOND, CONCAT(appointment_date, ' ', appointment_time), '$now') as seconds_diff,
                CASE 
                    WHEN appointment_date < CURDATE() THEN 'overdue'
                    WHEN appointment_date = CURDATE() AND appointment_time <= CURTIME() THEN 'due_now'
                    WHEN appointment_date = CURDATE() AND appointment_time <= '$targetTime' THEN 'upcoming_soon'
                    ELSE 'later'
                END as alert_type
                FROM appointment_log 
                WHERE status = 'Pending'
                AND (
                    (appointment_date = CURDATE() AND appointment_time <= CURTIME() AND acknowledged_at IS NULL)
                    OR
                    (appointment_date = CURDATE() AND appointment_time BETWEEN CURTIME() AND '$targetTime' AND reminder_sent = 0)
                    OR
                    (appointment_date < CURDATE() AND acknowledged_at IS NULL)
                )
                ORDER BY appointment_date ASC, appointment_time ASC
                LIMIT 10");

            $alerts = $stmt->fetchAll(PDO::FETCH_ASSOC);
            $hasInstant = false;
            $nowAlert = null;

            foreach ($alerts as &$a) {
                // Auto-mark reminder_sent if within window
                if ($a['alert_type'] === 'upcoming_soon' && !$a['reminder_sent']) {
                    $conn->prepare("UPDATE appointment_log SET reminder_sent = 1, reminder_level = 1 WHERE id = ?")->execute([$a['id']]);
                    $a['reminder_sent'] = 1;
                    
                    // Send push notification
                    $title = "📅 Upcoming Appointment";
                    $msg = "{$a['customer_name']} at " . date('h:i A', strtotime($a['appointment_time']));
                    sendPushNotification($title, $msg, '/topics/all', [
                        'type' => 'APPOINTMENT_REMINDER',
                        'appointment_id' => $a['id'],
                        'full_screen' => $fullScreen ? 'true' : 'false'
                    ], 'appointment');
                }

                if ($a['alert_type'] === 'due_now' && !$a['acknowledged_at']) {
                    $hasInstant = true;
                    $nowAlert = $a;

                    // Send INSTANT push for due-now appointments
                    if ($a['reminder_level'] < 2) {
                        $conn->prepare("UPDATE appointment_log SET reminder_level = 2 WHERE id = ?")->execute([$a['id']]);
                        
                        $title = "🚨 APPOINTMENT NOW!";
                        $msg = "{$a['customer_name']} - " . ($a['vehicle_no'] ?: 'No vehicle');
                        sendPushNotification($title, $msg, '/topics/all', [
                            'type' => 'APPOINTMENT_NOW',
                            'appointment_id' => $a['id'],
                            'full_screen' => 'true',
                            'customer_name' => $a['customer_name'],
                            'vehicle_no' => $a['vehicle_no'] ?? '',
                            'mobile' => $a['mobile_number'] ?? '',
                            'purpose' => $a['purpose'] ?? ''
                        ], 'appointment');
                    }
                }

                if ($a['alert_type'] === 'overdue' && !$a['acknowledged_at'] && $a['reminder_level'] < 3) {
                    $conn->prepare("UPDATE appointment_log SET reminder_level = 3 WHERE id = ?")->execute([$a['id']]);
                    $hasInstant = true;
                    if (!$nowAlert) $nowAlert = $a;
                }
            }

            echo json_encode([
                'success' => true,
                'has_instant_alert' => $hasInstant,
                'sound_enabled' => $soundEnabled,
                'voice_enabled' => $voiceEnabled,
                'full_screen' => $fullScreen,
                'alert' => $nowAlert,
                'alerts' => $alerts,
                'server_time' => $now,
                'lead_minutes' => $leadMinutes
            ]);
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'error' => $e->getMessage()]);
        }
        break;

    // ─── GET SMART SUGGESTIONS ───
    case 'get_suggestions':
        try {
            $q = $_GET['q'] ?? '';
            if (strlen($q) < 2) {
                echo json_encode([]);
                exit;
            }
            // Search from existing appointments and customer master
            $stmt = $conn->prepare("SELECT DISTINCT customer_name, mobile_number, vehicle_no 
                FROM appointment_log 
                WHERE customer_name LIKE ? OR mobile_number LIKE ? OR vehicle_no LIKE ?
                LIMIT 10");
            $like = "%$q%";
            $stmt->execute([$like, $like, $like]);
            $suggestions = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // Also try from customerdatas table
            try {
                $stmt2 = $conn->prepare("SELECT name, mobile, vehicle_no FROM customerdatas 
                    WHERE name LIKE ? OR mobile LIKE ? OR vehicle_no LIKE ? LIMIT 5");
                $stmt2->execute([$like, $like, $like]);
                $custData = $stmt2->fetchAll(PDO::FETCH_ASSOC);
                foreach ($custData as $c) {
                    $suggestions[] = [
                        'customer_name' => $c['name'],
                        'mobile_number' => $c['mobile'] ?? '',
                        'vehicle_no' => $c['vehicle_no'] ?? ''
                    ];
                }
            } catch (Exception $e) {}

            echo json_encode(array_slice($suggestions, 0, 10));
        } catch (Exception $e) {
            echo json_encode([]);
        }
        break;

    // ─── DELETE ───
    case 'delete':
        try {
            $id = $_POST['id'] ?? 0;
            $stmt = $conn->prepare("DELETE FROM appointment_log WHERE id = ?");
            $stmt->execute([$id]);
            echo json_encode(['success' => true]);
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
        break;

    // ─── STATS ───
    case 'stats':
        try {
            $today = date('Y-m-d');
            $stats = [
                'today_total' => $conn->query("SELECT COUNT(*) FROM appointment_log WHERE appointment_date = '$today'")->fetchColumn(),
                'today_pending' => $conn->query("SELECT COUNT(*) FROM appointment_log WHERE appointment_date = '$today' AND status = 'Pending'")->fetchColumn(),
                'today_completed' => $conn->query("SELECT COUNT(*) FROM appointment_log WHERE appointment_date = '$today' AND status = 'Completed'")->fetchColumn(),
                'overdue' => $conn->query("SELECT COUNT(*) FROM appointment_log WHERE appointment_date < '$today' AND status = 'Pending'")->fetchColumn(),
                'upcoming' => $conn->query("SELECT COUNT(*) FROM appointment_log WHERE appointment_date > '$today' AND status = 'Pending'")->fetchColumn(),
            ];
            echo json_encode(['success' => true, 'stats' => $stats]);
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'error' => $e->getMessage()]);
        }
        break;

    default:
        echo json_encode(['success' => false, 'message' => 'Unknown action']);
}
?>
