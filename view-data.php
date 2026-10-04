<?php
include 'config.php';
cek_login();

$user_id   = $_SESSION['user_id'];
$role      = $_SESSION['role'];
$nama_user = $_SESSION['nama_lengkap'];

$search = isset($_GET['search']) ? mysqli_real_escape_string($koneksi, $_GET['search']) : '';
$status_filter = isset($_GET['status']) ? mysqli_real_escape_string($koneksi, $_GET['status']) : '';
$limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 10;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$start = ($page > 1) ? ($page * $limit) - $limit : 0;

$where_clause = "WHERE 1=1";
if ($role !== 'admin') {
    $where_clause .= " AND user_id = '$user_id'";
}
if (!empty($search)) {
    $where_clause .= " AND (nama_guru LIKE '%$search%' OR kelas_tujuan LIKE '%$search%' OR mapel LIKE '%$search%' OR jurusan LIKE '%$search%')";
}
if (!empty($status_filter)) {
    $where_clause .= " AND status LIKE '%$status_filter%'";
}

$q_total = mysqli_query($koneksi, "SELECT COUNT(*) AS total FROM tbmasalah $where_clause");
$d_total = mysqli_fetch_assoc($q_total);
$total_data = $d_total['total'];
$total_pages = ceil($total_data / $limit);

