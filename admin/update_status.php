<?php
include __DIR__ . '/../koneksi.php';

$id = $_GET['id'] ?? null;
if (!$id) die("ID tidak ditemukan.");

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $status = $_POST['status'];
  $query = "UPDATE queue SET status='$status' WHERE queue_id=$id";
  if ($conn->query($query)) {
    header("Location: dashboard.php");
    exit;
  } else {
    echo "Gagal mengubah status: " . $conn->error;
  }
}

$result = $conn->query("SELECT * FROM queue WHERE queue_id=$id");
$data = $result->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <title>Ubah Status</title>
  <style>
    body { font-family: Poppins; background: #f5f5f5; display: flex; justify-content: center; align-items: center; height: 100vh; }
    form { background: white; padding: 20px; border-radius: 10px; box-shadow: 0 3px 6px rgba(0,0,0,0.1); width: 320px; }
    select, button { width: 100%; padding: 10px; margin-top: 10px; border-radius: 6px; border: 1px solid #ccc; }
    button { background: #007bff; color: white; font-weight: 600; cursor: pointer; }
  </style>
</head>
<body>
  <form method="POST">
    <h3>Ubah Status - <?= htmlspecialchars($data['queue_number']); ?></h3>
    <label>Status:</label>
    <select name="status">
      <option <?= $data['status']=='Menunggu'?'selected':'' ?>>Menunggu</option>
      <option <?= $data['status']=='Diproses'?'selected':'' ?>>Diproses</option>
      <option <?= $data['status']=='Selesai'?'selected':'' ?>>Selesai</option>
      <option <?= $data['status']=='Dibatalkan'?'selected':'' ?>>Dibatalkan</option>
    </select>
    <button type="submit">Simpan</button>
  </form>
</body>
</html>
