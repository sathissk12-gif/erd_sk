<?php
/**
 * Advanced Business Intelligence API v3.0
 * Multi-module reporting with cross-table search and time analytics
 */
header('Content-Type: application/json');
include 'db_connect.php';

$action = $_GET['action'] ?? '';

switch ($action) {
    case 'kpi':
        get_kpi($conn);
        break;
    case 'pending_renewals_month':
        get_pending_renewals_month($conn);
        break;
    case 'update_pending_renewal_status':
        update_pending_renewal_status($conn);
        break;
    case 'detailed_search':
        handle_detailed_search($conn);
        break;
    case 'dealer_performance':
        handle_dealer_performance($conn);
        break;
    case 'time_analytics':
        handle_time_analytics($conn);
        break;
    case 'sales_trend':
        get_sales_trend($conn);
        break;
    case 'software_stats':
        get_software_stats($conn);
        break;
    case 'payment_stats':
        get_payment_stats($conn);
        break;
    case 'expense_log':
        get_expense_log($conn);
        break;
    case 'add_expense':
        handle_add_expense($conn);
        break;
    default:
        echo json_encode(['success' => true, 'message' => 'REPORTS API ACTIVE']);
}

/**
 * 🔍 Advanced IMEI/Vehicle Search
 * Cross-links device_master with sales_log to show full lifecycle
 */
function handle_detailed_search($conn) {
    $q = trim($_GET['q'] ?? '');
    if (!$q) { echo json_encode([]); exit; }

    try {
        // Search in both sales_log and device_master
        $sql = "SELECT d.imei, d.device_model as model, d.holder, d.date as purchase_date, d.sold_date as office_sale_date,
                       s.vehicle_no, s.customer_name, s.sale_date, s.location, s.selling_price
                FROM device_master d
                LEFT JOIN sales_log s ON d.imei = s.imei
                WHERE d.imei LIKE ? OR s.vehicle_no LIKE ?
                ORDER BY s.id DESC LIMIT 50";
        
        $stmt = $conn->prepare($sql);
        $stmt->execute(["%$q%", "%$q%"]);
        echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
    } catch (Exception $e) { echo json_encode(['error' => $e->getMessage()]); }
}

/**
 * 📦 Dealer Inventory & Sale Report
 */
function handle_dealer_performance($conn) {
    $dealer = trim($_GET['dealer'] ?? '');
    if (!$dealer) { echo json_encode([]); exit; }

    try {
        $sql = "SELECT d.imei, d.device_model as model, d.status, d.issue_date,
                       s.vehicle_no, s.customer_name, s.sale_date, s.selling_price
                FROM device_master d
                LEFT JOIN sales_log s ON d.imei = s.imei
                WHERE d.holder = ?
                ORDER BY d.issue_date DESC";
        
        $stmt = $conn->prepare($sql);
        $stmt->execute([$dealer]);
        echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
    } catch (Exception $e) { echo json_encode([]); }
}

/**
 * 📊 Advanced Time-based Analytics
 * Today, Weekly, Monthly, Yearly Comparison
 */
function handle_time_analytics($conn) {
    try {
        $today = date('Y-m-d');
        $weekStart = date('Y-m-d', strtotime('monday this week'));
        $monthStart = date('Y-m-01');
        $yearStart = date('Y-01-01');

        $stats = [];
        $periods = [
            'Today' => "sale_date = '$today'",
            'Week' => "sale_date >= '$weekStart'",
            'Month' => "sale_date >= '$monthStart'",
            'Year' => "sale_date >= '$yearStart'"
        ];

        foreach ($periods as $label => $where) {
            $stmt = $conn->query("SELECT COUNT(*) as count, SUM(received_amount) as sales, SUM(profit) as profit FROM sales_log WHERE $where");
            $res = $stmt->fetch(PDO::FETCH_ASSOC);
            $stats[$label] = [
                'count' => (int)$res['count'],
                'sales' => (float)$res['sales'],
                'profit' => (float)$res['profit']
            ];
        }

        echo json_encode($stats);
    } catch (Exception $e) { echo json_encode([]); }
}

function get_kpi($conn) {
    try {
        $monthStart = date('Y-m-01');
        $today = date('Y-m-d');
        
        $monthly = $conn->query("SELECT SUM(received_amount) as sales, SUM(profit) as profit FROM sales_log WHERE sale_date >= '$monthStart'")->fetch(PDO::FETCH_ASSOC);
        $todaySales = $conn->query("SELECT SUM(received_amount) FROM sales_log WHERE sale_date = '$today'")->fetchColumn();
        
        // Total Expenses for current month (Handle missing table gracefully)
        $monthlyExpenses = 0;
        try {
            $monthlyExpenses = $conn->query("SELECT SUM(amount) FROM expenses WHERE date >= '$monthStart'")->fetchColumn();
        } catch(Exception $e) { /* Table may not exist yet */ }
        
        $netProfit = (float)$monthly['profit'] - (float)$monthlyExpenses;

        $simPending = 0; 
        $officePendingItems = $conn->query("SELECT COUNT(*) FROM sales_log WHERE office_settled IS NULL OR office_settled != 'DONE'")->fetchColumn();
        $monthlyRenewals = $conn->query("SELECT COUNT(*) FROM renewal_log WHERE (status='YES' OR processed='YES') AND date >= '$monthStart'")->fetchColumn();

        echo json_encode([
            'todaySales' => (float)$todaySales,
            'monthlySales' => (float)$monthly['sales'],
            'monthlyProfit' => (float)$monthly['profit'],
            'monthlyExpenses' => (float)$monthlyExpenses,
            'netProfit' => $netProfit,
            'simPending' => (float)$simPending,
            'officePendingItems' => (int)$officePendingItems,
            'monthlyRenewals' => (int)$monthlyRenewals
        ]);
    } catch (Exception $e) { echo json_encode(['error' => $e->getMessage()]); }
}

