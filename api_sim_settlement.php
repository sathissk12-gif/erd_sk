<?php
/**
 * SIM Settlement API
 * Ported to match the Google Apps Script settlement flow.
 */
header('Content-Type: application/json');
include 'db_connect.php';

try {
    $conn->exec("CREATE TABLE IF NOT EXISTS sim_settlement_log (
        id INT AUTO_INCREMENT PRIMARY KEY,
        settle_date DATETIME DEFAULT CURRENT_TIMESTAMP,
        sales_amount DECIMAL(10,2) DEFAULT 0,
        sales_vehicles TEXT,
        renewal_amount DECIMAL(10,2) DEFAULT 0,
        renewal_vehicles TEXT,
        total_amount DECIMAL(10,2) DEFAULT 0,
        status VARCHAR(20) DEFAULT 'PENDING',
        txn_id VARCHAR(100)
    ) ENGINE=InnoDB");

    $conn->exec("CREATE TABLE IF NOT EXISTS sim_settlement (
        id INT AUTO_INCREMENT PRIMARY KEY,
        log_ref_id INT DEFAULT NULL,
        date DATE DEFAULT NULL,
        total_sale_amount DECIMAL(10,2) DEFAULT 0,
        ref TEXT,
        total_renewal_amount DECIMAL(10,2) DEFAULT 0,
        total_amount DECIMAL(10,2) DEFAULT 0,
        status TEXT,
        transection_id TEXT
    ) ENGINE=InnoDB");

    $simCols = $conn->query("DESCRIBE sim_settlement")->fetchAll(PDO::FETCH_COLUMN);
    if (!in_array('log_ref_id', $simCols)) {
        $conn->exec("ALTER TABLE sim_settlement ADD log_ref_id INT DEFAULT NULL");
    }
} catch (Exception $e) {}

$action = strtolower(trim($_GET['action'] ?? $_POST['action'] ?? ''));
$page = strtolower(trim($_GET['page'] ?? $_POST['page'] ?? ''));
$route = $action ?: $page;

switch ($route) {
    case 'summary':
        get_sim_summary($conn);
        break;
    case 'latest-report':
        get_latest_settlement_report($conn);
        break;
    case 'generate':
        generate_sim_settlement($conn);
        break;
    case 'confirm':
        confirm_sim_settlement($conn);
        break;
    case 'history':
        get_sim_history($conn);
        break;
    case 'breakdown':
        get_sim_breakdown($conn);
        break;
    default:
        echo json_encode(['success' => true, 'message' => 'SIM SETTLEMENT API RUNNING']);
        break;
}

function safe_sim_text($value) {
    return trim((string)($value ?? ''));
}

function to_sim_number($value) {
    return is_numeric($value) ? (float)$value : 0.0;
}

function round_sim_2($value) {
    return round((float)$value, 2);
}

