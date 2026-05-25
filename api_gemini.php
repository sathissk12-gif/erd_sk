<?php
/**
 * SK AI Chat API
 * Database-first assistant. Searches schema and records locally.
 */
header('Content-Type: application/json');
error_reporting(0); 
date_default_timezone_set('Asia/Kolkata'); // Set to India Time
set_time_limit(60); 
include 'db_connect.php'; // Default connection (Usually ERD)

// Cross-Branch Connections
$host = "127.0.0.1";
$pass = "S@kenterprises6198";
$db_erd = "u182809524_sk_core"; $user_erd = "u182809524_skerode";
$db_slm = "u182809524_slm"; $user_slm = "u182809524_slm";

try {
    $erd_conn = new PDO("mysql:host=$host;dbname=$db_erd;charset=utf8", $user_erd, $pass);
    $erd_conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $slm_conn = new PDO("mysql:host=$host;dbname=$db_slm;charset=utf8", $user_slm, $pass);
    $slm_conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (Exception $e) {}

// Use $conn as the primary branch (determined by the folder we are in)
$current_branch = (strpos(__DIR__, 'SLM') !== false) ? 'SLM' : 'ERD';
$primary_conn = ($current_branch === 'SLM') ? $slm_conn : $erd_conn;
$secondary_conn = ($current_branch === 'SLM') ? $erd_conn : $slm_conn;
$secondary_name = ($current_branch === 'SLM') ? 'ERD' : 'SLM';

$chatHistory = $_POST['history'] ?? '[]'; // Receive history as JSON string
$historyArr = json_decode($chatHistory, true) ?: [];

if ($userQuery === '') {
    echo json_encode([
        'answer' => "AI ready. DB-la irukka sales, renewal, invoice, dealer, stock, customer data ellam kelunga.",
        'mode' => 'idle'
    ]);
    exit;
}

function normalizeText($value) {
    $value = strtolower(trim((string)$value));
    return preg_replace('/\s+/', ' ', $value);
}

function formatMoney($value) {
    return number_format((float)$value, 2);
}

function queryOne($stmt, $params = []) {
    $stmt->execute($params);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    return $row ?: null;
}

function queryAll($stmt, $params = []) {
    $stmt->execute($params);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function getTables(PDO $conn) {
    return $conn->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
}

function getTableColumns(PDO $conn, $table) {
    $stmt = $conn->query("DESCRIBE `$table`");
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function discoverSchema(PDO $conn) {
    $schema = [];
    foreach (getTables($conn) as $table) {
        $columns = getTableColumns($conn, $table);
        $schema[$table] = [
            'columns' => array_map(function ($col) {
                return $col['Field'];
            }, $columns),
            'text_columns' => array_values(array_map(function ($col) {
                return $col['Field'];
            }, array_filter($columns, function ($col) {
                $type = strtolower($col['Type']);
                return strpos($type, 'char') !== false ||
                    strpos($type, 'text') !== false ||
                    strpos($type, 'varchar') !== false;
            }))),
            'date_columns' => array_values(array_map(function ($col) {
                return $col['Field'];
            }, array_filter($columns, function ($col) {
                $type = strtolower($col['Type']);
                return strpos($type, 'date') !== false || strpos($type, 'time') !== false;
            }))),
        ];
    }
    return $schema;
}

function pendingRenewalClause() {
    return "(UPPER(COALESCE(status, '')) IN ('PENDING', 'NO') OR UPPER(COALESCE(processed, '')) = 'PENDING')";
}

function getSalesSummary(PDO $conn) {
    $monthStart = date('Y-m-01');
    $today = date('Y-m-d');
    $yesterday = date('Y-m-d', strtotime('-1 day'));
    $weekStart = date('Y-m-d', strtotime('-7 days'));

    $todayRes = $conn->query("SELECT COUNT(*) as c, SUM(received_amount) as s FROM sales_log WHERE sale_date = '$today'")->fetch(PDO::FETCH_ASSOC);
    $yestRes = $conn->query("SELECT COUNT(*) as c, SUM(received_amount) as s FROM sales_log WHERE sale_date = '$yesterday'")->fetch(PDO::FETCH_ASSOC);
    $weekRes = $conn->query("SELECT COUNT(*) as c, SUM(received_amount) as s FROM sales_log WHERE sale_date >= '$weekStart'")->fetch(PDO::FETCH_ASSOC);
    $monthRes = $conn->query("SELECT COUNT(*) as c, SUM(received_amount) as s, SUM(profit) as p FROM sales_log WHERE sale_date >= '$monthStart'")->fetch(PDO::FETCH_ASSOC);

    return [
        'today_count' => (int)$todayRes['c'],
        'today_amount' => (float)$todayRes['s'],
        'yesterday_count' => (int)$yestRes['c'],
        'yesterday_amount' => (float)$yestRes['s'],
        'week_count' => (int)$weekRes['c'],
        'week_amount' => (float)$weekRes['s'],
        'month_count' => (int)$monthRes['c'],
        'month_amount' => (float)$monthRes['s'],
        'month_profit' => (float)$monthRes['p']
    ];
}

function getRenewalSummary(PDO $conn) {
    $pendingClause = pendingRenewalClause();
    $overall = $conn->query("SELECT COUNT(*) AS count, COALESCE(SUM(amount), 0) AS amount FROM renewal_log WHERE $pendingClause")->fetch(PDO::FETCH_ASSOC);
    $today = $conn->query("SELECT COUNT(*) AS count FROM renewal_log WHERE valid_to = CURDATE() AND $pendingClause")->fetch(PDO::FETCH_ASSOC);
    $tomorrow = $conn->query("SELECT COUNT(*) AS count FROM renewal_log WHERE valid_to = DATE_ADD(CURDATE(), INTERVAL 1 DAY) AND $pendingClause")->fetch(PDO::FETCH_ASSOC);
    return [
        'pending_count' => (int)($overall['count'] ?? 0),
        'pending_amount' => (float)($overall['amount'] ?? 0),
        'today_due' => (int)($today['count'] ?? 0),
        'tomorrow_due' => (int)($tomorrow['count'] ?? 0),
    ];
}

function getStockSummary(PDO $conn) {
    $rows = $conn->query("SELECT device_model, COUNT(*) AS count FROM device_master WHERE status = 'In Stock' GROUP BY device_model ORDER BY count DESC, device_model ASC")->fetchAll(PDO::FETCH_ASSOC);
    $parts = [];
    foreach ($rows as $row) {
        $parts[] = ($row['device_model'] ?: 'Unknown') . " (" . (int)$row['count'] . ")";
    }
    return $parts;
}

function getDealerSummary(PDO $conn) {
    return $conn->query("SELECT holder, COUNT(*) AS count FROM device_master WHERE status = 'SOLD' AND holder IS NOT NULL AND holder != '' AND holder != 'OFFICE' GROUP BY holder ORDER BY count DESC, holder ASC LIMIT 8")->fetchAll(PDO::FETCH_ASSOC);
}

function findBestIdentifier($query) {
    preg_match_all('/[a-z0-9-]{4,}/i', $query, $matches);
    $tokens = $matches[0] ?? [];
    usort($tokens, function ($a, $b) {
        return strlen($b) <=> strlen($a);
    });
    return $tokens[0] ?? '';
}

function findImportantTokens($query) {
    preg_match_all('/[a-z0-9-]{3,}/i', $query, $matches);
    $tokens = array_values(array_unique($matches[0] ?? []));
    usort($tokens, function ($a, $b) {
        return strlen($b) <=> strlen($a);
    });
    return array_slice($tokens, 0, 5);
}

function searchSpecificTables(PDO $conn, $needle) {
    if ($needle === '') {
        return [];
    }

    $like = '%' . $needle . '%';
    $results = [];

    $lookups = [
        'sales_log' => [
            'sql' => "SELECT vehicle_no, customer_name, imei, received_amount, sale_date FROM sales_log WHERE vehicle_no LIKE ? OR customer_name LIKE ? OR imei LIKE ? OR mobile_number LIKE ? ORDER BY id DESC LIMIT 2",
            'params' => [$like, $like, $like, $like],
            'format' => function ($row) {
                return "sales_log: {$row['vehicle_no']} / {$row['customer_name']} / {$row['imei']} / Rs." . formatMoney($row['received_amount']) . " / {$row['sale_date']}";
            }
        ],
        'invoice_log' => [
            'sql' => "SELECT invoice_no, customer_name, vehicle_no, imei, paid_amount, status FROM invoice_log WHERE invoice_no LIKE ? OR vehicle_no LIKE ? OR customer_name LIKE ? OR imei LIKE ? ORDER BY id DESC LIMIT 2",
            'params' => [$like, $like, $like, $like],
            'format' => function ($row) {
                return "invoice_log: {$row['invoice_no']} / {$row['vehicle_no']} / {$row['customer_name']} / Rs." . formatMoney($row['paid_amount']) . " / {$row['status']}";
            }
        ],
        'renewal_log' => [
            'sql' => "SELECT vehicle_no, customer_name, imei, amount, status, valid_to, expiry_date FROM renewal_log WHERE vehicle_no LIKE ? OR customer_name LIKE ? OR imei LIKE ? OR mobile_no LIKE ? OR m1 LIKE ? ORDER BY id DESC LIMIT 2",
            'params' => [$like, $like, $like, $like, $like],
            'format' => function ($row) {
                $due = $row['valid_to'] ?: $row['expiry_date'] ?: '-';
                return "renewal_log: {$row['vehicle_no']} / {$row['customer_name']} / {$row['imei']} / Rs." . formatMoney($row['amount']) . " / {$row['status']} / due {$due}";
            }
        ],
        'renewal_invoice_log' => [
            'sql' => "SELECT invoice_num, customer_name, vehicle_num, received_amount, date FROM renewal_invoice_log WHERE invoice_num LIKE ? OR vehicle_num LIKE ? OR customer_name LIKE ? ORDER BY id DESC LIMIT 2",
            'params' => [$like, $like, $like],
            'format' => function ($row) {
                return "renewal_invoice_log: {$row['invoice_num']} / {$row['vehicle_num']} / {$row['customer_name']} / Rs." . formatMoney($row['received_amount']) . " / {$row['date']}";
            }
        ],
        'device_master' => [
            'sql' => "SELECT imei, device_model, status, holder, rate FROM device_master WHERE imei LIKE ? OR holder LIKE ? OR device_model LIKE ? ORDER BY id DESC LIMIT 2",
            'params' => [$like, $like, $like],
            'format' => function ($row) {
                return "device_master: {$row['imei']} / {$row['device_model']} / {$row['status']} / {$row['holder']} / rate {$row['rate']}";
            }
        ],
        'customerdatas' => [
            'sql' => "SELECT name, mobile, location FROM customerdatas WHERE name LIKE ? OR mobile LIKE ? OR location LIKE ? ORDER BY id DESC LIMIT 2",
            'params' => [$like, $like, $like],
            'format' => function ($row) {
                return "customerdatas: {$row['name']} / {$row['mobile']} / {$row['location']}";
            }
        ],
        'dealer_ledger' => [
            'sql' => "SELECT dealer_name, imei, selling_price, profit, txn_id, date FROM dealer_ledger WHERE dealer_name LIKE ? OR imei LIKE ? OR txn_id LIKE ? ORDER BY id DESC LIMIT 2",
            'params' => [$like, $like, $like],
            'format' => function ($row) {
                return "dealer_ledger: {$row['dealer_name']} / {$row['imei']} / sell {$row['selling_price']} / profit {$row['profit']} / {$row['txn_id']} / {$row['date']}";
            }
        ],
        'stock_ledger' => [
            'sql' => "SELECT item_name, qty, item_type, date, remark FROM stock_ledger WHERE item_name LIKE ? OR remark LIKE ? ORDER BY id DESC LIMIT 3",
            'params' => [$like, $like],
            'format' => function ($row) {
                return "stock_ledger: {$row['item_name']} ({$row['item_type']}) | Qty: {$row['qty']} | {$row['date']} | {$row['remark']}";
            }
        ],
    ];

    foreach ($lookups as $table => $config) {
        try {
            $stmt = $conn->prepare($config['sql']);
            $rows = queryAll($stmt, $config['params']);
            foreach ($rows as $row) {
                $results[] = $config['format']($row);
            }
        } catch (Exception $e) {
            continue;
        }
    }

    return array_slice($results, 0, 10);
}

function genericSchemaSearch(PDO $conn, array $schema, array $tokens) {
    $results = [];
    foreach ($schema as $table => $meta) {
        if (!$meta['text_columns']) {
            continue;
        }

        $searchCols = array_slice($meta['text_columns'], 0, 6);
        $selectCols = [];
        foreach ($meta['columns'] as $col) {
            if (in_array($col, ['id', 'uid', 'customer_name', 'name', 'vehicle_no', 'vehicle_num', 'invoice_no', 'invoice_num', 'imei', 'mobile', 'mobile_no', 'holder', 'status', 'date'])) {
                $selectCols[] = "`$col`";
            }
        }
        if (!$selectCols) {
            $selectCols = array_map(function ($col) {
                return "`$col`";
            }, array_slice($meta['columns'], 0, min(4, count($meta['columns']))));
        }

        foreach ($tokens as $token) {
            $conditions = [];
            $params = [];
            foreach ($searchCols as $col) {
                $conditions[] = "`$col` LIKE ?";
                $params[] = '%' . $token . '%';
            }
            if (!$conditions) {
                continue;
            }

            $sql = "SELECT " . implode(', ', $selectCols) . " FROM `$table` WHERE " . implode(' OR ', $conditions) . " ORDER BY 1 DESC LIMIT 1";
            try {
                $stmt = $conn->prepare($sql);
                $row = queryOne($stmt, $params);
                if ($row) {
                    $pairs = [];
                    foreach ($row as $key => $value) {
                        if ($value === null || $value === '') continue;
                        $pairs[] = $key . ": " . $value;
                    }
                    if ($pairs) {
                        $results[] = $table . " -> " . implode(" | ", array_slice($pairs, 0, 5));
                    }
                }
            } catch (Exception $e) {
                continue;
            }
        }
    }

    return array_values(array_unique(array_slice($results, 0, 12)));
}

function answerFromIntent(PDO $conn, $query, array $schema) {
    $normalized = strtoupper(str_replace([' ', '-', '.', ','], '', $query));
    
    // 1. Summary Intents
    if (preg_match('/^(sale|sales|collection|revenue|income|panam|kaasu)$/i', trim($query))) return "";
    if (preg_match('/(today|inniku|yesterday|netthu|month|monthly|masam|week|weekly).*(sale|collection|revenue)/i', $query)) return "";
    if (preg_match('/(profit|labam|gain)/i', $query)) return "";
    if (preg_match('/^(stock|inventory|iruppu|available)$/i', trim($query))) return "";

    // 2. Extract Entity (Aggressive Patterns)
    $entity = '';
    // Vehicle (Flexible: TN 33 AK 1234 or TN 33 1234 or TN33AK1234)
    if (preg_match('/[A-Z]{2}[0-9]{2}[A-Z]{0,2}[0-9]{4}/', $normalized, $v)) $entity = $v[0];
    // IMEI (15 digits)
    else if (preg_match('/[0-9]{15}/', $normalized, $i)) $entity = $i[0];
    // Mobile (10 digits)
    else if (preg_match('/[0-9]{10}/', $normalized, $m)) $entity = $m[0];
    else {
        // Fallback: Longest word or number sequence
        $words = preg_split('/[\s,]+/', $query);
        foreach($words as $w) {
            $w = trim($w, '?,.!');
            if (strlen($w) > strlen($entity) && strlen($w) >= 3) $entity = $w;
        }
    }

    if ($entity !== '') {
        $results = deepSearch($erd_conn, $slm_conn, $entity);
        if (!empty($results)) {
            return "Cross-Branch Matches for '{$entity}':\n" . implode("\n", array_map(function($r){ return "• " . $r; }, $results));
        }
    }

    return '';
}

function deepSearch($erd_conn, $slm_conn, $q) {
    $results = [];
    $likeQ = "%$q%";
    $normQ = strtoupper(str_replace([' ', '-'], '', $q));
    $normLikeQ = "%$normQ%";

    $conns = ['ERD' => $erd_conn, 'SLM' => $slm_conn];

    foreach($conns as $branch => $db) {
        if(!$db) continue;
        // 1. Sales Log
        $stmt = $db->prepare("SELECT vehicle_no, customer_name, imei, sale_date FROM sales_log 
                                WHERE REPLACE(REPLACE(vehicle_no, ' ', ''), '-', '') LIKE ? 
                                OR customer_name LIKE ? 
                                OR imei LIKE ? 
                                OR mobile_number LIKE ? LIMIT 2");
        $stmt->execute([$normLikeQ, $likeQ, $likeQ, $likeQ]);
        while($r = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $results[] = "[$branch Branch] SALE: Vehicle {$r['vehicle_no']}, Customer {$r['customer_name']}, IMEI {$r['imei']}, Date {$r['sale_date']}";
        }

        // 2. Renewal Log
        $stmt = $db->prepare("SELECT vehicle_no, customer_name, imei, status, valid_to FROM renewal_log 
                                WHERE REPLACE(REPLACE(vehicle_no, ' ', ''), '-', '') LIKE ? 
                                OR customer_name LIKE ? 
                                OR imei LIKE ? LIMIT 2");
        $stmt->execute([$normLikeQ, $likeQ, $likeQ]);
        while($r = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $results[] = "[$branch Branch] RENEWAL: Vehicle {$r['vehicle_no']}, Status {$r['status']}, IMEI {$r['imei']}, Valid till {$r['valid_to']}";
        }

        // 3. Device Master
        $stmt = $db->prepare("SELECT imei, device_model, status, holder FROM device_master 
                                WHERE imei LIKE ? OR device_model LIKE ? OR holder LIKE ? LIMIT 2");
        $stmt->execute([$likeQ, $likeQ, $likeQ]);
        while($r = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $results[] = "[$branch Branch] INVENTORY: {$r['device_model']} (IMEI: {$r['imei']}) is {$r['status']} at {$r['holder']}";
        }
    }

    return $results;
}

function buildDbContext(PDO $conn, array $schema) {
    $sales = getSalesSummary($conn);
    $renewals = getRenewalSummary($conn);
    $stock = getStockSummary($conn);
    $dealers = getDealerSummary($conn);
    $tableNames = array_keys($schema);
    $tableSummary = [];

    // Detailed stats for inventory/tools
    $priceMaster = $conn->query("SELECT type, COUNT(*) as count FROM price_master GROUP BY type")->fetchAll(PDO::FETCH_ASSOC);
    $pmParts = [];
    foreach($priceMaster as $p) $pmParts[] = ($p['type'] ?: 'Device') . ": " . $p['count'];

    foreach (array_slice($tableNames, 0, 15) as $table) {
        try {
            $count = (int)$conn->query("SELECT COUNT(*) FROM `$table`")->fetchColumn();
            $tableSummary[] = $table . " (" . $count . ")";
        } catch (Exception $e) {
            continue;
        }
    }

    $dealerText = [];
    foreach ($dealers as $row) {
        $dealerText[] = "{$row['holder']} ({$row['count']})";
    }

    // Add Schema hints for Gemini to know what columns exist
    $schemaHints = [];
    $importantTables = ['sales_log', 'renewal_log', 'device_master', 'price_master', 'stock_ledger', 'dealer_ledger', 'invoice_log'];
    foreach($importantTables as $t) {
        if(isset($schema[$t])) {
            $schemaHints[] = "$t columns: " . implode(', ', $schema[$t]['columns']);
        }
    }

    // SYSTEM KNOWLEDGE BASE (Deep System & Coding Logic)
    $systemMap = [
        "Architecture" => "Modular PHP API backend with Vanilla JS/CSS frontend. Uses Firebase for Auth/Security. DB: u182809524_sk_core.",
        "Coding Standards" => "Use PDO for database interaction. All APIs must return JSON. Error handling via try-catch with JSON error messages.",
        "Inventory Logic" => "price_master (Master defs) -> stock_ledger (Batch movements) -> device_master (Single unit tracking with IMEI).",
        "Sales & Finance" => "api_sales.php calculates GST, updates dealer_ledger for credits, and logs to sales_log for history. Net profit = Sale Price - Buy Cost - Expenses.",
        "Renewal Engine" => "api_renewal.php + api_renewal_automation.php manage expiries. renewal_log tracks validity dates and status (PENDING/PAID).",
        "Branch Logic" => "System supports multiple branches (ERD/SLM). Database schemas are unified but locations are tracked in 'location' columns.",
        "System Tools" => "master_device.php (Hardware), master_software.php (Digital), and hostinger_manager.php (Server/Domain APIs)."
    ];
    $logicText = [];
    foreach($systemMap as $k => $v) $logicText[] = "$k: $v";

    return [
        'summary' => [
            "Tables & Counts: " . implode(', ', $tableSummary),
            "Inventory Master (price_master): " . implode(', ', $pmParts),
            "Schema Info: " . implode(' | ', $schemaHints),
            "System Logic Map: " . implode(' | ', $logicText),
            "Today sales: Rs." . formatMoney($sales['today_amount']) . " ({$sales['today_count']} sales)",
            "Yesterday sales: Rs." . formatMoney($sales['yesterday_amount']) . " ({$sales['yesterday_count']} sales)",
            "Last 7 days: Rs." . formatMoney($sales['week_amount']) . " ({$sales['week_count']} sales)",
            "Month sales: Rs." . formatMoney($sales['month_amount']) . " ({$sales['month_count']} sales)",
            "Month profit: Rs." . formatMoney($sales['month_profit']),
            "Pending renewals: {$renewals['pending_count']}",
            "Renewals due today: {$renewals['today_due']}",
            "Stock: " . ($stock ? implode(', ', array_slice($stock, 0, 8)) : 'No stock'),
            "Top Dealers: " . ($dealerText ? implode(', ', $dealerText) : 'No dealer data'),
        ]
    ];
}

function loadGeminiApiKey() {
    $envKey = getenv('GEMINI_API_KEY');
    if ($envKey) {
        return trim($envKey);
    }

    $configFile = __DIR__ . DIRECTORY_SEPARATOR . 'gemini_config.php';
    if (is_file($configFile)) {
        $config = include $configFile;
        if (is_array($config) && !empty($config['api_key'])) {
            return trim($config['api_key']);
        }
    }

    return '';
}

function askGemini($apiKey, $userQuery, array $context, $localAnswer, array $history = []) {
    if ($apiKey === '') {
        return '';
    }

    $summaryText = isset($context['summary']) ? implode("\n- ", $context['summary']) : 'No summary context provided.';

    $systemPrompt = "You are SK-AI, a World-Class Business Intelligence Agent.\n" .
        "Persona: Highly analytical, proactive, and friendly business partner.\n" .
        "Current Time: " . date('l, Y-m-d H:i:s') . " (India/Kolkata).\n\n" .
        "BUSINESS CONTEXT (Real-time Stats):\n- " . $summaryText . "\n\n" .
        "LOCAL DB MATCHES (Quick Find):\n" . ($localAnswer ?: 'None') . "\n\n" .
        "GOALS:\n" .
        "1. Analyze trends. If sales are up, tell WHY. If low, suggest actions.\n" .
        "2. Remember previous context. If user says 'Him', refer to the last mentioned person/vehicle.\n" .
        "3. Provide insights in professional Tanglish. Be a true 'Partner', not just a bot.\n";

    // Build contents with history
    $contents = [];
    // Add history (limit to last 4 exchanges for context window safety)
    foreach(array_slice($history, -8) as $chat) {
        $contents[] = [
            'role' => ($chat['side'] === 'user' ? 'user' : 'model'),
            'parts' => [['text' => $chat['text']]]
        ];
    }
    // Add current query
    $contents[] = [
        'role' => 'user',
        'parts' => [['text' => $systemPrompt . "\n\nUSER QUESTION: " . $userQuery]]
    ];

    $url = "https://generativelanguage.googleapis.com/v1beta/models/gemini-1.5-flash:generateContent?key=" . rawurlencode($apiKey);
    $payload = [
        'contents' => $contents,
        'safetySettings' => [
            ['category' => 'HARM_CATEGORY_HARASSMENT', 'threshold' => 'BLOCK_NONE'],
            ['category' => 'HARM_CATEGORY_HATE_SPEECH', 'threshold' => 'BLOCK_NONE'],
            ['category' => 'HARM_CATEGORY_SEXUALLY_EXPLICIT', 'threshold' => 'BLOCK_NONE'],
            ['category' => 'HARM_CATEGORY_DANGEROUS_CONTENT', 'threshold' => 'BLOCK_NONE']
        ]
    ];

    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
    curl_setopt($ch, CURLOPT_TIMEOUT, 15);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // Important for SSL issues
    $response = curl_exec($ch);
    $curlError = curl_error($ch);
    curl_close($ch);

    if ($curlError || !$response) {
        return '';
    }

    $decoded = json_decode($response, true);
    return trim($decoded['candidates'][0]['content']['parts'][0]['text'] ?? '');
}

function cleanSql($sql) {
    $sql = preg_replace('/```(sql)?/i', '', $sql);
    $sql = trim($sql, " `\n\r\t;"); // Remove trailing semicolon
    return $sql;
}

function logDebug($query, $sql, $data, $error) {
    $logFile = __DIR__ . DIRECTORY_SEPARATOR . 'debug_ai.log';
    $entry = "[" . date('Y-m-d H:i:s') . "] \nQuery: $query\nSQL: $sql\nError: $error\nRows: " . (is_array($data) ? count($data) : 'N/A') . "\n" . str_repeat('-', 30) . "\n";
    file_put_contents($logFile, $entry, FILE_APPEND);
}

function executeSafeSql(PDO $conn, $sql) {
    $sql = cleanSql($sql);
    if ($sql === '' || strtoupper($sql) === 'NONE') return null;

    if (stripos($sql, 'SELECT') !== 0) return "Error: Only SELECT allowed.";
    if (preg_match('/(update|delete|insert|drop|truncate|alter|create|rename|replace)/i', $sql)) return "Error: Destructive queries blocked.";
    
    if (stripos($sql, 'LIMIT') === false) $sql .= " LIMIT 15";

    try {
        $stmt = $conn->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (Exception $e) {
        return "SQL Error: " . $e->getMessage();
    }
}

try {
    $apiKey = loadGeminiApiKey();
    if ($apiKey === '') {
        $schema = discoverSchema($conn);
        $local = answerFromIntent($conn, $userQuery, $schema);
        echo json_encode(['answer' => $local ?: 'AI Key missing. Connect server.', 'mode' => 'no-key']);
        exit;
    }

    $schema = discoverSchema($primary_conn);
    $context = buildDbContext($primary_conn, $schema);

    // STEP 1: Generate SQL if needed
    $sqlPrompt = "MySQL expert. Primary Branch: $current_branch. Secondary: $secondary_name.\n" .
        "Schema:\n";
    foreach(['sales_log', 'renewal_log', 'device_master', 'price_master', 'stock_ledger'] as $t) {
        if(isset($schema[$t])) $sqlPrompt .= "- $t: " . implode(',', $schema[$t]['columns']) . "\n";
    }
    $sqlPrompt .= "\nQuestion: $userQuery\nTask: Write a read-only SELECT. Return ONLY SQL or 'NONE'.";
    
    $generatedSql = askGemini($apiKey, $sqlPrompt, [], '', $historyArr);
    $dbData = null;
    $sqlError = '';

    if ($generatedSql !== '' && strtoupper(trim($generatedSql)) !== 'NONE') {
        $dbData = executeSafeSql($primary_conn, $generatedSql);
        // If not found in primary, try secondary for IMEI lookups
        if (empty($dbData) && (preg_match('/[0-9]{15}/', $userQuery) || preg_match('/TN[0-9]/i', $userQuery))) {
             $dbData = executeSafeSql($secondary_conn, $generatedSql);
             if (!empty($dbData) && is_array($dbData)) {
                 foreach($dbData as &$row) $row['Found_In_Branch'] = $secondary_name;
             }
        }
        
        if (is_string($dbData) && stripos($dbData, 'Error') !== false) {
            $sqlError = $dbData;
            $dbData = null;
        }
    }

    // FALLBACK ENGINE: If SQL found nothing, use our robust local search
    if (empty($dbData)) {
        $localResult = answerFromIntent($conn, $userQuery, $schema);
        if ($localResult !== '') {
            $dbData = [['MatchFound' => $localResult]];
        }
    }

    // Log interaction
    try { @file_put_contents('debug_ai.log', "[" . date('Y-m-d H:i:s') . "] Q: $userQuery | SQL: " . cleanSql($generatedSql) . " | Data: " . (is_array($dbData) ? count($dbData) : 0) . "\n", FILE_APPEND); } catch(Exception $e){}

    // STEP 2: Final Answer (Beautify with Personality & Analytics)
    $trimmedData = is_array($dbData) ? array_slice($dbData, 0, 10) : $dbData;
    
    $finalPrompt = "You are SK-AI Partner.\n" .
        "History context is already in the contents array. Focus on the latest query.\n" .
        "Real-time Stats: " . implode(' | ', $context['summary']) . "\n" .
        "Database Data: " . json_encode($trimmedData) . "\n" .
        "Instructions:\n" .
        "1. Give a proactive business insight based on this data.\n" .
        "2. If results found, explain clearly. If no rows found, suggest what the user can do.\n" .
        "3. Highlight profit trends or inventory gaps.\n" .
        "4. Keep the [OPEN:ID] format for deep links.\n";

    $finalAnswer = askGemini($apiKey, $finalPrompt, [], '', $historyArr);
    
    // Ultimate Resilience: If AI fails to beautify, format the raw data nicely ourselves
    if ($finalAnswer === '' && !empty($dbData)) {
        if (isset($dbData[0]['MatchFound'])) {
            $finalAnswer = "Found it boss! \n" . $dbData[0]['MatchFound'];
        } else {
            $finalAnswer = "Data kidaichiduchi boss, aana AI explain panna mudiyala. Inthaanga details:\n";
            foreach($dbData as $row) {
                foreach($row as $k => $v) $finalAnswer .= "• $k: $v\n";
                $finalAnswer .= "\n";
            }
        }
    }

    echo json_encode([
        'answer' => $finalAnswer ?: "Sorry boss, intha query-ku data kidaikkala. Konjam specific-ah kelunga!",
        'mode' => 'dual-engine-ai',
        'debug_sql' => cleanSql($generatedSql)
    ]);

} catch (Exception $e) {
    echo json_encode(['answer' => "System Error: " . $e->getMessage(), 'mode' => 'error']);
}
?>
