<?php
// 🔄 Device Return / Replacement System API
require_once 'db_connect.php';
header('Content-Type: application/json');

$action = $_REQUEST['action'] ?? '';

switch($action) {

    case 'search_sale':
        // 🔍 Search sold device by IMEI or Vehicle
        $q = trim($_GET['query'] ?? '');
        if (!$q || strlen($q) < 2) {
            echo json_encode(['status' => 'error', 'error' => 'Enter at least 2 characters']);
            exit;
        }
        try {
            $like = "%$q%";
            $stmt = $conn->prepare("SELECT s.*, d.device_model, d.rate, d.supplier_name, d.holder 
                FROM sales_log s 
                LEFT JOIN device_master d ON s.imei = d.imei
                WHERE s.vehicle_no LIKE ? OR s.imei LIKE ? OR s.customer_name LIKE ?
                ORDER BY s.id DESC LIMIT 20");
            $stmt->execute([$like, $like, $like]);
            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Check if already returned
            foreach ($rows as &$r) {
                $check = $conn->prepare("SELECT id FROM device_returns WHERE sale_uid = ? AND return_status NOT IN ('CANCELLED') LIMIT 1");
                $check->execute([$r['uid']]);
                $r['already_returned'] = $check->fetchColumn() ? true : false;
                
                // Get return details if returned
                if ($r['already_returned']) {
                    $retStmt = $conn->prepare("SELECT * FROM device_returns WHERE sale_uid = ? AND return_status NOT IN ('CANCELLED') ORDER BY id DESC LIMIT 1");
                    $retStmt->execute([$r['uid']]);
                    $r['return_info'] = $retStmt->fetch(PDO::FETCH_ASSOC);
                }
            }
            
            echo json_encode(['status' => 'success', 'data' => $rows]);
        } catch (Exception $e) {
            echo json_encode(['status' => 'error', 'error' => $e->getMessage()]);
        }
        break;

    case 'process_return':
        // 🔄 Process device return
        try {
            $conn->beginTransaction();
            
            $saleUid = trim($_POST['sale_uid'] ?? '');
            $vehicle = trim($_POST['vehicle'] ?? '');
            $imei = trim($_POST['imei'] ?? '');
            $customer = trim($_POST['customer'] ?? '');
            $mobile = trim($_POST['mobile'] ?? '');
            $returnReason = trim($_POST['return_reason'] ?? '');
            $returnType = strtoupper(trim($_POST['return_type'] ?? 'RETURN')); // RETURN or REPLACEMENT
            $newImei = trim($_POST['new_imei'] ?? '');
            $newVehicle = trim($_POST['new_vehicle'] ?? '');
            $charge = (float)($_POST['charge'] ?? 0);
            $refundAmount = (float)($_POST['refund_amount'] ?? 0);
            $notes = trim($_POST['notes'] ?? '');

            if (!$saleUid || !$vehicle) {
                throw new Exception('Sale UID and Vehicle required');
            }

            // 1️⃣ Mark current device as RETURNED in device_master
            if ($imei) {
                $conn->prepare("UPDATE device_master SET status = 'RETURNED', holder = ? WHERE imei = ?")
                    ->execute([$customer, $imei]);
            }

            // 2️⃣ Insert into device_returns table
            $returnData = [
                'sale_uid' => $saleUid,
                'vehicle_no' => $vehicle,
                'imei' => $imei,
                'customer_name' => $customer,
                'mobile_number' => $mobile,
                'return_date' => date('Y-m-d'),
                'return_reason' => $returnReason,
                'return_type' => $returnType,
                'charge' => $charge,
                'refund_amount' => $refundAmount,
                'notes' => $notes,
                'return_status' => 'COMPLETED'
            ];

            $cols = implode(', ', array_keys($returnData));
            $vals = array_values($returnData);
            $placeholders = str_repeat('?,', count($vals)-1) . '?';
            $conn->prepare("INSERT INTO device_returns ($cols) VALUES ($placeholders)")->execute($vals);
            
            $returnId = $conn->lastInsertId();

            // 3️⃣ If REPLACEMENT — assign new device
            if ($returnType === 'REPLACEMENT' && $newImei) {
                // Mark old device replacement
                $conn->prepare("UPDATE device_returns SET new_imei = ?, new_vehicle = ? WHERE id = ?")
                    ->execute([$newImei, $newVehicle, $returnId]);
                
                // Update new device as SOLD
                $conn->prepare("UPDATE device_master SET status = 'SOLD', holder = ? WHERE imei = ?")
                    ->execute([$customer, $newImei]);
                
                // Update sales_log with new IMEI
                $conn->prepare("UPDATE sales_log SET imei = ? WHERE uid = ?")->execute([$newImei, $saleUid]);
                $conn->prepare("UPDATE invoice_log SET imei = ? WHERE uid = ?")->execute([$newImei, $saleUid]);
            }

            // 4️⃣ Add device back to stock if it's a RETURN (not replacement)
            if ($returnType === 'RETURN' && $imei) {
                $conn->prepare("UPDATE device_master SET status = 'In Stock', holder = NULL, sold_date = NULL, sales_person = NULL WHERE imei = ?")
                    ->execute([$imei]);
            }

            $conn->commit();
            echo json_encode(['status' => 'success', 'message' => 'Return processed successfully', 'return_id' => $returnId]);

        } catch (Exception $e) {
            if ($conn->inTransaction()) $conn->rollBack();
            echo json_encode(['status' => 'error', 'error' => $e->getMessage()]);
        }
        break;

    case 'list_returns':
        // 📋 List all returns
        try {
            $stmt = $conn->query("SELECT * FROM device_returns ORDER BY id DESC LIMIT 100");
            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
            echo json_encode(['status' => 'success', 'data' => $rows]);
        } catch (Exception $e) {
            echo json_encode(['status' => 'error', 'error' => $e->getMessage()]);
        }
        break;

    case 'cancel_return':
        // ❌ Cancel a return
        $id = (int)($_POST['id'] ?? 0);
        try {
            $conn->beginTransaction();
            
            $stmt = $conn->prepare("SELECT * FROM device_returns WHERE id = ?");
            $stmt->execute([$id]);
            $ret = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$ret) throw new Exception('Return record not found');
            
            // Restore device status
            if ($ret['imei']) {
                $conn->prepare("UPDATE device_master SET status = 'SOLD', holder = ? WHERE imei = ?")
                    ->execute([$ret['customer_name'], $ret['imei']]);
            }
            
            // If replacement, revert new device
            if ($ret['return_type'] === 'REPLACEMENT' && $ret['new_imei']) {
                $conn->prepare("UPDATE device_master SET status = 'In Stock', holder = NULL WHERE imei = ?")
                    ->execute([$ret['new_imei']]);
            }
            
            $conn->prepare("UPDATE device_returns SET return_status = 'CANCELLED' WHERE id = ?")->execute([$id]);
            
            $conn->commit();
            echo json_encode(['status' => 'success', 'message' => 'Return cancelled']);
        } catch (Exception $e) {
            if ($conn->inTransaction()) $conn->rollBack();
            echo json_encode(['status' => 'error', 'error' => $e->getMessage()]);
        }
        break;

    case 'get_stats':
        // 📊 Return stats
        try {
            $total = $conn->query("SELECT COUNT(*) FROM device_returns")->fetchColumn();
            $monthly = $conn->query("SELECT COUNT(*) FROM device_returns WHERE MONTH(return_date) = MONTH(CURDATE()) AND YEAR(return_date) = YEAR(CURDATE())")->fetchColumn();
            $replacements = $conn->query("SELECT COUNT(*) FROM device_returns WHERE return_type = 'REPLACEMENT'")->fetchColumn();
            $cancelled = $conn->query("SELECT COUNT(*) FROM device_returns WHERE return_status = 'CANCELLED'")->fetchColumn();
            echo json_encode([
                'status' => 'success',
                'total' => (int)$total,
                'monthly' => (int)$monthly,
                'replacements' => (int)$replacements,
                'cancelled' => (int)$cancelled
            ]);
        } catch (Exception $e) {
            echo json_encode(['status' => 'error', 'error' => $e->getMessage()]);
        }
        break;

    case 'setup_table':
        // 🛠️ Auto-create device_returns table
        try {
            $conn->exec("CREATE TABLE IF NOT EXISTS device_returns (
                id INT AUTO_INCREMENT PRIMARY KEY,
                sale_uid VARCHAR(50) NOT NULL,
                vehicle_no VARCHAR(50) NOT NULL,
                imei VARCHAR(50),
                customer_name VARCHAR(255),
                mobile_number VARCHAR(20),
                return_date DATE,
                return_reason TEXT,
                return_type VARCHAR(20) DEFAULT 'RETURN',
                charge DECIMAL(10,2) DEFAULT 0,
                refund_amount DECIMAL(10,2) DEFAULT 0,
                notes TEXT,
                return_status VARCHAR(20) DEFAULT 'COMPLETED',
                new_imei VARCHAR(50),
                new_vehicle VARCHAR(50),
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
            echo json_encode(['status' => 'success', 'message' => 'device_returns table created/verified']);
        } catch (Exception $e) {
            echo json_encode(['status' => 'error', 'error' => $e->getMessage()]);
        }
        break;

    default:
        echo json_encode(['status' => 'error', 'error' => 'Unknown action']);
}
?>
