<?php
/**
 * SK AI Chat API - DeepSeek Edition
 * Database-first assistant with DeepSeek AI integration.
 * Uses ONLY the ERD database (no cross-branch SLM).
 */
header('Content-Type: application/json');
error_reporting(0); 
date_default_timezone_set('Asia/Kolkata');
set_time_limit(60); 
include 'db_connect.php';

$chatHistory = $_POST['history'] ?? '[]';
$historyArr = json_decode($chatHistory, true) ?: [];
$userQuery = trim($_POST['q'] ?? '');

if ($userQuery === '') {
    echo json_encode([
        'answer' => "DeepSeek AI ready! DB-la irukka sales, renewal, invoice, dealer, stock, customer data ellam kelunga.",
        'mode' => 'idle',
        'model' => 'deepseek'
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

function searchSpecificTables(PDO $conn, $needle) {
    if ($needle === '') return [];

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
        } catch (Exception $e) { continue; }
    }

    return array_slice($results, 0, 10);
}

function buildDbContext(PDO $conn, array $schema) {
    $sales = getSalesSummary($conn);
    $renewals = getRenewalSummary($conn);
    $stock = getStockSummary($conn);
    $dealers = getDealerSummary($conn);
    $tableNames = array_keys($schema);

    $priceMaster = $conn->query("SELECT type, COUNT(*) as count FROM price_master GROUP BY type")->fetchAll(PDO::FETCH_ASSOC);
    $pmParts = [];
    foreach($priceMaster as $p) $pmParts[] = ($p['type'] ?: 'Device') . ": " . $p['count'];

    $tableSummary = [];
    foreach (array_slice($tableNames, 0, 15) as $table) {
        try {
            $count = (int)$conn->query("SELECT COUNT(*) FROM `$table`")->fetchColumn();
            $tableSummary[] = $table . " (" . $count . ")";
        } catch (Exception $e) { continue; }
    }

    $dealerText = [];
    foreach ($dealers as $row) {
        $dealerText[] = "{$row['holder']} ({$row['count']})";
    }

    $schemaHints = [];
    $importantTables = ['sales_log', 'renewal_log', 'device_master', 'price_master', 'stock_ledger', 'dealer_ledger', 'invoice_log'];
    foreach($importantTables as $t) {
        if(isset($schema[$t])) {
            $schemaHints[] = "$t columns: " . implode(', ', $schema[$t]['columns']);
        }
    }

    $systemMap = [
        "Architecture" => "Modular PHP API backend with Vanilla JS/CSS frontend. DB: u182809524_sk_core.",
        "Inventory Logic" => "price_master (Master defs) -> stock_ledger (Batch movements) -> device_master (Single unit tracking with IMEI).",
        "Sales & Finance" => "api_sales.php calculates GST, updates dealer_ledger, logs to sales_log.",
        "Renewal Engine" => "Manages expiries. renewal_log tracks validity and status (PENDING/PAID).",
        "System Tools" => "master_device.php, master_software.php, hostinger_manager.php."
    ];
    $logicText = [];
    foreach($systemMap as $k => $v) $logicText[] = "$k: $v";

    return [
        'summary' => [
            "Tables & Counts: " . implode(', ', $tableSummary),
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

function loadDeepSeekApiKey() {
    $envKey = getenv('DEEPSEEK_API_KEY');
    if ($envKey) {
        return trim($envKey);
    }

    $configFile = __DIR__ . DIRECTORY_SEPARATOR . 'deepseek_config.php';
    if (is_file($configFile)) {
        $config = include $configFile;
        if (is_array($config) && !empty($config['api_key'])) {
            return trim($config['api_key']);
        }
    }

    return '';
}

function askDeepSeek($apiKey, $userQuery, array $context, $localAnswer, array $history = []) {
    if ($apiKey === '') {
        return '';
    }

    $summaryText = isset($context['summary']) ? implode("\n- ", $context['summary']) : 'No summary context provided.';

    $systemPrompt = "You are SK-AI, my friend's friendly & super-smart Business Partner AI!\n" .
        "Persona: You talk like a close friend/partner — warm, enthusiastic, and always helpful in Tanglish (Tamil + English mix).\n" .
        "Current Time: " . date('l, Y-m-d H:i:s') . " (India/Kolkata).\n\n" .
        "BUSINESS CONTEXT (Real-time Stats):\n- " . $summaryText . "\n\n" .
        "LOCAL DB MATCHES (Quick Find):\n" . ($localAnswer ?: 'None') . "\n\n" .
        "🌟 STYLE GUIDE:\n" .
        "1. Always respond in friendly Tanglish (like a close business partner chatting).\n" .
        "2. Use emojis to make replies lively & neat.\n" .
        "3. Format answers CLEANLY with line breaks, bullet points, and sections.\n" .
        "4. Be CONCISE — short and sweet, but complete info.\n" .
        "5. If sales/profit is high → celebrate! If low → give actionable suggestions.\n" .
        "6. Remember previous context. If user says 'Him', refer to the last mentioned person/vehicle.\n" .
        "7. Never dump raw JSON or SQL in response. Always explain neatly.\n";

    $messages = [];
    $messages[] = [
        'role' => 'system',
        'content' => $systemPrompt
    ];
    
    foreach(array_slice($history, -8) as $chat) {
        $messages[] = [
            'role' => ($chat['side'] === 'user' ? 'user' : 'assistant'),
            'content' => $chat['text']
        ];
    }
    
    $messages[] = [
        'role' => 'user',
        'content' => "USER QUESTION: " . $userQuery
    ];

    $url = "https://api.deepseek.com/v1/chat/completions";
    $payload = [
        'model' => 'deepseek-chat',
        'messages' => $messages,
        'temperature' => 0.5,
        'max_tokens' => 512,
        'stream' => false
    ];

    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json',
        'Authorization: Bearer ' . $apiKey
    ]);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
    curl_setopt($ch, CURLOPT_TIMEOUT, 20);

    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    $response = curl_exec($ch);
    $curlError = curl_error($ch);
    curl_close($ch);

    if ($curlError || !$response) {
        return '';
    }

    $decoded = json_decode($response, true);
    if (isset($decoded['error'])) {
        return '';
    }
    
    return trim($decoded['choices'][0]['message']['content'] ?? '');
}

/**
 * 🧠 JARVIS: Detect user intent for business actions
 */
function detectJarvisIntent($query) {
    $q = strtolower(trim($query));

    // Send reminders / notify customers
    if (preg_match('/(send|remind|notify|message|sms|whatsapp).*(renewal|pending|due|customer)/i', $q) ||
        preg_match('/(renewal|pending|due|customer).*(send|remind|notify|message|sms|whatsapp)/i', $q)) {
        return 'SEND_REMINDERS';
    }

    // Check / show renewals
    if (preg_match('/(check|show|list|find|get).*(renewal|pending|due)/i', $q) ||
        preg_match('/(renewal|pending|due).*(check|show|list|find|get|status)/i', $q) ||
        preg_match('/pending.*ethana|due.*ethana/i', $q)) {
        return 'CHECK_RENEWALS';
    }

    // Send WhatsApp message
    if (preg_match('/(send|post|push).*(whatsapp|wa|message)/i', $q) ||
        preg_match('/whatsapp.*(send|message)/i', $q)) {
        return 'SEND_WHATSAPP';
    }

    // Open / navigate to page
    if (preg_match('/(open|go to|navigate|show|take me).*(dashboard|sales|renewal|stock|dealer|report|invoice)/i', $q)) {
        return 'SHOW_STATS';
    }

    return null;
}

function cleanSql($sql) {
    $sql = preg_replace('/```(sql)?/i', '', $sql);
    $sql = trim($sql, " `\n\r\t;");
    return $sql;
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

function answerFromIntent(PDO $conn, $query, array $schema) {
    $normalized = strtoupper(str_replace([' ', '-', '.', ','], '', $query));
    
    if (preg_match('/^(sale|sales|collection|revenue|income|panam|kaasu)$/i', trim($query))) return "";
    if (preg_match('/(today|inniku|yesterday|netthu|month|monthly|masam|week|weekly).*(sale|collection|revenue)/i', $query)) return "";
    if (preg_match('/(profit|labam|gain)/i', $query)) return "";
    if (preg_match('/^(stock|inventory|iruppu|available)$/i', trim($query))) return "";

    $entity = '';
    if (preg_match('/[A-Z]{2}[0-9]{2}[A-Z]{0,2}[0-9]{4}/', $normalized, $v)) $entity = $v[0];
    else if (preg_match('/[0-9]{15}/', $normalized, $i)) $entity = $i[0];
    else if (preg_match('/[0-9]{10}/', $normalized, $m)) $entity = $m[0];
    else {
        $words = preg_split('/[\s,]+/', $query);
        foreach($words as $w) {
            $w = trim($w, '?,.!');
            if (strlen($w) > strlen($entity) && strlen($w) >= 3) $entity = $w;
        }
    }

    if ($entity !== '') {
        $results = searchSpecificTables($conn, $entity);
        if (!empty($results)) {
            return "Matches for '{$entity}':\n" . implode("\n", array_map(function($r){ return "• " . $r; }, $results));
        }
    }

    return '';
}

try {
    $apiKey = loadDeepSeekApiKey();
    if ($apiKey === '') {
        $schema = discoverSchema($conn);
        $local = answerFromIntent($conn, $userQuery, $schema);
        echo json_encode(['answer' => $local ?: 'DeepSeek Key missing. Connect server.', 'mode' => 'no-key', 'model' => 'deepseek']);
        exit;
    }

    $schema = discoverSchema($conn);
    $context = buildDbContext($conn, $schema);

    // STEP 1: Generate SQL if needed
    $sqlPrompt = "MySQL expert. Database: u182809524_sk_core (ERD).\n" .
        "Schema:\n";
    foreach(['sales_log', 'renewal_log', 'device_master', 'price_master', 'stock_ledger'] as $t) {
        if(isset($schema[$t])) $sqlPrompt .= "- $t: " . implode(',', $schema[$t]['columns']) . "\n";
    }
    $sqlPrompt .= "\nQuestion: $userQuery\nTask: Write a read-only SELECT. Return ONLY SQL or 'NONE'.";
    
    $generatedSql = askDeepSeek($apiKey, $sqlPrompt, [], '', $historyArr);
    $dbData = null;

    if ($generatedSql !== '' && strtoupper(trim($generatedSql)) !== 'NONE') {
        $dbData = executeSafeSql($conn, $generatedSql);
        
        if (is_string($dbData) && stripos($dbData, 'Error') !== false) {
            $dbData = null;
        }
    }

    // FALLBACK: If SQL found nothing, use our robust local search
    if (empty($dbData)) {
        $localResult = answerFromIntent($conn, $userQuery, $schema);
        if ($localResult !== '') {
            $dbData = [['MatchFound' => $localResult]];
        }
    }

    // Log interaction
    try { @file_put_contents('debug_ai.log', "[" . date('Y-m-d H:i:s') . "] [DeepSeek] Q: $userQuery | SQL: " . cleanSql($generatedSql) . " | Data: " . (is_array($dbData) ? count($dbData) : 0) . "\n", FILE_APPEND); } catch(Exception $e){}

    // STEP 2: Final Answer
    $trimmedData = is_array($dbData) ? array_slice($dbData, 0, 10) : $dbData;
    
    $finalPrompt = "You are SK-AI — my friend's friendly Business Partner!\n" .
        "Real-time Stats: " . implode(' | ', $context['summary']) . "\n" .
        "Database Data: " . json_encode($trimmedData) . "\n" .
        "🌟 INSTRUCTIONS:\n" .
        "1. Speak in friendly Tanglish with emojis 😊\n" .
        "2. Format neatly with line breaks and bullet points\n" .
        "3. If data found → explain clearly & give insights\n" .
        "4. If no data → suggest what user can search for\n" .
        "5. Highlight profit/trends/gaps like a real partner\n" .
        "6. Use [OPEN:ID] format for deep links\n" .
        "7. Keep it SHORT & SWEET — don't write essays!\n";

    $finalAnswer = askDeepSeek($apiKey, $finalPrompt, [], '', $historyArr);
    
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

    // 🧠 JARVIS: Detect intent for actions
    $jarvisIntent = detectJarvisIntent($userQuery);
    $jarvisData = [];

    if ($jarvisIntent === 'SEND_REMINDERS') {
        preg_match('/\d+/', $userQuery, $m);
        $jarvisData = ['days' => $m ? (int)$m[0] : 3];
        if ($finalAnswer && !strpos($finalAnswer, '[OPEN:SEND_REMINDERS]')) {
            $finalAnswer .= "\n\nWant me to send WhatsApp reminders to them? 🧠";
        }
    } elseif ($jarvisIntent === 'CHECK_RENEWALS') {
        preg_match('/\d+/', $userQuery, $m);
        $jarvisData = ['days' => $m ? (int)$m[0] : 7];
        if ($finalAnswer && !strpos($finalAnswer, '[OPEN:CHECK_RENEWALS]')) {
            $finalAnswer .= "\n\nWant me to check details? 🧠";
        }
    } elseif ($jarvisIntent === 'SEND_WHATSAPP') {
        preg_match('/\d{10}/', $userQuery, $m);
        $jarvisData = ['to' => $m[0] ?? '', 'message' => $userQuery];
    }

    echo json_encode([
        'answer' => $finalAnswer ?: "Sorry boss, intha query-ku data kidaikkala. Konjam specific-ah kelunga!",
        'mode' => 'dual-engine-ai',
        'model' => 'deepseek',
        'debug_sql' => cleanSql($generatedSql),
        'jarvis_intent' => $jarvisIntent,
        'jarvis_data' => $jarvisData
    ]);

} catch (Exception $e) {
    echo json_encode(['answer' => "System Error: " . $e->getMessage(), 'mode' => 'error', 'model' => 'deepseek']);
}
?>
