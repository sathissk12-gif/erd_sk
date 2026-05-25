<?php
include 'db_connect.php';
header('Content-Type: application/json');

$action = $_REQUEST['action'] ?? '';

switch ($action) {
    case 'get_data':
        try {
            $type = $_GET['type'] ?? 'sales';
            $startDate = $_GET['start_date'] ?? date('Y-m-01');
            $endDate = $_GET['end_date'] ?? date('Y-m-d');

            $data = [];
            
            if ($type === 'sales') {
                $stmt = $conn->prepare("SELECT invoice_no, invoice_date as date, customer_name, mobile_number, vehicle_no, imei, software, total_amount, paid_amount FROM invoice_log WHERE invoice_date BETWEEN ? AND ? ORDER BY invoice_date DESC");
                $stmt->execute([$startDate, $endDate]);
                $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
            } 
            elseif ($type === 'renewal') {
                $stmt = $conn->prepare("SELECT * FROM renewal_log WHERE date BETWEEN ? AND ? OR valid_to BETWEEN ? AND ? ORDER BY valid_to ASC");
                $stmt->execute([$startDate, $endDate, $startDate, $endDate]);
                $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
            }
            elseif ($type === 'device_stock') {
                $stmt = $conn->query("SELECT imei, device_model, supplier_name, status, date as purchase_date FROM device_master ORDER BY device_model ASC");
                $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
            }
            elseif ($type === 'software_stock') {
                $stmt = $conn->query("SELECT item_name, item_type, SUM(CAST(qty AS SIGNED)) as current_stock FROM stock_ledger GROUP BY item_name, item_type");
                $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
            }
            elseif ($type === 'device_sales') {
                $stmt = $conn->prepare("SELECT sl_no, imei, device_model, supplier_name, status, date as purchase_date FROM device_master WHERE status = 'Sold' AND date BETWEEN ? AND ? ORDER BY date DESC");
                $stmt->execute([$startDate, $endDate]);
                $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
            }

            echo json_encode($data);
        } catch (Exception $e) {
            echo json_encode(['error' => $e->getMessage()]);
        }
        break;
}
?>
