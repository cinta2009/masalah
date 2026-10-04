<?php
include 'config.php';
cek_login();

$role = $_SESSION['role'] ?? 'guru';
$user_id = $_SESSION['user_id'] ?? 0;

// Filter Tahun (Default ke tahun berjalan jika tidak dipilih)
$tahun_pilihan = isset($_GET['tahun']) ? intval($_GET['tahun']) : date('Y');

// Array Nama Bulan
$daftar_bulan = [
    1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
    5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
    9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
];

// Hitung rekap bulanan dari database
$rekap_data = [];
for ($m = 1; $m <= 12; $m++) {
    $bulan_pad = str_pad($m, 2, '0', STR_PAD_LEFT);
    
    if ($role === 'admin') {
        // Admin: Hitung total jurnal semua guru
        $sql = "SELECT COUNT(*) as total FROM tbmasalah WHERE DATE_FORMAT(tanggal, '%Y-%m') = '$tahun_pilihan-$bulan_pad'";
    } else {
        // Guru: Hanya hitung jurnal milik guru itu sendiri
        $sql = "SELECT COUNT(*) as total FROM tbmasalah WHERE user_id = '$user_id' AND DATE_FORMAT(tanggal, '%Y-%m') = '$tahun_pilihan-$bulan_pad'";
    }
    
    $query = mysqli_query($koneksi, $sql);
    $res = mysqli_fetch_assoc($query);
    $rekap_data[$m] = $res['total'] ?? 0;
}
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>Rekapitulasi tugas - etugas</title>
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
        background-color: #F8FAFC;
        color: #1E293B;
        padding: 40px 20px;
    }

    .container {
        max-width: 1100px;
        margin: 0 auto;
    }

    .header-bar {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 30px;
    }

    .header-title h1 {
        font-size: 24px;
        font-weight: 700;
        color: #0F172A;
    }

    .header-title p {
        font-size: 14px;
        color: #64748B;
        margin-top: 4px;
    }

    .btn-dashboard {
        background: #64748B;
        color: white;
        text-decoration: none;
        padding: 10px 18px;
        border-radius: 10px;
        font-weight: 600;
        font-size: 14px;
    }

    .filter-section {
        margin-bottom: 25px;
        display: flex;
        align-items: center;
        gap: 10px;
        background: white;
        padding: 15px 20px;
        border-radius: 12px;
        border: 1px solid #E2E8F0;
        width: fit-content;
    }

    .filter-section label {
        font-weight: 600;
        font-size: 14px;
    }

    .filter-section select {
        padding: 8px 14px;
        border-radius: 8px;
        border: 1px solid #CBD5E1;
        font-weight: 600;
        cursor: pointer;
    }

    .grid-container {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
        gap: 20px;
    }

    .card-month {
        background: white;
        border: 1px solid #E2E8F0;
        border-radius: 16px;
        padding: 22px;
        text-decoration: none;
        color: inherit;
        transition: all 0.2s ease;
        display: block;
    }

    .card-month:hover {
        transform: translateY(-4px);
        border-color: #6366F1;
        box-shadow: 0 10px 20px rgba(99, 102, 241, 0.1);
    }

    .month-header {
        display: flex;
        align-items: center;
        gap: 8px;
        font-weight: 700;
        font-size: 16px;
        color: #1E293B;
        margin-bottom: 6px;
    }

    .month-sub {
        font-size: 12px;
        color: #64748B;
        margin-bottom: 15px;
    }

    .month-count {
        font-size: 22px;
        font-weight: 800;
        color: #4F46E5;
    }

    .month-count span {
        font-size: 14px;
        font-weight: 600;
        color: #64748B;
    }
    </style>
</head>

<body>

    <div class="container">
        <div class="header-bar">
            <div class="header-title">
                <h1>Rekapitulasi penugasan Tahun <?= $tahun_pilihan; ?></h1>
                <p>Akses Mode: <strong><?= strtoupper($role); ?></strong>
                    <?= $role === 'guru' ? '(Menampilkan data pribadi)' : '(Menampilkan data seluruh guru)'; ?></p>
            </div>
            <a href="dashboard.php" class="btn-dashboard">← Dashboard</a>
        </div>

        <!-- Filter Tahun -->
        <div class="filter-section">
            <form method="GET" action="" id="formTahun">
                <label for="tahun">Pilih Tahun Rekap:</label>
                <select name="tahun" id="tahun" onchange="document.getElementById('formTahun').submit();">
                    <?php 
                $tahun_sekarang = date('Y');
                for ($t = $tahun_sekarang - 3; $t <= $tahun_sekarang + 2; $t++) {
                    $selected = ($t == $tahun_pilihan) ? 'selected' : '';
                    echo "<option value='$t' $selected>$t</option>";
                }
                ?>
                </select>
            </form>
        </div>

        <!-- Grid 12 Bulan -->
        <div class="grid-container">
            <?php foreach ($daftar_bulan as $num => $nama): ?>
            <a href="view-data.php?bulan=<?= $num; ?>&tahun=<?= $tahun_pilihan; ?>" class="card-month">
                <div class="month-header">
                    📅 <?= $nama; ?>
                </div>
                <div class="month-sub">Total Mengajar / Tugas</div>
                <div class="month-count"><?= $rekap_data[$num]; ?> <span>Jurnal</span></div>
            </a>
            <?php endforeach; ?>
        </div>
    </div>

</body>

</html>