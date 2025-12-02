<?php
include __DIR__ . '/../koneksi.php';

if (!isset($conn) || !$conn) {
    die("❌ Koneksi database gagal.");
}

$antrian = $_GET['antrian'] ?? null;
if (!$antrian) {
    die("ID antrian tidak ditemukan.");
}

// Query dengan COALESCE untuk compatibility dengan data lama
$query = "SELECT q.*, 
          COALESCE(c.name, q.nama, 'Tidak tersedia') AS nama, 
          COALESCE(c.phone_number, q.telepon, '-') AS telepon,
          COALESCE(c.address, q.alamat, '-') AS alamat,
          m.name AS layanan_nama 
          FROM queue q 
          LEFT JOIN customer c ON q.customer_id = c.customer_id
          LEFT JOIN menu m ON q.id_menu = m.id_menu
          WHERE q.queue_number = ?";

$stmt = $conn->prepare($query);
$stmt->bind_param("s", $antrian);
$stmt->execute();
$result = $stmt->get_result();

if ($result && $result->num_rows > 0) {
    $data = $result->fetch_assoc();
} else {
    die("Data antrian tidak ditemukan.");
}

$no_wa = "628993322514"; // ubah sesuai nomor WhatsApp bengkel
$pesan = urlencode("Halo, saya *{$data['nama']}*.\nNomor antrean saya: *{$data['queue_number']}* untuk layanan *{$data['layanan_nama']}*.\nTerima kasih!");
$link_wa = "https://wa.me/$no_wa?text=$pesan";
?>

