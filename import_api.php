<?php
/**
 * Generic JSON Import API
 * Handles batch inserts/updates from Google Apps Script
 * Also auto-adds missing columns so full sheet columns can sync into DB.
 */
include 'db_connect.php';
header('Content-Type: application/json');

$input = file_get_contents('php://input');
$json = json_decode($input, true);

if (!$json || !isset($json['table']) || !isset($json['data'])) {
    echo json_encode(['success' => false, 'message' => 'Invalid Input Structure']);
    exit;
}

function sanitizeColumnName($name) {
    $name = strtolower(trim((string)$name));
    $name = preg_replace('/\s+/', '_', $name);
    return preg_replace('/[^a-z0-9_]/', '', $name);
}

function getExistingColumns(PDO $conn, $table) {
    $stmt = $conn->query("DESCRIBE `$table`");
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $columns = [];
    foreach ($rows as $row) {
        $columns[$row['Field']] = strtolower($row['Type']);
    }
    return $columns;
}

function inferColumnType(array $values) {
    $hasValue = false;
    $allInts = true;
    $allNumeric = true;
    $allDates = true;
    $maxLen = 0;

    foreach ($values as $value) {
        if ($value === null || $value === '') {
            continue;
        }

        $hasValue = true;
        $stringValue = trim((string)$value);
        $maxLen = max($maxLen, strlen($stringValue));

        if (!preg_match('/^-?\d+$/', $stringValue)) {
            $allInts = false;
        }
        if (!is_numeric($stringValue)) {
            $allNumeric = false;
        }
        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $stringValue)) {
            $allDates = false;
        }
    }

    if (!$hasValue) {
        return 'TEXT NULL';
    }
    if ($allDates) {
        return 'DATE NULL';
    }
    if ($allInts) {
        return 'BIGINT NULL';
    }
    if ($allNumeric) {
        return 'DECIMAL(15,2) NULL';
    }
    if ($maxLen <= 100) {
        return 'VARCHAR(100) NULL';
    }
    if ($maxLen <= 255) {
        return 'VARCHAR(255) NULL';
    }
    return 'TEXT NULL';
}

$table = preg_replace('/[^a-zA-Z0-9_]/', '', $json['table']);
$data = $json['data'];

if (empty($data)) {
    echo json_encode(['success' => true, 'message' => 'No data to import']);
    exit;
}

try {
    $conn->beginTransaction();

    $existingColumns = getExistingColumns($conn, $table);
    $allIncomingColumns = [];
    foreach ($data as $row) {
        foreach ($row as $col => $val) {
            $safeCol = sanitizeColumnName($col);
            if ($safeCol !== '') {
                $allIncomingColumns[$safeCol][] = $val;
            }
        }
    }

    $addedColumns = [];
    foreach ($allIncomingColumns as $col => $values) {
        if (!isset($existingColumns[$col])) {
            $type = inferColumnType($values);
            $conn->exec("ALTER TABLE `$table` ADD `$col` $type");
            $existingColumns[$col] = strtolower($type);
            $addedColumns[] = $col;
        }
    }

    $cachedStmt = null;
    $cachedSql = '';
    $successCount = 0;

    foreach ($data as $row) {
        $cleanRow = [];
        foreach ($row as $col => $val) {
            $safeCol = sanitizeColumnName($col);
            if ($safeCol !== '' && isset($existingColumns[$safeCol])) {
                $cleanRow[$safeCol] = $val;
            }
        }

        $columns = array_keys($cleanRow);
        
        // 🔑 Auto-Generate UID for new rows if table supports it but data is missing
        if (in_array('uid', array_keys($existingColumns)) && (empty($cleanRow['uid']) || $cleanRow['uid'] == '-')) {
            $cleanRow['uid'] = bin2hex(random_bytes(6)); // 12-char unique hex
            if (!in_array('uid', $columns)) $columns[] = 'uid';
        }

        if (empty($cleanRow)) {
            continue;
        }

        $columns = array_keys($cleanRow);
        $placeholders = array_fill(0, count($columns), '?');
        $updateParts = [];
        foreach ($columns as $col) {
            $updateParts[] = "`$col` = VALUES(`$col`)";
        }

        $sql = "INSERT INTO `$table` (`" . implode('`, `', $columns) . "`) VALUES (" . implode(', ', $placeholders) . ")
                ON DUPLICATE KEY UPDATE " . implode(', ', $updateParts);

        if ($sql !== $cachedSql) {
            $cachedSql = $sql;
            $cachedStmt = $conn->prepare($sql);
        }

        $cachedStmt->execute(array_values($cleanRow));
        $successCount++;
    }

    $conn->commit();
    $message = $successCount . ' records imported to ' . $table;
    if ($addedColumns) {
        $message .= ' | Added columns: ' . implode(', ', $addedColumns);
    }
    echo json_encode(['success' => true, 'message' => $message]);
} catch (Exception $e) {
    if ($conn->inTransaction()) {
        $conn->rollBack();
    }
    echo json_encode(['success' => false, 'message' => 'Database Error: ' . $e->getMessage()]);
}
?>
