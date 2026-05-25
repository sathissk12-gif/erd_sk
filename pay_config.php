<?php
/**
 * 💳 Payment Gateway Configuration
 * ================================
 * Supports: Razorpay (UPI / Card / Netbanking / Wallet)
 * 
 * SETUP INSTRUCTIONS:
 * 1. Go to https://dashboard.razorpay.com → Sign Up (Free!)
 * 2. Settings → API Keys → Generate Key ID + Secret
 * 3. Paste them below
 * 
 * TEST MODE:
 * - Use test keys from Razorpay Test Mode
 * - Test UPI: customer@upi or 9876543210@paytm
 * - Test Card: 4111 1111 1111 1111 | Exp: 12/25 | CVV: 123
 */

// 🔐 Razorpay API Credentials
// Get from: https://dashboard.razorpay.com/app/keys
$pay_razorpay_key_id     = "rzp_live_Ss6JlXfpvR4mAt"; // Test = rzp_test_, Live = rzp_live_
$pay_razorpay_key_secret = "E1Na0pyMTf6a1hBV92usaoto";

// 📱 UPI Settings (Static UPI - no account needed!)
$pay_upi_id       = "sathiscontacts12-4@okaxis";  // e.g., business@paytm or 9876543210@paytm
$pay_upi_name     = "SK TraXen";         // Name shown on UPI app
$pay_upi_qr_path  = "";                // Leave empty - auto-generates

// 💰 Business Details
$pay_company_name = "SK LOGIC SOLUTIONS";
$pay_currency     = "INR";
$pay_contact_email = "your@email.com";

// 🧪 Test Mode (false = live payments)
$pay_test_mode = true; // Change to false for real payments

// 📊 Database table
function ensurePayTable($conn) {
    $conn->exec("CREATE TABLE IF NOT EXISTS payment_transactions (
        id INT PRIMARY KEY AUTO_INCREMENT,
        order_id VARCHAR(100) UNIQUE,
        payment_id VARCHAR(100),
        reference_type VARCHAR(50) COMMENT 'sale / renewal / manual',
        reference_id INT DEFAULT 0,
        customer_name VARCHAR(100),
        customer_mobile VARCHAR(20),
        amount DECIMAL(12,2) NOT NULL,
        currency VARCHAR(5) DEFAULT 'INR',
        status ENUM('created','paid','failed','refunded') DEFAULT 'created',
        payment_method VARCHAR(50),
        upi_transaction_id VARCHAR(100),
        payment_link VARCHAR(500),
        receipt_id VARCHAR(100),
        notes TEXT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        paid_at DATETIME,
        INDEX idx_ref (reference_type, reference_id),
        INDEX idx_mobile (customer_mobile),
        INDEX idx_status (status)
    )");

    // Add columns if missing
    try {
        $conn->exec("ALTER TABLE payment_transactions ADD COLUMN upi_transaction_id VARCHAR(100) AFTER payment_method");
    } catch (Exception $e) {}
    try {
        $conn->exec("ALTER TABLE payment_transactions ADD COLUMN payment_link VARCHAR(500) AFTER upi_transaction_id");
    } catch (Exception $e) {}
}
?>
