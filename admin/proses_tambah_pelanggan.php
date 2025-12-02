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
if (!isset($_POST['action']) || $_POST['action'] !== 'add') {
    echo json_encode(['success' => false, 'message' => 'Invalid action']);
    exit;
}

// Validasi input
$required = ['nama', 'telepon', 'alamat'];
foreach ($required as $field) {
    if (!isset($_POST[$field])) {
        echo json_encode(['success' => false, 'message' => 'Data tidak lengkap']);
        exit;
    }
}

// Ambil data POST
$nama     = trim($_POST['nama']);
$telepon  = trim($_POST['telepon']);
$alamat   = trim($_POST['alamat']);

// Validasi tidak kosong
if ($nama === '' || $telepon === '' || $alamat === '') {
    echo json_encode(['success' => false, 'message' => 'Semua field harus diisi!']);
    exit;
}

// Validasi panjang
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

/* ==========================================================
   FUNGSI GENERATE ID CUSTOMER
   Format ID: CUST001, CUST002, dst
========================================================== */
function generateCustomerId($conn)
{
    $query = "SELECT customer_id FROM customer ORDER BY customer_id DESC LIMIT 1";
    $result = $conn->query($query);

    if ($result && $result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $lastId = $row['customer_id'];

        // Ambil angka di akhir ID
        $number = (int)substr($lastId, 4);
        $newNumber = $number + 1;

        return 'CUST' . str_pad($newNumber, 3, '0', STR_PAD_LEFT);
    } else {
        // Jika tabel kosong
        return 'CUST001';
    }
}

// Generate ID pelanggan
$customer_id = generateCustomerId($conn);

// Cek jika ID sudah dipakai (jaga-jaga race condition)
$check_stmt = $conn->prepare("SELECT customer_id FROM customer WHERE customer_id = ?");
$check_stmt->bind_param("s", $customer_id);
$check_stmt->execute();
$exist = $check_stmt->get_result()->num_rows;
$check_stmt->close();

if ($exist > 0) {
    $customer_id = generateCustomerId($conn);
}

/* ==========================================================
   INSERT DATA CUSTOMER BARU
========================================================== */
$stmt = $conn->prepare("INSERT INTO customer (customer_id, name, phone_number, address) 
                        VALUES (?, ?, ?, ?)");
$stmt->bind_param("ssss", $customer_id, $nama, $telepon, $alamat);

if ($stmt->execute()) {

    // --- DATA BARU (untuk LOG) ---
    $new_data_json = json_encode([
        'id'           => $customer_id,
        'name'         => $nama,
        'phone_number' => $telepon,
        'address'      => $alamat
    ], JSON_UNESCAPED_UNICODE);

    /* ==========================================================
       INSERT LOG ACTIVITY (ACTION: INSERT)
    ========================================================== */
    $log_stmt = $conn->prepare("
        INSERT INTO activity_log 
        (table_name, record_id, action_type, old_data, new_data, updated_at) 
        VALUES ('customer', ?, 'INSERT', NULL, ?, NOW())
    ");
    $log_stmt->bind_param("ss", $customer_id, $new_data_json);
    $log_stmt->execute();
    $log_stmt->close();

    echo json_encode([
        'success' => true,
        'message' => 'Pelanggan berhasil ditambahkan!',
        'data' => [
            'customer_id'   => $customer_id,
            'name'          => $nama,
            'phone_number'  => $telepon,
            'address'       => $alamat
        ]
    ]);

} else {
    echo json_encode([
        'success' => false,
        'message' => 'Gagal menambahkan pelanggan: ' . $conn->error
    ]);
}

$stmt->close();
$conn->close();
?>
