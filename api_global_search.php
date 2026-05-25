<?php
/**
 * Global Omni-Search API
 * Searches across multiple modules for unified data retrieval
 */
header('Content-Type: application/json');
include 'db_connect.php';

$q = trim($_GET['q'] ?? '');
if (!$q) {
    echo json_encode(['results' => []]);
    exit;
}

$results = [];
$likeQ = "%$q%";

try {
    // 1. Search Sales (Vehicle, Customer, IMEI)
    $stmtSales = $conn->prepare("SELECT vehicle_no, customer_name, imei, 'SALE' as type FROM sales_log 
                                WHERE vehicle_no LIKE ? OR customer_name LIKE ? OR imei LIKE ? 
                                ORDER BY id DESC LIMIT 5");
    $stmtSales->execute([$likeQ, $likeQ, $likeQ]);
    $sales = $stmtSales->fetchAll(PDO::FETCH_ASSOC);
    foreach($sales as $s) {
        $results[] = [
            'title' => $s['vehicle_no'],
            'subtitle' => $s['customer_name'] . " | " . $s['imei'],
            'type' => 'Sale Record',
            'link' => 'sales_invoice_search.php?query=' . urlencode($s['vehicle_no']),
            'icon' => 'fa-car'
        ];
    }

    // 2. Search Invoices
    $stmtInv = $conn->prepare("SELECT invoice_no, customer_name, vehicle_no FROM invoice_log 
                              WHERE invoice_no LIKE ? OR vehicle_no LIKE ? 
                              ORDER BY id DESC LIMIT 5");
    $stmtInv->execute([$likeQ, $likeQ]);
    $invs = $stmtInv->fetchAll(PDO::FETCH_ASSOC);
    foreach($invs as $i) {
        $results[] = [
            'title' => "Invoice: " . $i['invoice_no'],
            'subtitle' => $i['customer_name'] . " (" . $i['vehicle_no'] . ")",
            'type' => 'Billing',
            'link' => 'sales_invoice.php?invoice_no=' . urlencode($i['invoice_no']),
            'icon' => 'fa-file-invoice-dollar'
        ];
    }

    // 3. Search Renewals (Vehicle, Customer)
    $stmtRen = $conn->prepare("SELECT vehicle_no, vehicle, customer_name, customer FROM renewal_log 
                              WHERE vehicle_no LIKE ? OR vehicle LIKE ? OR customer_name LIKE ? OR customer LIKE ?
                              ORDER BY id DESC LIMIT 5");
    $stmtRen->execute([$likeQ, $likeQ, $likeQ, $likeQ]);
    $rens = $stmtRen->fetchAll(PDO::FETCH_ASSOC);
    foreach($rens as $r) {
        $vno = $r['vehicle_no'] ?? $r['vehicle'] ?? "Unknown";
        $results[] = [
            'title' => "Renewal: " . $vno,
            'subtitle' => $r['customer_name'] ?? $r['customer'] ?? "",
            'type' => 'Subscription',
            'link' => 'renewal_entry.php?vehicle=' . urlencode($vno),
            'icon' => 'fa-rotate'
        ];
    }

    // 4. Search Renewal Invoices
    $stmtRenInv = $conn->prepare("SELECT invoice_num, invoice_no, customer_name, vehicle_num, vehicle_no FROM renewal_invoice_log 
                              WHERE invoice_num LIKE ? OR invoice_no LIKE ? OR vehicle_num LIKE ? OR vehicle_no LIKE ?
                              ORDER BY id DESC LIMIT 5");
    $stmtRenInv->execute([$likeQ, $likeQ, $likeQ, $likeQ]);
    $renInvs = $stmtRenInv->fetchAll(PDO::FETCH_ASSOC);
    foreach($renInvs as $ri) {
        $inv = $ri['invoice_num'] ?? $ri['invoice_no'] ?? "";
        $vno = $ri['vehicle_num'] ?? $ri['vehicle_no'] ?? "";
        $results[] = [
            'title' => "Renewal Bill: " . $inv,
            'subtitle' => ($ri['customer_name'] ?? "") . " (" . $vno . ")",
            'type' => 'Billing',
            'link' => 'renewal_invoice.php?invoice_no=' . urlencode($inv),
            'icon' => 'fa-file-invoice'
        ];
    }

    echo json_encode(['results' => $results]);

} catch (Exception $e) {
    echo json_encode(['error' => $e->getMessage(), 'results' => []]);
}
