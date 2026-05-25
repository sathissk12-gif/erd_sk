<?php
/**
 * RECREATED RENEWAL API v4.2
 * Targeted for M1, M2, M3 column structure
 */
ob_start();
error_reporting(0);
include 'db_connect.php';
ob_clean();
header('Content-Type: application/json');

$action = isset($_REQUEST['action']) ? strtolower($_REQUEST['action']) : "";

function normalizeRenewalDate($val) {
    if ($val === null || $val === '') return null;
    $s = substr((string)$val, 0, 10);
    if ($s === '0000-00-00') return null;
    return $s;
}

function getAmountInWords($number) {
    if ($number == 0) return "Zero";
    $decimal = round($number - ($no = floor($number)), 2) * 100;
    $no = floor($no); $hundred = null; $digits_length = strlen($no); $i = 0; $str = array();
    $words = array(0 => '', 1 => 'One', 2 => 'Two', 3 => 'Three', 4 => 'Four', 5 => 'Five', 6 => 'Six', 7 => 'Seven', 8 => 'Eight', 9 => 'Nine', 10 => 'Ten', 11 => 'Eleven', 12 => 'Twelve', 13 => 'Thirteen', 14 => 'Fourteen', 15 => 'Fifteen', 16 => 'Sixteen', 17 => 'Seventeen', 18 => 'Eighteen', 19 => 'Nineteen', 20 => 'Twenty', 30 => 'Thirty', 40 => 'Forty', 50 => 'Fifty', 60 => 'Sixty', 70 => 'Seventy', 80 => 'Eighty', 90 => 'Ninety');
    $digits = array('', 'Hundred','Thousand','Lakh', 'Crore');
    while( $i < $digits_length ) {
        $divider = ($i == 2) ? 10 : 100;
        $number = floor($no % $divider); $no = floor($no / $divider); $i += $divider == 10 ? 1 : 2;
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

// Global Table Detection
$tables = $conn->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
$tableName = in_array('renewal_log', $tables) ? 'renewal_log' : (in_array('RENEWAL_LOG', $tables) ? 'RENEWAL_LOG' : 'renewal_log');

if ($action === 'softwarelist') {
    try {
        $sql = "SELECT DISTINCT name FROM (
                    SELECT name FROM price_master WHERE type = 'SOFTWARE'
                    UNION
                    SELECT item_name AS name FROM stock_ledger WHERE item_type = 'SOFTWARE'
                ) AS combined ORDER BY name ASC";
        $stmt = $conn->query($sql);
        echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
    } catch (Exception $e) {
        echo json_encode([]);
    }
    exit;
}

if ($action === 'history') {
    $vehicle = trim($_REQUEST['vehicle'] ?? '');
    try {
        $qCols = $conn->query("DESCRIBE `$tableName` ");
        $allCols = $qCols->fetchAll(PDO::FETCH_COLUMN);
        $searchCol = in_array('vehicle_no', $allCols) ? 'vehicle_no' : (in_array('vehicle', $allCols) ? 'vehicle' : 'vehicle_num');
        $dateCol = in_array('date', $allCols) ? 'date' : 'id';
        
        $stmt = $conn->prepare("SELECT * FROM `$tableName` WHERE `$searchCol` = ? ORDER BY id DESC LIMIT 10");
        $stmt->execute([$vehicle]);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        $history = [];
        foreach ($rows as $r) {
            $validTo = normalizeRenewalDate($r['valid_to'] ?? $r['expiry_date'] ?? null);
            $history[] = [
                'id' => $r['id'],
                'date' => $r['date'] ?? '—',
                'customer' => $r['customer_name'] ?? $r['customer'] ?? '—',
                'software' => $r['software'] ?? $r['software_type'] ?? '—',
                'amount' => $r['amount'] ?? 0,
                'status' => $r['status'] ?? 'PENDING',
                'valid_to' => $validTo,
                'm1' => $r['m1'] ?? ''
            ];
        }
        echo json_encode(['success' => true, 'history' => $history]);
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'history' => [], 'error' => $e->getMessage()]);
    }
    exit;
}

