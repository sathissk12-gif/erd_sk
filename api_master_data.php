<?php
// Modern Master Data API v4.0
include 'db_connect.php';
header('Content-Type: application/json');

$action = $_REQUEST['action'] ?? '';

switch($action) {
    case 'get_inventory_config':
        try {
            // Get all models from price_master (buy rate list)
            $stmt = $conn->query("SELECT name as device_model, cost as rate FROM price_master WHERE type = 'DEVICE' OR type IS NULL ORDER BY name ASC");
            $models = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // Get Next SL number
            $stmtSl = $conn->query("SELECT sl_no FROM device_master ORDER BY CAST(sl_no AS UNSIGNED) DESC LIMIT 1");
            $lastSlRow = $stmtSl->fetch(PDO::FETCH_ASSOC);
            $nextSl = $lastSlRow ? ((int)$lastSlRow['sl_no'] + 1) : 1;

            echo json_encode(['status' => 'success', 'models' => $models, 'next_sl' => $nextSl]);
        } catch (Exception $e) { echo json_encode(['error' => $e->getMessage()]); }
        break;

    case 'add_inventory_stock':
        $supplier = strtoupper(trim($_POST['supplier'] ?? ''));
        $model = trim($_POST['model'] ?? '');
        $rate = (float)($_POST['rate'] ?? 0);
        $imeis_raw = $_POST['imeis'] ?? '';

        if (!$supplier || !$model || !$imeis_raw) {
            echo json_encode(['status' => 'error', 'error' => 'Please provide Supplier, Model, and IMEIs']);
            exit;
        }

        $imeis = array_unique(array_filter(array_map('trim', preg_split('/[\n,]+/', $imeis_raw))));
        
        try {
            $conn->beginTransaction();

            // Resilient SL Number calculation
            $currentSl = 0;
            try {
                $maxSlRes = $conn->query("SELECT sl_no FROM device_master ORDER BY CAST(sl_no AS UNSIGNED) DESC LIMIT 1")->fetch();
                if($maxSlRes) $currentSl = (int)$maxSlRes['sl_no'];
            } catch (Exception $e) {
                // If sl_no column logic fails, check if id exists
                try { $currentSl = (int)$conn->query("SELECT COUNT(*) FROM device_master")->fetchColumn(); } catch(Exception $e2) {}
            }

            // Using INSERT IGNORE to skip duplicate IMEIs without crashing
            $stmt = $conn->prepare("INSERT IGNORE INTO device_master (sl_no, imei, supplier_name, device_model, rate, status, date) 
                                   VALUES (?, ?, ?, ?, ?, 'In Stock', CURDATE())");

            $addedCount = 0;
            foreach ($imeis as $imei) {
                if(!$imei) continue;
                $currentSl++;
                $stmt->execute([$currentSl, $imei, $supplier, $model, $rate]);
                if($stmt->rowCount() > 0) $addedCount++;
            }

            $conn->commit();
            
            if ($addedCount > 0) {
                echo json_encode(['status' => 'success', 'message' => "Successfully added $addedCount devices. " . (count($imeis) - $addedCount) . " duplicates were skipped."]);
            } else {
                echo json_encode(['status' => 'error', 'error' => "No new devices added. All IMEIs might already exist in stock."]);
            }
        } catch (Exception $e) {
            if ($conn->inTransaction()) $conn->rollBack();
            echo json_encode(['status' => 'error', 'error' => "System Error: " . $e->getMessage()]);
        }
        break;

    case 'get_live_stock':
        try {
            // Merge both sources by case-insensitive name and sum qty
            $aggregated = [];

            // 1️⃣ Device Master (hardware with IMEI)
            try {
                $stmtDev = $conn->query("SELECT device_model as name, COUNT(*) as qty, 'DEVICE' as type FROM device_master WHERE status = 'In Stock' GROUP BY device_model");
                while ($row = $stmtDev->fetch(PDO::FETCH_ASSOC)) {
                    $key = strtolower(trim($row['name']));
                    if (!isset($aggregated[$key])) {
                        $aggregated[$key] = ['name' => trim($row['name']), 'qty' => 0, 'type' => 'DEVICE'];
                    }
                    $aggregated[$key]['qty'] += (int)$row['qty'];
                }
            } catch (Exception $e) { /* skip */ }

            // 2️⃣ Stock Ledger (software/relay/tools)
            try {
                $stmtL = $conn->query("SELECT item_name as name, SUM(CAST(qty AS SIGNED)) as qty, item_type as type FROM stock_ledger GROUP BY item_name, item_type");
                while ($row = $stmtL->fetch(PDO::FETCH_ASSOC)) {
                    $name = trim($row['name']);
                    if (!$name) continue;
                    $key = strtolower($name);
                    $qty = (int)$row['qty'];

                    if (!isset($aggregated[$key])) {
                        // New item — use its type directly
                        $aggregated[$key] = ['name' => $name, 'qty' => $qty, 'type' => $row['type'] ?: 'SOFTWARE'];
                    } else {
                        // Already exists (from device_master) — merge qty
                        $aggregated[$key]['qty'] += $qty;
                        // Mark as MIXED since it exists in both sources
                        $aggregated[$key]['type'] = 'MIXED';
                    }
                }
            } catch (Exception $e) { /* skip */ }

            if (empty($aggregated)) {
                echo json_encode([]);
            } else {
                // Sort by name alphabetically
                usort($aggregated, function($a, $b) {
                    return strcasecmp($a['name'], $b['name']);
                });
                // Remove items with zero or negative qty
                $aggregated = array_values(array_filter($aggregated, function($item) {
                    return $item['qty'] > 0;
                }));
                echo json_encode($aggregated);
            }
        } catch (Exception $e) {
            echo json_encode([]);
        }
        break;

    case 'get_software_list':
        try {
            $stmt = $conn->query("SELECT name, type FROM price_master WHERE type IN ('SOFTWARE', 'RELAY', 'TOOL') ORDER BY type ASC, name ASC");
            echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
        } catch (Exception $e) { echo json_encode([]); }
        break;

    case 'add_software_stock':
        $name = trim($_POST['name'] ?? '');
        $qty = (int)($_POST['qty'] ?? 0);
        $itemType = strtoupper(trim($_POST['item_type'] ?? ''));
        try {
            if (!$name || $qty <= 0) {
                throw new Exception('Name and valid quantity are required');
            }
            if (!in_array($itemType, ['SOFTWARE', 'RELAY', 'TOOL'], true)) {
                $stmtType = $conn->prepare("SELECT type FROM price_master WHERE name = ? AND type IN ('SOFTWARE', 'RELAY', 'TOOL') LIMIT 1");
                $stmtType->execute([$name]);
                $itemType = strtoupper($stmtType->fetchColumn() ?: 'SOFTWARE');
            }
            $stmt = $conn->prepare("INSERT INTO stock_ledger (item_name, qty, item_type, date) VALUES (?, ?, ?, CURDATE())");
            $stmt->execute([$name, $qty, $itemType]);
            echo json_encode(['status' => 'success']);
        } catch (Exception $e) { echo json_encode(['status' => 'error', 'error' => $e->getMessage()]); }
        break;

    case 'get_customer_names':
        try {
            // Try customerdatas table first
            $stmt = $conn->query("SELECT name, mobile, location FROM customerdatas ORDER BY name ASC");
            echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
        } catch (Exception $e) { echo json_encode([]); }
        break;

    case 'add_customer':
        $name = trim($_POST['name'] ?? '');
        $mobile = trim($_POST['mobile'] ?? '');
        $location = trim($_POST['location'] ?? '');

        if (!$name) {
            echo json_encode(['status' => 'error', 'message' => 'Customer name is required']);
            exit;
        }

        try {
            $stmt = $conn->prepare("INSERT INTO customerdatas (name, mobile, location) VALUES (?, ?, ?)");
            $stmt->execute([$name, $mobile, $location]);
            echo json_encode(['status' => 'success', 'message' => 'Customer registered successfully']);
        } catch (Exception $e) {
            echo json_encode(['status' => 'error', 'message' => 'Error adding customer: ' . $e->getMessage()]);
        }
        break;

    case 'get_stock_count':
        try {
            $count = $conn->query("SELECT COUNT(*) FROM device_master WHERE status = 'In Stock'")->fetchColumn();
            echo json_encode(['total' => (int)$count]);
        } catch (Exception $e) { echo json_encode(['total' => 0]); }
        break;

    case 'get_data':
        $table = $_GET['table'] ?? '';
        if(!$table) { echo json_encode([]); exit; }
        try {
            // Try ordering by ID first
            try {
                $stmt = $conn->query("SELECT * FROM `$table` ORDER BY id DESC LIMIT 2000");
            } catch (Exception $e) {
                // Fallback for tables without 'id' column
                $stmt = $conn->query("SELECT * FROM `$table` LIMIT 2000");
            }
            $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
            $cols = [];
            if(count($data) > 0) $cols = array_keys($data[0]);
            else {
                $q = $conn->prepare("DESCRIBE `$table`"); $q->execute();
                $cols = $q->fetchAll(PDO::FETCH_COLUMN);
            }
            echo json_encode(['data' => $data, 'columns' => $cols]);
        } catch (Exception $e) { echo json_encode(['error' => $e->getMessage(), 'data' => [], 'columns' => []]); }
        break;

    case 'update_cell':
        $table = $_POST['table'] ?? '';
        $id = $_POST['id'] ?? '';
        $col = $_POST['column'] ?? '';
        $val = $_POST['value'] ?? '';
        try {
            $conn->prepare("UPDATE `$table` SET `$col` = ? WHERE id = ?")->execute([$val, $id]);
            echo json_encode(['status' => 'success']);
        } catch (Exception $e) { echo json_encode(['error' => $e->getMessage()]); }
        break;

    case 'save_full_row':
        $table = $_POST['table'] ?? '';
        if (!$table) { echo json_encode(['status' => 'error', 'error' => 'Table required']); exit; }
        try {
            // Get all form fields except action and table
            $fields = $_POST;
            unset($fields['action'], $fields['table']);
            
            if (empty($fields)) { echo json_encode(['status' => 'error', 'error' => 'No data provided']); exit; }
            
            $cols = array_keys($fields);
            $vals = array_values($fields);
            $placeholders = implode(',', array_fill(0, count($cols), '?'));
            $colList = '`' . implode('`, `', $cols) . '`';
            
            $stmt = $conn->prepare("INSERT INTO `$table` ($colList) VALUES ($placeholders)");
            $stmt->execute($vals);
            
            echo json_encode(['status' => 'success', 'id' => $conn->lastInsertId()]);
        } catch (Exception $e) { echo json_encode(['status' => 'error', 'error' => $e->getMessage()]); }
        break;

    case 'delete_row':
        $table = $_POST['table'] ?? '';
        $ids = $_POST['ids'] ?? '';
        try {
            // Support comma-separated IDs for bulk delete
            $idArray = array_map('trim', explode(',', $ids));
            $idArray = array_filter($idArray, fn($v) => is_numeric($v));
            if (empty($idArray)) throw new Exception('No valid IDs provided');
            $placeholders = implode(',', array_fill(0, count($idArray), '?'));
            $stmt = $conn->prepare("DELETE FROM `$table` WHERE id IN ($placeholders)");
            $stmt->execute($idArray);
            echo json_encode(['status' => 'success', 'deleted' => $stmt->rowCount()]);
        } catch (Exception $e) { echo json_encode(['error' => $e->getMessage()]); }
        break;

    case 'batch_update':
        $table = $_POST['table'] ?? '';
        $ids = $_POST['ids'] ?? '';
        $column = $_POST['column'] ?? '';
        $value = $_POST['value'] ?? '';
        try {
            $idArray = array_map('trim', explode(',', $ids));
            $idArray = array_filter($idArray, fn($v) => is_numeric($v));
            if (empty($idArray)) throw new Exception('No valid IDs provided');
            if (!$column) throw new Exception('Column name required');
            $placeholders = implode(',', array_fill(0, count($idArray), '?'));
            $stmt = $conn->prepare("UPDATE `$table` SET `$column` = ? WHERE id IN ($placeholders)");
            $stmt->execute(array_merge([$value], $idArray));
            echo json_encode(['status' => 'success', 'updated' => $stmt->rowCount()]);
        } catch (Exception $e) { echo json_encode(['error' => $e->getMessage()]); }
        break;

    case 'duplicate_row':
        $table = $_POST['table'] ?? '';
        $ids = $_POST['ids'] ?? '';
        try {
            $idArray = array_map('trim', explode(',', $ids));
            $idArray = array_filter($idArray, fn($v) => is_numeric($v));
            if (empty($idArray)) throw new Exception('No valid IDs provided');
            
            // Get columns (exclude auto-increment id)
            $cols = $conn->query("DESCRIBE `$table`")->fetchAll(PDO::FETCH_ASSOC);
            $colNames = [];
            $skipCols = ['id']; // Skip auto-increment primary key
            foreach ($cols as $col) {
                $name = $col['Field'];
                if (in_array($name, $skipCols)) continue;
                // Skip columns with auto_increment
                if (strpos($col['Extra'] ?? '', 'auto_increment') !== false) continue;
                $colNames[] = $name;
            }
            
            if (empty($colNames)) throw new Exception('No columns to duplicate');
            
            $placeholders = implode(',', array_fill(0, count($idArray), '?'));
            $selectCols = '`' . implode('`, `', $colNames) . '`';
            
            // Fetch source records
            $stmt = $conn->prepare("SELECT $selectCols FROM `$table` WHERE id IN ($placeholders)");
            $stmt->execute($idArray);
            $sourceRows = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            if (empty($sourceRows)) throw new Exception('No records found to duplicate');
            
            $insertCols = '`' . implode('`, `', $colNames) . '`';
            $insertPlaceholders = implode(',', array_fill(0, count($colNames), '?'));
            $insertStmt = $conn->prepare("INSERT INTO `$table` ($insertCols) VALUES ($insertPlaceholders)");
            
            $duplicated = 0;
            foreach ($sourceRows as $row) {
                $values = [];
                foreach ($colNames as $cn) {
                    $values[] = $row[$cn] ?? '';
                }
                $insertStmt->execute($values);
                $duplicated++;
            }
            
            echo json_encode(['status' => 'success', 'duplicated' => $duplicated]);
        } catch (Exception $e) { echo json_encode(['error' => $e->getMessage()]); }
        break;

    case 'add_column':
        $table = $_POST['table'] ?? '';
        $name = $_POST['name'] ?? '';
        try {
            $conn->exec("ALTER TABLE `$table` ADD `$name` TEXT");
            echo json_encode(['status' => 'success']);
        } catch (Exception $e) { echo json_encode(['error' => $e->getMessage()]); }
        break;

    case 'update_price_master':
        $name = trim($_POST['model'] ?? $_POST['name'] ?? '');
        $rate = (float)($_POST['rate'] ?? $_POST['cost'] ?? 0);
        $type = strtoupper($_POST['type'] ?? 'DEVICE');

        if (!$name) {
            echo json_encode(['status' => 'error', 'error' => 'Name/Model is required']);
            exit;
        }

        try {
            // Check if exists
            $stmt = $conn->prepare("SELECT id FROM price_master WHERE name = ? AND type = ? LIMIT 1");
            $stmt->execute([$name, $type]);
            $exists = $stmt->fetch();

            if ($exists) {
                $upd = $conn->prepare("UPDATE price_master SET cost = ? WHERE id = ?");
                $upd->execute([$rate, $exists['id']]);
            } else {
                $ins = $conn->prepare("INSERT INTO price_master (name, cost, type) VALUES (?, ?, ?)");
                $ins->execute([$name, $rate, $type]);
            }
            echo json_encode(['status' => 'success']);
        } catch (Exception $e) { echo json_encode(['status' => 'error', 'error' => $e->getMessage()]); }
        break;

    case 'import_csv':
        $table = $_POST['table'] ?? '';
        if (!$table || !isset($_FILES['file'])) {
            echo json_encode(['status' => 'error', 'error' => 'Table and File required']);
            exit;
        }
        try {
            $file = $_FILES['file']['tmp_name'];
            $handle = fopen($file, "r");
            $headers = fgetcsv($handle);
            if (!$headers) throw new Exception("Invalid CSV format");

            $conn->beginTransaction();
            $successCount = 0;
            $skippedCount = 0;
            
            // Get valid columns and sanitize them for comparison
            $stmtCols = $conn->query("DESCRIBE `$table` ");
            $validCols = $stmtCols->fetchAll(PDO::FETCH_COLUMN);
            $cleanValidCols = array_map(function($c) {
                return strtolower(trim($c));
            }, $validCols);

            while (($row = fgetcsv($handle)) !== FALSE) {
                // Skip empty or malformed rows
                if (empty($row) || (count($row) == 1 && $row[0] === null)) continue;
                
                if (count($row) !== count($headers)) {
                    $skippedCount++;
                    continue;
                }
                
                $data = array_combine($headers, $row);
                if (!$data) {
                    $skippedCount++;
                    continue;
                }

                $finalData = [];
                foreach($data as $k => $v) { 
                    // Sanitize the incoming header key
                    $cleanK = strtolower(trim((string)$k));
                    $cleanK = preg_replace('/^\xEF\xBB\xBF/', '', $cleanK); // Remove BOM if present
                    $cleanK = preg_replace('/\s+/', '_', $cleanK);
                    $cleanK = preg_replace('/[^a-z0-9_]/', '', $cleanK);

                    // Find matching column in DB
                    if (in_array($cleanK, $cleanValidCols)) {
                        $finalData[$cleanK] = $v;
                    }
                }

                $id = $finalData['id'] ?? null;
                unset($finalData['id']); // Don't try to update the ID column directly if it's auto-increment

                // 🔑 Auto-Generate UID for new rows if table supports it
                if (in_array('uid', $cleanValidCols) && (empty($finalData['uid']) || $finalData['uid'] == '-')) {
                    $finalData['uid'] = bin2hex(random_bytes(6)); // 12-char unique hex
                }

                if ($id && is_numeric($id)) {
                    // UPDATE existing record
                    $sets = []; $vals = [];
                    foreach($finalData as $k => $v) { $sets[] = "`$k` = ?"; $vals[] = $v; }
                    $vals[] = $id;
                    $sql = "UPDATE `$table` SET " . implode(', ', $sets) . " WHERE id = ?";
                    $conn->prepare($sql)->execute($vals);
                } else {
                    // INSERT new record
                    if (empty($finalData)) { $skippedCount++; continue; }
                    $cols = array_keys($finalData);
                    $placeholders = str_repeat('?,', count($cols)-1) . '?';
                    $sql = "INSERT INTO `$table` (`" . implode('`, `', $cols) . "`) VALUES ($placeholders)";
                    $conn->prepare($sql)->execute(array_values($finalData));
                }
                $successCount++;
            }
            $conn->commit();
            fclose($handle);
            $msg = "Imported $successCount records successfully.";
            if($skippedCount > 0) $msg .= " Skipped $skippedCount rows (Header mismatch or missing columns).";
            echo json_encode(['status' => 'success', 'message' => $msg]);
        } catch (Exception $e) {
            if($conn->inTransaction()) $conn->rollBack();
            echo json_encode(['status' => 'error', 'error' => $e->getMessage()]);
        }
        break;

    case 'list_tables':
        try {
            $st = $conn->query("SHOW TABLES");
            $tables = $st->fetchAll(PDO::FETCH_COLUMN);
            echo json_encode(['status' => 'success', 'tables' => $tables]);
        } catch(Exception $e) {
            echo json_encode(['status' => 'error', 'error' => $e->getMessage()]);
        }
        break;

    case 'get_settings':
        try {
            $stmt = $conn->query("SELECT * FROM settings LIMIT 1");
            echo json_encode($stmt->fetch(PDO::FETCH_ASSOC));
        } catch (Exception $e) { echo json_encode([]); }
        break;

    case 'get_imei_trace':
        // 🔍 Full IMEI Trace: History from stock → sale → returns
        try {
            $imei = trim($_GET['imei'] ?? '');
            if (!$imei) { echo json_encode(['status' => 'error', 'error' => 'IMEI required']); exit; }

            // Sanitize: remove spaces/dashes for matching
            $searchImei = str_replace([' ', '-'], '', $imei);

            // 1️⃣ Device Master Info - Check which columns exist
            $colCheck = $conn->query("SHOW COLUMNS FROM device_master LIKE 'imei_no'");
            $hasImeiNo = $colCheck->rowCount() > 0;
            
            $whereClause = "REPLACE(REPLACE(imei, ' ', ''), '-', '') = ?";
            $params = [$searchImei];
            if ($hasImeiNo) {
                $whereClause .= " OR REPLACE(REPLACE(imei_no, ' ', ''), '-', '') = ?";
                $params[] = $searchImei;
            }
            
            $stmt = $conn->prepare("SELECT * FROM device_master WHERE $whereClause LIMIT 1");
            $stmt->execute($params);
            $device = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$device) {
                echo json_encode(['status' => 'error', 'error' => 'IMEI not found in stock records']);
                exit;
            }

            // 2️⃣ Sales History
            $stmt = $conn->prepare("SELECT * FROM sales_log WHERE imei = ? ORDER BY id DESC");
            $stmt->execute([$imei]);
            $sales = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // 3️⃣ Invoice History
            $stmt = $conn->prepare("SELECT * FROM invoice_log WHERE imei = ? ORDER BY id DESC");
            $stmt->execute([$imei]);
            $invoices = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // 4️⃣ Return/Replacement History
            $stmt = $conn->prepare("SELECT * FROM device_returns WHERE imei = ? OR new_imei = ? ORDER BY id DESC");
            $stmt->execute([$imei, $imei]);
            $returns = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // 5️⃣ Stock Ledger entries (if any) - safely check columns
            try {
                $ledgerCols = $conn->query("DESCRIBE stock_ledger")->fetchAll(PDO::FETCH_COLUMN);
                $hasVehicleCol = in_array('vehicle_no', $ledgerCols) || in_array('reference', $ledgerCols);
                $hasRemarkCol = in_array('remark', $ledgerCols);
                
                if ($hasVehicleCol && $hasRemarkCol) {
                    $refCol = in_array('vehicle_no', $ledgerCols) ? 'vehicle_no' : 'reference';
                    $stmt = $conn->prepare("SELECT * FROM stock_ledger WHERE $refCol IN (SELECT vehicle_no FROM sales_log WHERE imei = ?) OR remark LIKE ? ORDER BY id DESC LIMIT 10");
                    $stmt->execute([$imei, "%$imei%"]);
                } elseif ($hasRemarkCol) {
                    $stmt = $conn->prepare("SELECT * FROM stock_ledger WHERE remark LIKE ? ORDER BY id DESC LIMIT 10");
                    $stmt->execute(["%$imei%"]);
                } else {
                    $stmt = $conn->prepare("SELECT * FROM stock_ledger WHERE item_name = ? OR item_type = ? ORDER BY id DESC LIMIT 10");
                    $stmt->execute(['DEVICE', 'DEVICE']);
                }
                $ledger = $stmt->fetchAll(PDO::FETCH_ASSOC);
            } catch (Exception $e) {
                $ledger = [];
            }

            echo json_encode([
                'status' => 'success',
                'device' => $device,
                'sales' => $sales,
                'invoices' => $invoices,
                'returns' => $returns,
                'ledger' => $ledger
            ]);
        } catch (Exception $e) {
            echo json_encode(['status' => 'error', 'error' => $e->getMessage()]);
        }
        break;

    case 'check_imei':
        try {
            $imei_raw = trim($_GET['imei'] ?? '');
            $imei = str_replace([' ', '-'], '', $imei_raw);
            if (!$imei) { echo json_encode(['status' => 'error', 'error' => 'IMEI required']); exit; }
            
            // Check if imei_no column exists
            $colCheck = $conn->query("SHOW COLUMNS FROM device_master LIKE 'imei_no'");
            $hasImeiNo = $colCheck->rowCount() > 0;
            
            // Build query safely based on available columns
            $whereClause = "REPLACE(REPLACE(imei, ' ', ''), '-', '') = ?";
            if ($hasImeiNo) {
                $whereClause .= " OR REPLACE(REPLACE(imei_no, ' ', ''), '-', '') = ?";
            }
            
            $st = $conn->prepare("SELECT imei, device_model, status, holder, supplier_name, rate FROM device_master WHERE $whereClause LIMIT 1");
            if ($hasImeiNo) {
                $st->execute([$imei, $imei]);
            } else {
                $st->execute([$imei]);
            }
            $row = $st->fetch(PDO::FETCH_ASSOC);
            
            if ($row) {
                $row['branch'] = 'ERD';
                echo json_encode(['status' => 'success', 'data' => $row]);
            } else {
                echo json_encode(['status' => 'error', 'error' => 'Not found in ERD']);
            }
        } catch (Exception $e) {
            echo json_encode(['status' => 'error', 'error' => $e->getMessage()]);
        }
        break;

    case 'get_imeis_for_model':
        // 📋 List all IMEIs for a specific model
        try {
            $model = trim($_GET['model'] ?? '');
            $branch = strtoupper(trim($_GET['branch'] ?? 'ERD'));
            if (!$model) { echo json_encode(['ok' => false, 'error' => 'Model required']); break; }

            // 🔍 Detect IMEI column AND primary key column
            $cols = $conn->query("DESCRIBE device_master")->fetchAll(PDO::FETCH_COLUMN);
            $imeiCol = in_array('imei', $cols) ? 'imei' : (in_array('imei_no', $cols) ? 'imei_no' : 'imei');
            $pkCol = in_array('id', $cols) ? 'id' : (in_array('sl_no', $cols) ? 'sl_no' : 'id');

            if ($branch === 'SLM') {
                $slm_conn = new PDO("mysql:host=127.0.0.1;dbname=u182809524_slm;charset=utf8", "u182809524_slm", "S@kenterprises6198");
                $slmCols = $slm_conn->query("DESCRIBE device_master")->fetchAll(PDO::FETCH_COLUMN);
                $slmImeiCol = in_array('imei', $slmCols) ? 'imei' : (in_array('imei_no', $slmCols) ? 'imei_no' : 'imei');
                $slmPkCol = in_array('id', $slmCols) ? 'id' : 'sl_no';
                $stmt = $slm_conn->prepare("SELECT `$slmPkCol` as pk, `$slmImeiCol` as imei FROM device_master WHERE device_model = ? AND status = 'IN_STOCK' LIMIT 500");
                $stmt->execute([$model]);
            } else {
                $stmt = $conn->prepare("SELECT `$pkCol` as pk, `$imeiCol` as imei FROM device_master WHERE device_model = ? AND status = 'In Stock' LIMIT 500");
                $stmt->execute([$model]);
            }
            $imeis = $stmt->fetchAll(PDO::FETCH_ASSOC);
            echo json_encode(['ok' => true, 'imeis' => $imeis, 'count' => count($imeis)]);
        } catch (Exception $e) {
            echo json_encode(['ok' => false, 'error' => $e->getMessage()]);
        }
        break;

    // === STOCK TRANSFER ENDPOINTS (Cross-Branch) ===
    case 'stock-transfer-init':
        try {
            $result = ['ok' => true, 'erd_stock' => [], 'slm_stock' => [], 'history' => []];

            // ERD hardware stock
            try {
                $hw = $conn->query("SELECT device_model as name, COUNT(*) as qty FROM device_master WHERE status = 'In Stock' GROUP BY device_model")->fetchAll(PDO::FETCH_ASSOC);
                $result['erd_stock'] = array_merge($result['erd_stock'],
                    array_map(fn($i) => ['name'=>$i['name'], 'qty'=>(int)$i['qty'], 'type'=>'HARDWARE'], $hw));
            } catch (Exception $e) { $result['erd_hw_error'] = $e->getMessage(); }

            // ERD software stock
            try {
                $sw = $conn->query("SELECT item_name as name, SUM(qty) as qty FROM stock_ledger WHERE item_type IN ('SOFTWARE','RELAY','TOOL') GROUP BY item_name")->fetchAll(PDO::FETCH_ASSOC);
                $result['erd_stock'] = array_merge($result['erd_stock'],
                    array_map(fn($i) => ['name'=>$i['name'], 'qty'=>(int)$i['qty'], 'type'=>'SOFTWARE'], $sw));
            } catch (Exception $e) { $result['erd_sw_error'] = $e->getMessage(); }

            // SLM branch stock
            try {
                $slm_conn = new PDO("mysql:host=127.0.0.1;dbname=u182809524_slm;charset=utf8", "u182809524_slm", "S@kenterprises6198");
                $slm_hw = $slm_conn->query("SELECT device_model as name, COUNT(*) as qty FROM device_master WHERE status = 'IN_STOCK' GROUP BY device_model")->fetchAll(PDO::FETCH_ASSOC);
                $result['slm_stock'] = array_merge($result['slm_stock'],
                    array_map(fn($i) => ['name'=>$i['name'], 'qty'=>(int)$i['qty'], 'type'=>'HARDWARE'], $slm_hw));
                $slm_sw = $slm_conn->query("SELECT item_name as name, SUM(qty) as qty FROM stock_ledger WHERE item_type = 'SOFTWARE' GROUP BY item_name")->fetchAll(PDO::FETCH_ASSOC);
                $result['slm_stock'] = array_merge($result['slm_stock'],
                    array_map(fn($i) => ['name'=>$i['name'], 'qty'=>(int)$i['qty'], 'type'=>'SOFTWARE'], $slm_sw));
            } catch (Exception $e) { $result['slm_error'] = $e->getMessage(); }

            // Transfer history
            try {
                $stmt = $conn->query("SELECT * FROM stock_transfer_log ORDER BY id DESC LIMIT 50");
                $result['history'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
            } catch (Exception $e) { $result['history'] = []; }

            echo json_encode($result);
        } catch (Exception $e) {
            echo json_encode(['ok' => true, 'erd_stock' => [], 'slm_stock' => [], 'history' => [], 'error' => $e->getMessage()]);
        }
        break;

    case 'stock-transfer-execute':
        try {
            $direction = strtoupper(trim($_REQUEST['direction'] ?? ''));
            $itemName = trim($_REQUEST['item_name'] ?? '');
            $itemType = strtoupper(trim($_REQUEST['item_type'] ?? 'HARDWARE'));
            $qty = (int)($_REQUEST['qty'] ?? 0);
            $remark = trim($_REQUEST['remark'] ?? '');
            $selectedImeisStr = trim($_REQUEST['imeis'] ?? '');

            if (!$direction || !$itemName || $qty <= 0) {
                echo json_encode(['ok' => false, 'error' => 'Direction, item name and valid quantity required']);
                break;
            }
            if (!in_array($direction, ['SLM_TO_ERD', 'ERD_TO_SLM'])) {
                echo json_encode(['ok' => false, 'error' => 'Invalid direction']);
                break;
            }

            // 💰 Auto-fetch price from price_master
            $price = 0;
            try {
                $pStmt = $conn->prepare("SELECT cost FROM price_master WHERE name = ? AND (type = 'DEVICE' OR type IS NULL) LIMIT 1");
                $pStmt->execute([$itemName]);
                $price = (float)($pStmt->fetchColumn() ?: 0);
            } catch (Exception $e) { /* price optional */ }

            // 🔍 Detect columns for both DBs
            $erdCols = $conn->query("DESCRIBE device_master")->fetchAll(PDO::FETCH_COLUMN);
            $erdImeiCol = in_array('imei', $erdCols) ? 'imei' : (in_array('imei_no', $erdCols) ? 'imei_no' : 'imei');

            $slm_conn = new PDO("mysql:host=127.0.0.1;dbname=u182809524_slm;charset=utf8", "u182809524_slm", "S@kenterprises6198");
            $slm_conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $slmCols = $slm_conn->query("DESCRIBE device_master")->fetchAll(PDO::FETCH_COLUMN);
            $slmImeiCol = in_array('imei', $slmCols) ? 'imei' : (in_array('imei_no', $slmCols) ? 'imei_no' : 'imei');

            // Determine source/target DB & columns
            $isSlmToErd = ($direction === 'SLM_TO_ERD');
            $srcConn = $isSlmToErd ? $slm_conn : $conn;
            $srcImeiCol = $isSlmToErd ? $slmImeiCol : $erdImeiCol;
            $srcStatus = $isSlmToErd ? "'IN_STOCK'" : "'In Stock'";
            $dstCode = $isSlmToErd ? 'ERD_TRANSFER' : 'SLM_TRANSFER';

            // Ensure table exists
            $conn->exec("CREATE TABLE IF NOT EXISTS stock_transfer_log (
                id INT AUTO_INCREMENT PRIMARY KEY, direction VARCHAR(20) NOT NULL,
                item_name VARCHAR(200) NOT NULL, item_type VARCHAR(20) NOT NULL,
                qty INT NOT NULL, remark TEXT, status VARCHAR(20) DEFAULT 'DONE',
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP)");
            $slm_conn->exec("CREATE TABLE IF NOT EXISTS stock_transfer_log (
                id INT AUTO_INCREMENT PRIMARY KEY, direction VARCHAR(20) NOT NULL,
                item_name VARCHAR(200) NOT NULL, item_type VARCHAR(20) NOT NULL,
                qty INT NOT NULL, remark TEXT, status VARCHAR(20) DEFAULT 'DONE',
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP)");

            $conn->beginTransaction();

            if ($itemType === 'HARDWARE') {
                // 🔍 Get devices from source
                if ($selectedImeisStr) {
                    $imeiList = array_map('trim', explode(',', $selectedImeisStr));
                    $placeholders = implode(',', array_fill(0, count($imeiList), '?'));
                    $stmt = $srcConn->prepare("SELECT `$srcImeiCol` as imei FROM device_master WHERE device_model = ? AND status = $srcStatus AND `$srcImeiCol` IN ($placeholders)");
                    $stmt->execute(array_merge([$itemName], $imeiList));
                } else {
                    $stmt = $srcConn->prepare("SELECT `$srcImeiCol` as imei FROM device_master WHERE device_model = ? AND status = $srcStatus LIMIT " . (int)$qty);
                    $stmt->execute([$itemName]);
                }
                $devices = $stmt->fetchAll(PDO::FETCH_ASSOC);
                if (count($devices) < $qty) {
                    $conn->rollBack();
                    echo json_encode(['ok' => false, 'error' => "Only " . count($devices) . " units available"]);
                    break;
                }

                // ✅ UPDATE instead of DELETE: status='SOLD', holder='DESTINATION', rate=price
                // Check which columns exist for safe UPDATE
                $srcCols = $srcConn->query("DESCRIBE device_master")->fetchAll(PDO::FETCH_COLUMN);
                $hasRate = in_array('rate', $srcCols);
                $hasHolder = in_array('holder', $srcCols);
                $hasSoldDate = in_array('sold_date', $srcCols) || in_array('transfer_date', $srcCols) || in_array('issue_date', $srcCols);
                $soldDateCol = in_array('sold_date', $srcCols) ? 'sold_date' : (in_array('transfer_date', $srcCols) ? 'transfer_date' : (in_array('issue_date', $srcCols) ? 'issue_date' : null));

                $sql = "UPDATE device_master SET status = 'SOLD', holder = ?";
                $params = [$dstCode];
                if ($hasRate) { $sql .= ", rate = ?"; $params[] = $price; }
                if ($soldDateCol) { $sql .= ", `$soldDateCol` = CURDATE()"; }

                foreach ($devices as $d) {
                    $imeiParams = array_merge($params, [$d['imei']]);
                    $updStmt = $srcConn->prepare($sql . " WHERE `$srcImeiCol` = ?");
                    $updStmt->execute($imeiParams);
                }

                // 📝 Stock ledger entry in source DB
                $srcConn->prepare("INSERT INTO stock_ledger (date, item_type, item_name, qty, reference, remark) VALUES (CURDATE(), 'HARDWARE', ?, ?, 'STOCK_TRANSFER', ?)")
                    ->execute([$itemName, -$qty, 'Transferred to ' . ($isSlmToErd ? 'ERD' : 'SLM') . ': ' . $remark]);
            } else {
                // 💿 Software transfer (same as before - ledger only)
                if ($isSlmToErd) {
                    $availStmt = $slm_conn->prepare("SELECT COALESCE(SUM(qty), 0) FROM stock_ledger WHERE item_name = ? AND item_type = 'SOFTWARE'");
                } else {
                    $availStmt = $conn->prepare("SELECT COALESCE(SUM(qty), 0) FROM stock_ledger WHERE item_name = ? AND item_type IN ('SOFTWARE','RELAY','TOOL')");
                }
                $availStmt->execute([$itemName]);
                $avail = (int)$availStmt->fetchColumn();
                if ($avail < $qty) {
                    $conn->rollBack();
                    $branchName = $isSlmToErd ? 'SLM' : 'ERD';
                    echo json_encode(['ok' => false, 'error' => "Only $avail software units in $branchName"]);
                    break;
                }
                // Source: deduct
                $srcConn->prepare("INSERT INTO stock_ledger (date, item_type, item_name, qty, reference, remark) VALUES (CURDATE(), 'SOFTWARE', ?, ?, 'STOCK_TRANSFER', ?)")
                    ->execute([$itemName, -$qty, 'Transferred to ' . ($isSlmToErd ? 'ERD' : 'SLM') . ': ' . $remark]);
            }

            // 📝 Log transfer in both DBs
            $logData = [$direction, $itemName, $itemType, $qty, $remark];
            $conn->prepare("INSERT INTO stock_transfer_log (direction, item_name, item_type, qty, remark) VALUES (?, ?, ?, ?, ?)")->execute($logData);
            $slm_conn->prepare("INSERT INTO stock_transfer_log (direction, item_name, item_type, qty, remark) VALUES (?, ?, ?, ?, ?)")->execute($logData);

            $conn->commit();
            echo json_encode(['ok' => true, 'message' => "✅ $qty x $itemName transferred ($direction)" . ($price > 0 ? " @ ₹$price" : "")]);
        } catch (Exception $e) {
            if ($conn->inTransaction()) $conn->rollBack();
            echo json_encode(['ok' => false, 'error' => 'Transfer failed: ' . $e->getMessage()]);
        }
        break;

    case 'get_software_sales_detail':
        // 📦 Detailed sales/renewal info for a software item (clicked from live stock)
        try {
            $softwareName = trim($_GET['software_name'] ?? '');
            if (!$softwareName) {
                echo json_encode(['status' => 'error', 'error' => 'Software name required']);
                break;
            }

            // Detect columns in renewal_log (software vs software_type, received_amount vs amount)
            $colCheck = $conn->query("DESCRIBE renewal_log");
            $renewCols = $colCheck->fetchAll(PDO::FETCH_COLUMN);
            $swCol = in_array('software', $renewCols) ? 'software' : (in_array('software_type', $renewCols) ? 'software_type' : null);
            $amtCol = in_array('received_amount', $renewCols) ? 'received_amount' : (in_array('amount', $renewCols) ? 'amount' : null);

            // 1️⃣ Sales from invoice_log
            $stmt = $conn->prepare("SELECT invoice_no, invoice_date, customer_name, mobile_number, vehicle_no, total_amount, paid_amount FROM invoice_log WHERE software = ? ORDER BY invoice_date DESC");
            $stmt->execute([$softwareName]);
            $sales = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // 2️⃣ Renewals from renewal_log (with dynamic column detection)
            $renewals = [];
            if ($swCol) {
                $stmtR = $conn->prepare("SELECT * FROM renewal_log WHERE $swCol = ? ORDER BY date DESC");
                $stmtR->execute([$softwareName]);
                $renewals = $stmtR->fetchAll(PDO::FETCH_ASSOC);
            }

            // 3️⃣ Current stock info from stock_ledger
            $stmtStock = $conn->prepare("SELECT COALESCE(SUM(CAST(qty AS SIGNED)), 0) as current_stock FROM stock_ledger WHERE item_name = ?");
            $stmtStock->execute([$softwareName]);
            $stock = $stmtStock->fetch(PDO::FETCH_ASSOC);

            // Calculate sales totals
            $totalSales = count($sales);
            $totalRevenue = 0;
            $totalPaid = 0;
            foreach ($sales as $s) {
                $totalRevenue += (float)($s['total_amount'] ?? 0);
                $totalPaid += (float)($s['paid_amount'] ?? 0);
            }

            // Calculate renewal totals
            $totalRenewals = count($renewals);
            $totalRenewalAmt = 0;
            foreach ($renewals as $r) {
                $totalRenewalAmt += (float)(($r[$amtCol] ?? $r['amount'] ?? 0));
            }

            echo json_encode([
                'status' => 'success',
                'software_name' => $softwareName,
                'current_stock' => (int)($stock['current_stock'] ?? 0),
                'sales_count' => $totalSales,
                'sales_total_amount' => $totalRevenue,
                'sales_paid_amount' => $totalPaid,
                'sales' => $sales,
                'renewals_count' => $totalRenewals,
                'renewals_total' => $totalRenewalAmt,
                'renewals' => $renewals
            ]);
        } catch (Exception $e) {
            echo json_encode(['status' => 'error', 'error' => $e->getMessage()]);
        }
        break;

    case 'get_device_stock_detail':
        // 📱 Detailed stock info for a device model (clicked from live stock)
        try {
            $modelName = trim($_GET['model_name'] ?? '');
            if (!$modelName) {
                echo json_encode(['status' => 'error', 'error' => 'Device model name required']);
                break;
            }

            // 1️⃣ Get all IMEIs grouped by status
            $stmt = $conn->prepare("SELECT status, COUNT(*) as count FROM device_master WHERE device_model = ? GROUP BY status");
            $stmt->execute([$modelName]);
            $statusRows = $stmt->fetchAll(PDO::FETCH_ASSOC);
            $inStock = 0; $sold = 0; $returned = 0;
            foreach ($statusRows as $r) {
                $s = strtolower(trim($r['status']));
                if ($s === 'in stock') $inStock = (int)$r['count'];
                elseif ($s === 'sold') $sold = (int)$r['count'];
                elseif (strpos($s, 'return') !== false) $returned += (int)$r['count'];
            }
            $total = $inStock + $sold + $returned;

            // 2️⃣ Recent additions (last 30)
            $stmt = $conn->prepare("SELECT imei, supplier_name, date, rate, sl_no, status FROM device_master WHERE device_model = ? ORDER BY date DESC, id DESC LIMIT 30");
            $stmt->execute([$modelName]);
            $recentAdditions = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // 3️⃣ Sales records via invoice_log
            $stmt = $conn->prepare("SELECT imei FROM device_master WHERE device_model = ?");
            $stmt->execute([$modelName]);
            $imeis = $stmt->fetchAll(PDO::FETCH_COLUMN);

            $sales = [];
            if (!empty($imeis)) {
                // Process in chunks of 100 to avoid query size limits
                $chunks = array_chunk($imeis, 100);
                foreach ($chunks as $chunk) {
                    $placeholders = implode(',', array_fill(0, count($chunk), '?'));
                    $stmt = $conn->prepare("SELECT invoice_no, invoice_date, customer_name, vehicle_no, total_amount, paid_amount, imei FROM invoice_log WHERE imei IN ($placeholders) ORDER BY invoice_date DESC");
                    $stmt->execute($chunk);
                    $sales = array_merge($sales, $stmt->fetchAll(PDO::FETCH_ASSOC));
                }
                // Sort by date descending
                usort($sales, function($a, $b) {
                    return ($b['invoice_date'] ?? '') <=> ($a['invoice_date'] ?? '');
                });
            }

            // Calculate totals
            $totalRevenue = 0;
            $totalPaid = 0;
            foreach ($sales as $s) {
                $totalRevenue += (float)($s['total_amount'] ?? 0);
                $totalPaid += (float)($s['paid_amount'] ?? 0);
            }

            echo json_encode([
                'status' => 'success',
                'model_name' => $modelName,
                'in_stock' => $inStock,
                'sold' => $sold,
                'returned' => $returned,
                'total' => $total,
                'recent_additions' => $recentAdditions,
                'additions_count' => count($recentAdditions),
                'sales' => $sales,
                'sales_count' => count($sales),
                'sales_total_amount' => $totalRevenue,
                'sales_paid_amount' => $totalPaid
            ]);
        } catch (Exception $e) {
            echo json_encode(['status' => 'error', 'error' => $e->getMessage()]);
        }
        break;

    default:
        echo json_encode(['status' => 'ready']);
        break;
}
?>
