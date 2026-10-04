<?php
include 'config.php';
cek_login();

// Pastikan hanya admin yang bisa mengakses
if ($_SESSION['role'] !== 'admin') {
    header("Location: dashboard.php");
    exit;
}

$nama_user = $_SESSION['nama_lengkap'];
$role      = $_SESSION['role'];
$pesan     = "";

// ==========================================
// 0. PROSES UPDATE TOKEN BOT TELEGRAM
// ==========================================
if (isset($_POST['update_token_bot'])) {
    $token_baru = mysqli_real_escape_string($koneksi, $_POST['bot_token']);
    if (!empty($token_baru)) {
        $cek = mysqli_query($koneksi, "SELECT * FROM tb_pengaturan_bot WHERE nama_setting = 'bot_token'");
        if (mysqli_num_rows($cek) > 0) {
            $q_up = "UPDATE tb_pengaturan_bot SET nilai_setting = '$token_baru' WHERE nama_setting = 'bot_token'";
        } else {
            $q_up = "INSERT INTO tb_pengaturan_bot (nama_setting, nilai_setting) VALUES ('bot_token', '$token_baru')";
        }
        
        if (mysqli_query($koneksi, $q_up)) {
            header("Location: manajemen-telegram.php?status=sukses_update_token");
            exit;
        } else {
            $pesan = "Gagal memperbarui Token Bot: " . mysqli_error($koneksi);
        }
    } else {
        $pesan = "Token Bot tidak boleh kosong!";
    }
}

$q_token = mysqli_query($koneksi, "SELECT nilai_setting FROM tb_pengaturan_bot WHERE nama_setting = 'bot_token'");
$d_token = mysqli_fetch_assoc($q_token);
$token_aktif = $d_token ? $d_token['nilai_setting'] : '8327213877:AAGGTi6QcRi3N7IUMOU58Pp0UUNtJastybU';


// ==========================================
// 1. PROSES TAMBAH & HAPUS TELEGRAM ADMIN
// ==========================================
if (isset($_POST['tambah_telegram_admin'])) {
    $nama_admin    = mysqli_real_escape_string($koneksi, $_POST['nama_admin']);
    $jabatan       = mysqli_real_escape_string($koneksi, $_POST['jabatan']);
    $chat_id_admin = mysqli_real_escape_string($koneksi, $_POST['chat_id_telegram']);

    if (!empty($nama_admin) && !empty($jabatan) && !empty($chat_id_admin)) {
        // Disesuaikan dengan kolom database: role_jabatan dan chat_id_admin
        $query = "INSERT INTO tb_admin_telegram (nama_admin, role_jabatan, chat_id_admin) VALUES ('$nama_admin', '$jabatan', '$chat_id_admin')";
        if (mysqli_query($koneksi, $query)) {
            header("Location: manajemen-telegram.php?status=sukses_tambah_admin");
            exit;
        } else {
            $pesan = "Gagal menyimpan data Admin Telegram: " . mysqli_error($koneksi);
        }
    } else {
        $pesan = "Semua kolom Telegram Admin wajib diisi!";
    }
}

if (isset($_GET['hapus_admin'])) {
    $id = intval($_GET['hapus_admin']);
    $query_hapus = "DELETE FROM tb_admin_telegram WHERE id = $id";
    if (mysqli_query($koneksi, $query_hapus)) {
        header("Location: manajemen-telegram.php?status=sukses_hapus_admin");
        exit;
    } else {
        $pesan = "Gagal menghapus data Admin Telegram: " . mysqli_error($koneksi);
    }
}


// ==========================================
// 2. PROSES TAMBAH & HAPUS KETUA KELAS
// ==========================================
if (isset($_POST['tambah_telegram'])) {
    $nama_ketua         = mysqli_real_escape_string($koneksi, $_POST['nama_ketua']);
    $kelas             = mysqli_real_escape_string($koneksi, $_POST['kelas']);
    $chat_id_telegram  = mysqli_real_escape_string($koneksi, $_POST['chat_id_telegram']);

    if (!empty($nama_ketua) && !empty($kelas) && !empty($chat_id_telegram)) {
        $query = "INSERT INTO tb_ketua_kelas (nama_ketua, kelas, chat_id_telegram) VALUES ('$nama_ketua', '$kelas', '$chat_id_telegram')";
        if (mysqli_query($koneksi, $query)) {
            header("Location: manajemen-telegram.php?status=sukses_tambah");
            exit;
        } else {
            $pesan = "Gagal menyimpan data: " . mysqli_error($koneksi);
        }
    } else {
        $pesan = "Semua kolom wajib diisi!";
    }
}

