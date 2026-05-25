<?php
// api_quotation.php
require_once 'db_connect.php';
header('Content-Type: application/json');

$action = $_REQUEST['action'] ?? '';

function generateUID($length = 12) {
    return substr(bin2hex(random_bytes($length)), 0, $length);
}

function getNextQuotationNumber($conn) {
    $prefix = "QTN-" . date('ym') . "-";
    $stmt = $conn->prepare("SELECT quotation_no FROM quotation_log WHERE quotation_no LIKE ? ORDER BY id DESC LIMIT 1");
    $stmt->execute([$prefix . '%']);
    $last = $stmt->fetchColumn();
    if (!$last) return $prefix . "001";
    $num = (int)substr($last, -3);
    return $prefix . str_pad($num + 1, 3, "0", STR_PAD_LEFT);
}

switch ($action) {
    case 'save':
        try {
            $uid = generateUID();
            $qno = getNextQuotationNumber($conn);
            $date = date('Y-m-d');
            $valid = date('Y-m-d', strtotime('+7 days'));

            $data = [
                'uid' => $uid,
                'quotation_no' => $qno,
                'quotation_date' => $date,
                'customer_name' => $_POST['customer_name'] ?? '',
                'mobile_number' => $_POST['mobile_number'] ?? '',
                'location' => $_POST['location'] ?? '',
                'device_model' => $_POST['device_model'] ?? '',
                'software_name' => $_POST['software_name'] ?? '',
                'software_duration' => $_POST['software_duration'] ?? '1_year',
                'sim_type' => $_POST['sim_type'] ?? 'BASIC',
                'relay' => $_POST['relay'] ?? 'NO',
                'total_amount' => (float)($_POST['total_amount'] ?? 0),
                'discount_amount' => (float)($_POST['discount_amount'] ?? 0),
                'valid_until' => $valid,
                'sales_person' => $_POST['sales_person'] ?? ''
            ];

            $cols = implode(', ', array_keys($data));
            $stmt = $conn->prepare("INSERT INTO quotation_log ($cols) VALUES (".str_repeat('?,', count($data)-1)."?)");
            $stmt->execute(array_values($data));

            echo json_encode(['success' => true, 'uid' => $uid, 'quotation_no' => $qno]);
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
        break;

    case 'get':
        $uid = $_GET['uid'] ?? '';
        try {
            $stmt = $conn->prepare("SELECT * FROM quotation_log WHERE uid = ?");
            $stmt->execute([$uid]);
            $data = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($data) {
                $settings = $conn->query("SELECT * FROM settings LIMIT 1")->fetch(PDO::FETCH_ASSOC);
                echo json_encode(['success' => true, 'data' => $data, 'settings' => $settings]);
            } else {
                echo json_encode(['success' => false, 'message' => 'Quotation not found']);
            }
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
        break;
}
?>
