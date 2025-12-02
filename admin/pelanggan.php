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
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Data Pelanggan - Admin E-SPEED</title>

<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">

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
  --info: #0891b2;
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

.content h1 {
  font-size: 1.75rem;
  font-weight: 600;
  margin-bottom: 24px;
  color: var(--text);
}

/* BUTTON */
.btn {
  display: inline-block;
  padding: 10px 20px;
  background: var(--success);
  color: white;
  text-decoration: none;
  border-radius: 6px;
  font-weight: 500;
  font-size: 0.9rem;
  transition: background 0.2s ease;
  margin-bottom: 20px;
  border: none;
  cursor: pointer;
}

.btn:hover {
  background: #047857;
}

/* TABLE */
.table-container {
  background: var(--card-bg);
  border-radius: 8px;
  border: 1px solid var(--border);
  overflow: hidden;
}

table {
  width: 100%;
  border-collapse: collapse;
}

thead {
  background: #f8fafc;
  border-bottom: 1px solid var(--border);
}

.dark thead {
  background: #1c2128;
}

th {
  padding: 16px;
  text-align: left;
  font-weight: 600;
  font-size: 0.875rem;
  color: var(--text-secondary);
  text-transform: uppercase;
  letter-spacing: 0.5px;
}

td {
  padding: 16px;
  border-bottom: 1px solid var(--border);
  font-size: 0.9rem;
  color: var(--text);
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

/* BUTTONS */
.btn-edit,
.btn-hapus {
  padding: 6px 14px;
  border-radius: 4px;
  text-decoration: none;
  font-size: 0.85rem;
  font-weight: 500;
  color: white;
  transition: background 0.2s ease;
  display: inline-block;
  margin: 0 4px;
  border: none;
  cursor: pointer;
}

.btn-edit {
  background: var(--info);
}

.btn-edit:hover {
  background: #0e7490;
}

.btn-hapus {
  background: var(--danger);
}

.btn-hapus:hover {
  background: #b91c1c;
}

.empty-state {
  text-align: center;
  padding: 48px 20px;
  color: var(--text-secondary);
  font-size: 0.95rem;
}

/* MODAL */
.modal {
  display: none;
  position: fixed;
  z-index: 2000;
  left: 0;
  top: 0;
  width: 100%;
  height: 100%;
  background: rgba(0, 0, 0, 0.6);
  backdrop-filter: blur(4px);
  animation: fadeIn 0.3s ease;
}

@keyframes fadeIn {
  from { opacity: 0; }
  to { opacity: 1; }
}

.modal-content {
  position: relative;
  background: var(--card-bg);
  margin: 5% auto;
  padding: 0;
  border-radius: 12px;
  width: 90%;
  max-width: 500px;
  box-shadow: 0 10px 40px rgba(0, 0, 0, 0.3);
  animation: slideDown 0.3s ease;
}

@keyframes slideDown {
  from {
    transform: translateY(-50px);
    opacity: 0;
  }
  to {
    transform: translateY(0);
    opacity: 1;
  }
}

.modal-header {
  padding: 20px 24px;
  border-bottom: 1px solid var(--border);
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.modal-header h2 {
  font-size: 1.25rem;
  font-weight: 600;
  color: var(--text);
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

.modal-body {
  padding: 24px;
}

.form-group {
  margin-bottom: 20px;
}

.form-group label {
  display: block;
  margin-bottom: 8px;
  font-weight: 500;
  color: var(--text);
  font-size: 0.9rem;
}

.form-group input,
.form-group textarea {
  width: 100%;
  padding: 12px;
  border: 1px solid var(--border);
  border-radius: 6px;
  font-size: 0.9rem;
  font-family: 'Inter', sans-serif;
  background: var(--bg);
  color: var(--text);
  transition: border 0.2s ease;
}

.form-group input:focus,
.form-group textarea:focus {
  outline: none;
  border-color: var(--primary);
}

.form-group textarea {
  resize: vertical;
  min-height: 80px;
}

.form-group input:disabled {
  background: var(--border);
  cursor: not-allowed;
}

.modal-footer {
  padding: 16px 24px;
  border-top: 1px solid var(--border);
  display: flex;
  justify-content: flex-end;
  gap: 12px;
}

.btn-modal {
  padding: 10px 20px;
  border: none;
  border-radius: 6px;
  font-weight: 500;
  font-size: 0.9rem;
  cursor: pointer;
  transition: all 0.2s ease;
}

.btn-primary-modal {
  background: var(--primary);
  color: white;
}

.btn-primary-modal:hover {
  background: var(--primary-dark);
}

.btn-success-modal {
  background: var(--success);
  color: white;
}

.btn-success-modal:hover {
  background: #047857;
}

.btn-danger-modal {
  background: var(--danger);
  color: white;
}

.btn-danger-modal:hover {
  background: #b91c1c;
}

.btn-secondary-modal {
  background: var(--text-secondary);
  color: white;
}

.btn-secondary-modal:hover {
  background: #545b62;
}

/* Alert Messages */
.alert {
  padding: 12px 16px;
  border-radius: 6px;
  margin-bottom: 16px;
  font-size: 0.9rem;
  display: none;
  animation: slideDown 0.3s ease;
}

.alert-success {
  background: #d1fae5;
  color: #065f46;
  border: 1px solid #a7f3d0;
}

.alert-danger {
  background: #fee;
  color: #dc2626;
  border: 1px solid #fcc;
}

.dark .alert-success {
  background: rgba(5, 150, 105, 0.2);
  color: #34d399;
  border-color: rgba(5, 150, 105, 0.3);
}

.dark .alert-danger {
  background: rgba(220, 38, 38, 0.2);
  color: #f87171;
  border-color: rgba(220, 38, 38, 0.3);
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
  
  .table-container {
    overflow-x: auto;
  }
  
  th, td {
    padding: 12px;
    font-size: 0.85rem;
  }
  
  .modal-content {
    width: 95%;
    margin: 10% auto;
  }
  
  .modal-body {
    padding: 16px;
  }
}
</style>

</head>
<body>

<nav class="sidebar" id="sidebar">
  <h2>E-SPEED</h2>
  <ul>
    <li><a href="dashboard.php"><span class="icon">🏠</span> <span>Dashboard</span></a></li>
    <li><a href="pelanggan.php" class="active"><span class="icon">👥</span> <span>Pelanggan</span></a></li>
    <li><a href="pesanan.php"><span class="icon">🧾</span> <span>Pesanan</span></a></li>
    <li><a href="layanan.php"><span class="icon">🛠️</span> <span>Layanan</span></a></li>
    <li><a href="riwayat_repair.php"><span class="icon">📋</span> <span>Riwayat Repair</span></a></li>
    <li><a href="logout.php" class="logout"><span class="icon">🚪</span> <span>Logout</span></a></li>
  </ul>
</nav>

<div class="topbar">
  <h1>Data Pelanggan</h1>
  <div class="switch-container">
    <button class="toggle-btn" onclick="toggleSidebar()">☰</button>
    <button class="toggle-btn" onclick="toggleDark()">🌙</button>
    <div class="avatar"><?= isset($_SESSION['admin_nama']) ? strtoupper(substr($_SESSION['admin_nama'], 0, 1)) : 'A' ?></div>
  </div>
</div>

<main class="content">
  <h1>Data Pelanggan</h1>
  
  <!-- Alert Container -->
  <div id="alertContainer"></div>
  
  <button class="btn" onclick="openAddModal()">+ Tambah Pelanggan</button>

  <div class="table-container">
    <table id="customerTable">
      <thead>
        <tr>
          <th>No</th>
          <th>ID</th>
          <th>Nama</th>
          <th>No. Telepon</th>
          <th>Alamat</th>
          <th>Aksi</th>
        </tr>
      </thead>
      <tbody>
        <?php if ($result->num_rows > 0): $no=1; while($row = $result->fetch_assoc()): ?>
        <tr data-id="<?= $row['customer_id'] ?>">
          <td><?= $no++ ?></td>
          <td><?= htmlspecialchars($row['customer_id']) ?></td>
          <td class="customer-name"><?= htmlspecialchars($row['name']) ?></td>
          <td class="customer-phone"><?= htmlspecialchars($row['phone_number']) ?></td>
          <td class="customer-address"><?= htmlspecialchars($row['address']) ?></td>
          <td>
            <button class="btn-edit" onclick='openEditModal(<?= json_encode($row) ?>)'>Edit</button>
            <button class="btn-hapus" onclick='openDeleteModal("<?= $row['customer_id'] ?>", "<?= htmlspecialchars($row['name']) ?>")'>Hapus</button>
          </td>
        </tr>
        <?php endwhile; else: ?>
        <tr><td colspan="6" class="empty-state">Belum ada data pelanggan</td></tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</main>

<!-- MODAL TAMBAH -->
<div id="addModal" class="modal">
  <div class="modal-content">
    <div class="modal-header">
      <h2>Tambah Pelanggan Baru</h2>
      <span class="close" onclick="closeModal('addModal')">&times;</span>
    </div>
    <div class="modal-body">
      <form id="addForm">
        <div class="form-group">
          <label>Nama Lengkap *</label>
          <input type="text" id="add_nama" name="nama" placeholder="Masukkan nama lengkap" required>
        </div>
        
        <div class="form-group">
          <label>No. Telepon *</label>
          <input type="tel" id="add_telepon" name="telepon" placeholder="Contoh: 081234567890" required>
        </div>
        
        <div class="form-group">
          <label>Alamat *</label>
          <textarea id="add_alamat" name="alamat" placeholder="Masukkan alamat lengkap" required></textarea>
        </div>
      </form>
    </div>
    <div class="modal-footer">
      <button class="btn-modal btn-secondary-modal" onclick="closeModal('addModal')">Batal</button>
      <button class="btn-modal btn-primary-modal" onclick="addCustomer()">Simpan</button>
    </div>
  </div>
</div>

<!-- MODAL EDIT -->
<div id="editModal" class="modal">
  <div class="modal-content">
    <div class="modal-header">
      <h2>✏ Edit Pelanggan</h2>
      <span class="close" onclick="closeModal('editModal')">&times;</span>
    </div>
    <div class="modal-body">
      <form id="editForm">
        <input type="hidden" id="edit_customer_id" name="customer_id">
        
        <div class="form-group">
          <label>ID Pelanggan</label>
          <input type="text" id="edit_customer_id_display" disabled>
        </div>
        
        <div class="form-group">
          <label>Nama Lengkap *</label>
          <input type="text" id="edit_nama" name="nama" required>
        </div>
        
        <div class="form-group">
          <label>No. Telepon *</label>
          <input type="tel" id="edit_telepon" name="telepon" required>
        </div>
        
        <div class="form-group">
          <label>Alamat *</label>
          <textarea id="edit_alamat" name="alamat" required></textarea>
        </div>
      </form>
    </div>
    <div class="modal-footer">
      <button class="btn-modal btn-secondary-modal" onclick="closeModal('editModal')">Batal</button>
      <button class="btn-modal btn-primary-modal" onclick="updateCustomer()">Simpan</button>
    </div>
  </div>
</div>

<!-- MODAL DELETE -->
<div id="deleteModal" class="modal">
  <div class="modal-content">
    <div class="modal-header">
      <h2>⚠ Konfirmasi Hapus</h2>
      <span class="close" onclick="closeModal('deleteModal')">&times;</span>
    </div>
    <div class="modal-body">
      <p>Apakah Anda yakin ingin menghapus pelanggan:</p>
      <p style="font-weight: 600; margin: 16px 0; padding: 12px; background: var(--bg); border-radius: 6px;">
        <span id="delete_customer_name"></span>
      </p>
      <p style="color: var(--danger); font-size: 0.9rem;">⚠ Tindakan ini tidak dapat dibatalkan!</p>
      <input type="hidden" id="delete_customer_id">
    </div>
    <div class="modal-footer">
      <button class="btn-modal btn-secondary-modal" onclick="closeModal('deleteModal')">Batal</button>
      <button class="btn-modal btn-danger-modal" onclick="deleteCustomer()">Hapus</button>
    </div>
  </div>
</div>

<script>
// Toggle Sidebar
function toggleSidebar(){
    document.getElementById("sidebar").classList.toggle("collapsed");
    localStorage.setItem("sidebar", document.getElementById("sidebar").classList.contains("collapsed") ? "collapsed" : "expanded");
}

// Toggle Dark Mode
function toggleDark(){
    document.body.classList.toggle("dark");
    localStorage.setItem("darkmode", document.body.classList.contains("dark") ? "on" : "off");
}

// Load preferences
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

// EDIT MODAL
function openEditModal(customer) {
  document.getElementById('edit_customer_id').value = customer.customer_id;
  document.getElementById('edit_customer_id_display').value = customer.customer_id;
  document.getElementById('edit_nama').value = customer.name;
  document.getElementById('edit_telepon').value = customer.phone_number;
  document.getElementById('edit_alamat').value = customer.address;
  document.getElementById('editModal').style.display = 'block';
}

function updateCustomer() {
  const formData = new FormData();
  formData.append('action', 'update');
  formData.append('customer_id', document.getElementById('edit_customer_id').value);
  formData.append('nama', document.getElementById('edit_nama').value);
  formData.append('telepon', document.getElementById('edit_telepon').value);
  formData.append('alamat', document.getElementById('edit_alamat').value);
  
  fetch('edit_pelanggan.php', {
    method: 'POST',
    body: formData
  })
  .then(response => response.json())
  .then(data => {
    if (data.success) {
      showAlert(data.message, 'success');
      closeModal('editModal');
      
      // Update tabel tanpa reload
      const customerId = document.getElementById('edit_customer_id').value;
      const row = document.querySelector(`tr[data-id="${customerId}"]`);
      if (row) {
        row.querySelector('.customer-name').textContent = document.getElementById('edit_nama').value;
        row.querySelector('.customer-phone').textContent = document.getElementById('edit_telepon').value;
        row.querySelector('.customer-address').textContent = document.getElementById('edit_alamat').value;
      }
    } else {
      showAlert(data.message, 'danger');
    }
  })
  .catch(error => {
    showAlert('Terjadi kesalahan: ' + error, 'danger');
  });
}

// DELETE MODAL
function openDeleteModal(customerId, customerName) {
  document.getElementById('delete_customer_id').value = customerId;
  document.getElementById('delete_customer_name').textContent = customerName;
  document.getElementById('deleteModal').style.display = 'block';
}

function deleteCustomer() {
  const formData = new FormData();
  formData.append('action', 'delete');
  formData.append('customer_id', document.getElementById('delete_customer_id').value);
  
  fetch('hapus_pelanggan.php', {
    method: 'POST',
    body: formData
  })
  .then(response => response.json())
  .then(data => {
    if (data.success) {
      showAlert(data.message, 'success');
      closeModal('deleteModal');
      
      // Hapus baris dari tabel tanpa reload
      const customerId = document.getElementById('delete_customer_id').value;
      const row = document.querySelector(`tr[data-id="${customerId}"]`);
      if (row) {
        row.remove();
      }
      
      // Jika tabel kosong, tampilkan empty state
      const tbody = document.querySelector('#customerTable tbody');
      if (tbody.querySelectorAll('tr').length === 0) {
        tbody.innerHTML = '<tr><td colspan="6" class="empty-state">Belum ada data pelanggan</td></tr>';
      }
    } else {
      showAlert(data.message, 'danger');
    }
  })
  .catch(error => {
    showAlert('Terjadi kesalahan: ' + error, 'danger');
  });
}

// TAMBAH MODAL
function openAddModal() {
  // Reset form
  document.getElementById('add_nama').value = '';
  document.getElementById('add_telepon').value = '';
  document.getElementById('add_alamat').value = '';
  document.getElementById('addModal').style.display = 'block';
}

function addCustomer() {
  const formData = new FormData();
  formData.append('action', 'add');
  formData.append('nama', document.getElementById('add_nama').value);
  formData.append('telepon', document.getElementById('add_telepon').value);
  formData.append('alamat', document.getElementById('add_alamat').value);
  
  fetch('proses_tambah_pelanggan.php', {
    method: 'POST',
    body: formData
  })
  .then(response => response.json())
  .then(data => {
    if (data.success) {
      showAlert(data.message, 'success');
      closeModal('addModal');
      
      // Tambahkan baris baru ke tabel
      const tbody = document.querySelector('#customerTable tbody');
      const emptyState = tbody.querySelector('.empty-state');
      
      if (emptyState) {
        tbody.innerHTML = '';
      }
      
      const rowCount = tbody.querySelectorAll('tr').length + 1;
      const customerData = {
        customer_id: data.data.customer_id,
        name: data.data.name,
        phone_number: data.data.phone_number,
        address: data.data.address
      };
      
      const newRow = `
        <tr data-id="${data.data.customer_id}">
          <td>${rowCount}</td>
          <td>${data.data.customer_id}</td>
          <td class="customer-name">${data.data.name}</td>
          <td class="customer-phone">${data.data.phone_number}</td>
          <td class="customer-address">${data.data.address}</td>
          <td>
            <button class="btn-edit" onclick='openEditModal(${JSON.stringify(customerData)})'>✏ Edit</button>
            <button class="btn-hapus" onclick='openDeleteModal("${data.data.customer_id}", "${data.data.name}")'>🗑 Hapus</button>
          </td>
        </tr>
      `;
      
      tbody.insertAdjacentHTML('afterbegin', newRow);
      
      // Update nomor urut semua baris
      updateRowNumbers();
    } else {
      showAlert(data.message, 'danger');
    }
  })
  .catch(error => {
    showAlert('Terjadi kesalahan: ' + error, 'danger');
  });
}

// Fungsi untuk update nomor urut tabel
function updateRowNumbers() {
  const rows = document.querySelectorAll('#customerTable tbody tr');
  rows.forEach((row, index) => {
    const firstCell = row.querySelector('td:first-child');
    if (firstCell && !row.querySelector('.empty-state')) {
      firstCell.textContent = index + 1;
    }
  });
}
</script>

</body>
</html>