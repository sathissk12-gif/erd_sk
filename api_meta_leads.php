<?php
// api_meta_leads.php
require_once 'db_connect.php';

header('Content-Type: application/json');

$action = $_GET['action'] ?? 'list';

if ($action === 'list') {
    try {
        $stmt = $conn->query("SELECT * FROM meta_leads ORDER BY created_time DESC LIMIT 100");
        $leads = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo json_encode($leads);
    } catch (PDOException $e) {
        echo json_encode(['error' => $e->getMessage()]);
    }
}

if ($action === 'stats') {
    try {
        $today = date('Y-m-d');
        $stmt = $conn->prepare("SELECT 
            COUNT(*) as total,
            SUM(CASE WHEN DATE(created_time) = ? THEN 1 ELSE 0 END) as today,
            SUM(CASE WHEN is_processed = 0 THEN 1 ELSE 0 END) as pending
            FROM meta_leads");
        $stmt->execute([$today]);
        echo json_encode($stmt->fetch(PDO::FETCH_ASSOC));
    } catch (PDOException $e) {
        echo json_encode(['error' => $e->getMessage()]);
    }
}

if ($action === 'mark_processed') {
    $id = $_GET['id'] ?? null;
    if ($id) {
        $stmt = $conn->prepare("UPDATE meta_leads SET is_processed = 1 WHERE id = ?");
        $stmt->execute([$id]);
        echo json_encode(['success' => true]);
    }
}

if ($action === 'latest_unprocessed') {
    try {
        $stmt = $conn->query("SELECT * FROM meta_leads WHERE is_processed = 0 ORDER BY created_time DESC LIMIT 1");
        $lead = $stmt->fetch(PDO::FETCH_ASSOC);
        echo json_encode($lead ?: ['none' => true]);
    } catch (PDOException $e) {
        echo json_encode(['error' => $e->getMessage()]);
    }
}

if ($action === 'sound_settings') {
    try {
        $keys = ['appt_sound_enabled','notification_sound','notification_sound_lead','notification_custom_sound','notification_vibration','notification_vibration_pattern'];
        $placeholders = rtrim(str_repeat('?,', count($keys)), ',');
        $stmt = $conn->prepare("SELECT key_name, key_value FROM system_settings WHERE key_name IN ($placeholders)");
        $stmt->execute($keys);
        $result = [];
        foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
            $result[$row['key_name']] = $row['key_value'];
        }
        echo json_encode($result);
    } catch (Exception $e) {
        echo json_encode(['error' => $e->getMessage()]);
    }
}
?>
