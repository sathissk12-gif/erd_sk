<?php
include 'db_connect.php';
function printSchema($conn, $table) {
    echo "--- $table ---\n";
    try {
        $stmt = $conn->query("DESCRIBE $table");
        while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            echo $row['Field'] . " (" . $row['Type'] . ")\n";
        }
    } catch(Exception $e) { echo "Error: " . $e->getMessage() . "\n"; }
}
printSchema($conn, 'sales_log');
printSchema($conn, 'renewal_log');
?>
