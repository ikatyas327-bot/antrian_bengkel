<?php
include __DIR__ . '/../koneksi.php';

// Ambil semua layanan untuk dropdown
$services = [];
$result = $conn->query("SELECT * FROM menu ORDER BY name ASC");
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $services[] = $row;
    }
}

// Jika pelanggan datang dari halaman layanan
$selected_id = isset($_GET['id_menu']) ? $_GET['id_menu'] : '';
$selected_service = null;
if ($selected_id != '') {
    foreach ($services as $srv) {
        if ($srv['id_menu'] == $selected_id) {
            $selected_service = $srv;
            break;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <title>Form Ambil Antrian</title>
  <style>
    body {
      font-family: 'Segoe UI', sans-serif;
      background-color: #f7f7f7;
      display: flex;
      justify-content: center;
      align-items: center;
      height: 100vh;
      margin: 0;
    }
    form {
      background: white;
      padding: 25px 30px;
      border-radius: 10px;
      box-shadow: 0 2px 10px rgba(0,0,0,0.1);
      width: 400px;
    }
    h2 {
      text-align: center;
      margin-bottom: 20px;
    }
    input, textarea, select, button {
      width: 100%;
      padding: 10px;
      margin: 8px 0;
      border-radius: 5px;
      border: 1px solid #ccc;
      font-size: 14px;
    }
    button {
      background: #007bff;
      color: white;
      border: none;
      cursor: pointer;
      font-weight: bold;
      transition: 0.3s;
    }
    button:hover {
      background: #0056b3;
    }
  </style>
</head>
<body>

<form action="simpan_antrian.php" method="POST">
  <h2>Form Ambil Antrian</h2>

  <label>Nama Anda</label>
  <input type="text" name="nama" required>

  <label>No. Telepon</label>
  <input type="text" name="telepon" required>

  <label>Alamat</label>
  <textarea name="alamat" required></textarea>

  <label>Layanan</label>
  <?php if ($selected_service): ?>
      <input type="hidden" name="id_menu" value="<?= $selected_service['id_menu'] ?>">
      <input type="text" value="<?= htmlspecialchars($selected_service['name']) ?>" disabled>
  <?php else: ?>
      <select name="id_menu" required>
        <option value="">-- Pilih Layanan --</option>
        <?php foreach ($services as $srv): ?>
          <option value="<?= $srv['id_menu'] ?>"><?= htmlspecialchars($srv['name']) ?></option>
        <?php endforeach; ?>
      </select>
  <?php endif; ?>
    <label>Keluhan / Detail</label>
<textarea name="keluhan" rows="3" placeholder="Tuliskan masalah atau permintaan khusus..."></textarea>
<label>Prioritas</label>
<select name="priority">
  <option value="Normal" selected>Normal</option>
    <option value="Urgent">Urgent</option>
</select>
  <button type="submit">Ambil Antrian</button>
</form>
</body>
</html>
