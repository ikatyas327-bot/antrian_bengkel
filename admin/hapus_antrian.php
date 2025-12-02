<?php
session_start();
include '../koneksi.php';

// Cek login
if (!isset($_SESSION['admin_id'])) {
  if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
  } else {
    header("Location: login.php");
    exit;
  }
}

// ========== METHOD GET (Link Langsung) ==========
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['id'])) {
  $queue_id = (int)$_GET['id'];
  
  if ($queue_id <= 0) {
    die("ID tidak valid");
  }
  
  // Hapus langsung
  $stmt = $conn->prepare("DELETE FROM queue WHERE queue_id = ?");
  $stmt->bind_param("i", $queue_id);
  $stmt->execute();
  
  header("Location: pesanan.php");
  exit;
}

// ========== METHOD POST (AJAX Modal) ==========
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  header('Content-Type: application/json');
  
  $queue_id = isset($_POST['queue_id']) ? (int)$_POST['queue_id'] : 0;
  
  if ($queue_id <= 0) {
    echo json_encode(['success' => false, 'message' => 'ID tidak valid']);
    exit;
  }
  
  // Ambil data sebelum dihapus
  $check = $conn->query("SELECT queue_number FROM queue WHERE queue_id = $queue_id");
  
  if ($check && $check->num_rows > 0) {
    $data = $check->fetch_assoc();
    $queue_number = $data['queue_number'];
    
    // Hapus
    $conn->query("DELETE FROM queue WHERE queue_id = $queue_id");
    
    echo json_encode([
      'success' => true, 
      'message' => "Pesanan #$queue_number berhasil dihapus!"
    ]);
  } else {
    echo json_encode(['success' => false, 'message' => 'Pesanan tidak ditemukan']);
  }
  exit;
}

// Method tidak valid
echo "Invalid request";
exit;
?>