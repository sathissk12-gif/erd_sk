<?php
// C:\Users\sathi\.gemini\antigravity\scratch\billing_app\api_sales.php v4.0 (Privacy Features)
include 'db_connect.php';
header('Content-Type: application/json');

$action = $_REQUEST['action'] ?? '';

function generateUID($length = 12) {
    return substr(bin2hex(random_bytes($length)), 0, $length);
}

function getRelayMaster($conn) {
    $stmt = $conn->query("SELECT name, cost FROM price_master WHERE type = 'RELAY' OR name LIKE 'RELAY%' ORDER BY CASE WHEN type = 'RELAY' THEN 0 ELSE 1 END, id ASC LIMIT 1");
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    return [
        'name' => $row['name'] ?? 'RELAY',
        'cost' => (float)($row['cost'] ?? 70)
    ];
}

function getNextInvoiceNumber($conn) {
    $today = new DateTime();
    $month = (int)$today->format('m');
    $year = (int)$today->format('Y');
    
    // Financial Year Logic (Apr-Mar)
    if ($month >= 4) {
        $fy = substr($year, -2) . "-" . substr($year + 1, -2);
    } else {
        $fy = substr($year - 1, -2) . "-" . substr($year, -2);
    }
    
    $prefix = "INVS" . $fy . "-";
    
    // Find the latest number for THIS financial year
    $stmt = $conn->prepare("SELECT invoice_no FROM invoice_log WHERE invoice_no LIKE ? ORDER BY id DESC LIMIT 1");
    $stmt->execute([$prefix . '%']);
    $lastInvoice = $stmt->fetchColumn();
    
    if (!$lastInvoice) {
        return $prefix . "001";
    }
    
    // Extract last 3 digits
    $lastNum = (int)substr($lastInvoice, -3);
    return $prefix . str_pad($lastNum + 1, 3, "0", STR_PAD_LEFT);
}

function mapSaleRowToFrontend($row) {
    if (!$row) {
        return null;
    }

    $softwareRaw = trim((string)($row['software'] ?? ''));
    $softwareName = $softwareRaw;
    $softwareDuration = '1_year';
    if ($softwareRaw !== '' && preg_match('/^(.*)\s+\((13 Month|13 Months|14 Month|14 Months|1 Year|1 Years|2 Year|2 Years|3 Year|3 Years|4 Year|4 Years)\)$/i', $softwareRaw, $matches)) {
        $softwareName = trim($matches[1]);
        $durationText = strtolower(trim($matches[2]));
        $durationText = str_replace([' months', ' month', ' years', ' year'], ['_month', '_month', '_year', '_year'], $durationText);
        $softwareDuration = $durationText;
    }

    return [
        'uid' => $row['uid'] ?? '',
        'vehicle' => $row['vehicle_no'] ?? '',
        'vehicle_no' => $row['vehicle_no'] ?? '',
        'imei' => $row['imei'] ?? '',
        'simNumber' => $row['sim_number'] ?? '',
        'sim_number' => $row['sim_number'] ?? '',
        'software' => $softwareRaw,
        'softwareName' => $softwareName,
        'softwareDuration' => $softwareDuration,
        'relay' => $row['relay'] ?? 'NO',
        'customer' => $row['customer_name'] ?? '',
        'customer_name' => $row['customer_name'] ?? '',
        'salesPerson' => $row['sales_person'] ?? '',
        'sales_person' => $row['sales_person'] ?? '',
        'location' => $row['location'] ?? '',
        'mobileNumber' => $row['mobile_number'] ?? '',
        'mobile_number' => $row['mobile_number'] ?? '',
        'simType' => $row['sim_type'] ?? 'BASIC',
        'hasSim' => (($row['sim_type'] ?? 'BASIC') !== 'NO_SIM'),
        'sim_type' => $row['sim_type'] ?? 'BASIC',
        'sellingPrice' => $row['selling_price'] ?? 0,
        'selling_price' => $row['selling_price'] ?? 0,
        'discountAmount' => $row['discount_price'] ?? 0,
        'discount_price' => $row['discount_price'] ?? 0,
        'receivedAmount' => $row['received_amount'] ?? 0,
        'received_amount' => $row['received_amount'] ?? 0,
        'installerPayout' => $row['installer_payout'] ?? 0,
        'installer_payout' => $row['installer_payout'] ?? 0,
        'paymentMode' => $row['payment_mode'] ?? 'CASH',
        'payment_mode' => $row['payment_mode'] ?? 'CASH'
    ];
}

