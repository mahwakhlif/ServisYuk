<?php
// ==========================================================
// Layout: Public Navbar
// ==========================================================
$currentPage = $_GET['page'] ?? 'home';
?>
<nav class="navbar">
    <div class="container navbar-container">
        <a href="<?= BASE_URL ?>" class="brand-logo">
            <div class="logo-icon"><i class="fa-solid fa-screwdriver-wrench"></i></div>
            <span>ServisYuk<span class="ai-tag">AI</span></span>
        </a>

        <ul class="nav-links">
            <li><a href="<?= BASE_URL ?>" class="nav-link <?= $currentPage === 'home' ? 'active' : '' ?>"><i class="fa-solid fa-house"></i> Beranda</a></li>
            <li><a href="<?= BASE_URL ?>/index.php?page=services" class="nav-link <?= $currentPage === 'services' ? 'active' : '' ?>"><i class="fa-solid fa-layer-group"></i> Layanan & Tarif</a></li>
            <li><a href="<?= BASE_URL ?>/index.php?page=ai_diagnostic" class="nav-link <?= $currentPage === 'ai_diagnostic' ? 'active' : '' ?>" style="color:var(--primary); font-weight:700;"><i class="fa-solid fa-wand-magic-sparkles"></i> AI Diagnosa</a></li>
            <li><a href="<?= BASE_URL ?>/index.php?page=tracking" class="nav-link <?= $currentPage === 'tracking' ? 'active' : '' ?>"><i class="fa-solid fa-location-crosshairs"></i> Lacak Status</a></li>
        </ul>

        <div class="nav-cta">
            <?php if (Auth::check()): ?>
                <a href="<?= Auth::getRedirectPathByRole(Auth::role()) ?>" class="btn btn-primary btn-sm">
                    <i class="fa-solid fa-gauge-high"></i> Dashboard (<?= ucfirst(Auth::role()) ?>)
                </a>
                <a href="<?= BASE_URL ?>/index.php?page=logout" class="btn btn-secondary btn-sm" title="Keluar">
                    <i class="fa-solid fa-arrow-right-from-bracket"></i>
                </a>
            <?php else: ?>
                <a href="<?= BASE_URL ?>/index.php?page=login" class="btn btn-secondary btn-sm">
                    <i class="fa-solid fa-right-to-bracket"></i> Masuk
                </a>
                <a href="<?= BASE_URL ?>/index.php?page=register" class="btn btn-primary btn-sm">
                    <i class="fa-solid fa-user-plus"></i> Daftar
                </a>
            <?php endif; ?>
        </div>
    </div>
</nav>
