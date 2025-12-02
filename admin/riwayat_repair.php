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
<link rel="stylesheet" href="style.css">

<style>
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
            <span class="warranty-badge warranty-<?= $row['is_warranty_used'] ?>">
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
    if (event.target === modal) {
        closeDetailModal();
    }
}
</script>

</body>
</html>