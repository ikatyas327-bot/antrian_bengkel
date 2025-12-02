<?php
session_start();
include '../koneksi.php';

if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit;
}

// Ambil data riwayat dari view
$query = "SELECT * FROM v_riwayat_service_repair ORDER BY start_date DESC";
$result = $conn->query($query);

// Hitung total riwayat
$total_riwayat = $result->num_rows;

// Hitung statistik
$stats_query = "SELECT 
    COUNT(*) as total,
    COUNT(CASE WHEN is_warranty_used = 'sudah' THEN 1 END) as warranty_used,
    COUNT(CASE WHEN is_warranty_used = 'belum' THEN 1 END) as warranty_unused
    FROM repair";
$stats_result = $conn->query($stats_query);
$stats = $stats_result->fetch_assoc();
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Riwayat Repair - Admin E-SPEED</title>

<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">

<style>
/* RIWAYAT REPAIR - STYLESHEET */
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
  --info: #0891b2;
  --warning: #f59e0b;
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
  height: 8px;
}

::-webkit-scrollbar-track {
  background: transparent;
}

::-webkit-scrollbar-thumb {
  background: var(--border);
  border-radius: 4px;
}

/* ============================================
   SIDEBAR
   ============================================ */

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

/* HEADER SECTION*/

.header-section {
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
  margin-bottom: 24px;
  gap: 20px;
  flex-wrap: wrap;
}

.header-section h1 {
  font-size: 1.75rem;
  font-weight: 600;
  margin-bottom: 8px;
  color: var(--text);
}

.subtitle {
  font-size: 0.9rem;
  color: var(--text-secondary);
  margin-bottom: 0;
}

/* FILTER & SEARCH */

.filter-section {
  display: flex;
  gap: 12px;
  align-items: center;
  flex-wrap: wrap;
}

.search-box {
  position: relative;
  flex: 1;
  min-width: 250px;
}

.search-box input {
  width: 100%;
  padding: 10px 16px;
  border: 1px solid var(--border);
  border-radius: 8px;
  font-size: 0.9rem;
  font-family: 'Inter', sans-serif;
  background: var(--card-bg);
  color: var(--text);
  transition: all 0.2s ease;
}

.search-box input:focus {
  outline: none;
  border-color: var(--primary);
  box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.1);
}

.filter-select {
  padding: 10px 16px;
  border: 1px solid var(--border);
  border-radius: 8px;
  font-size: 0.9rem;
  font-family: 'Inter', sans-serif;
  background: var(--card-bg);
  color: var(--text);
  cursor: pointer;
  transition: all 0.2s ease;
  min-width: 180px;
}

.filter-select:focus {
  outline: none;
  border-color: var(--primary);
  box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.1);
}

/* STATISTICS CARDS*/

.stats-row {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
  gap: 20px;
  margin-bottom: 28px;
}

.stat-card {
  background: var(--card-bg);
  padding: 20px;
  border-radius: 12px;
  border: 1px solid var(--border);
  display: flex;
  align-items: center;
  gap: 16px;
  transition: all 0.2s ease;
}

.stat-card:hover {
  transform: translateY(-2px);
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
}

.stat-icon {
  width: 56px;
  height: 56px;
  border-radius: 12px;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 1.5rem;
  flex-shrink: 0;
}

.stat-info h3 {
  font-size: 0.85rem;
  font-weight: 500;
  color: var(--text-secondary);
  margin-bottom: 6px;
  text-transform: uppercase;
  letter-spacing: 0.5px;
}

.stat-number {
  font-size: 1.75rem;
  font-weight: 700;
  color: var(--text);
  margin: 0;
}

/* TABLE */

.table-container {
  background: var(--card-bg);
  border-radius: 12px;
  border: 1px solid var(--border);
  overflow: hidden;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
}

table {
  width: 100%;
  border-collapse: collapse;
}

thead {
  background: #f8fafc;
  border-bottom: 2px solid var(--border);
  position: sticky;
  top: 0;
  z-index: 10;
}

.dark thead {
  background: #1c2128;
}

