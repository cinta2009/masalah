<?php
include 'config.php';
cek_login();
if($_SESSION['role'] !== 'admin') { header("Location: dashboard.php"); exit; }
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>Keamanan & Sistem - etugas</title>
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
    }

    .sidebar-header {
        padding: 24px 20px;
        border-bottom: 1px solid #1E293B;
    }

    .sidebar-menu {
        padding: 20px 12px;
        list-style: none;
        flex: 1;
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
        margin-bottom: 4px;
    }

    .sidebar-menu a:hover,
    .sidebar-menu a.active {
        background: #1E293B;
        color: white;
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

    .main-content {
        margin-left: 260px;
        flex: 1;
        padding: 30px;
    }

    .topbar {
        background: white;
        padding: 18px 25px;
        border-radius: 16px;
        border: 1px solid #E2E8F0;
        margin-bottom: 25px;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .card {
        background: white;
        border: 1px solid #E2E8F0;
        border-radius: 16px;
        padding: 24px;
        margin-bottom: 20px;
    }
    </style>
</head>

<body>
    <div class="sidebar">
        <div>
            <div class="sidebar-header">
                <div class="logo-title">etugas</div>
                <div class="logo-sub">Panel Administrator</div>
            </div>
            <ul class="sidebar-menu">
                <div class="menu-category">Utama</div>
                <li><a href="dashboard.php">🏠 Beranda Admin</a></li>
                <li><a href="index.php">📝 Tambah Tugas</a></li> <!-- Tambahan Menu Input Tugas -->
                <li><a href="view-data.php">📊 Kelola Seluruh Data</a></li>
                <li><a href="kehadiran.php">📋 Rekap Kehadiran Guru</a></li>
                <li><a href="cetak-rekap.php">📅 Rekapitulasi Bulanan</a></li>

                <div class="menu-category" style="margin-top: 15px;">Manajemen Sistem</div>
                <li><a href="manajemen-guru.php">👥 Manajemen Akun Guru</a></li>
                <li><a href="manajemen-telegram.php">🤖 Telegram</a></li>
                <li><a href="manajemen-kelas.php">🏫 Data Kelas & Mapel</a></li>
                <li><a href="pengaturan.php">⚙️ Pengaturan Umum</a></li>
                <li><a href="manajemen-keamanan.php" class="active">🔒 Keamanan & Sistem</a></li>
            </ul>
        </div>
        <div style="padding: 20px;"><a href="logout.php"
                style="color: #EF4444; text-decoration: none; font-weight: 600;">🚪 Keluar Sistem</a></div>
    </div>
    <div class="main-content">
        <div class="topbar">
            <h3>Keamanan Web & Informasi Sistem</h3><span
                style="font-size: 13px; font-weight: 600; background: #2563EB; color: #fff; padding: 4px 10px; border-radius: 10px;">ADMIN</span>
        </div>
        <div class="card">
            <h3 style="margin-bottom: 10px;">Status Sistem Keamanan etugas</h3>
            <p style="color: #64748B; font-size: 14px; margin-bottom: 20px;">Informasi umum terkait server lokal,
                enkripsi sandi, dan status perlindungan sesi login administrator.</p>
            <ul style="list-style: none; display: flex; flex-direction: column; gap: 12px; font-size: 14px;">
                <li>🛡️ **Status Enkripsi Sesi:** <span style="color: #16A34A; font-weight: bold;">Aktif & Terproteksi
                        (Session Secure)</span></li>
                <li>⚙️ **Versi PHP Server:** <?= phpversion(); ?></li>
                <li>📂 **Direktori Database:** Lokal MySQL (`localhost / masalah`)</li>
                <li>👤 **Akun Login Aktif:** <?= htmlspecialchars($_SESSION['nama_lengkap']); ?> (Administrator Utama)
                </li>
            </ul>
        </div>
    </div>
</body>

</html>