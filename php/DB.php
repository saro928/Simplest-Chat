<?php

// Show Errors if not enabled in .ini File
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Get OS Environment Variables for connection
$host = getenv("DB_HOST");
$user = getenv("DB_USER");
$pass = getenv("DB_PASSWORD");
$db = getenv("DB_DATABASE");

// Create connection
$conn = new mysqli("localhost", "root", "doris", "chat", 3308);
// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

?>
