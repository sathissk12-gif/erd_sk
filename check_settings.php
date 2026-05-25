<?php
include 'db_connect.php';
try {
    $stmt = $conn->query("DESCRIBE settings");
    $cols = $stmt->fetchAll(PDO::FETCH_ASSOC);
    print_r($cols);
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
?>
