<?php
/**
 * Advanced SK-AI Brain (Omni-Knowledge Edition)
 * Handles Natural Language queries and falls back to Global Database Search
 */
header('Content-Type: application/json');
include 'db_connect.php';

$query = strtolower(trim($_GET['q'] ?? ''));
if (!$query) { echo json_encode(['answer' => 'I am listening. Ask me anything about your vehicles, sales, or stock.']); exit; }

$response = "";
$searchResults = [];

try {
    // 1. SALES QUERIES (Tanglish: collection, varumanam, panam, kaasu, evlo)
    if (preg_match('/(sale|revenue|income|collection|money|amount|varumanam|panam|kaasu|evlo)/', $query)) {
        if (preg_match('/(today|inniku|inneram)/', $query)) {
            $val = $conn->query("SELECT SUM(received_amount) FROM sales_log WHERE sale_date = CURDATE()")->fetchColumn();
            $response = "📊 Inniku total collection: ₹" . number_format($val ?: 0, 2) . ".";
        } elseif (preg_match('/(month|masam|monthly)/', $query)) {
            $monthStart = date('Y-m-01');
            $val = $conn->query("SELECT SUM(received_amount) FROM sales_log WHERE sale_date >= '$monthStart'")->fetchColumn();
            $response = "📈 Inda masam collection: ₹" . number_format($val ?: 0, 2) . ".";
        }
    }
    
    // 2. PROFIT QUERIES (Tanglish: labam, profit)
    if (preg_match('/(profit|labam|gain)/', $query)) {
        $val = $conn->query("SELECT SUM(profit) FROM sales_log WHERE sale_date >= '".date('Y-m-01')."'")->fetchColumn();
        $response = "💰 Inda masa labam (estimated): ₹" . number_format($val ?: 0, 2) . ".";
    }

    // 3. STOCK QUERIES (Tanglish: stock, iruppu, ethana vandi, device)
    if (preg_match('/(stock|device|inventory|available|how many|iruppu|ethana|vandi)/', $query)) {
        $stmt = $conn->query("SELECT device_model, COUNT(*) as c FROM device_master WHERE status = 'In Stock' GROUP BY device_model");
        $stocks = $stmt->fetchAll(PDO::FETCH_ASSOC);
        if($stocks) {
            $response = "📦 Current Stock Nilai: ";
            foreach($stocks as $s) $response .= $s['device_model'] . " (" . $s['c'] . "), ";
            $response = rtrim($response, ", ");
        } else {
            $response = "⚠️ Stock ippo ethuvum illa.";
        }
    }

    // 4. RENEWAL QUERIES (Tanglish: renewal, pending, bakki, recovery, due)
    if (preg_match('/(renewal|pending|bakki|recovery|kudukka|due)/', $query)) {
        if (preg_match('/(today|inniku|inneram)/', $query)) {
            $count = $conn->query("SELECT COUNT(*) FROM renewal_log WHERE valid_to = CURDATE() AND UPPER(status) = 'PENDING'")->fetchColumn();
            $response = "🔔 Inniku total-ah " . $count . " renewals bucket-la irukku.";
        } elseif (preg_match('/(tomorrow|nalaiku)/', $query)) {
            $count = $conn->query("SELECT COUNT(*) FROM renewal_log WHERE valid_to = DATE_ADD(CURDATE(), INTERVAL 1 DAY) AND UPPER(status) = 'PENDING'")->fetchColumn();
            $response = "📅 Nalaiku " . $count . " renewals pending irukku.";
        } else {
            $count = $conn->query("SELECT COUNT(*) FROM renewal_log WHERE UPPER(status) = 'PENDING'")->fetchColumn();
            $response = "🔔 Overall-ah " . $count . " renewals pending bakki irukku.";
        }
    }
    
    // 5. CUSTOMER / VEHICLE DEEP LOOKUP (Tanglish: yaruthu, owner, yaru)
    if (preg_match('/(who is|vandi|vehicle|yaruthu|yaru|owner)/', $query)) {
        $cleanQ = preg_replace('/(who|is|vandi|vehicle|yaruthu|yaru|owner)/', '', $query);
        $cleanQ = trim($cleanQ);
        if(strlen($cleanQ) > 2) {
            $likeQ = "%$cleanQ%";
            $stmt = $conn->prepare("SELECT vehicle_no, customer_name FROM renewal_log WHERE vehicle_no LIKE ? OR customer_name LIKE ? LIMIT 1");
            $stmt->execute([$likeQ, $likeQ]);
            if($m = $stmt->fetch()) {
                $response = "🚗 Intha " . $m['vehicle_no'] . " vandi " . $m['customer_name'] . " odathu.";
            }
        }
    }

    // --- 2. DEEP DATABASE SEARCH (The 'Memory' Brain) ---
    // If we still don't have a specific answer, search for the query string in major tables
    if (!$response || strlen($query) > 3) {
        $likeQ = "%$query%";
        
        // Search Sales / Vehicles
        $stmtS = $conn->prepare("SELECT vehicle_no, customer_name, imei FROM sales_log WHERE vehicle_no LIKE ? OR customer_name LIKE ? OR imei LIKE ? OR mobile_number LIKE ? LIMIT 3");
        $stmtS->execute([$likeQ, $likeQ, $likeQ, $likeQ]);
        $matches = $stmtS->fetchAll(PDO::FETCH_ASSOC);
        
        if ($matches) {
            $response = "🔍 I found matching records for '" . $query . "':\n";
            foreach($matches as $m) {
                $response .= "• " . $m['vehicle_no'] . " (" . $m['customer_name'] . ")\n";
            }
            $response .= "\nDo you want me to open the details for these?";
        }
    }

    // Default Fallback
    if (!$response) {
        $response = "I couldn't find an exact match for that. You can ask me things like 'Today sale', 'Profit this month', or search for a vehicle number like 'TN 33'.";
    }

    echo json_encode([
        'answer' => $response,
        'query' => $query
    ]);

} catch (Exception $e) {
    echo json_encode(['answer' => 'My neural link is interrupted. Error: ' . $e->getMessage()]);
}
