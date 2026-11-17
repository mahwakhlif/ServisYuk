<?php
$pageTitle = 'Dashboard Admin - ServisYuk';
require_once __DIR__ . '/../layouts/header.php';
$flash = Helper::getFlash();
?>

<div class="dashboard-layout">
    <?php require_once __DIR__ . '/../layouts/sidebar_admin.php'; ?>

    <main class="dashboard-main">
        <header class="dashboard-topbar">
            <div class="topbar-title">
                <h2>Panel Kontrol Administrator</h2>
                <p>Ringkasan performa operasional & analitik transaksi layanan servis.</p>
            </div>
            <div class="topbar-actions">
                <a href="<?= BASE_URL ?>/index.php?page=admin_bookings" class="btn btn-primary btn-sm">
                    <i class="fa-solid fa-list-check"></i> Semua Permintaan
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

            <!-- High Level KPI Stats -->
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-icon primary"><i class="fa-solid fa-clipboard-list"></i></div>
                    <div class="stat-info">
                        <div class="stat-value"><?= $stats['total_bookings'] ?></div>
                        <div class="stat-label">Total Permintaan Masuk</div>
                    </div>
                </div>

                <div class="stat-card">
                    <div class="stat-icon info"><i class="fa-solid fa-screwdriver-wrench"></i></div>
                    <div class="stat-info">
                        <div class="stat-value"><?= $stats['in_progress_bookings'] ?></div>
                        <div class="stat-label">Sedang Dikerjakan (Tahap 3-6)</div>
                    </div>
                </div>

                <div class="stat-card">
                    <div class="stat-icon success"><i class="fa-solid fa-rupiah-sign"></i></div>
                    <div class="stat-info">
                        <div class="stat-value" style="font-size: 1.35rem;"><?= Helper::formatRupiah($stats['total_revenue']) ?></div>
                        <div class="stat-label">Total Pendapatan Terverifikasi</div>
                    </div>
                </div>

                <div class="stat-card">
                    <div class="stat-icon warning"><i class="fa-solid fa-user-gear"></i></div>
                    <div class="stat-info">
                        <div class="stat-value"><?= $stats['total_technicians'] ?></div>
                        <div class="stat-label">Jumlah Teknisi Resmi</div>
                    </div>
                </div>
            </div>

            <!-- Ringkasan Status Permintaan Servis (8 Status Terperinci Sesuai Dropdown & Database) -->
            <?php
            $statusCardsConfig = [
                'Menunggu' => [
                    'icon' => 'fa-solid fa-clock-rotate-left',
                    'icon_bg' => '#fef3c7',
                    'icon_color' => '#d97706',
                    'border_color' => '#fde68a'
                ],
                'Dikonfirmasi' => [
                    'icon' => 'fa-solid fa-clipboard-check',
                    'icon_bg' => '#e0f2fe',
                    'icon_color' => '#0284c7',
                    'border_color' => '#bae6fd'
                ],
                'Teknisi Ditugaskan' => [
                    'icon' => 'fa-solid fa-user-check',
                    'icon_bg' => '#e0e7ff',
                    'icon_color' => '#4338ca',
                    'border_color' => '#c7d2fe'
                ],
                'Dalam Perjalanan' => [
                    'icon' => 'fa-solid fa-motorcycle',
                    'icon_bg' => '#ede9fe',
                    'icon_color' => '#6d28d9',
                    'border_color' => '#ddd6fe'
                ],
                'Pemeriksaan' => [
                    'icon' => 'fa-solid fa-magnifying-glass',
                    'icon_bg' => '#fae8ff',
                    'icon_color' => '#a21caf',
                    'border_color' => '#f5d0fe'
                ],
                'Dalam Perbaikan' => [
                    'icon' => 'fa-solid fa-screwdriver-wrench',
                    'icon_bg' => '#ffedd5',
                    'icon_color' => '#c2410c',
                    'border_color' => '#fed7aa'
                ],
                'Selesai' => [
                    'icon' => 'fa-solid fa-circle-check',
                    'icon_bg' => '#d1fae5',
                    'icon_color' => '#047857',
                    'border_color' => '#a7f3d0'
                ],
                'Dibatalkan' => [
                    'icon' => 'fa-solid fa-circle-xmark',
                    'icon_bg' => '#fee2e2',
                    'icon_color' => '#b91c1c',
                    'border_color' => '#fecaca'
                ]
            ];
            ?>
            <div class="card" style="margin-bottom: 28px; padding: 22px;">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 18px; flex-wrap: wrap; gap: 12px;">
                    <div>
                        <h3 style="font-size: 1.15rem; font-weight: 700; margin: 0; display: flex; align-items: center; gap: 8px;">
                            <i class="fa-solid fa-list-check" style="color: var(--primary);"></i> Ringkasan Status Permintaan Servis
                        </h3>
                        <p style="font-size: 0.84rem; color: var(--text-muted); margin: 4px 0 0 0;">
                            Rincian status aktual 8 tahapan servis yang konsisten dengan sistem & database. Klik kartu untuk melihat daftar tiket.
                        </p>
                    </div>
                    <div style="font-size: 0.82rem; color: var(--text-muted); background: #f8fafc; padding: 6px 14px; border-radius: var(--radius-sm); border: 1px solid var(--border-color);">
                        Total: <strong style="color: var(--primary);"><?= $stats['total_bookings'] ?> Permintaan</strong> &bull; Sedang Berjalan: <strong style="color: var(--info);"><?= $stats['in_progress_bookings'] ?> Permintaan</strong>
                    </div>
                </div>

                <div class="status-cards-grid" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(210px, 1fr)); gap: 14px;">
                    <?php foreach ($statusCardsConfig as $stName => $stCfg): 
                        $cnt = $stats['status_counts'][$stName] ?? 0;
                    ?>
                        <a href="<?= BASE_URL ?>/index.php?page=admin_bookings&status=<?= urlencode($stName) ?>" 
                           class="stat-card" 
                           style="margin-bottom: 0; padding: 14px 16px; text-decoration: none; border-left: 4px solid <?= $stCfg['icon_color'] ?>; display: flex; align-items: center; gap: 14px; border-radius: var(--radius-md); transition: transform 0.2s ease, box-shadow 0.2s ease;">
                            <div class="stat-icon" style="width: 44px; height: 44px; border-radius: 10px; background: <?= $stCfg['icon_bg'] ?>; color: <?= $stCfg['icon_color'] ?>; font-size: 1.15rem;">
                                <i class="<?= $stCfg['icon'] ?>"></i>
                            </div>
                            <div class="stat-info" style="flex: 1; min-width: 0;">
                                <div class="stat-value" style="font-size: 1.45rem; font-weight: 800; color: <?= $cnt > 0 ? 'var(--text-main)' : 'var(--text-muted)' ?>;">
                                    <?= $cnt ?>
                                </div>
                                <div class="stat-label" style="font-size: 0.82rem; font-weight: 600; color: var(--text-main); white-space: nowrap; overflow: hidden; text-overflow: ellipsis;" title="<?= htmlspecialchars($stName) ?>">
                                    <?= htmlspecialchars($stName) ?>
                                </div>
                            </div>
                        </a>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- Two Columns: Recent Requests & Technician List -->
            <div style="display: grid; grid-template-columns: 1.3fr 0.7fr; gap: 24px; margin-bottom: 28px;">
                <!-- Recent Orders Table -->
                <div class="table-card" style="margin-bottom: 0;">
                    <div class="table-header">
                        <h3><i class="fa-solid fa-clock-rotate-left" style="color:var(--primary); margin-right:8px;"></i> Permintaan Servis Terbaru</h3>
                        <a href="<?= BASE_URL ?>/index.php?page=admin_bookings" class="btn btn-secondary btn-sm">Lihat Semua</a>
                    </div>
                    <div class="table-responsive">
                        <table class="custom-table">
                            <thead>
                                <tr>
                                    <th>Kode Tiket</th>
                                    <th>Pelanggan</th>
                                    <th>Peralatan</th>
                                    <th>Status</th>
                                    <th>Teknisi</th>
                                    <th style="text-align: right;">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($recentBookings as $b): 
                                    $stInfo = Helper::getStatusInfo($b['status']);
                                    $bCode = $b['request_code'] ?? ($b['booking_code'] ?? 'SRV');
                                ?>
                                    <tr>
                                        <td style="font-weight: 700; color: var(--primary);">
                                            <?= htmlspecialchars($bCode) ?>
                                        </td>
                                        <td>
                                            <div style="font-weight: 600;"><?= htmlspecialchars($b['customer_name'] ?? 'Pelanggan') ?></div>
                                            <div style="font-size: 0.78rem; color: var(--text-muted);"><?= htmlspecialchars($b['customer_phone'] ?? '-') ?></div>
                                        </td>
                                        <td><?= htmlspecialchars($b['appliance_name'] ?? ($b['category_name'] ?? 'Alat')) ?></td>
                                        <td>
                                            <span class="badge <?= $stInfo['class'] ?>" style="font-size: 0.75rem;">
                                                <?= $stInfo['label'] ?>
                                            </span>
                                        </td>
                                        <td>
                                            <?= htmlspecialchars($b['assigned_technician_name'] ?? ($b['technician_name'] ?? 'Belum Ditugaskan')) ?>
                                        </td>
                                        <td style="text-align: right;">
                                            <a href="<?= BASE_URL ?>/index.php?page=admin_booking_detail&id=<?= $b['id'] ?>" class="btn btn-secondary btn-sm">
                                                Kelola
                                            </a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Technician Performance / Status -->
                <div class="table-card" style="margin-bottom: 0;">
                    <div class="table-header">
                        <h3><i class="fa-solid fa-user-gear" style="color:var(--warning); margin-right:8px;"></i> Daftar Teknisi</h3>
                        <a href="<?= BASE_URL ?>/index.php?page=admin_technicians" class="btn btn-secondary btn-sm">Kelola</a>
                    </div>
                    <div style="padding: 16px;">
                        <?php foreach ($technicians as $tech): ?>
                            <div style="display: flex; align-items: center; justify-content: space-between; padding: 12px 0; border-bottom: 1px solid var(--border-color);">
                                <div style="display: flex; align-items: center; gap: 10px;">
                                    <div class="user-avatar" style="width: 38px; height: 38px; font-size: 0.9rem;">
                                        <?= strtoupper(substr($tech['name'], 0, 1)) ?>
                                    </div>
                                    <div>
                                        <div style="font-weight: 700; font-size: 0.9rem;"><?= htmlspecialchars($tech['name']) ?></div>
                                        <div style="font-size: 0.78rem; color: var(--text-muted);"><?= htmlspecialchars($tech['specialization']) ?></div>
                                    </div>
                                </div>
                                <div style="text-align: right;">
                                    <span class="badge <?= $tech['availability'] === 'available' ? 'badge-success' : 'badge-warning' ?>" style="font-size: 0.75rem;">
                                        <?= $tech['availability'] === 'available' ? 'Tersedia' : 'Sibuk' ?>
                                    </span>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>

            <!-- Appliance Distribution Overview -->
            <div class="card">
                <h3 style="font-size: 1.15rem; margin-bottom: 16px;">
                    <i class="fa-solid fa-chart-simple" style="color:var(--primary); margin-right:6px;"></i> Distribusi Permintaan Berdasarkan Jenis Peralatan
                </h3>
                <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(220px, 1fr)); gap: 16px;">
                    <?php foreach ($categoryBreakdown as $cb): ?>
                        <div style="background: #f8fafc; border: 1px solid var(--border-color); border-radius: var(--radius-md); padding: 14px 16px; display: flex; align-items: center; justify-content: space-between;">
                            <span style="font-weight: 600; font-size: 0.88rem;"><?= htmlspecialchars($cb['name']) ?></span>
                            <span class="badge badge-primary" style="font-size: 0.82rem; font-weight: 700;">
                                <?= $cb['count'] ?> Permintaan
                            </span>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </main>
</div>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
