<?php
include __DIR__ . '/../koneksi.php';

$id = $_GET['id'] ?? null;
if (!$id) die("ID tidak ditemukan.");

$conn->query("DELETE FROM queue WHERE queue_id=$id");
header("Location: dashboard.php");
exit;
?>
