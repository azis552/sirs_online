<?php
require 'db.php';
header('Content-Type: application/json');

$id = $_POST['id'] ?? null;

if (!$id) {
    echo json_encode(['success' => false, 'message' => 'ID tidak valid']);
    exit;
}

$stmt = $conn->prepare("DELETE FROM mapping_siranap WHERE id = ?");
$stmt->bind_param("i", $id);

if ($stmt->execute()) {
    echo json_encode(['success' => true, 'message' => 'Data berhasil dihapus']);
} else {
    echo json_encode(['success' => false, 'message' => 'Gagal menghapus data: ' . $stmt->error]);
}

$stmt->close();
$conn->close();
