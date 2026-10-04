<?php
include 'config.php';
cek_login();

$user_id   = $_SESSION['user_id'];
$nama_user = $_SESSION['nama_lengkap'];
$role      = $_SESSION['role'];

// Hitung statistik keseluruhan
$q_hadir = mysqli_query($koneksi, "SELECT COUNT(*) as total FROM tbmasalah WHERE status LIKE '%Hadir%'");
$d_hadir = mysqli_fetch_assoc($q_hadir)['total'];

$q_izin = mysqli_query($koneksi, "SELECT COUNT(*) as total FROM tbmasalah WHERE status LIKE '%Izin%'");
$d_izin = mysqli_fetch_assoc($q_izin)['total'];

$q_sakit = mysqli_query($koneksi, "SELECT COUNT(*) as total FROM tbmasalah WHERE status LIKE '%Sakit%'");
$d_sakit = mysqli_fetch_assoc($q_sakit)['total'];

// --- KONFIGURASI LIMIT DATA & PAGINATION ---
$opsi_limit = [10, 20, 30, 50, 100];
$batas = isset($_GET['limit']) ? (int)$_GET['limit'] : 10;
if (!in_array($batas, $opsi_limit)) {
    $batas = 10; // Fallback jika parameter limit diubah manual di URL
}

$halaman = isset($_GET['halaman']) ? (int)$_GET['halaman'] : 1;
$halaman = ($halaman > 0) ? $halaman : 1;
$halaman_awal = ($halaman > 1) ? ($halaman * $batas) - $batas : 0;

// Tangkap keyword pencarian jika ada
$cari = isset($_GET['cari']) ? mysqli_real_escape_string($koneksi, $_GET['cari']) : '';

// Filter pencarian SQL
$where_sql = "";
if (!empty($cari)) {
    $where_sql = " WHERE (tbmasalah.status LIKE '%$cari%' OR users.nama_lengkap LIKE '%$cari%' OR tbmasalah.tanggal LIKE '%$cari%') ";
}

// Hitung total data untuk pagination
$sql_total = "SELECT COUNT(*) as total FROM tbmasalah LEFT JOIN users ON tbmasalah.user_id = users.id $where_sql";
$q_total = mysqli_query($koneksi, $sql_total);
$d_total = mysqli_fetch_assoc($q_total)['total'];
$total_halaman = ceil($d_total / $batas);

