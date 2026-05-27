<?php
include 'db_connect.php';
header('Content-Type: application/json');

$action = $_REQUEST['action'] ?? '';

switch ($action) {
    case 'get_data':
        try {
            $type = $_GET['type'] ?? 'sales';
            $startDate = $_GET['start_date'] ?? date('Y-m-01');
            $endDate = $_GET['end_date'] ?? date('Y-m-d');

            $data = [];
            
            if ($type === 'sales') {
                $stmt = $conn->prepare("SELECT invoice_no, invoice_date as date, customer_name, mobile_number, vehicle_no, imei, software, total_amount, paid_amount FROM invoice_log WHERE invoice_date BETWEEN ? AND ? ORDER BY invoice_date DESC");
                $stmt->execute([$startDate, $endDate]);
                $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
            }
            elseif ($type === 'renewal') {
                $stmt = $conn->prepare("SELECT * FROM renewal_log WHERE date BETWEEN ? AND ? OR valid_to BETWEEN ? AND ? ORDER BY valid_to ASC");
                $stmt->execute([$startDate, $endDate, $startDate, $endDate]);
                $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
            }
            elseif ($type === 'device_stock') {
                $stmt = $conn->query("SELECT imei, device_model, supplier_name, status, date as purchase_date FROM device_master ORDER BY device_model ASC");
                $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
            }
            elseif ($type === 'software_stock') {
                $stmt = $conn->query("SELECT item_name, item_type, SUM(CAST(qty AS SIGNED)) as current_stock FROM stock_ledger GROUP BY item_name, item_type");
                $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
            }
            elseif ($type === 'device_sales') {
                $stmt = $conn->prepare("SELECT sl_no, imei, device_model, supplier_name, status, date as purchase_date FROM device_master WHERE status = 'Sold' AND date BETWEEN ? AND ? ORDER BY date DESC");
                $stmt->execute([$startDate, $endDate]);
                $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
            }
            // === NEW: Software Sales Summary ===
            elseif ($type === 'software_sales_summary') {
                $stmt = $conn->prepare("
                    SELECT
                        software AS 'Software Name',
                        COUNT(*) AS 'Sales Count',
                        SUM(total_amount) AS 'Total Amount',
                        SUM(paid_amount) AS 'Received Amount',
                        SUM(total_amount - paid_amount) AS 'Pending Amount',
                        MIN(invoice_date) AS 'First Sale Date',
                        MAX(invoice_date) AS 'Last Sale Date'
                    FROM invoice_log
                    WHERE invoice_date BETWEEN ? AND ? AND software IS NOT NULL AND software != ''
                    GROUP BY software
                    ORDER BY COUNT(*) DESC
                ");
                $stmt->execute([$startDate, $endDate]);
                $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
            }
            // === NEW: Software Renewal Summary ===
            elseif ($type === 'software_renewal_summary') {
                $stmt = $conn->prepare("
                    SELECT
                        COALESCE(software, software_type, 'N/A') AS 'Software Name',
                        COUNT(*) AS 'Renewal Count',
                        SUM(amount) AS 'Total Amount',
                        SUM(received_amount) AS 'Received Amount',
                        MIN(date) AS 'First Renewal Date',
                        MAX(date) AS 'Last Renewal Date'
                    FROM renewal_log
                    WHERE (date BETWEEN ? AND ?) AND software IS NOT NULL AND software != ''
                    GROUP BY COALESCE(software, software_type, 'N/A')
                    ORDER BY COUNT(*) DESC
                ");
                $stmt->execute([$startDate, $endDate]);
                $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
            }
            // === NEW: Combined Software Performance (Sales + Renewals) ===
            elseif ($type === 'software_combined') {
                // Sales by software
                $stmtSales = $conn->prepare("
                    SELECT
                        software AS software_name,
                        COUNT(*) AS sale_count,
                        SUM(paid_amount) AS sale_received
                    FROM invoice_log
                    WHERE invoice_date BETWEEN ? AND ? AND software IS NOT NULL AND software != ''
                    GROUP BY software
                ");
                $stmtSales->execute([$startDate, $endDate]);
                $salesData = [];
                foreach ($stmtSales->fetchAll(PDO::FETCH_ASSOC) as $r) {
                    $salesData[$r['software_name']] = $r;
                }

                // Renewals by software
                $stmtRenew = $conn->prepare("
                    SELECT
                        COALESCE(software, software_type, 'N/A') AS software_name,
                        COUNT(*) AS renewal_count,
                        SUM(received_amount) AS renewal_received
                    FROM renewal_log
                    WHERE (date BETWEEN ? AND ?) AND software IS NOT NULL AND software != ''
                    GROUP BY COALESCE(software, software_type, 'N/A')
                ");
                $stmtRenew->execute([$startDate, $endDate]);
                $renewData = [];
                foreach ($stmtRenew->fetchAll(PDO::FETCH_ASSOC) as $r) {
                    $renewData[$r['software_name']] = $r;
                }

                // Merge all software names
                $allSoftwares = array_unique(array_merge(array_keys($salesData), array_keys($renewData)));
                $data = [];
                foreach ($allSoftwares as $sw) {
                    $s = $salesData[$sw] ?? [];
                    $r = $renewData[$sw] ?? [];
                    $data[] = [
                        'Software Name' => $sw,
                        'Sales Count' => (int)($s['sale_count'] ?? 0),
                        'Sales Revenue' => (float)($s['sale_received'] ?? 0),
                        'Renewal Count' => (int)($r['renewal_count'] ?? 0),
                        'Renewal Revenue' => (float)($r['renewal_received'] ?? 0),
                        'Total Revenue' => (float)(($s['sale_received'] ?? 0) + ($r['renewal_received'] ?? 0))
                    ];
                }
                // Sort by total revenue descending
                usort($data, function($a, $b) { return $b['Total Revenue'] <=> $a['Total Revenue']; });
            }

            echo json_encode($data);
        } catch (Exception $e) {
            echo json_encode(['error' => $e->getMessage()]);
        }
        break;
}
?>