th {
  padding: 16px;
  text-align: left;
  font-weight: 600;
  font-size: 0.8rem;
  color: var(--text-secondary);
  text-transform: uppercase;
  letter-spacing: 0.5px;
  white-space: nowrap;
}

td {
  padding: 16px;
  border-bottom: 1px solid var(--border);
  font-size: 0.9rem;
  color: var(--text);
  vertical-align: middle;
}

tbody tr {
  transition: background 0.15s ease;
}

tbody tr:hover {
  background: #f8fafc;
}

.dark tbody tr:hover {
  background: #1c2128;
}

tbody tr:last-child td {
  border-bottom: none;
}

/* TABLE BADGES & STYLES*/

.repair-id {
  display: inline-block;
  padding: 4px 10px;
  background: rgba(37, 99, 235, 0.1);
  color: var(--primary);
  border-radius: 6px;
  font-weight: 600;
  font-size: 0.85rem;
}

.queue-number {
  display: inline-block;
  padding: 4px 10px;
  background: rgba(8, 145, 178, 0.1);
  color: var(--info);
  border-radius: 6px;
  font-weight: 600;
  font-size: 0.85rem;
}

.customer-name,
.service-name,
.technician-name {
  font-weight: 500;
}

.badge-warranty {
  display: inline-block;
  padding: 5px 12px;
  border-radius: 20px;
  font-size: 0.8rem;
  font-weight: 600;
  text-align: center;
}

.warranty-yes {
  background: rgba(5, 150, 105, 0.15);
  color: var(--success);
}

.warranty-no {
  background: rgba(107, 114, 128, 0.15);
  color: var(--text-secondary);
}

.description-cell {
  max-width: 300px;
}

.description-text {
  font-size: 0.85rem;
  color: var(--text-secondary);
  line-height: 1.5;
  overflow: hidden;
  text-overflow: ellipsis;
  display: -webkit-box;
  -webkit-line-clamp: 2;
  -webkit-box-orient: vertical;
}

/* EMPTY STATE */

.empty-state {
  text-align: center;
  padding: 60px 20px;
  color: var(--text-secondary);
}

.empty-icon {
  font-size: 3rem;
  margin-bottom: 12px;
}

.empty-state p {
  font-size: 1rem;
  margin: 0;
}

/* RESPONSIVE*/

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
  
  .header-section {
    flex-direction: column;
  }
  
  .filter-section {
    width: 100%;
  }
  
  .search-box {
    flex: 1;
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
  
  .header-section h1 {
    font-size: 1.5rem;
  }
  
  .stats-row {
    grid-template-columns: 1fr;
  }
  
  .table-container {
    overflow-x: auto;
  }
  
  th, td {
    padding: 12px;
    font-size: 0.85rem;
  }
  
  .description-cell {
    max-width: 200px;
  }
  
  .filter-section {
    flex-direction: column;
  }
  
  .search-box,
  .filter-select {
    width: 100%;
  }
}

@media (max-width: 480px) {
  .stat-card {
    flex-direction: row;
    padding: 16px;
  }
  
  .stat-icon {
    width: 48px;
    height: 48px;
    font-size: 1.25rem;
  }
  
  .stat-number {
    font-size: 1.5rem;
  }
  
  th, td {
    padding: 10px;
    font-size: 0.8rem;
  }
}


.stats-cards {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
  gap: 20px;
  margin-bottom: 30px;
}

.stat-card {
  background: var(--card-bg);
  padding: 20px;
  border-radius: 8px;
  border: 1px solid var(--border);
  text-align: center;
}

.stat-card h3 {
  font-size: 0.9rem;
  color: var(--text-secondary);
  margin-bottom: 10px;
  font-weight: 600;
  text-transform: uppercase;
  letter-spacing: 0.5px;
}

.stat-card .number {
  font-size: 2rem;
  font-weight: 700;
  color: var(--primary);
}

.search-box {
  margin-bottom: 20px;
  display: flex;
  gap: 12px;
  align-items: center;
}

