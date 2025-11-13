<?php
include '../koneksi.php';
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
  $name = $_POST['name'];
  $price = $_POST['price'];
  $conn->query("INSERT INTO menu (name, price) VALUES ('$name', '$price')");
  header("Location: layanan.php");
}
?>
<!DOCTYPE html>
<html>
<head><title>Tambah Layanan</title><link rel="stylesheet" href="style.css"></head>
<body>
<main class="content">
  <h1>Tambah Layanan</h1>
  <form method="POST">
    <label>Nama Layanan</label>
    <input type="text" name="name" required>

    <label>Harga</label>
    <input type="number" name="price" required>

    <button type="submit" class="btn">Simpan</button>
    <a href="layanan.php" class="btn-hapus">Batal</a>
  </form>
</main>
</body>
</html>
