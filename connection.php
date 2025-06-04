<?php

// Database credentials - **REPLACE WITH YOUR ACTUAL DETAILS**
$db_host = 'localhost';
$db_user = 'root'; // e.g., 'root'
$db_pass = ''; // e.g., '' or 'password'
$db_name = 'storage';

// Create database connection
$conn = new mysqli($db_host, $db_user, $db_pass, $db_name);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$conn->set_charset("utf8mb4");

// The connection variable $conn is now available for use in other files.
