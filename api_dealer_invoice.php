<?php
include 'db_connect.php';
header('Content-Type: application/json');

$action = $_REQUEST['action'] ?? '';

function generateUID($length = 12) {
    return substr(bin2hex(random_bytes($length)), 0, $length);
}

function getExistingColumns(PDO $conn, $table) {
    try {
        $stmt = $conn->query("DESCRIBE `$table`");
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    } catch (Exception $e) {
        return [];
    }
}

function ensureDealerInvoiceTable(PDO $conn) {
    $conn->exec("CREATE TABLE IF NOT EXISTS dealer_invoice_log (
        id INT AUTO_INCREMENT PRIMARY KEY,
        uid VARCHAR(20) NOT NULL,
        invoice_no VARCHAR(50) NOT NULL,
        invoice_date DATE NOT NULL,
        dealer_name VARCHAR(150) NOT NULL,
        total_actual_rate DECIMAL(12,2) DEFAULT 0,
        total_selling_price DECIMAL(12,2) DEFAULT 0,
        total_profit DECIMAL(12,2) DEFAULT 0,
        total_devices INT DEFAULT 0,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        UNIQUE KEY uniq_dealer_date (dealer_name, invoice_date),
        UNIQUE KEY uniq_dealer_invoice_no (invoice_no),
        UNIQUE KEY uniq_dealer_uid (uid)
    )");
}

function getNextDealerInvoiceNumber(PDO $conn) {
    $today = new DateTime();
    $month = (int)$today->format('m');
    $year = (int)$today->format('Y');
    $fy = ($month >= 4)
        ? substr($year, -2) . "-" . substr($year + 1, -2)
        : substr($year - 1, -2) . "-" . substr($year, -2);

    $prefix = "INVD" . $fy . "-";
    $stmt = $conn->prepare("SELECT invoice_no FROM dealer_invoice_log WHERE invoice_no LIKE ? ORDER BY id DESC LIMIT 1");
    $stmt->execute([$prefix . '%']);
    $lastInvoice = $stmt->fetchColumn();
    if (!$lastInvoice) {
        return $prefix . "001";
    }
    $lastNum = (int)substr($lastInvoice, -3);
    return $prefix . str_pad($lastNum + 1, 3, "0", STR_PAD_LEFT);
}

function getLedgerTxnColumn(PDO $conn, $table) {
    $cols = getExistingColumns($conn, $table);
    foreach (['txn_id', 'transection_id', 'transaction_id'] as $wanted) {
        foreach ($cols as $col) {
            if (strtolower((string)$col) === $wanted) {
                return $col;
            }
        }
    }
    return '';
}

function tableExists(PDO $conn, $table) {
    try {
        $stmt = $conn->prepare("SHOW TABLES LIKE ?");
        $stmt->execute([$table]);
        return (bool)$stmt->fetchColumn();
    } catch (Exception $e) {
        return false;
    }
}

function getDealerContact(PDO $conn, $dealerName) {
    $fallback = [
        'customer_name' => $dealerName,
        'mobile' => '',
        'location' => '',
    ];

    if ($dealerName === '' || !tableExists($conn, 'customerdatas')) {
        return $fallback;
    }

    $normalized = preg_replace('/\s+/', ' ', trim((string)$dealerName));
    $upper = strtoupper($normalized);

    $queries = [
        ["SELECT name, mobile, location FROM customerdatas WHERE UPPER(TRIM(name)) = ? LIMIT 1", [$upper]],
        ["SELECT name, mobile, location FROM customerdatas WHERE UPPER(name) LIKE ? ORDER BY CASE WHEN UPPER(name) = ? THEN 0 ELSE 1 END, id DESC LIMIT 1", ['%' . $upper . '%', $upper]],
    ];

    foreach ($queries as [$sql, $params]) {
        try {
            $stmt = $conn->prepare($sql);
            $stmt->execute($params);
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($row) {
                return [
                    'customer_name' => $row['name'] ?? $dealerName,
                    'mobile' => $row['mobile'] ?? '',
                    'location' => $row['location'] ?? '',
                ];
            }
        } catch (Exception $e) {
            return $fallback;
        }
    }

    return $fallback;
}

