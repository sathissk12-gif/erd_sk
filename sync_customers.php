<?php
include 'db_connect.php';
header('Content-Type: application/json');

try {
    // 1. Force Clean Start: Ensure the table is fresh and correctly structured
    $conn->exec("DROP TABLE IF EXISTS customerdatas");
    $conn->exec("CREATE TABLE customerdatas (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(255),
        mobile VARCHAR(20),
        location VARCHAR(255),
        UNIQUE KEY unique_cust (name, mobile)
    ) ENGINE=InnoDB");

    // 2. Define Sources and their likely column names
    $sources = [
        ['table' => 'sales_log',            'name' => 'customer_name', 'mobile' => 'mobile_number', 'loc' => 'location'],
        ['table' => 'invoice_log',          'name' => 'customer_name', 'mobile' => 'mobile_number', 'loc' => 'location'],
        ['table' => 'renewal_invoice_log',  'name' => 'customer_name', 'mobile' => 'mobile_number', 'loc' => 'location'],
        ['table' => 'renewal_log',          'name' => 'customer',      'mobile' => 'mobile',        'loc' => 'location']
    ];

    $totalSynced = 0;

    foreach ($sources as $src) {
        $table = $src['table'];
        
        // Skip if table doesn't exist
        $check = $conn->query("SHOW TABLES LIKE '$table'");
        if ($check->rowCount() == 0) continue;

        // Get actual columns in the source table to handle dynamic naming
        $stmtCols = $conn->query("DESCRIBE $table");
        $cols = $stmtCols->fetchAll(PDO::FETCH_COLUMN);

        // Find the best matching columns for Name, Mobile, Location
        $n = in_array($src['name'], $cols) ? $src['name'] : (in_array('name', $cols) ? 'name' : (in_array('customer_name', $cols) ? 'customer_name' : ''));
        $m = in_array($src['mobile'], $cols) ? $src['mobile'] : (in_array('contact_no', $cols) ? 'contact_no' : (in_array('mobile_number', $cols) ? 'mobile_number' : (in_array('mobile', $cols) ? 'mobile' : "''")));
        $l = in_array($src['loc'], $cols) ? $src['loc'] : (in_array('location', $cols) ? 'location' : "''");

        if (!$n) continue; // Must have a name

        // Perform the sync using INSERT IGNORE to handle duplicates automatically (only sync customers with a mobile number)
        $sql = "INSERT IGNORE INTO customerdatas (name, mobile, location) 
                SELECT $n, $m, $l 
                FROM $table WHERE $n IS NOT NULL AND $n != '' AND $m IS NOT NULL AND TRIM($m) != '' AND TRIM($m) != '-'";
        
        $affected = $conn->exec($sql);
        if ($affected !== false) $totalSynced += $affected;
    }

    echo json_encode([
        'status' => 'success', 
        'message' => "Successfully rebuilt Customer Master and synchronized $totalSynced unique records from all operation logs."
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => 'SYNC FAILED: ' . $e->getMessage()]);
}
?>
