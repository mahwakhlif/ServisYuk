<?php
$pageTitle = 'Manajemen Jenis Layanan - ServisYuk Admin';
require_once __DIR__ . '/../layouts/header.php';
$flash = Helper::getFlash();
?>

<div class="dashboard-layout">
    <?php require_once __DIR__ . '/../layouts/sidebar_admin.php'; ?>

    <main class="dashboard-main">
        <header class="dashboard-topbar">
            <div class="topbar-title">
                <h2>Manajemen Jenis Layanan</h2>
                <p>Kelola katalog layanan servis dan estimasi biaya perbaikan.</p>
            </div>
            <div class="topbar-actions">
                <button type="button" class="btn btn-primary btn-sm" data-modal-target="modal_add_srv">
                    <i class="fa-solid fa-circle-plus"></i> Tambah Layanan Baru
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

            <!-- Specific Services Table -->
            <div class="table-card">
                <div class="table-header">
                    <h3><i class="fa-solid fa-list-check" style="color:var(--primary); margin-right:8px;"></i> Daftar Layanan Perbaikan (<?= count($services) ?> Jenis Layanan)</h3>
                    <button type="button" class="btn btn-primary btn-sm" data-modal-target="modal_add_srv">
                        <i class="fa-solid fa-plus"></i> Tambah Layanan
                    </button>
                </div>

                <div class="table-responsive">
                    <table class="custom-table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Nama Layanan</th>
                                <th>Deskripsi</th>
                                <th>Estimasi Biaya Dasar</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($services as $s): ?>
                                <tr>
                                    <td style="font-weight: 700; color: var(--text-muted);">#<?= $s['id'] ?></td>
                                    <td style="font-weight: 700; color: var(--text-main);"><?= htmlspecialchars($s['service_name']) ?></td>
                                    <td style="font-size: 0.85rem; color: var(--text-muted); max-width: 350px;"><?= htmlspecialchars($s['description'] ?? '-') ?></td>
                                    <td style="font-weight: 700; color: var(--primary);">
                                        <?= Helper::formatRupiah($s['estimated_price']) ?>
                                    </td>
                                    <td>
                                        <span class="badge <?= $s['status'] === 'active' ? 'badge-success' : 'badge-danger' ?>">
                                            <?= $s['status'] === 'active' ? 'Aktif' : 'Nonaktif' ?>
                                        </span>
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

<!-- Modal: Tambah Layanan -->
<div id="modal_add_srv" class="modal-backdrop">
    <div class="modal-dialog">
        <form action="<?= BASE_URL ?>/index.php?page=admin_services" method="POST">
            <?= Helper::csrfField() ?>
            <input type="hidden" name="action" value="create_service">

            <div class="modal-header">
                <h4><i class="fa-solid fa-circle-plus" style="color:var(--primary); margin-right:6px;"></i> Tambah Jenis Layanan</h4>
                <button type="button" class="modal-close" data-modal-close>&times;</button>
            </div>

            <div class="modal-body">
                <div class="form-group">
                    <label class="form-label">Nama Layanan <span class="required">*</span></label>
                    <input type="text" name="service_name" class="form-control" placeholder="Contoh: Perbaikan & Pembersihan AC Split" required>
                </div>

                <div class="form-group">
                    <label class="form-label">Deskripsi Layanan</label>
                    <textarea name="description" class="form-control" rows="3" placeholder="Jelaskan cakupan perbaikan untuk layanan ini..."></textarea>
                </div>

                <div class="form-group">
                    <label class="form-label">Estimasi Biaya Dasar (Rp) <span class="required">*</span></label>
                    <input type="number" name="estimated_price" class="form-control" value="75000" min="0" step="5000" required>
                </div>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-modal-close>Batal</button>
                <button type="submit" class="btn btn-primary">Simpan Layanan</button>
            </div>
        </form>
    </div>
</div>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
