<?php
require_once 'db_connect.php';

try {
    $stmt = $conn->query("SHOW TABLES");
    $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
    echo "Tables in database:\n";
    print_r($tables);
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}
?>
