<?php
require_once 'env.php';
load_env();

$host   = $_ENV['DB_HOST'] ?? 'localhost';
$port   = $_ENV['DB_PORT'] ?? '3306'; // Default MySQL port
$user   = $_ENV['DB_USER'] ?? 'root';
$pass   = $_ENV['DB_PASS'] ?? '';
$dbname = $_ENV['DB_NAME'] ?? 'rs';
// var_dump($host, $port, $user, $pass, $dbname);die;
$conn = new mysqli($host, $user, $pass, $dbname, $port);
if ($conn->connect_error) {
    die("Koneksi database gagal: " . $conn->connect_error);
}