$query = "SELECT * FROM tbmasalah $where_clause ORDER BY id DESC LIMIT $start, $limit";
$result = mysqli_query($koneksi, $query);
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>Kelola Data & Laporan - etugas SMKN 1 Probolinggo</title>
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
        font-size: 10px;
        text-transform: uppercase;
        color: #64748B;
        font-weight: 700;
        padding: 10px 12px 6px 12px;
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
        border: 1px solid #E2E8F0;
        border-radius: 16px;
        padding: 24px;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.01);
    }

    .card-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 20px;
        flex-wrap: wrap;
        gap: 15px;
    }

    .card-header h3 {
        font-size: 18px;
        color: #0F172A;
    }

    .filter-form {
        display: flex;
        gap: 10px;
        align-items: center;
        flex-wrap: wrap;
    }

    .filter-form select,
    .filter-form input {
        padding: 9px 14px;
        border: 1px solid #CBD5E1;
        border-radius: 8px;
        font-size: 13px;
        outline: none;
        background: #FFF;
    }

    .btn-submit {
        background: #2563EB;
        color: white;
        border: none;
        padding: 9px 16px;
        border-radius: 8px;
        font-weight: 600;
        font-size: 13px;
        cursor: pointer;
    }

    .table-responsive {
        width: 100%;
        overflow-x: auto;
    }

    table {
        width: 100%;
        border-collapse: collapse;
        text-align: left;
        font-size: 12px;
    }

    th {
        background: #F8FAFC;
        color: #475569;
        font-weight: 700;
        padding: 12px 10px;
        border-bottom: 2px solid #E2E8F0;
        white-space: nowrap;
    }

    td {
        padding: 12px 10px;
        border-bottom: 1px solid #E2E8F0;
        color: #334155;
    }

    tr:hover {
        background: #F8FAFC;
    }

    .badge-status {
        background: #DCFCE7;
        color: #166534;
        padding: 4px 8px;
        border-radius: 20px;
        font-size: 11px;
        font-weight: 700;
        display: inline-block;
        white-space: nowrap;
    }

    .pagination-container {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-top: 20px;
        padding-top: 15px;
        border-top: 1px solid #E2E8F0;
        flex-wrap: wrap;
        gap: 15px;
    }

    .pagination-info {
        font-size: 13px;
        color: #64748B;
    }

    .pagination {
        display: flex;
        gap: 5px;
        list-style: none;
    }

    .pagination a,
    .pagination span {
        padding: 6px 12px;
        border: 1px solid #CBD5E1;
        border-radius: 6px;
        font-size: 13px;
        text-decoration: none;
        color: #334155;
        font-weight: 600;
    }

    .pagination .active {
        background: #2563EB;
        color: white;
        border-color: #2563EB;
    }

    .pagination .disabled {
        color: #94A3B8;
        pointer-events: none;
        background: #F8FAFC;
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
                <li><a href="view-data.php" class="active">📊 Kelola Seluruh Data</a></li>
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

    <div class="main-content">
        <div class="topbar">
            <div>
                <h3 style="font-size: 18px; color: #0F172A;">Kelola Seluruh Laporan Tugas</h3>
            </div>
            <div style="display: flex; align-items: center; gap: 12px;">
                <span class="role-badge"><?= $role; ?></span>
                <span style="font-size: 14px; font-weight: 600;"><?= htmlspecialchars($nama_user); ?></span>
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <h3>Daftar Laporan Mengajar</h3>
                <div style="display: flex; gap: 10px; align-items: center; flex-wrap: wrap;">
                    <a href="export-excel.php?search=<?= urlencode($search); ?>&status=<?= urlencode($status_filter); ?>"
                        target="_blank"
                        style="background: #10B981; color: white; padding: 9px 14px; border-radius: 8px; font-size: 13px; font-weight: 600; text-decoration: none;">📥
                        Excel</a>
                    <a href="export-pdf.php?search=<?= urlencode($search); ?>&status=<?= urlencode($status_filter); ?>"
                        target="_blank"
                        style="background: #EF4444; color: white; padding: 9px 14px; border-radius: 8px; font-size: 13px; font-weight: 600; text-decoration: none;">📄
                        PDF</a>

                    <form method="GET" class="filter-form" style="margin: 0;">
                        <?php if(!empty($status_filter)): ?><input type="hidden" name="status"
                            value="<?= htmlspecialchars($status_filter); ?>"><?php endif; ?>
                        <select name="limit" onchange="this.form.submit()">
                            <option value="10" <?= ($limit == 10) ? 'selected' : ''; ?>>Tampilkan 10</option>
                            <option value="20" <?= ($limit == 20) ? 'selected' : ''; ?>>Tampilkan 20</option>
                            <option value="30" <?= ($limit == 30) ? 'selected' : ''; ?>>Tampilkan 30</option>
                            <option value="50" <?= ($limit == 50) ? 'selected' : ''; ?>>Tampilkan 50</option>
                        </select>
                        <input type="text" name="search" placeholder="Cari guru, kelas, mapel..."
                            value="<?= htmlspecialchars($search); ?>">
                        <button type="submit" class="btn-submit">Cari</button>
                        <?php if(!empty($search) || !empty($status_filter)): ?><a href="view-data.php"
                            style="font-size: 13px; color: #EF4444; text-decoration: none; font-weight: 600;">Reset</a><?php endif; ?>
                    </form>
                </div>
            </div>

            <div class="table-responsive">
                <table>
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Nama Guru</th>
                            <th>Mata Pelajaran</th>
                            <th>Tanggal</th>
                            <th>Jurusan</th>
                            <th>Kelas Tujuan</th>
                            <th>Jam Ke</th>
                            <th>Status Kehadiran</th>
                            <th>Lokasi Ruangan</th>
                            <th>Instruksi / Catatan</th>
                            <th>Berkas</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        if (mysqli_num_rows($result) > 0) {
                            $no = $start + 1;
                            while ($row = mysqli_fetch_assoc($result)) {
                                // Mengambil nama file sesuai kolom di database Anda: 'file_tugas'
                                $berkas = $row['file_tugas'] ?? $row['berkas'] ?? $row['file'] ?? $row['lampiran'] ?? '';
                        ?>
                        <tr>
                            <td><?= $no++; ?></td>
                            <td><strong><?= htmlspecialchars($row['nama_guru'] ?? '-'); ?></strong></td>
                            <td><?= htmlspecialchars($row['mapel'] ?? '-'); ?></td>
                            <td><?= htmlspecialchars($row['tanggal'] ?? '-'); ?></td>
                            <td><?= htmlspecialchars($row['jurusan'] ?? '-'); ?></td>
                            <td><strong><?= htmlspecialchars($row['kelas_tujuan'] ?? $row['kelas'] ?? '-'); ?></strong>
                            </td>
                            <td><?= htmlspecialchars($row['jam_ke'] ?? '-'); ?></td>
                            <td><span class="badge-status"><?= htmlspecialchars($row['status'] ?? 'Hadir'); ?></span>
                            </td>
                            <td><?= htmlspecialchars($row['lokasi'] ?? $row['ruangan'] ?? '-'); ?></td>
                            <td><?= htmlspecialchars(substr($row['instruksi'] ?? $row['catatan'] ?? '-', 0, 40)) . '...'; ?>
                            </td>
                            <td>
                                <?php if (!empty($berkas)): ?>
                                <a href="uploads/<?= htmlspecialchars($berkas); ?>" target="_blank"
                                    style="background: #2563EB; color: white; padding: 4px 8px; border-radius: 6px; font-size: 11px; text-decoration: none; font-weight: 600; display: inline-block;">📎
                                    Lihat</a>
                                <?php else: ?>
                                <span style="color: #94A3B8; font-style: italic;">Tidak ada</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php 
                            }
                        } else {
                            echo '<tr><td colspan="11" style="text-align: center; padding: 30px; color: #64748B;">Tidak ada data laporan yang ditemukan.</td></tr>';
                        }
                        ?>
                    </tbody>
                </table>
            </div>

            <div class="pagination-container">
                <div class="pagination-info">
                    Menampilkan data <strong><?= ($total_data > 0) ? $start + 1 : 0; ?></strong> sampai
                    <strong><?= min($start + $limit, $total_data); ?></strong> dari total
                    <strong><?= $total_data; ?></strong> data
                </div>
                <ul class="pagination">
                    <?php if($page > 1): ?>
                    <li><a
                            href="?page=1&limit=<?= $limit; ?>&search=<?= urlencode($search); ?>&status=<?= urlencode($status_filter); ?>">&laquo;
                            First</a></li>
                    <li><a
                            href="?page=<?= $page - 1; ?>&limit=<?= $limit; ?>&search=<?= urlencode($search); ?>&status=<?= urlencode($status_filter); ?>">&lt;
                            Prev</a></li>
                    <?php else: ?>
                    <li><span class="disabled">&laquo; First</span></li>
                    <li><span class="disabled">&lt; Prev</span></li>
                    <?php endif; ?>

                    <?php 
                    $start_number = max(1, $page - 2);
                    $end_number = min($total_pages, $page + 2);
                    for($i = $start_number; $i <= $end_number; $i++): 
                    ?>
                    <li><a href="?page=<?= $i; ?>&limit=<?= $limit; ?>&search=<?= urlencode($search); ?>&status=<?= urlencode($status_filter); ?>"
                            class="<?= ($page == $i) ? 'active' : ''; ?>"><?= $i; ?></a></li>
                    <?php endfor; ?>

                    <?php if($page < $total_pages): ?>
                    <li><a
                            href="?page=<?= $page + 1; ?>&limit=<?= $limit; ?>&search=<?= urlencode($search); ?>&status=<?= urlencode($status_filter); ?>">Next
                            &gt;</a></li>
                    <li><a
                            href="?page=<?= $total_pages; ?>&limit=<?= $limit; ?>&search=<?= urlencode($search); ?>&status=<?= urlencode($status_filter); ?>">Last
                            &raquo;</a></li>
                    <?php else: ?>
                    <li><span class="disabled">Next &gt;</span></li>
                    <li><span class="disabled">Last &raquo;</span></li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </div>
</body>

</html>