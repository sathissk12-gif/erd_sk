<?php
/**
 * Branch-Specific Dealer API - ERD Branch
 * Only manages ERD inventory and ledger.
 */
header('Content-Type: application/json');
error_reporting(0);
date_default_timezone_set('Asia/Kolkata');

try {
    $db_erd = "u182809524_sk_core"; $user_erd = "u182809524_skerode";
    $pass = "S@kenterprises6198";
    
    // Connect ONLY to ERD
    $conn = new PDO("mysql:host=127.0.0.1;dbname=$db_erd;charset=utf8", $user_erd, $pass);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $action = $_REQUEST['action'] ?? '';

    switch($action) {
        case 'update':
            $dealer = trim($_REQUEST['dealer'] ?? '');
            $imei_raw = trim($_REQUEST['imei'] ?? '');
            $imei = str_replace([' ', '-'], '', $imei_raw);
            $software = trim($_REQUEST['software'] ?? '');
            $sim_no = trim($_REQUEST['sim_no'] ?? '');
            $selling_rate = (float)($_REQUEST['selling_rate'] ?? 0);
            $date = date('Y-m-d');

            if (!$dealer || !$imei) {
                echo json_encode(['status' => 'error', 'message' => 'Missing Dealer or IMEI']); exit;
            }

            // Search ALL records for this IMEI in ERD ONLY
            $st = $conn->prepare("SELECT imei, status, device_model FROM device_master 
                                    WHERE REPLACE(REPLACE(imei, ' ', ''), '-', '') = ?");
            $st->execute([$imei]);
            $matches = $st->fetchAll(PDO::FETCH_ASSOC);

            if (empty($matches)) throw new Exception("IMEI $imei Not Found in ERD!");

            // Prioritize In Stock
            usort($matches, function($a, $b) {
                $as = (stripos($a['status'], 'stock') !== false) ? 0 : 1;
                $bs = (stripos($b['status'], 'stock') !== false) ? 0 : 1;
                return $as - $bs;
            });

            $target = $matches[0];
            if (stripos($target['status'], 'stock') === false) {
                throw new Exception("IMEI ALREADY SOLD in ERD Database!");
            }

            $db_imei = $target['imei'] ?: $imei;

            // Add to ERD Ledger (Old Table only)
            $stLedgerOld = $conn->prepare("INSERT INTO dealer_ledger_old_1778788199 (dealer_name, imei, date, selling_price, profit, actual_rate) VALUES (?, ?, ?, ?, 0, 0)");
            $stLedgerOld->execute([$dealer, $db_imei, $date, $selling_rate]);
            echo json_encode(['status' => 'success', 'message' => "SUCCESS: $imei Issued in ERD!"]);
            break;

        case 'sync_pending_ledger':
            echo json_encode(['status' => 'success']);
            break;

        case 'pending':
            try {
                $st = $conn->query("
                    SELECT d.dealer_name as holder, d.imei, m.device_model as model
                    FROM dealer_ledger_old_1778788199 d
                    LEFT JOIN device_master m ON REPLACE(REPLACE(d.imei, ' ', ''), '-', '') = REPLACE(REPLACE(m.imei, ' ', ''), '-', '')
                    WHERE (d.txn_id IS NULL OR d.txn_id = '')
                    AND d.imei != 'PAYMENT'
                ");
                $pending = $st->fetchAll(PDO::FETCH_ASSOC);
                echo json_encode($pending);
            } catch(Exception $e) {
                echo json_encode([]);
            }
            break;

        case 'payment':
            if (isset($_REQUEST['imei']) && $_REQUEST['imei'] !== '') {
                $imei = trim($_REQUEST['imei'] ?? '');
                $txn = trim($_REQUEST['txn'] ?? '');
                $sale_rate = (float)($_REQUEST['sale_rate'] ?? 0);
                
                try {
                    $stmtOld = $conn->prepare("UPDATE dealer_ledger_old_1778788199 SET txn_id = ?, selling_price = ? WHERE imei = ? AND imei != 'PAYMENT'");
                    $stmtOld->execute([$txn, $sale_rate, $imei]);
                    
                    echo json_encode([
                        'status' => 'success',
                        'imei' => $imei,
                        'txn' => $txn,
                        'updated_columns' => ['txn_id', 'selling_price']
                    ]);
                } catch(Exception $e) {
                    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
                }
            } else {
                $dealer = trim($_REQUEST['dealer'] ?? '');
                $amount = (float)($_REQUEST['amount'] ?? 0);
                $mode = $_REQUEST['mode'] ?? 'CASH';
                $remark = $_REQUEST['remark'] ?? 'Payment';
                $date = date('Y-m-d');
                try {
                    $stmtLOld = $conn->prepare("INSERT INTO dealer_ledger_old_1778788199 (dealer_name, imei, date, selling_price, profit, actual_rate, txn_id) VALUES (?, 'PAYMENT', ?, ?, 0, 0, ?)");
                    $stmtLOld->execute([$dealer, $date, $amount, 'PAY-' . time()]);
                    
                    echo json_encode(['status' => 'success', 'message' => 'Payment Recorded']);
                } catch(Exception $e) {
                    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
                }
            }
            break;
        case 'schema':
            $out = [];
            $st = $conn->query("SHOW TABLES");
            $out['tables'] = $st->fetchAll(PDO::FETCH_COLUMN);
            $st = $conn->query("DESCRIBE device_master");
            $out['device_master'] = $st->fetchAll(PDO::FETCH_ASSOC);
            $st = $conn->query("DESCRIBE dealer_ledger");
            $out['dealer_ledger'] = $st->fetchAll(PDO::FETCH_ASSOC);
            
            // Diagnostics for old ledger
            try {
                $st = $conn->query("DESCRIBE dealer_ledger_old_1778788199");
                $out['old_ledger_cols'] = $st->fetchAll(PDO::FETCH_ASSOC);
                
                $st = $conn->query("SELECT COUNT(*) FROM dealer_ledger_old_1778788199");
                $out['old_ledger_total_rows'] = (int)$st->fetchColumn();
                
                // Find a column for txn
                $txnCol = '';
                foreach ($out['old_ledger_cols'] as $c) {
                    if (in_array(strtolower($c['Field']), ['txn_id', 'transection_id', 'transaction_id'])) {
                        $txnCol = $c['Field'];
                        break;
                    }
                }
                
                if ($txnCol) {
                    $st = $conn->query("SELECT COUNT(*) FROM dealer_ledger_old_1778788199 WHERE (`$txnCol` IS NULL OR `$txnCol` = '') AND imei != 'PAYMENT'");
                    $out['old_ledger_pending_rows'] = (int)$st->fetchColumn();
                    
                    $st = $conn->query("SELECT * FROM dealer_ledger_old_1778788199 WHERE (`$txnCol` IS NULL OR `$txnCol` = '') AND imei != 'PAYMENT' LIMIT 3");
                    $out['old_ledger_pending_samples'] = $st->fetchAll(PDO::FETCH_ASSOC);
                } else {
                    $st = $conn->query("SELECT * FROM dealer_ledger_old_1778788199 LIMIT 3");
                    $out['old_ledger_samples'] = $st->fetchAll(PDO::FETCH_ASSOC);
                }
            } catch(Exception $ex) {
                $out['old_ledger_error'] = $ex->getMessage();
            }
            
            echo json_encode($out);
            break;
    }
} catch (Exception $e) { 
    if(isset($conn) && $conn->inTransaction()) $conn->rollBack();
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]); 
}
