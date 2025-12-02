<?php
session_start();
include '../koneksi.php';

if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit;
}

$today = date('Y-m-d');

// Ubah ke 2 bulan ini
$twoMonthsAgo = date('Y-m-d', strtotime('-2 months'));
$resultPesanan = $conn->query("SELECT COUNT(*) AS total FROM queue WHERE DATE(created_at) >= '$twoMonthsAgo'");
$pesanan = $resultPesanan->fetch_assoc()['total'] ?? 0;

$resultPelanggan = $conn->query("SELECT COUNT(*) AS total FROM customer");
$pelanggan = $resultPelanggan->fetch_assoc()['total'] ?? 0;

$resultLayanan = $conn->query("SELECT COUNT(*) AS total FROM menu");
$layanan = $resultLayanan->fetch_assoc()['total'] ?? 0;

/* ===== STATUS PESANAN (case-insensitive) ===== */
$totalAll = $conn->query("SELECT COUNT(*) AS t FROM queue")->fetch_assoc()['t'] ?? 0;
$totalSelesai = $conn->query("SELECT COUNT(*) AS t FROM queue WHERE LOWER(status)='selesai'")->fetch_assoc()['t'] ?? 0;
$totalProses = $conn->query("SELECT COUNT(*) AS t FROM queue WHERE LOWER(status)='diproses'")->fetch_assoc()['t'] ?? 0;
$totalMenunggu = $conn->query("SELECT COUNT(*) AS t FROM queue WHERE LOWER(status)='menunggu'")->fetch_assoc()['t'] ?? 0;

/* ===== QUERY: Pesanan 7 Hari (based on created_at) ===== */
$labelHarian = [];
$dataHarian = [];

for ($i = 6; $i >= 0; $i--) {
    $d = date('Y-m-d', strtotime("-$i days"));
    $labelHarian[] = date('d M', strtotime($d));
    $dataHarian[$d] = 0;
}

$sql7 = "
    SELECT DATE(created_at) AS tgl, COUNT(*) AS total
    FROM queue
    WHERE created_at >= DATE_SUB(CURDATE(), INTERVAL 6 DAY)
    GROUP BY DATE(created_at)
    ORDER BY DATE(created_at)
";
$res7 = $conn->query($sql7);
if ($res7) {
    while ($r = $res7->fetch_assoc()) {
        $tgl = $r['tgl'];
        $dataHarian[$tgl] = (int)$r['total'];
    }
}
$dataHarianFinal = array_values($dataHarian);

/* ===== QUERY: Pesanan 2 Bulan Terakhir (per minggu) ===== */
$labelsMinggu = [];
$dataMingguan = [];

// Hitung minggu dari 8 minggu yang lalu sampai minggu ini
$currentWeek = date('oW'); // Format: YearWeek
$weeksData = [];

// Query untuk mendapatkan data 8 minggu terakhir
$sqlWeek = "
    SELECT YEARWEEK(created_at, 1) AS yw, COUNT(*) AS total
    FROM queue
    WHERE created_at >= DATE_SUB(CURDATE(), INTERVAL 8 WEEK)
    GROUP BY YEARWEEK(created_at, 1)
    ORDER BY YEARWEEK(created_at, 1)
";
$resWeek = $conn->query($sqlWeek);

// Simpan data dari database ke array
$dbWeeks = [];
if ($resWeek && $resWeek->num_rows) {
    while ($r = $resWeek->fetch_assoc()) {
        $dbWeeks[$r['yw']] = (int)$r['total'];
    }
}

// Buat array untuk 8 minggu terakhir, dimulai dari minggu pertama
for ($i = 7; $i >= 0; $i--) {
    $weekDate = strtotime("-$i weeks");
    $yearWeek = date('oW', $weekDate);
    
    $labelsMinggu[] = 'Minggu ' . (8 - $i);
    $dataMingguan[] = isset($dbWeeks[$yearWeek]) ? $dbWeeks[$yearWeek] : 0;
}

// ✅ PERBAIKAN: HANYA hapus pesanan yang BELUM SELESAI dan lebih dari 2 bulan
// Pesanan yang sudah selesai akan dipindahkan ke service & repair oleh trigger
// Jadi kita hanya perlu hapus yang Menunggu/Diproses yang sudah kadaluarsa