function findOrCreateDealerInvoice(PDO $conn, $dealerName, $invoiceDate) {
    ensureDealerInvoiceTable($conn);

    $stmt = $conn->prepare("SELECT * FROM dealer_invoice_log WHERE dealer_name = ? AND invoice_date = ? LIMIT 1");
    $stmt->execute([$dealerName, $invoiceDate]);
    $existing = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($existing) {
        return $existing;
    }

    // Active ledger
    $sum1 = $conn->prepare("SELECT 
        COUNT(*) AS total_devices,
        COALESCE(SUM(selling_price), 0) AS total_selling_price,
        COALESCE(SUM(profit), 0) AS total_profit
        FROM dealer_ledger
        WHERE dealer_name = ? AND `date` = ?");
    $sum1->execute([$dealerName, $invoiceDate]);
    $t1 = $sum1->fetch(PDO::FETCH_ASSOC);

    // Old ledger
    $t2 = ['total_devices' => 0, 'total_selling_price' => 0, 'total_profit' => 0];
    if (tableExists($conn, 'dealer_ledger_old_1778788199')) {
        try {
            $sum2 = $conn->prepare("SELECT 
                COUNT(*) AS total_devices,
                COALESCE(SUM(selling_price), 0) AS total_selling_price,
                COALESCE(SUM(profit), 0) AS total_profit
                FROM dealer_ledger_old_1778788199
                WHERE dealer_name = ? AND `date` = ?");
            $sum2->execute([$dealerName, $invoiceDate]);
            $t2 = $sum2->fetch(PDO::FETCH_ASSOC);
        } catch (Exception $e) {}
    }

    $total_devices = (int)($t1['total_devices'] ?? 0) + (int)($t2['total_devices'] ?? 0);
    $total_selling_price = (float)($t1['total_selling_price'] ?? 0) + (float)($t2['total_selling_price'] ?? 0);
    $total_profit = (float)($t1['total_profit'] ?? 0) + (float)($t2['total_profit'] ?? 0);

    $uid = generateUID();
    $invoiceNo = getNextDealerInvoiceNumber($conn);
    $insert = $conn->prepare("INSERT INTO dealer_invoice_log
        (uid, invoice_no, invoice_date, dealer_name, total_actual_rate, total_selling_price, total_profit, total_devices)
        VALUES (?, ?, ?, ?, 0, ?, ?, ?)");
    $insert->execute([
        $uid,
        $invoiceNo,
        $invoiceDate,
        $dealerName,
        $total_selling_price,
        $total_profit,
        $total_devices
    ]);

    $stmt->execute([$dealerName, $invoiceDate]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

function getInvoiceRows(PDO $conn, $dealerName, $invoiceDate, $txnCol1, $txnCol2) {
    $txnSelect1 = $txnCol1 ? ", dl.`$txnCol1` AS txn_value" : ", '' AS txn_value";
    $stmt1 = $conn->prepare("SELECT dl.imei, 0 as actual_rate, dl.selling_price, dl.profit, dl.`date`, dl.dealer_name,
        COALESCE(dm.device_model, '') AS device_model $txnSelect1, 'Current' as source
        FROM dealer_ledger dl
        LEFT JOIN device_master dm ON TRIM(dm.imei) = TRIM(dl.imei)
        WHERE dl.dealer_name = ? AND dl.`date` = ?");
    $stmt1->execute([$dealerName, $invoiceDate]);
    $rows1 = $stmt1->fetchAll(PDO::FETCH_ASSOC);

    $rows2 = [];
    if (tableExists($conn, 'dealer_ledger_old_1778788199')) {
        try {
            $txnSelect2 = $txnCol2 ? ", dl.`$txnCol2` AS txn_value" : ", '' AS txn_value";
            $stmt2 = $conn->prepare("SELECT dl.imei, 0 as actual_rate, dl.selling_price, dl.profit, dl.`date`, dl.dealer_name,
                COALESCE(dm.device_model, '') AS device_model $txnSelect2, 'Old' as source
                FROM dealer_ledger_old_1778788199 dl
                LEFT JOIN device_master dm ON TRIM(dm.imei) = TRIM(dl.imei)
                WHERE dl.dealer_name = ? AND dl.`date` = ?");
            $stmt2->execute([$dealerName, $invoiceDate]);
            $rows2 = $stmt2->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {}
    }

    return array_merge($rows1, $rows2);
}

function getPendingSummary(array $items) {
    $pendingDevices = 0;
    $pendingAmount = 0.0;
    $paidAmount = 0.0;

    foreach ($items as $item) {
        $amount = (float)($item['selling_price'] ?? 0);
        $txn = trim((string)($item['txn_value'] ?? ''));
        if ($txn === '') {
            $pendingDevices++;
            $pendingAmount += $amount;
        } else {
            $paidAmount += $amount;
        }
    }

    return [
        'pending_devices' => $pendingDevices,
        'pending_amount' => $pendingAmount,
        'paid_amount' => $paidAmount,
    ];
}

switch ($action) {
    case 'search':
        $q = trim($_GET['query'] ?? '');
        if ($q === '') {
            echo json_encode([]);
            exit;
        }
        try {
            ensureDealerInvoiceTable($conn);
            $txnCol1 = getLedgerTxnColumn($conn, 'dealer_ledger');
            $txnCol2 = getLedgerTxnColumn($conn, 'dealer_ledger_old_1778788199');
            
            $pendingTxnCondition1 = $txnCol1 ? "SUM(CASE WHEN `$txnCol1` IS NULL OR TRIM(`$txnCol1`) = '' THEN 1 ELSE 0 END)" : "COUNT(*)";
            $pendingAmountExpr1 = $txnCol1 ? "SUM(CASE WHEN `$txnCol1` IS NULL OR TRIM(`$txnCol1`) = '' THEN COALESCE(selling_price, 0) ELSE 0 END)" : "SUM(COALESCE(selling_price, 0))";
            
            $stmt1 = $conn->prepare("SELECT dealer_name, `date`,
                COUNT(*) AS total_devices,
                0 AS total_actual_rate,
                COALESCE(SUM(selling_price), 0) AS total_selling_price,
                COALESCE(SUM(profit), 0) AS total_profit,
                COALESCE($pendingTxnCondition1, 0) AS pending_devices,
                COALESCE($pendingAmountExpr1, 0) AS pending_amount
                FROM dealer_ledger
                WHERE dealer_name LIKE ?
                GROUP BY dealer_name, `date`");
            $stmt1->execute(['%' . $q . '%']);
            $rows1 = $stmt1->fetchAll(PDO::FETCH_ASSOC);

            $rows2 = [];
            if (tableExists($conn, 'dealer_ledger_old_1778788199')) {
                try {
                    $pendingTxnCondition2 = $txnCol2 ? "SUM(CASE WHEN `$txnCol2` IS NULL OR TRIM(`$txnCol2`) = '' THEN 1 ELSE 0 END)" : "COUNT(*)";
                    $pendingAmountExpr2 = $txnCol2 ? "SUM(CASE WHEN `$txnCol2` IS NULL OR TRIM(`$txnCol2`) = '' THEN COALESCE(selling_price, 0) ELSE 0 END)" : "SUM(COALESCE(selling_price, 0))";
                    
                    $stmt2 = $conn->prepare("SELECT dealer_name, `date`,
                        COUNT(*) AS total_devices,
                        0 AS total_actual_rate,
                        COALESCE(SUM(selling_price), 0) AS total_selling_price,
                        COALESCE(SUM(profit), 0) AS total_profit,
                        COALESCE($pendingTxnCondition2, 0) AS pending_devices,
                        COALESCE($pendingAmountExpr2, 0) AS pending_amount
                        FROM dealer_ledger_old_1778788199
                        WHERE dealer_name LIKE ?
                        GROUP BY dealer_name, `date`");
                    $stmt2->execute(['%' . $q . '%']);
                    $rows2 = $stmt2->fetchAll(PDO::FETCH_ASSOC);
                } catch (Exception $e) {}
            }

            $merged = [];
            foreach (array_merge($rows1, $rows2) as $row) {
                $key = strtolower(trim($row['dealer_name'])) . '_' . $row['date'];
                if (!isset($merged[$key])) {
                    $merged[$key] = [
                        'dealer_name' => $row['dealer_name'],
                        'date' => $row['date'],
                        'total_devices' => 0,
                        'total_actual_rate' => 0,
                        'total_selling_price' => 0,
                        'total_profit' => 0,
                        'pending_devices' => 0,
                        'pending_amount' => 0
                    ];
                }
                $merged[$key]['total_devices'] += (int)$row['total_devices'];
                $merged[$key]['total_selling_price'] += (float)$row['total_selling_price'];
                $merged[$key]['total_profit'] += (float)$row['total_profit'];
                $merged[$key]['pending_devices'] += (int)$row['pending_devices'];
                $merged[$key]['pending_amount'] += (float)$row['pending_amount'];
            }

            usort($merged, function($a, $b) {
                $dateDiff = strcmp($b['date'], $a['date']);
                if ($dateDiff !== 0) return $dateDiff;
                return strcmp($a['dealer_name'], $b['dealer_name']);
            });

            $result = [];
            foreach ($merged as $row) {
                $inv = findOrCreateDealerInvoice($conn, $row['dealer_name'], $row['date']);
                if ($inv) {
                    $contact = getDealerContact($conn, $row['dealer_name']);
                    $result[] = array_merge($row, [
                        'uid' => $inv['uid'],
                        'invoice_no' => $inv['invoice_no'],
                        'invoice_date' => $inv['invoice_date'],
                        'dealer_mobile' => $contact['mobile'],
                        'dealer_location' => $contact['location'],
                        'customer_name' => $contact['customer_name'],
                    ]);
                }
            }
            echo json_encode($result);
        } catch (Exception $e) {
            echo json_encode(['error' => $e->getMessage()]);
        }
        break;

    case 'invoice-data':
        $uid = trim($_GET['uid'] ?? '');
        $invoiceNo = trim($_GET['invoice_no'] ?? '');
        $dealerName = trim($_GET['dealer_name'] ?? '');
        $invoiceDate = trim($_GET['invoice_date'] ?? '');
        try {
            ensureDealerInvoiceTable($conn);
            $txnCol1 = getLedgerTxnColumn($conn, 'dealer_ledger');
            $txnCol2 = getLedgerTxnColumn($conn, 'dealer_ledger_old_1778788199');

            if ($uid !== '') {
                $stmt = $conn->prepare("SELECT * FROM dealer_invoice_log WHERE uid = ? LIMIT 1");
                $stmt->execute([$uid]);
            } elseif ($invoiceNo !== '') {
                $stmt = $conn->prepare("SELECT * FROM dealer_invoice_log WHERE invoice_no = ? LIMIT 1");
                $stmt->execute([$invoiceNo]);
            } elseif ($dealerName !== '' && $invoiceDate !== '') {
                findOrCreateDealerInvoice($conn, $dealerName, $invoiceDate);
                $stmt = $conn->prepare("SELECT * FROM dealer_invoice_log WHERE dealer_name = ? AND invoice_date = ? LIMIT 1");
                $stmt->execute([$dealerName, $invoiceDate]);
            } else {
                throw new Exception('Invoice not found');
            }

            $invoice = $stmt->fetch(PDO::FETCH_ASSOC);
            if (!$invoice) {
                throw new Exception('Invoice not found');
            }

            $items = getInvoiceRows($conn, $invoice['dealer_name'], $invoice['invoice_date'], $txnCol1, $txnCol2);
            
            $totalActual = 0;
            $totalSelling = 0;
            $totalProfit = 0;
            $totalDevices = count($items);
            foreach ($items as $item) {
                $totalActual += (float)($item['actual_rate'] ?? 0);
                $totalSelling += (float)($item['selling_price'] ?? 0);
                $totalProfit += (float)($item['profit'] ?? 0);
            }

            if ($totalSelling != $invoice['total_selling_price'] || $totalDevices != $invoice['total_devices']) {
                $updHeader = $conn->prepare("UPDATE dealer_invoice_log SET total_actual_rate = ?, total_selling_price = ?, total_profit = ?, total_devices = ? WHERE id = ?");
                $updHeader->execute([$totalActual, $totalSelling, $totalProfit, $totalDevices, $invoice['id']]);
                $invoice['total_actual_rate'] = $totalActual;
                $invoice['total_selling_price'] = $totalSelling;
                $invoice['total_profit'] = $totalProfit;
                $invoice['total_devices'] = $totalDevices;
            }

            $settings = $conn->query("SELECT * FROM settings LIMIT 1")->fetch(PDO::FETCH_ASSOC);
            $contact = getDealerContact($conn, $invoice['dealer_name']);
            $summary = getPendingSummary($items);

            echo json_encode([
                'success' => true,
                'data' => array_merge($invoice, [
                    'dealer_mobile' => $contact['mobile'],
                    'dealer_location' => $contact['location'],
                    'customer_name' => $contact['customer_name'],
                    'pending_devices' => $summary['pending_devices'],
                    'pending_amount' => $summary['pending_amount'],
                    'paid_amount' => $summary['paid_amount'],
                ]),
                'items' => $items,
                'settings' => $settings,
                'txn_column' => $txnCol1,
            ]);
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
        break;

    default:
        echo json_encode(['status' => 'online']);
        break;
}
?>
