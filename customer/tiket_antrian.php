<?php
include __DIR__ . '/../koneksi.php';

if (!isset($conn) || !$conn) {
    die("❌ Koneksi database gagal.");
}

$antrian = $_GET['antrian'] ?? null;
if (!$antrian) {
    die("ID antrian tidak ditemukan.");
}

$query = "SELECT q.*, m.name AS layanan_nama 
          FROM queue q 
          LEFT JOIN menu m ON q.id_menu = m.id_menu
          WHERE q.queue_number = '$antrian'";
$result = $conn->query($query);

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
  <title>Tiket Antrian - <?= htmlspecialchars($data['queue_number']); ?></title>
  <style>
    body {
      font-family: 'Poppins', sans-serif;
      background-color: #f2f4f7;
      display: flex;
      justify-content: center;
      align-items: center;
      height: 100vh;
    }

    .ticket {
      background: white;
      border-radius: 12px;
      padding: 30px 40px;
      box-shadow: 0 4px 10px rgba(0,0,0,0.15);
      text-align: center;
      width: 380px;
    }

    .ticket h1 {
      color: #007bff;
      margin-bottom: 15px;
    }

    .queue-number {
      font-size: 2.5rem;
      font-weight: bold;
      color: #00bfa6;
      margin: 15px 0;
    }

    button, a {
      display: inline-block;
      margin: 6px;
      padding: 10px 20px;
      border: none;
      border-radius: 6px;
      cursor: pointer;
      text-decoration: none;
      font-weight: bold;
      transition: 0.3s;
    }

    .btn-print { background-color: #007bff; color: white; }
    .btn-print:hover { background-color: #0056b3; }

    .btn-wa { background-color: #25D366; color: white; }
    .btn-wa:hover { background-color: #1da955; }

    @media print {
      .actions { display: none; }
      body { background: white; }
      .ticket { box-shadow: none; border: 1px solid #ccc; }
    }
  </style>
</head>
<body>
  <div class="ticket">
    <h1>E-SPEED Bengkel</h1>
    <p><strong>Nomor Antrian:</strong></p>
    <div class="queue-number"><?= htmlspecialchars($data['queue_number']); ?></div>
    <p><strong>Nama:</strong> <?= htmlspecialchars($data['nama']); ?></p>
    <p><strong>Layanan:</strong> <?= htmlspecialchars($data['layanan_nama']); ?></p>
    <p><strong>Tanggal:</strong> <?= htmlspecialchars($data['tanggal']); ?></p>
    <p><strong>Status:</strong> <?= htmlspecialchars($data['status']); ?></p>

    <div class="actions">
      <button class="btn-print" onclick="window.print()">🖨️ Cetak Tiket</button>
      <a href="<?= $link_wa ?>" class="btn-wa" target="_blank">💬 Kirim ke WhatsApp</a>
    </div>
  </div>
</body>
</html>
