<?php
/**
 * 💳 Payment Gateway API
 * ======================
 * Razorpay UPI/Card/Netbanking integration
 * + Static UPI QR code payments
 * + Payment link generation
 */

header('Content-Type: application/json');
require_once __DIR__ . '/db_connect.php';
require_once __DIR__ . '/pay_config.php';

$action = $_REQUEST['action'] ?? '';

// Ensure table exists
try {
    ensurePayTable($conn);
} catch (Exception $e) {}

/**
 * 📱 Generate Razorpay Order
 */
function createRazorpayOrder($amount, $receipt, $notes = []) {
    global $pay_razorpay_key_id, $pay_razorpay_key_secret;

    if (strpos($pay_razorpay_key_id, 'rzp_test') !== 0 && strpos($pay_razorpay_key_id, 'rzp_live') !== 0) {
        return ['success' => false, 'error' => 'Payment gateway not configured. Update pay_config.php'];
    }

    $data = [
        'amount' => round($amount * 100), // Razorpay uses paise
        'currency' => 'INR',
        'receipt' => $receipt,
        'notes' => $notes
    ];

    $ch = curl_init('https://api.razorpay.com/v1/orders');
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => json_encode($data),
        CURLOPT_HTTPHEADER => [
            'Authorization: Basic ' . base64_encode($pay_razorpay_key_id . ':' . $pay_razorpay_key_secret),
            'Content-Type: application/json'
        ],
        CURLOPT_TIMEOUT => 15,
        CURLOPT_SSL_VERIFYPEER => true
    ]);

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($httpCode === 200) {
        return ['success' => true, 'data' => json_decode($response, true)];
    }

    $err = json_decode($response, true);
    return ['success' => false, 'error' => $err['error']['description'] ?? 'Order creation failed'];
}

/**
 * ✅ Verify Payment
 */
function verifyPayment($orderId, $paymentId, $signature) {
    global $pay_razorpay_key_secret;

    // Generate expected signature
    $expected = hash_hmac('sha256', $orderId . '|' . $paymentId, $pay_razorpay_key_secret);
    
    return hash_equals($expected, $signature);
}

/**
 * 🔍 Fetch Payment Details from Razorpay
 */
function fetchPaymentDetails($paymentId) {
    global $pay_razorpay_key_id, $pay_razorpay_key_secret;

    $ch = curl_init("https://api.razorpay.com/v1/payments/$paymentId");
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HTTPHEADER => [
            'Authorization: Basic ' . base64_encode($pay_razorpay_key_id . ':' . $pay_razorpay_key_secret),
            'Content-Type: application/json'
        ],
        CURLOPT_TIMEOUT => 10
    ]);
    $response = curl_exec($ch);
    curl_close($ch);

    return json_decode($response, true);
}

/**
 * 📤 Generate UPI Payment Link
 */
function createUpiLink($upiId, $amount, $name, $note = '') {
    // UPI deep link format
    $uri = 'upi://pay?pa=' . urlencode($upiId);
    $uri .= '&pn=' . urlencode($name);
    $uri .= '&am=' . number_format($amount, 2, '.', '');
    $uri .= '&cu=INR';
    $uri .= '&tn=' . urlencode($note);
    
    return $uri;
}

/**
 * 📊 Generate QR Code URL (via Google Charts API - free)
 */
function getQRCodeUrl($data, $size = 200) {
    return 'https://chart.googleapis.com/chart?chs=' . $size . 'x' . $size . '&cht=qr&chl=' . urlencode($data) . '&choe=UTF-8';
}

// ============ HANDLE REQUESTS ============

