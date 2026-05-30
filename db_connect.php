<?php
// C:\Users\sathi\.gemini\antigravity\scratch\billing_app\db_connect.php

// Set PHP default timezone to Indian Standard Time (IST)
date_default_timezone_set('Asia/Kolkata');

// Updated for Docker Environment
$host = "db"; 
$dbname = "erd_sk"; 
$username = "erd_sk";    
$password = "Skenterprises";       

try {
    $conn = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Set MySQL connection timezone to Indian Standard Time (IST)
    $conn->exec("SET time_zone = '+05:30'");
} catch (PDOException $e) {
    die("Database Connection Error: " . $e->getMessage());
}
?>