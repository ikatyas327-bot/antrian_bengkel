<?php
// admin/api_delete.php
ini_set('display_errors',1);
error_reporting(E_ALL);

$mysqli = require __DIR__ . '/../koneksi.php';
$queue_id = isset($_POST['queue_id']) ? (int)$_POST['queue_id'] : 0;
if(!$queue_id){ echo json_encode(['success'=>false,'error'=>'Missing id']); exit; }

$stmt = $mysqli->prepare("DELETE FROM queue WHERE queue_id = ?");
$stmt->bind_param('i',$queue_id);
$ok = $stmt->execute();
echo json_encode(['success' => $ok, 'error'=> $ok? null : $mysqli->error ]);