if (isset($_GET['hapus'])) {
    $id = intval($_GET['hapus']);
    $query_hapus = "DELETE FROM tb_ketua_kelas WHERE id = $id";
    if (mysqli_query($koneksi, $query_hapus)) {
        header("Location: manajemen-telegram.php?status=sukses_hapus");
        exit;
    } else {
        $pesan = "Gagal menghapus data: " . mysqli_error($koneksi);
    }
}

// PENGATURAN PAGINASI & PENCARIAN KETUA KELAS
$limit = isset($_GET['limit']) ? intval($_GET['limit']) : 10;
$page  = isset($_GET['page']) ? intval($_GET['page']) : 1;
$start = ($page > 1) ? ($page * $limit) - $limit : 0;

$cari = isset($_GET['cari']) ? mysqli_real_escape_string($koneksi, $_GET['cari']) : '';
$where_clause = "";
if (!empty($cari)) {
    $where_clause = "WHERE nama_ketua LIKE '%$cari%' OR kelas LIKE '%$cari%' OR chat_id_telegram LIKE '%$cari%'";
}

$q_total = mysqli_query($koneksi, "SELECT COUNT(*) as total FROM tb_ketua_kelas $where_clause");
$d_total = mysqli_fetch_assoc($q_total);
$total_data = $d_total['total'];
$total_pages = ceil($total_data / $limit);