// Query data rekap dengan limit, pencarian, dan pagination
$query = mysqli_query($koneksi, "
    SELECT tbmasalah.*, 
           users.nama_lengkap as nama_guru_tabel 
    FROM tbmasalah 
    LEFT JOIN users ON tbmasalah.user_id = users.id 
    $where_sql
    ORDER BY tbmasalah.id DESC 
    LIMIT $halaman_awal, $batas
");
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rekap Kehadiran Guru - etugas SMKN 1 Probolinggo</title>
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
        display: flex;
        min-height: 100vh;
    }

    /* .sidebar {
        width: 260px;
        background: #0F172A;
        color: white;
        display: flex;
        flex-direction: column;
        justify-content: space-between;
        position: fixed;
        top: 0;
        bottom: 0;
        left: 0;
        z-index: 100;
    }

    .sidebar-header {
        padding: 24px 20px;
        border-bottom: 1px solid #1E293B;
    }

    .logo-title {
        font-size: 18px;
        font-weight: 700;
        color: white;
    }

    .logo-sub {
        font-size: 11px;
        color: #38BDF8;
        margin-top: 2px;
        text-transform: uppercase;
    }

    .sidebar-menu {
        padding: 20px 12px;
        list-style: none;
        flex: 1;
        overflow-y: auto;
    }

    .menu-category {
        font-size: 11px;
        font-weight: 700;
        color: #64748B;
        text-transform: uppercase;
        padding: 10px 14px 6px 14px;
        letter-spacing: 0.5px;
    }

    .sidebar-menu li {
        margin-bottom: 4px;
    }

    .sidebar-menu a {
        display: flex;
        align-items: center;
        gap: 12px;
        padding: 11px 14px;
        color: #94A3B8;
        text-decoration: none;
        font-size: 13px;
        font-weight: 600;
        border-radius: 8px;
    }

    .sidebar-menu a:hover,
    .sidebar-menu a.active {
        background: #1E293B;
        color: white;
    }

    .sidebar-footer {
        padding: 20px;
        border-top: 1px solid #1E293B;
    } */

    .sidebar {
        width: 260px;
        background: #0F172A;
        color: white;
        display: flex;
        flex-direction: column;
        justify-content: space-between;
        position: fixed;
        top: 0;
        bottom: 0;
        left: 0;
        z-index: 100;
    }

    .sidebar-header {
        padding: 24px 20px;
        border-bottom: 1px solid #1E293B;
    }

    .logo-title {
        font-size: 18px;
        font-weight: 700;
        color: white;
    }

    .logo-sub {
        font-size: 11px;
        color: #38BDF8;
        margin-top: 2px;
        text-transform: uppercase;
    }

    .sidebar-menu {
        padding: 20px 12px;
        list-style: none;
        flex: 1;
    }

    .sidebar-menu li {
        margin-bottom: 4px;
    }

    .sidebar-menu a {
        display: flex;
        align-items: center;
        gap: 12px;
        padding: 11px 14px;
        color: #94A3B8;
        text-decoration: none;
        font-size: 13px;
        font-weight: 600;
        border-radius: 8px;
    }

    .sidebar-menu a:hover,
    .sidebar-menu a.active {
        background: #1E293B;
        color: white;
    }

    .sidebar-footer {
        padding: 20px;
        border-top: 1px solid #1E293B;
    }

    .logout-btn {
        display: flex;
        align-items: center;
        justify-content: center;
        background: rgba(239, 68, 68, 0.1);
        color: #EF4444;
        text-decoration: none;
        padding: 10px;
        border-radius: 8px;
        font-size: 13px;
        font-weight: 600;
    }

    .main-content {
        margin-left: 260px;
        flex: 1;
        padding: 30px;
    }

    .topbar {
        display: flex;
        justify-content: space-between;
        align-items: center;
        background: white;
        padding: 18px 25px;
        border-radius: 16px;
        border: 1px solid #E2E8F0;
        margin-bottom: 25px;
    }

    .role-badge {
        background: #2563EB;
        color: white;
        padding: 3px 10px;
        border-radius: 12px;
        font-size: 11px;
        font-weight: 700;
        text-transform: uppercase;
    }

    /* Statistik Cards */
    .stats-container {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 20px;
        margin-bottom: 25px;
    }

    .stat-card {
        background: white;
        padding: 20px;
        border-radius: 16px;
        border: 1px solid #E2E8F0;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.01);
    }

    .stat-title {
        font-size: 12px;
        font-weight: 600;
        color: #64748B;
        text-transform: uppercase;
        margin-bottom: 6px;
    }

    .stat-value {
        font-size: 24px;
        font-weight: 700;
        color: #0F172A;
    }

    .card-table {
        background: white;
        border: 1px solid #E2E8F0;
        border-radius: 16px;
        padding: 25px;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.01);
    }

    .table-header-flex {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 20px;
        gap: 15px;
        flex-wrap: wrap;
    }

    .table-title {
        font-size: 16px;
        font-weight: 700;
        color: #0F172A;
    }

    /* Style Search Box & Limit */
    .search-box {
        display: flex;
        gap: 8px;
        align-items: center;
        flex-wrap: wrap;
    }

    .search-box select,
    .search-box input {
        padding: 8px 12px;
        border: 1px solid #CBD5E1;
        border-radius: 8px;
        font-size: 13px;
        outline: none;
        background: white;
    }

    .search-box input {
        width: 200px;
    }

    .search-box select:focus,
    .search-box input:focus {
        border-color: #2563EB;
    }

    .search-box button {
        background: #2563EB;
        color: white;
        border: none;
        padding: 8px 14px;
        border-radius: 8px;
        font-size: 13px;
        font-weight: 600;
        cursor: pointer;
    }

    .search-box button:hover {
        background: #1D4ED8;
    }

    table {
        width: 100%;
        border-collapse: collapse;
        text-align: left;
    }

    th {
        background: #F8FAFC;
        color: #475569;
        font-size: 12px;
        font-weight: 700;
        text-transform: uppercase;
        padding: 12px 16px;
        border-bottom: 1px solid #E2E8F0;
    }

    td {
        padding: 14px 16px;
        font-size: 13px;
        color: #1E293B;
        border-bottom: 1px solid #F1F5F9;
    }

    .badge-hadir {
        background: #DCFCE7;
        color: #15803D;
        padding: 5px 12px;
        border-radius: 20px;
        font-weight: 600;
        font-size: 11px;
        display: inline-flex;
        align-items: center;
        gap: 5px;
    }

    .badge-izin {
        background: #FEF9C3;
        color: #A16207;
        padding: 5px 12px;
        border-radius: 20px;
        font-weight: 600;
        font-size: 11px;
        display: inline-flex;
        align-items: center;
        gap: 5px;
    }

    .badge-sakit {
        background: #FEE2E2;
        color: #B91C1C;
        padding: 5px 12px;
        border-radius: 20px;
        font-weight: 600;
        font-size: 11px;
        display: inline-flex;
        align-items: center;
        gap: 5px;
    }

    /* Style Navigasi Pagination (< >) */
    .pagination-container {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-top: 20px;
        padding-top: 15px;
        border-top: 1px solid #F1F5F9;
        font-size: 13px;
        color: #64748B;
        flex-wrap: wrap;
        gap: 15px;
    }

    .pagination {
        display: flex;
        gap: 5px;
        list-style: none;
    }

    .pagination a {
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 6px 12px;
        background: #F8FAFC;
        border: 1px solid #E2E8F0;
        color: #1E293B;
        text-decoration: none;
        border-radius: 6px;
        font-weight: 600;
        font-size: 13px;
    }

    .pagination a:hover,
    .pagination a.active {
        background: #2563EB;
        color: white;
        border-color: #2563EB;
    }
    </style>
