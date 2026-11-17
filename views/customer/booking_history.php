<?php
$pageTitle = 'Riwayat Permintaan Servis - ServisYuk';
require_once __DIR__ . '/../layouts/header.php';
$flash = Helper::getFlash();
$currentStatus = $_GET['status'] ?? '';
?>

<div class="dashboard-layout">
    <?php require_once __DIR__ . '/../layouts/sidebar_customer.php'; ?>

    <main class="dashboard-main">
        <header class="dashboard-topbar">
            <div class="topbar-title">
                <h2>Riwayat Layanan Servis</h2>
                <p>Seluruh riwayat pengajuan perbaikan peralatan rumah tangga Anda.</p>
            </div>
            <div class="topbar-actions">
                <a href="<?= BASE_URL ?>/index.php?page=customer_booking_create" class="btn btn-primary btn-sm">
                    <i class="fa-solid fa-plus"></i> Ajukan Servis Baru
                </a>
            </div>
        </header>

        <div class="dashboard-content">
            <?php if ($flash): ?>
                <div class="alert alert-<?= $flash['type'] ?>">
                    <i class="fa-solid fa-circle-info"></i>
                    <?= htmlspecialchars($flash['message']) ?>
                </div>
            <?php endif; ?>

            <!-- Filter Tabs -->
            <div style="display: flex; gap: 8px; margin-bottom: 24px; flex-wrap: wrap;">
                <a href="<?= BASE_URL ?>/index.php?page=customer_history" class="btn <?= empty($currentStatus) ? 'btn-primary' : 'btn-secondary' ?> btn-sm">
                    Semua Permintaan
                </a>
                <a href="<?= BASE_URL ?>/index.php?page=customer_history&status=Menunggu" class="btn <?= $currentStatus === 'Menunggu' ? 'btn-primary' : 'btn-secondary' ?> btn-sm">
                    Menunggu
                </a>
                <a href="<?= BASE_URL ?>/index.php?page=customer_history&status=Teknisi Ditugaskan" class="btn <?= $currentStatus === 'Teknisi Ditugaskan' ? 'btn-primary' : 'btn-secondary' ?> btn-sm">
                    Teknisi Ditugaskan
                </a>
                <a href="<?= BASE_URL ?>/index.php?page=customer_history&status=Dalam Perbaikan" class="btn <?= $currentStatus === 'Dalam Perbaikan' ? 'btn-primary' : 'btn-secondary' ?> btn-sm">
                    Dalam Pengerjaan
                </a>
                <a href="<?= BASE_URL ?>/index.php?page=customer_history&status=Selesai" class="btn <?= $currentStatus === 'Selesai' ? 'btn-primary' : 'btn-secondary' ?> btn-sm">
                    Selesai
                </a>
            </div>

            <div class="table-card">
                <?php if (empty($bookings)): ?>
                    <div style="padding: 50px 20px; text-align: center; color: var(--text-muted);">
                        <i class="fa-solid fa-receipt" style="font-size: 2.5rem; color: #cbd5e1; margin-bottom: 12px; display:block;"></i>
                        <p style="font-weight: 600;">Tidak ada data permintaan pada filter ini.</p>
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="custom-table">
                            <thead>
                                <tr>
                                    <th>Kode Tiket</th>
                                    <th>Peralatan</th>
                                    <th>Jadwal Kunjungan</th>
                                    <th>Status</th>
                                    <th>Teknisi</th>
                                    <th>Total Biaya</th>
                                    <th style="text-align: right;">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($bookings as $b): 
                                    $stInfo = Helper::getStatusInfo($b['status']);
                                    $bCode = $b['request_code'] ?? ($b['booking_code'] ?? 'SRV');
                                    $appDesc = ($b['appliance_name'] ?? '') . (!empty($b['brand']) ? ' (' . $b['brand'] . ')' : '');
                                ?>
                                    <tr>
                                        <td style="font-weight: 700; color: var(--primary);">
                                            <?= htmlspecialchars($bCode) ?>
                                        </td>
                                        <td>
                                            <div style="font-weight: 600;"><?= htmlspecialchars($appDesc) ?></div>
                                            <div style="font-size: 0.8rem; color: var(--text-muted);"><?= htmlspecialchars($b['service_name'] ?? 'Servis Standar') ?></div>
                                        </td>
                                        <td>
                                            <div><?= Helper::formatDateIndo($b['schedule'] ?? ($b['scheduled_date'] ?? null), true) ?></div>
                                        </td>
                                        <td>
                                            <span class="badge <?= $stInfo['class'] ?>">
                                                <i class="<?= $stInfo['icon'] ?>"></i> <?= $stInfo['label'] ?>
                                            </span>
                                        </td>
                                        <td>
                                            <?= htmlspecialchars($b['assigned_technician_name'] ?? ($b['technician_name'] ?? '-')) ?>
                                        </td>
                                        <td>
                                            <?php if (!empty($b['total_amount'])): ?>
                                                <div style="font-weight: 700;"><?= Helper::formatRupiah($b['total_amount']) ?></div>
                                                <span class="badge <?= ($b['payment_status'] ?? '') === 'paid' ? 'badge-success' : 'badge-warning' ?>" style="font-size: 0.72rem;">
                                                    <?= ($b['payment_status'] ?? '') === 'paid' ? 'Lunas' : 'Belum Bayar' ?>
                                                </span>
                                            <?php else: ?>
                                                <span style="color: var(--text-muted); font-size: 0.85rem;">Est. <?= Helper::formatRupiah($b['service_estimated_price'] ?? 75000) ?></span>
                                            <?php endif; ?>
                                        </td>
                                        <td style="text-align: right;">
                                            <a href="<?= BASE_URL ?>/index.php?page=customer_booking_detail&id=<?= $b['id'] ?>" class="btn btn-secondary btn-sm">
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
