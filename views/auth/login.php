<?php
$pageTitle = 'Masuk ke Sistem - ServisYuk';
require_once __DIR__ . '/../layouts/header.php';
require_once __DIR__ . '/../layouts/navbar.php';
$flash = Helper::getFlash();
?>

<div class="container" style="padding: 60px 24px 80px; max-width: 480px;">
    <div class="card" style="box-shadow: var(--shadow-xl); border-radius: var(--radius-xl);">
        <div style="text-align: center; margin-bottom: 28px;">
            <div class="logo-icon" style="width: 48px; height: 48px; margin: 0 auto 12px; font-size: 1.4rem;">
                <i class="fa-solid fa-lock"></i>
            </div>
            <h1 style="font-size: 1.8rem; font-weight: 800;">Masuk ke ServisYuk</h1>
            <p style="color: var(--text-muted); font-size: 0.9rem;">Silakan masukkan email & kata sandi akun Anda</p>
        </div>

        <?php if ($flash): ?>
            <div class="alert alert-<?= $flash['type'] ?>">
                <i class="fa-solid fa-circle-info"></i>
                <?= htmlspecialchars($flash['message']) ?>
            </div>
        <?php endif; ?>

        <form action="<?= BASE_URL ?>/index.php?page=login" method="POST">
            <?= Helper::csrfField() ?>
            <div class="form-group">
                <label class="form-label">Alamat Email <span class="required">*</span></label>
                <input type="email" name="email" id="input_email" class="form-control" placeholder="nama@email.com" value="<?= htmlspecialchars($_POST['email'] ?? '') ?>" required autofocus>
            </div>

            <div class="form-group">
                <label class="form-label">Kata Sandi <span class="required">*</span></label>
                <input type="password" name="password" id="input_password" class="form-control" placeholder="••••••••" required>
            </div>

            <button type="submit" class="btn btn-primary btn-block btn-lg" style="margin-top: 10px;">
                <i class="fa-solid fa-right-to-bracket"></i> Masuk Sekarang
            </button>
        </form>

        <!-- Quick Demo Account Switcher for easy testing -->
        <div style="margin-top: 28px; padding-top: 20px; border-top: 1px dashed var(--border-color);">
            <div style="font-size: 0.8rem; font-weight: 700; color: var(--text-muted); text-transform: uppercase; text-align: center; margin-bottom: 12px;">
                ⚡ Akses Cepat Akun Demo (1-Klik)
            </div>
            <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 8px;">
                <button type="button" class="btn btn-secondary btn-sm" onclick="setDemo('admin@jasa.com', 'admin123')">
                    <i class="fa-solid fa-user-shield"></i> Admin
                </button>
                <button type="button" class="btn btn-secondary btn-sm" onclick="setDemo('budi@jasa.com', 'teknisi123')">
                    <i class="fa-solid fa-screwdriver-wrench"></i> Teknisi
                </button>
                <button type="button" class="btn btn-secondary btn-sm" onclick="setDemo('customer@jasa.com', 'customer123')">
                    <i class="fa-solid fa-user"></i> Customer
                </button>
            </div>
        </div>

        <div style="text-align: center; margin-top: 24px; font-size: 0.9rem; color: var(--text-muted);">
            Belum punya akun? <a href="<?= BASE_URL ?>/index.php?page=register" style="font-weight: 700;">Daftar Akun Baru</a>
        </div>
    </div>
</div>

<script>
function setDemo(email, pass) {
    document.getElementById('input_email').value = email;
    document.getElementById('input_password').value = pass;
}
</script>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
