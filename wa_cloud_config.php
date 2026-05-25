<?php
/**
 * Meta WhatsApp Cloud API Configuration
 * ======================================
 * No PC / VPS required — works directly from Hostinger server!
 * 
 * SETUP INSTRUCTIONS:
 * 1. Go to https://developers.facebook.com/apps
 * 2. Create App → Business → WhatsApp
 * 3. Add WhatsApp product → Get API key
 * 4. Copy Phone Number ID & Access Token below
 * 5. Set up Webhook (optional, for incoming messages)
 * 
 * TEST: Open wa_cloud_manager.php in browser
 */

// 🔐 WhatsApp Cloud API Credentials
// Paste these from Meta Developer Console:
$wa_cloud_phone_number_id = "YOUR_PHONE_NUMBER_ID"; // e.g. "123456789012345"
$wa_cloud_access_token    = "YOUR_ACCESS_TOKEN";    // Permanent token from Meta

// 📞 Your WhatsApp Business Number (with country code, NO + sign)
$wa_cloud_business_phone   = "919876543210"; // e.g. 91XXXXXXXXXX

// 🌐 API Endpoint (DO NOT CHANGE)
$wa_cloud_api_url = "https://graph.facebook.com/v22.0/{phone-number-id}/messages";

// 📊 Logging
$wa_cloud_log_file = __DIR__ . "/wa_cloud_log.txt";
$wa_cloud_enable_log = true;

// 🔄 Fallback: If Meta API fails, try local Gateway
$wa_cloud_fallback_local = true;
?>
