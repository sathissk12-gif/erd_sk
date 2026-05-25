<?php
header('Content-Type: application/json');

$sharedToken = 'sk_whatsapp_heartbeat_2026';
$raw = file_get_contents('php://input');
$data = json_decode($raw, true);

if (!is_array($data) || ($data['token'] ?? '') !== $sharedToken) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

$payload = [
    'status' => strtoupper((string)($data['status'] ?? 'UNKNOWN')),
    'lastEvent' => (string)($data['lastEvent'] ?? ''),
    'lastError' => (string)($data['lastError'] ?? ''),
    'hasQr' => !empty($data['hasQr']),
    'uptimeSeconds' => (int)($data['uptimeSeconds'] ?? 0),
    'reportedAt' => gmdate('c'),
    'source' => (string)($data['source'] ?? 'gateway')
];

$file = __DIR__ . DIRECTORY_SEPARATOR . 'whatsapp_gateway_state.json';
$written = @file_put_contents($file, json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES), LOCK_EX);

if ($written === false) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Unable to persist heartbeat']);
    exit;
}

echo json_encode(['success' => true]);
?>
