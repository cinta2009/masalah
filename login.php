<?php
include 'config.php';

if (isset($_SESSION['user_id'])) {
    header("Location: dashboard.php");
    exit();
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = mysqli_real_escape_string($koneksi, $_POST['username']);
    $password = $_POST['password'];

    $query = mysqli_query($koneksi, "SELECT * FROM users WHERE username='$username'");
    
    if (mysqli_num_rows($query) === 1) {
        $row = mysqli_fetch_assoc($query);
        if ($password === $row['password'] || password_verify($password, $row['password'])) {
            $_SESSION['user_id']      = $row['id'];
            $_SESSION['username']     = $row['username'];
            $_SESSION['nama_lengkap'] = $row['nama_lengkap'];
            $_SESSION['role']         = $row['role'];

            header("Location: dashboard.php");
            exit();
        } else {
            $error = "Password yang Anda masukkan salah!";
        }
    } else {
        $error = "Username / Email tidak terdaftar!";
    }
}
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - etugas SMKN 1 Probolinggo</title>
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
        background: linear-gradient(135deg, #0F172A 0%, #1E1B4B 50%, #311042 100%);
        min-height: 100vh;
        display: flex;
        justify-content: center;
        align-items: center;
        padding: 20px;
    }

    .login-wrapper {
        background: rgba(255, 255, 255, 0.05);
        backdrop-filter: blur(16px);
        -webkit-backdrop-filter: blur(16px);
        border: 1px solid rgba(255, 255, 255, 0.1);
        border-radius: 24px;
        padding: 40px;
        width: 100%;
        max-width: 420px;
        box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5);
        color: #FFFFFF;
    }

    .brand-logo {
        text-align: center;
        margin-bottom: 30px;
    }

    .brand-logo .badge {
        background: rgba(99, 102, 241, 0.2);
        color: #A5B4FC;
        padding: 6px 14px;
        border-radius: 20px;
        font-size: 12px;
        font-weight: 600;
        display: inline-block;
        margin-bottom: 12px;
        border: 1px solid rgba(99, 102, 241, 0.3);
    }

    .brand-logo h1 {
        font-size: 26px;
        font-weight: 700;
        letter-spacing: -0.5px;
    }

    .brand-logo p {
        font-size: 13px;
        color: #94A3B8;
        margin-top: 4px;
    }

    .form-group {
        margin-bottom: 20px;
    }

    .form-group label {
        display: block;
        font-size: 13px;
        font-weight: 500;
        color: #CBD5E1;
        margin-bottom: 8px;
    }

    .form-group input {
        width: 100%;
        padding: 14px 16px;
        border-radius: 12px;
        border: 1px solid rgba(255, 255, 255, 0.15);
        background: rgba(15, 23, 42, 0.6);
        color: #FFF;
        font-size: 14px;
        outline: none;
        transition: all 0.3s;
    }

    .form-group input:focus {
        border-color: #6366F1;
        box-shadow: 0 0 0 4px rgba(99, 102, 241, 0.25);
        background: rgba(15, 23, 42, 0.8);
    }

    .btn-login {
        width: 100%;
        padding: 14px;
        background: linear-gradient(135deg, #6366F1 0%, #4F46E5 100%);
        border: none;
        border-radius: 12px;
        color: white;
        font-size: 15px;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.3s;
        box-shadow: 0 4px 12px rgba(79, 70, 229, 0.4);
        margin-top: 10px;
    }

    .btn-login:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 20px rgba(79, 70, 229, 0.6);
    }

    .alert-error {
        background: rgba(239, 68, 68, 0.2);
        border: 1px solid rgba(239, 68, 68, 0.4);
        color: #FCA5A5;
        padding: 12px;
        border-radius: 10px;
        font-size: 13px;
        margin-bottom: 20px;
        text-align: center;
    }
    </style>
</head>

<body>

    <div class="login-wrapper">
        <div class="brand-logo">
            <span class="badge">SMKN 1 PROBOLINGGO</span>
            <h1>etugas Portal</h1>
            <p>Sistem Informasi Tugas siswa-siswi</p>
        </div>

        <?php if($error): ?>
        <div class="alert-error"><?= $error; ?></div>
        <?php endif; ?>

        <form action="" method="POST">
            <div class="form-group">
                <label>Username / Email</label>
                <input type="text" name="username" placeholder="Masukkan ID Guru Anda" required autocomplete="off">
            </div>
            <div class="form-group">
                <label>Password</label>
                <input type="password" name="password" placeholder="••••••••" required>
            </div>
            <button type="submit" class="btn-login">Masuk ke Sistem</button>
        </form>
    </div>

</body>

</html>