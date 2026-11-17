<?php
// ==========================================================
// Layout: Sidebar Administrator (Strict PROMPT 3 Structure)
// ==========================================================
$currentPage = $_GET['page'] ?? 'admin_dashboard';
$currentStatus = $_GET['status'] ?? '';
$user = Auth::user();
?>
<aside class="sidebar">
    <a href="<?= BASE_URL ?>" class="sidebar-brand">
        <div class="logo-icon"><i class="fa-solid fa-shield-halved"></i></div>
        <span>ServisYuk <span style="font-size:0.75rem; color:var(--secondary);">ADMIN</span></span>
    </a>

    <div class="sidebar-user">
        <div class="user-avatar"><i class="fa-solid fa-user-gear"></i></div>
        <div class="user-info">
            <div class="user-name"><?= htmlspecialchars($user['name'] ?? 'Administrator') ?></div>
            <div class="user-role">Super Admin</div>
        </div>
    </div>

    <ul class="sidebar-nav">
        <li class="nav-section-title">Menu Utama</li>
        <li>
            <a href="<?= BASE_URL ?>/index.php?page=admin_dashboard" class="sidebar-link <?= $currentPage === 'admin_dashboard' ? 'active' : '' ?>">
                <i class="fa-solid fa-chart-pie"></i> Dashboard
            </a>
        </li>
        <li>
            <a href="<?= BASE_URL ?>/index.php?page=admin_customers" class="sidebar-link <?= $currentPage === 'admin_customers' ? 'active' : '' ?>">
                <i class="fa-solid fa-users"></i> Customer
            </a>
        </li>
        <li>
            <a href="<?= BASE_URL ?>/index.php?page=admin_technicians" class="sidebar-link <?= $currentPage === 'admin_technicians' ? 'active' : '' ?>">
                <i class="fa-solid fa-user-tie"></i> Teknisi
            </a>
        </li>
        <li>
            <a href="<?= BASE_URL ?>/index.php?page=admin_services" class="sidebar-link <?= $currentPage === 'admin_services' ? 'active' : '' ?>">
                <i class="fa-solid fa-boxes-stacked"></i> Jenis Layanan
            </a>
        </li>

        <li class="nav-section-title">Manajemen Operasional</li>
        <li>
            <a href="<?= BASE_URL ?>/index.php?page=admin_bookings" class="sidebar-link <?= ($currentPage === 'admin_bookings' && empty($currentStatus)) ? 'active' : '' ?>">
                <i class="fa-solid fa-clipboard-list"></i> Permintaan Jasa
            </a>
        </li>
        <li>
            <a href="<?= BASE_URL ?>/index.php?page=admin_bookings&status=pending" class="sidebar-link <?= ($currentPage === 'admin_bookings' && $currentStatus === 'pending') ? 'active' : '' ?>">
                <i class="fa-solid fa-user-plus"></i> Penugasan Teknisi
            </a>
        </li>
        <li>
            <a href="<?= BASE_URL ?>/index.php?page=admin_bookings&status=completed" class="sidebar-link <?= ($currentPage === 'admin_bookings' && $currentStatus === 'completed') ? 'active' : '' ?>">
                <i class="fa-solid fa-clock-rotate-left"></i> Riwayat Pekerjaan
            </a>
        </li>
        <li>
            <a href="<?= BASE_URL ?>/index.php?page=admin_reports" class="sidebar-link <?= $currentPage === 'admin_reports' ? 'active' : '' ?>">
                <i class="fa-solid fa-file-invoice-dollar"></i> Laporan
            </a>
        </li>
        <li>
            <a href="<?= BASE_URL ?>/index.php?page=admin_ai_config" class="sidebar-link <?= in_array($currentPage, ['admin_ai_config', 'admin_ai_settings']) ? 'active' : '' ?>">
                <i class="fa-solid fa-robot"></i> Konfigurasi AI
            </a>
        </li>

        <li class="nav-section-title">Pengaturan Akun</li>
        <li>
            <a href="<?= BASE_URL ?>/index.php?page=admin_profile" class="sidebar-link <?= $currentPage === 'admin_profile' ? 'active' : '' ?>">
                <i class="fa-solid fa-user-gear"></i> Profil
            </a>
        </li>
        <li>
            <a href="<?= BASE_URL ?>/index.php?page=logout" class="sidebar-link" style="color:var(--danger);">
                <i class="fa-solid fa-right-from-bracket"></i> Logout
            </a>
        </li>
    </ul>
</aside>