.search-box input {
  flex: 1;
  padding: 12px 16px;
  border: 1px solid var(--border);
  border-radius: 6px;
  font-size: 0.9rem;
  background: var(--bg);
  color: var(--text);
}

.search-box input:focus {
  outline: none;
  border-color: var(--primary);
}

.btn-export {
  padding: 12px 24px;
  background: var(--success);
  color: white;
  border: none;
  border-radius: 6px;
  font-weight: 500;
  cursor: pointer;
  text-decoration: none;
  display: inline-block;
  transition: background 0.2s ease;
}

.btn-export:hover {
  background: #047857;
}

.warranty-badge {
  display: inline-block;
  padding: 4px 12px;
  border-radius: 12px;
  font-size: 0.8rem;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.2s ease;
}

.warranty-badge:hover {
  transform: scale(1.05);
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.15);
}

.warranty-sudah {
  background: rgba(220, 38, 38, 0.1);
  color: var(--danger);
}

.warranty-belum {
  background: rgba(5, 150, 105, 0.1);
  color: var(--success);
}

.detail-btn {
  padding: 6px 14px;
  background: var(--info);
  color: white;
  border: none;
  border-radius: 4px;
  font-size: 0.85rem;
  font-weight: 500;
  cursor: pointer;
  transition: background 0.2s ease;
}

.detail-btn:hover {
  background: #0e7490;
}

/* Modal Detail */
.modal-detail {
  display: none;
  position: fixed;
  z-index: 2000;
  left: 0;
  top: 0;
  width: 100%;
  height: 100%;
  background: rgba(0, 0, 0, 0.6);
  backdrop-filter: blur(4px);
}

.modal-detail-content {
  background: var(--card-bg);
  margin: 3% auto;
  padding: 0;
  border-radius: 12px;
  width: 90%;
  max-width: 700px;
  max-height: 90vh;
  overflow-y: auto;
  box-shadow: 0 10px 40px rgba(0, 0, 0, 0.3);
}

.modal-detail-header {
  padding: 20px 24px;
  border-bottom: 1px solid var(--border);
  display: flex;
  justify-content: space-between;
  align-items: center;
  position: sticky;
  top: 0;
  background: var(--card-bg);
  z-index: 1;
}

.modal-detail-header h2 {
  font-size: 1.25rem;
  font-weight: 600;
  color: var(--text);
}

.modal-detail-body {
  padding: 24px;
}

.detail-grid {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 20px;
  margin-bottom: 20px;
}

.detail-item {
  padding: 12px;
  background: var(--bg);
  border-radius: 6px;
}

.detail-item label {
  display: block;
  font-size: 0.75rem;
  color: var(--text-secondary);
  text-transform: uppercase;
  letter-spacing: 0.5px;
  margin-bottom: 6px;
  font-weight: 600;
}

.detail-item .value {
  font-size: 0.95rem;
  color: var(--text);
  font-weight: 500;
}

.detail-full {
  grid-column: 1 / -1;
}

.close {
  font-size: 28px;
  font-weight: 300;
  color: var(--text-secondary);
  cursor: pointer;
  line-height: 1;
  transition: color 0.2s ease;
}

.close:hover {
  color: var(--danger);
}

/* Warranty Option Buttons */
.warranty-option-btn {
  width: 100%;
  padding: 20px;
  border: 2px solid var(--border);
  border-radius: 12px;
  background: var(--card-bg);
  color: var(--text);
  cursor: pointer;
  font-family: 'Inter', sans-serif;
  font-size: 1rem;
  display: flex;
  align-items: center;
  gap: 16px;
  transition: all 0.2s ease;
}

.warranty-option-btn:hover {
  transform: translateY(-2px);
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
}

.warranty-belum-btn:hover {
  border-color: var(--success);
  background: rgba(5, 150, 105, 0.05);
}

.warranty-sudah-btn:hover {
  border-color: var(--danger);
  background: rgba(220, 38, 38, 0.05);
}

.warranty-option-btn strong {
  font-size: 1.1rem;
}

.warranty-option-btn small {
  color: var(--text-secondary);
  font-size: 0.85rem;
}

