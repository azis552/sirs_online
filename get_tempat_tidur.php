<?php
require_once 'env.php';
load_env(); // load dari file .env

$rs_id  = $_ENV['KEMKES_RS_ID'];
$pass   = $_ENV['KEMKES_PASS'];
$timestamp = time(); // <-- Timestamp realtime saat ini (UNIX timestamp)

$headers = [
    "X-rs-id: $rs_id",
    "X-Timestamp: $timestamp",
    "X-pass: $pass"
];

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, "https://sirs.kemkes.go.id/fo/index.php/Fasyankes");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
$response = curl_exec($ch);
curl_close($ch);

header('Content-Type: application/json');
echo $response;