function get_duration_multiplier_from_text($label) {
    $label = trim((string)$label);
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

function get_sim_rate($simType, $softwareLabel = '') {
    $simType = strtoupper(safe_sim_text($simType));
    $multiplier = get_duration_multiplier_from_text($softwareLabel);
    if ($simType === 'VOICE') return round_sim_2(570 * $multiplier);
    if ($simType === 'BASIC') return round_sim_2(500 * $multiplier);
    return 0;
}

function get_renewal_rate($row) {
    return 500;
}

function split_vehicle_list($value) {
    $items = array_map('trim', explode(',', (string)($value ?? '')));
    return array_values(array_filter($items, function ($item) {
        return $item !== '';
    }));
}

function build_vehicle_ref_text($salesVehicles, $renewalVehicles) {
    $parts = [];
    if (!empty($salesVehicles)) {
        $parts[] = "SALES: " . implode(', ', $salesVehicles);
    }
    if (!empty($renewalVehicles)) {
        $parts[] = "RENEWAL: " . implode(', ', $renewalVehicles);
    }
    return implode(' | ', $parts);
}

function build_report($row) {
    $salesVehicles = split_vehicle_list($row['sales_vehicles'] ?? '');
    $renewalVehicles = split_vehicle_list($row['renewal_vehicles'] ?? '');
    $salesLines = count($salesVehicles) ? implode("\n", array_map(function ($v) {
        return "- SALES: " . $v;
    }, $salesVehicles)) : "- SALES: None";
    $renewalLines = count($renewalVehicles) ? implode("\n", array_map(function ($v) {
        return "- RENEWAL: " . $v;
    }, $renewalVehicles)) : "- RENEWAL: None";

    $message = "*SK ENTERPRISES - SIM SETTLEMENT REPORT*\n\n" .
        "Settlement Date: " . date('d-M-Y h:i A', strtotime($row['settle_date'])) . "\n" .
        "Status: " . ($row['status'] ?? 'PENDING') . "\n" .
        "Transaction ID: " . (safe_sim_text($row['txn_id'] ?? '') ?: '-') . "\n\n" .
        "*Sales SIM Settlement*\n" .
        "Count: " . count($salesVehicles) . "\n" .
        "Amount: Rs. " . number_format(to_sim_number($row['sales_amount'] ?? 0), 2) . "\n" .
        "Vehicles: " . (count($salesVehicles) ? implode(', ', $salesVehicles) : "None") . "\n" .
        $salesLines . "\n\n" .
        "*Renewal SIM Settlement*\n" .
        "Count: " . count($renewalVehicles) . "\n" .
        "Amount: Rs. " . number_format(to_sim_number($row['renewal_amount'] ?? 0), 2) . "\n" .
        "Vehicles: " . (count($renewalVehicles) ? implode(', ', $renewalVehicles) : "None") . "\n" .
        $renewalLines . "\n\n" .
        "*Grand Total*\n" .
        "Amount: Rs. " . number_format(to_sim_number($row['total_amount'] ?? 0), 2) . "\n\n" .
        "Regards,\nSK ENTERPRISES";

    return [
        'date' => date('d-M-Y h:i A', strtotime($row['settle_date'] ?? 'now')),
        'salesAmount' => round_sim_2($row['sales_amount'] ?? 0),
        'renewalAmount' => round_sim_2($row['renewal_amount'] ?? 0),
        'totalAmount' => round_sim_2($row['total_amount'] ?? 0),
        'status' => $row['status'] ?? 'PENDING',
        'txnId' => $row['txn_id'] ?? '',
        'salesVehicles' => $salesVehicles,
        'renewalVehicles' => $renewalVehicles,
        'salesCount' => count($salesVehicles),
        'renewalCount' => count($renewalVehicles),
        'message' => $message,
        'whatsappUrl' => 'https://wa.me/?text=' . rawurlencode($message)
    ];
}

function upsert_legacy_sim_settlement($conn, $logId, $salesAmount, $renewalAmount, $totalAmount, $status, $txnId = '', $refText = '') {
    $stmt = $conn->prepare("SELECT id FROM sim_settlement WHERE log_ref_id = ? ORDER BY id DESC LIMIT 1");
    $stmt->execute([(int)$logId]);
    $existingId = $stmt->fetchColumn();

    if ($existingId) {
        $upd = $conn->prepare("UPDATE sim_settlement
            SET log_ref_id = ?,
                date = CURDATE(),
                total_sale_amount = ?,
                ref = ?,
                total_renewal_amount = ?,
                total_amount = ?,
                status = ?,
                transection_id = ?
            WHERE id = ?");
        $upd->execute([$logId, $salesAmount, $refText, $renewalAmount, $totalAmount, $status, $txnId, $existingId]);
        return (int)$existingId;
    }

    $ins = $conn->prepare("INSERT INTO sim_settlement
        (log_ref_id, date, total_sale_amount, ref, total_renewal_amount, total_amount, status, transection_id)
        VALUES (?, CURDATE(), ?, ?, ?, ?, ?, ?)");
    $ins->execute([$logId, $salesAmount, $refText, $renewalAmount, $totalAmount, $status, $txnId]);
    return (int)$conn->lastInsertId();
}

function get_latest_log_row($conn) {
    $stmt = $conn->query("SELECT * FROM sim_settlement_log ORDER BY id DESC LIMIT 1");
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    return $row ?: null;
}

function get_sales_pending_rows($conn) {
    $sql = "SELECT vehicle_no, sim_type, software
            FROM sales_log
            WHERE TRIM(COALESCE(sim_type, '')) != ''
              AND UPPER(TRIM(COALESCE(sim_settled, ''))) != 'YES'";
    return $conn->query($sql)->fetchAll(PDO::FETCH_ASSOC);
}

function get_renewal_pending_rows($conn) {
    $sql = "SELECT vehicle_no, amount, status, sim_settled
            FROM renewal_log
            WHERE UPPER(TRIM(COALESCE(status, ''))) = 'YES'
              AND UPPER(TRIM(COALESCE(sim_settled, ''))) != 'YES'";
    return $conn->query($sql)->fetchAll(PDO::FETCH_ASSOC);
}

function get_sim_summary($conn) {
    $salesRows = get_sales_pending_rows($conn);
    $renewalRows = get_renewal_pending_rows($conn);
    $latest = get_latest_log_row($conn);

    $salesAmount = 0;
    $renewalAmount = 0;
    $count = 0;

    foreach ($salesRows as $row) {
        $rate = get_sim_rate($row['sim_type'] ?? '', $row['software'] ?? '');
        if ($rate <= 0) continue;
        $salesAmount += $rate;
        $count++;
    }

    foreach ($renewalRows as $row) {
        $renewalAmount += get_renewal_rate($row);
        $count++;
    }

    echo json_encode([
        'success' => true,
        'sales' => round_sim_2($salesAmount),
        'renewal' => round_sim_2($renewalAmount),
        'total' => round_sim_2($salesAmount + $renewalAmount),
        'count' => $count,
        'salesCount' => count($salesRows),
        'renewalCount' => count($renewalRows),
        'latestStatus' => $latest ? ($latest['status'] ?? 'NONE') : 'NONE',
        'latestReport' => $latest ? build_report($latest) : null
    ]);
}

function generate_sim_settlement($conn) {
    $salesRows = get_sales_pending_rows($conn);
    $renewalRows = get_renewal_pending_rows($conn);

    $salesAmount = 0;
    $renewalAmount = 0;
    $salesVehicles = [];
    $renewalVehicles = [];

    foreach ($salesRows as $row) {
        $rate = get_sim_rate($row['sim_type'] ?? '', $row['software'] ?? '');
        if ($rate <= 0) continue;
        $salesAmount += $rate;
        $salesVehicles[] = safe_sim_text($row['vehicle_no'] ?? '');
    }

    foreach ($renewalRows as $row) {
        $renewalAmount += get_renewal_rate($row);
        $renewalVehicles[] = safe_sim_text($row['vehicle_no'] ?? '');
    }

    $totalAmount = round_sim_2($salesAmount + $renewalAmount);
    if ($totalAmount <= 0) {
        echo json_encode(['success' => false, 'status' => 'empty', 'message' => 'No pending records to settle']);
        return;
    }

    $stmt = $conn->prepare("INSERT INTO sim_settlement_log
        (sales_amount, sales_vehicles, renewal_amount, renewal_vehicles, total_amount, status, txn_id)
        VALUES (?, ?, ?, ?, ?, 'PENDING', '')");

    $ok = $stmt->execute([
        round_sim_2($salesAmount),
        implode(', ', array_filter($salesVehicles)),
        round_sim_2($renewalAmount),
        implode(', ', array_filter($renewalVehicles)),
        $totalAmount
    ]);

    if (!$ok) {
        echo json_encode(['success' => false, 'message' => 'Failed to generate settlement']);
        return;
    }

    $logId = (int)$conn->lastInsertId();
    $refText = build_vehicle_ref_text(array_values(array_filter($salesVehicles)), array_values(array_filter($renewalVehicles)));
    upsert_legacy_sim_settlement(
        $conn,
        $logId,
        round_sim_2($salesAmount),
        round_sim_2($renewalAmount),
        $totalAmount,
        'PENDING',
        '',
        $refText
    );

    $generated = get_latest_log_row($conn);

    echo json_encode([
        'success' => true,
        'status' => 'generated',
        'logId' => $logId,
        'salesVehicles' => count(array_filter($salesVehicles)),
        'renewalVehicles' => count(array_filter($renewalVehicles)),
        'amount' => $totalAmount,
        'report' => $generated ? build_report($generated) : null
    ]);
}

function confirm_sim_settlement($conn) {
    $txnId = safe_sim_text($_POST['txnId'] ?? $_POST['txnid'] ?? $_GET['txnId'] ?? '');
    if ($txnId === '') {
        echo json_encode(['success' => false, 'status' => 'no_txn', 'message' => 'Transaction ID is required']);
        return;
    }

    $latest = get_latest_log_row($conn);
    if (!$latest) {
        echo json_encode(['success' => false, 'status' => 'no_settlement', 'message' => 'Settlement row not found']);
        return;
    }

    $conn->beginTransaction();
    try {
        $conn->exec("UPDATE sales_log
            SET sim_settled = 'YES'
            WHERE TRIM(COALESCE(sim_type, '')) != ''
              AND UPPER(TRIM(COALESCE(sim_settled, ''))) != 'YES'");

        $conn->exec("UPDATE renewal_log
            SET sim_settled = 'YES'
            WHERE UPPER(TRIM(COALESCE(status, ''))) = 'YES'
              AND UPPER(TRIM(COALESCE(sim_settled, ''))) != 'YES'");

        $stmt = $conn->prepare("UPDATE sim_settlement_log SET status = 'DONE', txn_id = ? WHERE id = ?");
        $stmt->execute([$txnId, $latest['id']]);

        upsert_legacy_sim_settlement(
            $conn,
            (int)$latest['id'],
            round_sim_2($latest['sales_amount'] ?? 0),
            round_sim_2($latest['renewal_amount'] ?? 0),
            round_sim_2($latest['total_amount'] ?? 0),
            'DONE',
            $txnId,
            build_vehicle_ref_text(
                split_vehicle_list($latest['sales_vehicles'] ?? ''),
                split_vehicle_list($latest['renewal_vehicles'] ?? '')
            )
        );

        $conn->commit();
    } catch (Exception $e) {
        if ($conn->inTransaction()) {
            $conn->rollBack();
        }
        echo json_encode(['success' => false, 'status' => 'error', 'message' => $e->getMessage()]);
        return;
    }

    $updated = get_latest_log_row($conn);
    echo json_encode([
        'success' => true,
        'status' => 'done',
        'report' => build_report($updated)
    ]);
}

function get_latest_settlement_report($conn) {
    $latest = get_latest_log_row($conn);
    if (!$latest) {
        echo json_encode(['success' => false, 'message' => 'No settlement history found']);
        return;
    }

    echo json_encode([
        'success' => true,
        'report' => build_report($latest)
    ]);
}

function get_sim_history($conn) {
    try {
        $stmt = $conn->query("SELECT * FROM sim_settlement_log ORDER BY id DESC LIMIT 50");
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo json_encode(['success' => true, 'history' => $rows]);
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'history' => [], 'error' => $e->getMessage()]);
    }
}

function get_sim_breakdown($conn) {
    try {
        $salesRows = get_sales_pending_rows($conn);
        $renewalRows = get_renewal_pending_rows($conn);
        
        $voiceTotal = 0;
        $basicTotal = 0;
        $voiceCount = 0;
        $basicCount = 0;
        
        foreach ($salesRows as $row) {
            $rate = get_sim_rate($row['sim_type'] ?? '', $row['software'] ?? '');
            $simType = strtoupper(safe_sim_text($row['sim_type'] ?? ''));
            if ($rate <= 0) continue;
            if ($simType === 'VOICE') { $voiceTotal += $rate; $voiceCount++; }
            elseif ($simType === 'BASIC') { $basicTotal += $rate; $basicCount++; }
        }
        
        echo json_encode([
            'success' => true,
            'voice_total' => round_sim_2($voiceTotal),
            'voice_count' => $voiceCount,
            'basic_total' => round_sim_2($basicTotal),
            'basic_count' => $basicCount,
            'renewal_count' => count($renewalRows),
            'renewal_total' => round_sim_2(count($renewalRows) * 500)
        ]);
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
}
?>
