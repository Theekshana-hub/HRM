<?php
// db_connect.php
$host = "localhost";
$user = "root";
$pass = "";              // XAMPP default - change if you have password
$db   = "smart_hris";

$conn = new mysqli($host, $user, $pass, $db);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Optional: set charset
$conn->set_charset("utf8mb4");
?>