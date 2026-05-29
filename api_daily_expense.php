<?php
// Daily Expense Manager API v1.0 - Premium Backend
include 'db_connect.php';
header('Content-Type: application/json');

// Auto-create tables if they don't exist
function ensure_tables($conn) {
    $conn->exec("CREATE TABLE IF NOT EXISTS daily_items (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(150) NOT NULL UNIQUE,
        icon VARCHAR(80) DEFAULT 'fa-tag',
        type ENUM('income','expense','both') DEFAULT 'expense',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

    $conn->exec("CREATE TABLE IF NOT EXISTS daily_transactions (
        id INT AUTO_INCREMENT PRIMARY KEY,
        type ENUM('income','expense') NOT NULL,
        item_name VARCHAR(150) NOT NULL,
        icon VARCHAR(80) DEFAULT 'fa-tag',
        amount DECIMAL(12,2) NOT NULL DEFAULT 0,
        transaction_date DATE NOT NULL,
        note TEXT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

    // Add indexes
    try { $conn->exec("ALTER TABLE daily_transactions ADD INDEX idx_trans_date (transaction_date)"); } catch(Exception $e) {}
    try { $conn->exec("ALTER TABLE daily_transactions ADD INDEX idx_type (type)"); } catch(Exception $e) {}
}
ensure_tables($conn);

// ─── Smart Icon Auto-Assign Engine ───
function auto_icon($name) {
    $name = strtolower(trim($name));
    $map = [
        'salary'     => 'fa-money-bill-wave',     'wage'       => 'fa-money-bill-wave',
        'rent'       => 'fa-building',             'lease'      => 'fa-building',
        'food'       => 'fa-utensils',             'lunch'      => 'fa-utensils',
        'dinner'     => 'fa-utensils',             'breakfast'  => 'fa-mug-hot',
        'tea'        => 'fa-mug-hot',              'coffee'     => 'fa-mug-hot',
        'snacks'     => 'fa-cookie-bite',          'grocery'    => 'fa-basket-shopping',
        'petrol'     => 'fa-gas-pump',             'diesel'     => 'fa-gas-pump',
        'fuel'       => 'fa-gas-pump',             'transport'  => 'fa-car',
        'bus'        => 'fa-bus',                  'train'      => 'fa-train',
        'auto'       => 'fa-taxi',                 'travel'     => 'fa-plane',
        'electricity'=> 'fa-bolt',                 'eb'         => 'fa-bolt',
        'bill'       => 'fa-file-invoice-dollar',  'utility'    => 'fa-lightbulb',
        'water'      => 'fa-droplet',              'internet'   => 'fa-wifi',
        'mobile'     => 'fa-mobile-screen',        'phone'      => 'fa-phone',
        'recharge'   => 'fa-sim-card',             'shopping'   => 'fa-bag-shopping',
        'clothes'    => 'fa-shirt',                'medical'    => 'fa-kit-medical',
        'doctor'     => 'fa-user-doctor',          'medicine'   => 'fa-pills',
        'hospital'   => 'fa-hospital',             'health'     => 'fa-heart-pulse',
        'education'  => 'fa-book',                 'school'     => 'fa-school',
        'college'    => 'fa-graduation-cap',       'course'     => 'fa-laptop-file',
        'books'      => 'fa-book-open',            'entertainment'=>'fa-film',
        'movie'      => 'fa-film',                 'ott'        => 'fa-tv',
        'netflix'    => 'fa-tv',                   'subscription'=>'fa-rotate',
        'investment' => 'fa-chart-line',           'savings'    => 'fa-piggy-bank',
        'loan'       => 'fa-hand-holding-dollar',  'emi'        => 'fa-calculator',
        'insurance'  => 'fa-shield-halved',        'tax'        => 'fa-file-lines',
        'repair'     => 'fa-screwdriver-wrench',   'maintenance'=> 'fa-toolbox',
        'cleaning'   => 'fa-broom',                'house'      => 'fa-house',
        'gift'       => 'fa-gift',                 'donation'   => 'fa-hand-holding-heart',
        'party'      => 'fa-champagne-glasses',    'function'   => 'fa-cake-candles',
        'marketing'  => 'fa-bullhorn',             'ad'         => 'fa-rectangle-ad',
        'printing'   => 'fa-print',                'stationery' => 'fa-pen-ruler',
        'office'     => 'fa-briefcase',            'software'   => 'fa-laptop-code',
        'hardware'   => 'fa-microchip',            'tools'      => 'fa-toolbox',
        'staff'      => 'fa-users',                'employee'   => 'fa-user-tie',
        'commission' => 'fa-percent',              'bonus'      => 'fa-star',
        'freelance'  => 'fa-laptop',               'business'   => 'fa-store',
        'sales'      => 'fa-cart-shopping',        'profit'     => 'fa-arrow-trend-up',
        'service'    => 'fa-gear',                 'consulting' => 'fa-comments',
        'rental'     => 'fa-key',                  'dividend'   => 'fa-coins',
        'interest'   => 'fa-percent',              'refund'     => 'fa-rotate-left',
        'cashback'   => 'fa-wallet',               'other'      => 'fa-ellipsis',
    ];

    foreach ($map as $keyword => $icon) {
        if (strpos($name, $keyword) !== false) return $icon;
    }
    return 'fa-circle-dollar';
}

// ─── ROUTER ───
$action = $_REQUEST['action'] ?? '';

switch ($action) {

    // ─── ITEMS MANAGEMENT ───
    case 'get_items':
        $type = $_GET['type'] ?? 'all';
        $sql = "SELECT * FROM daily_items";
        if ($type !== 'all') {
            $sql .= " WHERE type IN ('$type', 'both')";
        }
        $sql .= " ORDER BY name ASC";
        $rows = $conn->query($sql)->fetchAll(PDO::FETCH_ASSOC);
        echo json_encode(['status' => 'ok', 'items' => $rows]);
        break;

    case 'add_item':
        $name = trim($_POST['name'] ?? '');
        $icon = trim($_POST['icon'] ?? '');
        $type = $_POST['type'] ?? 'expense';
        if (!$name) { echo json_encode(['status' => 'error', 'error' => 'Item name required']); exit; }
        if (!$icon) $icon = auto_icon($name);
        try {
            $stmt = $conn->prepare("INSERT INTO daily_items (name, icon, type) VALUES (?, ?, ?)");
            $stmt->execute([$name, $icon, $type]);
            echo json_encode(['status' => 'ok', 'id' => $conn->lastInsertId(), 'icon' => $icon]);
        } catch (Exception $e) {
            echo json_encode(['status' => 'error', 'error' => 'Item may already exist']);
        }
        break;

    case 'update_item':
        $id = (int)($_POST['id'] ?? 0);
        $name = trim($_POST['name'] ?? '');
        $icon = trim($_POST['icon'] ?? '');
        $type = $_POST['type'] ?? 'expense';
        if (!$id || !$name) { echo json_encode(['status' => 'error', 'error' => 'ID and name required']); exit; }
        $stmt = $conn->prepare("UPDATE daily_items SET name=?, icon=?, type=? WHERE id=?");
        $stmt->execute([$name, $icon, $type, $id]);
        echo json_encode(['status' => 'ok']);
        break;

    case 'delete_item':
        $id = (int)($_POST['id'] ?? 0);
        if ($id) {
            $conn->prepare("DELETE FROM daily_items WHERE id=?")->execute([$id]);
            echo json_encode(['status' => 'ok']);
        } else {
            echo json_encode(['status' => 'error', 'error' => 'Invalid ID']);
        }
        break;

    case 'auto_icon':
        $name = trim($_GET['name'] ?? '');
        echo json_encode(['status' => 'ok', 'icon' => $name ? auto_icon($name) : 'fa-tag']);
        break;

    // ─── TRANSACTIONS ───
    case 'add_transaction':
        $type = $_POST['type'] ?? 'expense';
        $item_name = trim($_POST['item_name'] ?? '');
        $icon = trim($_POST['icon'] ?? '');
        $amount = (float)($_POST['amount'] ?? 0);
        $date = $_POST['date'] ?? date('Y-m-d');
        $note = trim($_POST['note'] ?? '');

        if (!$item_name || $amount <= 0) {
            echo json_encode(['status' => 'error', 'error' => 'Item name and valid amount required']);
            exit;
        }
        if (!in_array($type, ['income', 'expense'])) $type = 'expense';

        // Auto-icon if not provided
        if (!$icon) {
            // Check if item exists in saved items
            $check = $conn->prepare("SELECT icon FROM daily_items WHERE name = ?");
            $check->execute([$item_name]);
            $existing = $check->fetch(PDO::FETCH_ASSOC);
            $icon = $existing ? $existing['icon'] : auto_icon($item_name);
        }

        // Auto-save item if new
        try {
            $conn->prepare("INSERT IGNORE INTO daily_items (name, icon, type) VALUES (?, ?, 'both')")
                 ->execute([$item_name, $icon]);
        } catch (Exception $e) {}

        $stmt = $conn->prepare("INSERT INTO daily_transactions (type, item_name, icon, amount, transaction_date, note) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->execute([$type, $item_name, $icon, $amount, $date, $note]);

        echo json_encode([
            'status' => 'ok',
            'id' => $conn->lastInsertId(),
            'icon' => $icon
        ]);
        break;

    case 'update_transaction':
        $id = (int)($_POST['id'] ?? 0);
        $type = $_POST['type'] ?? 'expense';
        $item_name = trim($_POST['item_name'] ?? '');
        $icon = trim($_POST['icon'] ?? '');
        $amount = (float)($_POST['amount'] ?? 0);
        $date = $_POST['date'] ?? date('Y-m-d');
        $note = trim($_POST['note'] ?? '');

        if (!$id || !$item_name || $amount <= 0) {
            echo json_encode(['status' => 'error', 'error' => 'Invalid data']);
            exit;
        }

        $stmt = $conn->prepare("UPDATE daily_transactions SET type=?, item_name=?, icon=?, amount=?, transaction_date=?, note=? WHERE id=?");
        $stmt->execute([$type, $item_name, $icon, $amount, $date, $note, $id]);
        echo json_encode(['status' => 'ok']);
        break;

    case 'delete_transaction':
        $id = (int)($_POST['id'] ?? 0);
        if ($id) {
            $conn->prepare("DELETE FROM daily_transactions WHERE id=?")->execute([$id]);
            echo json_encode(['status' => 'ok']);
        } else {
            echo json_encode(['status' => 'error', 'error' => 'Invalid ID']);
        }
        break;

    // ─── FETCH TRANSACTIONS ───
    case 'get_transactions':
        $month = (int)($_GET['month'] ?? date('m'));
        $year = (int)($_GET['year'] ?? date('Y'));
        $type = $_GET['type'] ?? 'all';

        $startDate = sprintf("%04d-%02d-01", $year, $month);
        $endDate = date("Y-m-t", strtotime($startDate));

        $sql = "SELECT * FROM daily_transactions WHERE transaction_date BETWEEN ? AND ?";
        $params = [$startDate, $endDate];

        if ($type !== 'all') {
            $sql .= " AND type = ?";
            $params[] = $type;
        }
        $sql .= " ORDER BY transaction_date DESC, id DESC";

        $stmt = $conn->prepare($sql);
        $stmt->execute($params);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Group by date for daily view
        $grouped = [];
        foreach ($rows as $row) {
            $d = $row['transaction_date'];
            if (!isset($grouped[$d])) $grouped[$d] = ['date' => $d, 'items' => [], 'day_total_income' => 0, 'day_total_expense' => 0];
            $grouped[$d]['items'][] = $row;
            if ($row['type'] === 'income') $grouped[$d]['day_total_income'] += (float)$row['amount'];
            else $grouped[$d]['day_total_expense'] += (float)$row['amount'];
        }

        echo json_encode([
            'status' => 'ok',
            'grouped' => array_values($grouped),
            'all' => $rows,
            'count' => count($rows)
        ]);
        break;

    // ─── SUMMARY ───
    case 'get_summary':
        $month = (int)($_GET['month'] ?? date('m'));
        $year = (int)($_GET['year'] ?? date('Y'));

        $startDate = sprintf("%04d-%02d-01", $year, $month);
        $endDate = date("Y-m-t", strtotime($startDate));

        // Monthly totals
        $stmt = $conn->prepare("SELECT type, SUM(amount) as total FROM daily_transactions WHERE transaction_date BETWEEN ? AND ? GROUP BY type");
        $stmt->execute([$startDate, $endDate]);
        $monthly = ['income' => 0, 'expense' => 0];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $monthly[$row['type']] = (float)$row['total'];
        }

        // Yearly totals
        $yearStart = sprintf("%04d-01-01", $year);
        $yearEnd = sprintf("%04d-12-31", $year);
        $stmt2 = $conn->prepare("SELECT type, SUM(amount) as total FROM daily_transactions WHERE transaction_date BETWEEN ? AND ? GROUP BY type");
        $stmt2->execute([$yearStart, $yearEnd]);
        $yearly = ['income' => 0, 'expense' => 0];
        while ($row = $stmt2->fetch(PDO::FETCH_ASSOC)) {
            $yearly[$row['type']] = (float)$row['total'];
        }

        // Daily breakdown for chart
        $stmt3 = $conn->prepare("SELECT transaction_date, type, SUM(amount) as total FROM daily_transactions WHERE transaction_date BETWEEN ? AND ? GROUP BY transaction_date, type ORDER BY transaction_date");
        $stmt3->execute([$startDate, $endDate]);
        $daily = [];
        while ($row = $stmt3->fetch(PDO::FETCH_ASSOC)) {
            $d = $row['transaction_date'];
            if (!isset($daily[$d])) $daily[$d] = ['date' => $d, 'income' => 0, 'expense' => 0];
            $daily[$d][$row['type']] = (float)$row['total'];
        }

        // Category breakdown
        $stmt4 = $conn->prepare("SELECT item_name, icon, type, SUM(amount) as total FROM daily_transactions WHERE transaction_date BETWEEN ? AND ? GROUP BY item_name, icon, type ORDER BY total DESC");
        $stmt4->execute([$startDate, $endDate]);
        $categories = $stmt4->fetchAll(PDO::FETCH_ASSOC);

        // Month-over-month for current year
        $mom = [];
        for ($m = 1; $m <= 12; $m++) {
            $ms = sprintf("%04d-%02d-01", $year, $m);
            $me = date("Y-m-t", strtotime($ms));
            $stmtM = $conn->prepare("SELECT type, SUM(amount) as total FROM daily_transactions WHERE transaction_date BETWEEN ? AND ? GROUP BY type");
            $stmtM->execute([$ms, $me]);
            $mom[$m] = ['month' => $m, 'income' => 0, 'expense' => 0];
            while ($r = $stmtM->fetch(PDO::FETCH_ASSOC)) {
                $mom[$m][$r['type']] = (float)$r['total'];
            }
        }

        echo json_encode([
            'status' => 'ok',
            'monthly' => $monthly,
            'yearly' => $yearly,
            'balance' => $monthly['income'] - $monthly['expense'],
            'daily' => array_values($daily),
            'categories' => $categories,
            'mom' => array_values($mom)
        ]);
        break;

    // ─── QUICK SUMMARY (today) ───
    case 'today_summary':
        $today = date('Y-m-d');
        $stmt = $conn->prepare("SELECT type, SUM(amount) as total FROM daily_transactions WHERE transaction_date = ? GROUP BY type");
        $stmt->execute([$today]);
        $todayData = ['income' => 0, 'expense' => 0];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $todayData[$row['type']] = (float)$row['total'];
        }
        echo json_encode(['status' => 'ok', 'today' => $todayData, 'date' => $today]);
        break;

    // ─── SEARCH ───
    case 'search':
        $q = trim($_GET['q'] ?? '');
        $year = (int)($_GET['year'] ?? date('Y'));
        if (strlen($q) < 2) { echo json_encode(['status' => 'ok', 'results' => []]); exit; }

        $stmt = $conn->prepare("SELECT * FROM daily_transactions WHERE (item_name LIKE ? OR note LIKE ?) AND YEAR(transaction_date) = ? ORDER BY transaction_date DESC LIMIT 100");
        $like = "%$q%";
        $stmt->execute([$like, $like, $year]);
        echo json_encode(['status' => 'ok', 'results' => $stmt->fetchAll(PDO::FETCH_ASSOC)]);
        break;

    default:
        echo json_encode(['status' => 'error', 'error' => 'Unknown action']);
}
