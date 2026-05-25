<?php
include 'db_connect.php';

// Ensure settings table exists
$conn->exec("CREATE TABLE IF NOT EXISTS system_settings (
    key_name VARCHAR(100) PRIMARY KEY,
    key_value TEXT
)");

// Initialize default values if not exists
$defaults = [
    'appt_reminder_time' => '10', // minutes before
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

// Handle Save
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    foreach ($_POST as $k => $v) {
        $stmt = $conn->prepare("UPDATE system_settings SET key_value = ? WHERE key_name = ?");
        $stmt->execute([$v, $k]);
    }
    $msg = "Settings saved successfully!";
}

// Load Settings
$settings = [];
$res = $conn->query("SELECT * FROM system_settings")->fetchAll(PDO::FETCH_ASSOC);
foreach ($res as $row) {
    $settings[$row['key_name']] = $row['key_value'];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <script src="theme_engine.js"></script>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Notification Settings | SK LOGIC</title>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root { --primary: #8b5cf6; --bg: #030712; --surface: rgba(15, 23, 42, 0.6); --border: rgba(255, 255, 255, 0.08); }
        body { font-family: 'Plus Jakarta Sans', sans-serif; background: var(--bg); color: white; padding: 20px; }
        .container { max-width: 600px; margin: 0 auto; }
        .card { background: var(--surface); border: 1px solid var(--border); border-radius: 20px; padding: 30px; backdrop-filter: blur(20px); }
        h1 { font-size: 24px; font-weight: 800; margin-bottom: 25px; display: flex; align-items: center; gap: 12px; }
        .form-group { margin-bottom: 20px; }
        label { display: block; font-size: 12px; font-weight: 700; color: #94a3b8; text-transform: uppercase; margin-bottom: 8px; }
        .input { width: 100%; padding: 12px 15px; background: rgba(0,0,0,0.2); border: 1px solid var(--border); border-radius: 10px; color: white; font-family: inherit; }
        .input:focus { border-color: var(--primary); outline: none; }
        .btn { background: var(--primary); color: white; border: none; padding: 15px 30px; border-radius: 12px; font-weight: 700; cursor: pointer; width: 100%; margin-top: 10px; }
        .alert { padding: 15px; border-radius: 10px; background: rgba(16, 185, 129, 0.1); color: #10b981; border: 1px solid rgba(16, 185, 129, 0.2); margin-bottom: 20px; text-align: center; }
        .back-link { display: block; text-align: center; margin-top: 20px; color: #94a3b8; text-decoration: none; font-size: 14px; }
    </style>
</head>
<body>

<div class="container">
    <div class="card">
        <h1><i class="fa-solid fa-bell-concierge" style="color:var(--primary)"></i> Alert Settings</h1>
        
        <?php if(isset($msg)): ?>
            <div class="alert"><?php echo $msg; ?></div>
        <?php endif; ?>

        <form method="POST">
            <div class="form-group">
                <label>Appointment Reminder Time</label>
                <select name="appt_reminder_time" class="input">
                    <option value="0" <?php echo ($settings['appt_reminder_time'] == '0') ? 'selected' : ''; ?>>Exactly at Appointment Time</option>
                    <option value="5" <?php echo ($settings['appt_reminder_time'] == '5') ? 'selected' : ''; ?>>5 Minutes Before</option>
                    <option value="10" <?php echo ($settings['appt_reminder_time'] == '10') ? 'selected' : ''; ?>>10 Minutes Before</option>
                    <option value="30" <?php echo ($settings['appt_reminder_time'] == '30') ? 'selected' : ''; ?>>30 Minutes Before</option>
                    <option value="60" <?php echo ($settings['appt_reminder_time'] == '60') ? 'selected' : ''; ?>>1 Hour Before</option>
                </select>
            </div>

            <div class="form-group">
                <label>Daily Payment Summary Time</label>
                <input type="time" name="payment_alert_time" class="input" value="<?php echo $settings['payment_alert_time']; ?>">
            </div>

            <div class="form-group">
                <label>Enable Payment Alerts</label>
                <select name="enable_payment_alerts" class="input">
                    <option value="1" <?php echo ($settings['enable_payment_alerts'] == '1') ? 'selected' : ''; ?>>Enabled</option>
                    <option value="0" <?php echo ($settings['enable_payment_alerts'] == '0') ? 'selected' : ''; ?>>Disabled</option>
                </select>
            </div>

            <div class="form-group">
                <label>Full Screen UI (Android)</label>
                <select name="full_screen_notifications" class="input">
                    <option value="1" <?php echo ($settings['full_screen_notifications'] == '1') ? 'selected' : ''; ?>>Show Full Screen Alert</option>
                    <option value="0" <?php echo ($settings['full_screen_notifications'] == '0') ? 'selected' : ''; ?>>Standard Notification</option>
                </select>
            </div>

            <div class="form-group">
                <label>Enable Renewal Alerts</label>
                <select name="enable_renewal_alerts" class="input">
                    <option value="1" <?php echo ($settings['enable_renewal_alerts'] == '1') ? 'selected' : ''; ?>>Enabled</option>
                    <option value="0" <?php echo ($settings['enable_renewal_alerts'] == '0') ? 'selected' : ''; ?>>Disabled</option>
                </select>
            </div>

            <div class="form-group">
                <label>Renewal Reminder Days (Before Expiry)</label>
                <input type="number" name="renewal_alert_days" class="input" value="<?php echo $settings['renewal_alert_days']; ?>" placeholder="e.g. 7">
            </div>
            
            <div class="form-group">
                <label>Grace Period Days (After Expiry)</label>
                <input type="number" name="grace_period_days" class="input" value="<?php echo $settings['grace_period_days'] ?? '5'; ?>" placeholder="e.g. 5">
            </div>

            <div class="form-group">
                <label>FCM Server Key (Firebase)</label>
                <input type="text" name="fcm_server_key" class="input" placeholder="Paste your Firebase Key here" value="<?php echo $settings['fcm_server_key']; ?>">
            </div>

            <button type="submit" class="btn">Save Configuration</button>
        </form>

        <a href="index.html" class="back-link"><i class="fa-solid fa-arrow-left"></i> Back to Dashboard</a>
    </div>
</div>

</body>
</html>
