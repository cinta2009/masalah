<?php
include 'config.php';
cek_login();

$user_id   = $_SESSION['user_id'];
$nama_user = $_SESSION['nama_lengkap'];
$role      = $_SESSION['role'];

$bulan = isset($_GET['bulan']) ? $_GET['bulan'] : 'September';
$tahun = isset($_GET['tahun']) ? $_GET['tahun'] : '2026';

$array_bulan = [
    'Januari' => '01', 'Februari' => '02', 'Maret' => '03', 'April' => '04',
    'Mei' => '05', 'Juni' => '06', 'Juli' => '07', 'Agustus' => '08',
    'September' => '09', 'Oktober' => '10', 'November' => '11', 'Desember' => '12'
];

$angka_bulan = $array_bulan[$bulan] ?? '09';

// Tangkap Parameter Pencarian & Limit Baris
$limit   = isset($_GET['limit']) ? (int)$_GET['limit'] : 10;
$cari    = isset($_GET['cari']) ? mysqli_real_escape_string($koneksi, trim($_GET['cari'])) : '';
$halaman = isset($_GET['halaman']) ? (int)$_GET['halaman'] : 1;
$halaman_awal = ($halaman > 1) ? ($halaman * $limit) - $limit : 0;

// Kondisi Filter Pencarian (menyesuaikan kolom asli: mapel, jurusan, kelas_tujuan, status, lokasi, instruksi)
$where_cari = "";
if (!empty($cari)) {
    $where_cari = " AND (
        mapel LIKE '%$cari%' OR 
        jurusan LIKE '%$cari%' OR 
        kelas_tujuan LIKE '%$cari%' OR 
        status LIKE '%$cari%' OR 
        lokasi LIKE '%$cari%' OR 
        instruksi LIKE '%$cari%'
    )";
}

// Hitung total data sesuai filter bulan, tahun, dan pencarian
$sql_total = "SELECT COUNT(*) as total FROM tbmasalah WHERE user_id = '$user_id' AND MONTH(tanggal) = '$angka_bulan' AND YEAR(tanggal) = '$tahun' $where_cari";
$query_total = mysqli_query($koneksi, $sql_total);
$row_total = mysqli_fetch_assoc($query_total);
$jumlah_data = $row_total['total'];
$total_halaman = ($jumlah_data > 0) ? ceil($jumlah_data / $limit) : 1;

if ($halaman > $total_halaman) {
    $halaman = $total_halaman;
}

// Query Data Utama
$query = "SELECT * FROM tbmasalah WHERE user_id = '$user_id' AND MONTH(tanggal) = '$angka_bulan' AND YEAR(tanggal) = '$tahun' $where_cari ORDER BY tanggal DESC LIMIT $halaman_awal, $limit";
$result = mysqli_query($koneksi, $query);

