<?php
/**
 * Dedicated API for Renewal Invoices
 * Reads strictly from renewal_invoice_log
 */
error_reporting(0);
include 'db_connect.php';
header('Content-Type: application/json');

$action = (isset($_REQUEST['action'])) ? strtolower($_REQUEST['action']) : "";

function getAmountInWords($number) {
    if ($number == 0) return "Zero";
    $decimal = round($number - ($no = floor($number)), 2) * 100;
    $hundred = null;
    $digits_length = strlen($no);
    $i = 0;
    $str = array();
    $words = array(0 => '', 1 => 'One', 2 => 'Two',
        3 => 'Three', 4 => 'Four', 5 => 'Five', 6 => 'Six',
        7 => 'Seven', 8 => 'Eight', 9 => 'Nine',
        10 => 'Ten', 11 => 'Eleven', 12 => 'Twelve',
        13 => 'Thirteen', 14 => 'Fourteen', 15 => 'Fifteen',
        16 => 'Sixteen', 17 => 'Seventeen', 18 => 'Eighteen',
        19 => 'Nineteen', 20 => 'Twenty',
        30 => 'Thirty', 40 => 'Forty', 50 => 'Fifty',
        60 => 'Sixty', 70 => 'Seventy',
        80 => 'Eighty', 90 => 'Ninety');
    $digits = array('', 'Hundred','Thousand','Lakh', 'Crore');
    while( $i < $digits_length ) {
        $divider = ($i == 2) ? 10 : 100;
        $number = floor($no % $divider);
        $no = floor($no / $divider);
        $i += $divider == 10 ? 1 : 2;
        if ($number) {
            $plural = (($counter = count($str)) && $number > 9) ? 's' : null;
            $hundred = ($counter == 1 && $str[0]) ? ' and ' : null;
            $str [] = ($number < 21) ? $words[$number].' '. $digits[$counter]. $plural.' '.$hundred:$words[floor($number / 10) * 10].' '.$words[$number % 10]. ' '.$digits[$counter].$plural.' '.$hundred;
        } else $str[] = null;
    }
    $Rupees = implode('', array_reverse($str));
    $paise = ($decimal > 0) ? " and " . ($words[$decimal / 10] . " " . $words[$decimal % 10]) . ' Paise' : '';
    return ($Rupees ? $Rupees . 'Rupees ' : '') . $paise;
}

if ($action === 'invoice-data') {
    $uid = $_REQUEST['uid'] ?? "";
    $invoiceNo = $_REQUEST['invoice_no'] ?? "";
    $vehicle = $_REQUEST['vehicle'] ?? "";
    $id = $_REQUEST['id'] ?? "";

    try {
        $row = null;
        if ($id) {
            $stmt = $conn->prepare("SELECT * FROM renewal_invoice_log WHERE id = ?");
            $stmt->execute([$id]);
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
        }
        if (!$row && $uid) {
            $stmt = $conn->prepare("SELECT * FROM renewal_invoice_log WHERE uid = ?");
            $stmt->execute([$uid]);
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
        }
        if (!$row && $invoiceNo) {
            $stmt = $conn->prepare("SELECT * FROM renewal_invoice_log WHERE invoice_num LIKE ? ORDER BY id DESC LIMIT 1");
            $stmt->execute(['%' . $invoiceNo . '%']);
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
        }
        if (!$row && $vehicle) {
            $stmt = $conn->prepare("SELECT * FROM renewal_invoice_log WHERE vehicle_num LIKE ? OR vehicle_no LIKE ? ORDER BY id DESC LIMIT 1");
            $stmt->execute(['%' . $vehicle . '%', '%' . $vehicle . '%']);
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
        }

        if (!$row) throw new Exception("No Invoice Record found for " . ($uid ?: $invoiceNo ?: $vehicle));

        // Normalize Casing
        $r = [];
        foreach($row as $k=>$v) { $r[strtolower($k)] = $v; }

        $mappedData = [
            'uid' => $r['uid'] ?? "",
            'invoiceNo' => $r['invoice_num'] ?? $r['invoice_no'] ?? "-",
            'invoiceDate' => $r['date'] ?? "",
            'customerName' => $r['customer_name'] ?? $r['customer'] ?? "-",
            'customerMobile' => $r['mobile_no'] ?? $r['mobile'] ?? "",
            'vehicleNumber' => $r['vehicle_num'] ?? $r['vehicle_no'] ?? $r['vehicle'] ?? "-",
            'softwareType' => $r['software_type'] ?? $r['software'] ?? "-",
            'amount' => $r['amount'] ?? 0,
            'receivedAmount' => $r['received_amount'] ?? 0,
            'amountWords' => $r['amount_words'] ?? getAmountInWords($r['received_amount'] ?? 0)
        ];

        // Fetch Settings
        $settingsRaw = $conn->query("SELECT * FROM settings LIMIT 1")->fetch(PDO::FETCH_ASSOC);
        $settings = [];
        if ($settingsRaw) {
            foreach($settingsRaw as $k => $v) { $settings[strtolower($k)] = $v; }
        }
        $mappedData['settings'] = $settings;

        echo json_encode(['success' => true, 'data' => $mappedData]);
    } catch (Exception $e) { echo json_encode(['success' => false, 'message' => $e->getMessage()]); }
    exit;
}

if ($action === 'search') {
    $q = trim($_REQUEST['query'] ?? "");
    try {
        $stmt = $conn->prepare("SELECT * FROM renewal_invoice_log WHERE vehicle_num LIKE ? OR customer_name LIKE ? OR invoice_num LIKE ? ORDER BY date DESC LIMIT 100");
        $stmt->execute(["%$q%", "%$q%", "%$q%"]);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $normalized = [];
        foreach($rows as $row) {
            $item = [];
            foreach($row as $k=>$v) { $item[strtolower($k)] = $v; }
            $item['vehicle_num'] = $item['vehicle_num'] ?? $item['vehicle_no'] ?? $item['vehicle'] ?? "";
            $item['invoice_num'] = $item['invoice_num'] ?? $item['invoice_no'] ?? "";
            $item['customer_name'] = $item['customer_name'] ?? $item['customer'] ?? "";
            $item['date'] = $item['date'] ?? "";
            $normalized[] = $item;
        }
        echo json_encode($normalized);
    } catch (Exception $e) { echo json_encode([]); }
    exit;
}
?>
