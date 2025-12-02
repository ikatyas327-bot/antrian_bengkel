<?php
session_start();
include '../koneksi.php';
if (!isset($_SESSION['admin_id'])) {
  header("Location: login.php");
  exit;
}

// Ambil data antrian (pesanan) dengan teknisi
$query = "SELECT q.*, 
          COALESCE(c.name, q.nama, 'Tidak tersedia') AS nama_pelanggan,
          m.name AS layanan,
          t.name AS nama_teknisi
          FROM queue q
          LEFT JOIN customer c ON q.customer_id = c.customer_id
          LEFT JOIN menu m ON q.id_menu = m.id_menu
          LEFT JOIN technician t ON q.technician_id = t.technician_id
          ORDER BY q.created_at DESC";
$result = $conn->query($query);

// Ambil data teknisi untuk dropdown
$technician_query = "SELECT technician_id, name FROM technician ORDER BY name ASC";
$technician_result = $conn->query($technician_query);
$technicians = [];
if ($technician_result && $technician_result->num_rows > 0) {
  while($tech = $technician_result->fetch_assoc()) {
    $technicians[] = $tech;
  }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Data Pesanan - Admin E-SPEED</title>

<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="style.css">

</head>
<body>

<nav class="sidebar" id="sidebar">
  <h2>E-SPEED</h2>
  <ul>
    <li><a href="dashboard.php"><span class="icon">🏠</span> <span>Dashboard</span></a></li>
    <li><a href="pelanggan.php"><span class="icon">👥</span> <span>Pelanggan</span></a></li>
    <li><a href="pesanan.php" class="active"><span class="icon">🧾</span> <span>Pesanan</span></a></li>
    <li><a href="layanan.php"><span class="icon">🛠️</span> <span>Layanan</span></a></li>
    <li><a href="riwayat_repair.php"><span class="icon">📋</span> <span>Riwayat Repair</span></a></li>
    <li><a href="logout.php" class="logout"><span class="icon">🚪</span> <span>Logout</span></a></li>
  </ul>
</nav>

<div class="topbar">
  <h1>Data Pesanan</h1>
  <div class="switch-container">
    <button class="toggle-btn" onclick="toggleSidebar()">☰</button>
    <button class="toggle-btn" onclick="toggleDark()">🌙</button>
    <div class="avatar"><?= isset($_SESSION['admin_nama']) ? strtoupper(substr($_SESSION['admin_nama'], 0, 1)) : 'A' ?></div>
  </div>
</div>

<main class="content">
  <h1>Data Pesanan / Antrian</h1>

  <!-- Alert Container -->
  <div id="alertContainer"></div>

  <div class="table-container">
    <table>
      <thead>
        <tr>
          <th>No</th>
          <th>Nomor Antrian</th>
          <th>Nama Pelanggan</th>
          <th>Teknisi</th>
          <th>Layanan</th>
          <th>Status</th>
          <th>Tanggal</th>
          <th>Aksi</th>
        </tr>
      </thead>
      <tbody>
        <?php if ($result && $result->num_rows > 0): $no=1; while($row = $result->fetch_assoc()): 
          $status_class = 'status-menunggu';
          if ($row['status'] == 'Diproses') $status_class = 'status-diproses';
          if ($row['status'] == 'Selesai') $status_class = 'status-selesai';
          
          // Tentukan tanggal yang ditampilkan berdasarkan status
          $display_date = $row['created_at'];
          if ($row['status'] == 'Diproses' && !empty($row['processed_at'])) {
            $display_date = $row['processed_at'];
          } elseif ($row['status'] == 'Selesai' && !empty($row['completed_at'])) {
            $display_date = $row['completed_at'];
          }
        ?>
        <tr data-queue-id="<?= $row['queue_id'] ?>">
          <td><?= $no++ ?></td>
          <td><span class="queue-number">#<?= htmlspecialchars($row['queue_number']) ?></span></td>
          <td class="order-customer"><?= htmlspecialchars($row['nama_pelanggan']) ?></td>
          <td class="order-technician"><?= !empty($row['nama_teknisi']) ? htmlspecialchars($row['nama_teknisi']) : '-' ?></td>
          <td class="order-service"><?= htmlspecialchars($row['layanan']) ?></td>
          <td><span class="status-badge <?= $status_class ?> order-status"><?= htmlspecialchars($row['status']) ?></span></td>
          <td class="order-date"><?= date('d/m/Y H:i', strtotime($display_date)) ?></td>
          <td>
            <button class="btn-edit" onclick='openStatusModal(<?= json_encode($row) ?>)'>Ubah Status</button>
            <button class="btn-hapus" onclick='openDeleteModal(<?= $row['queue_id'] ?>, "#<?= htmlspecialchars($row['queue_number']) ?>")'>Hapus</button>
          </td>
        </tr>
        <?php endwhile; else: ?>
        <tr><td colspan="8" class="empty-state">Belum ada pesanan</td></tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</main>

<!-- MODAL UBAH STATUS -->
<div id="statusModal" class="modal">
  <div class="modal-content">
    <div class="modal-header">
      <h2>Ubah Status Pesanan</h2>
      <span class="close" onclick="closeModal('statusModal')">&times;</span>
    </div>
    <div class="modal-body">
      <div class="info-box">
        <p><strong>Nomor Antrian:</strong> <span id="modal_queue_number"></span></p>
        <p><strong>Pelanggan:</strong> <span id="modal_customer_name"></span></p>
        <p><strong>Layanan:</strong> <span id="modal_service"></span></p>
      </div>
      
      <form id="statusForm">
        <input type="hidden" id="status_queue_id" name="queue_id">
        
        <div class="form-group">
          <label>Status Pesanan *</label>
          <select id="status_value" name="status" required onchange="toggleTechnicianField()">
            <option value="">-- Pilih Status --</option>
            <option value="Menunggu">Menunggu</option>
            <option value="Diproses">Diproses</option>
            <option value="Selesai">Selesai</option>
          </select>
        </div>
        
        <div class="form-group" id="technician_field" style="display: none;">
          <label>Pilih Teknisi *</label>
          <select id="technician_id" name="technician_id">
            <option value="">-- Pilih Teknisi --</option>
            <?php foreach($technicians as $tech): ?>
              <option value="<?= $tech['technician_id'] ?>"><?= htmlspecialchars($tech['name']) ?></option>
            <?php endforeach; ?>
          </select>
        </div>
      </form>
    </div>
    <div class="modal-footer">
      <button class="btn-modal btn-secondary-modal" onclick="closeModal('statusModal')">Batal</button>
      <button class="btn-modal btn-primary-modal" onclick="updateStatus()">Simpan</button>
    </div>
  </div>
</div>

<!-- MODAL HAPUS -->
<div id="deleteModal" class="modal">
  <div class="modal-content">
    <div class="modal-header">
      <h2>Konfirmasi Hapus</h2>
      <span class="close" onclick="closeModal('deleteModal')">&times;</span>
    </div>
    <div class="modal-body">
      <p>Apakah Anda yakin ingin menghapus pesanan:</p>
      <p style="font-weight: 600; margin: 16px 0; padding: 12px; background: var(--bg); border-radius: 6px;">
        <span id="delete_queue_number"></span>
      </p>
      <p style="color: var(--danger); font-size: 0.9rem;">⚠ Tindakan ini tidak dapat dibatalkan!</p>
      <input type="hidden" id="delete_queue_id">
    </div>
    <div class="modal-footer">
      <button class="btn-modal btn-secondary-modal" onclick="closeModal('deleteModal')">Batal</button>
      <button class="btn-modal btn-danger-modal" onclick="deleteOrder()">Hapus</button>
    </div>
  </div>
</div>

<script>
// Data teknisi dari PHP
const technicians = <?= json_encode($technicians) ?>;

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

// Show Alert
function showAlert(message, type = 'success') {
  const alertContainer = document.getElementById('alertContainer');
  const alertDiv = document.createElement('div');
  alertDiv.className = `alert alert-${type}`;
  alertDiv.textContent = type === 'success' ? '✅ ' + message : '❌ ' + message;
  alertDiv.style.display = 'block';
  
  alertContainer.innerHTML = '';
  alertContainer.appendChild(alertDiv);
  
  setTimeout(() => {
    alertDiv.style.display = 'none';
  }, 5000);
}

// Modal Functions
function closeModal(modalId) {
  document.getElementById(modalId).style.display = 'none';
}

window.onclick = function(event) {
  if (event.target.classList.contains('modal')) {
    event.target.style.display = 'none';
  }
}

// Toggle technician field berdasarkan status
function toggleTechnicianField() {
  const status = document.getElementById('status_value').value;
  const techField = document.getElementById('technician_field');
  const techSelect = document.getElementById('technician_id');
  
  if (status === 'Diproses' || status === 'Selesai') {
    techField.style.display = 'block';
    techSelect.required = true;
  } else {
    techField.style.display = 'none';
    techSelect.required = false;
    techSelect.value = '';
  }
}

// UBAH STATUS MODAL
function openStatusModal(order) {
  document.getElementById('status_queue_id').value = order.queue_id;
  document.getElementById('modal_queue_number').textContent = '#' + order.queue_number;
  document.getElementById('modal_customer_name').textContent = order.nama_pelanggan;
  document.getElementById('modal_service').textContent = order.layanan;
  document.getElementById('status_value').value = order.status;
  
  // Set teknisi jika ada
  if (order.technician_id) {
    document.getElementById('technician_id').value = order.technician_id;
  } else {
    document.getElementById('technician_id').value = '';
  }
  
  // Toggle technician field based on current status
  toggleTechnicianField();
  
  document.getElementById('statusModal').style.display = 'block';
}

function updateStatus() {
  const status = document.getElementById('status_value').value;
  const technicianId = document.getElementById('technician_id').value;
  
  // Validasi teknisi untuk status Diproses dan Selesai
  if ((status === 'Diproses' || status === 'Selesai') && !technicianId) {
    showAlert('Pilih teknisi terlebih dahulu!', 'danger');
    return;
  }
  
  const formData = new FormData();
  formData.append('action', 'update');
  formData.append('queue_id', document.getElementById('status_queue_id').value);
  formData.append('status', status);
  formData.append('technician_id', technicianId);
  
  fetch('update_status.php', {
    method: 'POST',
    body: formData
  })
  .then(response => response.json())
  .then(data => {
    console.log('Response dari server:', data); // Debug log
    
    if (data.success) {
      showAlert(data.message, 'success');
      closeModal('statusModal');
      
      // Update status di tabel tanpa reload
      const queueId = document.getElementById('status_queue_id').value;
      const row = document.querySelector(`tr[data-queue-id="${queueId}"]`);
      
      if (row) {
        const statusBadge = row.querySelector('.order-status');
        const newStatus = document.getElementById('status_value').value;
        
        // Update status text
        statusBadge.textContent = newStatus;
        
        // Update status class
        statusBadge.className = 'status-badge order-status';
        if (newStatus === 'Menunggu') {
          statusBadge.classList.add('status-menunggu');
        } else if (newStatus === 'Diproses') {
          statusBadge.classList.add('status-diproses');
        } else if (newStatus === 'Selesai') {
          statusBadge.classList.add('status-selesai');
        }
        
        // Update teknisi
        const techCell = row.querySelector('.order-technician');
        const selectedTechId = document.getElementById('technician_id').value;
        if (selectedTechId) {
          const selectedTech = technicians.find(t => t.technician_id == selectedTechId);
          if (selectedTech) {
            techCell.textContent = selectedTech.name;
          }
        } else {
          techCell.textContent = '-';
        }
        
        // ⭐ PERBAIKAN: Update tanggal dari response server
        const dateCell = row.querySelector('.order-date');
        if (data.updated_date) {
          console.log('Mengupdate tanggal ke:', data.updated_date); // Debug log
          dateCell.textContent = data.updated_date;
        } else {
          console.warn('updated_date tidak ada di response!'); // Debug warning
        }
      }
    } else {
      showAlert(data.message || 'Gagal mengupdate status', 'danger');
    }
  })
  .catch(error => {
    console.error('Error:', error);
    showAlert('Terjadi kesalahan saat mengupdate status', 'danger');
  });
}

// DELETE MODAL
function openDeleteModal(queueId, queueNumber) {
  document.getElementById('delete_queue_id').value = queueId;
  document.getElementById('delete_queue_number').textContent = queueNumber;
  document.getElementById('deleteModal').style.display = 'block';
}

function deleteOrder() {
  const queueId = document.getElementById('delete_queue_id').value;
  
  const formData = new FormData();
  formData.append('action', 'delete');
  formData.append('queue_id', queueId);
  
  fetch('update_status.php', {
    method: 'POST',
    body: formData
  })
  .then(response => response.json())
  .then(data => {
    if (data.success) {
      showAlert(data.message, 'success');
      closeModal('deleteModal');
      
      // Hapus row dari tabel
      const row = document.querySelector(`tr[data-queue-id="${queueId}"]`);
      if (row) {
        row.style.animation = 'fadeOut 0.3s ease';
        setTimeout(() => {
          row.remove();
          
          // Update nomor urut
          const rows = document.querySelectorAll('tbody tr');
          rows.forEach((r, index) => {
            const firstCell = r.querySelector('td:first-child');
            if (firstCell && !firstCell.querySelector('.empty-state')) {
              firstCell.textContent = index + 1;
            }
          });
          
          // Jika tidak ada data, tampilkan pesan kosong
          if (rows.length === 0 || (rows.length === 1 && rows[0].querySelector('.empty-state'))) {
            document.querySelector('tbody').innerHTML = '<tr><td colspan="8" class="empty-state">Belum ada pesanan</td></tr>';
          }
        }, 300);
      }
    } else {
      showAlert(data.message || 'Gagal menghapus pesanan', 'danger');
    }
  })
  .catch(error => {
    console.error('Error:', error);
    showAlert('Terjadi kesalahan saat menghapus pesanan', 'danger');
  });
}
</script>

</body>
</html>