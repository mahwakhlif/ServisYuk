<?php
$pageTitle = 'Pendaftaran Akun - ServisYuk';
require_once __DIR__ . '/../layouts/header.php';
require_once __DIR__ . '/../layouts/navbar.php';
$flash = Helper::getFlash();
$selectedRole = htmlspecialchars($_POST['role'] ?? 'customer');
?>

<div class="container" style="padding: 50px 24px 80px; max-width: 600px;">
    <div class="card" style="box-shadow: var(--shadow-xl); border-radius: var(--radius-xl);">
        <div style="text-align: center; margin-bottom: 28px;">
            <div class="logo-icon" style="width: 48px; height: 48px; margin: 0 auto 12px; font-size: 1.4rem;">
                <i class="fa-solid fa-user-plus"></i>
            </div>
            <h1 style="font-size: 1.8rem; font-weight: 800;">Daftar Akun Baru</h1>
            <p style="color: var(--text-muted); font-size: 0.9rem;">Buat akun sebagai Pelanggan, Teknisi, atau Administrator</p>
        </div>

        <?php if ($flash): ?>
            <div class="alert alert-<?= $flash['type'] ?>">
                <i class="fa-solid fa-circle-info"></i>
                <?= htmlspecialchars($flash['message']) ?>
            </div>
        <?php endif; ?>

        <form action="<?= BASE_URL ?>/index.php?page=register" method="POST" id="register-form">
            <?= Helper::csrfField() ?>

            <!-- Role Selection Cards -->
            <div class="form-group">
                <label class="form-label" style="font-weight: 700; font-size: 0.95rem;">Daftar Sebagai <span class="required">*</span></label>
                <div id="role-cards" style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 12px; margin-top: 8px;">
                    <!-- Customer -->
                    <label class="role-card <?= $selectedRole === 'customer' ? 'role-card-active' : '' ?>" data-role="customer" style="cursor: pointer; display: flex; flex-direction: column; align-items: center; gap: 8px; padding: 16px 10px; border-radius: var(--radius-lg); border: 2px solid <?= $selectedRole === 'customer' ? 'var(--primary)' : 'var(--border)' ?>; background: <?= $selectedRole === 'customer' ? 'rgba(37,99,235,0.06)' : 'var(--bg-secondary)' ?>; transition: all 0.25s ease; text-align: center;">
                        <input type="radio" name="role" value="customer" <?= $selectedRole === 'customer' ? 'checked' : '' ?> required style="display:none;">
                        <div style="width: 44px; height: 44px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 1.2rem; background: <?= $selectedRole === 'customer' ? 'var(--primary)' : 'var(--bg-tertiary)' ?>; color: <?= $selectedRole === 'customer' ? '#fff' : 'var(--text-muted)' ?>; transition: all 0.25s ease;">
                            <i class="fa-solid fa-user"></i>
                        </div>
                        <span style="font-weight: 700; font-size: 0.85rem; color: var(--text-primary);">Pelanggan</span>
                        <span style="font-size: 0.72rem; color: var(--text-muted); line-height: 1.3;">Pesan layanan servis & pantau tiket</span>
                    </label>

                    <!-- Technician -->
                    <label class="role-card <?= $selectedRole === 'technician' ? 'role-card-active' : '' ?>" data-role="technician" style="cursor: pointer; display: flex; flex-direction: column; align-items: center; gap: 8px; padding: 16px 10px; border-radius: var(--radius-lg); border: 2px solid <?= $selectedRole === 'technician' ? 'var(--warning)' : 'var(--border)' ?>; background: <?= $selectedRole === 'technician' ? 'rgba(245,158,11,0.06)' : 'var(--bg-secondary)' ?>; transition: all 0.25s ease; text-align: center;">
                        <input type="radio" name="role" value="technician" <?= $selectedRole === 'technician' ? 'checked' : '' ?> style="display:none;">
                        <div style="width: 44px; height: 44px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 1.2rem; background: <?= $selectedRole === 'technician' ? 'var(--warning)' : 'var(--bg-tertiary)' ?>; color: <?= $selectedRole === 'technician' ? '#fff' : 'var(--text-muted)' ?>; transition: all 0.25s ease;">
                            <i class="fa-solid fa-screwdriver-wrench"></i>
                        </div>
                        <span style="font-weight: 700; font-size: 0.85rem; color: var(--text-primary);">Teknisi</span>
                        <span style="font-size: 0.72rem; color: var(--text-muted); line-height: 1.3;">Terima & kerjakan tugas servis</span>
                    </label>

                    <!-- Admin -->
                    <label class="role-card <?= $selectedRole === 'admin' ? 'role-card-active' : '' ?>" data-role="admin" style="cursor: pointer; display: flex; flex-direction: column; align-items: center; gap: 8px; padding: 16px 10px; border-radius: var(--radius-lg); border: 2px solid <?= $selectedRole === 'admin' ? 'var(--success)' : 'var(--border)' ?>; background: <?= $selectedRole === 'admin' ? 'rgba(16,185,129,0.06)' : 'var(--bg-secondary)' ?>; transition: all 0.25s ease; text-align: center;">
                        <input type="radio" name="role" value="admin" <?= $selectedRole === 'admin' ? 'checked' : '' ?> style="display:none;">
                        <div style="width: 44px; height: 44px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 1.2rem; background: <?= $selectedRole === 'admin' ? 'var(--success)' : 'var(--bg-tertiary)' ?>; color: <?= $selectedRole === 'admin' ? '#fff' : 'var(--text-muted)' ?>; transition: all 0.25s ease;">
                            <i class="fa-solid fa-shield-halved"></i>
                        </div>
                        <span style="font-weight: 700; font-size: 0.85rem; color: var(--text-primary);">Admin</span>
                        <span style="font-size: 0.72rem; color: var(--text-muted); line-height: 1.3;">Kelola seluruh sistem & data</span>
                    </label>
                </div>
            </div>

            <div class="form-group">
                <label class="form-label">Nama Lengkap <span class="required">*</span></label>
                <input type="text" name="name" class="form-control" placeholder="Contoh: Rina Kusuma" value="<?= htmlspecialchars($_POST['name'] ?? '') ?>" required>
            </div>

            <div class="form-group">
                <label class="form-label">Alamat Email <span class="required">*</span></label>
                <input type="email" name="email" class="form-control" placeholder="nama@email.com" value="<?= htmlspecialchars($_POST['email'] ?? '') ?>" required>
            </div>

            <div class="form-group">
                <label class="form-label" style="display: flex; justify-content: space-between; align-items: center;">
                    <span><i class="fa-brands fa-whatsapp" style="color: #25D366;"></i> Nomor WhatsApp Aktif <span class="required">*</span></span>
                    <span class="badge" style="background: rgba(37,211,102,0.12); color: #15803d; font-size: 0.72rem; font-weight: 600;">Wajib OTP</span>
                </label>
                <input type="tel" name="phone" class="form-control" placeholder="Contoh: 081234567890" value="<?= htmlspecialchars($_POST['phone'] ?? '') ?>" required>
                <small style="color: var(--text-muted); font-size: 0.8rem; display: block; margin-top: 5px; line-height: 1.4;">
                    <i class="fa-solid fa-shield-halved" style="color: var(--primary); margin-right: 4px;"></i> Kode OTP 6-digit akan dikirimkan ke nomor WhatsApp ini untuk verifikasi akun Anda.
                </small>
            </div>

            <div class="form-group">
                <label class="form-label">Alamat Lengkap Rumah</label>
                <textarea name="address" class="form-control" placeholder="Alamat jalan, nomor rumah, RT/RW, kelurahan, kecamatan..." rows="2"><?= htmlspecialchars($_POST['address'] ?? '') ?></textarea>
            </div>

            <!-- Technician-specific fields -->
            <div id="technician-fields" style="display: <?= $selectedRole === 'technician' ? 'block' : 'none' ?>; padding: 16px; background: rgba(245,158,11,0.04); border: 1px solid rgba(245,158,11,0.15); border-radius: var(--radius-lg); margin-bottom: 16px;">
                <h4 style="font-size: 0.9rem; font-weight: 700; margin-bottom: 12px; color: var(--warning);">
                    <i class="fa-solid fa-screwdriver-wrench" style="margin-right: 6px;"></i> Informasi Teknisi
                </h4>
                <div class="form-group" style="margin-bottom: 10px;">
                    <label class="form-label">Spesialisasi / Keahlian <span class="required">*</span></label>
                    <select name="specialization" class="form-control" id="specialization-select">
                        <option value="">-- Pilih Spesialisasi --</option>
                        <option value="AC & Sistem Pendingin" <?= ($_POST['specialization'] ?? '') === 'AC & Sistem Pendingin' ? 'selected' : '' ?>>AC & Sistem Pendingin</option>
                        <option value="Kulkas & Freezer" <?= ($_POST['specialization'] ?? '') === 'Kulkas & Freezer' ? 'selected' : '' ?>>Kulkas & Freezer</option>
                        <option value="Mesin Cuci" <?= ($_POST['specialization'] ?? '') === 'Mesin Cuci' ? 'selected' : '' ?>>Mesin Cuci</option>
                        <option value="TV LED/Smart TV" <?= ($_POST['specialization'] ?? '') === 'TV LED/Smart TV' ? 'selected' : '' ?>>TV LED / Smart TV</option>
                        <option value="Kompor Gas & Listrik" <?= ($_POST['specialization'] ?? '') === 'Kompor Gas & Listrik' ? 'selected' : '' ?>>Kompor Gas & Listrik</option>
                        <option value="Dispenser Air" <?= ($_POST['specialization'] ?? '') === 'Dispenser Air' ? 'selected' : '' ?>>Dispenser Air</option>
                        <option value="Microwave & Oven" <?= ($_POST['specialization'] ?? '') === 'Microwave & Oven' ? 'selected' : '' ?>>Microwave & Oven</option>
                        <option value="Multi-Spesialis" <?= ($_POST['specialization'] ?? '') === 'Multi-Spesialis' ? 'selected' : '' ?>>Multi-Spesialis (Semua Kategori)</option>
                    </select>
                </div>
                <div class="form-group" style="margin-bottom: 0;">
                    <label class="form-label">Pengalaman</label>
                    <select name="experience" class="form-control">
                        <option value="1 Tahun" <?= ($_POST['experience'] ?? '') === '1 Tahun' ? 'selected' : '' ?>>1 Tahun</option>
                        <option value="2 Tahun" <?= ($_POST['experience'] ?? '') === '2 Tahun' ? 'selected' : '' ?>>2 Tahun</option>
                        <option value="3 Tahun" <?= ($_POST['experience'] ?? '3 Tahun') === '3 Tahun' ? 'selected' : '' ?>>3 Tahun</option>
                        <option value="5 Tahun" <?= ($_POST['experience'] ?? '') === '5 Tahun' ? 'selected' : '' ?>>5+ Tahun</option>
                        <option value="10 Tahun" <?= ($_POST['experience'] ?? '') === '10 Tahun' ? 'selected' : '' ?>>10+ Tahun</option>
                    </select>
                </div>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px;">
                <div class="form-group">
                    <label class="form-label">Kata Sandi <span class="required">*</span></label>
                    <input type="password" name="password" class="form-control" placeholder="Min. 6 karakter" required>
                </div>

                <div class="form-group">
                    <label class="form-label">Ulangi Sandi <span class="required">*</span></label>
                    <input type="password" name="password_confirmation" class="form-control" placeholder="Ulangi sandi" required>
                </div>
            </div>

            <button type="submit" class="btn btn-primary btn-block btn-lg" style="margin-top: 12px; height: 50px; font-weight: 700;">
                <i class="fa-brands fa-whatsapp"></i> Lanjut Verifikasi OTP WhatsApp
            </button>
        </form>

        <div style="text-align: center; margin-top: 24px; font-size: 0.9rem; color: var(--text-muted);">
            Sudah memiliki akun? <a href="<?= BASE_URL ?>/index.php?page=login" style="font-weight: 700;">Masuk di Sini</a>
        </div>
    </div>
