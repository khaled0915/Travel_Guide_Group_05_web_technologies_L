<?php

$DB_HOST = 'localhost';
$DB_USER = 'root';
$DB_PASS = '';          
$DB_NAME = 'travel_guide_5';

$conn = mysqli_connect($DB_HOST, $DB_USER, $DB_PASS, $DB_NAME);
if (!$conn) {
    die('Connection failed: ' . mysqli_connect_error());
}
mysqli_set_charset($conn, 'utf8mb4');

return [
    'host' => $DB_HOST,
    'user' => $DB_USER,
    'password' => $DB_PASS,
    'database' => $DB_NAME,
    'charset' => 'utf8mb4',
    'connection' => $conn,
];