@media (max-width: 768px) {
  .detail-grid {
    grid-template-columns: 1fr;
  }
  
  .stats-cards {
    grid-template-columns: 1fr;
  }
  
  .search-box {
    flex-direction: column;
  }
  
  .search-box input,
  .btn-export {
    width: 100%;
  }
}
</style>

</head>
<body>

<nav class="sidebar" id="sidebar">
  <h2>E-SPEED</h2>
  <ul>
    <li><a href="dashboard.php"><span class="icon">🏠</span> <span>Dashboard</span></a></li>
    <li><a href="pelanggan.php"><span class="icon">👥</span> <span>Pelanggan</span></a></li>
    <li><a href="pesanan.php"><span class="icon">🧾</span> <span>Pesanan</span></a></li>
    <li><a href="layanan.php"><span class="icon">🛠️</span> <span>Layanan</span></a></li>
    <li><a href="riwayat_repair.php" class="active"><span class="icon">📋</span> <span>Riwayat Repair</span></a></li>
    <li><a href="logout.php" class="logout"><span class="icon">🚪</span> <span>Logout</span></a></li>
  </ul>
</nav>

<div class="topbar">
  <h1>Riwayat Repair</h1>
  <div class="switch-container">
    <button class="toggle-btn" onclick="toggleSidebar()">☰</button>
    <button class="toggle-btn" onclick="toggleDark()">🌙</button>
    <div class="avatar"><?= isset($_SESSION['admin_nama']) ? strtoupper(substr($_SESSION['admin_nama'], 0, 1)) : 'A' ?></div>
  </div>
</div>

<main class="content">
  <h1>📋 Riwayat Repair & Service</h1>
  <p style="color: var(--text-secondary); margin-bottom: 24px;">
    Data riwayat pesanan yang telah selesai. Data ini bersifat permanen dan tidak akan terhapus.
  </p>

  <!-- Statistik Cards -->
  <div class="stats-cards">
    <div class="stat-card">
      <h3>Total Riwayat</h3>
      <div class="number" style="color: var(--primary);"><?= $stats['total'] ?></div>
    </div>
    
    <div class="stat-card">
      <h3>Garansi Terpakai</h3>
      <div class="number" style="color: var(--danger);"><?= $stats['warranty_used'] ?></div>
    </div>
    
    <div class="stat-card">
      <h3>Garansi Tersedia</h3>
      <div class="number" style="color: var(--success);"><?= $stats['warranty_unused'] ?></div>
    </div>
  </div>

  <!-- Search & Export -->
  <div class="search-box">
    <input type="text" id="searchInput" placeholder="🔍 Cari berdasarkan nama, no. antrian, atau layanan..." onkeyup="searchTable()">
    <a href="export_riwayat.php" class="btn-export">📥 Export Excel</a>
  </div>

  <!-- Tabel Riwayat -->
  <div class="table-container">
    <table id="riwayatTable">
      <thead>
        <tr>
          <th>No</th>
          <th>No. Antrian</th>
          <th>Pelanggan</th>
          <th>Layanan</th>
          <th>Teknisi</th>
          <th>Tanggal Selesai</th>
          <th>Garansi</th>
          <th>Aksi</th>
        </tr>
      </thead>
      <tbody>
        <?php if ($result && $result->num_rows > 0): $no=1; while($row = $result->fetch_assoc()): ?>
        <tr>
          <td><?= $no++ ?></td>
          <td><span class="queue-number">#<?= htmlspecialchars($row['queue_number'] ?? 'N/A') ?></span></td>
          <td><?= htmlspecialchars($row['customer_name'] ?? 'N/A') ?></td>
          <td><?= htmlspecialchars($row['service_name'] ?? 'N/A') ?></td>
          <td><?= htmlspecialchars($row['technician_name'] ?? 'N/A') ?></td>
          <td><?= $row['completed_at'] ? date('d/m/Y H:i', strtotime($row['completed_at'])) : date('d/m/Y H:i', strtotime($row['start_date'])) ?></td>
          <td>
            <span class="warranty-badge warranty-<?= $row['is_warranty_used'] ?>" 
                  onclick='showWarrantyPopup(<?= $row['queue_id'] ?>, "<?= $row['is_warranty_used'] ?>")' 
                  title="Klik untuk ubah status garansi">
              <?= ucfirst($row['is_warranty_used']) ?>
            </span>
          </td>
          <td>
            <button class="detail-btn" onclick='showDetail(<?= json_encode($row) ?>)'>Detail</button>
          </td>
        </tr>
        <?php endwhile; else: ?>
        <tr><td colspan="8" class="empty-state">Belum ada riwayat repair</td></tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</main>