$data_awal_tampil = ($jumlah_data > 0) ? $halaman_awal + 1 : 0;
$data_akhir_tampil = min($halaman_awal + $limit, $jumlah_data);
$no = $halaman_awal + 1;
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rekapitulasi Tugas Bulanan - etugas SMKN 1 Probolinggo</title>
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
        padding: 30px;
    }

    .container {
        max-width: 1400px;
        margin: 0 auto;
    }

    .top-nav-box {
        display: flex;
        justify-content: space-between;
        align-items: center;
        background: white;
        padding: 16px 24px;
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

    .card-filter {
        background: white;
        border-radius: 16px;
        border: 1px solid #E2E8F0;
        padding: 24px;
        margin-bottom: 25px;
    }

    .filter-title {
        font-size: 14px;
        font-weight: 700;
        color: #0F172A;
        margin-bottom: 15px;
    }

    .filter-grid {
        display: grid;
        grid-template-columns: 1fr 1fr auto;
        gap: 15px;
        align-items: end;
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
        outline: none;
        background: white;
        color: #1E293B;
    }

    .btn-submit {
        background: #4F46E5;
        color: white;
        border: none;
        padding: 10px 20px;
        border-radius: 8px;
        font-size: 13px;
        font-weight: 600;
        cursor: pointer;
        height: 42px;
    }

    .btn-action-group {
        display: flex;
        gap: 10px;
        margin-top: 20px;
    }

    .btn-pdf {
        background: #EF4444;
        color: white;
        padding: 8px 16px;
        border-radius: 8px;
        text-decoration: none;
        font-size: 12px;
        font-weight: 600;
        display: inline-flex;
        align-items: center;
        gap: 6px;
    }

    .btn-excel {
        background: #10B981;
        color: white;
        padding: 8px 16px;
        border-radius: 8px;
        text-decoration: none;
        font-size: 12px;
        font-weight: 600;
        display: inline-flex;
        align-items: center;
        gap: 6px;
    }

    .card-content {
        background: white;
        border-radius: 16px;
        border: 1px solid #E2E8F0;
        padding: 24px;
    }

    .card-header-flex {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 20px;
        flex-wrap: wrap;
        gap: 15px;
    }

    .card-title {
        font-size: 16px;
        font-weight: 700;
        color: #0F172A;
    }

    .table-controls {
        display: flex;
        align-items: center;
        gap: 10px;
        flex-wrap: wrap;
    }

    .select-limit {
        padding: 8px 12px;
        border: 1px solid #CBD5E1;
        border-radius: 8px;
        font-size: 13px;
        background: white;
        color: #1E293B;
        outline: none;
        font-weight: 500;
    }

    .search-input {
        padding: 8px 14px;
        border: 1px solid #CBD5E1;
        border-radius: 8px;
        font-size: 13px;
        outline: none;
        width: 230px;
    }

    .btn-search {
        background: #2563EB;
        color: white;
        border: none;
        padding: 8px 16px;
        border-radius: 8px;
        font-size: 13px;
        font-weight: 600;
        cursor: pointer;
    }

    .btn-search:hover {
        background: #1D4ED8;
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
        white-space: nowrap;
    }

    th,
    td {
        padding: 12px 14px;
        border-bottom: 1px solid #F1F5F9;
    }

    th {
        background: #F8FAFC;
        font-weight: 700;
        color: #475569;
        text-transform: uppercase;
        font-size: 11px;
    }

    tr:hover {
        background: #F8FAFC;
    }

    .empty-state {
        text-align: center;
        padding: 40px;
        color: #94A3B8;
    }

    .back-link {
        font-size: 13px;
        color: #4F46E5;
        text-decoration: none;
        font-weight: 600;
        display: inline-block;
        margin-bottom: 10px;
    }

    .badge-file {
        background: #EEF2FF;
        color: #4F46E5;
        padding: 4px 8px;
        border-radius: 6px;
        text-decoration: none;
        font-weight: 600;
        display: inline-block;
    }

    .badge-file:hover {
        background: #E0E7FF;
    }

    .btn-edit {
        background: #F59E0B;
        color: white;
        padding: 5px 10px;
        border-radius: 6px;
        text-decoration: none;
        font-size: 11px;
        font-weight: 600;
        display: inline-block;
        margin-right: 4px;
    }

    .btn-edit:hover {
        background: #D97706;
    }

    .btn-hapus {
        background: #EF4444;
        color: white;
        padding: 5px 10px;
        border-radius: 6px;
        text-decoration: none;
        font-size: 11px;
        font-weight: 600;
        display: inline-block;
        border: none;
        cursor: pointer;
    }

    .btn-hapus:hover {
        background: #DC2626;
    }

    .pagination-box {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-top: 20px;
        padding-top: 15px;
        border-top: 1px solid #E2E8F0;
        font-size: 13px;
        color: #475569;
        flex-wrap: wrap;
        gap: 10px;
    }

    .pagination {
        display: flex;
        gap: 4px;
        list-style: none;
        align-items: center;
    }

    .pagination li a,
    .pagination li span {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        padding: 6px 12px;
        border: 1px solid #CBD5E1;
        border-radius: 6px;
        text-decoration: none;
        color: #334155;
        font-weight: 500;
        font-size: 13px;
        background: white;
    }

    .pagination li a:hover {
        background: #F1F5F9;
    }

    .pagination li.active span {
        background: #2563EB;
        color: white;
        border-color: #2563EB;
        font-weight: 600;
    }

    .pagination li.disabled span {
        color: #94A3B8;
        border-color: #E2E8F0;
        background: #F8FAFC;
        cursor: not-allowed;
    }
    </style>
</head>

<body>

    <div class="container">
        <a href="dashboard.php" class="back-link">← Kembali ke Dashboard</a>

        <div class="top-nav-box">
            <h3 style="font-size: 16px; color: #0F172A;">Rekapitulasi Tugas Bulanan Saya</h3>
            <div style="display: flex; align-items: center; gap: 10px;">
                <span class="role-badge"><?= $role; ?></span>
                <span style="font-size: 14px; font-weight: 600;"><?= htmlspecialchars($nama_user); ?></span>
            </div>
        </div>

        <div class="card-filter">
            <div class="filter-title">Filter Periode Rekap</div>
            <form method="GET" action="">
                <input type="hidden" name="limit" value="<?= $limit; ?>">
                <?php if(!empty($cari)): ?>
                <input type="hidden" name="cari" value="<?= htmlspecialchars($cari); ?>">
                <?php endif; ?>

                <div class="filter-grid">
                    <div class="form-group">
                        <label>Pilih Bulan</label>
                        <select name="bulan" class="form-control">
                            <?php foreach ($array_bulan as $nama_bln => $num): ?>
                            <option value="<?= $nama_bln; ?>" <?= ($bulan == $nama_bln) ? 'selected' : ''; ?>>
                                <?= $nama_bln; ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Pilih Tahun</label>
                        <select name="tahun" class="form-control">
                            <?php for ($thn = 2024; $thn <= 2030; $thn++): ?>
                            <option value="<?= $thn; ?>" <?= ($tahun == $thn) ? 'selected' : ''; ?>><?= $thn; ?>
                            </option>
                            <?php endfor; ?>
                        </select>
                    </div>
                    <div>
                        <button type="submit" class="btn-submit">Tampilkan Rekap</button>
                    </div>
                </div>
            </form>

            <div class="btn-action-group">
                <a href="export-pdf.php?bulan=<?= $bulan; ?>&tahun=<?= $tahun; ?>" target="_blank" class="btn-pdf">🖨️
                    Cetak PDF / Print</a>
            </div>
        </div>

        <div class="card-content">
            <div class="card-header-flex">
                <div class="card-title">Daftar Laporan Bulan <?= htmlspecialchars($bulan); ?> Tahun
                    <?= htmlspecialchars($tahun); ?></div>

                <form method="GET" action="" class="table-controls">
                    <input type="hidden" name="bulan" value="<?= $bulan; ?>">
                    <input type="hidden" name="tahun" value="<?= $tahun; ?>">

                    <select name="limit" class="select-limit" onchange="this.form.submit()">
                        <option value="10" <?= ($limit == 10) ? 'selected' : ''; ?>>Tampilkan 10</option>
                        <option value="25" <?= ($limit == 25) ? 'selected' : ''; ?>>Tampilkan 25</option>
                        <option value="50" <?= ($limit == 50) ? 'selected' : ''; ?>>Tampilkan 50</option>
                        <option value="100" <?= ($limit == 100) ? 'selected' : ''; ?>>Tampilkan 100</option>
                    </select>

                    <input type="text" name="cari" class="search-input" placeholder="Cari mapel, jurusan, kelas..."
                        value="<?= htmlspecialchars($cari); ?>">
                    <button type="submit" class="btn-search">Cari</button>
                </form>
            </div>

            <div class="table-responsive">
                <table>
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Tanggal</th>
                            <th>Jam Ke</th>
                            <th>Nama Guru</th>
                            <th>Mata Pelajaran</th>
                            <th>Jurusan</th>
                            <th>Kelas Tujuan</th>
                            <th>Status Kehadiran</th>
                            <th>Lokasi Ruangan</th>
                            <th>Instruksi / Catatan Tugas</th>
                            <th>Berkas Tugas</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($result && mysqli_num_rows($result) > 0): ?>
                        <?php while ($row = mysqli_fetch_assoc($result)): 
                            // Mengambil data dari kolom database yang tepat
                            $mapel_val  = !empty($row['mapel']) ? $row['mapel'] : '-';
                            $berkas_val = isset($row['file_tugas']) ? trim($row['file_tugas']) : '';
                        ?>
                        <tr>
                            <td><?= $no++; ?></td>
                            <td><?= date('d/m/Y', strtotime($row['tanggal'])); ?></td>
                            <td><?= htmlspecialchars($row['jam_ke'] ?? '-'); ?></td>
                            <td><?= htmlspecialchars($nama_user); ?></td>
                            <td><?= htmlspecialchars($mapel_val); ?></td>
                            <td><?= htmlspecialchars($row['jurusan'] ?? '-'); ?></td>
                            <td><?= htmlspecialchars($row['kelas_tujuan'] ?? '-'); ?></td>
                            <td><?= htmlspecialchars($row['status'] ?? '-'); ?></td>
                            <td><?= htmlspecialchars($row['lokasi'] ?? '-'); ?></td>
                            <td><?= htmlspecialchars($row['instruksi'] ?? '-'); ?></td>
                            <td>
                                <?php if (!empty($berkas_val) && strtolower($berkas_val) !== 'null'): ?>
                                <a href="uploads/<?= htmlspecialchars($berkas_val); ?>" target="_blank"
                                    class="badge-file">📁 Lihat Berkas</a>
                                <?php else: ?>
                                <span style="color: #94A3B8;">Tidak ada</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <a href="update.php?id=<?= $row['id']; ?>" class="btn-edit">✏️ Edit</a>
                                <a href="hapus.php?id=<?= $row['id']; ?>&bulan=<?= $bulan; ?>&tahun=<?= $tahun; ?>"
                                    class="btn-hapus"
                                    onclick="return confirm('Apakah Anda yakin ingin menghapus laporan ini?');">🗑️
                                    Hapus</a>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                        <?php else: ?>
                        <tr>
                            <td colspan="12" class="empty-state">Tidak ada data laporan ditemukan
                                <?= !empty($cari) ? 'dengan kata kunci "'.htmlspecialchars($cari).'"' : ''; ?> untuk
                                periode <?= htmlspecialchars($bulan); ?> <?= htmlspecialchars($tahun); ?>.</td>
                        </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <!-- Bagian Navigasi Bawah -->
            <div class="pagination-box">
                <div>Menampilkan data <b><?= $data_awal_tampil; ?></b> sampai <b><?= $data_akhir_tampil; ?></b> dari
                    total <b><?= $jumlah_data; ?></b> data</div>
                <ul class="pagination">
                    <?php 
                    $param_url = "&bulan=$bulan&tahun=$tahun&limit=$limit" . (!empty($cari) ? "&cari=" . urlencode($cari) : "");
                    ?>

                    <?php if ($halaman > 1): ?>
                    <li><a href="?halaman=1<?= $param_url; ?>">« First</a></li>
                    <?php else: ?>
                    <li class="disabled"><span>« First</span></li>
                    <?php endif; ?>

                    <?php if ($halaman > 1): ?>
                    <li><a href="?halaman=<?= $halaman - 1; ?><?= $param_url; ?>">&lt; Prev</a></li>
                    <?php else: ?>
                    <li class="disabled"><span>&lt; Prev</span></li>
                    <?php endif; ?>

                    <?php 
                    $total_halaman_render = max(1, $total_halaman);
                    for ($x = 1; $x <= $total_halaman_render; $x++): 
                    ?>
                    <?php if ($x == $halaman): ?>
                    <li class="active"><span><?= $x; ?></span></li>
                    <?php else: ?>
                    <li><a href="?halaman=<?= $x; ?><?= $param_url; ?>"><?= $x; ?></a></li>
                    <?php endif; ?>
                    <?php endfor; ?>

                    <?php if ($halaman < $total_halaman): ?>
                    <li><a href="?halaman=<?= $halaman + 1; ?><?= $param_url; ?>">Next &gt;</a></li>
                    <?php else: ?>
                    <li class="disabled"><span>Next &gt;</span></li>
                    <?php endif; ?>

                    <?php if ($halaman < $total_halaman): ?>
                    <li><a href="?halaman=<?= $total_halaman; ?><?= $param_url; ?>">Last »</a></li>
                    <?php else: ?>
                    <li class="disabled"><span>Last »</span></li>
                    <?php endif; ?>
                </ul>
            </div>

        </div>
    </div>

</body>

</html>