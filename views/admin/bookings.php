<?php
$pageTitle = 'Manajemen Permintaan Jasa - ServisYuk';
require_once __DIR__ . '/../layouts/header.php';
$flash = Helper::getFlash();
$currentStatus = $_GET['status'] ?? '';
$searchQuery = $_GET['q'] ?? '';
?>

<div class="dashboard-layout">
    <?php require_once __DIR__ . '/../layouts/sidebar_admin.php'; ?>

    <main class="dashboard-main">
        <header class="dashboard-topbar">
            <div class="topbar-title">
                <h2>Manajemen Permintaan Servis</h2>
                <p>Pantau seluruh siklus perbaikan, penugasan teknisi, dan status pengerjaan.</p>
            </div>
        </header>

        <div class="dashboard-content">
            <?php if ($flash): ?>
                <div class="alert alert-<?= $flash['type'] ?>">
                    <i class="fa-solid fa-circle-info"></i>
                    <?= htmlspecialchars($flash['message']) ?>
                </div>
            <?php endif; ?>

            <!-- Search & Filters Bar -->
            <div class="card" style="margin-bottom: 24px; padding: 18px 24px;">
                <form action="<?= BASE_URL ?>/index.php" method="GET" style="display: flex; gap: 14px; flex-wrap: wrap; align-items: flex-end;">
                    <input type="hidden" name="page" value="admin_bookings">

                    <div style="flex-grow: 1; min-width: 220px;">
                        <label class="form-label" style="margin-bottom: 4px;">Pencarian (Kode / Nama / HP / Keluhan)</label>
                        <input type="text" name="q" class="form-control" placeholder="Ketik kata kunci..." value="<?= htmlspecialchars($searchQuery) ?>">
                    </div>

                    <div style="min-width: 200px;">
                        <label class="form-label" style="margin-bottom: 4px;">Status Permintaan</label>
                        <select name="status" class="form-select">
                            <option value="">-- Semua Status --</option>
                            <option value="Menunggu" <?= $currentStatus === 'Menunggu' ? 'selected' : '' ?>>Menunggu</option>
                            <option value="Dikonfirmasi" <?= $currentStatus === 'Dikonfirmasi' ? 'selected' : '' ?>>Dikonfirmasi</option>
                            <option value="Teknisi Ditugaskan" <?= $currentStatus === 'Teknisi Ditugaskan' ? 'selected' : '' ?>>Teknisi Ditugaskan</option>
                            <option value="Dalam Perjalanan" <?= $currentStatus === 'Dalam Perjalanan' ? 'selected' : '' ?>>Dalam Perjalanan</option>
                            <option value="Pemeriksaan" <?= $currentStatus === 'Pemeriksaan' ? 'selected' : '' ?>>Pemeriksaan</option>
                            <option value="Dalam Perbaikan" <?= $currentStatus === 'Dalam Perbaikan' ? 'selected' : '' ?>>Dalam Perbaikan</option>
                            <option value="Selesai" <?= $currentStatus === 'Selesai' ? 'selected' : '' ?>>Selesai</option>
                            <option value="Dibatalkan" <?= $currentStatus === 'Dibatalkan' ? 'selected' : '' ?>>Dibatalkan</option>
                        </select>
                    </div>

                    <div>
                        <button type="submit" class="btn btn-primary" style="height: 42px;">
                            <i class="fa-solid fa-filter"></i> Terapkan Filter
                        </button>
                        <a href="<?= BASE_URL ?>/index.php?page=admin_bookings" class="btn btn-secondary" style="height: 42px;">Reset</a>
                    </div>
                </form>
            </div>

            <!-- Table of Bookings -->
            <div class="table-card">
                <div class="table-header">
                    <h3>Daftar Seluruh Permintaan Servis (<?= count($bookings) ?> Data)</h3>
                </div>

                <?php if (empty($bookings)): ?>
                    <div style="padding: 50px; text-align: center; color: var(--text-muted);">
                        <i class="fa-solid fa-folder-open" style="font-size: 2.5rem; color: #cbd5e1; margin-bottom: 12px; display:block;"></i>
                        <p style="font-weight: 600;">Tidak ada data permintaan yang cocok dengan kriteria filter.</p>
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="custom-table">
                            <thead>
                                <tr>
                                    <th>Kode Tiket</th>
                                    <th>Pelanggan</th>
                                    <th>Peralatan</th>
                                    <th>Jadwal Kunjungan</th>
                                    <th>Status Pengerjaan</th>
                                    <th>Teknisi Bertugas</th>
                                    <th>Total Tagihan</th>
                                    <th style="text-align: right;">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($bookings as $b): 
                                    $stInfo = Helper::getStatusInfo($b['status']);
                                    $bCode = $b['request_code'] ?? ($b['booking_code'] ?? 'SRV');
                                    $appName = $b['appliance_name'] ?? ($b['category_name'] ?? 'Alat');
                                    $techName = $b['assigned_technician_name'] ?? ($b['technician_name'] ?? '');
                                ?>
                                    <tr>
                                        <td style="font-weight: 700; color: var(--primary);">
                                            <?= htmlspecialchars($bCode) ?>
                                        </td>
                                        <td>
                                            <div style="font-weight: 600;"><?= htmlspecialchars($b['customer_name'] ?? 'Pelanggan') ?></div>
                                            <div style="font-size: 0.78rem; color: var(--text-muted);"><?= htmlspecialchars($b['customer_phone'] ?? '-') ?></div>
                                        </td>
                                        <td>
                                            <div><?= htmlspecialchars($appName) ?></div>
                                            <div style="font-size: 0.78rem; color: var(--text-muted);"><?= htmlspecialchars($b['brand'] ?? '-') ?></div>
                                        </td>
                                        <td>
                                            <div><?= Helper::formatDateIndo($b['schedule'] ?? ($b['scheduled_date'] ?? null), true) ?></div>
                                        </td>
                                        <td>
                                            <span class="badge <?= $stInfo['class'] ?>" style="font-size: 0.75rem;">
                                                <i class="<?= $stInfo['icon'] ?>"></i> <?= $stInfo['label'] ?>
                                            </span>
                                        </td>
                                        <td>
                                            <?php if (!empty($techName)): ?>
                                                <div style="font-weight: 600; color: #334155;"><?= htmlspecialchars($techName) ?></div>
                                            <?php else: ?>
                                                <span class="badge badge-warning" style="font-size: 0.72rem;">Belum Ditugaskan</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php if (!empty($b['total_amount'])): ?>
                                                <div style="font-weight: 700;"><?= Helper::formatRupiah($b['total_amount']) ?></div>
                                                <span class="badge <?= ($b['payment_status'] ?? '') === 'paid' ? 'badge-success' : 'badge-warning' ?>" style="font-size: 0.7rem;">
                                                    <?= ($b['payment_status'] ?? '') === 'paid' ? 'LUNAS' : 'UNPAID' ?>
                                                </span>
                                            <?php else: ?>
                                                <span style="color: var(--text-muted); font-size: 0.85rem;">Est. <?= Helper::formatRupiah($b['service_estimated_price'] ?? 75000) ?></span>
                                            <?php endif; ?>
                                        </td>
                                        <td style="text-align: right;">
                                            <a href="<?= BASE_URL ?>/index.php?page=admin_booking_detail&id=<?= $b['id'] ?>" class="btn btn-secondary btn-sm">
                                                <i class="fa-solid fa-gear"></i> Kelola
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
