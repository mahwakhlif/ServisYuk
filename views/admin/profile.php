<?php
$pageTitle = 'Profil Administrator - ServisYuk';
require_once __DIR__ . '/../layouts/header.php';
$flash = Helper::getFlash();
?>

<div class="dashboard-layout">
    <?php require_once __DIR__ . '/../layouts/sidebar_admin.php'; ?>

    <main class="dashboard-main">
        <header class="dashboard-topbar">
            <div class="topbar-title">
                <h2>Profil Administrator</h2>
                <p>Kelola data akun dan kata sandi akses super admin.</p>
            </div>
        </header>

        <div class="dashboard-content" style="max-width: 720px;">
            <?php if ($flash): ?>
                <div class="alert alert-<?= $flash['type'] ?>">
                    <i class="fa-solid fa-circle-info"></i>
                    <?= htmlspecialchars($flash['message']) ?>
                </div>
            <?php endif; ?>

            <div class="card">
                <form action="<?= BASE_URL ?>/index.php?page=admin_profile" method="POST">
                    <?= Helper::csrfField() ?>

                    <div style="display: flex; align-items: center; gap: 20px; margin-bottom: 24px; padding-bottom: 20px; border-bottom: 1px solid var(--border-color);">
                        <div class="user-avatar" style="width: 64px; height: 64px; font-size: 1.5rem; background: var(--accent-gradient);">
                            <i class="fa-solid fa-user-shield"></i>
                        </div>
                        <div>
                            <h3 style="font-size: 1.2rem; margin-bottom: 2px;"><?= htmlspecialchars($user['name']) ?></h3>
                            <div style="font-size: 0.85rem; color: var(--text-muted);"><?= htmlspecialchars($user['email']) ?></div>
                            <span class="badge badge-primary" style="margin-top: 6px;">Super Administrator</span>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Nama Lengkap Admin <span class="required">*</span></label>
                        <input type="text" name="name" class="form-control" value="<?= htmlspecialchars($user['name']) ?>" required>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Alamat Email (Login)</label>
                        <input type="email" class="form-control" value="<?= htmlspecialchars($user['email']) ?>" disabled style="background:#f1f5f9; cursor:not-allowed;">
                    </div>

                    <div class="form-group">
                        <label class="form-label">Nomor Telepon / WhatsApp</label>
                        <input type="tel" name="phone" class="form-control" value="<?= htmlspecialchars($user['phone'] ?? '') ?>">
                    </div>

                    <div class="form-group">
                        <label class="form-label">Alamat Kantor / Domisili</label>
                        <textarea name="address" class="form-control" rows="2"><?= htmlspecialchars($user['address'] ?? '') ?></textarea>
                    </div>

                    <h4 style="font-size: 1.05rem; margin: 24px 0 12px; padding-top: 16px; border-top: 1px solid var(--border-color);">
                        Ubah Kata Sandi Admin (Opsional)
                    </h4>

                    <div class="form-group">
                        <label class="form-label">Kata Sandi Baru</label>
                        <input type="password" name="new_password" class="form-control" placeholder="Biarkan kosong jika tidak ingin mengubah sandi">
                    </div>

                    <div style="margin-top: 24px; text-align: right;">
                        <button type="submit" class="btn btn-primary">
                            <i class="fa-solid fa-floppy-disk"></i> Simpan Profil Admin
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </main>
</div>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