<!-- Modal Detail -->
<div id="detailModal" class="modal-detail">
  <div class="modal-detail-content">
    <div class="modal-detail-header">
      <h2>📄 Detail Riwayat Repair</h2>
      <span class="close" onclick="closeDetailModal()">&times;</span>
    </div>
    <div class="modal-detail-body">
      <div class="detail-grid">
        <div class="detail-item">
          <label>No. Antrian</label>
          <div class="value" id="detail_queue_number"></div>
        </div>
        
        <div class="detail-item">
          <label>ID Service</label>
          <div class="value" id="detail_service_id"></div>
        </div>
        
        <div class="detail-item">
          <label>Nama Pelanggan</label>
          <div class="value" id="detail_customer_name"></div>
        </div>
        
        <div class="detail-item">
          <label>No. Telepon</label>
          <div class="value" id="detail_phone"></div>
        </div>
        
        <div class="detail-item detail-full">
          <label>Alamat</label>
          <div class="value" id="detail_address"></div>
        </div>
        
        <div class="detail-item">
          <label>Layanan</label>
          <div class="value" id="detail_service"></div>
        </div>
        
        <div class="detail-item">
          <label>Harga</label>
          <div class="value" id="detail_price"></div>
        </div>
        
        <div class="detail-item">
          <label>Estimasi Waktu</label>
          <div class="value" id="detail_time"></div>
        </div>
        
        <div class="detail-item">
          <label>Teknisi</label>
          <div class="value" id="detail_technician"></div>
        </div>
        
        <div class="detail-item">
          <label>Tanggal Selesai</label>
          <div class="value" id="detail_date"></div>
        </div>
        
        <div class="detail-item">
          <label>Status Garansi</label>
          <div class="value" id="detail_warranty"></div>
        </div>
        
        <div class="detail-item detail-full">
          <label>Keluhan / Deskripsi</label>
          <div class="value" id="detail_description"></div>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- Modal Garansi -->
<div id="warrantyModal" class="modal-detail">
  <div class="modal-detail-content" style="max-width: 450px;">
    <div class="modal-detail-header">
      <h2>⚙️ Ubah Status Garansi</h2>
      <span class="close" onclick="closeWarrantyModal()">&times;</span>
    </div>
    <div class="modal-detail-body">
      <p style="margin-bottom: 20px; color: var(--text-secondary); font-size: 0.9rem;">
        Pilih status garansi untuk pesanan ini:
      </p>
      
      <div style="display: grid; gap: 12px;">
        <button class="warranty-option-btn warranty-belum-btn" onclick="updateWarranty('belum')">
          <span style="font-size: 1.5rem; margin-bottom: 8px;">✅</span>
          <div>
            <strong>Belum Terpakai</strong>
            <small style="display: block; margin-top: 4px; opacity: 0.8;">Garansi masih tersedia</small>
          </div>
        </button>
        
        <button class="warranty-option-btn warranty-sudah-btn" onclick="updateWarranty('sudah')">
          <span style="font-size: 1.5rem; margin-bottom: 8px;">❌</span>
          <div>
            <strong>Sudah Terpakai</strong>
            <small style="display: block; margin-top: 4px; opacity: 0.8;">Garansi telah digunakan</small>
          </div>
        </button>
      </div>
    </div>
  </div>
</div>

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

