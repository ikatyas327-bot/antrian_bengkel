<?php
session_start();
include '../koneksi.php';

if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit;
}

// Ambil data riwayat
$query = "SELECT * FROM v_riwayat_service_repair ORDER BY start_date DESC";
$result = $conn->query($query);

// Set header untuk download Excel
$filename = "Riwayat_Repair_" . date('Y-m-d_His') . ".xls";
header("Content-Type: application/vnd.ms-excel");
header("Content-Disposition: attachment; filename=\"$filename\"");
header("Pragma: no-cache");
header("Expires: 0");

// Output Excel content
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <style>
        table {
            border-collapse: collapse;
            width: 100%;
        }
        th {
            background-color: #2563eb;
            color: white;
            font-weight: bold;
            padding: 10px;
            text-align: left;
            border: 1px solid #000;
        }
        td {
            padding: 8px;
            border: 1px solid #000;
        }
        .header-info {
            margin-bottom: 20px;
        }
        .header-info td {
            border: none;
            padding: 5px;
        }
    </style>
</head>
<body>
    <table class="header-info">
        <tr>
            <td colspan="11" style="font-size: 18px; font-weight: bold; text-align: center;">
                RIWAYAT REPAIR & SERVICE
            </td>
        </tr>
        <tr>
            <td colspan="11" style="font-size: 14px; text-align: center;">
                E-SPEED Bengkel Bubut
            </td>
        </tr>
        <tr>
            <td colspan="11" style="font-size: 12px; text-align: center;">
                Dicetak pada: <?= date('d F Y, H:i:s') ?>
            </td>
        </tr>
        <tr><td colspan="11">&nbsp;</td></tr>
    </table>

    <table>
        <thead>
            <tr>
                <th>No</th>
                <th>No. Antrian</th>
                <th>ID Service</th>
                <th>Nama Pelanggan</th>
                <th>No. Telepon</th>
                <th>Alamat</th>
                <th>Layanan</th>
                <th>Harga</th>
                <th>Teknisi</th>
                <th>Tanggal Selesai</th>
                <th>Status Garansi</th>
            </tr>
        </thead>
        <tbody>
            <?php 
            if ($result && $result->num_rows > 0): 
                $no = 1;
                $total_harga = 0;
                while($row = $result->fetch_assoc()): 
                    $total_harga += floatval($row['service_price']);
            ?>
            <tr>
                <td><?= $no++ ?></td>
                <td><?= htmlspecialchars($row['queue_number'] ?? 'N/A') ?></td>
                <td>SV-<?= str_pad($row['service_id'], 5, '0', STR_PAD_LEFT) ?></td>
                <td><?= htmlspecialchars($row['customer_name'] ?? 'N/A') ?></td>
                <td><?= htmlspecialchars($row['customer_phone'] ?? 'N/A') ?></td>
                <td><?= htmlspecialchars($row['customer_address'] ?? 'N/A') ?></td>
                <td><?= htmlspecialchars($row['service_name'] ?? 'N/A') ?></td>
                <td style="text-align: right;">Rp <?= number_format($row['service_price'], 0, ',', '.') ?></td>
                <td><?= htmlspecialchars($row['technician_name'] ?? 'N/A') ?></td>
                <td><?= $row['completed_at'] ? date('d/m/Y H:i', strtotime($row['completed_at'])) : date('d/m/Y H:i', strtotime($row['start_date'])) ?></td>
                <td><?= ucfirst($row['is_warranty_used']) ?></td>
            </tr>
            <?php 
                endwhile;
            ?>
            <tr style="font-weight: bold; background-color: #f0f0f0;">
                <td colspan="7" style="text-align: right;">TOTAL:</td>
                <td style="text-align: right;">Rp <?= number_format($total_harga, 0, ',', '.') ?></td>
                <td colspan="3"></td>
            </tr>
            <?php else: ?>
            <tr>
                <td colspan="11" style="text-align: center;">Tidak ada data riwayat</td>
            </tr>
            <?php endif; ?>
        </tbody>
    </table>

    <table class="header-info" style="margin-top: 30px;">
        <tr>
            <td colspan="11" style="font-size: 10px; color: #666;">
                Dokumen ini digenerate secara otomatis oleh sistem E-SPEED
            </td>
        </tr>
    </table>
</body>
</html>
<?php
$conn->close();
?>