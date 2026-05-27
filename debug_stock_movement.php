<?php
require_once 'db_connect.php';

$softwareName = $_GET['software'] ?? 'TRACK IN';

echo "<h2>Checking stock_ledger for: " . htmlspecialchars($softwareName) . "</h2>";

// 1. Check stock_ledger columns
echo "<h3>1. stock_ledger columns:</h3>";
$stmt = $conn->query("DESCRIBE stock_ledger");
echo "<table border='1'><tr><th>Field</th><th>Type</th></tr>";
while($r = $stmt->fetch(PDO::FETCH_ASSOC)) {
    echo "<tr><td>{$r['Field']}</td><td>{$r['Type']}</td></tr>";
}
echo "</table>";

// 2. Check stock_ledger data for this software
echo "<h3>2. stock_ledger data for '{$softwareName}':</h3>";
$ledgerCols = $conn->query("DESCRIBE stock_ledger")->fetchAll(PDO::FETCH_COLUMN);
$selCols = ['date', 'qty', 'item_type'];
if (in_array('remark', $ledgerCols)) $selCols[] = 'remark';
if (in_array('reference', $ledgerCols)) $selCols[] = 'reference';
if (in_array('vehicle_no', $ledgerCols)) $selCols[] = 'vehicle_no';
$selList = implode(', ', $selCols);
$orderCol = in_array('id', $ledgerCols) ? 'id' : 'date';

echo "SELECT $selList FROM stock_ledger WHERE item_name = ? ORDER BY $orderCol DESC LIMIT 50<br><br>";

$stmt = $conn->prepare("SELECT $selList FROM stock_ledger WHERE item_name = ? ORDER BY $orderCol DESC LIMIT 50");
$stmt->execute([$softwareName]);
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo count($rows) . " rows found<br>";
echo "<table border='1'><tr>";
foreach($selCols as $c) echo "<th>" . htmlspecialchars($c) . "</th>";
echo "</tr>";
foreach($rows as $r) {
    echo "<tr>";
    foreach($selCols as $c) echo "<td>" . htmlspecialchars($r[$c] ?? '—') . "</td>";
    echo "</tr>";
}
echo "</table>";

// 3. Check invoice_log for this software
echo "<h3>3. invoice_log data for '{$softwareName}':</h3>";
$stmt = $conn->prepare("SELECT invoice_no, invoice_date, customer_name, vehicle_no, total_amount, paid_amount FROM invoice_log WHERE software = ? ORDER BY invoice_date DESC LIMIT 20");
$stmt->execute([$softwareName]);
$sales = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo count($sales) . " rows found<br>";
echo "<table border='1'><tr><th>invoice_no</th><th>invoice_date</th><th>customer_name</th><th>vehicle_no</th><th>total_amount</th><th>paid_amount</th></tr>";
foreach($sales as $s) {
    echo "<tr>";
    echo "<td>" . htmlspecialchars($s['invoice_no'] ?? '—') . "</td>";
    echo "<td>" . htmlspecialchars($s['invoice_date'] ?? '—') . "</td>";
    echo "<td>" . htmlspecialchars($s['customer_name'] ?? '—') . "</td>";
    echo "<td>" . htmlspecialchars($s['vehicle_no'] ?? '—') . "</td>";
    echo "<td>" . htmlspecialchars($s['total_amount'] ?? '—') . "</td>";
    echo "<td>" . htmlspecialchars($s['paid_amount'] ?? '—') . "</td>";
    echo "</tr>";
}
echo "</table>";

// 4. Check the actual API response
echo "<h3>4. API Response for get_software_sales_detail:</h3>";
$url = "http://" . ($_SERVER['HTTP_HOST'] ?? 'localhost') . dirname($_SERVER['SCRIPT_NAME']) . "/api_master_data.php?action=get_software_sales_detail&software_name=" . urlencode($softwareName);
echo "URL: " . htmlspecialchars($url) . "<br>";
$apiResp = file_get_contents($url);
$data = json_decode($apiResp, true);
if ($data) {
    echo "stock_movement count: " . count($data['stock_movement'] ?? []) . "<br>";
    echo "<pre>" . htmlspecialchars(json_encode($data['stock_movement'] ?? [], JSON_PRETTY_PRINT)) . "</pre>";
} else {
    echo "Failed to decode API response<br>";
    echo htmlspecialchars($apiResp);
}
?>