if ($action === 'search') {
    $v = trim($_REQUEST['vehicle'] ?? '');
    try {
        $qCols = $conn->query("DESCRIBE `$tableName` ");
        $allCols = $qCols->fetchAll(PDO::FETCH_COLUMN);
        $searchParts = []; $params = []; $lk = "%$v%";
        $possible = ['vehicle_no', 'vehicle', 'vehicle_num', 'imei', 'customer_name', 'customer', 'm1', 'm2', 'm3'];
        foreach ($possible as $c) { if (in_array($c, $allCols)) { $searchParts[] = "`$c` LIKE ?"; $params[] = $lk; } }

        $sql = "SELECT * FROM `$tableName` " . (!empty($searchParts) ? "WHERE (" . implode(' OR ', $searchParts) . ")" : "") . " ORDER BY id DESC LIMIT 1";
        $stmt = $conn->prepare($sql);
        $stmt->execute($params);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if($row) {
            $r = []; foreach($row as $k=>$val) { $r[strtolower($k)] = $val; }
            $validFrom = normalizeRenewalDate($r['valid_from'] ?? null);
            $validTo = normalizeRenewalDate($r['valid_to'] ?? null);
            $expiryDate = normalizeRenewalDate($r['expiry_date'] ?? null);
            if (!$validTo) $validTo = $expiryDate;
            if (!$expiryDate) $expiryDate = $validTo;

            $data = [
                'id' => $r['id'],
                'vehicle_no' => $r['vehicle_no'] ?? $r['vehicle'] ?? $r['vehicle_num'] ?? "N/A",
                'customer_name' => $r['customer_name'] ?? $r['customer'] ?? "N/A",
                'mobile_no' => $r['m1'] ?? $r['mobile_no'] ?? $r['mobile'] ?? "",
                'm1' => $r['m1'] ?? "",
                'm2' => $r['m2'] ?? "",
                'm3' => $r['m3'] ?? "",
                'software_type' => $r['software'] ?? $r['software_type'] ?? "",
                'amount' => $r['amount'] ?? 0,
                'location' => $r['location'] ?? "",
                'valid_from' => $validFrom,
                'valid_to' => $validTo,
                'expiry_date' => $expiryDate
            ];
            echo json_encode(['success' => true, 'data' => $data]);
        } else {
            echo json_encode([
                'success' => false,
                'allow_new' => true,
                'message' => "Vehicle not found. New renewal entry can be added.",
                'data' => [
                    'id' => '',
                    'vehicle_no' => strtoupper($v),
                    'customer_name' => '',
                    'mobile_no' => '',
                    'software_type' => '',
                    'amount' => 0,
                    'location' => ''
                ]
            ]);
        }
    } catch (Exception $e) { echo json_encode(['success' => false, 'message' => $e->getMessage()]); }
    exit;
}

