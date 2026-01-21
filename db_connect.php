<?php
/**
 * db_connect.php
 * Secure database connection file for Employee Census System
 */

// Database configuration
$host = "localhost";
$user = "root";
$pass = "";
$dbname = "employee_census";

// Create connection using MySQLi
$conn = new mysqli($host, $user, $pass, $dbname);

// Check connection
if ($conn->connect_error) {
    die("❌ Database connection failed: " . $conn->connect_error);
}

// Set UTF-8 encoding for consistent data storage
if (!$conn->set_charset("utf8mb4")) {
    die("❌ Error setting character set utf8mb4: " . $conn->error);
}

// Enable strict error reporting (for development)
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

// Optional: you can suppress error details in production like this:
// error_reporting(0);

// You can now safely use $conn in other files via include("db_connect.php");
?>
