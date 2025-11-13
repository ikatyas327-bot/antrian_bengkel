<?php
// customer/simpan_antrian.php
// Handles saving new customer + queue record.
// Place this file in C:/xampp/htdocs/antrian_bengkel/customer/simpan_antrian.php
// Assumes koneksi.php in same folder defines $conn (mysqli)

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

include __DIR__ . '/../koneksi.php';
 // must set $conn as mysqli connection

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    exit('Method Not Allowed');
}

// sanitize + fetch
$id_menu  = isset($_POST['id_menu']) ? trim($_POST['id_menu']) : null;
$nama     = isset($_POST['nama']) ? trim($_POST['nama']) : '';
$telepon  = isset($_POST['telepon']) ? trim($_POST['telepon']) : '';
$alamat   = isset($_POST['alamat']) ? trim($_POST['alamat']) : '';
$keluhan  = isset($_POST['keluhan']) ? trim($_POST['keluhan']) : '';
$priority = isset($_POST['priority']) ? trim($_POST['priority']) : 'Normal';

// server-side validation (repeat of client checks)
$errors = [];
if (!$id_menu) $errors[] = "Layanan belum dipilih.";
if ($nama === '') $errors[] = "Nama wajib diisi.";
// telepon: normalize digits only and minimal length 10
$tele_digits = preg_replace('/\D/', '', $telepon);
if ($tele_digits === '' || strlen($tele_digits) < 10) $errors[] = "Nomor telepon tidak valid (minimal 10 digit).";
if ($alamat === '') $errors[] = "Alamat wajib diisi.";
if ($keluhan === '') $errors[] = "Keluhan wajib diisi.";

if (!empty($errors)) {
    // show first error (you can return JSON here if you prefer)
    echo "<h3>Terjadi kesalahan:</h3><ul>";
    foreach ($errors as $err) echo "<li>" . htmlspecialchars($err) . "</li>";
    echo "</ul><p><a href='javascript:history.back()'>Kembali</a></p>";
    exit;
}

// Begin transaction
$conn->begin_transaction();

try {
    // 1) generate new customer_id (C + 5 digit)
    $q = $conn->query("SELECT customer_id FROM customer ORDER BY customer_id DESC LIMIT 1");
    if ($q && $q->num_rows > 0) {
        $last = $q->fetch_assoc()['customer_id'];
        $num = (int) substr($last, 1) + 1;
    } else {
        $num = 1;
    }
    $customer_id = 'C' . str_pad($num, 5, '0', STR_PAD_LEFT);

    // 2) insert into customer (name, phone_number, address, NULL)
    $stmtCust = $conn->prepare("INSERT INTO customer (customer_id, name, phone_number, address) VALUES (?, ?, ?, ?)");
    if (!$stmtCust) throw new Exception("Prepare customer failed: " . $conn->error);
    $stmtCust->bind_param("ssss", $customer_id, $nama, $tele_digits, $alamat);
    if (!$stmtCust->execute()) throw new Exception("Insert customer failed: " . $stmtCust->error);
    $stmtCust->close();

    // 3) generate queue_number per service prefix (initials)
    // get service name
    $mres = $conn->prepare("SELECT name FROM menu WHERE id_menu = ? LIMIT 1");
    if (!$mres) throw new Exception("Prepare menu select failed: " . $conn->error);
    $mres->bind_param("s", $id_menu);
    $mres->execute();
    $mres->bind_result($service_name);
    if (!$mres->fetch()) {
        $mres->close();
        throw new Exception("Layanan tidak ditemukan.");
    }
    $mres->close();

    // build prefix from initials
    $words = preg_split('/\s+/', trim($service_name));
    $prefix = "";
    foreach ($words as $w) {
        if ($w !== "") $prefix .= strtoupper(mb_substr($w, 0, 1));
    }
    if ($prefix === "") $prefix = "Q";

    // find last queue_number for this service
    $stmtLast = $conn->prepare("SELECT queue_number FROM queue WHERE id_menu = ? ORDER BY queue_id DESC LIMIT 1");
    if (!$stmtLast) throw new Exception("Prepare last queue failed: " . $conn->error);
    $stmtLast->bind_param("s", $id_menu);
    $stmtLast->execute();
    $stmtLast->bind_result($lastQueueNumber);
    $nextNum = 1;
    if ($stmtLast->fetch()) {
        // expected lastQueueNumber like "BP-03" -> parse numeric part after dash
        $stmtLast->close();
        $parts = explode('-', $lastQueueNumber);
        $numPart = (int) end($parts);
        $nextNum = $numPart + 1;
    } else {
        $stmtLast->close();
        $nextNum = 1;
    }
    $queue_number = $prefix . "-" . str_pad($nextNum, 2, "0", STR_PAD_LEFT);

    // 4) insert into queue (clean: store customer_id, id_menu, queue_number, priority, status, tanggal)
    $stmtQ = $conn->prepare("INSERT INTO queue (queue_number, id_menu, customer_id, status, priority, tanggal) VALUES (?, ?, ?, 'Menunggu', ?, CURDATE())");
    if (!$stmtQ) throw new Exception("Prepare insert queue failed: " . $conn->error);
    $stmtQ->bind_param("ssis", $queue_number, $id_menu, $customer_id, $priority);
    if (!$stmtQ->execute()) throw new Exception("Insert queue failed: " . $stmtQ->error);
    $stmtQ->close();

    // commit
    $conn->commit();

    // redirect to ticket page (you can change URL)
    header("Location: tiket_antrian.php?antrian=" . urlencode($queue_number));
    exit;
} catch (Exception $ex) {
    // rollback and show error
    $conn->rollback();
    echo "<h3>Terjadi kesalahan saat menyimpan:</h3>";
    echo "<pre>" . htmlspecialchars($ex->getMessage()) . "</pre>";
    echo "<p><a href='javascript:history.back()'>Kembali</a></p>";
    exit;
}