$conn->query("DELETE FROM queue 
              WHERE created_at < DATE_SUB(CURDATE(), INTERVAL 2 MONTH) 
              AND LOWER(status) IN ('menunggu', 'diproses')");

// ✅ Untuk pesanan Selesai yang lebih dari 2 bulan, 
// pastikan sudah masuk ke service & repair (trigger akan handle otomatis)
// Baru kemudian hapus dari queue untuk menghemat ruang

$old_completed = $conn->query("
    SELECT q.queue_id 
    FROM queue q 
    WHERE q.created_at < DATE_SUB(CURDATE(), INTERVAL 2 MONTH) 
    AND LOWER(q.status) = 'selesai'
    AND EXISTS (SELECT 1 FROM repair r WHERE r.queue_id = q.queue_id)
");

if ($old_completed && $old_completed->num_rows > 0) {
    $queue_ids_to_delete = [];
    while ($row = $old_completed->fetch_assoc()) {
        $queue_ids_to_delete[] = $row['queue_id'];
    }
    
    if (!empty($queue_ids_to_delete)) {
        $ids_string = implode(',', $queue_ids_to_delete);
        $conn->query("DELETE FROM queue WHERE queue_id IN ($ids_string)");
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1.0"/>
<title>E-SPEED Dashboard</title>

<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">

<!-- Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<style>
:root {
  --bg: #f8f9fa;
  --text: #212529;
  --text-secondary: #6c757d;
  --border: #dee2e6;
  --sidebar-bg: #1a1d29;
  --card-bg: #ffffff;
  --primary: #2563eb;
  --primary-dark: #1e40af;
  --success: #059669;
  --danger: #dc2626;
  --warning: #d97706;
}

.dark {
  --bg: #0f1419;
  --text: #e4e6eb;
  --text-secondary: #8b949e;
  --border: #30363d;
  --sidebar-bg: #0d1117;
  --card-bg: #161b22;
}

* {
  margin: 0;
  padding: 0;
  box-sizing: border-box;
}

body {
  font-family: 'Inter', sans-serif;
  background: var(--bg);
  color: var(--text);
  transition: background 0.3s ease, color 0.3s ease;
}

::-webkit-scrollbar {
  width: 8px;
}

::-webkit-scrollbar-track {
  background: transparent;
}

::-webkit-scrollbar-thumb {
  background: var(--border);
  border-radius: 4px;
}

/* SIDEBAR */
.sidebar {
  width: 260px;
  height: 100vh;
  position: fixed;
  background: var(--sidebar-bg);
  padding: 24px 0;
  z-index: 1000;
  transition: width 0.3s ease;
}

.sidebar.collapsed {
  width: 70px;
}

.sidebar h2 {
  text-align: center;
  font-size: 1.5rem;
  margin-bottom: 32px;
  color: #fff;
  font-weight: 700;
  transition: opacity 0.3s ease;
}

.sidebar.collapsed h2 {
  opacity: 0;
}

.sidebar ul {
  list-style: none;
  padding: 0 12px;
}

.sidebar ul li {
  margin: 4px 0;
}

.sidebar ul li a {
  display: flex;
  align-items: center;
  gap: 12px;
  padding: 12px 16px;
  text-decoration: none;
  color: #a0a0a0;
  border-radius: 8px;
  transition: all 0.2s ease;
  font-size: 0.9rem;
  font-weight: 500;
}

.sidebar ul li a:hover {
  background: rgba(255,255,255,0.08);
  color: #fff;
}

.sidebar ul li a.active {
  background: var(--primary);
  color: #fff;
}

.sidebar ul li a .icon {
  font-size: 1.2rem;
  min-width: 20px;
  text-align: center;
}

.sidebar.collapsed a span:not(.icon) {
  display: none;
}

.logout {
  color: #ef4444 !important;
}

.logout:hover {
  background: rgba(239, 68, 68, 0.1) !important;
}

/* TOPBAR */
.topbar {
  margin-left: 260px;
  padding: 20px 32px;
  background: var(--card-bg);
  border-bottom: 1px solid var(--border);
  display: flex;
  justify-content: space-between;
  align-items: center;
  transition: margin-left 0.3s ease;
}

.sidebar.collapsed ~ .topbar {
  margin-left: 70px;
}

.topbar h1 {
  font-size: 1.5rem;
  font-weight: 600;
  color: var(--text);
}

.switch-container {
  display: flex;
  align-items: center;
  gap: 12px;
}

.toggle-btn {
  padding: 8px 16px;
  background: var(--primary);
  color: #fff;
  border: none;
  border-radius: 6px;
  cursor: pointer;
  font-weight: 500;
  font-size: 0.9rem;
  transition: background 0.2s ease;
}

.toggle-btn:hover {
  background: var(--primary-dark);
}

.avatar {
  width: 40px;
  height: 40px;
  border-radius: 50%;
  background: var(--primary);
  display: flex;
  justify-content: center;
  align-items: center;
  font-size: 1rem;
  color: #fff;
  font-weight: 600;
}

/* CONTENT */
.content {
  margin-left: 260px;
  padding: 32px;
  transition: margin-left 0.3s ease;
  min-height: calc(100vh - 70px);
}

.sidebar.collapsed ~ .content {
  margin-left: 70px;
}

/* total pesanan */
.main-stats-row {
  display: grid;
  grid-template-columns: repeat(3, 1fr);
  gap: 24px;
  margin-top: 24px;
}

.main-stat-card {
  background: var(--card-bg);
  padding: 28px;
  border-radius: 12px;
  border: 1px solid var(--border);
  text-align: left;
  transition: 0.2s ease;
}

.main-stat-card:hover {
  transform: translateY(-3px);
}

.main-stat-card h3 {
  font-size: 1.2rem;
  margin-bottom: 10px;
  color: var(--text);
  font-weight: 600;
}

.main-stat-card .main-num {
  font-size: 2.6rem;
  font-weight: 800;
  margin-bottom: 6px;
}

.main-stat-card p {
  font-size: 0.9rem;
  color: var(--text-secondary);
}

@media (max-width: 900px) {
  .main-stats-row {
    grid-template-columns: 1fr;
  }
}

/* CARDS */
.status-row {
  display: flex;
  gap: 16px;
  margin-top: 20px;
}

.status-card {
  flex: 1;
  min-width: 150px;
  background: var(--card-bg);
  padding: 16px;
  border-radius: 8px;
  border: 1px solid var(--border);
  text-align: center;
}

.status-card h4 {
  font-size: 1rem;
  color: var(--text-secondary);
  margin-bottom: 6px;
  font-weight: 600;
}

.status-card .stat-num {
  font-size: 1.6rem;
  font-weight: 800;
}

@media (max-width: 768px) {
  .status-row {
    flex-direction: column;
  }
}

/* GRAPH ROW */
.graph-row {
  display: flex;
  gap: 20px;
  margin-top: 30px;
  flex-wrap: wrap;
}

.graph-box {
  flex: 1;
  min-width: 320px;
  background: var(--card-bg);
  padding: 20px;
  border-radius: 12px;
  border: 1px solid var(--border);
}

.graph-box canvas {
  width: 100% !important;
  height: 260px !important;
}

/* RESPONSIVE */
@media (max-width: 1024px) {
  .sidebar {
    width: 70px;
  }
  
  .sidebar h2 {
    opacity: 0;
  }
  
  .sidebar a span:not(.icon) {
    display: none;
  }
  
  .topbar,
  .content {
    margin-left: 70px;
  }
}

@media (max-width: 768px) {
  .sidebar {
    width: 100%;
    height: auto;
    position: relative;
  }
  
  .sidebar.collapsed {
    width: 100%;
  }
  
  .sidebar h2 {
    opacity: 1;
  }
  
  .sidebar a span {
    display: inline;
  }
  
  .topbar {
    margin-left: 0;
    padding: 16px 20px;
  }
  
  .topbar h1 {
    font-size: 1.25rem;
  }
  
  .content {
    margin-left: 0;
    padding: 20px;
  }
  
  .cards {
    grid-template-columns: 1fr;
    gap: 16px;
  }
}
</style>
</head>
<body>

<!-- SIDEBAR -->
<div class="sidebar" id="sidebar">
  <h2>E-SPEED</h2>

  <ul>
    <li><a href="dashboard.php" class="active"><span class="icon">🏠</span> <span>Dashboard</span></a></li>
    <li><a href="pelanggan.php"><span class="icon">👥</span> <span>Pelanggan</span></a></li>
    <li><a href="pesanan.php"><span class="icon">🧾</span> <span>Pesanan</span></a></li>
    <li><a href="layanan.php"><span class="icon">🛠️</span> <span>Layanan</span></a></li>
    <li><a href="riwayat_repair.php"><span class="icon">📋</span> <span>Riwayat Repair</span></a></li>
    <li><a href="logout.php" class="logout"><span class="icon">🚪</span> <span>Logout</span></a></li>
  </ul>
</div>

<!-- TOPBAR -->
<div class="topbar">
  <h1>Selamat Datang, <?= htmlspecialchars($_SESSION['admin_nama']) ?></h1>

  <div class="switch-container">
    <button class="toggle-btn" onclick="toggleSidebar()">☰</button>
    <button class="toggle-btn" onclick="toggleDark()">🌙</button>
    <div class="avatar"><?= isset($_SESSION['admin_nama']) ? strtoupper(substr($_SESSION['admin_nama'], 0, 1)) : 'A' ?></div>
  </div>
</div>

<!-- MAIN CONTENT -->
<div class="content">

 <div class="main-stats-row">

    <div class="main-stat-card">
        <h3>Pesanan 2 Bulan Ini</h3>
        <div class="main-num" style="color:var(--primary);"><?= $pesanan ?></div>
        <p>Jumlah pesanan dalam 2 bulan terakhir.</p>
    </div>

    <div class="main-stat-card">
        <h3>Total Pelanggan</h3>
        <div class="main-num" style="color:var(--success);"><?= $pelanggan ?></div>
        <p>Total pelanggan terdaftar.</p>
    </div>

    <div class="main-stat-card">
        <h3>Daftar Layanan</h3>
        <div class="main-num" style="color:var(--warning);"><?= $layanan ?></div>
        <p>Jumlah menu/Daftar layanan.</p>
    </div>

  </div>

    <!-- STATUS CARDS -->
    <div class="status-row">
    <div class="status-card">
        <h4>Total Pesanan</h4>
        <div class="stat-num" style="color:var(--primary-dark);"><?= $totalAll ?></div>
    </div>

    <div class="status-card">
        <h4>Selesai</h4>
        <div class="stat-num" style="color:var(--success);"><?= $totalSelesai ?></div>
    </div>

    <div class="status-card">
        <h4>Diproses</h4>
        <div class="stat-num" style="color:var(--warning);"><?= $totalProses ?></div>
    </div>

    <div class="status-card">
        <h4>Menunggu</h4>
        <div class="stat-num" style="color:var(--danger);"><?= $totalMenunggu ?></div>
    </div>
</div>

  <!-- GRAFIK KIRI & KANAN -->
  <div class="graph-row">
    <div class="graph-box">
      <h3 style="margin-bottom:12px;color:var(--text);">Pesanan 7 Hari Terakhir</h3>
      <canvas id="grafikKiri"></canvas>
    </div>

    <div class="graph-box">
      <h3 style="margin-bottom:12px;color:var(--text);">Pesanan 2 Bulan Terakhir</h3>
      <canvas id="grafikKanan"></canvas>
    </div>
  </div>

</div>

<!-- JS -->
<script>
function toggleSidebar(){
    document.getElementById("sidebar").classList.toggle("collapsed");
    localStorage.setItem("sidebar", document.getElementById("sidebar").classList.contains("collapsed") ? "collapsed" : "expanded");
}

function toggleDark(){
    document.body.classList.toggle("dark");
    localStorage.setItem("darkmode", document.body.classList.contains("dark") ? "on" : "off");
}

if (localStorage.getItem("darkmode") === "on") {
    document.body.classList.add("dark");
}

if (localStorage.getItem("sidebar") === "collapsed") {
    document.getElementById("sidebar").classList.add("collapsed");
}

/* === Chart Data from PHP === */
const labelsHarian = <?= json_encode($labelHarian) ?>;
const dataHarian = <?= json_encode($dataHarianFinal) ?>;

const labelsMinggu = <?= json_encode($labelsMinggu) ?>;
const dataMinggu = <?= json_encode($dataMingguan) ?>;

/* === Chart: 7 days (line) === */
new Chart(document.getElementById('grafikKiri'), {
    type: 'line',
    data: {
        labels: labelsHarian,
        datasets: [{
            label: 'Pesanan',
            data: dataHarian,
            borderColor: '#2563eb',
            backgroundColor: 'rgba(37,99,235,0.15)',
            borderWidth: 2,
            tension: 0.35,
            fill: true,
            pointRadius: 4,
            pointHoverRadius: 6
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: { display: false }
        },
        scales: {
            y: {
                beginAtZero: true,
                ticks: {
                    stepSize: 1,
                    precision: 0
                },
                grid: { color: (getComputedStyle(document.documentElement).getPropertyValue('--border') || 'rgba(0,0,0,0.05)') }
            },
            x: {
                grid: { display: false }
            }
        }
    }
});

/* === Chart: 8 weeks (bar) === */
new Chart(document.getElementById('grafikKanan'), {
    type: 'bar',
    data: {
        labels: labelsMinggu,
        datasets: [{
            label: 'Pesanan',
            data: dataMinggu,
            backgroundColor: 'rgba(8,145,178,0.9)',
            borderColor: '#0891b2',
            borderWidth: 1
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: { legend: { display: false } },
        scales: {
            y: {
                beginAtZero: true,
                ticks: {
                    stepSize: 1,
                    precision: 0
                },
                grid: { color: (getComputedStyle(document.documentElement).getPropertyValue('--border') || 'rgba(0,0,0,0.05)') }
            }
        }
    }
});
</script>

</body>
</html>