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

// Ambil data dari database tanpa batasan pagination agar terekap semua
$query = "SELECT * FROM tbmasalah WHERE user_id = '$user_id' AND MONTH(tanggal) = '$angka_bulan' AND YEAR(tanggal) = '$tahun' ORDER BY tanggal DESC";
$result = mysqli_query($koneksi, $query);

// Header HTTP agar file terbaca sebagai Excel (.xls)
header("Content-type: application/vnd-ms-excel");
header("Content-Disposition: attachment; filename=Rekap-Tugas-$bulan-$tahun-$nama_user.xls");
header("Pragma: no-cache");
header("Expires: 0");
?>
<center>
    <h3>REKAPITULASI TUGAS BULANAN</h3>
    <h4>Bulan: <?= htmlspecialchars($bulan); ?> <?= htmlspecialchars($tahun); ?></h4>
    <h4>Nama Guru: <?= htmlspecialchars($nama_user); ?></h4>
</center>
<br>
<table border="1">
    <thead>
        <tr style="background-color: #4F46E5; color: white;">
            <th>No</th>
            <th>Tanggal</th>
            <th>Jam Ke</th>
            <th>Nama Guru</th>
            <th>Mata Pelajaran</th>
            <th>Jurusan</th>
            <th>Kelas Tujuan</th>
            <th>Status Kehadiran</th>
            <th>Lokasi Ruangan</th>
            <th>Instruksi / Catatan Tugas</th>
        </tr>
    </thead>
    <tbody>
        <?php 
        $no = 1;
        if ($result && mysqli_num_rows($result) > 0): 
            while ($row = mysqli_fetch_assoc($result)): 
        ?>
        <tr>
            <td><?= $no++; ?></td>
            <td><?= date('d/m/Y', strtotime($row['tanggal'])); ?></td>
            <td><?= htmlspecialchars($row['jam_ke'] ?? '-'); ?></td>
            <td><?= htmlspecialchars($nama_user); ?></td>
            <td><?= htmlspecialchars($row['mata_pelajaran'] ?? '-'); ?></td>
            <td><?= htmlspecialchars($row['jurusan'] ?? '-'); ?></td>
            <td><?= htmlspecialchars($row['kelas_tujuan'] ?? '-'); ?></td>
            <td><?= htmlspecialchars($row['status'] ?? '-'); ?></td>
            <td><?= htmlspecialchars($row['lokasi'] ?? '-'); ?></td>
            <td><?= htmlspecialchars($row['instruksi'] ?? '-'); ?></td>
        </tr>
        <?php 
            endwhile; 
        else: 
        ?>
        <tr>
            <td colspan="10" align="center">Tidak ada data laporan ditemukan.</td>
        </tr>
        <?php endif; ?>
    </tbody>
</table>