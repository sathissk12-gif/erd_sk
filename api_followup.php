<?php
include 'db_connect.php';
header('Content-Type: application/json');

// 🛡️ Auto-Table Creation (Self-Fix)
try {
    $conn->query("SELECT 1 FROM payment_followups LIMIT 1");
} catch (Exception $e) {
    $conn->exec("CREATE TABLE IF NOT EXISTS payment_followups (
        id INT AUTO_INCREMENT PRIMARY KEY,
        uid VARCHAR(50) UNIQUE,
        customer_name VARCHAR(100) NOT NULL,
        mobile_no VARCHAR(20) NOT NULL,
        vehicle_no VARCHAR(20),
        software VARCHAR(100),
        amount_due DECIMAL(10, 2) DEFAULT 0,
        followup_date DATE,
        remark TEXT,
        status ENUM('PENDING', 'PAID') DEFAULT 'PENDING',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )");
}

$action = $_REQUEST['action'] ?? '';

switch ($action) {
    case 'save':
        try {
            $uid = $_POST['uid'] ?: substr(md5(uniqid(mt_rand(), true)), 0, 12);
            $name = trim($_POST['customer_name']);
            $mobile = trim($_POST['mobile_no']);
            $vehicle = strtoupper(trim($_POST['vehicle_no']));
            $software = trim($_POST['software']);
            $amount = (float)$_POST['amount_due'];
            $date = $_POST['followup_date'];
            $remark = trim($_POST['remark']);
            $status = $_POST['status'] ?? 'PENDING';

            if (!$name || !$mobile) throw new Exception("Customer Name and Mobile are required");

            $sql = "INSERT INTO payment_followups (uid, customer_name, mobile_no, vehicle_no, software, amount_due, followup_date, remark, status) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
                    ON DUPLICATE KEY UPDATE 
                    customer_name=VALUES(customer_name), mobile_no=VALUES(mobile_no), vehicle_no=VALUES(vehicle_no), 
                    software=VALUES(software), amount_due=VALUES(amount_due), followup_date=VALUES(followup_date), 
                    remark=VALUES(remark), status=VALUES(status)";
            
            $stmt = $conn->prepare($sql);
            $stmt->execute([$uid, $name, $mobile, $vehicle, $software, $amount, $date, $remark, $status]);

            echo json_encode(['success' => true, 'message' => 'Follow-up saved successfully']);
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
        break;

    case 'list':
        try {
            $filter = $_GET['filter'] ?? 'all';
            $sql = "SELECT * FROM payment_followups ";
            if ($filter === 'today') {
                $sql .= " WHERE followup_date = CURDATE() AND status = 'PENDING' ";
            } elseif ($filter === 'pending') {
                $sql .= " WHERE status = 'PENDING' ";
            }
            $sql .= " ORDER BY followup_date ASC";
            
            $stmt = $conn->query($sql);
            echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
        } catch (Exception $e) {
            echo json_encode([]);
        }
        break;

    case 'search_customer':
        try {
            $q = trim($_GET['query'] ?? '');
            $sql = "SELECT DISTINCT name as customer_name, mobile as mobile_no, location FROM customerdatas 
                    WHERE name LIKE ? OR mobile LIKE ? LIMIT 10";
            $stmt = $conn->prepare($sql);
            $stmt->execute(["%$q%", "%$q%"]);
            echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
        } catch (Exception $e) {
            echo json_encode([]);
        }
        break;

    case 'delete':
        try {
            $uid = $_POST['uid'];
            $stmt = $conn->prepare("DELETE FROM payment_followups WHERE uid = ?");
            $stmt->execute([$uid]);
            echo json_encode(['success' => true]);
        } catch (Exception $e) {
            echo json_encode(['success' => false]);
        }
        break;
}
?>
