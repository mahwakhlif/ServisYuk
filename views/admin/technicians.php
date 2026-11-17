<?php
$pageTitle = 'Manajemen Teknisi - ServisYuk Admin';
require_once __DIR__ . '/../layouts/header.php';
$flash = Helper::getFlash();
?>

<div class="dashboard-layout">
    <?php require_once __DIR__ . '/../layouts/sidebar_admin.php'; ?>

    <main class="dashboard-main">
        <header class="dashboard-topbar">
            <div class="topbar-title">
                <h2>Manajemen Data Teknisi</h2>
                <p>Kelola teknisi terdaftar, status ketersediaan, dan spesialisasi keahlian.</p>
            </div>
            <div class="topbar-actions">
                <button type="button" class="btn btn-primary btn-sm" data-modal-target="modal_add_tech">
                    <i class="fa-solid fa-user-plus"></i> Tambah Teknisi Baru
                </button>
            </div>
        </header>

        <div class="dashboard-content">
            <?php if ($flash): ?>
                <div class="alert alert-<?= $flash['type'] ?>">
                    <i class="fa-solid fa-circle-info"></i>
                    <?= htmlspecialchars($flash['message']) ?>
                </div>
            <?php endif; ?>

            <div class="table-card">
                <div class="table-header">
                    <h3>Daftar Seluruh Teknisi (<?= count($technicians) ?> Orang)</h3>
                </div>

                <div class="table-responsive">
                    <table class="custom-table">
                        <thead>
                            <tr>
                                <th>Teknisi</th>
                                <th>Spesialisasi</th>
                                <th>Pengalaman</th>
                                <th>Kontak & Alamat</th>
                                <th>Ketersediaan</th>
                                <th style="text-align: right;">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($technicians as $t): ?>
                                <tr>
                                    <td>
                                        <div style="display: flex; align-items: center; gap: 10px;">
                                            <div class="user-avatar" style="width: 36px; height: 36px; font-size: 0.85rem; background:linear-gradient(135deg, #059669 0%, #10b981 100%);">
                                                <?= strtoupper(substr($t['name'], 0, 1)) ?>
                                            </div>
                                            <div>
                                                <div style="font-weight: 700;"><?= htmlspecialchars($t['name']) ?></div>
                                                <div style="font-size: 0.78rem; color: var(--text-muted);"><?= htmlspecialchars($t['email']) ?></div>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="badge badge-info" style="font-size: 0.75rem;">
                                            <?= htmlspecialchars($t['specialization']) ?>
                                        </span>
                                    </td>
                                    <td>
                                        <span style="font-size: 0.85rem; font-weight: 600; color: #475569;">
                                            <?= htmlspecialchars($t['experience']) ?>
                                        </span>
                                    </td>
                                    <td>
                                        <div><?= htmlspecialchars($t['phone'] ?: '-') ?></div>
                                        <div style="font-size: 0.75rem; color: var(--text-muted); max-width: 200px;"><?= htmlspecialchars($t['address'] ?: '-') ?></div>
                                    </td>
                                    <td>
                                        <span class="badge <?= $t['availability'] === 'available' ? 'badge-success' : 'badge-warning' ?>">
                                            <?= $t['availability'] === 'available' ? 'Tersedia' : 'Sedang Bertugas' ?>
                                        </span>
                                    </td>
                                    <td style="text-align: right;">
                                        <form action="<?= BASE_URL ?>/index.php?page=admin_technicians" method="POST" style="display:inline;">
                                            <?= Helper::csrfField() ?>
                                            <input type="hidden" name="action" value="toggle_status">
                                            <input type="hidden" name="technician_id" value="<?= $t['id'] ?>">
                                            <button type="submit" class="btn btn-secondary btn-sm" onclick="return confirm('Ubah status ketersediaan teknisi ini?')">
                                                <i class="fa-solid fa-repeat"></i> <?= $t['availability'] === 'available' ? 'Set Sibuk' : 'Set Tersedia' ?>
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </main>
</div>

<!-- Modal: Tambah Teknisi Baru -->
<div id="modal_add_tech" class="modal-backdrop">
    <div class="modal-dialog">
        <form action="<?= BASE_URL ?>/index.php?page=admin_technicians" method="POST">
            <?= Helper::csrfField() ?>
            <input type="hidden" name="action" value="create">

            <div class="modal-header">
                <h4><i class="fa-solid fa-user-plus" style="color:var(--primary); margin-right:6px;"></i> Tambah Teknisi Baru</h4>
                <button type="button" class="modal-close" data-modal-close>&times;</button>
            </div>

            <div class="modal-body">
                <div class="form-group">
                    <label class="form-label">Nama Lengkap <span class="required">*</span></label>
                    <input type="text" name="name" class="form-control" placeholder="Contoh: Rahmat Hidayat" required>
                </div>

                <div class="form-group">
                    <label class="form-label">Email Login <span class="required">*</span></label>
                    <input type="email" name="email" class="form-control" placeholder="rahmat@jasa.com" required>
                </div>

                <div class="form-group">
                    <label class="form-label">Kata Sandi Default <span class="required">*</span></label>
                    <input type="password" name="password" class="form-control" placeholder="Min. 6 karakter" value="teknisi123" required>
                </div>

                <div class="form-group">
                    <label class="form-label">Spesialisasi Keahlian <span class="required">*</span></label>
                    <input type="text" name="specialization" class="form-control" placeholder="Contoh: Spesialis AC Split & Pendingin" required>
                </div>

                <div class="form-group">
                    <label class="form-label">Pengalaman Kerja</label>
                    <input type="text" name="experience" class="form-control" placeholder="Contoh: 4 Tahun" value="3 Tahun">
                </div>

                <div class="form-group">
                    <label class="form-label">Nomor HP / WhatsApp</label>
                    <input type="tel" name="phone" class="form-control" placeholder="0812xxxxxxxx">
                </div>

                <div class="form-group">
                    <label class="form-label">Alamat Domisili</label>
                    <textarea name="address" class="form-control" rows="2" placeholder="Alamat tinggal teknisi..."></textarea>
                </div>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-modal-close>Batal</button>
                <button type="submit" class="btn btn-primary">
                    <i class="fa-solid fa-check"></i> Simpan Data Teknisi
                </button>
            </div>
        </form>
    </div>
</div>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
