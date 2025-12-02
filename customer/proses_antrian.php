<?php
include __DIR__ . '/../koneksi.php';

if (isset($_POST['submit'])) {
    $nama = $_POST['nama'];
    $telepon = $_POST['telepon'];
    $alamat = $_POST['alamat'];
    $keluhan = $_POST['keluhan'];
    $id_menu = $_POST['id_menu'];

    // Ambil nomor antrean terakhir
    $result = $conn->query("SELECT MAX(queue_id) AS last_id FROM queue");
    $row = $result->fetch_assoc();
    $nextId = $row['last_id'] + 1;

    // Buat nomor antrean otomatis (misal BCR-01)
    $queue_number = 'BCR-' . str_pad($nextId, 2, '0', STR_PAD_LEFT);

    // Simpan ke database
    $stmt = $conn->prepare("INSERT INTO queue (queue_number, nama, telepon, alamat, tanggal, keluhan, id_menu, priority, status, created_at)
                            VALUES (?, ?, ?, ?, CURDATE(), ?, ?, 'Normal', 'Menunggu', NOW())");
    $stmt->bind_param("sssssi", $queue_number, $nama, $telepon, $alamat, $keluhan, $id_menu);
    $stmt->execute();

    // Arahkan ke halaman tiket
    header("Location: tiket_antrian.php?antrian=" . $queue_number);
    exit;
} else {
    echo "Form tidak dikirim dengan benar.";
}
?>
