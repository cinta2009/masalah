<?php
include 'config.php';
cek_login();

$role = $_SESSION['role'] ?? 'guru';
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tambah Tugas - etugas SMKN 1 Probolinggo</title>
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

    /* SIDEBAR GELAP HANYA UNTUK ADMIN */
    /* .sidebar {
        width: 260px;
        background: #111827;
        display: flex;
        flex-direction: column;
        position: fixed;
        height: 100vh;
        z-index: 10;
        border-right: 1px solid #1F2937;
    }

    .sidebar-header {
        padding: 24px 20px;
        border-bottom: 1px solid #1F2937;
    }

    .sidebar-header h1 {
        font-size: 20px;
        font-weight: 700;
        color: #FFFFFF;
        letter-spacing: -0.5px;
    }

    .sidebar-header span {
        font-size: 11px;
        font-weight: 600;
        color: #38BDF8;
        letter-spacing: 0.5px;
        text-transform: uppercase;
    }

    .sidebar-menu {
        list-style: none;
        padding: 20px 15px;
        overflow-y: auto;
        flex-grow: 1;
    }

    .menu-category {
        font-size: 10px;
        font-weight: 700;
        color: #6B7280;
        text-transform: uppercase;
        letter-spacing: 0.8px;
        margin-bottom: 10px;
        margin-top: 15px;
        padding-left: 10px;
    }

    .menu-category:first-child {
        margin-top: 0;
    }

    .sidebar-menu li {
        margin-bottom: 4px;
    }

    .sidebar-menu a {
        display: flex;
        align-items: center;
        gap: 12px;
        padding: 10px 12px;
        color: #9CA3AF;
        text-decoration: none;
        font-size: 13px;
        font-weight: 500;
        border-radius: 8px;
        transition: all 0.2s;
    }

    .sidebar-menu a:hover,
    .sidebar-menu a.active {
        background: #1F2937;
        color: #FFFFFF;
    }

    .sidebar-footer {
        padding: 15px;
        border-top: 1px solid #1F2937;
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

    .btn-logout {
        display: block;
        width: 100%;
        background: #1F2937;
        color: #EF4444;
        text-align: center;
        padding: 11px;
        border-radius: 8px;
        text-decoration: none;
        font-size: 13px;
        font-weight: 600;
        transition: background 0.2s;
    }

    .btn-logout:hover {
        background: #374151;
    }

    /* MAIN CONTENT DINAMIS (Menyesuaikan jika ada sidebar atau tidak) */
    .main-content {
        margin-left: <?=($role==='admin') ? '260px': '0';
        ?>;
        flex-grow: 1;
        padding: 40px;
        display: flex;
        justify-content: center;
        align-items: flex-start;
        width: 100%;
    }

    .form-container {
        width: 100%;
        max-width: 650px;
        background: white;
        padding: 35px;
        border-radius: 16px;
        border: 1px solid #E2E8F0;
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);
    }

    .back-link {
        display: inline-block;
        color: #4F46E5;
        text-decoration: none;
        font-size: 13px;
        font-weight: 500;
        margin-bottom: 15px;
    }

    .back-link:hover {
        text-decoration: underline;
    }

    h2 {
        font-size: 22px;
        color: #0F172A;
        margin-bottom: 25px;
    }

    .form-group {
        margin-bottom: 18px;
    }

    .form-group label {
        display: block;
        font-size: 12px;
        font-weight: 600;
        color: #64748B;
        margin-bottom: 6px;
    }

    .form-group input,
    .form-group select,
    .form-group textarea {
        width: 100%;
        padding: 11px 14px;
        border: 1px solid #CBD5E1;
        border-radius: 8px;
        font-size: 13px;
        outline: none;
        background: #F8FAFC;
        color: #1E293B;
    }

    .form-group input:focus,
    .form-group select:focus,
    .form-group textarea:focus {
        border-color: #4F46E5;
        background: #fff;
    }

    .form-row {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 15px;
    }

    .btn-submit {
        width: 100%;
        background: #4F46E5;
        color: white;
        padding: 12px;
        border: none;
        border-radius: 8px;
        font-weight: 600;
        font-size: 14px;
        cursor: pointer;
        margin-top: 10px;
        transition: background 0.2s;
    }

    .btn-submit:hover {
        background: #4338CA;
    }
    </style>