// Search function
function searchTable() {
    const input = document.getElementById('searchInput');
    const filter = input.value.toLowerCase();
    const table = document.getElementById('riwayatTable');
    const tr = table.getElementsByTagName('tr');

    for (let i = 1; i < tr.length; i++) {
        const td = tr[i].getElementsByTagName('td');
        let found = false;
        
        for (let j = 0; j < td.length; j++) {
            if (td[j]) {
                const txtValue = td[j].textContent || td[j].innerText;
                if (txtValue.toLowerCase().indexOf(filter) > -1) {
                    found = true;
                    break;
                }
            }
        }
        
        tr[i].style.display = found ? '' : 'none';
    }
}

// Show detail modal
function showDetail(data) {
    document.getElementById('detail_queue_number').textContent = '#' + (data.queue_number || 'N/A');
    document.getElementById('detail_service_id').textContent = 'SV-' + String(data.service_id).padStart(5, '0');
    document.getElementById('detail_customer_name').textContent = data.customer_name || 'N/A';
    document.getElementById('detail_phone').textContent = data.customer_phone || 'N/A';
    document.getElementById('detail_address').textContent = data.customer_address || 'N/A';
    document.getElementById('detail_service').textContent = data.service_name || 'N/A';
    
    // Format harga
    const price = parseFloat(data.service_price) || 0;
    document.getElementById('detail_price').textContent = 'Rp ' + price.toLocaleString('id-ID');
    
    document.getElementById('detail_time').textContent = data.estimated_time || 'N/A';
    document.getElementById('detail_technician').textContent = data.technician_name || 'N/A';
    
    // Format tanggal
    const date = data.completed_at || data.start_date;
    if (date) {
        const d = new Date(date);
        document.getElementById('detail_date').textContent = d.toLocaleDateString('id-ID', {
            day: '2-digit',
            month: 'long',
            year: 'numeric',
            hour: '2-digit',
            minute: '2-digit'
        });
    } else {
        document.getElementById('detail_date').textContent = 'N/A';
    }
    
    // Warranty badge
    const warrantyBadge = document.createElement('span');
    warrantyBadge.className = 'warranty-badge warranty-' + data.is_warranty_used;
    warrantyBadge.textContent = data.is_warranty_used === 'sudah' ? 'Sudah Terpakai' : 'Belum Terpakai';
    document.getElementById('detail_warranty').innerHTML = '';
    document.getElementById('detail_warranty').appendChild(warrantyBadge);
    
    document.getElementById('detail_description').textContent = data.keluhan || 'Tidak ada deskripsi';
    
    document.getElementById('detailModal').style.display = 'block';
}

// Close detail modal
function closeDetailModal() {
    document.getElementById('detailModal').style.display = 'none';
}

// Close modal when clicking outside
window.onclick = function(event) {
    const modal = document.getElementById('detailModal');
    const warrantyModal = document.getElementById('warrantyModal');
    if (event.target === modal) {
        closeDetailModal();
    }
    if (event.target === warrantyModal) {
        closeWarrantyModal();
    }
}

// Warranty modal functions
let currentQueueId = null;

function showWarrantyPopup(queueId, currentStatus) {
    currentQueueId = queueId;
    document.getElementById('warrantyModal').style.display = 'block';
}

function closeWarrantyModal() {
    document.getElementById('warrantyModal').style.display = 'none';
    currentQueueId = null;
}

function updateWarranty(status) {
    if (!currentQueueId) {
        alert('Error: Queue ID tidak ditemukan');
        return;
    }

    // Konfirmasi
    const statusText = status === 'sudah' ? 'Sudah Terpakai' : 'Belum Terpakai';
    if (!confirm('Apakah Anda yakin ingin mengubah status garansi menjadi "' + statusText + '"?')) {
        return;
    }

    // Kirim request ke server
    fetch('update_warranty.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: 'queue_id=' + currentQueueId + '&warranty_status=' + status
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert(data.message);
            closeWarrantyModal();
            location.reload(); // Reload halaman untuk update tampilan
        } else {
            alert('Gagal: ' + data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Terjadi kesalahan saat mengupdate status garansi');
    });
}
</script>

</body>
</html>