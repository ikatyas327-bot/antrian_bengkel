<?php
session_start();
include '../koneksi.php';

// Pastikan hanya admin yang bisa akses
if (!isset($_SESSION['admin_id'])) {
  echo json_encode(['success' => false, 'message' => 'Unauthorized']);
  exit;
}

// Hanya terima POST request
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
  echo json_encode(['success' => false, 'message' => 'Invalid request method']);
  exit;
}

header('Content-Type: application/json');

// Ambil data dari POST
$id_menu = isset($_POST['id_menu']) ? trim($_POST['id_menu']) : '';

// Validasi input
if (empty($id_menu)) {
  echo json_encode(['success' => false, 'message' => 'ID layanan tidak boleh kosong']);
  exit;
}

// Cek apakah layanan ada
$check_service = $conn->prepare("SELECT id_menu, name FROM menu WHERE id_menu = ?");
$check_service->bind_param("i", $id_menu);
$check_service->execute();
$service_result = $check_service->get_result();

if ($service_result->num_rows === 0) {
  echo json_encode(['success' => false, 'message' => 'Layanan tidak ditemukan']);
  exit;
}

// Cek apakah layanan sedang digunakan dalam order_detail
$check_orders = $conn->prepare("SELECT COUNT(*) as total FROM order_detail WHERE id_menu = ?");
$check_orders->bind_param("i", $id_menu);
$check_orders->execute();
$orders_result = $check_orders->get_result()->fetch_assoc();

if ($orders_result['total'] > 0) {
  echo json_encode([
    'success' => false, 
    'message' => 'Tidak dapat menghapus! Layanan ini masih digunakan dalam ' . $orders_result['total'] . ' pesanan.'
  ]);
  exit;
}

// Hapus layanan
$delete = $conn->prepare("DELETE FROM menu WHERE id_menu = ?");
$delete->bind_param("i", $id_menu);

if ($delete->execute()) {
  echo json_encode([
    'success' => true, 
    'message' => 'Layanan berhasil dihapus!',
    'deleted_id' => $id_menu
  ]);
} else {
  echo json_encode([
    'success' => false, 
    'message' => 'Gagal menghapus layanan: ' . $conn->error
  ]);
}

$delete->close();
$conn->close();
?>