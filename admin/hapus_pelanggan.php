<?php
session_start();
include '../koneksi.php';

// Set header JSON
header('Content-Type: application/json');

// Cek login admin
if (!isset($_SESSION['admin_id'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit;
}

// Cek request method
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

// Validasi action
if (!isset($_POST['action']) || $_POST['action'] !== 'delete') {
    echo json_encode(['success' => false, 'message' => 'Invalid action']);
    exit;
}

// Validasi ID customer
if (!isset($_POST['customer_id']) || empty(trim($_POST['customer_id']))) {
    echo json_encode(['success' => false, 'message' => 'ID pelanggan tidak valid']);
    exit;
}

$customer_id = trim($_POST['customer_id']);

/* ==========================================================
   STEP 1: Cek apakah pelanggan ada
========================================================== */
$check_customer = $conn->prepare("SELECT * FROM customer WHERE customer_id = ?");
$check_customer->bind_param("s", $customer_id);
$check_customer->execute();
$customer_result = $check_customer->get_result();

if ($customer_result->num_rows === 0) {
    echo json_encode(['success' => false, 'message' => 'Pelanggan tidak ditemukan']);
    exit;
}

$customer_data = $customer_result->fetch_assoc();
$check_customer->close();

/* ==========================================================
   STEP 2: Cek apakah masih ada antrian terkait customer
========================================================== */
$check_queue = $conn->prepare("SELECT COUNT(*) as total FROM queue WHERE customer_id = ?");
$check_queue->bind_param("s", $customer_id);
$check_queue->execute();
$queue_result = $check_queue->get_result()->fetch_assoc();
$check_queue->close();

if ($queue_result['total'] > 0) {
    echo json_encode([
        'success' => false,
        'message' => 'Tidak dapat menghapus! Pelanggan "' 
            . htmlspecialchars($customer_data['name']) . 
            '" masih memiliki ' . $queue_result['total'] . ' antrian aktif.'
    ]);
    exit;
}

/* ==========================================================
   STEP 3: Simpan OLD DATA untuk log
========================================================== */
$old_data_json = json_encode([
    'id'           => $customer_data['customer_id'],
    'name'         => $customer_data['name'],
    'phone_number' => $customer_data['phone_number'],
    'address'      => $customer_data['address']
], JSON_UNESCAPED_UNICODE);

/* ==========================================================
   STEP 4: Hapus customer
========================================================== */
$delete_stmt = $conn->prepare("DELETE FROM customer WHERE customer_id = ?");
$delete_stmt->bind_param("s", $customer_id);

if ($delete_stmt->execute() && $delete_stmt->affected_rows > 0) {

    /* ==========================================================
       STEP 5: INSERT LOG KE activity_log
       action_type = DELETE
    ========================================================== */
    $log_stmt = $conn->prepare("
        INSERT INTO activity_log 
        (table_name, record_id, action_type, old_data, new_data, updated_at)
        VALUES ('customer', ?, 'DELETE', ?, NULL, NOW())
    ");
    $log_stmt->bind_param("ss", $customer_id, $old_data_json);
    $log_stmt->execute();
    $log_stmt->close();

    echo json_encode([
        'success' => true,
        'message' => 'Pelanggan "' . htmlspecialchars($customer_data['name']) . '" berhasil dihapus!',
        'deleted_id' => $customer_id
    ]);

} else {
    echo json_encode([
        'success' => false,
        'message' => 'Gagal menghapus pelanggan: ' . $conn->error
    ]);
}

$delete_stmt->close();
$conn->close();
?>
