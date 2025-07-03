<?php
require 'db.php'; // koneksi
header('Content-Type: application/json');

// Ambil data dari form
$id_kamar = $_POST['id_kamar'] ?? null;
$id_tt    = $_POST['id_tt'] ?? null;

if (!$id_kamar || !$id_tt) {
    echo json_encode(['success' => false, 'message' => 'Data tidak lengkap']);
    exit;
}

// Simpan ke tabel mapping_siranap
$stmt = $conn->prepare("INSERT INTO mapping_siranap (id_kamar, id_tt) VALUES (?, ?)");
$stmt->bind_param("ss", $id_kamar, $id_tt);

if ($stmt->execute()) {
    echo json_encode(['success' => true, 'message' => 'Data berhasil disimpan']);
} else {
    echo json_encode(['success' => false, 'message' => 'Gagal menyimpan: ' . $stmt->error]);
}

$stmt->close();
$conn->close();
