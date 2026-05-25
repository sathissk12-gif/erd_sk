<?php
// Unified API v6.0 - Final Stabilized Suite
error_reporting(E_ALL);
ini_set('display_errors', 0);
header('Content-Type: application/json');

$host = "127.0.0.1";
$pass = "S@kenterprises6198";
$db_erd = "u182809524_sk_core"; $user_erd = "u182809524_skerode";
$db_slm = "u182809524_slm"; $user_slm = "u182809524_slm";

try {
    $erd_conn = new PDO("mysql:host=$host;dbname=$db_erd;charset=utf8", $user_erd, $pass);
    $erd_conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $slm_conn = new PDO("mysql:host=$host;dbname=$db_slm;charset=utf8", $user_slm, $pass);
    $slm_conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => "Connection Error: " . $e->getMessage()]); exit;
}

function repairDealers(PDO $conn) {
    try {
        $st = $conn->query("DESCRIBE dealers");
        $cols = $st->fetchAll(PDO::FETCH_COLUMN);
        if (!in_array('location', $cols)) {
            $conn->exec("ALTER TABLE dealers ADD COLUMN location VARCHAR(255) AFTER office");
        }
    } catch(Exception $e) {}
}

repairDealers($erd_conn);

function getAvailableCols(PDO $conn, $table) {
    try { $st = $conn->query("DESCRIBE `$table` "); return $st->fetchAll(PDO::FETCH_COLUMN); } catch(Exception $e) { return []; }
}

function buildDeviceQuery(PDO $conn) {
    $cols = getAvailableCols($conn, 'device_master');
    $select = [];
    if (in_array('imei', $cols)) $select[] = "imei";
    else if (in_array('imei_no', $cols)) $select[] = "imei_no as imei";
    else $select[] = "'N/A' as imei";
    if (in_array('device_model', $cols)) $select[] = "device_model";
    else $select[] = "'Unknown' as device_model";
    if (in_array('rate', $cols)) $select[] = "rate";
    else if (in_array('price', $cols)) $select[] = "price as rate";
    else $select[] = "0 as rate";
    if (in_array('issue_date', $cols)) $select[] = "issue_date";
    else $select[] = "NULL as issue_date";
    if (in_array('status', $cols)) $select[] = "status";
    else $select[] = "'N/A' as status";
    return implode(", ", $select);
}

$action = $_REQUEST['action'] ?? 'list';

