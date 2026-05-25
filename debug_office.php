<?php
header('Content-Type: text/plain');
error_reporting(E_ALL);
ini_set('display_errors', 1);

try {
    include 'db_connect.php';
    echo "Connection OK\n";
    
    // Testing the queries from api_office_settlement.php
    $RELAY_RATE = 60;
    
    echo "Checking tables...\n";
    $tabs = $conn->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
    print_r($tabs);
    
    echo "\nTesting Sales Query...\n";
    $salesStmt = $conn->query("
        SELECT 
            s.imei, s.software, s.relay
        FROM sales_log s
        LIMIT 1
    ");
    echo "Sales Query OK\n";

} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage();
}
?>