function getDurationMultiplier($duration) {
    $key = strtolower(trim((string)$duration));
    if (preg_match('/^(\d+)_month$/', $key, $m)) {
        return (float)$m[1] / 12;
    }
    if (preg_match('/^(\d+)_year$/', $key, $m)) {
        return (float)$m[1];
    }
    return 1.0;
}

function formatDurationLabel($duration) {
    $key = strtolower(trim((string)$duration));
    if (preg_match('/^(\d+)_month$/', $key, $m)) {
        return $m[1] . ' ' . ($m[1] == 1 ? 'Month' : 'Months');
    }
    if (preg_match('/^(\d+)_year$/', $key, $m)) {
        return $m[1] . ' ' . ($m[1] == 1 ? 'Year' : 'Years');
    }
    return '1 Year';
}

function buildSoftwareDisplayName($software, $duration) {
    $software = trim((string)$software);
    if ($software === '') {
        return '';
    }
    return $software . ' (' . formatDurationLabel($duration) . ')';
}

function getSoftwareStockQty($duration) {
    return (int)max(1, round(getDurationMultiplier($duration)));
}

function getSoftwareBaseCost(PDO $conn, $software) {
    $software = trim((string)$software);
    if ($software === '') {
        return 0.0;
    }
    $stmtSoft = $conn->prepare("SELECT cost FROM price_master WHERE name = ? AND type = 'SOFTWARE' LIMIT 1");
    $stmtSoft->execute([$software]);
    return (float)($stmtSoft->fetchColumn() ?: 0);
}

function getSoftwareCost(PDO $conn, $software, $duration) {
    $baseCost = getSoftwareBaseCost($conn, $software);
    return round($baseCost * getDurationMultiplier($duration), 2);
}

function getSimCost($simType, $duration) {
    $type = strtoupper(trim((string)$simType));
    if ($type === 'NO_SIM' || $type === '') {
        return 0.0;
    }
    $annualBase = ($type === 'VOICE') ? 570 : 500;
    return round($annualBase * getDurationMultiplier($duration), 2);
}

// --- 🔄 RENEWAL SYNC HELPERS ---

