<?php
$host = "localhost";
$user = "root";
$pass = "";
$db   = "umkn_bengkel_bubut_new";

$conn = new mysqli($host, $user, $pass, $db);

if ($conn->connect_error) {
    die("Koneksi gagal: " . $conn->connect_error);
}
?>
