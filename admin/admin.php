<?php
session_start();
include __DIR__ . '/../koneksi.php';

// Cek login admin
if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit;
}

// Ambil nama admin untuk salam
$nama_admin = $_SESSION['admin_nama'] ?? 'Admin';

// Ambil statistik cepat
$total_admin = $conn->query("SELECT COUNT(*) AS total FROM admin")->fetch_assoc()['total'] ?? 0;
$total_pelanggan = $conn->query("SELECT COUNT(*) AS total FROM customer")->fetch_assoc()['total'] ?? 0;
$total_pesanan = $conn->query("SELECT COUNT(*) AS total FROM queue")->fetch_assoc()['total'] ?? 0;
$pesanan_hari_ini = $conn->query("SELECT COUNT(*) AS total FROM queue WHERE DATE(created_at) = CURDATE()")->fetch_assoc()['total'] ?? 0;

// Ambil pesanan terbaru
$pesanan_terbaru = $conn->query("
  SELECT q.queue_number, q.nama, q.telepon, m.name AS layanan, q.status, q.created_at
  FROM queue q
  LEFT JOIN menu m ON q.id_menu = m.id_menu
  ORDER BY q.created_at DESC
  LIMIT 6
");
?>

<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Dashboard Admin - E-SPEED Bengkel</title>
<style>
  body {
    font-family: 'Poppins', sans-serif;
    background-color: #f4f6f9;
    margin: 0;
  }

  header {
    background: #1a1a1a;
    color: white;
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 15px 30px;
  }

  header h1 { margin: 0; font-size: 1.3rem; }
  header a {
    color: #00ffc6;
    text-decoration: none;
    font-weight: 600;
  }

  main { padding: 30px; }

  .greeting {
    font-size: 1.2rem;
    margin-bottom: 20px;
  }

  .stats {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
    gap: 15px;
    margin-bottom: 30px;
  }

  .card {
    background: white;
    padding: 20px;
    border-radius: 10px;
    text-align: center;
    box-shadow: 0 3px 10px rgba(0,0,0,0.1);
    transition: transform 0.2s ease;
  }

  .card:hover { transform: scale(1.03); }

  .card h3 { margin: 0; color: #555; font-size: 1rem; }
  .card p { font-size: 1.6rem; font-weight: bold; margin-top: 10px; color: #007bff; }

  .section {
    background: white;
    padding: 20px;
    border-radius: 10px;
    box-shadow: 0 3px 10px rgba(0,0,0,0.1);
  }

  table {
    width: 100%;
    border-collapse: collapse;
    margin-top: 15px;
  }

  th, td {
    padding: 10px 12px;
    text-align: left;
    border-bottom: 1px solid #eee;
  }

  th {
    background: #007bff;
    color: white;
  }

  tr:nth-child(even) { background: #f9f9f9; }

  .status {
    font-weight: bold;
    border-radius: 6px;
    padding: 4px 8px;
    color: white;
    font-size: 0.85rem;
  }

  .status-menunggu { background-color: #ffc107; }
  .status-diproses { background-color: #17a2b8; }
  .status-selesai { background-color: #28a745; }
  .status-dibatalkan { background-color: #dc3545; }

  footer {
    text-align: center;
    color: #666;
    padding: 20px;
    font-size: 0.9rem;
    margin-top: 30px;
  }

  .nav-links {
    margin-top: 20px;
    display: flex;
    flex-wrap: wrap;
    gap: 10px;
  }

  .nav-links a {
    flex: 1;
    background: #007bff;
    color: white;
    text-align: center;
    padding: 10px;
    border-radius: 8px;
    text-decoration: none;
    font-weight: 500;
    transition: 0.3s;
  }

  .nav-links a:hover {
    background: #005fc7;
  }
</style>
</head>
<body>

<header>
  <h1>Dashboard Admin - E-SPEED Bengkel</h1>
  <a href="logout.php">Logout</a>
</header>

<main>
  <div class="greeting">👋 Selamat datang, <strong><?= htmlspecialchars($nama_admin) ?></strong>!</div>

  <div class="stats">
    <div class="card"><h3>Total Admin</h3><p><?= $total_admin ?></p></div>
    <div class="card"><h3>Total Pelanggan</h3><p><?= $total_pelanggan ?></p></div>
    <div class="card"><h3>Total Pesanan</h3><p><?= $total_pesanan ?></p></div>
    <div class="card"><h3>Pesanan Hari Ini</h3><p><?= $pesanan_hari_ini ?></p></div>
  </div>

  <div class="nav-links">
    <a href="admin_page.php">👥 Kelola Admin</a>
    <a href="dashboard.php">📋 Daftar Antrean</a>
    <a href="kelola_menu.php">🛠️ Kelola Layanan</a>
    <a href="kelola_customer.php">👤 Data Pelanggan</a>
  </div>

  <div class="section">
    <h2>Pesanan Terbaru</h2>
    <table>
      <tr>
        <th>No Antrian</th>
        <th>Nama</th>
        <th>Telepon</th>
        <th>Layanan</th>
        <th>Status</th>
        <th>Tanggal</th>
      </tr>
      <?php if ($pesanan_terbaru && $pesanan_terbaru->num_rows > 0): ?>
        <?php while ($row = $pesanan_terbaru->fetch_assoc()): ?>
        <tr>
          <td><?= htmlspecialchars($row['queue_number']) ?></td>
          <td><?= htmlspecialchars($row['nama']) ?></td>
          <td><?= htmlspecialchars($row['telepon']) ?></td>
          <td><?= htmlspecialchars($row['layanan']) ?></td>
          <td>
            <span class="status status-<?= strtolower($row['status']) ?>">
              <?= htmlspecialchars($row['status']) ?>
            </span>
          </td>
          <td><?= htmlspecialchars(date("d M Y H:i", strtotime($row['created_at']))) ?></td>
        </tr>
        <?php endwhile; ?>
      <?php else: ?>
        <tr><td colspan="6" style="text-align:center;">Belum ada pesanan terbaru</td></tr>
      <?php endif; ?>
    </table>
  </div>
</main>

<footer>
  © <?= date("Y") ?> E-SPEED Bengkel | Sistem Manajemen Antrian & Pemesanan
</footer>

</body>
</html>
