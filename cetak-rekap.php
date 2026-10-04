<?php
include 'config.php';
cek_login();

$nama_user = $_SESSION['nama_lengkap'];
$role_user = $_SESSION['role'];

// Ambil filter bulan, tahun, keyword pencarian, dan limit tampil dari input user
$bulan_pilih  = isset($_GET['bulan']) ? $_GET['bulan'] : date('m');
$tahun_pilih  = isset($_GET['tahun']) ? $_GET['tahun'] : date('Y');
$cari_keyword = isset($_GET['cari']) ? trim($_GET['cari']) : '';

// Konfigurasi Limit Data per Halaman (default 10)
$batas_data   = isset($_GET['limit']) ? (int)$_GET['limit'] : 10;
$opsi_limit   = [10, 20, 30, 50, 100];
if (!in_array($batas_data, $opsi_limit)) {
    $batas_data = 10; 
}

// Konfigurasi Pagination
$halaman_aktif = isset($_GET['halaman']) ? (int)$_GET['halaman'] : 1;
$halaman_aktif = ($halaman_aktif > 0) ? $halaman_aktif : 1;
$mulai_data    = ($halaman_aktif > 1) ? ($halaman_aktif * $batas_data) - $batas_data : 0;

// Daftar nama bulan untuk pilihan dan tampilan
$nama_bulan = [
    '01' => 'Januari', '02' => 'Februari', '03' => 'Maret', 
    '04' => 'April', '05' => 'Mei', '06' => 'Juni', 
    '07' => 'Juli', '08' => 'Agustus', '09' => 'September', 
    '10' => 'Oktober', '11' => 'November', '12' => 'Desember'
];

// Kondisi dasar query filter bulan & tahun
$where_clause = "WHERE MONTH(tanggal) = '$bulan_pilih' AND YEAR(tanggal) = '$tahun_pilih'";

// Jika ada pencarian, tambahkan kondisi LIKE
if (!empty($cari_keyword)) {
    $keyword_aman = mysqli_real_escape_string($koneksi, $cari_keyword);
    $where_clause .= " AND (nama_guru LIKE '%$keyword_aman%' OR mapel LIKE '%$keyword_aman%' OR kelas_tujuan LIKE '%$keyword_aman%' OR jurusan LIKE '%$keyword_aman%' OR status LIKE '%$keyword_aman%')";
}

// Hitung total data keseluruhan untuk pagination
$query_total = "SELECT COUNT(*) AS total FROM tbmasalah $where_clause";
$result_total = mysqli_query($koneksi, $query_total);
$data_total = mysqli_fetch_assoc($result_total);
$jumlah_data = $data_total['total'];
$jumlah_halaman = ceil($jumlah_data / $batas_data);

