<?php
include 'koneksi.php';
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lacak Antrian</title>
    <link rel="stylesheet" href="style.css">
    <style>
        body {
            font-family: "Poppins", sans-serif;
            background: #f2f4f8;
            padding: 40px;
            text-align: center;
        }
        .track-box {
            background: #fff;
            max-width: 500px;
            margin: 0 auto;
            padding: 30px;
            border-radius: 12px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        input {
            width: 80%;
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 8px;
        }
        button {
            background: #007bff;
            color: #fff;
            padding: 10px 16px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
        }
        button:hover {
            background: #0056b3;
        }
        .result {
            margin-top: 20px;
            text-align: left;
        }
    </style>
</head>
<body>

    <div class="track-box">
        <h2>Lacak Status Antrian</h2>
        <form method="GET" action="">
            <input type="text" name="queue_number" placeholder="Masukkan Nomor Antrian (misal: PBM-01)" required>
            <button type="submit">Cari</button>
        </form>

        <div class="result">
            <?php
            if (isset($_GET['queue_number'])) {
                $queue_number = $_GET['queue_number'];

                $query = $conn->prepare("SELECT q.*, m.name AS menu_name
                FROM queue q
                JOIN menu m ON q.id_menu = m.id_menu
                WHERE q.queue_number = ?"); 
                $query->bind_param("s", $queue_number);
                $query->execute();
                $result = $query->get_result();

                if ($result->num_rows > 0) {
                    $row = $result->fetch_assoc();
                    echo "<h3>Detail Antrian:</h3>";
                    echo "<p><strong>Nomor Antrian:</strong> {$row['queue_number']}</p>";
                    echo "<p><strong>Nama:</strong> {$row['nama']}</p>";
                    echo "<p><strong>Layanan:</strong> {$row['menu_name']}</p>";
                    echo "<p><strong>Status:</strong> {$row['status']}</p>";
                    echo "<p><strong>Tanggal:</strong> {$row['tanggal']}</p>";
                } else {
                    echo "<p style='color:red;'>Nomor antrian tidak ditemukan.</p>";
                }
            }
            ?>
        </div>
    </div>

</body>
</html>
