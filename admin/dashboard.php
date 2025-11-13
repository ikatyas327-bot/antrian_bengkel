<?php
session_start();
include '../koneksi.php';

// Pastikan user sudah login
if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit;
}

// Ambil data dari database
$today = date('Y-m-d');

// Hitung jumlah pesanan hari ini
$resultPesanan = $conn->query("SELECT COUNT(*) AS total FROM queue WHERE DATE(created_at) = '$today'");
$pesanan = $resultPesanan->fetch_assoc()['total'] ?? 0;

// Hitung jumlah pelanggan
$resultPelanggan = $conn->query("SELECT COUNT(*) AS total FROM customer");
$pelanggan = $resultPelanggan->fetch_assoc()['total'] ?? 0;

// Hitung jumlah layanan aktif
$resultLayanan = $conn->query("SELECT COUNT(*) AS total FROM menu");
$layanan = $resultLayanan->fetch_assoc()['total'] ?? 0;
?>

<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>Dashboard Admin - E-SPEED</title>
<link rel="stylesheet" href="style.css">
</head>
<body>

<nav class="sidebar">
  <h2>E-SPEED Admin</h2>
  <ul>
    <li><a href="dashboard.php" class="active">🏠 Dashboard</a></li>
    <li><a href="pelanggan.php">👥 Pelanggan</a></li>
    <li><a href="pesanan.php">🧾 Pesanan</a></li>
    <li><a href="layanan.php">🛠️ Layanan</a></li>
    <li><a href="logout.php" class="logout">🚪 Logout</a></li>
  </ul>
</nav>

<main class="content">
  <h1>Selamat Datang, <?= htmlspecialchars($_SESSION['admin_nama']) ?> 👋</h1>
  <p>Gunakan menu di samping untuk mengelola data aplikasi E-SPEED Bengkel.</p>

  <div class="cards">
    <div class="card">
      <h3><?= $pesanan ?></h3>
      <p>Pesanan Hari Ini</p>
    </div>
    <div class="card">
      <h3><?= $pelanggan ?></h3>
      <p>Total Pelanggan</p>
    </div>
    <div class="card">
      <h3><?= $layanan ?></h3>
      <p>Layanan Aktif</p>
    </div>
  </div>
</main>

</body>
</html>