function syncRenewalFromSale($conn, $sale, $duration) {
    $tables = $conn->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
    $tableName = in_array('renewal_log', $tables) ? 'renewal_log' : (in_array('RENEWAL_LOG', $tables) ? 'RENEWAL_LOG' : 'renewal_log');
    
    $qCols = $conn->query("DESCRIBE `$tableName` ");
    $allCols = $qCols->fetchAll(PDO::FETCH_COLUMN);

    $mCol = in_array('m1', $allCols) ? 'm1' : (in_array('mobile_no', $allCols) ? 'mobile_no' : 'mobile');
    $sCol = in_array('software', $allCols) ? 'software' : (in_array('software_type', $allCols) ? 'software_type' : 'software');
    $vCol = in_array('vehicle_no', $allCols) ? 'vehicle_no' : (in_array('vehicle', $allCols) ? 'vehicle' : 'vehicle_num');
    $nCol = in_array('customer_name', $allCols) ? 'customer_name' : 'customer';

    $vehicle = $sale['vehicle_no'];
    if (!$vehicle) return;

    $expiryDate = calculateExpiryDate($duration);

    // 🛡️ Double Entry Prevention: Look for an existing PENDING record first, otherwise the latest one.
    $stmtCheck = $conn->prepare("SELECT id FROM `$tableName` WHERE `$vCol` = ? ORDER BY CASE WHEN status = 'PENDING' THEN 0 ELSE 1 END, id DESC LIMIT 1");
    $stmtCheck->execute([$vehicle]);
    $existingId = $stmtCheck->fetchColumn();

    $data = [
        $nCol => $sale['customer_name'],
        $mCol => $sale['mobile_number'] ?? $sale['mobile_no'] ?? '',
        $vCol => $vehicle,
        $sCol => $sale['software'],
        'amount' => getRenewalPrice($sale['software']),
        'location' => $sale['location'],
        'status' => 'PENDING',
        'uid' => $sale['uid']
    ];

    if (in_array('valid_from', $allCols)) $data['valid_from'] = date('Y-m-d');
    if (in_array('valid_to', $allCols)) $data['valid_to'] = $expiryDate;
    if (in_array('expiry_date', $allCols)) $data['expiry_date'] = $expiryDate;
    if (in_array('date', $allCols)) $data['date'] = date('Y-m-d');
    if (in_array('processed', $allCols)) $data['processed'] = 'NO';

    // Copy IMEI if available in sale
    if (in_array('imei', $allCols) && isset($sale['imei'])) {
        $data['imei'] = $sale['imei'];
    }

    if ($existingId) {
        // If the existing record is already PAID (YES), and we are doing a new sale sync, 
        // we might want to create a new one IF the sale date is much later.
        // But to keep it simple and avoid double entries, we update the latest PENDING or the latest record.
        $sets = []; $vals = [];
        foreach($data as $k => $v) { $sets[] = "`$k` = ?"; $vals[] = $v; }
        $vals[] = $existingId;
        $sql = "UPDATE `$tableName` SET " . implode(', ', $sets) . " WHERE id = ?";
        $conn->prepare($sql)->execute($vals);
    } else {
        $cols = array_keys($data);
        $placeholders = str_repeat('?,', count($cols)-1) . '?';
        $sql = "INSERT INTO `$tableName` (`" . implode('`, `', $cols) . "`) VALUES ($placeholders)";
        $conn->prepare($sql)->execute(array_values($data));
    }

    // 🔄 CREATE NEXT YEAR RENEWAL ENTRY automatically (same as api_renewal.php)
    $nextUid = substr(md5(uniqid(mt_rand(), true)), 0, 12);
    $nextData = [
        $nCol => $sale['customer_name'],
        $mCol => $sale['mobile_number'] ?? $sale['mobile_no'] ?? '',
        $vCol => $vehicle,
        $sCol => $sale['software'],
        'amount' => getRenewalPrice($sale['software']),
        'location' => $sale['location'],
        'status' => 'PENDING',
        'uid' => $nextUid,
        'date' => date('Y-m-d')
    ];
    
    if (in_array('imei', $allCols) && isset($sale['imei'])) $nextData['imei'] = $sale['imei'];
    if (in_array('valid_from', $allCols)) $nextData['valid_from'] = date('Y-m-d');
    if (in_array('valid_to', $allCols)) $nextData['valid_to'] = date('Y-m-d', strtotime('+1 year'));
    if (in_array('processed', $allCols)) $nextData['processed'] = 'NO';
    
    // Avoid duplicate future entry
    $checkNext = $conn->prepare("SELECT id FROM `$tableName` WHERE `$vCol` = ? AND status = 'PENDING' AND valid_from = ? LIMIT 1");
    $checkNext->execute([$vehicle, date('Y-m-d')]);
    
    if (!$checkNext->fetch()) {
        $nCols = array_keys($nextData);
        $nQuoted = implode(', ', array_map(fn($c) => "`$c`", $nCols));
        $nPlace = implode(', ', array_fill(0, count($nCols), '?'));
        $conn->prepare("INSERT INTO `$tableName` ($nQuoted) VALUES ($nPlace)")->execute(array_values($nextData));
    }
}

function calculateExpiryDate($duration) {
    $duration = strtolower(trim((string)$duration));
    if (preg_match('/^(\d+)_month$/', $duration, $m)) {
        return date('Y-m-d', strtotime('+' . $m[1] . ' months'));
    }
    if (preg_match('/^(\d+)_year$/', $duration, $m)) {
        return date('Y-m-d', strtotime('+' . $m[1] . ' years'));
    }
    return date('Y-m-d', strtotime('+1 year'));
}

