<?php
// admin/queues_data.php
ini_set('display_errors',1);
error_reporting(E_ALL);

$mysqli = require __DIR__ . '/../koneksi.php';

$sql = "
  SELECT q.queue_id,
         q.queue_number,
         q.nama,
         q.telepon,
         q.alamat,
         q.keluhan,
         q.id_menu,
         m.name AS service_name,
         q.priority,
         q.status,
         DATE_FORMAT(q.created_at, '%Y-%m-%d %H:%i:%s') AS created_at
  FROM queue q
  LEFT JOIN menu m ON q.id_menu = m.id_menu
  ORDER BY q.queue_id ASC
";

$res = $mysqli->query($sql);
$data = [];
while($row = $res->fetch_assoc()){
  $data[] = $row;
}

header('Content-Type: application/json');
echo json_encode(['data'=>$data], JSON_UNESCAPED_UNICODE);