if ($action === 'update') {
    try {
        $conn->beginTransaction();

        $id = $_REQUEST['id'] ?? null;
        $customerName = $_REQUEST['customer_name'] ?? "";
        $vehicleNo = strtoupper(trim($_REQUEST['vehicle_no'] ?? ''));
        $software = $_REQUEST['software_type'] ?? "";
        $amount = (float)($_REQUEST['amount'] ?? 0);
        $status = strtoupper(trim($_REQUEST['status'] ?? ''));
        $location = $_REQUEST['location'] ?? "";
        $received = (float)($_REQUEST['received_amount'] ?? 0);
        $mobile = $_REQUEST['mobile_no'] ?? "";
        $paymentMode = $_REQUEST['payment_mode'] ?? "";

        // Detect Columns for targeted update
        $qCols = $conn->query("DESCRIBE `$tableName` ");
        $allCols = $qCols->fetchAll(PDO::FETCH_COLUMN);

        $mCol = in_array('m1', $allCols) ? 'm1' : (in_array('mobile_no', $allCols) ? 'mobile_no' : 'mobile');
        $sCol = in_array('software', $allCols) ? 'software' : (in_array('software_type', $allCols) ? 'software_type' : null);
        $vehicleCol = in_array('vehicle_no', $allCols) ? 'vehicle_no' : (in_array('vehicle', $allCols) ? 'vehicle' : 'vehicle_num');
        $nameCol = in_array('customer_name', $allCols) ? 'customer_name' : 'customer';

        if (!$vehicleNo) throw new Exception("Vehicle number required");
        if (!$sCol) throw new Exception("Software column not found");

        // Calculate Profit
        $stmtP = $conn->prepare("SELECT cost FROM price_master WHERE name = ? AND type = 'SOFTWARE' LIMIT 1");
        $stmtP->execute([$software]);
        $cost = (float)($stmtP->fetchColumn() ?: 1500) + 500;
        $profit = ($status === 'YES') ? ($received - $cost) : 0;

        $uid = substr(md5(uniqid(mt_rand(), true)), 0, 12);

        $setParts = [
            "`$nameCol`=?",
            "`$mCol`=?",
            "`$vehicleCol`=?",
            "`$sCol`=?",
            "amount=?",
            "status=?",
            "location=?",
            "date=CURDATE()",
            "uid=?",
            "profit=?"
        ];
        $params = [$customerName, $mobile, $vehicleNo, $software, $amount, $status, $location, $uid, $profit];

        // Payment Mode column (if exists)
        if ($paymentMode && in_array('payment_mode', $allCols)) {
            $setParts[] = "payment_mode=?";
            $params[] = $paymentMode;
        }

        if ($status === 'YES') {
            if (in_array('valid_from', $allCols)) $setParts[] = "valid_from=CURDATE()";
            if (in_array('valid_to', $allCols)) $setParts[] = "valid_to=DATE_ADD(CURDATE(), INTERVAL 1 YEAR)";
            if (in_array('expiry_date', $allCols)) $setParts[] = "expiry_date=NULL";
            if (in_array('processed', $allCols)) $setParts[] = "processed='PAID'";
        } else {
            if (in_array('processed', $allCols)) $setParts[] = "processed=NULL";
        }

        if ($id) {
            $params[] = $id;
            $updateSql = "UPDATE `$tableName` SET " . implode(", ", $setParts) . " WHERE id=?";
            $conn->prepare($updateSql)->execute($params);
        } else {
            $insertData = [];
            if (in_array($nameCol, $allCols)) $insertData[$nameCol] = $customerName;
            if (in_array($mCol, $allCols)) $insertData[$mCol] = $mobile;
            if (in_array($vehicleCol, $allCols)) $insertData[$vehicleCol] = $vehicleNo;
            if (in_array($sCol, $allCols)) $insertData[$sCol] = $software;
            if (in_array('amount', $allCols)) $insertData['amount'] = $amount;
            if (in_array('status', $allCols)) $insertData['status'] = $status;
            if (in_array('location', $allCols)) $insertData['location'] = $location;
            if (in_array('uid', $allCols)) $insertData['uid'] = $uid;
            if (in_array('profit', $allCols)) $insertData['profit'] = $profit;
            if (in_array('date', $allCols)) $insertData['date'] = date('Y-m-d');
            if ($status === 'YES') {
                if (in_array('valid_from', $allCols)) $insertData['valid_from'] = date('Y-m-d');
                if (in_array('valid_to', $allCols)) $insertData['valid_to'] = date('Y-m-d', strtotime('+1 year'));
                if (in_array('expiry_date', $allCols)) $insertData['expiry_date'] = null;
                if (in_array('processed', $allCols)) $insertData['processed'] = 'PAID';
            } else {
                if (in_array('processed', $allCols)) $insertData['processed'] = null;
            }

            $insertCols = array_keys($insertData);
            $placeholders = implode(', ', array_fill(0, count($insertCols), '?'));
            $quotedCols = implode(', ', array_map(fn($col) => "`$col`", $insertCols));
            $insertSql = "INSERT INTO `$tableName` ($quotedCols) VALUES ($placeholders)";
            $conn->prepare($insertSql)->execute(array_values($insertData));
            $id = $conn->lastInsertId();
        }

        // Invoice logic
        $year = date('Y');
        $maxId = (int)$conn->query("SELECT MAX(id) FROM renewal_invoice_log")->fetchColumn();
        $invNo = "RINV-" . $year . "-" . str_pad($maxId + 1, 3, "0", STR_PAD_LEFT);

        $conn->prepare("INSERT INTO renewal_invoice_log (uid, date, invoice_num, customer_name, vehicle_num, software_type, amount, received_amount, mobile_no, amount_words) VALUES (?, CURDATE(), ?, ?, ?, ?, ?, ?, ?, ?)")
             ->execute([$uid, $invNo, $customerName, $vehicleNo, $software, $amount, $received, $mobile, getAmountInWords($received)]);

        if ($status === 'YES') {
            $stockCols = $conn->query("DESCRIBE stock_ledger")->fetchAll(PDO::FETCH_COLUMN);
            $vehicleRefCol = in_array('vehicle_no', $stockCols) ? 'vehicle_no' : (in_array('reference', $stockCols) ? 'reference' : null);
            if ($vehicleRefCol) {
                $conn->prepare("INSERT INTO stock_ledger (date, item_type, item_name, qty, `$vehicleRefCol`, remark) VALUES (CURDATE(), 'SOFTWARE', ?, -1, ?, 'RENEWAL')")
                     ->execute([$software, $vehicleNo]);
            }

            // Fetch current record to get IMEI and other details
            $stmtCurr = $conn->prepare("SELECT * FROM `$tableName` WHERE id = ?");
            $stmtCurr->execute([$id]);
            $curr = $stmtCurr->fetch(PDO::FETCH_ASSOC) ?: [];
            $imei = $_REQUEST['imei'] ?? $curr['imei'] ?? '';

            // 🔄 CREATE NEXT YEAR RENEWAL ENTRY AUTOMATICALLY
            $nextUid = substr(md5(uniqid(mt_rand(), true)), 0, 12);
            $nextValidFrom = date('Y-m-d'); // today
            $nextValidTo = date('Y-m-d', strtotime('+1 year')); // today + 1 year
            
            $nextData = [
                $nameCol => $customerName,
                $mCol => $mobile,
                $vehicleCol => $vehicleNo,
                $sCol => $software,
                'amount' => $amount, 
                'status' => 'PENDING',
                'location' => $location,
                'uid' => $nextUid,
                'date' => date('Y-m-d')
            ];
            
            if (in_array('imei', $allCols)) $nextData['imei'] = $imei;
            if (in_array('m1', $allCols)) $nextData['m1'] = $curr['m1'] ?? $mobile;
            if (in_array('m2', $allCols)) $nextData['m2'] = $curr['m2'] ?? '';
            if (in_array('m3', $allCols)) $nextData['m3'] = $curr['m3'] ?? '';
            
            if (in_array('valid_from', $allCols)) $nextData['valid_from'] = $nextValidFrom;
            if (in_array('valid_to', $allCols)) $nextData['valid_to'] = $nextValidTo;
            if (in_array('processed', $allCols)) $nextData['processed'] = 'NO';
            
            // Avoid duplicate future entry
            $checkNext = $conn->prepare("SELECT id FROM `$tableName` WHERE `$vehicleCol` = ? AND status = 'PENDING' AND valid_from >= ?");
            $checkNext->execute([$vehicleNo, $nextValidFrom]);
            
            if (!$checkNext->fetch()) {
                $nCols = array_keys($nextData);
                $nQuoted = implode(', ', array_map(fn($c) => "`$c`", $nCols));
                $nPlace = implode(', ', array_fill(0, count($nCols), '?'));
                $conn->prepare("INSERT INTO `$tableName` ($nQuoted) VALUES ($nPlace)")->execute(array_values($nextData));
            }
        }

        $conn->commit();
        echo json_encode(['success' => true, 'uid' => $uid, 'invoice_no' => $invNo, 'profit' => $profit, 'id' => $id, 'next_valid_from' => $nextValidFrom, 'next_valid_to' => $nextValidTo]);
    } catch (Exception $e) {
        if ($conn->inTransaction()) $conn->rollBack();
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
    exit;
}
?>
