<?php
include 'db_connect.php';
try {
    $stmt = $conn->query("SELECT * FROM software_master LIMIT 5");
    $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode($data);
} catch (Exception $e) {
    echo $e->getMessage();
}
?>
