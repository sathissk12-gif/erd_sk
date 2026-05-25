<?php
// C:\Users\sathi\.gemini\antigravity\scratch\billing_app\inventory_api.php
include 'db_connect.php';
header('Content-Type: application/json');

$action = $_REQUEST['action'] ?? '';

if ($action === 'get_config') {
    try {
        $stmt = $conn->query("SELECT name as device_model, cost as rate FROM price_master ORDER BY name ASC");
        $models = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $stmtSl = $conn->query("SELECT sl_no FROM device_master ORDER BY id DESC LIMIT 1");
        $lastSlRow = $stmtSl->fetch(PDO::FETCH_ASSOC);
        $nextSl = $lastSlRow ? ((int)$lastSlRow['sl_no'] + 1) : 1;

        echo json_encode([
            'status' => 'success',
            'models' => $models,
            'next_sl' => $nextSl
        ]);
    } catch (Exception $e) {
        echo json_encode(['error' => 'DB Error: ' . $e->getMessage()]);
    }
    exit;
}
?>
