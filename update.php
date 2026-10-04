<?php
include 'config.php';
cek_login();

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$user_id = $_SESSION['user_id'] ?? 0;
$role    = $_SESSION['role'] ?? 'guru';

// Ambil data yang akan diedit
$sql = ($role === 'admin') 
    ? "SELECT * FROM tbmasalah WHERE id = '$id'" 
    : "SELECT * FROM tbmasalah WHERE id = '$id' AND user_id = '$user_id'";

$query = mysqli_query($koneksi, $sql);
$data  = mysqli_fetch_assoc($query);

if (!$data) {
    echo "<script>alert('Data tidak ditemukan atau Anda tidak memiliki akses!'); window.location='view-data.php';</script>";
    exit;
}

// Proses Update Data & Kirim Ulang Telegram
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama_guru    = mysqli_real_escape_string($koneksi, $_POST['nama_guru']);
    $mapel        = mysqli_real_escape_string($koneksi, $_POST['mapel']);
    $tanggal      = mysqli_real_escape_string($koneksi, $_POST['tanggal']);
    $jurusan      = mysqli_real_escape_string($koneksi, $_POST['jurusan']);
    $kelas_tujuan = mysqli_real_escape_string($koneksi, $_POST['kelas_tujuan']);
    $jam_ke       = mysqli_real_escape_string($koneksi, $_POST['jam_ke']);
    $status       = mysqli_real_escape_string($koneksi, $_POST['status']);
    $lokasi       = mysqli_real_escape_string($koneksi, $_POST['lokasi']);
    $instruksi    = mysqli_real_escape_string($koneksi, $_POST['instruksi']);

    $nama_file = $data['file_tugas'];
    $path_file_lengkap = NULL;

    // Handle Upload File Baru Jika Ada
    if (isset($_FILES['file_tugas']) && $_FILES['file_tugas']['error'] == 0) {
        $target_dir = "uploads/";
        if (!is_dir($target_dir)) {
            mkdir($target_dir, 0777, true);
        }
        $ext = pathinfo($_FILES["file_tugas"]["name"], PATHINFO_EXTENSION);
        $nama_file_baru = time() . "_" . uniqid() . "." . $ext;
        
        if (move_uploaded_file($_FILES["file_tugas"]["tmp_name"], $target_dir . $nama_file_baru)) {
            // Hapus file lama dari server jika ada
            if (!empty($data['file_tugas']) && file_exists($target_dir . $data['file_tugas'])) {
                unlink($target_dir . $data['file_tugas']);
            }
            $nama_file = $nama_file_baru;
        }
    }

    if ($nama_file) {
        $path_file_lengkap = "uploads/" . $nama_file;
    }

    // Default Status Telegram
    $status_telegram = "Gagal (Ketua Kelas Tidak Ditemukan)";

    // Cari Chat ID Ketua Kelas dari Database berdasarkan Kelas Tujuan
    $queryKetua = mysqli_query($koneksi, "SELECT chat_id_telegram, nama_ketua FROM tb_ketua_kelas WHERE kelas = '$kelas_tujuan'");
    $dataKetua = mysqli_fetch_assoc($queryKetua);

    if ($dataKetua && !empty($dataKetua['chat_id_telegram'])) {
        $token = "8327213877:AAGGTi6QcRi3N7IUMOU58Pp0UUNtJastybU"; 
        $chat_id = $dataKetua['chat_id_telegram'];
        
        $pesan = "📢 *UPDATE PENGUMUMAN JURNAL & TUGAS KELAS*\n\n";
        $pesan .= "Halo *" . $dataKetua['nama_ketua'] . "*, ada pembaruan tugas:\n";
        $pesan .= "👨‍🏫 *Guru:* $nama_guru\n";
        $pesan .= "📚 *Mapel:* $mapel\n";
        $pesan .= "⏰ *Jam:* $jam_ke | *Tanggal:* $tanggal\n";
        $pesan .= "📍 *Lokasi:* $lokasi\n\n";
        $pesan .= "*INSTRUKSI TUGAS TERBARU:*\n$instruksi";

        $ch = curl_init();

        if ($nama_file && file_exists($path_file_lengkap)) {
            $url = "https://api.telegram.org/bot$token/sendDocument";
            $postData = [
                'chat_id' => $chat_id,
                'caption' => $pesan,
                'parse_mode' => 'Markdown',
                'document' => new CURLFile(realpath($path_file_lengkap))
            ];
        } else {
            $url = "https://api.telegram.org/bot$token/sendMessage";
            $postData = [
                'chat_id' => $chat_id,
                'text' => $pesan,
                'parse_mode' => 'Markdown'
            ];
        }

        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        
        $response = curl_exec($ch);
        curl_close($ch);

        $resArray = json_decode($response, true);
        if (isset($resArray['ok']) && $resArray['ok'] === true) {
            $status_telegram = "Sukses Terkirim";
        } else {
            $status_telegram = "Gagal Terkirim";
        }
    }

    // Update Database beserta Status Telegram Terbaru
    $update_sql = "UPDATE tbmasalah SET 
                    nama_guru       = '$nama_guru',
                    mapel           = '$mapel',
                    tanggal         = '$tanggal',
                    jurusan         = '$jurusan',
                    kelas_tujuan    = '$kelas_tujuan',
                    jam_ke          = '$jam_ke',
                    status          = '$status',
                    lokasi          = '$lokasi',
                    instruksi       = '$instruksi',
                    file_tugas      = '$nama_file',
                    status_telegram = '$status_telegram'
                   WHERE id = '$id'";

    if (mysqli_query($koneksi, $update_sql)) {
        header("Location: riwayat-guru.php");
        exit;
    } else {
        $error = "Gagal memperbarui data: " . mysqli_error($koneksi);
    }
}
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Data Jurnal Mengajar</title>
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
        padding: 30px 20px;
    }

    .form-card {
        max-width: 650px;
        margin: 0 auto;
        background: white;
        border-radius: 16px;
        padding: 30px;
        border: 1px solid #E2E8F0;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.03);
    }

    .form-card h2 {
        font-size: 22px;
        font-weight: 700;
        color: #0F172A;
        margin-bottom: 20px;
    }

    .form-group {
        margin-bottom: 18px;
    }

    label {
        display: block;
        font-weight: 600;
        font-size: 13px;
        margin-bottom: 6px;
        color: #334155;
    }

    input,
    select,
    textarea {
        width: 100%;
        padding: 10px 14px;
        border: 1px solid #CBD5E1;
        border-radius: 8px;
        font-size: 13px;
        outline: none;
        transition: all 0.2s;
        background-color: #FAFAFA;
    }

    input:focus,
    select:focus,
    textarea:focus {
        border-color: #4F46E5;
        background-color: #FFFFFF;
        box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.1);
    }

    .grid-2 {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 15px;
    }

    .btn-submit {
        background: #5B4CF5;
        color: white;
        border: none;
        padding: 12px 20px;
        border-radius: 8px;
        font-weight: 700;
        cursor: pointer;
        width: 100%;
        margin-top: 10px;
        font-size: 14px;
        transition: background 0.2s;
    }

    .btn-submit:hover {
        background: #4D3DF3;
    }

    .btn-cancel {
        display: block;
        text-align: center;
        margin-top: 12px;
        color: #64748B;
        text-decoration: none;
        font-size: 13px;
        font-weight: 600;
    }

    .file-info {
        font-size: 12px;
        color: #64748B;
        margin-top: 4px;
    }
    </style>
