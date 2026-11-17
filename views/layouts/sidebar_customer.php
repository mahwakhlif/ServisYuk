<?php
// ==========================================================
// Layout: Sidebar Pelanggan (Strict PROMPT 3 Structure)
// ==========================================================
$currentPage = $_GET['page'] ?? 'customer_dashboard';
$currentStatus = $_GET['status'] ?? '';
$user = Auth::user();
?>
<aside class="sidebar">
    <a href="<?= BASE_URL ?>" class="sidebar-brand">
        <div class="logo-icon"><i class="fa-solid fa-house-chimney-user"></i></div>
        <span>ServisYuk <span style="font-size:0.75rem; color:var(--secondary);">MEMBER</span></span>
    </a>

    <div class="sidebar-user">
        <div class="user-avatar"><i class="fa-solid fa-user"></i></div>
        <div class="user-info">
            <div class="user-name"><?= htmlspecialchars($user['name'] ?? 'Pelanggan') ?></div>
            <div class="user-role">Customer Terverifikasi</div>
        </div>
    </div>

    <ul class="sidebar-nav">
        <li class="nav-section-title">Menu Utama</li>
        <li>
            <a href="<?= BASE_URL ?>/index.php?page=customer_dashboard" class="sidebar-link <?= $currentPage === 'customer_dashboard' ? 'active' : '' ?>">
                <i class="fa-solid fa-gauge-high"></i> Dashboard
            </a>
        </li>
        <li>
            <a href="<?= BASE_URL ?>/index.php?page=customer_booking_create" class="sidebar-link <?= $currentPage === 'customer_booking_create' ? 'active' : '' ?>" style="color:#a5f3fc; font-weight:600;">
                <i class="fa-solid fa-circle-plus"></i> Ajukan Jasa
            </a>
        </li>
        <li>
            <a href="<?= BASE_URL ?>/index.php?page=ai_diagnostic" class="sidebar-link <?= $currentPage === 'ai_diagnostic' ? 'active' : '' ?>">
                <i class="fa-solid fa-wand-magic-sparkles"></i> AI Diagnosis
            </a>
        </li>

        <li class="nav-section-title">Layanan & Status</li>
        <li>
            <a href="<?= BASE_URL ?>/index.php?page=customer_history&status=in_progress" class="sidebar-link <?= ($currentPage === 'customer_history' && $currentStatus === 'in_progress') ? 'active' : '' ?>">
                <i class="fa-solid fa-spinner"></i> Permintaan Saya
            </a>
        </li>
        <li>
            <a href="<?= BASE_URL ?>/index.php?page=tracking" class="sidebar-link <?= $currentPage === 'tracking' ? 'active' : '' ?>">
                <i class="fa-solid fa-location-crosshairs"></i> Status Layanan
            </a>
        </li>
        <li>
            <a href="<?= BASE_URL ?>/index.php?page=customer_history" class="sidebar-link <?= ($currentPage === 'customer_history' && empty($currentStatus)) ? 'active' : '' ?>">
                <i class="fa-solid fa-receipt"></i> Riwayat
            </a>
        </li>

        <li class="nav-section-title">Pengaturan Akun</li>
        <li>
            <a href="<?= BASE_URL ?>/index.php?page=customer_profile" class="sidebar-link <?= $currentPage === 'customer_profile' ? 'active' : '' ?>">
                <i class="fa-solid fa-id-card"></i> Profil
            </a>
        </li>
        <li>
            <a href="<?= BASE_URL ?>/index.php?page=logout" class="sidebar-link" style="color:var(--danger);">
                <i class="fa-solid fa-right-from-bracket"></i> Logout
            </a>
        </li>
    </ul>
</aside>
