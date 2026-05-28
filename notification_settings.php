<?php
include 'db_connect.php';

// Ensure settings table exists
$conn->exec("CREATE TABLE IF NOT EXISTS system_settings (
    key_name VARCHAR(100) PRIMARY KEY,
    key_value TEXT
)");

// Initialize default values if not exists
$defaults = [
    'appt_reminder_time' => '10',
    'enable_payment_alerts' => '1',
    'payment_alert_time' => '09:00',
    'fcm_server_key' => '',
    'full_screen_notifications' => '1',
    'enable_renewal_alerts' => '1',
    'renewal_alert_days' => '7',
    'grace_period_days' => '5',
    'appt_sound_enabled' => '1',
    'appt_voice_announce' => '1',
    'appt_instant_check_interval' => '10',
    'enable_appt_whatsapp' => '1',
    'enable_appt_sms' => '0',
    'appt_multi_reminder' => '1',
    'appt_reminder_24h' => '1',
    'appt_reminder_1h' => '1',
    'appt_reminder_10min' => '1',
    // 🎵 Sound Management Settings
    'notification_sound' => 'default',
    'notification_sound_appt' => 'default',
    'notification_sound_renewal' => 'alarm',
    'notification_sound_payment' => 'notification',
    'notification_sound_lead' => 'chime',
    'notification_custom_sound' => '',
    'notification_vibration' => '1',
    'notification_vibration_pattern' => 'standard'
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
        :root { --primary: #8b5cf6; --secondary: #06b6d4; --bg: #030712; --surface: rgba(15, 23, 42, 0.6); --border: rgba(255, 255, 255, 0.08); }
        body { font-family: 'Plus Jakarta Sans', sans-serif; background: radial-gradient(circle at top right, #1e1b4b, #030712); color: white; padding: 20px; }
        .container { max-width: 650px; margin: 0 auto; }
        .card { background: var(--surface); border: 1px solid var(--border); border-radius: 20px; padding: 30px; backdrop-filter: blur(20px); margin-bottom: 20px; }
        h1 { font-size: 24px; font-weight: 800; margin-bottom: 25px; display: flex; align-items: center; gap: 12px; }
        h2 { font-size: 14px; font-weight: 700; color: var(--primary); text-transform: uppercase; letter-spacing: 1px; margin-bottom: 20px; padding-bottom: 10px; border-bottom: 1px solid var(--border); }
        .form-group { margin-bottom: 20px; }
        label { display: block; font-size: 12px; font-weight: 700; color: #94a3b8; text-transform: uppercase; margin-bottom: 8px; }
        .input { width: 100%; padding: 12px 15px; background: rgba(0,0,0,0.2); border: 1px solid var(--border); border-radius: 10px; color: white; font-family: inherit; }
        .input:focus { border-color: var(--primary); outline: none; }
        .btn { background: linear-gradient(135deg, var(--primary), #6366f1); color: white; border: none; padding: 15px 30px; border-radius: 12px; font-weight: 700; cursor: pointer; width: 100%; margin-top: 10px; }
        .btn:hover { transform: translateY(-2px); box-shadow: 0 10px 20px rgba(139, 92, 246, 0.3); }
        .alert { padding: 15px; border-radius: 10px; background: rgba(16, 185, 129, 0.1); color: #10b981; border: 1px solid rgba(16, 185, 129, 0.2); margin-bottom: 20px; text-align: center; }
        .back-link { display: block; text-align: center; margin-top: 20px; color: #94a3b8; text-decoration: none; font-size: 14px; }
        .grid-2 { display: grid; grid-template-columns: 1fr 1fr; gap: 15px; }
        .badge { display: inline-block; font-size: 9px; background: var(--primary); padding: 2px 8px; border-radius: 99px; margin-left: 8px; vertical-align: middle; }
        @media (max-width: 600px) { .grid-2 { grid-template-columns: 1fr; } }
    </style>
</head>
<body>

<div class="container">
    <div class="card">
        <h1><i class="fa-solid fa-bell-concierge" style="color:var(--primary)"></i> Smart Alert Settings</h1>
        
        <?php if(isset($msg)): ?>
            <div class="alert"><?php echo $msg; ?></div>
        <?php endif; ?>

        <form method="POST">
            <!-- 📅 APPOINTMENT SETTINGS -->
            <h2><i class="fa-solid fa-calendar-check"></i> Appointment Alerts</h2>

            <div class="form-group">
                <label>⏰ Reminder Lead Time</label>
                <select name="appt_reminder_time" class="input">
                    <option value="0" <?php echo ($settings['appt_reminder_time'] == '0') ? 'selected' : ''; ?>>Exactly at Appointment Time</option>
                    <option value="5" <?php echo ($settings['appt_reminder_time'] == '5') ? 'selected' : ''; ?>>5 Minutes Before</option>
                    <option value="10" <?php echo ($settings['appt_reminder_time'] == '10') ? 'selected' : ''; ?>>10 Minutes Before</option>
                    <option value="30" <?php echo ($settings['appt_reminder_time'] == '30') ? 'selected' : ''; ?>>30 Minutes Before</option>
                    <option value="60" <?php echo ($settings['appt_reminder_time'] == '60') ? 'selected' : ''; ?>>1 Hour Before</option>
                </select>
            </div>

            <div class="grid-2">
                <div class="form-group">
                    <label>🔊 Sound Alert <span class="badge">NEW</span></label>
                    <select name="appt_sound_enabled" class="input">
                        <option value="1" <?php echo ($settings['appt_sound_enabled'] ?? '1') == '1' ? 'selected' : ''; ?>>Enabled</option>
                        <option value="0" <?php echo ($settings['appt_sound_enabled'] ?? '1') == '0' ? 'selected' : ''; ?>>Disabled</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>🗣️ Voice Announce <span class="badge">NEW</span></label>
                    <select name="appt_voice_announce" class="input">
                        <option value="1" <?php echo ($settings['appt_voice_announce'] ?? '1') == '1' ? 'selected' : ''; ?>>Enabled</option>
                        <option value="0" <?php echo ($settings['appt_voice_announce'] ?? '1') == '0' ? 'selected' : ''; ?>>Disabled</option>
                    </select>
                </div>
            </div>

            <!-- 🎵 SOUND MANAGEMENT SECTION -->
            <h2 style="margin-top:30px;"><i class="fa-solid fa-music"></i> Sound Manager <span class="badge">NEW</span></h2>

            <div class="form-group">
                <label>🎵 Default Notification Sound</label>
                <select name="notification_sound" class="input">
                    <?php
                    $sounds = [
                        'default' => '📱 Default (System)',
                        'chime' => '🔔 Chime',
                        'bell' => '🔔 Bell',
                        'notification' => '📬 Notification',
                        'alarm' => '🚨 Alarm',
                        'alert' => '⚠️ Alert',
                        'ringtone' => '📞 Ringtone',
                        'custom' => '🎵 Custom Sound'
                    ];
                    $currentSound = $settings['notification_sound'] ?? 'default';
                    foreach ($sounds as $val => $label) {
                        $sel = ($currentSound === $val) ? 'selected' : '';
                        echo "<option value=\"$val\" $sel>$label</option>";
                    }
                    ?>
                </select>
                <small style="color:#64748b;font-size:11px;margin-top:4px;display:block;">Applied to all notifications when no specific sound is set</small>
            </div>

            <div class="form-group">
                <label>🎵 Per-Notification-Type Sounds</label>
                <div class="grid-2">
                    <div class="form-group">
                        <label style="font-size:10px;">📅 Appointment Sound</label>
                        <select name="notification_sound_appt" class="input">
                            <option value="">Use Default</option>
                            <?php foreach ($sounds as $val => $label): ?>
                                <option value="<?= $val ?>" <?= ($settings['notification_sound_appt'] ?? '') === $val ? 'selected' : '' ?>><?= $label ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label style="font-size:10px;">🔄 Renewal Sound</label>
                        <select name="notification_sound_renewal" class="input">
                            <option value="">Use Default</option>
                            <?php foreach ($sounds as $val => $label): ?>
                                <option value="<?= $val ?>" <?= ($settings['notification_sound_renewal'] ?? 'alarm') === $val ? 'selected' : '' ?>><?= $label ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label style="font-size:10px;">💰 Payment Sound</label>
                        <select name="notification_sound_payment" class="input">
                            <option value="">Use Default</option>
                            <?php foreach ($sounds as $val => $label): ?>
                                <option value="<?= $val ?>" <?= ($settings['notification_sound_payment'] ?? 'notification') === $val ? 'selected' : '' ?>><?= $label ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label style="font-size:10px;">📘 Lead Sound</label>
                        <select name="notification_sound_lead" class="input">
                            <option value="">Use Default</option>
                            <?php foreach ($sounds as $val => $label): ?>
                                <option value="<?= $val ?>" <?= ($settings['notification_sound_lead'] ?? 'chime') === $val ? 'selected' : '' ?>><?= $label ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
            </div>

            <div class="form-group" id="customSoundGroup" style="<?= ($settings['notification_sound'] ?? 'default') === 'custom' ? '' : 'display:none;' ?>">
                <label>🎶 Custom Sound File Name</label>
                <input type="text" name="notification_custom_sound" class="input" placeholder="e.g. my_sound.mp3 or raw/my_sound" value="<?= htmlspecialchars($settings['notification_custom_sound'] ?? '') ?>">
                <small style="color:#64748b;font-size:11px;margin-top:4px;display:block;">
                    For Android: filename in <code>res/raw/</code> folder (without extension).<br>
                    For Web: URL to sound file (.mp3/.wav).
                </small>
            </div>

            <div class="grid-2">
                <div class="form-group">
                    <label>📳 Vibration</label>
                    <select name="notification_vibration" class="input">
                        <option value="1" <?= ($settings['notification_vibration'] ?? '1') == '1' ? 'selected' : '' ?>>Enabled</option>
                        <option value="0" <?= ($settings['notification_vibration'] ?? '1') == '0' ? 'selected' : '' ?>>Disabled</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>📳 Vibration Pattern</label>
                    <select name="notification_vibration_pattern" class="input">
                        <option value="standard" <?= ($settings['notification_vibration_pattern'] ?? 'standard') == 'standard' ? 'selected' : '' ?>>Standard (1 short)</option>
                        <option value="double" <?= ($settings['notification_vibration_pattern'] ?? '') == 'double' ? 'selected' : '' ?>>Double Tap</option>
                        <option value="long" <?= ($settings['notification_vibration_pattern'] ?? '') == 'long' ? 'selected' : '' ?>>Long Vibration</option>
                        <option value="rapid" <?= ($settings['notification_vibration_pattern'] ?? '') == 'rapid' ? 'selected' : '' ?>>Rapid (Alarm)</option>
                        <option value="heartbeat" <?= ($settings['notification_vibration_pattern'] ?? '') == 'heartbeat' ? 'selected' : '' ?>>Heartbeat Pattern</option>
                    </select>
                </div>
            </div>

            <div style="text-align:center;margin-top:10px;">
                <button type="button" class="btn" style="background:linear-gradient(135deg,#10b981,#059669);padding:10px 20px;width:auto;display:inline-flex;align-items:center;gap:8px;" onclick="testSound()">
                    <i class="fa-solid fa-volume-high"></i> Test Sound
                </button>
            </div>

            <script>
            // Toggle custom sound input visibility
            document.querySelector('[name="notification_sound"]')?.addEventListener('change', function() {
                document.getElementById('customSoundGroup').style.display = this.value === 'custom' ? '' : 'none';
            });

            // Sound test function
            function testSound() {
                const soundSelect = document.querySelector('[name="notification_sound"]');
                const customInput = document.querySelector('[name="notification_custom_sound"]');
                let soundName = soundSelect?.value || 'default';
                
                if (soundName === 'custom' && customInput?.value) {
                    soundName = customInput.value;
                }
                
                // Show test feedback
                const btn = document.querySelector('button[onclick="testSound()"]');
                const origText = btn.innerHTML;
                btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Testing...';
                btn.style.opacity = '0.7';
                
                // Send test notification via FCM
                fetch('api_fcm.php?action=test_sound', {
                    method: 'POST',
                    headers: {'Content-Type': 'application/x-www-form-urlencoded'},
                    body: 'sound=' + encodeURIComponent(soundName) + '&vibration=' + encodeURIComponent(document.querySelector('[name="notification_vibration_pattern"]')?.value || 'standard')
                })
                .then(r => r.json())
                .then(data => {
                    btn.innerHTML = data.success ? '<i class="fa-solid fa-check"></i> Sound Sent!' : '<i class="fa-solid fa-xmark"></i> Failed';
                    btn.style.background = data.success ? 'linear-gradient(135deg,#10b981,#059669)' : 'linear-gradient(135deg,#ef4444,#dc2626)';
                    setTimeout(() => {
                        btn.innerHTML = origText;
                        btn.style.opacity = '1';
                        btn.style.background = '';
                    }, 2000);
                })
                .catch(() => {
                    btn.innerHTML = '<i class="fa-solid fa-xmark"></i> Error';
                    setTimeout(() => {
                        btn.innerHTML = origText;
                        btn.style.opacity = '1';
                        btn.style.background = '';
                    }, 2000);
                });
            }
            </script>

            <div class="form-group">
                <label>🖥️ Full Screen UI</label>
                <select name="full_screen_notifications" class="input">
                    <option value="1" <?php echo ($settings['full_screen_notifications'] == '1') ? 'selected' : ''; ?>>Show Full Screen Alert</option>
                    <option value="0" <?php echo ($settings['full_screen_notifications'] == '0') ? 'selected' : ''; ?>>Standard Notification</option>
                </select>
            </div>

            <div class="form-group">
                <label>🎯 Multi-Level Reminders <span class="badge">SMART</span></label>
                <select name="appt_multi_reminder" class="input">
                    <option value="1" <?php echo ($settings['appt_multi_reminder'] ?? '1') == '1' ? 'selected' : ''; ?>>Enabled (24h → 1h → 10min → Now)</option>
                    <option value="0" <?php echo ($settings['appt_multi_reminder'] ?? '1') == '0' ? 'selected' : ''; ?>>Single Reminder Only</option>
                </select>
            </div>

            <div class="form-group">
                <label>🔄 Polling Interval (seconds) <span class="badge">REAL-TIME</span></label>
                <input type="number" name="appt_instant_check_interval" class="input" value="<?php echo $settings['appt_instant_check_interval'] ?? '10'; ?>" min="3" max="60">
            </div>

            <!-- 📊 PAYMENT SETTINGS -->
            <h2><i class="fa-solid fa-money-bill"></i> Payment Alerts</h2>

            <div class="grid-2">
                <div class="form-group">
                    <label>Enable Payment Alerts</label>
                    <select name="enable_payment_alerts" class="input">
                        <option value="1" <?php echo ($settings['enable_payment_alerts'] == '1') ? 'selected' : ''; ?>>Enabled</option>
                        <option value="0" <?php echo ($settings['enable_payment_alerts'] == '0') ? 'selected' : ''; ?>>Disabled</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Daily Summary Time</label>
                    <input type="time" name="payment_alert_time" class="input" value="<?php echo $settings['payment_alert_time']; ?>">
                </div>
            </div>

            <!-- 🔄 RENEWAL SETTINGS -->
            <h2><i class="fa-solid fa-rotate"></i> Renewal Alerts</h2>

            <div class="grid-2">
                <div class="form-group">
                    <label>Enable Renewal Alerts</label>
                    <select name="enable_renewal_alerts" class="input">
                        <option value="1" <?php echo ($settings['enable_renewal_alerts'] == '1') ? 'selected' : ''; ?>>Enabled</option>
                        <option value="0" <?php echo ($settings['enable_renewal_alerts'] == '0') ? 'selected' : ''; ?>>Disabled</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Reminder Days (Before Expiry)</label>
                    <input type="number" name="renewal_alert_days" class="input" value="<?php echo $settings['renewal_alert_days']; ?>" placeholder="e.g. 7">
                </div>
            </div>

            <div class="grid-2">
                <div class="form-group">
                    <label>Grace Period Days (After Expiry)</label>
                    <input type="number" name="grace_period_days" class="input" value="<?php echo $settings['grace_period_days'] ?? '5'; ?>" placeholder="e.g. 5">
                </div>
            </div>

            <!-- 🔌 FCM SETTINGS -->
            <h2><i class="fa-solid fa-plug"></i> Firebase Configuration</h2>

            <div class="form-group">
                <label>FCM Server Key (Firebase)</label>
                <input type="text" name="fcm_server_key" class="input" placeholder="Paste your Firebase Key here" value="<?php echo $settings['fcm_server_key']; ?>">
            </div>

            <button type="submit" class="btn"><i class="fa-solid fa-save"></i> Save Smart Configuration</button>
            
            <div style="text-align:center;margin-top:15px;">
                <a href="app_notify.php?action=check_appointments" style="color:#64748b;text-decoration:none;font-size:12px;" target="_blank">
                    <i class="fa-solid fa-arrow-right"></i> Test Appointment Notifications Now
                </a>
            </div>
        </form>

        <a href="index.html" class="back-link"><i class="fa-solid fa-arrow-left"></i> Back to Dashboard</a>
    </div>
</div>

</body>
</html>
