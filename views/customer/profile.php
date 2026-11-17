<?php
$pageTitle = 'Profil Pengguna - ServisYuk';
require_once __DIR__ . '/../layouts/header.php';
$flash = Helper::getFlash();
?>

<div class="dashboard-layout">
    <?php require_once __DIR__ . '/../layouts/sidebar_customer.php'; ?>

    <main class="dashboard-main">
        <header class="dashboard-topbar">
            <div class="topbar-title">
                <h2>Profil & Pengaturan Akun</h2>
                <p>Kelola data pribadi dan alamat utama rumah Anda.</p>
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
                <form action="<?= BASE_URL ?>/index.php?page=customer_profile" method="POST">
                    <?= Helper::csrfField() ?>

                    <div style="display: flex; align-items: center; gap: 20px; margin-bottom: 24px; padding-bottom: 20px; border-bottom: 1px solid var(--border-color);">
                        <div class="user-avatar" style="width: 64px; height: 64px; font-size: 1.5rem;">
                            <?= strtoupper(substr($user['name'], 0, 1)) ?>
                        </div>
                        <div>
                            <h3 style="font-size: 1.2rem; margin-bottom: 2px;"><?= htmlspecialchars($user['name']) ?></h3>
                            <div style="font-size: 0.85rem; color: var(--text-muted);"><?= htmlspecialchars($user['email']) ?></div>
                            <span class="badge badge-primary" style="margin-top: 6px;">Customer Terverifikasi</span>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Nama Lengkap <span class="required">*</span></label>
                        <input type="text" name="name" class="form-control" value="<?= htmlspecialchars($user['name']) ?>" required>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Alamat Email (Akun)</label>
                        <input type="email" class="form-control" value="<?= htmlspecialchars($user['email']) ?>" disabled style="background:#f1f5f9; cursor:not-allowed;">
                    </div>

                    <div class="form-group">
                        <label class="form-label" style="display:flex; justify-content:space-between; align-items:center;">
                            <span><i class="fa-brands fa-whatsapp" style="color:#25D366; font-size:1.1rem;"></i> Nomor WhatsApp / HP <span class="required">*</span></span>
                            <span class="badge" style="background:#dcfce7; color:#15803d; font-weight:500; font-size:0.75rem;"><i class="fa-solid fa-bell"></i> Notifikasi Aktif</span>
                        </label>
                        <input type="tel" name="phone" class="form-control" placeholder="Contoh: 081234567890" value="<?= htmlspecialchars($user['phone'] ?? '') ?>" required>
                        <small style="color:var(--text-muted); font-size:0.8rem; display:block; margin-top:5px; line-height:1.4;">
                            Nomor WhatsApp ini digunakan untuk menerima pembaruan status tiket servis otomatis dari ServisYuk.
                        </small>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Alamat Lengkap Rumah</label>
                        <textarea name="address" class="form-control" rows="3"><?= htmlspecialchars($user['address'] ?? '') ?></textarea>
                    </div>

                    <div class="form-group">
                        <label class="form-label" style="display:flex; justify-content:space-between; align-items:center;">
                            <span><i class="fa-brands fa-telegram" style="color:#0088cc;"></i> Telegram Chat ID (Opsional)</span>
                            <span class="badge" style="background:#f1f5f9; color:#64748b; font-weight:500; font-size:0.75rem;">Testing Bot</span>
                        </label>
                        <input type="text" name="telegram_chat_id" class="form-control" placeholder="Contoh: 1988757332" value="<?= htmlspecialchars($user['telegram_chat_id'] ?? '') ?>">
                        <small style="color:var(--text-muted); font-size:0.8rem; display:block; margin-top:5px; line-height:1.4;">
                            (Opsional) Update tiket servis juga dapat diterima via bot Telegram <a href="https://t.me/servisyuk_bot" target="_blank" style="color:#0088cc; font-weight:600; text-decoration:underline;">@servisyuk_bot</a>.
                        </small>
                    </div>

                    <h4 style="font-size: 1.05rem; margin: 24px 0 12px; padding-top: 16px; border-top: 1px solid var(--border-color);">
                        Ubah Kata Sandi (Opsional)
                    </h4>

                    <div class="form-group">
                        <label class="form-label">Kata Sandi Baru</label>
                        <input type="password" name="new_password" class="form-control" placeholder="Biarkan kosong jika tidak ingin mengubah sandi">
                    </div>

                    <div style="margin-top: 24px; text-align: right;">
                        <button type="submit" class="btn btn-primary">
                            <i class="fa-solid fa-floppy-disk"></i> Simpan Perubahan Profil
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </main>
</div>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