$result_telegram = mysqli_query($koneksi, "SELECT * FROM tb_ketua_kelas $where_clause ORDER BY id DESC LIMIT $start, $limit");
$result_admin_telegram = mysqli_query($koneksi, "SELECT * FROM tb_admin_telegram ORDER BY id DESC");
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manajemen Telegram - etugas SMKN 1 Probolinggo</title>
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

    .card {
        background: white;
        padding: 25px;
        border-radius: 16px;
        border: 1px solid #E2E8F0;
        margin-bottom: 25px;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.01);
    }

    .card-title {
        font-size: 15px;
        font-weight: 700;
        color: #0F172A;
        margin-bottom: 18px;
    }

    .form-grid {
        display: grid;
        grid-template-columns: 1fr 1fr 1fr;
        gap: 15px;
        margin-bottom: 15px;
    }

    .form-group label {
        display: block;
        font-size: 12px;
        font-weight: 600;
        color: #64748B;
        margin-bottom: 6px;
    }

    .form-control {
        width: 100%;
        padding: 10px 14px;
        border: 1px solid #CBD5E1;
        border-radius: 8px;
        font-size: 13px;
        color: #1E293B;
        outline: none;
    }

    .form-control:focus {
        border-color: #2563EB;
    }

    .btn-primary {
        background: #2563EB;
        color: white;
        border: none;
        padding: 10px 20px;
        border-radius: 8px;
        font-size: 13px;
        font-weight: 600;
        cursor: pointer;
    }

    .btn-primary:hover {
        background: #1D4ED8;
    }

    .alert-error {
        background: #FEE2E2;
        color: #DC2626;
        padding: 10px 15px;
        border-radius: 8px;
        font-size: 13px;
        margin-bottom: 15px;
    }

    .alert-success {
        background: #DCFCE7;
        color: #16A34A;
        padding: 10px 15px;
        border-radius: 8px;
        font-size: 13px;
        margin-bottom: 15px;
    }

    .table-actions {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 18px;
        gap: 10px;
        flex-wrap: wrap;
    }

    .search-box {
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .search-box input {
        padding: 8px 14px;
        border: 1px solid #CBD5E1;
        border-radius: 8px;
        font-size: 13px;
        outline: none;
        width: 240px;
    }

    .data-table {
        width: 100%;
        border-collapse: collapse;
        margin-top: 5px;
    }

    .data-table th,
    .data-table td {
        padding: 12px 15px;
        text-align: left;
        font-size: 13px;
        border-bottom: 1px solid #E2E8F0;
    }

    .data-table th {
        background: #F8FAFC;
        color: #64748B;
        font-weight: 700;
        text-transform: uppercase;
        font-size: 11px;
    }

    .btn-delete {
        background: #FEE2E2;
        color: #DC2626;
        padding: 5px 12px;
        border-radius: 6px;
        font-size: 12px;
        font-weight: 600;
        text-decoration: none;
    }

    .pagination-container {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-top: 20px;
        font-size: 13px;
        color: #64748B;
        flex-wrap: wrap;
        gap: 10px;
    }

    .pagination {
        display: flex;
        list-style: none;
        gap: 4px;
    }

    .pagination a,
    .pagination span {
        padding: 6px 12px;
        border: 1px solid #CBD5E1;
        background: white;
        color: #1E293B;
        border-radius: 6px;
        text-decoration: none;
        font-size: 12px;
        font-weight: 600;
    }

    .pagination .active span {
        background: #2563EB;
        color: white;
        border-color: #2563EB;
    }

    .pagination .disabled span {
        background: #F8FAFC;
        color: #94A3B8;
        border-color: #E2E8F0;
        cursor: not-allowed;
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
                <li><a href="index.php">📝 Tambah Tugas</a></li>
                <li><a href="view-data.php">📊 Kelola Seluruh Data</a></li>
                <li><a href="kehadiran.php">📋 Rekap Kehadiran Guru</a></li>
                <li><a href="cetak-rekap.php">📅 Rekapitulasi Bulanan</a></li>

                <div class="menu-category" style="margin-top: 15px;">Manajemen Sistem</div>
                <li><a href="manajemen-guru.php">👥 Manajemen Akun Guru</a></li>
                <li><a href="manajemen-telegram.php" class="active">🤖 Telegram</a></li>
                <li><a href="manajemen-kelas.php">🏫 Data Kelas & Mapel</a></li>
                <li><a href="pengaturan.php">⚙️ Pengaturan Umum</a></li>
                <li><a href="manajemen-keamanan.php">🔒 Keamanan & Sistem</a></li>
            </ul>
        </div>
        <div class="sidebar-footer">
            <a href="logout.php" class="logout-btn">Keluar Sistem</a>
        </div>
    </div>

    <div class="main-content">
        <div class="topbar">
            <h3 style="font-size: 16px; color: #0F172A;">Manajemen Token & Akses Telegram</h3>
            <div style="display: flex; align-items: center; gap: 12px;">
                <span class="role-badge"><?= $role; ?></span>
                <span style="font-size: 14px; font-weight: 600;"><?= htmlspecialchars($nama_user); ?></span>
            </div>
        </div>

        <?php if (!empty($pesan)): ?>
        <div class="alert-error"><?= $pesan; ?></div>
        <?php endif; ?>

        <?php if (isset($_GET['status']) && $_GET['status'] == 'sukses_update_token'): ?>
        <div class="alert-success">Token Bot Telegram berhasil diperbarui!</div>
        <?php endif; ?>
        <?php if (isset($_GET['status']) && $_GET['status'] == 'sukses_tambah_admin'): ?>
        <div class="alert-success">Akun Telegram Admin berhasil ditambahkan!</div>
        <?php endif; ?>
        <?php if (isset($_GET['status']) && $_GET['status'] == 'sukses_hapus_admin'): ?>
        <div class="alert-success">Akun Telegram Admin berhasil dihapus!</div>
        <?php endif; ?>
        <?php if (isset($_GET['status']) && $_GET['status'] == 'sukses_tambah'): ?>
        <div class="alert-success">Akun Telegram ketua kelas berhasil ditambahkan!</div>
        <?php endif; ?>
        <?php if (isset($_GET['status']) && $_GET['status'] == 'sukses_hapus'): ?>
        <div class="alert-success">Akun Telegram ketua kelas berhasil dihapus!</div>
        <?php endif; ?>

        <!-- 0. PENGATURAN TOKEN BOT TELEGRAM -->
        <div class="card" style="border-left: 4px solid #8B5CF6;">
            <div class="card-title">🔑 Pengaturan Token Telegram (Global)</div>
            <p style="font-size: 13px; color: #64748B; margin-bottom: 15px;">
                Ubah Token Telegram di sini jika Anda membuat Token baru.
            </p>
            <form action="" method="POST">
                <div class="form-group" style="margin-bottom: 15px;">
                    <label>Token Bot Telegram Aktif</label>
                    <input type="text" name="bot_token" class="form-control"
                        value="<?= htmlspecialchars($token_aktif); ?>"
                        placeholder="Contoh: 123456789:ABCdefGhIJKlmNoPQRsTUVwxyZ" required>
                </div>
                <button type="submit" name="update_token_bot" class="btn-primary" style="background: #8B5CF6;">Perbarui
                    Token Bot</button>
            </form>
        </div>

        <!-- 1. MANAJEMEN TELEGRAM ADMIN / KURIKULUM -->
        <div class="card" style="border-left: 4px solid #2563EB;">
            <div class="card-title">🛡️ Tambah Akses Telegram Admin Baru</div>
            <form action="" method="POST">
                <div class="form-grid">
                    <div class="form-group">
                        <label>Nama Admin </label>
                        <input type="text" name="nama_admin" class="form-control"
                            placeholder="Contoh: Budi Santoso, S.Pd" required>
                    </div>
                    <div class="form-group">
                        <label>Jabatan / Peran</label>
                        <input type="text" name="jabatan" class="form-control" placeholder="Contoh: Tim Kurikulum"
                            required>
                    </div>
                    <div class="form-group">
                        <label>Telegram Chat ID Admin</label>
                        <input type="text" name="chat_id_telegram" class="form-control" placeholder="Contoh: 123456789"
                            required>
                    </div>
                </div>
                <button type="submit" name="tambah_telegram_admin" class="btn-primary">Simpan Admin Telegram</button>
            </form>
        </div>

        <div class="card">
            <div class="card-title">Daftar Telegram Admin Terdaftar</div>
            <table class="data-table">
                <thead>
                    <tr>
                        <th style="width: 50px;">No</th>
                        <th>Nama Admin</th>
                        <th>Jabatan</th>
                        <th>Chat ID Telegram</th>
                        <th style="width: 100px; text-align: center;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    $no_adm = 1;
                    if (mysqli_num_rows($result_admin_telegram) > 0) {
                        while ($row_adm = mysqli_fetch_assoc($result_admin_telegram)) {
                    ?>
                    <tr>
                        <td><?= $no_adm++; ?></td>
                        <td><?= htmlspecialchars($row_adm['nama_admin']); ?></td>
                        <td><?= htmlspecialchars($row_adm['role_jabatan']); ?></td>
                        <td><?= htmlspecialchars($row_adm['chat_id_admin']); ?></td>
                        <td style="text-align: center;">
                            <a href="manajemen-telegram.php?hapus_admin=<?= $row_adm['id']; ?>" class="btn-delete"
                                onclick="return confirm('Yakin ingin mencabut akses admin telegram ini?')">Hapus</a>
                        </td>
                    </tr>
                    <?php 
                        }
                    } else {
                        echo '<tr><td colspan="5" style="text-align: center; color: #64748B;">Belum ada data admin telegram terdaftar.</td></tr>';
                    }
                    ?>
                </tbody>
            </table>
        </div>

        <!-- 2. MANAJEMEN TELEGRAM KETUA KELAS -->
        <div class="card" style="margin-top: 30px;">
            <div class="card-title">Tambah Akses Telegram Ketua Kelas</div>
            <form action="" method="POST">
                <div class="form-grid">
                    <div class="form-group">
                        <label>Nama Ketua Kelas</label>
                        <input type="text" name="nama_ketua" class="form-control" placeholder="Contoh: Ahmad Fauzi"
                            required>
                    </div>
                    <div class="form-group">
                        <label>Kelas</label>
                        <input type="text" name="kelas" class="form-control" placeholder="Contoh: XII RPL 1" required>
                    </div>
                    <div class="form-group">
                        <label>Telegram Chat ID</label>
                        <input type="text" name="chat_id_telegram" class="form-control" placeholder="Contoh: 982736152"
                            required>
                    </div>
                </div>
                <button type="submit" name="tambah_telegram" class="btn-primary">Simpan Ketua Kelas</button>
            </form>
        </div>

        <div class="card">
            <div class="card-title">Daftar Telegram Ketua Kelas Terdaftar</div>
            <div class="table-actions">
                <form method="GET" action="" style="display: flex; align-items: center; gap: 10px;">
                    <span style="font-size: 13px; color: #64748B;">Tampilkan</span>
                    <select name="limit" class="form-control" style="width: auto; padding: 6px 10px;"
                        onchange="this.form.submit()">
                        <option value="10" <?= ($limit == 10) ? 'selected' : ''; ?>>10</option>
                        <option value="25" <?= ($limit == 25) ? 'selected' : ''; ?>>25</option>
                        <option value="50" <?= ($limit == 50) ? 'selected' : ''; ?>>50</option>
                        <option value="100" <?= ($limit == 100) ? 'selected' : ''; ?>>100</option>
                    </select>
                    <span style="font-size: 13px; color: #64748B;">data</span>
                    <?php if (!empty($cari)): ?>
                    <input type="hidden" name="cari" value="<?= htmlspecialchars($cari); ?>">
                    <?php endif; ?>
                </form>

                <form method="GET" action="" class="search-box">
                    <?php if ($limit != 10): ?>
                    <input type="hidden" name="limit" value="<?= $limit; ?>">
                    <?php endif; ?>
                    <input type="text" name="cari" placeholder="Cari nama, kelas, atau chat ID..."
                        value="<?= htmlspecialchars($cari); ?>">
                    <button type="submit" class="btn-primary" style="padding: 8px 16px;">Cari</button>
                </form>
            </div>

            <table class="data-table">
                <thead>
                    <tr>
                        <th style="width: 50px;">No</th>
                        <th>Nama Ketua Kelas</th>
                        <th>Kelas</th>
                        <th>Chat ID Telegram</th>
                        <th style="width: 100px; text-align: center;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    $no = $start + 1;
                    if (mysqli_num_rows($result_telegram) > 0) {
                        while ($row = mysqli_fetch_assoc($result_telegram)) {
                    ?>
                    <tr>
                        <td><?= $no++; ?></td>
                        <td><?= htmlspecialchars($row['nama_ketua']); ?></td>
                        <td><?= htmlspecialchars($row['kelas']); ?></td>
                        <td><?= htmlspecialchars($row['chat_id_telegram']); ?></td>
                        <td style="text-align: center;">
                            <a href="manajemen-telegram.php?hapus=<?= $row['id']; ?>" class="btn-delete"
                                onclick="return confirm('Yakin ingin menghapus data ini?')">Hapus</a>
                        </td>
                    </tr>
                    <?php 
                        }
                    } else {
                        echo '<tr><td colspan="5" style="text-align: center; color: #64748B;">Data ketua kelas tidak ditemukan.</td></tr>';
                    }
                    ?>
                </tbody>
            </table>

            <div class="pagination-container">
                <div>
                    <?php 
                    $end_data = min($start + $limit, $total_data);
                    if ($total_data > 0) {
                        echo "Menampilkan data " . ($start + 1) . " sampai " . $end_data . " dari total " . $total_data . " data";
                    } else {
                        echo "Tidak ada data untuk ditampilkan";
                    }
                    ?>
                </div>

                <ul class="pagination">
                    <?php if ($page > 1): ?>
                    <li><a href="?page=1&limit=<?= $limit; ?><?= !empty($cari) ? '&cari='.urlencode($cari) : ''; ?>">«
                            First</a></li>
                    <?php else: ?>
                    <li class="disabled"><span>« First</span></li>
                    <?php endif; ?>

                    <?php if ($page > 1): ?>
                    <li><a
                            href="?page=<?= $page - 1; ?>&limit=<?= $limit; ?><?= !empty($cari) ? '&cari='.urlencode($cari) : ''; ?>">&lt;
                            Prev</a></li>
                    <?php else: ?>
                    <li class="disabled"><span>&lt; Prev</span></li>
                    <?php endif; ?>

                    <li class="active"><span><?= $page; ?></span></li>

                    <?php if ($page < $total_pages && $total_pages > 0): ?>
                    <li><a
                            href="?page=<?= $page + 1; ?>&limit=<?= $limit; ?><?= !empty($cari) ? '&cari='.urlencode($cari) : ''; ?>">Next
                            &gt;</a></li>
                    <?php else: ?>
                    <li class="disabled"><span>Next &gt;</span></li>
                    <?php endif; ?>

                    <?php if ($page < $total_pages && $total_pages > 0): ?>
                    <li><a
                            href="?page=<?= $total_pages; ?>&limit=<?= $limit; ?><?= !empty($cari) ? '&cari='.urlencode($cari) : ''; ?>">Last
                            »</a></li>
                    <?php else: ?>
                    <li class="disabled"><span>Last »</span></li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </div>
</body>

</html>