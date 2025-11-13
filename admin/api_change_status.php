<?php
// admin/api_change_status.php
ini_set('display_errors',1);
error_reporting(E_ALL);

$mysqli = require __DIR__ . '/../koneksi.php';

$queue_id = isset($_POST['queue_id']) ? (int)$_POST['queue_id'] : 0;
$status = isset($_POST['status']) ? $_POST['status'] : '';

if(!$queue_id || !$status){
  echo json_encode(['success'=>false,'error'=>'Missing parameter']);
  exit;
}

$stmt = $mysqli->prepare("UPDATE queue SET status = ? WHERE queue_id = ?");
$stmt->bind_param('si', $status, $queue_id);
$ok = $stmt->execute();
if($ok){
  echo json_encode(['success'=>true]);
} else {
  echo json_encode(['success'=>false,'error'=>$mysqli->error]);
}