// Query data rekap dengan limit pagination dinamis
$query_rekap = "SELECT * FROM tbmasalah $where_clause ORDER BY tanggal DESC, jam_ke ASC LIMIT $mulai_data, $batas_data";
$result_rekap = mysqli_query($koneksi, $query_rekap);
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rekapitulasi Bulanan - etugas SMKN 1 Probolinggo</title>
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
        overflow-x: auto;
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
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.01);
        margin-bottom: 25px;
    }

    .card-title {
        font-size: 15px;
        font-weight: 700;
        color: #0F172A;
        margin-bottom: 18px;
    }

    .filter-form {
        display: flex;
        gap: 15px;
        align-items: flex-end;
        flex-wrap: wrap;
    }

    .form-group {
        flex: 1;
        min-width: 160px;
    }

    .form-group.small-col {
        flex: 0 0 120px;
        min-width: 120px;
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
        background: white;
    }

    .btn-filter {
        background: #2563EB;
        color: white;
        border: none;
        padding: 10px 20px;
        border-radius: 8px;
        font-size: 13px;
        font-weight: 600;
        cursor: pointer;
        height: 41px;
    }

    .btn-filter:hover {
        background: #1D4ED8;
    }

    .table-responsive {
        width: 100%;
        overflow-x: auto;
    }

    .data-table {
        width: 100%;
        border-collapse: collapse;
        margin-top: 15px;
        white-space: nowrap;
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

    /* Badge Status Kehadiran */
    .status-badge {
        display: inline-block;
        padding: 4px 10px;
        border-radius: 6px;
        font-size: 12px;
        font-weight: 600;
    }

    .status-hadir {
        background: #DCFCE7;
        color: #166534;
    }

    /* Tombol Berkas / Lihat */
    .btn-lihat {
        display: inline-flex;
        align-items: center;
        gap: 5px;
        background: #2563EB;
        color: white;
        padding: 5px 12px;
        border-radius: 6px;
        text-decoration: none;
        font-size: 12px;
        font-weight: 600;
    }

    .btn-lihat:hover {
        background: #1D4ED8;
    }

    .text-tidak-ada {
        color: #94A3B8;
        font-style: italic;
        font-size: 12px;
    }

    /* Style Pagination & Info */
    .table-footer {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-top: 20px;
        flex-wrap: wrap;
        gap: 15px;
    }

    .pagination {
        display: flex;
        gap: 5px;
        list-style: none;
    }

    .pagination a,
    .pagination span {
        display: flex;
        align-items: center;
        justify-content: center;
        height: 35px;
        padding: 0 12px;
        border: 1px solid #CBD5E1;
        border-radius: 6px;
        text-decoration: none;
        font-size: 13px;
        font-weight: 600;
        color: #475569;
        background: white;
    }

    .pagination a:hover {
        background: #F1F5F9;
        border-color: #94A3B8;
    }

    .pagination .active span,
    .pagination .active {
        background: #4F46E5;
        color: white;
        border-color: #4F46E5;
    }

    .pagination .disabled span {
        background: #F1F5F9;
        color: #94A3B8;
        border-color: #E2E8F0;
        cursor: not-allowed;
    }

    .data-info {
        font-size: 13px;
        color: #64748B;
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
                <div class="menu-category">Utama
                </div>
                <li><a href="dashboard.php">🏠 Beranda Admin</a></li>
                <li><a href="index.php">📝 Tambah Tugas</a></li> <!-- Tambahan Menu Input Tugas -->
                <li><a href="view-data.php">📊 Kelola Seluruh Data</a></li>
                <li><a href="kehadiran.php">📋 Rekap Kehadiran Guru</a></li>
                <li><a href="cetak-rekap.php" class="active">📅 Rekapitulasi Bulanan</a></li>

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
            <h3 style="font-size: 16px; color: #0F172A;">Rekapitulasi Tugas Bulanan</h3>
            <div style="display: flex; align-items: center; gap: 12px;">
                <span class="role-badge"><?= $role_user; ?></span>
                <span style="font-size: 14px; font-weight: 600;"><?= htmlspecialchars($nama_user); ?></span>
            </div>
        </div>

        <!-- FILTER BULAN, TAHUN, LIMIT & PENCARIAN -->
        <div class="card">
            <div class="card-title">Filter Periode & Pengaturan Tampilan</div>
            <form method="GET" action="" class="filter-form">
                <div class="form-group">
                    <label>Pilih Bulan</label>
                    <select name="bulan" class="form-control">
                        <?php 
                        foreach ($nama_bulan as $num => $namain) {
                            $selected = ($bulan_pilih == $num) ? 'selected' : '';
                            echo "<option value='$num' $selected>$namain</option>";
                        }
                        ?>
                    </select>
                </div>
                <div class="form-group">
                    <label>Pilih Tahun</label>
                    <select name="tahun" class="form-control">
                        <?php 
                        $tahun_sekarang = date('Y');
                        for ($t = $tahun_sekarang; $t >= $tahun_sekarang - 3; $t--) {
                            $selected = ($tahun_pilih == $t) ? 'selected' : '';
                            echo "<option value='$t' $selected>$t</option>";
                        }
                        ?>
                    </select>
                </div>
                <div class="form-group small-col">
                    <label>Tampilkan</label>
                    <select name="limit" class="form-control">
                        <?php foreach($opsi_limit as $opt): ?>
                        <option value="<?= $opt; ?>" <?= ($batas_data == $opt) ? 'selected' : ''; ?>><?= $opt; ?> Data
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label>Pencarian</label>
                    <input type="text" name="cari" value="<?= htmlspecialchars($cari_keyword); ?>"
                        placeholder="Cari guru, kelas, mapel..." class="form-control">
                </div>
                <button type="submit" class="btn-filter">Cari</button>
            </form>

            <!-- TOMBOL CETAK & EXPORT -->
            <div style="margin-top: 15px; display: flex; gap: 10px; border-top: 1px solid #E2E8F0; padding-top: 15px;">
                <a href="cetak-rekap.php?bulan=<?= $bulan_pilih; ?>&tahun=<?= $tahun_pilih; ?>&cari=<?= urlencode($cari_keyword); ?>&limit=<?= $batas_data; ?>&format=excel"
                    style="background: #10B981; color: white; padding: 8px 14px; border-radius: 6px; text-decoration: none; font-size: 12px; font-weight: 600;">📊
                    Excel</a>
                <a href="cetak-rekap.php?bulan=<?= $bulan_pilih; ?>&tahun=<?= $tahun_pilih; ?>&cari=<?= urlencode($cari_keyword); ?>&limit=<?= $batas_data; ?>&format=pdf"
                    target="_blank"
                    style="background: #EF4444; color: white; padding: 8px 14px; border-radius: 6px; text-decoration: none; font-size: 12px; font-weight: 600;">📄
                    PDF</a>
            </div>
        </div>

        <!-- TABEL HASIL REKAP -->
        <div class="card">
            <div class="card-title">
                Daftar Laporan Mengajar
                <?php if(!empty($cari_keyword)): ?>
                <span style="font-size: 13px; font-weight: normal; color: #64748B;">(Pencarian:
                    "<?= htmlspecialchars($cari_keyword); ?>")</span>
                <?php endif; ?>
            </div>

            <div class="table-responsive">
                <table class="data-table">
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
                        if (mysqli_num_rows($result_rekap) > 0) {
                            $no = $mulai_data + 1;
                            while ($row = mysqli_fetch_assoc($result_rekap)) {
                                // Mengambil nama file dari kolom database yang benar: file_tugas
                                $nama_berkas = isset($row['file_tugas']) ? $row['file_tugas'] : '';
                        ?>
                        <tr>
                            <td><?= $no++; ?></td>
                            <td><?= htmlspecialchars($row['nama_guru']); ?></td>
                            <td><?= htmlspecialchars($row['mapel']); ?></td>
                            <td><?= date('Y-m-d', strtotime($row['tanggal'])); ?></td>
                            <td><?= htmlspecialchars(isset($row['jurusan']) ? $row['jurusan'] : '-'); ?></td>
                            <td><?= htmlspecialchars($row['kelas_tujuan']); ?></td>
                            <td><?= htmlspecialchars($row['jam_ke']); ?></td>
                            <td>
                                <span class="status-badge status-hadir">
                                    <?= htmlspecialchars($row['status']); ?>
                                </span>
                            </td>
                            <td><?= htmlspecialchars(isset($row['lokasi']) ? $row['lokasi'] : '-'); ?></td>
                            <td><?= htmlspecialchars(isset($row['instruksi']) ? $row['instruksi'] : '-'); ?></td>
                            <td>
                                <?php if (!empty($nama_berkas)): ?>
                                <a href="uploads/<?= htmlspecialchars($nama_berkas); ?>" target="_blank"
                                    class="btn-lihat">
                                    📁 Lihat
                                </a>
                                <?php else: ?>
                                <span class="text-tidak-ada">Tidak ada</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php 
                            }
                        } else {
                            echo '<tr><td colspan="11" style="text-align: center; color: #64748B; padding: 30px;">Tidak ada data laporan ditemukan pada periode atau kata kunci tersebut.</td></tr>';
                        }
                        ?>
                    </tbody>
                </table>
            </div>

            <!-- BAGIAN NAVIGASI PAGINATION & INFO -->
            <?php if ($jumlah_data > 0): ?>
            <div class="table-footer">
                <div class="data-info">
                    Menampilkan data <b><?= $mulai_data + 1; ?></b> sampai
                    <b><?= min($mulai_data + $batas_data, $jumlah_data); ?></b> dari total <b><?= $jumlah_data; ?></b>
                    data
                </div>

                <ul class="pagination">
                    <!-- Tombol Sebelumnya («) -->
                    <?php if ($halaman_aktif > 1): ?>
                    <li><a
                            href="?bulan=<?= $bulan_pilih; ?>&tahun=<?= $tahun_pilih; ?>&limit=<?= $batas_data; ?>&cari=<?= urlencode($cari_keyword); ?>&halaman=<?= $halaman_aktif - 1; ?>">«
                            Sebelumnya</a></li>
                    <?php else: ?>
                    <li class="disabled"><span>« Sebelumnya</span></li>
                    <?php endif; ?>

                    <!-- Nomor Halaman -->
                    <?php 
                    $jumlah_link = 2; 
                    $start_number = ($halaman_aktif > $jumlah_link) ? $halaman_aktif - $jumlah_link : 1;
                    $end_number = ($halaman_aktif < $jumlah_halaman - $jumlah_link) ? $halaman_aktif + $jumlah_link : $jumlah_halaman;

                    for ($i = $start_number; $i <= $end_number; $i++): 
                    ?>
                    <?php if ($i == $halaman_aktif): ?>
                    <li class="active"><span><?= $i; ?></span></li>
                    <?php else: ?>
                    <li><a
                            href="?bulan=<?= $bulan_pilih; ?>&tahun=<?= $tahun_pilih; ?>&limit=<?= $batas_data; ?>&cari=<?= urlencode($cari_keyword); ?>&halaman=<?= $i; ?>"><?= $i; ?></a>
                    </li>
                    <?php endif; ?>
                    <?php endfor; ?>

                    <!-- Tombol Selanjutnya (>>) -->
                    <?php if ($halaman_aktif < $jumlah_halaman): ?>
                    <li><a
                            href="?bulan=<?= $bulan_pilih; ?>&tahun=<?= $tahun_pilih; ?>&limit=<?= $batas_data; ?>&cari=<?= urlencode($cari_keyword); ?>&halaman=<?= $halaman_aktif + 1; ?>">Selanjutnya
                            »</a></li>
                    <?php else: ?>
                    <li class="disabled"><span>Selanjutnya »</span></li>
                    <?php endif; ?>
                </ul>
            </div>
            <?php endif; ?>

        </div>
    </div>

</body>

</html>