function getRenewalPrice($software) {
    $s = strtoupper(trim((string)$software));
    if (strpos($s, 'TRACK IN') !== false) return 1800;
    if (strpos($s, 'NAVILAP') !== false) return 1700;
    if (strpos($s, 'DO TRACK') !== false) return 1700;
    return 0;
}

switch($action) {
    case 'saveSale':
        try {
            $conn->beginTransaction();
            $vehicle = strtoupper(trim($_POST['vehicle'] ?? ''));
            $imei = trim($_POST['imei'] ?? '');
            $software = trim($_POST['software'] ?? '');
            $relay = strtoupper($_POST['relay'] ?? 'NO');
            $simType = strtoupper($_POST['simType'] ?? 'BASIC');
            $softwareDuration = strtolower(trim($_POST['softwareDuration'] ?? '1_year'));
            $customer = trim($_POST['customer'] ?? '');
            $location = trim($_POST['location'] ?? '');
            $simNumber = trim($_POST['simNumber'] ?? '');
            $mobileNumber = trim($_POST['mobileNumber'] ?? '');
            $salesPerson = trim($_POST['salesName'] ?? '');
            $paymentMode = $_POST['paymentMode'] ?? 'CASH';
            $selling = (float)($_POST['sellingPrice'] ?? 0);
            $discount = (float)($_POST['discountAmount'] ?? 0);
            $received = (float)($_POST['receivedAmount'] ?? 0);
            $installer = (float)($_POST['installerPayout'] ?? 0);

            $stmtDev = $conn->prepare("SELECT rate, device_model FROM device_master WHERE imei = ? LIMIT 1");
            $stmtDev->execute([$imei]); $devData = $stmtDev->fetch(PDO::FETCH_ASSOC);
            $deviceRate = (float)($devData['rate'] ?? 0);
            $model = $devData['device_model'] ?? '';

            $softwareRate = getSoftwareCost($conn, $software, $softwareDuration);

            $relayRate = 0; if ($relay === 'YES') {
                $relayMaster = getRelayMaster($conn);
                $relayRate = $relayMaster['cost'];
            }
            $simRate = getSimCost($simType, $softwareDuration);
            $netSelling = $selling - $discount;
            $profit = $netSelling - ($deviceRate + $softwareRate + $simRate + $relayRate + $installer);
            $pending = $netSelling - $received;
            
            $uid = generateUID(); // 🛡️ Privacy ID
            $softwareDisplay = buildSoftwareDisplayName($software, $softwareDuration);
            $softwareStockQty = getSoftwareStockQty($softwareDuration);

            $salesData = [
                'uid' => $uid, 'sale_date' => date('Y-m-d'), 'vehicle_no' => $vehicle, 'imei' => $imei, 'software' => $softwareDisplay,
                'relay' => $relay, 'customer_name' => $customer, 'location' => $location, 'selling_price' => $selling,
                'sales_person' => $salesPerson, 'profit' => $profit, 'sim_number' => $simNumber, 'discount_price' => $discount,
                'installer_payout' => $installer, 'received_amount' => $received, 'sim_type' => $simType, 'mobile_number' => $mobileNumber,
                'payment_mode' => $paymentMode, 'invoice_status' => ($pending > 0 ? 'Pending' : 'Closed')
            ];

            $cols = implode(', ', array_keys($salesData));
            $stmt = $conn->prepare("INSERT INTO sales_log ($cols) VALUES (".str_repeat('?,', count($salesData)-1)."?)");
            $stmt->execute(array_values($salesData));

            $invoiceNo = getNextInvoiceNumber($conn);
            $invData = [
                'uid' => $uid, 'invoice_no' => $invoiceNo, 'invoice_date' => date('Y-m-d'), 'customer_name' => $customer,
                'location' => $location, 'vehicle_no' => $vehicle, 'imei' => $imei, 'device_model' => $model,
                'software' => $softwareDisplay, 'sim_number' => $simNumber, 'relay' => $relay, 'total_amount' => $selling,
                'paid_amount' => $received, 'discount_amount' => $discount, 'pending_amount' => $pending,
                'status' => $salesData['invoice_status'], 'sales_person' => $salesPerson, 'mobile_number' => $mobileNumber, 'payment_mode' => $paymentMode
            ];
            $invCols = implode(', ', array_keys($invData));
            $stmtInv = $conn->prepare("INSERT INTO invoice_log ($invCols) VALUES (".str_repeat('?,', count($invData)-1)."?)");
            $stmtInv->execute(array_values($invData));

            $conn->prepare("UPDATE device_master SET status = 'SOLD', sales_person = ?, sold_date = CURDATE() WHERE imei = ?")->execute([$salesPerson, $imei]);

            if ($relay === 'YES') {
                $relayMaster = getRelayMaster($conn);
                $stockCols = $conn->query("DESCRIBE stock_ledger")->fetchAll(PDO::FETCH_COLUMN);
                $refCol = in_array('vehicle_no', $stockCols) ? 'vehicle_no' : (in_array('reference', $stockCols) ? 'reference' : null);

                if ($refCol) {
                    $conn->prepare("INSERT INTO stock_ledger (date, item_type, item_name, qty, `$refCol`, remark) VALUES (CURDATE(), 'RELAY', ?, -1, ?, 'SALES')")
                        ->execute([$relayMaster['name'], $vehicle]);
                } else {
                    $conn->prepare("INSERT INTO stock_ledger (item_name, qty, item_type, date) VALUES (?, -1, 'RELAY', CURDATE())")
                        ->execute([$relayMaster['name']]);
                }
            }

            if ($software !== '') {
                // 🧾 Insert stock ledger entry for software sale — with remark='SALES' and vehicle_no as reference
                $stockCols = $conn->query("DESCRIBE stock_ledger")->fetchAll(PDO::FETCH_COLUMN);
                $saleRefCol = in_array('vehicle_no', $stockCols) ? 'vehicle_no' : (in_array('reference', $stockCols) ? 'reference' : null);
                if ($saleRefCol && !empty($vehicle)) {
                    $conn->prepare("INSERT INTO stock_ledger (date, item_type, item_name, qty, `$saleRefCol`, remark) VALUES (CURDATE(), 'SOFTWARE', ?, ?, ?, 'SALES')")
                        ->execute([$software, -1 * $softwareStockQty, $vehicle]);
                } else {
                    $conn->prepare("INSERT INTO stock_ledger (date, item_type, item_name, qty, remark) VALUES (CURDATE(), 'SOFTWARE', ?, ?, 'SALES')")
                        ->execute([$software, -1 * $softwareStockQty]);
                }
            }

            // 🆕 Auto-Register Customer if not exists (Only if they have a valid mobile number)
            if ($customer && $mobileNumber !== null && trim($mobileNumber) !== '' && trim($mobileNumber) !== '-') {
                $stmtCheck = $conn->prepare("SELECT id FROM customerdatas WHERE name = ? AND mobile = ? LIMIT 1");
                $stmtCheck->execute([$customer, $mobileNumber]);
                if (!$stmtCheck->fetch()) {
                    $conn->prepare("INSERT IGNORE INTO customerdatas (name, mobile, location) VALUES (?, ?, ?)")->execute([$customer, $mobileNumber, $location]);
                }
            }

            // 🔄 Auto-Sync to Renewal Log
            syncRenewalFromSale($conn, $salesData, $softwareDuration);

            $conn->commit();
            echo json_encode(['status' => 'saved', 'uid' => $uid, 'invoice_no' => $invoiceNo]);
        } catch (Throwable $e) { if($conn->inTransaction()) $conn->rollBack(); echo json_encode(['error' => $e->getMessage()]); }
        break;

    case 'updateSale':
        try {
            $conn->beginTransaction();
            $vehicle = strtoupper(trim($_POST['vehicle'] ?? ''));
            $imei = trim($_POST['imei'] ?? '');
            $customer = trim($_POST['customer'] ?? '');
            $mobileNumber = trim($_POST['mobileNumber'] ?? '');
            $salesPerson = trim($_POST['salesName'] ?? '');
            $softwareDuration = strtolower(trim($_POST['softwareDuration'] ?? '1_year'));
            
            // Collect all fields from POST
            $updateData = [
                'imei' => $imei,
                'software' => buildSoftwareDisplayName(trim($_POST['software'] ?? ''), $softwareDuration),
                'relay' => strtoupper($_POST['relay'] ?? 'NO'),
                'customer_name' => $customer,
                'sales_person' => $salesPerson,
                'location' => trim($_POST['location'] ?? ''),
                'mobile_number' => $mobileNumber,
                'sim_number' => trim($_POST['simNumber'] ?? ''),
                'sim_type' => strtoupper($_POST['simType'] ?? 'BASIC'),
                'selling_price' => (float)($_POST['sellingPrice'] ?? 0),
                'discount_price' => (float)($_POST['discountAmount'] ?? 0),
                'received_amount' => (float)($_POST['receivedAmount'] ?? 0),
                'installer_payout' => (float)($_POST['installerPayout'] ?? 0),
                'payment_mode' => $_POST['paymentMode'] ?? 'CASH'
            ];

            $updateParts = [];
            foreach($updateData as $key => $val) { $updateParts[] = "$key = ?"; }
            $sql = "UPDATE sales_log SET " . implode(', ', $updateParts) . " WHERE vehicle_no = ? ORDER BY id DESC LIMIT 1";
            $stmt = $conn->prepare($sql);
            $stmt->execute(array_merge(array_values($updateData), [$vehicle]));

            // Update Invoice Log as well
            $sqlInv = "UPDATE invoice_log SET customer_name = ?, mobile_number = ?, location = ?, imei = ?, software = ?, total_amount = ?, paid_amount = ?, discount_amount = ?, payment_mode = ?, sales_person = ? WHERE vehicle_no = ? ORDER BY id DESC LIMIT 1";
            $conn->prepare($sqlInv)->execute([
                $customer, $mobileNumber, $updateData['location'], $imei, $updateData['software'],
                $updateData['selling_price'], $updateData['received_amount'], $updateData['discount_price'], 
                $updateData['payment_mode'], $salesPerson, $vehicle
            ]);

            $conn->prepare("UPDATE device_master SET sales_person = ? WHERE imei = ?")->execute([$salesPerson, $imei]);

            // Auto-Register Customer (Only if they have a valid mobile number)
            if ($customer && $mobileNumber !== null && trim($mobileNumber) !== '' && trim($mobileNumber) !== '-') {
                $stmtCheck = $conn->prepare("SELECT id FROM customerdatas WHERE name = ? AND mobile = ? LIMIT 1");
                $stmtCheck->execute([$customer, $mobileNumber]);
                if (!$stmtCheck->fetch()) {
                    $conn->prepare("INSERT IGNORE INTO customerdatas (name, mobile, location) VALUES (?, ?, ?)")->execute([$customer, $mobileNumber, $updateData['location']]);
                }
            }

            $stmtMeta = $conn->prepare("SELECT uid, invoice_no FROM invoice_log WHERE vehicle_no = ? ORDER BY id DESC LIMIT 1");
            $stmtMeta->execute([$vehicle]);
            $invoiceMeta = $stmtMeta->fetch(PDO::FETCH_ASSOC) ?: [];

            // 🔄 Auto-Sync Update to Renewal Log
            $syncData = $updateData;
            $syncData['vehicle_no'] = $vehicle;
            $syncData['uid'] = $invoiceMeta['uid'] ?? '';
            syncRenewalFromSale($conn, $syncData, $softwareDuration);

            $conn->commit();
            echo json_encode([
                'status' => 'updated',
                'uid' => $invoiceMeta['uid'] ?? '',
                'invoice_no' => $invoiceMeta['invoice_no'] ?? ''
            ]);

        } catch (Throwable $e) { if($conn->inTransaction()) $conn->rollBack(); echo json_encode(['error' => $e->getMessage()]); }
        break;



    case 'invoice-data':
        $uid = $_GET['uid'] ?? '';
        $invoiceNo = $_GET['invoice_no'] ?? '';
        $vehicle = $_GET['vehicle'] ?? '';
        try {
            if ($uid) {
                $stmt = $conn->prepare("SELECT * FROM invoice_log WHERE uid = ?");
                $stmt->execute([$uid]);
            } elseif ($invoiceNo) {
                $stmt = $conn->prepare("SELECT * FROM invoice_log WHERE invoice_no = ?");
                $stmt->execute([$invoiceNo]);
            } else {
                $stmt = $conn->prepare("SELECT * FROM invoice_log WHERE vehicle_no = ? ORDER BY id DESC LIMIT 1");
                $stmt->execute([$vehicle]);
            }
            $data = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($data) {
                $settings = $conn->query("SELECT * FROM settings LIMIT 1")->fetch(PDO::FETCH_ASSOC);
                echo json_encode(['success' => true, 'data' => $data, 'settings' => $settings]);
            } else { echo json_encode(['success' => false, 'message' => 'Invoice not found']); }
        } catch (Exception $e) { echo json_encode(['success' => false, 'message' => $e->getMessage()]); }
        break;

    case 'search-invoices':
        $q = trim($_GET['query'] ?? '');
        if (!$q) { echo json_encode([]); exit; }
        try {
            $stmt = $conn->prepare("SELECT * FROM invoice_log WHERE 
                vehicle_no LIKE ? OR 
                customer_name LIKE ? OR 
                invoice_no LIKE ? OR 
                imei LIKE ? 
                ORDER BY id DESC LIMIT 50");
            $likeQ = "%$q%";
            $stmt->execute([$likeQ, $likeQ, $likeQ, $likeQ]);
            echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
        } catch (Exception $e) { echo json_encode([]); }
        break;

    case 'getSale':
        $vehicle = $_GET['vehicle'] ?? '';
        $stmt = $conn->prepare("SELECT * FROM sales_log WHERE vehicle_no = ? ORDER BY id DESC LIMIT 1");
        $stmt->execute([$vehicle]);
        $res = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($res) echo json_encode(['status' => 'found', 'data' => mapSaleRowToFrontend($res)]);
        else echo json_encode(['status' => 'not_found']);
        break;

    case 'softwareList':
        try {
            // Pull from both price_master (definitions) and stock_ledger (actual stock entries)
            $sql = "SELECT DISTINCT name FROM (
                        SELECT name FROM price_master WHERE type = 'SOFTWARE'
                        UNION
                        SELECT item_name as name FROM stock_ledger WHERE item_type = 'SOFTWARE'
                    ) as combined ORDER BY name ASC";
            $stmt = $conn->query($sql);
            $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
            echo json_encode($data);
        } catch (Exception $e) { echo json_encode([]); }
        break;

    case 'get_preview':
        try {
            $imei = $_GET['imei'] ?? '';
            $software = $_GET['software'] ?? '';
            $simType = $_GET['simType'] ?? 'BASIC';
            $softwareDuration = $_GET['softwareDuration'] ?? '1_year';
            $relay = $_GET['relay'] ?? 'NO';

            $stmtDev = $conn->prepare("SELECT rate FROM device_master WHERE imei = ? LIMIT 1");
            $stmtDev->execute([$imei]); $device_cost = (float)($stmtDev->fetchColumn() ?: 0);

            $software_cost = getSoftwareCost($conn, $software, $softwareDuration);

            $sim_cost = getSimCost($simType, $softwareDuration);
            
            $relay_cost = 0;
            if ($relay === 'YES') {
                $relayMaster = getRelayMaster($conn);
                $relay_cost = $relayMaster['cost'];
            }

            echo json_encode([
                'device_cost' => $device_cost,
                'software_cost' => $software_cost,
                'sim_cost' => $sim_cost,
                'relay_cost' => $relay_cost
            ]);
        } catch (Exception $e) { echo json_encode(['error' => $e->getMessage()]); }
        break;
}
?>
