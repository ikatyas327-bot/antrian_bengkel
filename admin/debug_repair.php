<?php
session_start();
include '../koneksi.php';

if (!isset($_SESSION['admin_id'])) {
  die("Unauthorized");
}

echo "<h2>🔍 DEBUG RIWAYAT REPAIR</h2>";
echo "<hr>";

// 1. Cek struktur tabel repair
echo "<h3>1. Struktur Tabel Repair:</h3>";
$struktur = $conn->query("DESCRIBE repair");
if ($struktur) {
    echo "<table border='1' cellpadding='5'>";
    echo "<tr><th>Field</th><th>Type</th><th>Null</th><th>Key</th></tr>";
    while ($row = $struktur->fetch_assoc()) {
        echo "<tr>";
        echo "<td>{$row['Field']}</td>";
        echo "<td>{$row['Type']}</td>";
        echo "<td>{$row['Null']}</td>";
        echo "<td>{$row['Key']}</td>";
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "<p style='color:red'>Error: " . $conn->error . "</p>";
}

echo "<hr>";

// 2. Cek jumlah data di repair
echo "<h3>2. Jumlah Data di Tabel Repair:</h3>";
$count = $conn->query("SELECT COUNT(*) as total FROM repair")->fetch_assoc()['total'];
echo "<p><strong>Total: {$count} records</strong></p>";

echo "<hr>";

// 3. Cek data di tabel repair
echo "<h3>3. Data di Tabel Repair:</h3>";
$repair_data = $conn->query("SELECT * FROM repair ORDER BY start_date DESC LIMIT 10");
if ($repair_data && $repair_data->num_rows > 0) {
    echo "<table border='1' cellpadding='5'>";
    echo "<tr><th>repair_id</th><th>queue_id</th><th>service_id</th><th>technician_id</th><th>start_date</th><th>description</th><th>warranty</th></tr>";
    while ($row = $repair_data->fetch_assoc()) {
        echo "<tr>";
        echo "<td>{$row['repair_id']}</td>";
        echo "<td>{$row['queue_id']}</td>";
        echo "<td>{$row['service_id']}</td>";
        echo "<td>{$row['technician_performer_id']}</td>";
        echo "<td>{$row['start_date']}</td>";
        echo "<td>{$row['description']}</td>";
        echo "<td>{$row['is_warranty_used']}</td>";
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "<p style='color:red'>❌ Tidak ada data di tabel repair!</p>";
}

echo "<hr>";

// 4. Cek pesanan dengan status Selesai
echo "<h3>4. Pesanan dengan Status 'Selesai':</h3>";
$selesai = $conn->query("SELECT queue_id, queue_number, status, technician_id, completed_at FROM queue WHERE LOWER(status) = 'selesai' ORDER BY completed_at DESC LIMIT 5");
if ($selesai && $selesai->num_rows > 0) {
    echo "<table border='1' cellpadding='5'>";
    echo "<tr><th>queue_id</th><th>queue_number</th><th>status</th><th>technician_id</th><th>completed_at</th></tr>";
    while ($row = $selesai->fetch_assoc()) {
        echo "<tr>";
        echo "<td>{$row['queue_id']}</td>";
        echo "<td>{$row['queue_number']}</td>";
        echo "<td>{$row['status']}</td>";
        echo "<td>{$row['technician_id']}</td>";
        echo "<td>{$row['completed_at']}</td>";
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "<p style='color:orange'>⚠️ Tidak ada pesanan dengan status Selesai</p>";
}

echo "<hr>";

// 5. Test insert ke repair
echo "<h3>5. Test Insert ke Repair (Simulasi):</h3>";
echo "<p><em>Untuk test insert, ubah status pesanan menjadi 'Selesai' di halaman pesanan</em></p>";

// 6. Cek log errors
echo "<hr>";
echo "<h3>6. Cek Error Log:</h3>";
$error_log = ini_get('error_log');
if ($error_log && file_exists($error_log)) {
    $logs = file_get_contents($error_log);
    $recent_logs = array_slice(explode("\n", $logs), -20);
    echo "<pre style='background:#f5f5f5; padding:10px; max-height:300px; overflow:auto;'>";
    echo implode("\n", $recent_logs);
    echo "</pre>";
} else {
    echo "<p>Error log tidak ditemukan di: " . ($error_log ? $error_log : 'tidak ada path') . "</p>";
}

echo "<hr>";
echo "<p><a href='pesanan.php'>← Kembali ke Pesanan</a> | <a href='riwayat_repair.php'>Lihat Riwayat Repair →</a></p>";
?>

<style>
body {
    font-family: Arial, sans-serif;
    padding: 20px;
    background: #f5f5f5;
}
h2, h3 {
    color: #333;
}
table {
    background: white;
    border-collapse: collapse;
    width: 100%;
    margin: 10px 0;
}
th {
    background: #2563eb;
    color: white;
    padding: 10px;
    text-align: left;
}
td {
    padding: 8px;
}
tr:nth-child(even) {
    background: #f9f9f9;
}
</style>