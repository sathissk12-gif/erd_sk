<?php
/**
 * AI Chat - Report Export API (CSV / PDF)
 */
include 'db_connect.php';
date_default_timezone_set('Asia/Kolkata');

$action = $_REQUEST['action'] ?? '';
$format = $_REQUEST['format'] ?? 'csv';
$type = $_REQUEST['type'] ?? 'sales';

if ($action === 'download') {
    $startDate = $_REQUEST['start_date'] ?? date('Y-m-01');
    $endDate = $_REQUEST['end_date'] ?? date('Y-m-d');
    $query = $_REQUEST['query'] ?? '';

    $data = [];
    $filename = "SK_Report_$type";

    try {
        if ($query) {
            // AI-generated custom query - direct SQL export
            $stmt = $conn->prepare("SELECT * FROM ($query) AS export_data");
            $stmt->execute();
            $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
            $filename = "SK_AI_Custom";
        } elseif ($type === 'sales') {
            $stmt = $conn->prepare("SELECT invoice_no AS 'Invoice No', invoice_date AS 'Date', customer_name AS 'Customer', mobile_number AS 'Mobile', vehicle_no AS 'Vehicle', imei AS 'IMEI', software AS 'Software', total_amount AS 'Total', paid_amount AS 'Paid', status AS 'Status' FROM invoice_log WHERE invoice_date BETWEEN ? AND ? ORDER BY invoice_date DESC");
            $stmt->execute([$startDate, $endDate]);
            $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
            $filename = "SK_Sales_$startDate-$endDate";
        } elseif ($type === 'renewal') {
            $stmt = $conn->prepare("SELECT id AS 'ID', vehicle_no AS 'Vehicle', customer_name AS 'Customer', imei AS 'IMEI', amount AS 'Amount', status AS 'Status', valid_to AS 'Valid Till', mobile_no AS 'Mobile' FROM renewal_log WHERE (date BETWEEN ? AND ?) OR (valid_to BETWEEN ? AND ?) ORDER BY valid_to ASC");
            $stmt->execute([$startDate, $endDate, $startDate, $endDate]);
            $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
            $filename = "SK_Renewals_$startDate-$endDate";
        } elseif ($type === 'stock') {
            $data = $conn->query("SELECT imei AS 'IMEI', device_model AS 'Model', supplier_name AS 'Supplier', status AS 'Status', rate AS 'Rate', date AS 'Date' FROM device_master ORDER BY device_model ASC")->fetchAll(PDO::FETCH_ASSOC);
            $filename = "SK_Stock";
        } elseif ($type === 'dealers') {
            $data = $conn->query("SELECT holder AS 'Dealer', COUNT(*) AS 'Devices Sold' FROM device_master WHERE status = 'SOLD' AND holder IS NOT NULL AND holder != '' AND holder != 'OFFICE' GROUP BY holder ORDER BY COUNT(*) DESC")->fetchAll(PDO::FETCH_ASSOC);
            $filename = "SK_Dealers";
        } elseif ($type === 'profit') {
            $stmt = $conn->prepare("SELECT invoice_no AS 'Invoice No', invoice_date AS 'Date', customer_name AS 'Customer', vehicle_no AS 'Vehicle', imei AS 'IMEI', total_amount AS 'Total', paid_amount AS 'Paid', profit AS 'Profit' FROM invoice_log WHERE invoice_date BETWEEN ? AND ? ORDER BY invoice_date DESC");
            $stmt->execute([$startDate, $endDate]);
            $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
            $filename = "SK_Profit_$startDate-$endDate";
        } elseif ($type === 'customers') {
            $data = $conn->query("SELECT name AS 'Name', mobile AS 'Mobile', location AS 'Location' FROM customerdatas ORDER BY name ASC")->fetchAll(PDO::FETCH_ASSOC);
            $filename = "SK_Customers";
        }

        if (empty($data)) {
            die("No data found for the selected report.");
        }

        if ($format === 'csv') {
            header('Content-Type: text/csv; charset=utf-8');
            header('Content-Disposition: attachment; filename="' . $filename . '.csv"');
            header('Pragma: no-cache');
            
            $output = fopen('php://output', 'w');
            // BOM for Excel UTF-8
            fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));
            // Headers
            fputcsv($output, array_keys($data[0]));
            // Data
            foreach ($data as $row) {
                fputcsv($output, $row);
            }
            fclose($output);
            exit;

        } elseif ($format === 'pdf') {
            // Simple HTML-to-PDF using HTML table
            header('Content-Type: text/html; charset=utf-8');
            
            $html = '<!DOCTYPE html><html><head><meta charset="UTF-8"><title>' . $filename . '</title>';
            $html .= '<style>
                body { font-family: Arial, sans-serif; font-size: 12px; margin: 20px; }
                h2 { color: #8b5cf6; text-align: center; }
                table { width: 100%; border-collapse: collapse; margin-top: 15px; }
                th { background: #8b5cf6; color: white; padding: 8px; text-align: left; font-size: 11px; }
                td { padding: 6px 8px; border-bottom: 1px solid #ddd; font-size: 11px; }
                tr:nth-child(even) { background: #f9f9f9; }
                .footer { text-align: center; margin-top: 20px; font-size: 10px; color: #888; }
            </style></head><body>';
            $html .= '<h2>SK Enterprise - ' . htmlspecialchars(ucfirst($type)) . ' Report</h2>';
            $html .= '<p>Period: ' . htmlspecialchars($startDate) . ' to ' . htmlspecialchars($endDate) . ' | Generated: ' . date('d-m-Y H:i') . '</p>';
            $html .= '<table><thead><tr>';
            foreach (array_keys($data[0]) as $header) {
                $html .= '<th>' . htmlspecialchars($header) . '</th>';
            }
            $html .= '</tr></thead><tbody>';
            foreach ($data as $row) {
                $html .= '<tr>';
                foreach ($row as $cell) {
                    $html .= '<td>' . htmlspecialchars($cell ?? '') . '</td>';
                }
                $html .= '</tr>';
            }
            $html .= '</tbody></table>';
            $html .= '<div class="footer">SK Enterprise AI Report | Total Records: ' . count($data) . '</div>';
            $html .= '</body></html>';
            
            echo $html;
            exit;
        }

    } catch (Exception $e) {
        die("Error: " . $e->getMessage());
    }
}
?>