switch ($action) {
    case 'list':
        try {
            $stmt = $erd_conn->query("SELECT id, name, phone, office, location, status FROM dealers ORDER BY name ASC");
            $dealers = $stmt->fetchAll(PDO::FETCH_ASSOC);
            foreach ($dealers as &$d) {
                $name = $d['name'];
                // ONLY count actual devices (where imei is not 'PAYMENT')
                $c1 = $erd_conn->prepare("SELECT COUNT(*) FROM dealer_ledger WHERE LOWER(TRIM(dealer_name)) = LOWER(TRIM(?)) AND imei != 'PAYMENT'");
                $c1->execute([$name]);
                $count1 = (int)$c1->fetchColumn();
                
                $c2 = $slm_conn->prepare("SELECT COUNT(*) FROM dealer_ledger WHERE LOWER(TRIM(dealer_name)) = LOWER(TRIM(?)) AND imei != 'PAYMENT'");
                $c2->execute([$name]);
                $count2 = (int)$c2->fetchColumn();
                
                $d['total_devices'] = $count1 + $count2;
                $d['pending_devices'] = $count1 + $count2; // This can be refined to exclude returned ones
            }
            echo json_encode(['success' => true, 'data' => $dealers]);
        } catch (Exception $e) { echo json_encode(['success' => false, 'message' => $e->getMessage()]); }
        break;

    case 'get_details':
        try {
            $id = $_GET['id'] ?? null;
            $st = $erd_conn->prepare("SELECT * FROM dealers WHERE id = ?");
            $st->execute([$id]);
            $dealer = $st->fetch(PDO::FETCH_ASSOC);
            if (!$dealer) throw new Exception("Partner not found");
            $name = $dealer['name'];
            $name_query = "%" . trim($name) . "%";

            $erd_q = buildDeviceQuery($erd_conn);
            $slm_q = buildDeviceQuery($slm_conn);

            $q1 = $erd_conn->prepare("SELECT $erd_q, 'ERD' as branch FROM device_master WHERE LOWER(TRIM(holder)) LIKE LOWER(TRIM(?)) AND status = 'SOLD'");
            $q1->execute([$name_query]);
            $devices = $q1->fetchAll(PDO::FETCH_ASSOC);

            $q2 = $slm_conn->prepare("SELECT $slm_q, 'SLM' as branch FROM device_master WHERE LOWER(TRIM(holder)) LIKE LOWER(TRIM(?)) AND status = 'SOLD'");
            $q2->execute([$name_query]);
            $devices = array_merge($devices, $q2->fetchAll(PDO::FETCH_ASSOC));

            $l1 = $erd_conn->prepare("SELECT *, 'ERD' as branch FROM dealer_ledger WHERE LOWER(TRIM(dealer_name)) LIKE LOWER(TRIM(?))");
            $l1->execute([$name_query]);
            $ledger = $l1->fetchAll(PDO::FETCH_ASSOC);

            $l2 = $slm_conn->prepare("SELECT *, 'SLM' as branch FROM dealer_ledger WHERE LOWER(TRIM(dealer_name)) LIKE LOWER(TRIM(?))");
            $l2->execute([$name_query]);
            $ledger = array_merge($ledger, $l2->fetchAll(PDO::FETCH_ASSOC));

            usort($ledger, function($a, $b) { return strtotime($b['date'] ?? '0') - strtotime($a['date'] ?? '0'); });
            echo json_encode(['success' => true, 'data' => $dealer, 'devices' => $devices, 'ledger' => $ledger]);
        } catch (Exception $e) { echo json_encode(['success' => false, 'message' => $e->getMessage()]); }
        break;

    case 'get_masters':
        try {
            $soft = $erd_conn->query("SELECT name as type FROM price_master WHERE type = 'SOFTWARE' ORDER BY name ASC")->fetchAll(PDO::FETCH_COLUMN);
            if(empty($soft)) {
                // Fallback to SLM if ERD is empty (optional, but safer for transition)
                $soft = $slm_conn->query("SELECT software_type as type FROM software_price_master")->fetchAll(PDO::FETCH_COLUMN);
            }
            echo json_encode(['success' => true, 'software' => $soft]);
        } catch (Exception $e) { echo json_encode(['success' => false, 'message' => $e->getMessage()]); }
        break;

    case 'save':
        try {
            $id = $_POST['id'] ?? null; 
            $name = $_POST['name'] ?? ''; 
            $phone = $_POST['phone'] ?? ''; 
            $office = $_POST['office'] ?? 'Salem';
            $location = $_POST['location'] ?? '';
            if ($id) {
                $st = $erd_conn->prepare("UPDATE dealers SET name=?, phone=?, office=?, location=? WHERE id=?");
                $st->execute([$name, $phone, $office, $location, $id]);
            } else {
                $st = $erd_conn->prepare("INSERT INTO dealers (name, phone, office, location) VALUES (?, ?, ?, ?)");
                $st->execute([$name, $phone, $office, $location]);
            }
            echo json_encode(['success' => true]);
        } catch (Exception $e) { echo json_encode(['success' => false, 'message' => $e->getMessage()]); }
        break;

    case 'check_imei':
        try {
            $imei = trim($_GET['imei'] ?? '');
            if (!$imei) throw new Exception("IMEI required");
            $res = null;
            foreach([$erd_conn, $slm_conn] as $index => $conn) {
                $cols = getAvailableCols($conn, 'device_master');
                if (empty($cols)) continue;
                $imei_col = in_array('imei_no', $cols) ? 'imei_no' : 'imei';
                $st = $conn->prepare("SELECT status, holder, device_model FROM device_master WHERE TRIM($imei_col) = TRIM(?) LIMIT 1");
                $st->execute([$imei]);
                $device = $st->fetch(PDO::FETCH_ASSOC);
                if ($device) {
                    $device['branch'] = ($index === 0) ? 'ERD' : 'SLM';
                    $res = $device;
                    break;
                }
            }
            if ($res) echo json_encode(['success' => true, 'data' => $res]);
            else echo json_encode(['success' => false, 'message' => 'IMEI not found']);
        } catch (Exception $e) { echo json_encode(['success' => false, 'message' => $e->getMessage()]); }
        break;

    case 'get_masters_list':
        try {
            $devices = $erd_conn->query("SELECT name, cost FROM price_master WHERE type = 'DEVICE' ORDER BY name ASC")->fetchAll(PDO::FETCH_ASSOC);
            $software = $erd_conn->query("SELECT name, cost FROM price_master WHERE type = 'SOFTWARE' ORDER BY name ASC")->fetchAll(PDO::FETCH_ASSOC);
            echo json_encode(['success' => true, 'devices' => $devices, 'software' => $software]);
        } catch (Exception $e) { echo json_encode(['success' => false, 'message' => $e->getMessage()]); }
        break;

    case 'save_master':
        try {
            $name = trim($_POST['name'] ?? '');
            $cost = (float)($_POST['cost'] ?? 0);
            $type = $_POST['type'] ?? 'DEVICE';
            if(!$name) throw new Exception("Name is required");

            $st = $erd_conn->prepare("SELECT id FROM price_master WHERE name = ? AND type = ?");
            $st->execute([$name, $type]);
            if($st->fetch()) {
                $upd = $erd_conn->prepare("UPDATE price_master SET cost = ? WHERE name = ? AND type = ?");
                $upd->execute([$cost, $name, $type]);
            } else {
                $ins = $erd_conn->prepare("INSERT INTO price_master (name, cost, type) VALUES (?, ?, ?)");
                $ins->execute([$name, $cost, $type]);
            }
            echo json_encode(['success' => true]);
        } catch (Exception $e) { echo json_encode(['success' => false, 'message' => $e->getMessage()]); }
        break;

    case 'delete_ledger':
        try {
            $id = $_POST['id'] ?? null;
            $branch = $_POST['branch'] ?? 'ERD';
            $targetConn = ($branch === 'SLM') ? $slm_conn : $erd_conn;
            
            $st = $targetConn->prepare("SELECT * FROM dealer_ledger WHERE id = ?");
            $st->execute([$id]);
            $entry = $st->fetch(PDO::FETCH_ASSOC);
            if (!$entry) throw new Exception("Entry not found");
            
            $imei = $entry['imei'];
            $targetConn->beginTransaction();
            $stDel = $targetConn->prepare("DELETE FROM dealer_ledger WHERE id = ?");
            $stDel->execute([$id]);
            
            if ($imei && $imei !== 'PAYMENT') {
                $stCols = $targetConn->query("DESCRIBE device_master");
                $cols = $stCols->fetchAll(PDO::FETCH_COLUMN);
                $imei_col = in_array('imei_no', $cols) ? 'imei_no' : 'imei';
                $stUpd = $targetConn->prepare("UPDATE device_master SET holder = NULL, status = 'In Stock', issue_date = NULL WHERE TRIM($imei_col) = TRIM(?)");
                $stUpd->execute([$imei]);
            }
            $targetConn->commit();
            echo json_encode(['success' => true]);
        } catch (Exception $e) { 
            if(isset($targetConn) && $targetConn->inTransaction()) $targetConn->rollBack();
            echo json_encode(['success' => false, 'message' => $e->getMessage()]); 
        }
        break;
}
?>
