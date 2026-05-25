<?php
include 'db_connect.php';
header('Content-Type: application/json');

try {
    $out = [];
    
    // 1. Check if table exists
    $st = $conn->query("SHOW TABLES LIKE 'dealer_ledger_old_1778788199'");
    $exists = $st->fetchColumn();
    $out['table_exists'] = (bool)$exists;
    
    if ($exists) {
        // 2. Get columns
        $st = $conn->query("DESCRIBE dealer_ledger_old_1778788199");
        $out['columns'] = $st->fetchAll(PDO::FETCH_ASSOC);
        
        // 3. Count total rows
        $st = $conn->query("SELECT COUNT(*) FROM dealer_ledger_old_1778788199");
        $out['total_rows'] = (int)$st->fetchColumn();
        
        // 4. Find potential txn column
        $txnCol = '';
        foreach ($out['columns'] as $c) {
            $name = strtolower($c['Field']);
            if (in_array($name, ['txn_id', 'transection_id', 'transaction_id'])) {
                $txnCol = $c['Field'];
            }
        }
        $out['detected_txn_column'] = $txnCol;
        
        // 5. Count pending (where txn is null or empty)
        if ($txnCol) {
            $st = $conn->query("SELECT COUNT(*) FROM dealer_ledger_old_1778788199 WHERE (`$txnCol` IS NULL OR `$txnCol` = '') AND imei != 'PAYMENT'");
            $out['pending_rows'] = (int)$st->fetchColumn();
            
            // 6. Get sample pending rows
            $st = $conn->query("SELECT * FROM dealer_ledger_old_1778788199 WHERE (`$txnCol` IS NULL OR `$txnCol` = '') AND imei != 'PAYMENT' LIMIT 5");
            $out['sample_pending'] = $st->fetchAll(PDO::FETCH_ASSOC);
        } else {
            // No txn column, let's sample 5 rows
            $st = $conn->query("SELECT * FROM dealer_ledger_old_1778788199 LIMIT 5");
            $out['sample_rows'] = $st->fetchAll(PDO::FETCH_ASSOC);
        }
    }
    
    echo json_encode($out, JSON_PRETTY_PRINT);
} catch (Exception $e) {
    echo json_encode(['error' => $e->getMessage()]);
}
?>