<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Tiket Antrian - <?= htmlspecialchars($data['queue_number']); ?></title>
  <style>
    * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
    }

    body {
      font-family: 'Poppins', 'Segoe UI', sans-serif;
      background: white;
      display: flex;
      justify-content: center;
      align-items: center;
      min-height: 100vh;
      padding: 20px;
    }

    .ticket {
      background: white;
      border-radius: 16px;
      padding: 40px;
      box-shadow: 0 20px 60px rgba(0,0,0,0.3);
      width: 100%;
      max-width: 450px;
      position: relative;
      overflow: hidden;
    }

    .ticket::before {
      content: '';
      position: absolute;
      top: 0;
      left: 0;
      right: 0;
      height: 6px;
      background: linear-gradient(90deg, #667eea 0%, #764ba2 100%);
    }

    .header {
      text-align: center;
      margin-bottom: 30px;
      padding-bottom: 20px;
      border-bottom: 2px dashed #e0e0e0;
    }

    .header h1 {
      color: #3449a7ff;
      margin-bottom: 5px;
      font-size: 1.8rem;
    }

    .header p {
      color: #666;
      font-size: 0.9rem;
    }

    .queue-number-section {
      text-align: center;
      margin: 25px 0;
      padding: 20px;
      background: linear-gradient(135deg, #040c33ff 0%, #443bc2ff 100%);
      border-radius: 12px;
    }

    .queue-label {
      color: rgba(255,255,255,0.9);
      font-size: 0.9rem;
      font-weight: 500;
      margin-bottom: 5px;
    }

    .queue-number {
      font-size: 3rem;
      font-weight: bold;
      color: white;
      text-shadow: 0 2px 10px rgba(0,0,0,0.2);
      letter-spacing: 2px;
    }

    .info-section {
      margin: 25px 0;
    }

    .info-row {
      display: flex;
      justify-content: space-between;
      padding: 12px 0;
      border-bottom: 1px solid #f0f0f0;
    }

    .info-row:last-child {
      border-bottom: none;
    }

    .info-label {
      color: #666;
      font-weight: 600;
      font-size: 0.9rem;
    }

    .info-value {
      color: #333;
      font-weight: 400;
      text-align: right;
      max-width: 60%;
      word-wrap: break-word;
    }

    .status-badge {
      display: inline-block;
      padding: 4px 12px;
      border-radius: 20px;
      font-size: 0.85rem;
      font-weight: 600;
    }

    .status-menunggu {
      background: #fff3cd;
      color: #856404;
    }

    .status-diproses {
      background: #cfe2ff;
      color: #084298;
    }

    .status-selesai {
      background: #d1e7dd;
      color: #0f5132;
    }

    .actions {
      margin-top: 30px;
      display: flex;
      gap: 10px;
      flex-wrap: wrap;
    }

    button, a {
      flex: 1;
      min-width: 140px;
      padding: 12px 20px;
      border: none;
      border-radius: 8px;
      cursor: pointer;
      text-decoration: none;
      font-weight: 600;
      font-size: 0.9rem;
      transition: all 0.3s ease;
      display: inline-flex;
      align-items: center;
      justify-content: center;
      gap: 8px;
    }

    .btn-print { 
      background: linear-gradient(135deg, #040c33ff 0%, #443bc2ff 100%);
      color: white;
    }
    .btn-print:hover { 
      transform: translateY(-2px);
      box-shadow: 0 5px 15px rgba(102, 126, 234, 0.4);
    }

    .btn-wa { 
      background: linear-gradient(135deg, #073f0fff 0%, #29a853ff 100%);
      color: white;
    }
    .btn-wa:hover { 
      background: linear-gradient(135deg, #073f0fff 0%, #29a853ff 100%);
      transform: translateY(-2px);
      box-shadow: 0 5px 15px rgba(37, 211, 102, 0.4);
    }

    .btn-back {
      background: #3c3f41ff;
      color: white;
    }
    .btn-back:hover {
      background: #545b62;
      transform: translateY(-2px);
      box-shadow: 0 5px 15px rgba(108, 117, 125, 0.4);
    }

    .footer {
      margin-top: 30px;
      padding-top: 20px;
      border-top: 2px dashed #e0e0e0;
      text-align: center;
      color: #999;
      font-size: 0.85rem;
    }

    @media print {
      body {
        background: white;
      }
      .actions { display: none; }
      .ticket { 
        box-shadow: none;
        border: 1px solid #ddd;
      }
      .ticket::before {
        display: none;
      }
    }

    @media (max-width: 480px) {
      .ticket {
        padding: 30px 20px;
      }
      
      .queue-number {
        font-size: 2.5rem;
      }
      
      .actions {
        flex-direction: column;
      }
      
      button, a {
        width: 100%;
      }
    }
  </style>
</head>
<body>
  <div class="ticket">
    <div class="header">
      <h1>E-SPEED Bengkel</h1>
      <p>Bengkel Bubut Terpercaya</p>
    </div>

    <div class="queue-number-section">
      <div class="queue-label">Nomor Antrian Anda</div>
      <div class="queue-number"><?= htmlspecialchars($data['queue_number']); ?></div>
    </div>

    <div class="info-section">
      <div class="info-row">
        <span class="info-label">Nama</span>
        <span class="info-value"><?= htmlspecialchars($data['nama']); ?></span>
      </div>
      <div class="info-row">
        <span class="info-label">Telepon</span>
        <span class="info-value"><?= htmlspecialchars($data['telepon']); ?></span>
      </div>
      <div class="info-row">
        <span class="info-label">Layanan</span>
        <span class="info-value"><?= htmlspecialchars($data['layanan_nama']); ?></span>
      </div>
      <div class="info-row">
        <span class="info-label">Tanggal</span>
        <span class="info-value"><?= htmlspecialchars($data['tanggal'] ?? date('Y-m-d')); ?></span>
      </div>
      <div class="info-row">
        <span class="info-label">Status</span>
        <span class="info-value">
          <?php 
          $status = $data['status'] ?? 'Menunggu';
          $status_class = 'status-menunggu';
          if ($status == 'Diproses') $status_class = 'status-diproses';
          if ($status == 'Selesai') $status_class = 'status-selesai';
          ?>
          <span class="status-badge <?= $status_class ?>"><?= htmlspecialchars($status); ?></span>
        </span>
      </div>
    </div>

    <div class="actions">
      <button class="btn-print" onclick="window.print()">
        Cetak Tiket
      </button>
      <a href="<?= $link_wa ?>" class="btn-wa" target="_blank">
        WhatsApp
      </a>
      <a href="index.php" class="btn-back">
        Kembali
      </a>
    </div>

    <div class="footer">
      Simpan tiket ini sebagai bukti antrian Anda
    </div>
  </div>

  <script>
    // Auto print option (optional)
    // window.onload = function() {
    //   if (confirm('Cetak tiket sekarang?')) {
    //     window.print();
    //   }
    // }
  </script>
</body>
</html>