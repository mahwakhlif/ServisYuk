<?php
$pageTitle = 'Riwayat Pekerjaan Selesai - ServisYuk';
require_once __DIR__ . '/../layouts/header.php';
$flash = Helper::getFlash();
?>

<div class="dashboard-layout">
    <?php require_once __DIR__ . '/../layouts/sidebar_technician.php'; ?>

    <main class="dashboard-main">
        <header class="dashboard-topbar">
            <div class="topbar-title">
                <h2>Riwayat Semua Pekerjaan Selesai</h2>
                <p>Arsip seluruh tugas yang telah Anda selesaikan beserta laporan pengerjaannya.</p>
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
                <?php if (empty($jobs)): ?>
                    <div style="padding: 40px; text-align: center; color: var(--text-muted);">
                        <i class="fa-solid fa-folder-open" style="font-size: 2.5rem; color: #cbd5e1; margin-bottom: 12px; display:block;"></i>
                        <p style="font-weight: 600;">Belum ada riwayat tugas pengerjaan yang selesai.</p>
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="custom-table">
                            <thead>
                                <tr>
                                    <th>Kode Tiket</th>
                                    <th>No. Faktur</th>
                                    <th>Peralatan</th>
                                    <th>Pelanggan</th>
                                    <th>Waktu Selesai</th>
                                    <th>Total Biaya</th>
                                    <th>Status Bayar</th>
                                    <th style="text-align: right;">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($jobs as $j): 
                                    $jCode = $j['request_code'] ?? ($j['booking_code'] ?? 'SRV');
                                    $appName = $j['appliance_name'] ?? ($j['category_name'] ?? 'Alat');
                                ?>
                                    <tr>
                                        <td style="font-weight: 700; color: var(--primary);"><?= htmlspecialchars($jCode) ?></td>
                                        <td style="font-size: 0.85rem; font-weight: 600;"><?= htmlspecialchars($j['invoice_number'] ?? '-') ?></td>
                                        <td>
                                            <div style="font-weight: 600;"><?= htmlspecialchars($appName) ?></div>
                                            <div style="font-size: 0.8rem; color: var(--text-muted);"><?= htmlspecialchars($j['brand'] ?? '-') ?></div>
                                        </td>
                                        <td>
                                            <div><?= htmlspecialchars($j['customer_name'] ?? 'Pelanggan') ?></div>
                                            <div style="font-size: 0.8rem; color: var(--text-muted);"><?= htmlspecialchars($j['customer_phone'] ?? '-') ?></div>
                                        </td>
                                        <td>
                                            <div><?= Helper::formatDateIndo($j['completed_at'] ?? null, true) ?></div>
                                        </td>
                                        <td style="font-weight: 700;">
                                            <?= !empty($j['total_amount']) ? Helper::formatRupiah($j['total_amount']) : '-' ?>
                                        </td>
                                        <td>
                                            <span class="badge <?= ($j['payment_status'] ?? '') === 'paid' ? 'badge-success' : 'badge-warning' ?>" style="font-size: 0.75rem;">
                                                <?= ($j['payment_status'] ?? '') === 'paid' ? 'Lunas' : 'Belum Bayar' ?>
                                            </span>
                                        </td>
                                        <td style="text-align: right;">
                                            <a href="<?= BASE_URL ?>/index.php?page=tech_job_detail&id=<?= $j['id'] ?>" class="btn btn-secondary btn-sm">
                                                <i class="fa-solid fa-eye"></i> Detail
                                            </a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </main>
</div>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
