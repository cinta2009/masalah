<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$koneksi = mysqli_connect("localhost", "root", "root", "dbmasalah");
if (!$koneksi){
    die("Koneksi Database Gagal: " . mysqli_connect_error());
}

function cek_login() {
    if (!isset($_SESSION['user_id'])) {
        header("Location: login.php");
        exit();
    }
}
?>