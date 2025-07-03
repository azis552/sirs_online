<?php
require 'db.php'; // pastikan sudah ada koneksi $conn

$sql = "
SELECT 
    b.kd_bangsal, 
    b.nm_bangsal, 
    COUNT(k.kd_kamar) AS jumlah_bed_kosong
FROM 
    bangsal b
INNER JOIN 
    kamar k ON b.kd_bangsal = k.kd_bangsal
GROUP BY 
    b.kd_bangsal, b.nm_bangsal;

";

$result = $conn->query($sql);
$data = [];

if ($result) {
    while ($row = $result->fetch_assoc()) {
        $data[] = $row;
    }
}

header('Content-Type: application/json');
echo json_encode($data);
