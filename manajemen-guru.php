<?php
include 'config.php';
cek_login();

// Pastikan hanya admin yang bisa mengakses
if ($_SESSION['role'] !== 'admin') {
    header("Location: dashboard.php");
    exit;
}

$nama_user = $_SESSION['nama_lengkap'];
$role_user = $_SESSION['role'];
$pesan     = "";

// PROSES TAMBAH AKUN GURU / ADMIN
if (isset($_POST['tambah_akun'])) {
    $nama_lengkap = mysqli_real_escape_string($koneksi, $_POST['nama_lengkap']);
    $username     = mysqli_real_escape_string($koneksi, $_POST['username']);
    $password     = mysqli_real_escape_string($koneksi, $_POST['password']); 
    $role         = mysqli_real_escape_string($koneksi, $_POST['role']);

    if (!empty($nama_lengkap) && !empty($username) && !empty($_POST['password'])) {
        // Cek apakah username sudah terdaftar
        $cek_user = mysqli_query($koneksi, "SELECT * FROM users WHERE username = '$username'");
        if (mysqli_num_rows($cek_user) > 0) {
            $pesan = "Username tersebut sudah digunakan oleh akun lain!";
        } else {
            $query = "INSERT INTO users (nama_lengkap, username, password, role) VALUES ('$nama_lengkap', '$username', '$password', '$role')";
            if (mysqli_query($koneksi, $query)) {
                header("Location: manajemen-guru.php?status=sukses_tambah");
                exit;
            } else {
                $pesan = "Gagal menyimpan akun: " . mysqli_error($koneksi);
            }
        }
    } else {
        $pesan = "Semua kolom wajib diisi!";
    }
}

// PROSES HAPUS AKUN
if (isset($_GET['hapus'])) {
    $id = intval($_GET['hapus']);
    
    // Cegah admin menghapus akunnya sendiri yang sedang aktif login
    if ($id == $_SESSION['user_id']) {
        $pesan = "Anda tidak dapat menghapus akun yang sedang aktif digunakan saat ini!";
    } else {
        $query_hapus = "DELETE FROM users WHERE id = $id";
        if (mysqli_query($koneksi, $query_hapus)) {
            header("Location: manajemen-guru.php?status=sukses_hapus");
            exit;
        } else {
            $pesan = "Gagal menghapus akun: " . mysqli_error($koneksi);
        }
    }
}

// PENGATURAN PAGINASI & PENCARIAN
$limit = isset($_GET['limit']) ? intval($_GET['limit']) : 10;
$page  = isset($_GET['page']) ? intval($_GET['page']) : 1;
$start = ($page > 1) ? ($page * $limit) - $limit : 0;

$cari = isset($_GET['cari']) ? mysqli_real_escape_string($koneksi, $_GET['cari']) : '';
$where_clause = "";
if (!empty($cari)) {
    $where_clause = "WHERE nama_lengkap LIKE '%$cari%' OR username LIKE '%$cari%' OR role LIKE '%$cari%'";
}

// HITUNG TOTAL DATA UNTUK PAGINASI
$q_total = mysqli_query($koneksi, "SELECT COUNT(*) as total FROM users $where_clause");
$d_total = mysqli_fetch_assoc($q_total);
$total_data = $d_total['total'];
$total_pages = ceil($total_data / $limit);

