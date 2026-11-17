<?php
$pageTitle = 'Manajemen Pelanggan - ServisYuk Admin';
require_once __DIR__ . '/../layouts/header.php';
$flash = Helper::getFlash();
?>

<div class="dashboard-layout">
    <?php require_once __DIR__ . '/../layouts/sidebar_admin.php'; ?>

    <main class="dashboard-main">
        <header class="dashboard-topbar">
            <div class="topbar-title">
                <h2>Data Pelanggan Terdaftar</h2>
                <p>Seluruh customer yang pernah mendaftar dan melakukan permintaan servis.</p>
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
                    <h3>Daftar Pelanggan (<?= count($customers) ?> Orang)</h3>
                </div>

                <div class="table-responsive">
                    <table class="custom-table">
                        <thead>
                            <tr>
                                <th>Pelanggan</th>
                                <th>Kontak WhatsApp / HP</th>
                                <th>Alamat Rumah</th>
                                <th>Total Permintaan</th>
                                <th>Bergabung Pada</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($customers as $c): ?>
                                <tr>
                                    <td>
                                        <div style="display: flex; align-items: center; gap: 10px;">
                                            <div class="user-avatar" style="width: 36px; height: 36px; font-size: 0.85rem;">
                                                <?= strtoupper(substr($c['name'], 0, 1)) ?>
                                            </div>
                                            <div>
                                                <div style="font-weight: 700;"><?= htmlspecialchars($c['name']) ?></div>
                                                <div style="font-size: 0.78rem; color: var(--text-muted);"><?= htmlspecialchars($c['email']) ?></div>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <?php if (!empty($c['phone'])): ?>
                                            <a href="https://wa.me/<?= preg_replace('/[^0-9]/', '', $c['phone']) ?>" target="_blank" style="color: var(--success); font-weight: 600;">
                                                <i class="fa-brands fa-whatsapp"></i> <?= htmlspecialchars($c['phone']) ?>
                                            </a>
                                        <?php else: ?>
                                            <span style="color: var(--text-muted);">-</span>
                                        <?php endif; ?>
                                    </td>
                                    <td style="max-width: 250px; font-size: 0.85rem; color: var(--text-muted);">
                                        <?= htmlspecialchars($c['address'] ?: '-') ?>
                                    </td>
                                    <td>
                                        <span class="badge badge-primary">
                                            <?= $c['total_orders'] ?> Permintaan
                                        </span>
                                    </td>
                                    <td style="font-size: 0.82rem; color: var(--text-muted);">
                                        <?= Helper::formatDateIndo($c['created_at']) ?>
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

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
