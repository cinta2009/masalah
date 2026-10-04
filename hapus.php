<?php
include 'config.php';
cek_login();

if (isset($_GET['id'])) {
    $id = mysqli_real_escape_string($koneksi, $_GET['id']);
    
    // Hapus file fisik di folder uploads jika ada
    $q = mysqli_query($koneksi, "SELECT file_tugas FROM tbmasalah WHERE id='$id'");
    $d = mysqli_fetch_assoc($q);
    if (!empty($d['file_tugas']) && file_exists("uploads/" . $d['file_tugas'])) {
        unlink("uploads/" . $d['file_tugas']);
    }

    mysqli_query($koneksi, "DELETE FROM tbmasalah WHERE id='$id'");
}

header("Location: riwayat-guru.php");
exit();
?>