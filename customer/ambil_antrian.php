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
      font-family: Arial, sans-serif;
      background: #f3f3f3;
      padding: 0;
      margin: 0;
      display: flex;
      justify-content: center;
      align-items: center;
      height: 100vh;
    }

    .container {
      width: 100%;
      max-width: 420px;
      background: #fff;
      padding: 25px;
      border-radius: 10px;
      box-shadow: 0 3px 10px rgba(0,0,0,0.1);
    }

    h2 {
      text-align: center;
      margin-bottom: 20px;
      font-size: 22px;
      font-weight: bold;
      color: #333;
    }

    label {
      font-size: 14px;
      color: #333;
      font-weight: bold;
    }

    input, textarea, select {
      width: 95%;
      padding: 10px;
      margin: 6px 0 15px 0;
      border: 1px solid #ccc;
      border-radius: 6px;
      font-size: 14px;
    }

    textarea {
      resize: none;
    }

    /* Tombol kiri & kanan */
    .button-row {
      display: flex;
      gap: 10px;
      margin-top: 10px;
    }

    .back-btn, .submit-btn {
      flex: 1;
      padding: 12px;
      border: none;
      border-radius: 6px;
      font-size: 15px;
      font-weight: bold;
      cursor: pointer;
      text-align: center;
      text-decoration: none;
      display: inline-block;
    }

    .back-btn {
      background: #777;
      color: white;
    }

    .back-btn:hover {
      background: #5c5c5c;
    }

    .submit-btn {
      background: #3498db;
      color: white;
    }

    .submit-btn:hover {
      background: #2c82c9;
    }
  </style>

</head>
<body>

<div class="container">
<form action="simpan_antrian.php" method="POST">
  <h2>Ambil Antrian</h2>

  <label>Nama Anda</label>
  <input type="text" name="nama" required>

  <label>No. Telepon</label>
  <input type="text" name="telepon" required>

  <label>Alamat</label>
  <textarea name="alamat" rows="3" required></textarea>

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
  <textarea name="keluhan" rows="3" placeholder="Tuliskan keluhan (opsional)..."></textarea>

  <!-- Tombol Kembali & Ambil Antrian -->
  <div class="button-row">
      <a href="index.php" class="back-btn">Kembali</a>
      <button type="submit" class="submit-btn">Ambil Antrian</button>
  </div>

</form>
</div>

</body>
</html>
