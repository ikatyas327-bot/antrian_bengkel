<?php
session_start();
include '../koneksi.php';
if (!isset($_SESSION['admin_id'])) {
  header("Location: login.php");
  exit;
}

// Ambil data antrian (pesanan)
$query = "SELECT q.*, c.name AS nama_pelanggan, m.name AS layanan
          FROM queue q
          LEFT JOIN customer c ON q.customer_id = c.customer_id
          LEFT JOIN menu m ON q.id_menu = m.id_menu
          ORDER BY q.created_at DESC";
$result = $conn->query($query);
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>Data Pesanan - Admin E-SPEED</title>
<link rel="stylesheet" href="style.css">
</head>
<body>

<nav class="sidebar">
  <h2>E-SPEED Admin</h2>
  <ul>
    <li><a href="dashboard.php">🏠 Dashboard</a></li>
    <li><a href="pelanggan.php">👥 Pelanggan</a></li>
    <li><a href="pesanan.php" class="active">🧾 Pesanan</a></li>
    <li><a href="layanan.php">🛠️ Layanan</a></li>
    <li><a href="logout.php" class="logout">🚪 Logout</a></li>
  </ul>
</nav>

<main class="content">
  <h1>Data Pesanan / Antrian</h1>
  <table>
    <tr>
      <th>No</th>
      <th>Nomor Antrian</th>
      <th>Nama Pelanggan</th>
      <th>Layanan</th>
      <th>Status</th>
      <th>Tanggal</th>
      <th>Aksi</th>
    </tr>
    <?php if ($result->num_rows > 0): $no=1; while($row = $result->fetch_assoc()): ?>
    <tr>
      <td><?= $no++ ?></td>
      <td><?= htmlspecialchars($row['queue_number']) ?></td>
      <td><?= htmlspecialchars($row['nama_pelanggan']) ?></td>
      <td><?= htmlspecialchars($row['layanan']) ?></td>
      <td><?= htmlspecialchars($row['status']) ?></td>
      <td><?= htmlspecialchars($row['created_at']) ?></td>
      <td>
        <a href="update_status.php?id=<?= $row['queue_id'] ?>" class="btn-edit">Ubah Status</a>
        <a href="hapus_pesanan.php?id=<?= $row['queue_id'] ?>" class="btn-hapus" onclick="return confirm('Hapus pesanan ini?')">Hapus</a>
      </td>
    </tr>
    <?php endwhile; else: ?>
    <tr><td colspan="7" style="text-align:center;">Belum ada pesanan</td></tr>
    <?php endif; ?>
  </table>
</main>

</body>
</html>
