<?php
session_start();
include '../koneksi.php';

// Enable error reporting untuk debugging
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);

// Cek apakah admin sudah login
if (!isset($_SESSION['admin_id'])) {
  header('Content-Type: application/json');
  echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
  exit;
}

// Set header JSON
header('Content-Type: application/json');

// Pastikan request method adalah POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
  echo json_encode(['success' => false, 'message' => 'Invalid request method']);
  exit;
}

try {
  // Validasi input
  if (!isset($_POST['queue_id']) || !isset($_POST['warranty_status'])) {
    echo json_encode(['success' => false, 'message' => 'Data tidak lengkap']);
    exit;
  }

  $queue_id = intval($_POST['queue_id']);
  $warranty_status = trim($_POST['warranty_status']);

  error_log("=== UPDATE WARRANTY START ===");
  error_log("Queue ID: $queue_id");
  error_log("Warranty Status: $warranty_status");

  // Validasi warranty status
  if (!in_array($warranty_status, ['sudah', 'belum'])) {
    echo json_encode(['success' => false, 'message' => 'Status garansi tidak valid']);
    exit;
  }

  // Cek apakah data repair untuk queue_id ini ada
  $check_stmt = $conn->prepare("SELECT repair_id, is_warranty_used FROM repair WHERE queue_id = ?");
  $check_stmt->bind_param("i", $queue_id);
  $check_stmt->execute();
  $check_result = $check_stmt->get_result();

  if ($check_result->num_rows === 0) {
    echo json_encode(['success' => false, 'message' => 'Data repair tidak ditemukan']);
    exit;
  }

  $repair_data = $check_result->fetch_assoc();
  $check_stmt->close();

  // Update status garansi
  $update_stmt = $conn->prepare("UPDATE repair SET is_warranty_used = ? WHERE queue_id = ?");
  $update_stmt->bind_param("si", $warranty_status, $queue_id);

  if ($update_stmt->execute()) {
    if ($update_stmt->affected_rows > 0) {
      error_log("✅ Status garansi berhasil diupdate");
      
      echo json_encode([
        'success' => true, 
        'message' => 'Status garansi berhasil diubah menjadi "' . ucfirst($warranty_status) . '"',
        'data' => [
          'queue_id' => $queue_id,
          'repair_id' => $repair_data['repair_id'],
          'old_warranty' => $repair_data['is_warranty_used'],
          'new_warranty' => $warranty_status
        ]
      ]);
    } else {
      echo json_encode([
        'success' => true, 
        'message' => 'Status garansi sudah sama, tidak ada perubahan'
      ]);
    }
  } else {
    error_log("❌ Gagal update: " . $update_stmt->error);
    echo json_encode([
      'success' => false, 
      'message' => 'Gagal mengupdate status garansi: ' . $update_stmt->error
    ]);
  }

  $update_stmt->close();

} catch (Exception $e) {
  error_log("Exception in update_warranty: " . $e->getMessage());
  echo json_encode([
    'success' => false, 
    'message' => 'Error: ' . $e->getMessage()
  ]);
}

$conn->close();
?>