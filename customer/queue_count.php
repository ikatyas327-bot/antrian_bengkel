<?php
header('Content-Type: application/json');
$mysqli = require '../koneksi.php';
$id = intval($_GET['id_menu'] ?? 0);
if (!$id) { echo json_encode(['count'=>0]); exit; }
$stmt = $mysqli->prepare("SELECT COUNT(*) FROM queue WHERE id_menu = ? AND status = 'Menunggu' AND DATE(created_at)=CURDATE()");
$stmt->bind_param("i",$id);
$stmt->execute();
$stmt->bind_result($count);
$stmt->fetch();
$stmt->close();
echo json_encode(['count' => intval($count)]);