// AMBIL DATA DARI DATABASE DENGAN LIMIT & PENCARIAN
$result_users = mysqli_query($koneksi, "SELECT * FROM users $where_clause ORDER BY id DESC LIMIT $start, $limit");
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manajemen Guru - etugas SMKN 1 Probolinggo</title>
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

    .content-grid {
        display: grid;
        grid-template-columns: 380px 1fr;
        gap: 25px;
        align-items: start;
    }

    .card {
        background: white;
        padding: 25px;
        border-radius: 16px;
        border: 1px solid #E2E8F0;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.01);
    }

    .card-title {
        font-size: 15px;
        font-weight: 700;
        color: #0F172A;
        margin-bottom: 18px;
    }

    .form-group {
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
        width: 100%;
        background: #4F46E5;
        color: white;
        border: none;
        padding: 11px;
        border-radius: 8px;
        font-size: 13px;
        font-weight: 600;
        cursor: pointer;
    }

    .btn-primary:hover {
        background: #4338CA;
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

    /* FILTER & SEARCH BAR HEADER */
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
        width: 200px;
    }

    .search-box input:focus {
        border-color: #2563EB;
    }

    .data-table {
        width: 100%;
        border-collapse: collapse;
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

    .btn-delete:hover {
        background: #FDC2C2;
    }

    /* PAGINATION STYLING */
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

    .pagination a:hover {
        background: #F1F5F9;
        border-color: #94A3B8;
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
                <li><a href="manajemen-guru.php" class="active">👥 Manajemen Akun Guru</a></li>
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
            <h3 style="font-size: 16px; color: #0F172A;">Kelola Akun Guru & Admin</h3>
            <div style="display: flex; align-items: center; gap: 12px;">
                <span class="role-badge"><?= $role_user; ?></span>
                <span style="font-size: 14px; font-weight: 600;"><?= htmlspecialchars($nama_user); ?></span>
            </div>
        </div>

        <?php if (!empty($pesan)): ?>
        <div class="alert-error"><?= $pesan; ?></div>
        <?php endif; ?>

        <?php if (isset($_GET['status']) && $_GET['status'] == 'sukses_tambah'): ?>
        <div class="alert-success">Akun berhasil ditambahkan dan langsung bisa digunakan untuk login!</div>
        <?php endif; ?>

        <?php if (isset($_GET['status']) && $_GET['status'] == 'sukses_hapus'): ?>
        <div class="alert-success">Akun berhasil dihapus dari sistem!</div>
        <?php endif; ?>

        <div class="content-grid">
            <!-- FORM TAMBAH AKUN -->
            <div class="card">
                <div class="card-title">Tambah Akun Baru</div>
                <form action="" method="POST">
                    <div class="form-group">
                        <label>Nama Lengkap & Gelar</label>
                        <input type="text" name="nama_lengkap" class="form-control"
                            placeholder="Cth: Budi Santoso, S.Pd" required>
                    </div>
                    <div class="form-group">
                        <label>Username / Email Login</label>
                        <input type="text" name="username" class="form-control" placeholder="Cth: budi@guru.smk.belajar"
                            required>
                    </div>
                    <div class="form-group">
                        <label>Password</label>
                        <input type="text" name="password" class="form-control" placeholder="Password akun" required>
                    </div>
                    <div class="form-group">
                        <label>Hak Akses (Role)</label>
                        <select name="role" class="form-control" required>
                            <option value="guru">Guru Mapel</option>
                            <option value="admin">Administrator</option>
                        </select>
                    </div>
                    <button type="submit" name="tambah_akun" class="btn-primary">Simpan Akun</button>
                </form>
            </div>

            <!-- TABEL DAFTAR AKUN -->
            <div class="card">
                <div class="card-title">Daftar Pengguna Sistem</div>

                <!-- BAGIAN KONTROL PENCARIAN & LIMIT -->
                <div class="table-actions">
                    <form method="GET" action="" style="display: flex; align-items: center; gap: 8px;">
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
                        <input type="text" name="cari" placeholder="Cari nama, username..."
                            value="<?= htmlspecialchars($cari); ?>">
                        <button type="submit" class="btn-primary" style="padding: 8px 14px; width: auto;">Cari</button>
                    </form>
                </div>

                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Nama</th>
                            <th>Username</th>
                            <th>Password</th>
                            <th>Role</th>
                            <th style="text-align: center; width: 90px;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        $no = $start + 1;
                        if (mysqli_num_rows($result_users) > 0) {
                            while ($row = mysqli_fetch_assoc($result_users)) {
                        ?>
                        <tr>
                            <td><?= htmlspecialchars($row['nama_lengkap']); ?></td>
                            <td><?= htmlspecialchars($row['username']); ?></td>
                            <td><code><?= htmlspecialchars($row['password']); ?></code></td>
                            <td><span
                                    style="font-weight: 600; color: <?= ($row['role'] == 'admin') ? '#2563EB' : '#475569'; ?>;"><?= $row['role']; ?></span>
                            </td>
                            <td style="text-align: center;">
                                <a href="manajemen-guru.php?hapus=<?= $row['id']; ?>" class="btn-delete"
                                    onclick="return confirm('Yakin ingin menghapus akun ini?')">Hapus</a>
                            </td>
                        </tr>
                        <?php 
                            }
                        } else {
                            echo '<tr><td colspan="5" style="text-align: center; color: #64748B;">Belum ada data pengguna.</td></tr>';
                        }
                        ?>
                    </tbody>
                </table>

                <!-- BAGIAN NAVIGASI HALAMAN / PAGINATION -->
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
                        <!-- Tombol First -->
                        <?php if ($page > 1): ?>
                        <li><a
                                href="?page=1&limit=<?= $limit; ?><?= !empty($cari) ? '&cari='.urlencode($cari) : ''; ?>">«
                                First</a></li>
                        <?php else: ?>
                        <li class="disabled"><span>« First</span></li>
                        <?php endif; ?>

                        <!-- Tombol Prev (<) -->
                        <?php if ($page > 1): ?>
                        <li><a
                                href="?page=<?= $page - 1; ?>&limit=<?= $limit; ?><?= !empty($cari) ? '&cari='.urlencode($cari) : ''; ?>">&lt;
                                Prev</a></li>
                        <?php else: ?>
                        <li class="disabled"><span>&lt; Prev</span></li>
                        <?php endif; ?>

                        <!-- Nomor Halaman Aktif -->
                        <li class="active"><span><?= $page; ?></span></li>

                        <!-- Tombol Next (>) -->
                        <?php if ($page < $total_pages && $total_pages > 0): ?>
                        <li><a
                                href="?page=<?= $page + 1; ?>&limit=<?= $limit; ?><?= !empty($cari) ? '&cari='.urlencode($cari) : ''; ?>">Next
                                &gt;</a></li>
                        <?php else: ?>
                        <li class="disabled"><span>Next &gt;</span></li>
                        <?php endif; ?>

                        <!-- Tombol Last -->
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
    </div>

</body>

</html>