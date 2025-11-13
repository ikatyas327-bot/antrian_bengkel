<?php
include __DIR__ . '/../koneksi.php';
?>

<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Ambil Antrian - E-SPEED Bengkel</title>
  <style>
    body {
      font-family: 'Poppins', sans-serif;
      background-color: #f5f7fa;
      display: flex;
      justify-content: center;
      align-items: center;
      height: 100vh;
    }

    .form-container {
      background: white;
      padding: 30px 40px;
      border-radius: 12px;
      box-shadow: 0 4px 10px rgba(0,0,0,0.1);
      width: 400px;
    }

    h2 {
      text-align: center;
      color: #007bff;
    }

    label {
      display: block;
      margin-top: 10px;
      font-weight: 600;
    }

    input, textarea, select {
      width: 100%;
      padding: 10px;
      border: 1px solid #ccc;
      border-radius: 6px;
      margin-top: 5px;
    }

    button {
      margin-top: 20px;
      width: 100%;
      background-color: #00bfa6;
      border: none;
      color: white;
      font-weight: bold;
      padding: 10px;
      border-radius: 6px;
      cursor: pointer;
      transition: 0.3s;
    }

    button:hover {
      background-color: #009d89;
    }
  </style>
</head>
<body>

  <div class="form-container">
    <h2>Ambil Nomor Antrian</h2>
    <form action="proses_antrian.php" method="POST">
      <label>Nama Lengkap</label>
      <input type="text" name="nama" required>

      <label>Nomor Telepon</label>
      <input type="text" name="telepon" required>

      <label>Alamat</label>
      <textarea name="alamat" required></textarea>

      <label>Keluhan</label>
      <textarea name="keluhan"></textarea>

      <label>Pilih Layanan</label>
      <select name="id_menu" required>
        <option value="">-- Pilih Layanan --</option>
        <option value="1">Servis Mesin</option>
        <option value="2">Bubut Besi</option>
        <option value="3">Ganti Oli</option>
      </select>

      <button type="submit" name="submit">Ambil Antrian</button>
    </form>
  </div>

</body>
</html>
