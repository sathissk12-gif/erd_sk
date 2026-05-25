<?php
header('Content-Type: application/json');
include 'hostinger_config.php';

$action = $_GET['action'] ?? '';

if (HOSTINGER_API_TOKEN === 'YOUR_API_TOKEN_HERE') {
    echo json_encode(['error' => 'API Token not configured. Please update hostinger_config.php']);
    exit;
}

function hostinger_request($endpoint, $method = 'GET', $data = null) {
    $url = HOSTINGER_API_URL . $endpoint;
    $ch = curl_init($url);
    
    $headers = [
        'Authorization: Bearer ' . HOSTINGER_API_TOKEN,
        'Accept: application/json',
        'Content-Type: application/json',
        'User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36'
    ];
    
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true); // Follow redirects if any
    
    if ($data) {
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    }
    
    $response = curl_exec($ch);
    $error = curl_error($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    if ($error) {
        return ['code' => 500, 'data' => ['error' => 'Curl Error: ' . $error]];
    }

    $decoded = json_decode($response, true);
    if (json_last_error() !== JSON_ERROR_NONE) {
        return ['code' => 500, 'data' => ['error' => 'Invalid JSON response from Hostinger: ' . $response]];
    }
    
    return [
        'code' => $httpCode,
        'data' => $decoded
    ];
}

switch ($action) {
    case 'vps_list':
        $res = hostinger_request('/api/vps/v1/virtual-machines');
        echo json_encode($res['data'] ?? []);
        break;

    case 'vps_details':
        $id = $_GET['id'] ?? '';
        $res = hostinger_request("/api/vps/v1/virtual-machines/$id");
        echo json_encode($res['data'] ?? []);
        break;

    case 'vps_control':
        $id = $_POST['id'] ?? '';
        $cmd = $_POST['command'] ?? ''; // power-on, power-off, reboot
        $res = hostinger_request("/api/vps/v1/virtual-machines/$id/$cmd", 'POST');
        echo json_encode($res['data'] ?? []);
        break;

    case 'vps_metrics':
        $id = $_GET['id'] ?? '';
        $res = hostinger_request("/api/vps/v1/virtual-machines/$id/metrics");
        echo json_encode($res['data'] ?? []);
        break;

    case 'domain_list':
        $res = hostinger_request('/api/domains/v1/domains');
        echo json_encode($res['data'] ?? []);
        break;

    case 'dns_list':
        $domain = $_GET['domain'] ?? '';
        $res = hostinger_request("/api/domains/v1/domains/$domain/dns-zone");
        echo json_encode($res['data'] ?? []);
        break;

    case 'usage':
        $res = hostinger_request('/account/usage');
        echo json_encode($res['data']);
        break;

    default:
        echo json_encode(['error' => 'Invalid action']);
        break;
}
?>
