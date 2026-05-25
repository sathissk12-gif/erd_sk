<?php
include 'db_connect.php';
header('Content-Type: text/plain');

echo "--- RENEWAL_LOG SCHEMA ---\n";
try {
    $stmt = $conn->query("DESCRIBE renewal_log");
    $cols = $stmt->fetchAll(PDO::FETCH_ASSOC);
    foreach($cols as $c) {
        echo $c['Field'] . " (" . $c['Type'] . ")\n";
    }
    
    echo "\n--- SAMPLE DATA (Last 5) ---\n";
    $stmt = $conn->query("SELECT * FROM renewal_log ORDER BY id DESC LIMIT 5");
    $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
    print_r($data);
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
?>
