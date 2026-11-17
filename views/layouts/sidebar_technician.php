<?php
// ==========================================================
// Layout: Sidebar Teknisi (Strict PROMPT 3 Structure)
// ==========================================================
$currentPage = $_GET['page'] ?? 'tech_dashboard';
$user = Auth::user();
?>
<aside class="sidebar">
    <a href="<?= BASE_URL ?>" class="sidebar-brand">
        <div class="logo-icon" style="background:linear-gradient(135deg, #059669 0%, #10b981 100%);"><i class="fa-solid fa-screwdriver-wrench"></i></div>
        <span>ServisYuk <span style="font-size:0.75rem; color:#86efac;">TEKNISI</span></span>
    </a>

    <div class="sidebar-user">
        <div class="user-avatar" style="background:linear-gradient(135deg, #059669 0%, #10b981 100%);"><i class="fa-solid fa-user-gear"></i></div>
        <div class="user-info">
            <div class="user-name"><?= htmlspecialchars($user['name'] ?? 'Teknisi') ?></div>
            <div class="user-role" style="color:#86efac;"><?= htmlspecialchars($user['specialization'] ?? 'Teknisi Lapangan') ?></div>
        </div>
    </div>

    <ul class="sidebar-nav">
        <li class="nav-section-title">Pekerjaan Lapangan</li>
        <li>
            <a href="<?= BASE_URL ?>/index.php?page=tech_dashboard" class="sidebar-link <?= ($currentPage === 'tech_dashboard' && empty($_GET['filter'])) ? 'active' : '' ?>">
                <i class="fa-solid fa-gauge"></i> Dashboard
            </a>
        </li>
        <li>
            <a href="<?= BASE_URL ?>/index.php?page=tech_dashboard#active-jobs" class="sidebar-link">
                <i class="fa-solid fa-briefcase"></i> Pekerjaan Saya
            </a>
        </li>
        <li>
            <a href="<?= BASE_URL ?>/index.php?page=tech_dashboard#today-schedule" class="sidebar-link">
                <i class="fa-solid fa-calendar-days"></i> Jadwal
            </a>
        </li>
        <li>
            <a href="<?= BASE_URL ?>/index.php?page=tech_history" class="sidebar-link <?= $currentPage === 'tech_history' ? 'active' : '' ?>">
                <i class="fa-solid fa-clock-rotate-left"></i> Riwayat
            </a>
        </li>

        <li class="nav-section-title">Alat Bantu & Akun</li>
        <li>
            <a href="<?= BASE_URL ?>/index.php?page=ai_diagnostic" target="_blank" class="sidebar-link">
                <i class="fa-solid fa-wand-magic-sparkles"></i> AI Triage Kerusakan
            </a>
        </li>
        <li>
            <a href="<?= BASE_URL ?>/index.php?page=tech_profile" class="sidebar-link <?= $currentPage === 'tech_profile' ? 'active' : '' ?>">
                <i class="fa-solid fa-id-badge"></i> Profil
            </a>
        </li>
        <li>
            <a href="<?= BASE_URL ?>/index.php?page=logout" class="sidebar-link" style="color:var(--danger);">
                <i class="fa-solid fa-right-from-bracket"></i> Logout
            </a>
        </li>
    </ul>
</aside>
