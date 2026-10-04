<?php
include 'config.php';
cek_login();

$user_id   = $_SESSION['user_id'];
$nama_user = $_SESSION['nama_lengkap'];
$bulan = isset($_GET['bulan']) ? $_GET['bulan'] : 'September';
$tahun = isset($_GET['tahun']) ? $_GET['tahun'] : '2026';

$array_bulan = [
    'Januari' => '01', 'Februari' => '02', 'Maret' => '03', 'April' => '04',
    'Mei' => '05', 'Juni' => '06', 'Juli' => '07', 'Agustus' => '08',
    'September' => '09', 'Oktober' => '10', 'November' => '11', 'Desember' => '12'
];
$angka_bulan = $array_bulan[$bulan] ?? '09';

$query = "SELECT * FROM tbmasalah WHERE user_id = '$user_id' AND MONTH(tanggal) = '$angka_bulan' AND YEAR(tanggal) = '$tahun' ORDER BY tanggal DESC";
$result = mysqli_query($koneksi, $query);
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>Cetak Laporan Bulanan - <?= htmlspecialchars($nama_user); ?></title>
    <style>
    body {
        font-family: Arial, sans-serif;
        font-size: 10px;
        color: #333;
        margin: 10px;
    }

    h2,
    h3 {
        text-align: center;
        margin-bottom: 3px;
    }

    p {
        text-align: center;
        margin-top: 0;
        color: #666;
        font-size: 10px;
        margin-bottom: 15px;
    }

    table {
        width: 100%;
        border-collapse: collapse;
        margin-top: 10px;
    }

    th,
    td {
        border: 1px solid #999;
        padding: 6px;
        text-align: left;
        vertical-align: top;
    }

    th {
        background-color: #f2f2f2;
        font-size: 9px;
        text-transform: uppercase;
    }
    </style>
</head>

<body onload="window.print()">

    <h2>Rekapitulasi Tugas Bulanan Guru</h2>
    <h3><?= htmlspecialchars($nama_user); ?> (Periode: <?= htmlspecialchars($bulan); ?>
        <?= htmlspecialchars($tahun); ?>)</h3>
    <p>SMKN 1 Probolinggo &bull; Dicetak pada: <?= date('d-m-Y H:i'); ?></p>

    <table>
        <thead>
            <tr>
                <th>No</th>
                <th>Tanggal</th>
                <th>Jam</th>
                <th>Mata Pelajaran</th>
                <th>Jurusan</th>
                <th>Kelas</th>
                <th>Status</th>
                <th>Lokasi Ruangan</th>
                <th>Instruksi Tugas</th>
                <th>Berkas</th>
            </tr>
        </thead>
        <tbody>
            <?php 
            $no = 1;
            if ($result && mysqli_num_rows($result) > 0) {
                while ($row = mysqli_fetch_assoc($result)) {
            ?>
            <tr>
                <td><?= $no++; ?></td>
                <td><?= date('d/m/Y', strtotime($row['tanggal'])); ?></td>
                <td><?= htmlspecialchars($row['jam_ke'] ?? '-'); ?></td>
                <td><?= htmlspecialchars($row['mata_pelajaran'] ?? '-'); ?></td>
                <td><?= htmlspecialchars($row['jurusan'] ?? '-'); ?></td>
                <td><?= htmlspecialchars($row['kelas_tujuan'] ?? '-'); ?></td>
                <td><?= htmlspecialchars($row['status'] ?? '-'); ?></td>
                <td><?= htmlspecialchars($row['lokasi'] ?? '-'); ?></td>
                <td><?= htmlspecialchars($row['instruksi'] ?? '-'); ?></td>
                <td><?= !empty($row['berkas']) ? 'Ada (Terlampir)' : 'Tidak Ada'; ?></td>
            </tr>
            <?php 
                }
            } else {
            ?>
            <tr>
                <td colspan="10" align="center">Tidak ada data laporan ditemukan untuk periode ini.</td>
            </tr>
            <?php } ?>
        </tbody>
    </table>

</body>

</html>