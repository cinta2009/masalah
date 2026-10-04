<?php
include 'config.php';
cek_login();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Ambil data sesi dengan pengaman default
    $user_id   = $_SESSION['user_id'] ?? 0;
    $role      = $_SESSION['role'] ?? 'guru';
    
    // Sanitasi input form agar aman dari SQL Injection
    $nama_guru    = mysqli_real_escape_string($koneksi, $_POST['nama_guru']);
    $mapel        = mysqli_real_escape_string($koneksi, $_POST['mapel']);
    $tanggal      = mysqli_real_escape_string($koneksi, $_POST['tanggal']);
    $jurusan      = mysqli_real_escape_string($koneksi, $_POST['jurusan']);
    $kelas_tujuan = mysqli_real_escape_string($koneksi, $_POST['kelas_tujuan']); 
    $jam_ke       = mysqli_real_escape_string($koneksi, $_POST['jam_ke']);
    $status       = mysqli_real_escape_string($koneksi, $_POST['status']); 
    $lokasi       = mysqli_real_escape_string($koneksi, $_POST['lokasi']);
    $instruksi    = mysqli_real_escape_string($koneksi, $_POST['instruksi']);

    // Handle Upload File (Foto/PDF)
    $nama_file = NULL;
    $path_file_lengkap = NULL;

    if (isset($_FILES['file_tugas']) && $_FILES['file_tugas']['error'] == 0) {
        $target_dir = "uploads/";
        if (!is_dir($target_dir)) {
            mkdir($target_dir, 0777, true);
        }
        $ext = pathinfo($_FILES["file_tugas"]["name"], PATHINFO_EXTENSION);
        $nama_file = time() . "_" . uniqid() . "." . $ext;
        $path_file_lengkap = $target_dir . $nama_file;
        
        move_uploaded_file($_FILES["file_tugas"]["tmp_name"], $path_file_lengkap);
    }

    // Default status telegram
    $status_telegram = "Gagal Terkirim";

    // ==========================================
    // AMBIL TOKEN BOT TERBARU DARI DATABASE
    // ==========================================
    $q_token_bot = mysqli_query($koneksi, "SELECT nilai_setting FROM tb_pengaturan_bot WHERE nama_setting = 'bot_token'");
    $d_token_bot = mysqli_fetch_assoc($q_token_bot);
    // Token otomatis menyesuaikan inputan dari web, dengan fallback aman jika belum diatur
    $token = $d_token_bot ? $d_token_bot['nilai_setting'] : '8327213877:AAGGTi6QcRi3N7IUMOU58Pp0UUNtJastybU'; 

    // Format pesan utama untuk dikirim ke Telegram
    $pesan  = "📢 *PENGUMUMAN JURNAL & TUGAS KELAS*\n\n";
    $pesan .= "👨‍🏫 *Guru:* " . $nama_guru . "\n";
    $pesan .= "📚 *Mapel:* " . $mapel . "\n";
    $pesan .= "🏫 *Kelas Tujuan:* " . $kelas_tujuan . "\n";
    $pesan .= "⏰ *Jam:* " . $jam_ke . " | *Tanggal:* " . $tanggal . "\n";
    $pesan .= "📍 *Lokasi:* " . $lokasi . "\n\n";
    $pesan .= "*INSTRUKSI TUGAS:*\n" . $instruksi;

    $berhasil_kirim = false;

    // ==========================================
    // 1. KIRIM KE KETUA KELAS (SESUAI KELAS TUJUAN)
    // ==========================================
    $queryKetua = mysqli_query($koneksi, "SELECT chat_id_telegram, nama_ketua FROM tb_ketua_kelas WHERE kelas = '$kelas_tujuan'");
    $dataKetua = mysqli_fetch_assoc($queryKetua);

    if ($dataKetua && !empty($dataKetua['chat_id_telegram'])) {
        $chat_id_ketua = $dataKetua['chat_id_telegram'];
        
        // Pesan khusus sapaan ketua kelas di baris atas
        $pesan_ketua = "Halo *" . $dataKetua['nama_ketua'] . "*, ada update tugas baru untuk kelasmu:\n\n" . $pesan;

        $ch = curl_init();
        if ($nama_file && file_exists($path_file_lengkap)) {
            $url = "https://api.telegram.org/bot$token/sendDocument";
            $document = function_exists('curl_file_create') ? 
                        curl_file_create(realpath($path_file_lengkap)) : 
                        '@' . realpath($path_file_lengkap);

            $postData = [
                'chat_id' => $chat_id_ketua,
                'caption' => $pesan_ketua,
                'parse_mode' => 'Markdown',
                'document' => $document
            ];
        } else {
            $url = "https://api.telegram.org/bot$token/sendMessage";
            $postData = [
                'chat_id' => $chat_id_ketua,
                'text' => $pesan_ketua,
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
            $berhasil_kirim = true;
        }
    }

    // ==========================================
    // 2. KIRIM KE SEMUA ADMIN / KURIKULUM TERDAFTAR
    // ==========================================
    $queryAdmin = mysqli_query($koneksi, "SELECT chat_id_telegram, nama_admin FROM tb_admin_telegram");
    while ($dataAdmin = mysqli_fetch_assoc($queryAdmin)) {
        if (!empty($dataAdmin['chat_id_telegram'])) {
            $chat_id_admin = $dataAdmin['chat_id_telegram'];
            
            // Pesan khusus laporan untuk admin
            $pesan_admin = "📋 *LAPORAN MASUK TUGAS BARU*\n(Untuk Arsip Admin/Kurikulum)\n\n" . $pesan;

            $ch_adm = curl_init();
            if ($nama_file && file_exists($path_file_lengkap)) {
                $url_adm = "https://api.telegram.org/bot$token/sendDocument";
                $document_adm = function_exists('curl_file_create') ? 
                                curl_file_create(realpath($path_file_lengkap)) : 
                                '@' . realpath($path_file_lengkap);

                $postDataAdm = [
                    'chat_id' => $chat_id_admin,
                    'caption' => $pesan_admin,
                    'parse_mode' => 'Markdown',
                    'document' => $document_adm
                ];
            } else {
                $url_adm = "https://api.telegram.org/bot$token/sendMessage";
                $postDataAdm = [
                    'chat_id' => $chat_id_admin,
                    'text' => $pesan_admin,
                    'parse_mode' => 'Markdown'
                ];
            }

            curl_setopt($ch_adm, CURLOPT_URL, $url_adm);
            curl_setopt($ch_adm, CURLOPT_POST, true);
            curl_setopt($ch_adm, CURLOPT_POSTFIELDS, $postDataAdm);
            curl_setopt($ch_adm, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch_adm, CURLOPT_SSL_VERIFYPEER, false);
            
            $response_adm = curl_exec($ch_adm);
            curl_close($ch_adm);

            $resArrayAdm = json_decode($response_adm, true);
            if (isset($resArrayAdm['ok']) && $resArrayAdm['ok'] === true) {
                $berhasil_kirim = true;
            }
        }
    }

    // Tentukan status telegram untuk database
    if ($berhasil_kirim) {
        $status_telegram = "Sukses Terkirim";
    } else {
        $status_telegram = "Gagal (Ketua Kelas Tidak Ditemukan / Token Salah)";
    }

    // Format query penyimpanan data ke database
    $sql_file_value = $nama_file ? "'$nama_file'" : "NULL";
    $sql = "INSERT INTO tbmasalah (user_id, nama_guru, mapel, kelas_tujuan, jurusan, jam_ke, tanggal, status, lokasi, instruksi, file_tugas, status_telegram) 
            VALUES ('$user_id', '$nama_guru', '$mapel', '$kelas_tujuan', '$jurusan', '$jam_ke', '$tanggal', '$status', '$lokasi', '$instruksi', $sql_file_value, '$status_telegram')";
    
    if (mysqli_query($koneksi, $sql)) {
        // PENGALIHAN HALAMAN BERDASARKAN ROLE
        if ($role === 'admin') {
            header("Location: view-data.php");
            exit();
        } else {
            header("Location: dashboard.php");
            exit();
        }
    } else {
        echo "Gagal menyimpan data ke database: " . mysqli_error($koneksi);
    }
}
?>