</head>

<body>

    <div class="form-card">
        <h2>Edit Data Jurnal Mengajar</h2>

        <?php if(isset($error)): ?>
        <p
            style="color: #DC2626; background: #FEE2E2; padding: 10px; border-radius: 8px; margin-bottom: 15px; font-size: 13px;">
            <?= $error; ?></p>
        <?php endif; ?>

        <form method="POST" action="" enctype="multipart/form-data">

            <div class="form-group">
                <label>Nama Guru</label>
                <input type="text" name="nama_guru" value="<?= htmlspecialchars($data['nama_guru']); ?>" required>
            </div>

            <div class="grid-2">
                <div class="form-group">
                    <label>Mata Pelajaran</label>
                    <input type="text" name="mapel" value="<?= htmlspecialchars($data['mapel']); ?>" required>
                </div>
                <div class="form-group">
                    <label>Tanggal Mengajar</label>
                    <input type="date" name="tanggal" value="<?= $data['tanggal']; ?>" required>
                </div>
            </div>

            <div class="grid-2">
                <div class="form-group">
                    <label>Jurusan</label>
                    <select name="jurusan" required>
                        <option value="RPL" <?= ($data['jurusan'] === 'RPL') ? 'selected' : ''; ?>>RPL (Rekayasa
                            Perangkat Lunak)</option>
                        <option value="TKJ" <?= ($data['jurusan'] === 'TKJ') ? 'selected' : ''; ?>>TKJ (Teknik Komputer
                            & Jaringan)</option>
                        <option value="AKL" <?= ($data['jurusan'] === 'AKL') ? 'selected' : ''; ?>>AKL (Akuntansi)
                        </option>
                        <option value="OTKP" <?= ($data['jurusan'] === 'OTKP') ? 'selected' : ''; ?>>OTKP (Perkantoran)
                        </option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Kelas Tujuan</label>
                    <select name="kelas_tujuan" required>
                        <option value="X RPL 1" <?= ($data['kelas_tujuan'] === 'X RPL 1') ? 'selected' : ''; ?>>X RPL 1
                        </option>
                        <option value="XI RPL 1" <?= ($data['kelas_tujuan'] === 'XI RPL 1') ? 'selected' : ''; ?>>XI RPL
                            1</option>
                        <option value="XI RPL 2" <?= ($data['kelas_tujuan'] === 'XI RPL 2') ? 'selected' : ''; ?>>XI RPL
                            2</option>
                        <option value="XII RPL 1" <?= ($data['kelas_tujuan'] === 'XII RPL 1') ? 'selected' : ''; ?>>XII
                            RPL 1</option>
                    </select>
                </div>
            </div>

            <div class="grid-2">
                <div class="form-group">
                    <label>Jam Ke-</label>
                    <input type="text" name="jam_ke" value="<?= htmlspecialchars($data['jam_ke']); ?>" required>
                </div>
                <div class="form-group">
                    <label>Status Kehadiran Guru</label>
                    <select name="status" required>
                        <option value="Hadir (Di Kelas)"
                            <?= ($data['status'] === 'Hadir (Di Kelas)') ? 'selected' : ''; ?>>Hadir (Di Kelas)</option>
                        <option value="Hadir / Ada Tugas"
                            <?= ($data['status'] === 'Hadir / Ada Tugas') ? 'selected' : ''; ?>>Hadir / Ada Tugas
                        </option>
                        <option value="Izin / Sakit (Tugas)"
                            <?= ($data['status'] === 'Izin / Sakit (Tugas)') ? 'selected' : ''; ?>>Izin / Sakit (Tugas)
                        </option>
                    </select>
                </div>
            </div>

            <div class="form-group">
                <label>Lokasi Ruangan / Laboratorium</label>
                <input type="text" name="lokasi" value="<?= htmlspecialchars($data['lokasi']); ?>" required>
            </div>

            <div class="form-group">
                <label>Instruksi / Catatan Tugas</label>
                <textarea name="instruksi" rows="4" required><?= htmlspecialchars($data['instruksi']); ?></textarea>
            </div>

            <div class="form-group">
                <label>Upload Berkas Tugas (Foto / PDF) - <i>Opsional</i></label>
                <input type="file" name="file_tugas">
                <?php if (!empty($data['file_tugas'])): ?>
                <div class="file-info">📁 File terpasang saat ini:
                    <strong><?= htmlspecialchars($data['file_tugas']); ?></strong>
                </div>
                <?php endif; ?>
            </div>

            <button type="submit" class="btn-submit">Simpan Perubahan</button>
            <a href="riwayat-guru.php" class="btn-cancel">Batal</a>
        </form>
    </div>

</body>

</html>