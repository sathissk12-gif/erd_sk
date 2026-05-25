<?php
// api_appointments.php
require_once 'db_connect.php';
header('Content-Type: application/json');

$action = $_REQUEST['action'] ?? '';

// 🛡️ Auto-healing: Ensure table exists
$conn->exec("CREATE TABLE IF NOT EXISTS appointment_log (
    id INT AUTO_INCREMENT PRIMARY KEY,
    customer_name VARCHAR(255) NOT NULL,
    mobile_number VARCHAR(20),
    vehicle_no VARCHAR(50),
    appointment_date DATE NOT NULL,
    appointment_time TIME NOT NULL,
    purpose TEXT,
    status ENUM('Pending', 'Completed', 'Cancelled') DEFAULT 'Pending',
    reminder_sent TINYINT(1) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)");

switch ($action) {
    case 'save':
        try {
            $name = $_POST['customer_name'] ?? '';
            $mobile = $_POST['mobile_number'] ?? '';
            $vehicle = $_POST['vehicle_no'] ?? '';
            $date = $_POST['appointment_date'] ?? '';
            $time = $_POST['appointment_time'] ?? '';
            $purpose = $_POST['purpose'] ?? '';

            if (!$name || !$date || !$time) {
                echo json_encode(['success' => false, 'message' => 'Missing required fields']);
                exit;
            }

            $stmt = $conn->prepare("INSERT INTO appointment_log (customer_name, mobile_number, vehicle_no, appointment_date, appointment_time, purpose) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->execute([$name, $mobile, $vehicle, $date, $time, $purpose]);

            echo json_encode(['success' => true, 'message' => 'Appointment saved']);
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
        break;

    case 'list':
        try {
            $stmt = $conn->query("SELECT * FROM appointment_log WHERE appointment_date >= CURDATE() ORDER BY appointment_date ASC, appointment_time ASC LIMIT 100");
            $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
            echo json_encode($data);
        } catch (Exception $e) {
            echo json_encode([]);
        }
        break;

    case 'update_status':
        try {
            $id = $_POST['id'] ?? '';
            $status = $_POST['status'] ?? 'Completed';
            $stmt = $conn->prepare("UPDATE appointment_log SET status = ? WHERE id = ?");
            $stmt->execute([$status, $id]);
            echo json_encode(['success' => true]);
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
        break;
}
?>
