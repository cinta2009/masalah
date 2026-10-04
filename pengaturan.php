<?php
include 'config.php';
cek_login();

$user_id   = $_SESSION['user_id'];
$nama_user = $_SESSION['nama_lengkap'];
$role      = $_SESSION['role'];

if ($role !== 'admin') {
    header("Location: dashboard.php");
    exit;
}

// PROSES SIMPAN PENGATURAN
if (isset($_POST['simpan_pengaturan'])) {
    $_SESSION['app_nama_sekolah']  = trim($_POST['nama_sekolah']);
    $_SESSION['app_alamat']        = trim($_POST['alamat_sekolah']);
    $_SESSION['app_tahun_ajaran']  = trim($_POST['tahun_ajaran']);
    $_SESSION['app_semester']      = $_POST['semester'];
    
    echo "<script>alert('Pengaturan berhasil diperbarui!'); window.location='pengaturan.php';</script>";
    exit;
}

// AMBIL NILAI DARI SESSION
$current_nama         = $_SESSION['app_nama_sekolah'] ?? 'SMKN 1 Probolinggo';
$current_alamat       = $_SESSION['app_alamat'] ?? 'Jl. Mastrip No. 162, Jrebeng Wetan, Kec. Kedopok, Kota Probolinggo, Jawa Timur';
$current_tahun_ajaran = $_SESSION['app_tahun_ajaran'] ?? '2025/2026';
$current_semester     = $_SESSION['app_semester'] ?? 'Ganjil';
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pengaturan Aplikasi - <?= htmlspecialchars($current_nama); ?></title>
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

    .card-form {
        background: white;
        border: 1px solid #E2E8F0;
        border-radius: 16px;
        padding: 25px;
        max-width: 800px;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.01);
    }

    .form-group {
        margin-bottom: 20px;
    }

    .form-group label {
        display: block;
        font-size: 13px;
        font-weight: 600;
        color: #475569;
        margin-bottom: 8px;
    }

    .form-control {
        width: 100%;
        padding: 10px 14px;
        border: 1px solid #CBD5E1;
        background: #FFFFFF;
        color: #1E293B;
        border-radius: 8px;
        font-size: 13px;
        outline: none;
    }

    .form-control:focus {
        border-color: #2563EB;
        box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.1);
    }

    textarea.form-control {
        resize: vertical;
        min-height: 80px;
    }

    .btn-submit {
        background: #2563EB;
        color: white;
        border: none;
        padding: 11px 20px;
        border-radius: 8px;
        font-size: 13px;
        font-weight: 600;
        cursor: pointer;
        transition: background 0.2s;
    }

    .btn-submit:hover {
        background: #1D4ED8;
    }

    .info-teks {
        font-size: 11px;
        color: #64748B;
        margin-top: 4px;
    }

    .section-divider {
        font-size: 14px;
        font-weight: 700;
        color: #2563EB;
        border-bottom: 1px solid #E2E8F0;
        padding-bottom: 8px;
        margin: 25px 0 15px 0;
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
                <li><a href="index.php">📝 Tambah Tugas</a></li> <!-- Tambahan Menu Input Tugas -->
                <li><a href="view-data.php">📊 Kelola Seluruh Data</a></li>
                <li><a href="kehadiran.php">📋 Rekap Kehadiran Guru</a></li>
                <li><a href="cetak-rekap.php">📅 Rekapitulasi Bulanan</a></li>

                <div class="menu-category" style="margin-top: 15px;">Manajemen Sistem</div>
                <li><a href="manajemen-guru.php">👥 Manajemen Akun Guru</a></li>
                <li><a href="manajemen-telegram.php">🤖 Telegram</a></li>
                <li><a href="manajemen-kelas.php">🏫 Data Kelas & Mapel</a></li>
                <li><a href="pengaturan.php" class="active">⚙️ Pengaturan Umum</a></li>
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
                <h3 style="font-size: 18px;">Pengaturan Aplikasi</h3>
            </div>
            <div style="display: flex; align-items: center; gap: 12px;">
                <span class="role-badge"><?= $role; ?></span>
                <span style="font-size: 14px; font-weight: 600;"><?= htmlspecialchars($nama_user); ?></span>
            </div>
        </div>

        <div class="card-form">
            <form action="" method="POST">

                <!-- IDENTITAS INSTITUSI -->
                <div class="section-divider" style="margin-top: 0;">🏫 Identitas Institusi</div>
                <div class="form-group">
                    <label>Nama Institusi / Sekolah</label>
                    <input type="text" name="nama_sekolah" class="form-control"
                        value="<?= htmlspecialchars($current_nama); ?>" required>
                </div>

                <div class="form-group">
                    <label>Alamat Lengkap</label>
                    <textarea name="alamat_sekolah" class="form-control"
                        required><?= htmlspecialchars($current_alamat); ?></textarea>
                </div>

                <!-- PERIODE AKADEMIK -->
                <div class="section-divider">📅 Periode Akademik</div>
                <div class="form-group">
                    <label>Tahun Ajaran Aktif</label>
                    <input type="text" name="tahun_ajaran" class="form-control"
                        value="<?= htmlspecialchars($current_tahun_ajaran); ?>" placeholder="Contoh: 2025/2026"
                        required>
                    <div class="info-teks">Digunakan untuk pengelompokan data tugas siswa/kelas.</div>
                </div>

                <div class="form-group">
                    <label>Semester Aktif</label>
                    <select name="semester" class="form-control">
                        <option value="Ganjil" <?= ($current_semester == 'Ganjil') ? 'selected' : ''; ?>>Ganjil</option>
                        <option value="Genap" <?= ($current_semester == 'Genap') ? 'selected' : ''; ?>>Genap</option>
                    </select>
                </div>

                <button type="submit" name="simpan_pengaturan" class="btn-submit" style="margin-top: 15px;">Simpan
                    Perubahan</button>
            </form>
        </div>
    </div>

</body>

</html>