try {
    switch ($action) {
        case 'create_order':
            // Create payment order for checkout
            $amount = floatval($_POST['amount'] ?? 0);
            $customerName = $_POST['customer_name'] ?? 'Customer';
            $customerMobile = $_POST['customer_mobile'] ?? '';
            $refType = $_POST['reference_type'] ?? 'manual';
            $refId = intval($_POST['reference_id'] ?? 0);
            $notes = $_POST['notes'] ?? "$refType - $customerName";

            if ($amount <= 0) {
                echo json_encode(['success' => false, 'error' => 'Invalid amount']);
                break;
            }

            if (strpos($GLOBALS['pay_razorpay_key_id'], 'YOUR_') !== false) {
                // Fallback: Offer static UPI instead
                $upiUri = createUpiLink($GLOBALS['pay_upi_id'], $amount, $GLOBALS['pay_upi_name'], $notes);
                $qrUrl = getQRCodeUrl($upiUri);
                
                echo json_encode([
                    'success' => true,
                    'method' => 'static_upi',
                    'upi_id' => $GLOBALS['pay_upi_id'],
                    'upi_uri' => $upiUri,
                    'qr_url' => $qrUrl,
                    'amount' => $amount,
                    'customer_name' => $customerName
                ]);
                break;
            }

            $receipt = 'SK' . date('YmdHis') . rand(100, 999);
            $result = createRazorpayOrder($amount, $receipt, [
                'customer_name' => $customerName,
                'customer_mobile' => $customerMobile,
                'reference_type' => $refType,
                'reference_id' => (string)$refId
            ]);

            if ($result['success']) {
                $order = $result['data'];
                
                // Save transaction
                $stmt = $conn->prepare("INSERT INTO payment_transactions 
                    (order_id, reference_type, reference_id, customer_name, customer_mobile, amount, status, receipt_id, notes)
                    VALUES (?, ?, ?, ?, ?, ?, 'created', ?, ?)");
                $stmt->execute([
                    $order['id'],
                    $refType,
                    $refId,
                    $customerName,
                    $customerMobile,
                    $amount,
                    $receipt,
                    $notes
                ]);

                // Generate UPI fallback link too
                $upiUri = createUpiLink($GLOBALS['pay_upi_id'], $amount, $GLOBALS['pay_upi_name'], $notes);
                $qrUrl = getQRCodeUrl($upiUri);

                echo json_encode([
                    'success' => true,
                    'method' => 'razorpay',
                    'order_id' => $order['id'],
                    'amount' => $order['amount'],
                    'currency' => $order['currency'],
                    'key_id' => $GLOBALS['pay_razorpay_key_id'],
                    'customer_name' => $customerName,
                    'customer_mobile' => $customerMobile,
                    // Also provide UPI fallback
                    'upi_id' => $GLOBALS['pay_upi_id'],
                    'upi_uri' => $upiUri,
                    'qr_url' => $qrUrl
                ]);
            } else {
                // Fallback to UPI
                $upiUri = createUpiLink($GLOBALS['pay_upi_id'], $amount, $GLOBALS['pay_upi_name'], $notes);
                $qrUrl = getQRCodeUrl($upiUri);
                echo json_encode([
                    'success' => true,
                    'method' => 'static_upi',
                    'upi_id' => $GLOBALS['pay_upi_id'],
                    'upi_uri' => $upiUri,
                    'qr_url' => $qrUrl,
                    'amount' => $amount,
                    'note' => 'Razorpay unavailable - using UPI QR'
                ]);
            }
            break;

        case 'verify':
            // Verify payment after checkout
            $orderId = $_POST['razorpay_order_id'] ?? '';
            $paymentId = $_POST['razorpay_payment_id'] ?? '';
            $signature = $_POST['razorpay_signature'] ?? '';
            $transactionId = intval($_POST['transaction_id'] ?? 0);

            if (!$orderId || !$paymentId || !$signature) {
                echo json_encode(['success' => false, 'error' => 'Missing payment verification data']);
                break;
            }

            $verified = verifyPayment($orderId, $paymentId, $signature);
            
            if ($verified) {
                // Fetch payment details
                $payment = fetchPaymentDetails($paymentId);
                $method = $payment['method'] ?? 'unknown';
                $vpa = $payment['vpa'] ?? '';

                // Update transaction
                $stmt = $conn->prepare("UPDATE payment_transactions SET 
                    payment_id = ?, status = 'paid', payment_method = ?, 
                    upi_transaction_id = ?, paid_at = NOW()
                    WHERE order_id = ?");
                $stmt->execute([$paymentId, $method, $vpa, $orderId]);

                echo json_encode([
                    'success' => true,
                    'payment_id' => $paymentId,
                    'method' => $method,
                    'amount' => ($payment['amount'] ?? 0) / 100
                ]);
            } else {
                // Mark as failed
                try {
                    $conn->prepare("UPDATE payment_transactions SET status = 'failed' WHERE order_id = ?")
                         ->execute([$orderId]);
                } catch (Exception $e) {}

                echo json_encode(['success' => false, 'error' => 'Payment verification failed - signature mismatch']);
            }
            break;

        case 'mark_paid_manual':
            // Manual: UPI received → mark as paid
            $amount = floatval($_POST['amount'] ?? 0);
            $customerName = $_POST['customer_name'] ?? '';
            $customerMobile = $_POST['customer_mobile'] ?? '';
            $refType = $_POST['reference_type'] ?? 'manual';
            $refId = intval($_POST['reference_id'] ?? 0);
            $upiRef = $_POST['upi_reference'] ?? '';
            $method = $_POST['payment_method'] ?? 'UPI';

            if ($amount <= 0) {
                echo json_encode(['success' => false, 'error' => 'Invalid amount']);
                break;
            }

            $orderId = 'MANUAL_' . date('YmdHis') . rand(100, 999);

            $stmt = $conn->prepare("INSERT INTO payment_transactions 
                (order_id, payment_id, reference_type, reference_id, customer_name, customer_mobile, 
                 amount, status, payment_method, upi_transaction_id, paid_at)
                VALUES (?, ?, ?, ?, ?, ?, ?, 'paid', ?, ?, NOW())");
            
            $stmt->execute([
                $orderId,
                $upiRef ?: $orderId,
                $refType,
                $refId,
                $customerName,
                $customerMobile,
                $amount,
                $method,
                $upiRef
            ]);

            echo json_encode([
                'success' => true,
                'transaction_id' => $conn->lastInsertId(),
                'message' => 'Payment recorded successfully'
            ]);
            break;

        case 'history':
            $mobile = $_GET['mobile'] ?? '';
            $status = $_GET['status'] ?? '';
            $limit = min(intval($_GET['limit'] ?? 50), 200);

            $sql = "SELECT * FROM payment_transactions WHERE 1=1";
            $params = [];

            if ($mobile) {
                $sql .= " AND customer_mobile LIKE ?";
                $params[] = "%$mobile%";
            }
            if ($status) {
                $sql .= " AND status = ?";
                $params[] = $status;
            }

            $sql .= " ORDER BY created_at DESC LIMIT " . $limit;

            $q = $conn->prepare($sql);
            $q->execute($params);
            $rows = $q->fetchAll(PDO::FETCH_ASSOC);

            echo json_encode(['success' => true, 'data' => $rows, 'total' => count($rows)]);
            break;

        case 'stats':
            $today = $conn->query("SELECT COALESCE(SUM(amount), 0) FROM payment_transactions WHERE DATE(paid_at) = CURDATE() AND status = 'paid'")->fetchColumn();
            $month = $conn->query("SELECT COALESCE(SUM(amount), 0) FROM payment_transactions WHERE MONTH(paid_at) = MONTH(CURDATE()) AND YEAR(paid_at) = YEAR(CURDATE()) AND status = 'paid'")->fetchColumn();
            $total = $conn->query("SELECT COALESCE(SUM(amount), 0) FROM payment_transactions WHERE status = 'paid'")->fetchColumn();
            $count = $conn->query("SELECT COUNT(*) FROM payment_transactions WHERE status = 'paid'")->fetchColumn();
            $pending = $conn->query("SELECT COUNT(*) FROM payment_transactions WHERE status = 'created'")->fetchColumn();

            echo json_encode([
                'success' => true,
                'today' => floatval($today),
                'month' => floatval($month),
                'total' => floatval($total),
                'count' => intval($count),
                'pending' => intval($pending)
            ]);
            break;

        case 'generate_qr':
            $amount = floatval($_GET['amount'] ?? 0);
            $note = $_GET['note'] ?? 'Payment';

            if ($amount <= 0) {
                echo json_encode(['success' => false, 'error' => 'Invalid amount']);
                break;
            }

            $upiUri = createUpiLink($GLOBALS['pay_upi_id'], $amount, $GLOBALS['pay_upi_name'], $note);
            $qrUrl = getQRCodeUrl($upiUri, 300);

            echo json_encode([
                'success' => true,
                'upi_id' => $GLOBALS['pay_upi_id'],
                'upi_uri' => $upiUri,
                'qr_url' => $qrUrl,
                'amount' => $amount
            ]);
            break;

        default:
            echo json_encode(['success' => false, 'error' => 'Unknown action. Try: create_order, verify, mark_paid_manual, history, stats, generate_qr']);
    }
} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
?>
