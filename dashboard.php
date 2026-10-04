<?php
include 'config.php';
cek_login();

$user_id    = $_SESSION['user_id'];
$nama_user  = $_SESSION['nama_lengkap'];
$role       = $_SESSION['role'];

// JIKA ROLE BUKAN ADMIN (YANG LOGIN ADALAH GURU), TAMPILKAN DASHBOARD GURU
if ($role !== 'admin') {
    $q_total = mysqli_query($koneksi, "SELECT COUNT(*) as total FROM tbmasalah WHERE user_id='$user_id'");
    $d_total = mysqli_fetch_assoc($q_total);
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - etugas SMKN 1 Probolinggo</title>
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

    .header-bg {
        background: linear-gradient(135deg, #0F172A 0%, #1E293B 100%);
        color: white;
        padding: 30px 0 90px 0;
    }

    .top-nav {
        max-width: 1100px;
        margin: 0 auto;
        padding: 0 20px;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .logo {
        font-size: 20px;
        font-weight: 700;
    }

    .user-info {
        display: flex;
        align-items: center;
        gap: 12px;
    }

    .role-badge {
        background: #3B82F6;
        color: white;
        padding: 3px 10px;
        border-radius: 12px;
        font-size: 11px;
        font-weight: 700;
        text-transform: uppercase;
    }

    .logout-link {
        color: #EF4444;
        text-decoration: none;
        font-size: 13px;
        font-weight: 600;
        background: rgba(239, 68, 68, 0.1);
        padding: 8px 14px;
        border-radius: 8px;
    }

    .main-container {
        max-width: 1100px;
        margin: -60px auto 40px auto;
        padding: 0 20px;
    }

    .welcome-card {
        background: white;
        border-radius: 20px;
        padding: 30px;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.05);
        margin-bottom: 25px;
    }

    .welcome-text h2 {
        font-size: 24px;
        color: #0F172A;
    }

    .welcome-text p {
        color: #64748B;
        font-size: 14px;
        margin-top: 4px;
    }

    .grid-menu {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
        gap: 20px;
    }

    .card-menu {
        background: white;
        border-radius: 16px;
        padding: 24px;
        text-decoration: none;
        color: inherit;
        border: 1px solid #E2E8F0;
        transition: all 0.3s;
        display: flex;
        flex-direction: column;
        justify-content: space-between;
    }

    .card-menu:hover {
        transform: translateY(-5px);
        box-shadow: 0 15px 30px rgba(0, 0, 0, 0.08);
        border-color: #6366F1;
    }

    .stat-box {
        background: linear-gradient(135deg, #4F46E5 0%, #3730A3 100%);
        color: white;
        padding: 25px;
        border-radius: 16px;
        margin-bottom: 25px;
    }

    .stat-box .num {
        font-size: 36px;
        font-weight: 800;
        margin-top: 10px;
    }
    </style>
</head>

<body>
    <div class="header-bg">
        <div class="top-nav">
            <div class="logo">etugas <span style="color: #818CF8;">SMKN 1 PROBOLINGGO</span></div>
            <div class="user-info">
                <span class="role-badge"><?= $role; ?></span>
                <span style="font-size: 14px; font-weight: 600;"><?= htmlspecialchars($nama_user); ?></span>
                <a href="logout.php" class="logout-link">Keluar</a>
            </div>
        </div>
    </div>
    <div class="main-container">
        <div class="welcome-card">
            <div class="welcome-text">
                <h2>Selamat Datang, <?= htmlspecialchars($nama_user); ?>! 👋</h2>
                <p>Sistem Modul Tugas Digital SMKN 1 Probolinggo.</p>
            </div>
        </div>
        <div class="stat-box">
            <h4>Total Riwayat Tugas (Anda)</h4>
            <div class="num"><?= $d_total['total']; ?> <span
                    style="font-size: 16px; font-weight: normal;">Laporan</span></div>
        </div>
        <div class="grid-menu">
            <a href="index.php" class="card-menu">
                <div>
                    <h3>📝 Tambah Tugas</h3>
                    <p>Isi tugas mengajar harian & upload berkas.</p>
                </div>
            </a>
            <a href="riwayat-guru.php" class="card-menu">
                <div>
                    <h3>📊 Lihat Riwayat</h3>
                    <p>Kelola dan lihat riwayat tugas Anda.</p>
                </div>
            </a>
        </div>
    </div>
</body>

</html>
<?php 
    exit(); 
} 

// ==========================================
// DASHBOARD ADMIN INTERAKTIF (JIKA ROLE == ADMIN)
// ==========================================
$q_total = mysqli_query($koneksi, "SELECT COUNT(*) as total FROM tbmasalah");
$q_hadir = mysqli_query($koneksi, "SELECT COUNT(*) as total FROM tbmasalah WHERE status LIKE '%Hadir%'");
$d_total = mysqli_fetch_assoc($q_total);
$d_hadir = mysqli_fetch_assoc($q_hadir);
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Panel - etugas SMKN 1 Probolinggo</title>
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

    /* SIDEBAR STYLING */
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
        box-shadow: 4px 0 10px rgba(0, 0, 0, 0.05);
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
        letter-spacing: 0.5px;
    }

    .sidebar-menu {
        padding: 20px 12px;
        list-style: none;
        flex: 1;
        overflow-y: auto;
    }

    .menu-category {
        font-size: 10px;
        text-transform: uppercase;
        color: #64748B;
        font-weight: 700;
        padding: 10px 12px 6px 12px;
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
        transition: all 0.2s;
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
        gap: 8px;
        background: rgba(239, 68, 68, 0.1);
        color: #EF4444;
        text-decoration: none;
        padding: 10px;
        border-radius: 8px;
        font-size: 13px;
        font-weight: 600;
        transition: background 0.2s;
    }

    .logout-btn:hover {
        background: rgba(239, 68, 68, 0.2);
    }

    /* MAIN CONTENT */
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
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.01);
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

    .welcome-banner {
        background: linear-gradient(135deg, #2563EB 0%, #1D4ED8 100%);
        color: white;
        padding: 30px;
        border-radius: 16px;
        margin-bottom: 25px;
        box-shadow: 0 10px 20px rgba(37, 99, 235, 0.15);
    }

    .welcome-banner h2 {
        font-size: 22px;
        margin-bottom: 6px;
    }

    .welcome-banner p {
        font-size: 13px;
        opacity: 0.9;
    }

    /* STATS CARDS */
    .stats-grid {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 20px;
        margin-bottom: 30px;
    }

    .stat-card {
        background: white;
        border: 1px solid #E2E8F0;
        padding: 22px;
        border-radius: 16px;
        text-decoration: none;
        color: inherit;
        display: block;
        transition: all 0.25s ease;
        box-shadow: 0 2px 6px rgba(0, 0, 0, 0.02);
    }

    .stat-card:hover {
        transform: translateY(-4px);
        border-color: #3B82F6;
        box-shadow: 0 10px 20px rgba(0, 0, 0, 0.05);
    }

    .stat-card h4 {
        font-size: 13px;
        color: #64748B;
        font-weight: 600;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .stat-card h4 span {
        font-size: 11px;
        background: #EFF6FF;
        color: #2563EB;
        padding: 2px 6px;
        border-radius: 6px;
    }

    .stat-card .num {
        font-size: 30px;
        font-weight: 800;
        color: #0F172A;
        margin-top: 8px;
    }

    /* QUICK MENU */
    .section-title {
        font-size: 16px;
        font-weight: 700;
        color: #0F172A;
        margin-bottom: 15px;
    }

    .quick-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
        gap: 15px;
    }

    .quick-item {
        background: white;
        border: 1px solid #E2E8F0;
        padding: 18px;
        border-radius: 12px;
        text-decoration: none;
        color: #1E293B;
        font-weight: 600;
        font-size: 13px;
        display: flex;
        align-items: center;
        gap: 12px;
        transition: all 0.2s;
    }

    .quick-item:hover {
        background: #F8FAFC;
        border-color: #cbd5e1;
    }

    .quick-item .icon {
        width: 36px;
        height: 36px;
        background: #F1F5F9;
        border-radius: 8px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 16px;
    }
    </style>
</head>

<body>

    <!-- SIDEBAR ADMIN -->
    <div class="sidebar">
        <div>
            <div class="sidebar-header">
                <div class="logo-title">etugas</div>
                <div class="logo-sub">Panel Administrator</div>
            </div>
            <ul class="sidebar-menu">
                <div class="menu-category">Utama</div>
                <li><a href="dashboard.php" class="active">🏠 Beranda Admin</a></li>
                <li><a href="index.php">📝 Tambah Tugas</a></li>
                <li><a href="view-data.php">📊 Kelola Seluruh Data</a></li>
                <li><a href="kehadiran.php">📋 Rekap Kehadiran Guru</a></li>
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

    <!-- KONTEN UTAMA ADMIN -->
    <div class="main-content">
        <div class="topbar">
            <div>
                <h3 style="font-size: 18px; color: #0F172A;">Dashboard Kontrol Utama</h3>
            </div>
            <div style="display: flex; align-items: center; gap: 12px;">
                <span class="role-badge"><?= $role; ?></span>
                <span style="font-size: 14px; font-weight: 600;"><?= htmlspecialchars($nama_user); ?></span>
            </div>
        </div>

        <div class="welcome-banner">
            <h2>Selamat Datang, Administrator! 👋</h2>
            <p>Kelola seluruh laporan penugasan guru, akun guru, dan konfigurasi bot telegram sekolah dari panel ini.
            </p>
        </div>

        <!-- KARTU STATISTIK -->
        <div class="stats-grid">
            <a href="view-data.php" class="stat-card">
                <h4>Total Seluruh Laporan <span>Lihat Semua →</span></h4>
                <div class="num"><?= $d_total['total']; ?> <span
                        style="font-size: 14px; font-weight: normal; color: #64748B;">Laporan</span></div>
            </a>

            <a href="kehadiran.php" class="stat-card">
                <h4>Kehadiran <span>Filter Hadir →</span></h4>
                <div class="num" style="color: #16A34A;"><?= $d_hadir['total']; ?> <span
                        style="font-size: 14px; font-weight: normal; color: #64748B;">Kali</span></div>
            </a>
        </div>

        <!-- FITUR PINTASAN TAMBAHAN -->
        <div class="section-title">Pintasan Cepat Administrator</div>
        <div class="quick-grid">
            <a href="cetak-rekap.php" class="quick-item">
                <div class="icon" style="background: #EEF2FF; color: #4F46E5;">📅</div> Rekapitulasi Bulanan
            </a>
            <a href="manajemen-guru.php" class="quick-item">
                <div class="icon">👥</div> Manajemen Akun Guru
            </a>
            <a href="pengaturan.php" class="quick-item">
                <div class="icon">🤖</div> Telegram
            </a>
            <a href="manajemen-kelas.php" class="quick-item">
                <div class="icon">🏫</div> Data Kelas
            </a>
            <a href="manajemen-keamanan.php" class="quick-item" style="color: #DC2626;">
                <div class="icon" style="background: #FEE2E2; color: #DC2626;">🔒</div> Keamanan & Sistem
            </a>
        </div>
    </div>

</body>

</html>