function get_expense_log($conn) {
    try {
        $stmt = $conn->query("SELECT * FROM expenses ORDER BY date DESC LIMIT 50");
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $total = 0; $monthStart = date('Y-m-01');
        $total = $conn->query("SELECT SUM(amount) FROM expenses WHERE date >= '$monthStart'")->fetchColumn();
        echo json_encode(['rows' => $rows, 'total' => (float)$total]);
    } catch (Exception $e) { echo json_encode([]); }
}

function handle_add_expense($conn) {
    try {
        $cat = $_POST['category'] ?? '';
        $amt = (float)($_POST['amount'] ?? 0);
        $rem = $_POST['remark'] ?? '';
        $stmt = $conn->prepare("INSERT INTO expenses (date, category, amount, remark) VALUES (CURDATE(), ?, ?, ?)");
        $stmt->execute([$cat, $amt, $rem]);
        echo json_encode(['status' => 'ok']);
    } catch (Exception $e) { echo json_encode(['status' => 'error']); }
}

function get_sales_trend($conn) {
    $days = []; $data = [];
    for($i = 6; $i >= 0; $i--) {
        $date = date('Y-m-d', strtotime("-$i days"));
        $days[] = date('D', strtotime($date));
        $val = $conn->query("SELECT SUM(received_amount) FROM sales_log WHERE sale_date = '$date'")->fetchColumn();
        $data[] = (float)$val;
    }
    echo json_encode(['labels' => $days, 'data' => $data]);
}

function get_software_stats($conn) {
    $stmt = $conn->query("SELECT software as name, COUNT(*) as count FROM sales_log WHERE software IS NOT NULL GROUP BY software ORDER BY count DESC LIMIT 5");
    echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
}

function get_payment_stats($conn) {
    $stmt = $conn->query("SELECT payment_mode as name, COUNT(*) as count FROM sales_log WHERE payment_mode IS NOT NULL GROUP BY payment_mode ORDER BY count DESC");
    echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
}

function get_pending_renewals_month($conn) {
    $month = trim($_GET['month'] ?? date('Y-m'));
    if (!preg_match('/^\d{4}-\d{2}$/', $month)) {
        echo json_encode([]);
        return;
    }

    try {
        $start = $month . '-01';
        $end = date('Y-m-t', strtotime($start));
        $columns = $conn->query("DESCRIBE renewal_log")->fetchAll(PDO::FETCH_COLUMN);
        $dueDateExpr = "COALESCE(
                    NULLIF(valid_to, '0000-00-00'),
                    NULLIF(expiry_date, '0000-00-00'),
                    NULLIF(date, '0000-00-00')
                )";
        $mobileCandidates = ['mobile_no', 'mobile', 'm1', 'm2', 'm3', 'mobile1', 'mobile2', 'mobile3'];
        $mobileParts = [];
        foreach ($mobileCandidates as $col) {
            if (in_array($col, $columns, true)) {
                $mobileParts[] = "NULLIF(TRIM($col), '')";
            }
        }
        $mobileExpr = !empty($mobileParts) ? "COALESCE(" . implode(",\n                        ", $mobileParts) . ")" : "''";

        $statusExpr = "UPPER(REPLACE(TRIM(COALESCE(status, 'PENDING')), ' ', ''))";

        $sql = "SELECT
                    id,
                    COALESCE(NULLIF(TRIM(vehicle_no), ''), 'N/A') AS vehicle_no,
                    COALESCE(NULLIF(TRIM(customer_name), ''), 'Customer') AS customer_name,
                    $mobileExpr AS mobile_no,
                    CASE
                        WHEN $statusExpr IN ('PENDING', 'PENDIG', 'NO') THEN 'PENDING'
                        ELSE COALESCE(NULLIF(TRIM(status), ''), 'PENDING')
                    END AS status,
                    $dueDateExpr AS due_date,
                    COALESCE(NULLIF(TRIM(software), ''), '-') AS software,
                    COALESCE(NULLIF(TRIM(location), ''), '-') AS location
                FROM renewal_log
                WHERE $statusExpr IN ('PENDING', 'PENDIG')
                  AND $dueDateExpr BETWEEN ? AND ?
                ORDER BY $dueDateExpr ASC, id DESC";

        $stmt = $conn->prepare($sql);
        $stmt->execute([$start, $end]);
        echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
    } catch (Exception $e) {
        echo json_encode([]);
    }
}

function update_pending_renewal_status($conn) {
    $id = (int)($_POST['id'] ?? 0);
    $status = strtoupper(trim($_POST['status'] ?? ''));

    if ($id <= 0 || !in_array($status, ['PENDING', 'NO'], true)) {
        echo json_encode(['success' => false, 'message' => 'Invalid request']);
        return;
    }

    try {
        $stmt = $conn->prepare("UPDATE renewal_log SET status = ? WHERE id = ? LIMIT 1");
        $stmt->execute([$status, $id]);

        echo json_encode([
            'success' => true,
            'id' => $id,
            'status' => $status
        ]);
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => 'Update failed']);
    }
}
?>
