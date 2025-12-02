<?php
// admin/simpan_antrian.php
ini_set('display_errors',1); error_reporting(E_ALL);
session_start();
$mysqli = require '../koneksi.php';
require '../generate_queue_number.php';
require '../admin_config.php';

if (!isset($_SESSION['admin_id'])) {
    header('Location: login.php');
    exit;
}
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: tambah.php');
    exit;
}

$nama    = trim($_POST['nama'] ?? '');
$telepon = trim($_POST['telepon'] ?? '');
$alamat  = trim($_POST['alamat'] ?? '');
$keluhan = trim($_POST['keluhan'] ?? '');
$id_menu = intval($_POST['id_menu'] ?? 0);
$priority= trim($_POST['priority'] ?? 'Normal');

if (!$nama || !$telepon || !$id_menu) {
    echo "Semua field wajib diisi.";
    exit;
}

// Mulai transaksi
$mysqli->begin_transaction();

try {
    // 1. Cek apakah customer sudah ada berdasarkan nomor telepon
    $check = $mysqli->prepare("SELECT customer_id FROM customer WHERE phone_number = ?");
    $check->bind_param("s", $telepon);
    $check->execute();
    $result = $check->get_result();
    
    if ($result->num_rows > 0) {
        // Customer sudah ada, ambil customer_id
        $row = $result->fetch_assoc();
        $customer_id = $row['customer_id'];
        $check->close();
        
        // Update data customer (opsional, jika nama/alamat berubah)
        $update = $mysqli->prepare("UPDATE customer SET name=?, address=? WHERE customer_id=?");
        $update->bind_param("sss", $nama, $alamat, $customer_id);
        $update->execute();
        $update->close();
        
    } else {
        $check->close();
        
        // Customer baru, generate customer_id
        $q = $mysqli->query("SELECT customer_id FROM customer ORDER BY customer_id DESC LIMIT 1");
        if ($q && $q->num_rows > 0) {
            $last = $q->fetch_assoc()['customer_id'];
            $num = (int) substr($last, 1) + 1;
        } else {
            $num = 1;
        }
        $customer_id = 'C' . str_pad($num, 5, '0', STR_PAD_LEFT);
        
        // Insert customer baru
        $insertCust = $mysqli->prepare("INSERT INTO customer (customer_id, name, phone_number, address) VALUES (?, ?, ?, ?)");
        $insertCust->bind_param("ssss", $customer_id, $nama, $telepon, $alamat);
        if (!$insertCust->execute()) {
            throw new Exception("Gagal insert customer: " . $insertCust->error);
        }
        $insertCust->close();
    }
    
    // 2. Generate nomor antrian
    $queue_number = generate_queue_number($mysqli, $id_menu);
    
    // 3. Insert ke tabel queue DENGAN customer_id
    $stmt = $mysqli->prepare("INSERT INTO queue (queue_number, customer_id, id_menu, keluhan, priority, status, created_at) VALUES (?, ?, ?, ?, ?, 'Menunggu', NOW())");
    $stmt->bind_param("ssiss", $queue_number, $customer_id, $id_menu, $keluhan, $priority);
    
    if (!$stmt->execute()) {
        throw new Exception("Gagal insert queue: " . $stmt->error);
    }
    $stmt->close();
    
    // Commit transaksi
    $mysqli->commit();
    
    header('Location: pesanan.php');
    exit;
    
} catch (Exception $e) {
    // Rollback jika ada error
    $mysqli->rollback();
    echo "Gagal: " . $e->getMessage();
    echo "<br><a href='javascript:history.back()'>Kembali</a>";
}
?>