<?php
session_start();
include '../koneksi.php';
if (!isset($_SESSION['admin_id'])) {
  header("Location: login.php");
  exit;
}

// Ambil data pelanggan
$result = $conn->query("SELECT * FROM customer ORDER BY customer_id DESC");
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>Data Pelanggan - Admin E-SPEED</title>
<link rel="stylesheet" href="style.css">
</head>
<body>

<nav class="sidebar">
  <h2>E-SPEED Admin</h2>
  <ul>
    <li><a href="dashboard.php">🏠 Dashboard</a></li>
    <li><a href="pelanggan.php" class="active">👥 Pelanggan</a></li>
    <li><a href="pesanan.php">🧾 Pesanan</a></li>
    <li><a href="layanan.php">🛠️ Layanan</a></li>
    <li><a href="logout.php" class="logout">🚪 Logout</a></li>
  </ul>
</nav>

<main class="content">
  <h1>Data Pelanggan</h1>
  <a href="tambah_pelanggan.php" class="btn">+ Tambah Pelanggan</a>

  <table>
    <tr>
      <th>No</th>
      <th>Nama</th>
      <th>No. Telepon</th>
      <th>Alamat</th>
      <th>Aksi</th>
    </tr>
    <?php if ($result->num_rows > 0): $no=1; while($row = $result->fetch_assoc()): ?>
    <tr>
      <td><?= $no++ ?></td>
      <td><?= htmlspecialchars($row['name']) ?></td>
      <td><?= htmlspecialchars($row['phone_number']) ?></td>
      <td><?= htmlspecialchars($row['address']) ?></td>
      <td>
        <a href="edit_pelanggan.php?id=<?= $row['customer_id'] ?>" class="btn-edit">Edit</a>
        <a href="hapus_pelanggan.php?id=<?= $row['customer_id'] ?>" class="btn-hapus" onclick="return confirm('Yakin hapus pelanggan ini?')">Hapus</a>
      </td>
    </tr>
    <?php endwhile; else: ?>
    <tr><td colspan="6" style="text-align:center;">Belum ada data pelanggan</td></tr>
    <?php endif; ?>
  </table>
</main>

</body>
</html>
