<?php
header('Content-Type: application/json');
$host = "127.0.0.1";
$pass = "S@kenterprises6198";
$db_slm = "u182809524_slm"; 
$user_slm = "u182809524_slm";

try {
    $conn = new PDO("mysql:host=$host;dbname=$db_slm;charset=utf8", $user_slm, $pass);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    $tables = $conn->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
    $report = [];
    
    foreach ($tables as $table) {
        $st = $conn->query("DESCRIBE `$table` ");
        $report[$table] = $st->fetchAll(PDO::FETCH_ASSOC);
    }
    
    echo json_encode(['success' => true, 'tables' => $report]);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
