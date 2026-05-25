<?php
require_once 'db_connect.php';

try {
    echo "--- PAYMENT SYNC ENGINE v1.0 ---\n\n";

    // 1. Find device_master txn column
    $stmtM = $conn->query("DESCRIBE device_master");
    $mCols = $stmtM->fetchAll(PDO::FETCH_COLUMN);
    $mTxn = null;
    foreach (['transection_id', 'transaction_id', 'txn_id'] as $p) {
        if (in_array($p, $mCols)) { $mTxn = $p; break; }
    }

    // 2. Find dealer_ledger txn column
    $stmtL = $conn->query("DESCRIBE dealer_ledger");
    $lCols = $stmtL->fetchAll(PDO::FETCH_COLUMN);
    $lTxn = null;
    foreach (['txn_id', 'transection_id', 'transaction_id'] as $p) {
        if (in_array($p, $lCols)) { $lTxn = $p; break; }
    }

    if (!$mTxn) { die("❌ Error: No transaction ID column found in device_master.\n"); }
    if (!$lTxn) { die("❌ Error: No transaction ID column found in dealer_ledger. Please add 'txn_id' column first.\n"); }

    echo "🔗 Found Master Column: $mTxn\n";
    echo "🔗 Found Ledger Column: $lTxn\n\n";

    // 3. Perform Sync
    echo "🏗️ Syncing payments from device_master to dealer_ledger...\n";
    
    $sql = "UPDATE dealer_ledger dl
            INNER JOIN device_master dm ON TRIM(dl.imei) = TRIM(dm.imei)
            SET dl.`$lTxn` = dm.`$mTxn`
            WHERE (dl.`$lTxn` IS NULL OR TRIM(dl.`$lTxn`) = '')
            AND (dm.`$mTxn` IS NOT NULL AND TRIM(dm.`$mTxn`) != '')";
            
    $count = $conn->exec($sql);
    
    echo "✅ Success! $count records were auto-filled in dealer_ledger.\n";

} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage();
}
?>