</head>

<body>

    <?php if ($role === 'admin'): ?>
    <!-- SIDEBAR GELAP (Hanya Tampil untuk Admin) -->
    <div class="sidebar">
        <div class="sidebar-header">
            <div class="logo-title">etugas</div>
            <div class="logo-sub">Panel Administrator</div>
        </div>
        <ul class="sidebar-menu">
            <div class="menu-category">Utama</div>
            <li><a href="dashboard.php">🏠 Beranda Admin</a></li>
            <li><a href="index.php" class="active">📝 Tambah Tugas</a></li> <!-- Tambahan Menu Input Tugas -->
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
        <div class="sidebar-footer">
            <a href="logout.php" class="btn-logout">Keluar Sistem</a>
        </div>
    </div>
    <?php endif; ?>

    <!-- MAIN CONTENT -->
    <div class="main-content">
        <div class="form-container">
            <a href="dashboard.php" class="back-link">&larr; Kembali ke Dashboard</a>
            <h2>Kirim Tugas ke Kelas</h2>

            <form action="prosesinput.php" method="POST" enctype="multipart/form-data">
                <div class="form-group">
                    <label>Nama Guru</label>
                    <input type="text" name="nama_guru"
                        value="<?= htmlspecialchars($_SESSION['nama_lengkap'] ?? ''); ?>" readonly required>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label>Mata Pelajaran</label>
                        <input type="text" name="mapel" placeholder="Contoh: Pemrograman Web" required>
                    </div>
                    <div class="form-group">
                        <label>Tanggal Mengajar</label>
                        <input type="date" name="tanggal" value="<?= date('Y-m-d'); ?>" required>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label>Jurusan</label>
                        <select name="jurusan" required>
                            <option value="RPL">RPL (Rekayasa Perangkat Lunak)</option>
                            <option value="MP">MP (Manajemen Perkantoran)</option>
                            <option value="BD">BD (Bisnis Digital)</option>
                            <option value="AK">AK (Akuntansi)</option>
                            <option value="LP">LP (Layanan Perbankan)</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Kelas Tujuan</label>
                        <select name="kelas_tujuan" required>
                            <?php
                            $tingkat = ['X', 'XI', 'XII'];
                            $jurusan_data = ['RPL' => 2, 'MP' => 4, 'BD' => 4, 'AK' => 3, 'LP' => 2];
                            foreach ($tingkat as $t) {
                                foreach ($jurusan_data as $j => $jumlah) {
                                    for ($i = 1; $i <= $jumlah; $i++) { 
                                        echo "<option value='$t $j $i'>$t $j $i</option>";
                                    }
                                }
                            }
                            ?>
                        </select>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label>Jam Ke-</label>
                        <input type="text" name="jam_ke" placeholder="Contoh: Jam 1 - 4" required>
                    </div>
                    <div class="form-group">
                        <label>Status Kehadiran Guru</label>
                        <select name="status">
                            <option value="Hadir (Di Kelas)">Hadir (Di Kelas)</option>
                            <option value="Hadir (Pindah Ruangan)">Hadir (Pindah Ruangan)</option>
                            <option value="Absen (Ada Tugas)">Absen (Ada Tugas)</option>
                        </select>
                    </div>
                </div>

                <div class="form-group">
                    <label>Lokasi Ruangan / Laboratorium</label>
                    <input type="text" name="lokasi" placeholder="Contoh: Lab Komputer RPL 1" required>
                </div>

                <div class="form-group">
                    <label>Instruksi / Catatan Tugas</label>
                    <textarea name="instruksi" rows="4" placeholder="Tuliskan instruksi tugas secara rinci di sini..."
                        required></textarea>
                </div>

                <div class="form-group">
                    <label>Upload Berkas Tugas (Foto / PDF) - <i>Opsional</i></label>
                    <input type="file" name="file_tugas" accept=".pdf, .png, .jpg, .jpeg">
                </div>

                <button type="submit" class="btn-submit">Kirim Tugas</button>
            </form>
        </div>
    </div>

</body>

</html>