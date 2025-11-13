<?php
session_start();
include '../koneksi.php';
if (!isset($_SESSION['admin_id'])) {
  header("Location: login.php");
  exit;
}

$result = $conn->query("SELECT * FROM menu ORDER BY id_menu DESC");
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>Data Layanan - Admin</title>
<link rel="stylesheet" href="style.css">
</head>
<body>

<nav class="sidebar">
  <h2>E-SPEED Admin</h2>
  <ul>
    <li><a href="dashboard.php">🏠 Dashboard</a></li>
    <li><a href="pelanggan.php">👥 Pelanggan</a></li>
    <li><a href="pesanan.php">🧾 Pesanan</a></li>
    <li><a href="layanan.php" class="active">🛠️ Layanan</a></li>
    <li><a href="logout.php" class="logout">🚪 Logout</a></li>
  </ul>
</nav>

<main class="content">
  <h1>Data Layanan</h1>
  <a href="tambah_layanan.php" class="btn">+ Tambah Layanan</a>

  <table>
    <tr>
      <th>No</th>
      <th>Nama Layanan</th>
      <th>Harga</th>
      <th>Aksi</th>
    </tr>
    <?php if ($result->num_rows > 0): $no=1; while($row = $result->fetch_assoc()): ?>
    <tr>
      <td><?= $no++ ?></td>
      <td><?= htmlspecialchars($row['name']) ?></td>
      <td>Rp<?= number_format($row['price'], 0, ',', '.') ?></td>
      <td>
        <a href="edit_layanan.php?id=<?= $row['id_menu'] ?>" class="btn-edit">Edit</a>
        <a href="hapus_layanan.php?id=<?= $row['id_menu'] ?>" class="btn-hapus" onclick="return confirm('Hapus layanan ini?')">Hapus</a>
      </td>
    </tr>
    <?php endwhile; else: ?>
    <tr><td colspan="4" style="text-align:center;">Belum ada layanan</td></tr>
    <?php endif; ?>
  </table>
</main>

</body>
</html>