</div>

<script>
(function() {
    const roleColors = {
        customer: { border: 'var(--primary)', bg: 'rgba(37,99,235,0.06)', icon: 'var(--primary)' },
        technician: { border: 'var(--warning)', bg: 'rgba(245,158,11,0.06)', icon: 'var(--warning)' },
        admin: { border: 'var(--success)', bg: 'rgba(16,185,129,0.06)', icon: 'var(--success)' }
    };

    const cards = document.querySelectorAll('.role-card');
    const techFields = document.getElementById('technician-fields');
    const specSelect = document.getElementById('specialization-select');

    cards.forEach(card => {
        card.addEventListener('click', function() {
            const role = this.dataset.role;
            const radio = this.querySelector('input[type="radio"]');
            radio.checked = true;

            // Reset all cards
            cards.forEach(c => {
                c.style.borderColor = 'var(--border)';
                c.style.background = 'var(--bg-secondary)';
                const icon = c.querySelector('div[style*="border-radius: 50%"]');
                if (icon) {
                    icon.style.background = 'var(--bg-tertiary)';
                    icon.style.color = 'var(--text-muted)';
                }
            });

            // Activate selected
            const colors = roleColors[role];
            this.style.borderColor = colors.border;
            this.style.background = colors.bg;
            const icon = this.querySelector('div[style*="border-radius: 50%"]');
            if (icon) {
                icon.style.background = colors.icon;
                icon.style.color = '#fff';
            }

            // Toggle technician fields
            if (role === 'technician') {
                techFields.style.display = 'block';
                specSelect.setAttribute('required', 'required');
            } else {
                techFields.style.display = 'none';
                specSelect.removeAttribute('required');
            }
        });

        // Hover effects
        card.addEventListener('mouseenter', function() {
            if (!this.querySelector('input[type="radio"]').checked) {
                this.style.borderColor = roleColors[this.dataset.role].border;
                this.style.transform = 'translateY(-2px)';
            }
        });
        card.addEventListener('mouseleave', function() {
            if (!this.querySelector('input[type="radio"]').checked) {
                this.style.borderColor = 'var(--border)';
                this.style.transform = 'translateY(0)';
            }
        });
    });
})();
</script>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>

