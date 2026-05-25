<?php
/**
 * Office Settlement API (Legacy GS Engine Replication) - DB SYNC VERSION
 */
error_reporting(E_ALL);
ini_set('display_errors', 1);
header('Content-Type: application/json');

include 'db_connect.php';

const RELAY_RATE = 60;

try {
    if (!isset($conn)) throw new Exception("Database connection failed");

    // 1. Setup Office-specific staging tables (These stay as is)
    setup_office_tables($conn);

    $action = strtolower($_GET['action'] ?? $_POST['action'] ?? '');

    switch ($action) {
        case 'fetch':
            handle_fetch($conn);
            break;
        case 'total':
            handle_total($conn);
            break;
        case 'details':
            handle_details($conn);
            break;
        case 'process':
            handle_process($conn);
            break;
        case 'history':
            handle_history($conn);
            break;
        case 'summary':
            handle_summary($conn);
            break;
        default:
            echo json_encode(['success' => true, 'message' => 'OFFICE SETTLEMENT API RUNNING']);
    }

} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'API Error: ' . $e->getMessage()]);
}

function setup_office_tables($conn) {
    // These are the intermediate tables for the settlement engine
    $conn->exec("CREATE TABLE IF NOT EXISTS office_sales (
        id INT AUTO_INCREMENT PRIMARY KEY,
        sales_log_id INT DEFAULT NULL,
        vehicle_no VARCHAR(50),
        imei VARCHAR(50),
        relay_rate DECIMAL(10,2) DEFAULT 0,
        device_rate DECIMAL(10,2) DEFAULT 0,
        software_rate DECIMAL(10,2) DEFAULT 0,
        office_settled VARCHAR(10) DEFAULT '',
        txn_id VARCHAR(100) DEFAULT '',
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB");

    $conn->exec("CREATE TABLE IF NOT EXISTS office_renewal (
        id INT AUTO_INCREMENT PRIMARY KEY,
        renewal_log_id INT DEFAULT NULL,
        vehicle_no VARCHAR(50),
        imei VARCHAR(50),
        software VARCHAR(100),
        renewal_amount DECIMAL(10,2) DEFAULT 0,
        office_settled VARCHAR(10) DEFAULT '',
        txn_id VARCHAR(100) DEFAULT '',
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB");

    $conn->exec("CREATE TABLE IF NOT EXISTS office_settlement_log (
        id INT AUTO_INCREMENT PRIMARY KEY,
        txn_id VARCHAR(100) DEFAULT '',
        sales_amount DECIMAL(10,2) DEFAULT 0,
        renewal_amount DECIMAL(10,2) DEFAULT 0,
        total_amount DECIMAL(10,2) DEFAULT 0,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB");

    ensure_column($conn, 'office_sales', 'sales_log_id', "INT DEFAULT NULL");
    ensure_column($conn, 'office_renewal', 'renewal_log_id', "INT DEFAULT NULL");

    // Ensure source tables have office_settled column (matching user screenshot)
    $salesCols = $conn->query("DESCRIBE sales_log")->fetchAll(PDO::FETCH_COLUMN);
    if(!in_array('office_settled', $salesCols)) $conn->exec("ALTER TABLE sales_log ADD office_settled VARCHAR(10) DEFAULT NULL");
    if(!in_array('office_txn_id', $salesCols)) $conn->exec("ALTER TABLE sales_log ADD office_txn_id VARCHAR(100) DEFAULT NULL");
    if(!in_array('office_processed', $salesCols)) $conn->exec("ALTER TABLE sales_log ADD office_processed VARCHAR(10) DEFAULT NULL");
    
    $renewalCols = $conn->query("DESCRIBE renewal_log")->fetchAll(PDO::FETCH_COLUMN);
    if(!in_array('office_settled', $renewalCols)) $conn->exec("ALTER TABLE renewal_log ADD office_settled VARCHAR(10) DEFAULT NULL");
    if(!in_array('office_txn_id', $renewalCols)) $conn->exec("ALTER TABLE renewal_log ADD office_txn_id VARCHAR(100) DEFAULT NULL");
    if(!in_array('office_processed', $renewalCols)) $conn->exec("ALTER TABLE renewal_log ADD office_processed VARCHAR(10) DEFAULT NULL");
}

function ensure_column($conn, $table, $column, $definition) {
    $cols = $conn->query("DESCRIBE `$table`")->fetchAll(PDO::FETCH_COLUMN);
    if (!in_array($column, $cols)) {
        $conn->exec("ALTER TABLE `$table` ADD `$column` $definition");
    }
}

function normalize_name($value) {
    return strtoupper(trim((string)($value ?? '')));
}

function extract_duration_multiplier($softwareLabel) {
    $label = trim((string)($softwareLabel ?? ''));
    if ($label !== '' && preg_match('/\((13 Month|13 Months|14 Month|14 Months|1 Year|1 Years|2 Year|2 Years|3 Year|3 Years|4 Year|4 Years)\)$/i', $label, $m)) {
        $map = [
            '13 MONTH' => 13 / 12,
            '13 MONTHS' => 13 / 12,
            '14 MONTH' => 14 / 12,
            '14 MONTHS' => 14 / 12,
            '1 YEAR' => 1,
            '1 YEARS' => 1,
            '2 YEAR' => 2,
            '2 YEARS' => 2,
            '3 YEAR' => 3,
            '3 YEARS' => 3,
            '4 YEAR' => 4,
            '4 YEARS' => 4,
        ];
        return $map[strtoupper(trim($m[1]))] ?? 1;
    }
    return 1;
}

function extract_software_base_name($softwareLabel) {
    $label = trim((string)($softwareLabel ?? ''));
    if ($label !== '' && preg_match('/^(.*)\s+\((13 Month|13 Months|14 Month|14 Months|1 Year|1 Years|2 Year|2 Years|3 Year|3 Years|4 Year|4 Years)\)$/i', $label, $m)) {
        return trim($m[1]);
    }
    return $label;
}

function get_price_master_rate($conn, $name, $type = null) {
    $name = normalize_name($name);
    if ($name === '') return 0;

    if ($type) {
        $stmt = $conn->prepare("SELECT cost FROM price_master WHERE UPPER(TRIM(name)) = ? AND UPPER(TRIM(type)) = ? ORDER BY id DESC LIMIT 1");
        $stmt->execute([$name, strtoupper($type)]);
    } else {
        $stmt = $conn->prepare("SELECT cost FROM price_master WHERE UPPER(TRIM(name)) = ? ORDER BY id DESC LIMIT 1");
        $stmt->execute([$name]);
    }
    return (float)($stmt->fetchColumn() ?: 0);
}

function get_software_rate($conn, $software) {
    $baseSoftware = extract_software_base_name($software);
    $multiplier = extract_duration_multiplier($software);
    $rate = get_price_master_rate($conn, $baseSoftware, 'SOFTWARE');
    if ($rate > 0) return round($rate * $multiplier, 2);

    $tables = $conn->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
    if (!in_array('software_master', $tables)) return 0;

    $swCols = $conn->query("DESCRIBE software_master")->fetchAll(PDO::FETCH_COLUMN);
    $swRateCol = in_array('rate', $swCols) ? 'rate' : (in_array('price', $swCols) ? 'price' : null);
    $swNameCol = in_array('software_name', $swCols) ? 'software_name' : (in_array('name', $swCols) ? 'name' : null);
    if (!$swRateCol || !$swNameCol) return 0;

    $stmt = $conn->prepare("SELECT `$swRateCol` FROM software_master WHERE UPPER(TRIM(`$swNameCol`)) = ? ORDER BY id DESC LIMIT 1");
    $stmt->execute([normalize_name($baseSoftware)]);
    return round((float)($stmt->fetchColumn() ?: 0) * $multiplier, 2);
}

function get_device_rate($conn, $imei) {
    $imei = trim((string)($imei ?? ''));
    if ($imei === '') return 0;
    $stmt = $conn->prepare("SELECT rate FROM device_master WHERE imei = ? LIMIT 1");
    $stmt->execute([$imei]);
    return (float)($stmt->fetchColumn() ?: 0);
}

function get_device_info($conn, $imei) {
    $imei = trim((string)($imei ?? ''));
    if ($imei === '') {
        return ['model' => '', 'rate' => 0];
    }

    $stmt = $conn->prepare("SELECT device_model, rate FROM device_master WHERE imei = ? LIMIT 1");
    $stmt->execute([$imei]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$row) {
        return ['model' => '', 'rate' => 0];
    }

    return [
        'model' => $row['device_model'] ?? '',
        'rate' => (float)($row['rate'] ?? 0)
    ];
}

function get_relay_rate($conn, $relay) {
    if (normalize_name($relay) !== 'YES') return 0;
    $rate = get_price_master_rate($conn, 'RELAY', 'RELAY');
    return $rate > 0 ? $rate : RELAY_RATE;
}

function get_pending_sales_rows($conn) {
    $stmt = $conn->query("SELECT * FROM sales_log WHERE COALESCE(office_settled, '') != 'DONE'");
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function get_pending_renewal_rows($conn) {
    $sql = "SELECT * FROM renewal_log
            WHERE (UPPER(TRIM(COALESCE(status, ''))) = 'YES'
               OR UPPER(TRIM(COALESCE(processed, ''))) IN ('YES', 'PAID'))
              AND COALESCE(office_settled, '') != 'DONE'";
    return $conn->query($sql)->fetchAll(PDO::FETCH_ASSOC);
}

function handle_fetch($conn) {
    try {
        $conn->beginTransaction();

        $sales = get_pending_sales_rows($conn);
        $fetchCount = 0;
        foreach ($sales as $row) {
            $imei = trim((string)($row['imei'] ?? ''));
            $deviceRate = get_device_rate($conn, $imei);
            $softwareRate = get_software_rate($conn, $row['software'] ?? '');
            $relayRate = get_relay_rate($conn, $row['relay'] ?? '');

            $chk = $conn->prepare("SELECT id FROM office_sales WHERE sales_log_id = ? LIMIT 1");
            $chk->execute([$row['id']]);
            $existingId = $chk->fetchColumn();
            if (!$existingId) {
                $legacyChk = $conn->prepare("SELECT id FROM office_sales
                    WHERE COALESCE(office_settled, '') != 'DONE'
                      AND (
                        (TRIM(COALESCE(imei, '')) != '' AND imei = ?)
                        OR (TRIM(COALESCE(vehicle_no, '')) != '' AND vehicle_no = ?)
                      )
                    ORDER BY id DESC
                    LIMIT 1");
                $legacyChk->execute([$imei, $row['vehicle_no']]);
                $existingId = $legacyChk->fetchColumn();
            }
            if ($existingId) {
                $upd = $conn->prepare("UPDATE office_sales
                    SET sales_log_id = ?, vehicle_no = ?, imei = ?, relay_rate = ?, device_rate = ?, software_rate = ?, office_settled = '', txn_id = ''
                    WHERE id = ?");
                $upd->execute([$row['id'], $row['vehicle_no'], $imei, $relayRate, $deviceRate, $softwareRate, $existingId]);
            } else {
                $ins = $conn->prepare("INSERT INTO office_sales (sales_log_id, vehicle_no, imei, relay_rate, device_rate, software_rate) VALUES (?, ?, ?, ?, ?, ?)");
                $ins->execute([$row['id'], $row['vehicle_no'], $imei, $relayRate, $deviceRate, $softwareRate]);
            }
            $fetchCount++;
        }

        $renewals = get_pending_renewal_rows($conn);
        $renewalCount = 0;
        foreach ($renewals as $row) {
            $vno = trim($row['vehicle_no'] ?? $row['vehicle'] ?? '');
            $imei = trim($row['imei'] ?? '');
            $software = trim($row['software'] ?? '');
            $renewalAmount = get_software_rate($conn, $software);

            $chk = $conn->prepare("SELECT id FROM office_renewal WHERE renewal_log_id = ? LIMIT 1");
            $chk->execute([$row['id']]);
            $existingId = $chk->fetchColumn();
            if (!$existingId) {
                $legacyChk = $conn->prepare("SELECT id FROM office_renewal
                    WHERE COALESCE(office_settled, '') != 'DONE'
                      AND vehicle_no = ?
                      AND software = ?
                    ORDER BY id DESC
                    LIMIT 1");
                $legacyChk->execute([$vno, $software]);
                $existingId = $legacyChk->fetchColumn();
            }
            if ($existingId) {
                $upd = $conn->prepare("UPDATE office_renewal
                    SET renewal_log_id = ?, vehicle_no = ?, imei = ?, software = ?, renewal_amount = ?, office_settled = '', txn_id = ''
                    WHERE id = ?");
                $upd->execute([$row['id'], $vno, $imei, $software, $renewalAmount, $existingId]);
            } else {
                $ins = $conn->prepare("INSERT INTO office_renewal (renewal_log_id, vehicle_no, imei, software, renewal_amount) VALUES (?, ?, ?, ?, ?)");
                $ins->execute([$row['id'], $vno, $imei, $software, $renewalAmount]);
            }
            $renewalCount++;
        }

        $conn->exec("DELETE os FROM office_sales os LEFT JOIN sales_log s ON s.id = os.sales_log_id WHERE os.office_settled != 'DONE' AND (s.id IS NULL OR COALESCE(s.office_settled, '') = 'DONE')");
        $conn->exec("DELETE orw FROM office_renewal orw LEFT JOIN renewal_log r ON r.id = orw.renewal_log_id WHERE orw.office_settled != 'DONE' AND (r.id IS NULL OR COALESCE(r.office_settled, '') = 'DONE')");

        $conn->commit();
        echo json_encode(['status' => 'fetched', 'sales' => $fetchCount, 'renewals' => $renewalCount]);

    } catch (Exception $e) {
        if ($conn->inTransaction()) $conn->rollBack();
        echo json_encode(['success' => false, 'message' => 'Fetch Error: ' . $e->getMessage()]);
    }
}

function handle_total($conn) {
    try {
        $sVal = 0;
        foreach (get_pending_sales_rows($conn) as $row) {
            $sVal += get_relay_rate($conn, $row['relay'] ?? '');
            $sVal += get_device_rate($conn, $row['imei'] ?? '');
            $sVal += get_software_rate($conn, $row['software'] ?? '');
        }

        $rVal = 0;
        foreach (get_pending_renewal_rows($conn) as $row) {
            $rVal += get_software_rate($conn, $row['software'] ?? '');
        }

        echo json_encode(['success' => true, 'sales' => $sVal, 'renewal' => $rVal, 'total' => $sVal + $rVal]);
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
}

function handle_details($conn) {
    try {
        $salesDetails = [];
        $salesTotal = 0;
        foreach (get_pending_sales_rows($conn) as $row) {
            $relayRate = get_relay_rate($conn, $row['relay'] ?? '');
            $deviceInfo = get_device_info($conn, $row['imei'] ?? '');
            $deviceRate = (float)($deviceInfo['rate'] ?? 0);
            $softwareRate = get_software_rate($conn, $row['software'] ?? '');
            $lineTotal = $relayRate + $deviceRate + $softwareRate;
            $salesTotal += $lineTotal;

            $salesDetails[] = [
                'source' => 'sales',
                'id' => $row['id'] ?? null,
                'vehicle_no' => $row['vehicle_no'] ?? '',
                'imei' => $row['imei'] ?? '',
                'device_model' => $deviceInfo['model'] ?? '',
                'software' => $row['software'] ?? '',
                'relay' => $row['relay'] ?? '',
                'device_rate' => $deviceRate,
                'software_rate' => $softwareRate,
                'relay_rate' => $relayRate,
                'total' => $lineTotal
            ];
        }

        $renewalDetails = [];
        $renewalTotal = 0;
        foreach (get_pending_renewal_rows($conn) as $row) {
            $softwareRate = get_software_rate($conn, $row['software'] ?? '');
            $renewalTotal += $softwareRate;

            $renewalDetails[] = [
                'source' => 'renewal',
                'id' => $row['id'] ?? null,
                'vehicle_no' => $row['vehicle_no'] ?? '',
                'imei' => $row['imei'] ?? '',
                'software' => $row['software'] ?? '',
                'software_rate' => $softwareRate,
                'total' => $softwareRate
            ];
        }

        echo json_encode([
            'success' => true,
            'sales' => $salesTotal,
            'renewal' => $renewalTotal,
            'total' => $salesTotal + $renewalTotal,
            'salesDetails' => $salesDetails,
            'renewalDetails' => $renewalDetails
        ]);
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
}

function handle_process($conn) {
    $txn = $_GET['txn'] ?? $_POST['txn'] ?? '';
    if (!$txn) { echo json_encode(['success' => false, 'message' => 'TXN ID Missing']); exit; }

    try {
        $conn->beginTransaction();

        $conn->exec("UPDATE office_sales os
            JOIN sales_log s
              ON COALESCE(os.sales_log_id, 0) = 0
             AND COALESCE(os.office_settled, '') != 'DONE'
             AND COALESCE(s.office_settled, '') != 'DONE'
             AND (
                (TRIM(COALESCE(os.imei, '')) != '' AND os.imei = s.imei)
                OR (TRIM(COALESCE(os.vehicle_no, '')) != '' AND os.vehicle_no = s.vehicle_no)
             )
            SET os.sales_log_id = s.id");

        $conn->exec("UPDATE office_renewal o
            JOIN renewal_log r
              ON COALESCE(o.renewal_log_id, 0) = 0
             AND COALESCE(o.office_settled, '') != 'DONE'
             AND COALESCE(r.office_settled, '') != 'DONE'
             AND o.vehicle_no = r.vehicle_no
             AND o.software = r.software
            SET o.renewal_log_id = r.id");

        $stmtI = $conn->query("SELECT imei FROM office_sales WHERE COALESCE(office_settled, '') != 'DONE'");
        $imeis = $stmtI->fetchAll(PDO::FETCH_COLUMN);
        $salesIds = $conn->query("SELECT sales_log_id FROM office_sales WHERE COALESCE(office_settled, '') != 'DONE' AND sales_log_id IS NOT NULL")->fetchAll(PDO::FETCH_COLUMN);
        $renewalIds = $conn->query("SELECT renewal_log_id FROM office_renewal WHERE COALESCE(office_settled, '') != 'DONE' AND renewal_log_id IS NOT NULL")->fetchAll(PDO::FETCH_COLUMN);
        $legacySalesRows = $conn->query("SELECT vehicle_no, imei FROM office_sales WHERE COALESCE(office_settled, '') != 'DONE' AND sales_log_id IS NULL")->fetchAll(PDO::FETCH_ASSOC);
        $legacyRenewalRows = $conn->query("SELECT vehicle_no, software FROM office_renewal WHERE COALESCE(office_settled, '') != 'DONE' AND renewal_log_id IS NULL")->fetchAll(PDO::FETCH_ASSOC);

        $salesAmount = (float)($conn->query("SELECT COALESCE(SUM(relay_rate + device_rate + software_rate), 0) FROM office_sales WHERE COALESCE(office_settled, '') != 'DONE'")->fetchColumn() ?: 0);
        $renewalAmount = (float)($conn->query("SELECT COALESCE(SUM(renewal_amount), 0) FROM office_renewal WHERE COALESCE(office_settled, '') != 'DONE'")->fetchColumn() ?: 0);

        $conn->exec("UPDATE office_sales SET office_settled = 'DONE', txn_id = " . $conn->quote($txn) . " WHERE COALESCE(office_settled, '') != 'DONE'");
        $conn->exec("UPDATE office_renewal SET office_settled = 'DONE', txn_id = " . $conn->quote($txn) . " WHERE COALESCE(office_settled, '') != 'DONE'");

        if (!empty($salesIds)) {
            $salesIds = array_values(array_unique(array_filter(array_map('intval', $salesIds))));
            if (!empty($salesIds)) {
                $salesIdList = implode(',', $salesIds);
                $conn->exec("UPDATE sales_log
                    SET office_settled = 'DONE',
                        office_txn_id = " . $conn->quote($txn) . ",
                        office_processed = 'YES'
                    WHERE id IN ($salesIdList)");
            }
        }

        foreach ($legacySalesRows as $legacyRow) {
            $vehicleNo = trim((string)($legacyRow['vehicle_no'] ?? ''));
            $imei = trim((string)($legacyRow['imei'] ?? ''));
            if ($vehicleNo === '' && $imei === '') continue;

            $conditions = [];
            if ($imei !== '') {
                $conditions[] = "imei = " . $conn->quote($imei);
            }
            if ($vehicleNo !== '') {
                $conditions[] = "vehicle_no = " . $conn->quote($vehicleNo);
            }
            $conn->exec("UPDATE sales_log
                SET office_settled = 'DONE',
                    office_txn_id = " . $conn->quote($txn) . ",
                    office_processed = 'YES'
                WHERE COALESCE(office_settled, '') != 'DONE'
                  AND (" . implode(' OR ', $conditions) . ")");
        }

        if (!empty($renewalIds)) {
            $renewalIds = array_values(array_unique(array_filter(array_map('intval', $renewalIds))));
            if (!empty($renewalIds)) {
                $renewalIdList = implode(',', $renewalIds);
                $conn->exec("UPDATE renewal_log
                    SET office_settled = 'DONE',
                        office_txn_id = " . $conn->quote($txn) . ",
                        office_processed = 'YES'
                    WHERE id IN ($renewalIdList)");
            }
        }

        foreach ($legacyRenewalRows as $legacyRow) {
            $vehicleNo = trim((string)($legacyRow['vehicle_no'] ?? ''));
            $software = trim((string)($legacyRow['software'] ?? ''));
            if ($vehicleNo === '' || $software === '') continue;

            $conn->exec("UPDATE renewal_log
                SET office_settled = 'DONE',
                    office_txn_id = " . $conn->quote($txn) . ",
                    office_processed = 'YES'
                WHERE COALESCE(office_settled, '') != 'DONE'
                  AND vehicle_no = " . $conn->quote($vehicleNo) . "
                  AND software = " . $conn->quote($software));
        }

        if (!empty($imeis)) {
            $ilist = implode(',', array_map([$conn, 'quote'], $imeis));
            $conn->exec("UPDATE device_master SET office_txn_id = " . $conn->quote($txn) . " WHERE imei IN ($ilist)");
        }

        $conn->prepare("INSERT INTO office_settlement_log (txn_id, sales_amount, renewal_amount, total_amount) VALUES (?, ?, ?, ?)")
            ->execute([$txn, $salesAmount, $renewalAmount, $salesAmount + $renewalAmount]);

        $conn->commit();
        echo json_encode(['status' => 'payment completed', 'txn' => $txn]);

    } catch (Exception $e) {
        if ($conn->inTransaction()) $conn->rollBack();
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
}

function handle_history($conn) {
    try {
        $stmt = $conn->query("SELECT * FROM office_settlement_log ORDER BY id DESC LIMIT 50");
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo json_encode(['success' => true, 'history' => $rows]);
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'history' => [], 'error' => $e->getMessage()]);
    }
}

function handle_summary($conn) {
    try {
        // Aggregate totals from pending entries
        $deviceTotal = (float)($conn->query("SELECT COALESCE(SUM(device_rate), 0) FROM office_sales WHERE COALESCE(office_settled, '') != 'DONE'")->fetchColumn() ?: 0);
        $softwareTotalSales = (float)($conn->query("SELECT COALESCE(SUM(software_rate), 0) FROM office_sales WHERE COALESCE(office_settled, '') != 'DONE'")->fetchColumn() ?: 0);
        $relayTotal = (float)($conn->query("SELECT COALESCE(SUM(relay_rate), 0) FROM office_sales WHERE COALESCE(office_settled, '') != 'DONE'")->fetchColumn() ?: 0);
        $renewalTotalAmount = (float)($conn->query("SELECT COALESCE(SUM(renewal_amount), 0) FROM office_renewal WHERE COALESCE(office_settled, '') != 'DONE'")->fetchColumn() ?: 0);
        
        echo json_encode([
            'success' => true,
            'device_total' => $deviceTotal,
            'software_total' => $softwareTotalSales,
            'relay_total' => $relayTotal,
            'renewal_total' => $renewalTotalAmount,
            'sales_subtotal' => $deviceTotal + $softwareTotalSales + $relayTotal,
            'grand_total' => $deviceTotal + $softwareTotalSales + $relayTotal + $renewalTotalAmount
        ]);
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
}
