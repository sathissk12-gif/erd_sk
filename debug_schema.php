<?php
include 'db_connect.php';
header('Content-Type: text/plain');

$tables = ['renewal_log', 'renewal_invoice_log', 'settings', 'stock_ledger'];

foreach ($tables as $table) {
    echo "\n--- Structure of $table ---\n";
    try {
        $stmt = $conn->query("DESCRIBE `$table` ");
        $cols = $stmt->fetchAll(PDO::FETCH_ASSOC);
        foreach ($cols as $col) {
            echo "{$col['Field']} - {$col['Type']}\n";
        }
    } catch (Exception $e) {
        echo "Error: " . $e->getMessage() . "\n";
    }
}
?>
