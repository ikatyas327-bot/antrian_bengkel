<?php
// customer/receipt.php
$mysqli = require '../koneksi.php';
require '../admin_config.php';

$id = intval($_GET['id'] ?? 0);
if (!$id) { echo "ID tidak valid"; exit; }

$stmt = $mysqli->prepare("SELECT q.*, m.name AS service_name, m.price, m.estimate_days FROM queue q LEFT JOIN menu m ON q.id_menu = m.id_menu WHERE q.id = ?");
$stmt->bind_param("i",$id);
$stmt->execute();
$res = $stmt->get_result();
$row = $res->fetch_assoc();
if (!$row) { echo "Data tidak ditemukan"; exit; }

// prepare WA text
$wa_text = "Halo ".$row['nama']."%0ANomor antrian Anda: ".$row['queue_number']."%0ALayanan: ".$row['service_name']."%0AEstimasi: ".($row['estimate_days'] ?? '-')." hari%0ATerima kasih.";
$wa_url = "https://wa.me/{$admin_whatsapp}?text={$wa_text}";

// QR codes (Google Charts API used for QR generation)
$payment_qr = 'https://chart.googleapis.com/chart?cht=qr&chs=300x300&chl=' . urlencode($payment_link . '&ref=' . $row['queue_number']);
$review_qr  = 'https://chart.googleapis.com/chart?cht=qr&chs=300x300&chl=' . urlencode($google_review_link);
?>
<!doctype html>
<html>
<head>
<meta charset="utf-8">
<title>Struk Antrian - <?=htmlspecialchars($row['queue_number'])?></title>
<style>
body{font-family: Arial, Helvetica, sans-serif; background:#f6f8fb; padding:20px}
.card{max-width:420px;margin:0 auto;background:#fff;border-radius:8px;box-shadow:0 6px 18px rgba(10,42,67,0.08);overflow:hidden}
.header{background:#0A2A43;padding:18px;color:#fff;text-align:center}
.header img{height:56px;opacity:0.92}
.header h2{margin:6px 0 0;font-size:18px;color:#cfcfcf}
.body{padding:18px;color:#0b2233}
.row{display:flex;justify-content:space-between;margin:6px 0}
.label{color:#6b7a86}
.value{font-weight:700;color:#0a2a43}
.kv{margin:8px 0}
.actions{display:flex;gap:8px;justify-content:center;padding:14px;background:#fbfcfd}
.btn{background:#0A2A43;color:#fff;padding:10px 14px;border-radius:6px;text-decoration:none}
.qrwrap{display:flex;gap:12px;justify-content:center;padding:14px}
.small{font-size:12px;color:#6b7a86}
.footer{padding:12px;font-size:12px;color:#6b7a86;text-align:center;background:#fbfcfd}
</style>
</head>
<body>
<div class="card">
  <div class="header">
    <img src="../umkmlogo.png" alt="logo"><br>
    <h2><?=htmlspecialchars($company_name)?></h2>
    <div class="small"><?=htmlspecialchars($company_address)?> • <?=htmlspecialchars($company_phone)?></div>
  </div>

  <div class="body">
    <div class="row"><div class="label">No. Antrian</div><div class="value"><?=htmlspecialchars($row['queue_number'])?></div></div>
    <div class="row"><div class="label">Service</div><div class="value"><?=htmlspecialchars($row['service_name'])?></div></div>
    <div class="row"><div class="label">Nama</div><div class="value"><?=htmlspecialchars($row['nama'])?></div></div>
    <div class="row"><div class="label">Telepon</div><div class="value"><?=htmlspecialchars($row['telepon'])?></div></div>

    <div class="kv"><div class="label">Estimasi</div><div class="value"><?=htmlspecialchars($row['estimate_days'] ?? '-')?> hari</div></div>
    <div class="kv"><div class="label">Prioritas</div><div class="value"><?=htmlspecialchars($row['priority'])?></div></div>
    <div class="kv"><div class="label">Keluhan</div><div><?=nl2br(htmlspecialchars($row['keluhan']))?></div></div>

    <hr style="border:none;border-top:1px solid #eef2f5;margin:12px 0">

    <div style="display:flex;justify-content:space-between;align-items:center">
      <div>
        <div class="small">Estimasi selesai</div>
        <div class="value"><?=htmlspecialchars($row['estimate_days'] ? date('d M Y', strtotime($row['created_at'] . ' + ' . intval($row['estimate_days']) . ' days')) : '-')?></div>
      </div>
      <div>
        <div class="small">Nomor referensi</div>
        <div class="value"><?=htmlspecialchars($row['queue_number'])?></div>
      </div>
    </div>

    <div class="qrwrap">
      <div style="text-align:center">
        <img src="<?= $payment_qr ?>" alt="pay" width="120"><br>
        <div class="small">Scan bayar</div>
      </div>
      <div style="text-align:center">
        <img src="<?= $review_qr ?>" alt="review" width="120"><br>
        <div class="small">Beri review</div>
      </div>
    </div>
  </div>

  <div class="actions">
    <a class="btn" href="javascript:window.print()">Print</a>
    <a class="btn" href="<?= $wa_url ?>" target="_blank">Kirim via WhatsApp</a>
  </div>

  <div class="footer">
    Estimasi & informasi hanya perkiraan. Simpan bukti ini. Terima kasih sudah memilih <?=htmlspecialchars($company_name)?>.
  </div>
</div>
</body>
</html>
