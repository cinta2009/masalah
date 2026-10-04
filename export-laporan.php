<?php
include 'config.php';
cek_login();
if ($_SESSION['role'] != 'admin') { header("Location: dashboard.php"); exit(); }

// Jika tombol export ditekan, alirkan sebagai file excel
if (isset($_GET['download']) && $_GET['download'] == 'excel') {
    header("Content-Type: application/vnd.ms-excel");
    header("Content-Disposition: attachment; filename=Rekap_Laporan_Tugas_SMKN1PROB.xls");
    
    $q_all = mysqli_query($koneksi, "SELECT * FROM tbmasalah ORDER BY id DESC");
    echo "<table border='1'>";
    echo "<tr><th>No</th><th>Tanggal</th><th>Jam Ke</th><th>Nama Guru</th><th>Mapel</th><th>Kelas</th><th>Lokasi</th><th>Status Telegram</th></tr>";
    $no = 1;
    while($r = mysqli_fetch_assoc($q_all)) {
        echo "<tr>";
        echo "<td>" . $no++ . "</td>";
        echo "<td>" . $r['tanggal'] . "</td>";
        echo "<td>" . $r['jam_ke'] . "</td>";
        echo "<td>" . $r['nama_guru'] . "</td>";
        echo "<td>" . $r['mapel'] . "</td>";
        echo "<td>" . $r['kelas_tujuan'] . "</td>";
        echo "<td>" . $r['lokasi'] . "</td>";
        echo "<td>" . $r['status_telegram'] . "</td>";
        echo "</tr>";
    }
    echo "</table>";
    exit();
}
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>Export Laporan - etugas SMKN 1 Probolinggo</title>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap"
        rel="stylesheet">
    <style>
    * {
        box-sizing: border-box;
        font-family: 'Plus Jakarta Sans', sans-serif;
        margin: 0;
        padding: 0;
    }

    body {
        background-color: #F1F5F9;
        color: #1E293B;
    }

    .card {
        background: white;
        border-radius: 16px;
        border: 1px solid #E2E8F0;
        padding: 30px;
        max-width: 600px;
    }

    .btn-export {
        display: inline-block;
        background: #059669;
        color: white;
        padding: 12px 24px;
        border-radius: 10px;
        text-decoration: none;
        font-weight: 700;
        margin-top: 15px;
    }
    </style>
</head>

<body>
    <div class="app-layout">
        <?php include 'sidebar.php'; ?>
        <div class="main-content-wrapper">
            <div class="topbar-header">
                <h3 style="font-size: 16px; font-weight: 700;">Export & Pusat Laporan Sekolah</h3>
            </div>
            <div class="main-body">
                <div class="card">
                    <h3 style="font-size: 18px; font-weight: 700; color: #0F172A; margin-bottom: 10px;">Unduh
                        Rekapitulasi Excel</h3>
                    <p style="font-size: 14px; color: #64748B; line-height: 1.5;">Gunakan fitur ini untuk mendownload
                        seluruh rekam jejak jurnal dan penugasan mengajar guru ke format spreadsheet Microsoft Excel
                        guna keperluan laporan berkala kepada manajemen sekolah.</p>
                    <a href="export-laporan.php?download=excel" class="btn-export">📥 Download Rekap Excel (.xls)</a>
                </div>
            </div>
        </div>
    </div>
</body>

</html>