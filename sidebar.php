<?php
// Pastikan session sudah aktif
$current_page = basename($_SERVER['PHP_SELF']);
$role_user = $_SESSION['role'] ?? 'guru';
$nama_user = $_SESSION['nama_lengkap'] ?? 'User';
?>
<!-- CSS Sidebar & Layout Utama -->
<style>
.app-layout {
    display: flex;
    min-height: 100vh;
    background-color: #F1F5F9;
}

.sidebar {
    width: 260px;
    background: #0F172A;
    color: #94A3B8;
    display: flex;
    flex-direction: column;
    position: fixed;
    top: 0;
    bottom: 0;
    left: 0;
    z-index: 100;
    box-shadow: 4px 0 10px rgba(0, 0, 0, 0.05);
}

.sidebar-brand {
    padding: 24px 20px;
    font-size: 18px;
    font-weight: 700;
    color: #FFFFFF;
    border-bottom: 1px solid #1E293B;
    display: flex;
    align-items: center;
    gap: 10px;
}

.sidebar-brand span {
    color: #818CF8;
}

.sidebar-menu {
    padding: 20px 15px;
    list-style: none;
    flex-grow: 1;
    overflow-y: auto;
}

.sidebar-menu li {
    margin-bottom: 6px;
}

.sidebar-menu a {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 12px 16px;
    color: #94A3B8;
    text-decoration: none;
    font-size: 14px;
    font-weight: 500;
    border-radius: 10px;
    transition: all 0.2s;
}

.sidebar-menu a:hover,
.sidebar-menu a.active {
    background: #1E293B;
    color: #FFFFFF;
}

.sidebar-menu a.active {
    background: #4F46E5;
    color: #FFFFFF;
}

.sidebar-section-title {
    font-size: 11px;
    text-transform: uppercase;
    letter-spacing: 1px;
    color: #64748B;
    margin: 20px 16px 10px 16px;
    font-weight: 700;
}

.sidebar-footer {
    padding: 20px;
    border-top: 1px solid #1E293B;
    background: #0B1120;
}

.user-mini {
    display: flex;
    align-items: center;
    gap: 12px;
    margin-bottom: 15px;
}

.user-mini-avatar {
    width: 38px;
    height: 38px;
    background: #3B82F6;
    color: white;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 700;
    font-size: 15px;
}

.user-mini-info h4 {
    font-size: 13px;
    color: #FFFFFF;
    font-weight: 600;
}

.user-mini-info span {
    font-size: 11px;
    color: #38BDF8;
    text-transform: uppercase;
}

.btn-logout-sidebar {
    display: block;
    text-align: center;
    background: rgba(239, 68, 68, 0.1);
    color: #EF4444;
    padding: 8px;
    border-radius: 8px;
    text-decoration: none;
    font-size: 13px;
    font-weight: 600;
    transition: background 0.2s;
}

.btn-logout-sidebar:hover {
    background: rgba(239, 68, 68, 0.2);
}

.main-content-wrapper {
    margin-left: 260px;
    flex-grow: 1;
    display: flex;
    flex-direction: column;
}

.topbar-header {
    background: white;
    padding: 15px 30px;
    border-bottom: 1px solid #E2E8F0;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.main-body {
    padding: 30px;
    flex-grow: 1;
}

@media(max-width: 768px) {
    .sidebar {
        width: 70px;
    }

    .sidebar-brand span,
    .sidebar-menu span,
    .sidebar-section-title,
    .user-mini-info {
        display: none;
    }

    .main-content-wrapper {
        margin-left: 70px;
    }
}
</style>

<div class="sidebar">
    <div class="sidebar-brand">
        📚 <span>e-Tugas</span>
    </div>

    <ul class="sidebar-menu">
        <li><a href="dashboard.php" class="<?= ($current_page == 'dashboard.php') ? 'active' : ''; ?>">🏠
                <span>Dashboard</span></a></li>
        <li><a href="index.php" class="<?= ($current_page == 'index.php') ? 'active' : ''; ?>">📝 <span>Input
                    Tugas</span></a></li>
        <li><a href="view-data.php" class="<?= ($current_page == 'view-data.php') ? 'active' : ''; ?>">📊 <span>Riwayat
                    & Data</span></a></li>
        <li><a href="rekap.php" class="<?= ($current_page == 'rekap.php') ? 'active' : ''; ?>">📅 <span>Rekapitulasi
                    Bulanan</span></a></li>

        <?php if ($role_user == 'admin'): ?>
        <div class="sidebar-section-title">Menu Administrator</div>
        <li><a href="manajemen-guru.php" class="<?= ($current_page == 'manajemen-guru.php') ? 'active' : ''; ?>">👥
                <span>Kelola Data Guru</span></a></li>
        <li><a href="manajemen-telegram.php"
                class="<?= ($current_page == 'manajemen-telegram.php') ? 'active' : ''; ?>">💬 <span>Telegram Ketua
                    Kelas</span></a></li>
        <?php endif; ?>
    </ul>

    <div class="sidebar-footer">
        <div class="user-mini">
            <div class="user-mini-avatar"><?= strtoupper(substr($nama_user, 0, 1)); ?></div>
            <div class="user-mini-info">
                <h4><?= htmlspecialchars($nama_user); ?></h4>
                <span><?= $role_user; ?></span>
            </div>
        </div>
        <a href="logout.php" class="btn-logout-sidebar">Keluar Sistem</a>
    </div>
</div>