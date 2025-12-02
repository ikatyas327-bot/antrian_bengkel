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
$name = isset($_POST['name']) ? trim($_POST['name']) : '';
$price = isset($_POST['price']) ? trim($_POST['price']) : '';

// Validasi input
if (empty($id_menu)) {
  echo json_encode(['success' => false, 'message' => 'ID layanan tidak boleh kosong']);
  exit;
}

if (empty($name)) {
  echo json_encode(['success' => false, 'message' => 'Nama layanan tidak boleh kosong']);
  exit;
}

if (empty($price)) {
  echo json_encode(['success' => false, 'message' => 'Harga tidak boleh kosong']);
  exit;
}

if (!is_numeric($price) || $price < 0) {
  echo json_encode(['success' => false, 'message' => 'Harga harus berupa angka positif']);
  exit;
}

// Cek apakah layanan ada
$check = $conn->prepare("SELECT id_menu FROM menu WHERE id_menu = ?");
$check->bind_param("i", $id_menu);
$check->execute();
$result = $check->get_result();

if ($result->num_rows === 0) {
  echo json_encode(['success' => false, 'message' => 'Layanan tidak ditemukan']);
  exit;
}

// Cek apakah nama layanan sudah digunakan oleh layanan lain
$check_name = $conn->prepare("SELECT id_menu FROM menu WHERE name = ? AND id_menu != ?");
$check_name->bind_param("si", $name, $id_menu);
$check_name->execute();
$name_result = $check_name->get_result();

if ($name_result->num_rows > 0) {
  echo json_encode(['success' => false, 'message' => 'Nama layanan sudah digunakan oleh layanan lain']);
  exit;
}

// Update data layanan
$stmt = $conn->prepare("UPDATE menu SET name=?, price=? WHERE id_menu=?");
$stmt->bind_param("sdi", $name, $price, $id_menu);

if ($stmt->execute()) {
  echo json_encode([
    'success' => true, 
    'message' => 'Layanan berhasil diupdate!',
    'data' => [
      'id_menu' => $id_menu,
      'name' => $name,
      'price' => $price
    ]
  ]);
} else {
  echo json_encode([
    'success' => false, 
    'message' => 'Gagal mengupdate layanan: ' . $conn->error
  ]);
}

$stmt->close();
$conn->close();
?>