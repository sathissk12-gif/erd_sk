<?php
include 'db_connect.php';
header('Content-Type: application/json');

$action = $_REQUEST['action'] ?? '';

switch ($action) {
    case 'get_summary':
        try {
            // 🛡️ Ensure settings table exists and has the key
            $conn->exec("CREATE TABLE IF NOT EXISTS system_settings (key_name VARCHAR(50) PRIMARY KEY, key_value TEXT)");
            $conn->exec("INSERT IGNORE INTO system_settings (key_name, key_value) VALUES ('opening_bank_balance', '0')");

            // 0. 🏁 Opening Balance
            $opening_bal = $conn->query("SELECT key_value FROM system_settings WHERE key_name = 'opening_bank_balance'")->fetchColumn() ?: 0;

            // 1. 💰 Bank Balance Calculation
            // Inflow: Total Office Settlements
            $inflow = $conn->query("SELECT SUM(total_amount) FROM office_settlement_log")->fetchColumn() ?: 0;
            
            // Outflow: Device Purchases
            $outflow_devices = $conn->query("SELECT SUM(rate) FROM device_master")->fetchColumn() ?: 0;
            
            // Outflow: Software/Relay Purchases (Stock Ledger additions)
            $sql_sw_out = "SELECT SUM(CAST(l.qty AS SIGNED) * IFNULL(p.cost, 0)) 
                           FROM stock_ledger l 
                           LEFT JOIN price_master p ON l.item_name = p.name 
                           WHERE CAST(l.qty AS SIGNED) > 0";
            $outflow_sw = $conn->query($sql_sw_out)->fetchColumn() ?: 0;
            
            $bank_balance = (float)$opening_bal + (float)$inflow - ((float)$outflow_devices + (float)$outflow_sw);

            // 2. 📦 Live Stock Value Calculation
            // Current Devices in Stock
            $stock_val_devices = $conn->query("SELECT SUM(rate) FROM device_master WHERE status = 'In Stock'")->fetchColumn() ?: 0;
            
            // Current Software/Relay in Stock
            $sql_sw_stock = "SELECT l.item_name, SUM(CAST(l.qty AS SIGNED)) as rem_qty, p.cost 
                             FROM stock_ledger l 
                             LEFT JOIN price_master p ON l.item_name = p.name 
                             GROUP BY l.item_name";
            $sw_rows = $conn->query($sql_sw_stock)->fetchAll(PDO::FETCH_ASSOC);
            $stock_val_sw = 0;
            foreach($sw_rows as $row) {
                $stock_val_sw += ((float)$row['rem_qty'] * (float)($row['cost'] ?? 0));
            }

            echo json_encode([
                'bank_balance' => (float)$bank_balance,
                'stock_value' => (float)($stock_val_devices + $stock_val_sw),
                'breakdown' => [
                    'opening_bal' => (float)$opening_bal,
                    'settlements' => (float)$inflow,
                    'device_purchases' => (float)$outflow_devices,
                    'software_purchases' => (float)$outflow_sw,
                    'device_stock_val' => (float)$stock_val_devices,
                    'software_stock_val' => (float)$stock_val_sw
                ]
            ]);
        } catch (Exception $e) {
            echo json_encode(['error' => $e->getMessage()]);
        }
        break;

    case 'update_settings':
        try {
            foreach($_POST as $key => $val) {
                $stmt = $conn->prepare("INSERT INTO system_settings (key_name, key_value) VALUES (?, ?) ON DUPLICATE KEY UPDATE key_value = ?");
                $stmt->execute([$key, $val, $val]);
            }
            echo json_encode(['success' => true]);
        } catch (Exception $e) { 
            echo json_encode(['success' => false, 'error' => $e->getMessage()]); 
        }
        break;

    case 'calibrate_balance':
        try {
            $target = (float)($_POST['target_balance'] ?? 0);
            
            // Calculate current net flow
            $inflow = $conn->query("SELECT SUM(total_amount) FROM office_settlement_log")->fetchColumn() ?: 0;
            $outflow_devices = $conn->query("SELECT SUM(rate) FROM device_master")->fetchColumn() ?: 0;
            $sql_sw_out = "SELECT SUM(CAST(l.qty AS SIGNED) * IFNULL(p.cost, 0)) 
                           FROM stock_ledger l 
                           LEFT JOIN price_master p ON l.item_name = p.name 
                           WHERE CAST(l.qty AS SIGNED) > 0";
            $outflow_sw = $conn->query($sql_sw_out)->fetchColumn() ?: 0;
            
            $net_flow = (float)$inflow - ((float)$outflow_devices + (float)$outflow_sw);
            $new_opening = $target - $net_flow;

            $stmt = $conn->prepare("INSERT INTO system_settings (key_name, key_value) VALUES ('opening_bank_balance', ?) ON DUPLICATE KEY UPDATE key_value = ?");
            $stmt->execute([$new_opening, $new_opening]);
            
            echo json_encode(['success' => true, 'new_opening' => $new_opening]);
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'error' => $e->getMessage()]);
        }
        break;

    case 'get_stock_details':
        try {
            // Detailed device stock
            $devices = $conn->query("SELECT device_model as name, COUNT(*) as qty, SUM(rate) as value FROM device_master WHERE status = 'In Stock' GROUP BY device_model")->fetchAll(PDO::FETCH_ASSOC);
            
            // Detailed software stock
            $sql_sw = "SELECT l.item_name as name, SUM(CAST(l.qty AS SIGNED)) as qty, IFNULL(p.cost, 0) as rate, (SUM(CAST(l.qty AS SIGNED)) * IFNULL(p.cost, 0)) as value 
                       FROM stock_ledger l 
                       LEFT JOIN price_master p ON l.item_name = p.name 
                       GROUP BY l.item_name";
            $software = $conn->query($sql_sw)->fetchAll(PDO::FETCH_ASSOC);
            
            echo json_encode(['devices' => $devices, 'software' => $software]);
        } catch (Exception $e) {
            echo json_encode(['error' => $e->getMessage()]);
        }
        break;

    case 'get_transactions':
        try {
            // 🛡️ Ensure table exists
            $conn->exec("CREATE TABLE IF NOT EXISTS bank_transactions (
                id INT AUTO_INCREMENT PRIMARY KEY,
                type ENUM('INFLOW', 'OUTFLOW'),
                txn_date DATETIME,
                description VARCHAR(255),
                amount DECIMAL(10,2),
                ref_id VARCHAR(100),
                ref_table VARCHAR(50),
                UNIQUE KEY unique_txn (ref_id, ref_table)
            )");

            // 🔄 Robust Auto-Sync
            // 1. Settlements
            try {
                $conn->exec("INSERT IGNORE INTO bank_transactions (type, txn_date, description, amount, ref_id, ref_table)
                             SELECT 'INFLOW', created_at, 'Office Settlement', total_amount, COALESCE(txn_id, id), 'office_settlement_log' 
                             FROM office_settlement_log WHERE total_amount > 0");
            } catch (Exception $e1) {}
            
            // 2. Device Purchases
            try {
                $conn->exec("INSERT IGNORE INTO bank_transactions (type, txn_date, description, amount, ref_id, ref_table)
                             SELECT 'OUTFLOW', date, CONCAT('Device Purchase: ', device_model), rate, COALESCE(imei, CAST(id AS CHAR)), 'device_master' 
                             FROM device_master WHERE rate > 0");
            } catch (Exception $e2) {}
            
            // 3. Software Stock Additions
            try {
                $conn->exec("INSERT IGNORE INTO bank_transactions (type, txn_date, description, amount, ref_id, ref_table)
                             SELECT 'OUTFLOW', l.date, CONCAT('Stock Add: ', l.item_name), (CAST(l.qty AS SIGNED) * IFNULL(p.cost, 0)), CONCAT(l.id, '_', l.item_name), 'stock_ledger' 
                             FROM stock_ledger l 
                             LEFT JOIN price_master p ON l.item_name = p.name
                             WHERE CAST(l.qty AS SIGNED) > 0");
            } catch (Exception $e3) {}

            // Fetch from centralized table
            $stmt = $conn->query("SELECT type, txn_date as date, description, amount FROM bank_transactions ORDER BY txn_date DESC LIMIT 100");
            $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            if (empty($data)) {
                // If still empty, return a dummy item to verify UI
                echo json_encode([['type' => 'INFLOW', 'date' => date('Y-m-d H:i:s'), 'description' => 'System Initialized', 'amount' => 0]]);
            } else {
                echo json_encode($data);
            }
        } catch (Exception $e) {
            echo json_encode(['error' => $e->getMessage()]);
        }
        break;
}
?>
