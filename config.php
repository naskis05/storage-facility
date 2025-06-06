<?php

$host = 'fhcx2.h.filess.io';
$db   = 'warehouse_sweptusual';
$user = 'warehouse_sweptusual';
$pass = '2f6e408a38d0070a9dc86577ab839cef149738cd';
$port = "61002";

$dsn = "mysql:host=$host;port=$port;dbname=$db;charset=utf8mb4";

$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
    $pdo = new PDO($dsn, $user, $pass, $options);
} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}
?>