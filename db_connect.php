<?php
// C:\Users\sathi\.gemini\antigravity\scratch\billing_app\db_connect.php

// Set PHP default timezone to Indian Standard Time (IST)
date_default_timezone_set('Asia/Kolkata');

// Intha details-ah unga Hostinger DB details-ku maathunga
$host = "163.128.112.26";
$dbname = "ERD"; // Replace with your DB name
$username = "root";    // Replace with your DB username
$password = "Skenterprises";       // Replace with your DB password

try {
    $conn = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Set MySQL connection timezone to Indian Standard Time (IST)
    $conn->exec("SET time_zone = '+05:30'");
} catch (PDOException $e) {
    $msg = $e->getMessage();
    // Check if the connection failed because MySQL is offline (connection refused/port closed)
    if (strpos($msg, '2002') !== false || strpos($msg, 'actively refused') !== false || strpos($msg, 'not responding') !== false) {
        // Attempt to auto-start local MySQL if in XAMPP on Windows
        if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN' && file_exists('C:\\xampp\\mysql\\bin\\mysqld.exe')) {
            @pclose(@popen('start /B C:\\xampp\\mysql\\bin\\mysqld.exe --defaults-file=C:\\xampp\\mysql\\bin\\my.ini --standalone', 'r'));
            sleep(4); // Wait 4 seconds for MariaDB to initialize
            try {
                $conn = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
                $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                $conn->exec("SET time_zone = '+05:30'");
                return; // Connected successfully on retry!
            } catch (PDOException $retryEx) {
                die("Connection Error (Auto-start MySQL failed): " . $retryEx->getMessage());
            }
        }
    }
    die("Connection Error: " . $msg);
}
?>
