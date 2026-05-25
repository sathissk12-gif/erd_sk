<?php
header('Content-Type: application/json');

require_once 'db_connect.php';

$heartbeatFile = __DIR__ . DIRECTORY_SEPARATOR . 'whatsapp_gateway_state.json';
$heartbeatFreshSeconds = 120;

if (is_file($heartbeatFile)) {
    $heartbeatRaw = @file_get_contents($heartbeatFile);
    $heartbeat = json_decode((string)$heartbeatRaw, true);
    if (is_array($heartbeat)) {
        $reportedAt = strtotime((string)($heartbeat['reportedAt'] ?? ''));
        if ($reportedAt && (time() - $reportedAt) <= $heartbeatFreshSeconds) {
            echo json_encode([
                'success' => true,
                'status' => strtoupper((string)($heartbeat['status'] ?? 'UNKNOWN')),
                'gatewayUrl' => 'heartbeat',
                'lastEvent' => $heartbeat['lastEvent'] ?? null,
                'lastError' => $heartbeat['lastError'] ?? null,
                'hasQr' => !empty($heartbeat['hasQr']),
                'uptimeSeconds' => $heartbeat['uptimeSeconds'] ?? null,
                'reportedAt' => $heartbeat['reportedAt'] ?? null,
                'source' => $heartbeat['source'] ?? 'gateway'
            ]);
            exit;
        }
    }
}

$gatewayUrl = 'http://localhost:3000';

try {
    if (isset($conn)) {
        $stmt = $conn->query("SELECT key_value FROM system_settings WHERE key_name = 'whatsapp_gateway_url' LIMIT 1");
        if ($stmt) {
            $value = $stmt->fetchColumn();
            if ($value) {
                $gatewayUrl = rtrim(trim($value), '/');
            }
        }
    }
} catch (Exception $e) {
    // Keep default URL if settings table/query is unavailable.
}

$host = parse_url($gatewayUrl, PHP_URL_HOST) ?: '';
$isLocalOnly = in_array(strtolower($host), ['localhost', '127.0.0.1', '::1'], true);

if ($isLocalOnly) {
    echo json_encode([
        'success' => true,
        'status' => 'LOCAL_ONLY',
        'gatewayUrl' => $gatewayUrl,
        'message' => 'Gateway URL points to localhost. Mobile devices cannot verify a PC-local gateway through the domain.'
    ]);
    exit;
}

$url = $gatewayUrl . '/status';
$ch = curl_init($url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 8);
curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Accept: application/json']);

$response = curl_exec($ch);
$error = curl_error($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($error || $httpCode < 200 || $httpCode >= 300) {
    echo json_encode([
        'success' => false,
        'status' => 'OFFLINE',
        'gatewayUrl' => $gatewayUrl,
        'message' => $error ?: ('Gateway status request failed with HTTP ' . $httpCode)
    ]);
    exit;
}

$decoded = json_decode($response, true);
if (!is_array($decoded)) {
    echo json_encode([
        'success' => false,
        'status' => 'INVALID_RESPONSE',
        'gatewayUrl' => $gatewayUrl,
        'message' => 'Gateway returned an invalid JSON response.'
    ]);
    exit;
}

echo json_encode([
    'success' => true,
    'status' => strtoupper((string)($decoded['status'] ?? 'UNKNOWN')),
    'gatewayUrl' => $gatewayUrl,
    'lastEvent' => $decoded['lastEvent'] ?? null,
    'lastError' => $decoded['lastError'] ?? null,
    'hasQr' => !empty($decoded['hasQr']),
    'uptimeSeconds' => $decoded['uptimeSeconds'] ?? null
]);
?>
