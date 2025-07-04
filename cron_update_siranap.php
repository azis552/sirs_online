<?php
require_once 'db.php';     // koneksi database
require_once 'env.php';    // baca variabel dari .env
load_env();

// Ambil variabel dari .env
$rs_id     = $_ENV['KEMKES_RS_ID'];
$pass      = $_ENV['KEMKES_PASS'];
$api_url   = $_ENV['KEMKES_API_URL']; // biasanya: https://sirs.kemkes.go.id/fo/index.php/Fasyankes
$timestamp = time(); // UNIX timestamp

// Ambil data mapping
$query = $conn->query("
    SELECT 
        m.id_tt,
        m.id_kamar,
        (
            SELECT COUNT(*) FROM kamar WHERE kd_bangsal = m.id_kamar
        ) AS jumlah,
        (
            SELECT COUNT(*) FROM kamar WHERE kd_bangsal = m.id_kamar AND status = 'isi'
        ) AS terpakai
    FROM mapping_siranap m
");

$cache_file = __DIR__ . '/.siranap_cache.json';
$cache = file_exists($cache_file) ? json_decode(file_get_contents($cache_file), true) : [];

$log = [];

while ($row = $query->fetch_assoc()) {
    $id_tt = $row['id_tt'];
    $jumlah = (int)$row['jumlah'];
    $terpakai = (int)$row['terpakai'];
    $jumlah_ruang = max(1, ceil($jumlah / 2));


    // Siapkan payload
    $payload = [
        "id_t_tt" => $id_tt,
        "jumlah_ruang" => "$jumlah_ruang",
        "jumlah" => "$jumlah",
        "terpakai" => "$terpakai"
    ];

    // Header seperti yang kamu inginkan
    $headers = [
        "Content-Type: application/json",
        "X-rs-id: $rs_id",
        "X-Timestamp: $timestamp",
        "X-pass: $pass"
    ];

    // CURL request PUT
    $ch = curl_init();
    curl_setopt_array($ch, [
        CURLOPT_URL => $api_url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_CUSTOMREQUEST => "PUT",
        CURLOPT_POSTFIELDS => json_encode($payload),
        CURLOPT_HTTPHEADER => $headers,
        CURLOPT_SSL_VERIFYPEER => false
    ]);

    $response = curl_exec($ch);
    $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    curl_close($ch);

    $log[] = [
        'id_tt' => $id_tt,
        'jumlah' => $jumlah,
        'terpakai' => $terpakai,
        'status' => $httpcode,
        'response' => $response,
        'timestamp' => $timestamp,
        'error' => $error
    ];

    $cache[$id_tt] = ['jumlah' => $jumlah, 'terpakai' => $terpakai];
}

// Simpan cache dan log
file_put_contents($cache_file, json_encode($cache, JSON_PRETTY_PRINT));
file_put_contents(__DIR__ . '/.log_siranap.json', json_encode($log, JSON_PRETTY_PRINT));

echo "Update selesai: " . date('Y-m-d H:i:s') . "\n";
