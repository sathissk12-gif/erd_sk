<?php
// api_crm.php
require_once 'db_connect.php';
header('Content-Type: application/json');

$action = $_REQUEST['action'] ?? '';

switch ($action) {
    case 'save_lead':
        try {
            $data = [
                'customer_name' => $_POST['customer_name'] ?? '',
                'mobile_number' => $_POST['mobile_number'] ?? '',
                'location' => $_POST['location'] ?? '',
                'interest' => $_POST['interest'] ?? '',
                'status' => $_POST['status'] ?? 'NEW',
                'source' => $_POST['source'] ?? 'DIRECT',
                'followup_date' => $_POST['followup_date'] ?? null,
                'last_remark' => $_POST['last_remark'] ?? ''
            ];

            $cols = implode(', ', array_keys($data));
            $stmt = $conn->prepare("INSERT INTO crm_leads ($cols) VALUES (".str_repeat('?,', count($data)-1)."?)");
            $stmt->execute(array_values($data));

            echo json_encode(['success' => true, 'message' => 'Lead saved']);
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
        break;

    case 'list_leads':
        try {
            $stmt = $conn->query("SELECT * FROM crm_leads ORDER BY created_at DESC LIMIT 100");
            $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
            echo json_encode($data);
        } catch (Exception $e) {
            echo json_encode([]);
        }
        break;

    case 'update_status':
        try {
            $id = $_POST['id'] ?? '';
            $status = $_POST['status'] ?? '';
            $remark = $_POST['remark'] ?? '';
            $followup = $_POST['followup_date'] ?? null;

            $stmt = $conn->prepare("UPDATE crm_leads SET status = ?, last_remark = ?, followup_date = ? WHERE id = ?");
            $stmt->execute([$status, $remark, $followup, $id]);
            echo json_encode(['success' => true]);
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
        break;

    case 'get_stats':
        try {
            $stmt = $conn->query("SELECT status, COUNT(*) as count FROM crm_leads GROUP BY status");
            echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
        } catch (Exception $e) {
            echo json_encode([]);
        }
        break;
}
?>