</head>

<body>

    <!-- SIDEBAR -->
    <div class="sidebar">
        <div>
            <div class="sidebar-header">
                <div class="logo-title">etugas</div>
                <div class="logo-sub">Panel Administrator</div>
            </div>
            <ul class="sidebar-menu">
                <div class="menu-category">Utama</div>
                <li><a href="dashboard.php">🏠 Beranda Admin</a></li>
                <li><a href="index.php">📝 Tambah Tugas</a></li>
                <li><a href="view-data.php">📊 Kelola Seluruh Data</a></li>
                <li><a href="kehadiran.php" class="active">📋 Rekap Kehadiran Guru</a></li>
                <li><a href="cetak-rekap.php">📅 Rekapitulasi Bulanan</a></li>

                <div class="menu-category" style="margin-top: 15px;">Manajemen Sistem</div>
                <li><a href="manajemen-guru.php">👥 Manajemen Akun Guru</a></li>
                <li><a href="manajemen-telegram.php">🤖 Telegram</a></li>
                <li><a href="manajemen-kelas.php">🏫 Data Kelas & Mapel</a></li>
                <li><a href="pengaturan.php">⚙️ Pengaturan Umum</a></li>
                <li><a href="manajemen-keamanan.php">🔒 Keamanan & Sistem</a></li>
            </ul>
        </div>
        <div class="sidebar-footer">
            <a href="logout.php" class="logout-btn">Keluar Sistem</a>
        </div>
    </div>

    <!-- MAIN CONTENT -->
    <div class="main-content">
        <div class="topbar">
            <div>
                <h3 style="font-size: 18px;">Monitoring Kehadiran Guru</h3>
            </div>
            <div style="display: flex; align-items: center; gap: 12px;">
                <span class="role-badge"><?= $role; ?></span>
                <span style="font-size: 14px; font-weight: 600;"><?= htmlspecialchars($nama_user); ?></span>
            </div>
        </div>

        <!-- STATISTIK RINGKAS -->
        <div class="stats-container">
            <div class="stat-card">
                <div class="stat-title">Total Hadir</div>
                <div class="stat-value" style="color: #15803D;"><?= $d_hadir; ?></div>
            </div>
            <div class="stat-card">
                <div class="stat-title">Total Izin</div>
                <div class="stat-value" style="color: #A16207;"><?= $d_izin; ?></div>
            </div>
            <div class="stat-card">
                <div class="stat-title">Total Sakit</div>
                <div class="stat-value" style="color: #B91C1C;"><?= $d_sakit; ?></div>
            </div>
        </div>

        <!-- TABEL LOG KEHADIRAN GURU -->
        <div class="card-table">
            <div class="table-header-flex">
                <div class="table-title">Log Kehadiran & Status Pengajar</div>
                <!-- Form Pencarian & Limit Tampil -->
                <form method="GET" action="" class="search-box">
                    <select name="limit" onchange="this.form.submit()">
                        <?php foreach($opsi_limit as $opt): ?>
                        <option value="<?= $opt; ?>" <?= ($batas == $opt) ? 'selected' : ''; ?>><?= $opt; ?> Data
                        </option>
                        <?php endforeach; ?>
                    </select>
                    <input type="text" name="cari" placeholder="Cari nama, status, tanggal..."
                        value="<?= htmlspecialchars($cari); ?>">
                    <button type="submit">Cari</button>
                    <?php if(!empty($cari)): ?>
                    <a href="kehadiran.php"
                        style="background:#64748B; color:white; padding: 8px 10px; border-radius:8px; text-decoration:none; font-size:12px; display:flex; align-items:center;">Reset</a>
                    <?php endif; ?>
                </form>
            </div>

            <table>
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Nama Guru</th>
                        <th>Status Kehadiran</th>
                        <th>Waktu / Tanggal Input</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    $no = $halaman_awal + 1;
                    if (mysqli_num_rows($query) > 0) {
                        while ($row = mysqli_fetch_assoc($query)) {
                            $status = isset($row['status']) ? $row['status'] : 'Hadir';
                            $tampil_nama = !empty($row['nama_guru_tabel']) ? $row['nama_guru_tabel'] : ('User ID: ' . $row['user_id']);
                            
                            $tampil_waktu = '-';
                            if (!empty($row['tanggal'])) { $tampil_waktu = $row['tanggal']; }
                            elseif (!empty($row['created_at'])) { $tampil_waktu = $row['created_at']; }
                            elseif (!empty($row['waktu'])) { $tampil_waktu = $row['waktu']; }
                    ?>
                    <tr>
                        <td><?= $no++; ?></td>
                        <td><strong><?= htmlspecialchars($tampil_nama); ?></strong></td>
                        <td>
                            <?php if (stripos($status, 'Hadir') !== false): ?>
                            <span class="badge-hadir">✅ <?= htmlspecialchars($status); ?></span>
                            <?php elseif (stripos($status, 'Izin') !== false): ?>
                            <span class="badge-izin">📌 <?= htmlspecialchars($status); ?></span>
                            <?php else: ?>
                            <span class="badge-sakit">🏥 <?= htmlspecialchars($status); ?></span>
                            <?php endif; ?>
                        </td>
                        <td><?= htmlspecialchars($tampil_waktu); ?></td>
                    </tr>
                    <?php 
                        }
                    } else {
                        echo '<tr><td colspan="4" style="text-align: center; color: #64748B;">Data kehadiran tidak ditemukan.</td></tr>';
                    }
                    ?>
                </tbody>
            </table>

            <!-- BAGIAN NAVIGASI HALAMAN (< >) -->
            <div class="pagination-container">
                <div>
                    Menampilkan data <b><?= ($d_total > 0) ? ($halaman_awal + 1) : 0; ?></b> sampai
                    <b><?= min($halaman_awal + $batas, $d_total); ?></b> dari total <b><?= $d_total; ?></b> data
                    (Halaman <b><?= $halaman; ?></b> dari <b><?= ($total_halaman == 0) ? 1 : $total_halaman; ?></b>)
                </div>
                <ul class="pagination">
                    <?php 
                    // Buat parameter tambahan untuk link pagination agar filter pencarian & limit terbawa
                    $query_params = '&limit=' . $batas . (!empty($cari) ? '&cari=' . urlencode($cari) : '');
                    ?>
                    <!-- Tombol Sebelumnya (<) -->
                    <?php if($halaman > 1): ?>
                    <li><a href="?halaman=<?= $halaman - 1; ?><?= $query_params; ?>">&laquo;</a>
                    </li>
                    <?php else: ?>
                    <li><a href="#" style="opacity: 0.5; cursor: not-allowed;">&laquo;</a></li>
                    <?php endif; ?>

                    <!-- Nomor Halaman -->
                    <?php 
                    $start_num = max(1, $halaman - 2);
                    $end_num = min($total_halaman, $halaman + 2);
                    for ($i = $start_num; $i <= $end_num; $i++): 
                    ?>
                    <li><a href="?halaman=<?= $i; ?><?= $query_params; ?>"
                            class="<?= ($halaman == $i) ? 'active' : ''; ?>"><?= $i; ?></a></li>
                    <?php endfor; ?>

                    <!-- Tombol Berikutnya (>) -->
                    <?php if($halaman < $total_halaman): ?>
                    <li><a href="?halaman=<?= $halaman + 1; ?><?= $query_params; ?>">&raquo;</a>
                    </li>
                    <?php else: ?>
                    <li><a href="#" style="opacity: 0.5; cursor: not-allowed;">&raquo;</a></li>
                    <?php endif; ?>
                </ul>
            </div>

        </div>
    </div>

</body>

</html>