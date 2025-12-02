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
if (!isset($_POST['action']) || $_POST['action'] !== 'update') {
    echo json_encode(['success' => false, 'message' => 'Invalid action']);
    exit;
}

// Validasi input
$required_fields = ['customer_id', 'nama', 'telepon', 'alamat'];
foreach ($required_fields as $field) {
    if (!isset($_POST[$field])) {
        echo json_encode(['success' => false, 'message' => 'Data tidak lengkap']);
        exit;
    }
}

// Ambil data dari POST
$customer_id = trim($_POST['customer_id']);
$nama        = trim($_POST['nama']);
$telepon     = trim($_POST['telepon']);
$alamat      = trim($_POST['alamat']);

// Validasi tidak kosong
if ($customer_id === '' || $nama === '' || $telepon === '' || $alamat === '') {
    echo json_encode(['success' => false, 'message' => 'Semua field harus diisi']);
    exit;
}

// Validasi panjang data
if (strlen($nama) > 100) {
    echo json_encode(['success' => false, 'message' => 'Nama terlalu panjang (maksimal 100 karakter)']);
    exit;
}

if (strlen($telepon) > 20) {
    echo json_encode(['success' => false, 'message' => 'Nomor telepon terlalu panjang (maksimal 20 karakter)']);
    exit;
}

if (strlen($alamat) > 255) {
    echo json_encode(['success' => false, 'message' => 'Alamat terlalu panjang (maksimal 255 karakter)']);
    exit;
}

// --- CEK DATA CUSTOMER ---
$check_stmt = $conn->prepare("SELECT * FROM customer WHERE customer_id = ?");
$check_stmt->bind_param("s", $customer_id);
$check_stmt->execute();
$oldData = $check_stmt->get_result()->fetch_assoc();
$check_stmt->close();

if (!$oldData) {
    echo json_encode(['success' => false, 'message' => 'Pelanggan tidak ditemukan']);
    exit;
}

// --- PROSES UPDATE ---
$update_stmt = $conn->prepare("UPDATE customer SET name=?, phone_number=?, address=? WHERE customer_id=?");
$update_stmt->bind_param("ssss", $nama, $telepon, $alamat, $customer_id);

if ($update_stmt->execute()) {

    // Jika ada perubahan
    if ($update_stmt->affected_rows > 0) {

        // --- LOG PERUBAHAN ---
        $old_data_json = json_encode([
            'id'           => $oldData['customer_id'],
            'name'         => $oldData['name'],
            'phone_number' => $oldData['phone_number'],
            'address'      => $oldData['address']
        ], JSON_UNESCAPED_UNICODE);

        $new_data_json = json_encode([
            'id'           => $customer_id,
            'name'         => $nama,
            'phone_number' => $telepon,
            'address'      => $alamat
        ], JSON_UNESCAPED_UNICODE);

        $log_stmt = $conn->prepare("
            INSERT INTO activity_log (table_name, record_id, action_type, old_data, new_data, updated_at)
            VALUES ('customer', ?, 'UPDATE', ?, ?, NOW())
        ");
        $log_stmt->bind_param("iss", $customer_id, $old_data_json, $new_data_json);
        $log_stmt->execute();
        $log_stmt->close();

        echo json_encode([
            'success' => true,
            'message' => 'Data pelanggan berhasil diupdate!',
            'data' => [
                'customer_id' => $customer_id,
                'nama'        => $nama,
                'telepon'     => $telepon,
                'alamat'      => $alamat
            ]
        ]);

    } else {
        echo json_encode([
            'success' => true,
            'message' => 'Tidak ada perubahan data'
        ]);
    }

} else {
    echo json_encode([
        'success' => false,
        'message' => 'Gagal mengupdate data: ' . $conn->error
    ]);
}

$update_stmt->close();
$conn->close();
?>
