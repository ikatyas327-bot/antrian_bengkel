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

$queue_number = generate_queue_number($mysqli, $id_menu);

$stmt = $mysqli->prepare("INSERT INTO queue (queue_number, id_menu, priority, nama, telepon, alamat, keluhan, status) VALUES (?, ?, ?, ?, ?, ?, ?, 'Menunggu')");
$stmt->bind_param("sisssss", $queue_number, $id_menu, $priority, $nama, $telepon, $alamat, $keluhan);
if ($stmt->execute()) {
    header('Location: index.php');
    exit;
} else {
    echo "Gagal: " . $stmt->error;
}
