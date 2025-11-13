<?php
$mysqli = require 'koneksi.php';

// DATA ADMIN PERTAMA — bisa kamu ubah
$nama = "Admin E-SPEED";
$email = "admin@espeed.com";
$password = "admin123"; // password asli (nanti di-hash)

// Cek apakah admin dengan email ini sudah ada
$check = $mysqli->prepare("SELECT id FROM admin WHERE email = ?");
$check->bind_param("s", $email);
$check->execute();
$check->store_result();

if ($check->num_rows > 0) {
    echo "❗ Admin dengan email $email sudah ada.";
    exit;
}
$check->close();

// Hash password
$hash = password_hash($password, PASSWORD_DEFAULT);

// Insert admin baru ke database
$stmt = $mysqli->prepare("INSERT INTO admin (nama, email, password) VALUES (?, ?, ?)");
$stmt->bind_param("sss", $nama, $email, $hash);

if ($stmt->execute()) {
    echo "✅ Admin berhasil dibuat!<br><br>";
    echo "Email: $email <br>";
    echo "Password: $password <br><br>";
    echo "📌 Sekarang kamu bisa login menggunakan akun ini.";
} else {
    echo "❌ Gagal membuat admin: " . $stmt->error;
}
$stmt->close();
