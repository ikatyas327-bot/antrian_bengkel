<?php
// simpan_antrian.php (customer)
ini_set('display_errors',1); error_reporting(E_ALL);

$mysqli = require 'koneksi.php';
require 'generate_queue_number.php';
require 'admin_config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: customer/index.php');
    exit;
}

$nama    = trim($_POST['nama'] ?? '');
$telepon = trim($_POST['telepon'] ?? '');
$alamat  = trim($_POST['alamat'] ?? '');
$keluhan = trim($_POST['keluhan'] ?? '');
$id_menu = intval($_POST['id_menu'] ?? 0);
$priority= trim($_POST['priority'] ?? 'Normal');

if (!$nama || !$telepon || !$alamat || !$id_menu) {
    echo "Semua field wajib diisi.";
    exit;
}

$queue_number = generate_queue_number($mysqli, $id_menu);

$stmt = $mysqli->prepare("INSERT INTO queue (queue_number, id_menu, priority, nama, telepon, alamat, keluhan, status) VALUES (?, ?, ?, ?, ?, ?, ?, 'Menunggu')");
$stmt->bind_param("sisssss", $queue_number, $id_menu, $priority, $nama, $telepon, $alamat, $keluhan);
$ok = $stmt->execute();
if (!$ok) {
    echo "Gagal menyimpan: " . $stmt->error;
    exit;
}
$insert_id = $stmt->insert_id;
$stmt->close();

// redirect ke receipt (struk)
header("Location: customer/receipt.php?id=" . $insert_id);
exit;
