<?php
$pageTitle = 'Dashboard Teknisi - ServisYuk';
require_once __DIR__ . '/../layouts/header.php';
$flash = Helper::getFlash();
?>

<div class="dashboard-layout">
    <?php require_once __DIR__ . '/../layouts/sidebar_technician.php'; ?>

    <main class="dashboard-main">
        <header class="dashboard-topbar">
            <div class="topbar-title">
                <h2>Portal Kerja Teknisi</h2>
                <p>Halo, <?= htmlspecialchars(Auth::name()) ?>! Siap melayani perbaikan hari ini.</p>
            </div>
            <div class="topbar-actions">
                <span class="badge badge-success" style="font-size: 0.85rem; padding: 6px 14px;">
                    <i class="fa-solid fa-circle-dot"></i> Status: Aktif Menerima Tugas
                </span>
            </div>
        </header>

        <div class="dashboard-content">
            <?php if ($flash): ?>
                <div class="alert alert-<?= $flash['type'] ?>">
                    <i class="fa-solid fa-circle-info"></i>
                    <?= htmlspecialchars($flash['message']) ?>
                </div>
            <?php endif; ?>

            <!-- Stats Grid -->
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-icon warning"><i class="fa-solid fa-wrench"></i></div>
                    <div class="stat-info">
                        <div class="stat-value"><?= $stats['active_jobs'] ?></div>
                        <div class="stat-label">Tugas Aktif Saat Ini</div>
                    </div>
                </div>

                <div class="stat-card">
                    <div class="stat-icon primary"><i class="fa-solid fa-calendar-day"></i></div>
                    <div class="stat-info">
                        <div class="stat-value"><?= $stats['today_jobs'] ?></div>
                        <div class="stat-label">Jadwal Hari Ini</div>
                    </div>
                </div>

                <div class="stat-card">
                    <div class="stat-icon success"><i class="fa-solid fa-circle-check"></i></div>
                    <div class="stat-info">
                        <div class="stat-value"><?= $stats['completed_jobs'] ?></div>
                        <div class="stat-label">Pekerjaan Selesai</div>
                    </div>
                </div>
            </div>

            <!-- Active Jobs Cards -->
            <div class="table-card">
                <div class="table-header">
                    <h3><i class="fa-solid fa-list-check" style="color:var(--primary); margin-right:8px;"></i> Daftar Tugas Perbaikan Aktif</h3>
                </div>

                <?php if (empty($activeJobs)): ?>
                    <div style="padding: 40px; text-align: center; color: var(--text-muted);">
                        <i class="fa-solid fa-mug-hot" style="font-size: 2.5rem; color: #cbd5e1; margin-bottom: 12px; display:block;"></i>
                        <p style="font-weight: 600;">Belum ada tugas perbaikan aktif yang ditugaskan saat ini.</p>
                        <p style="font-size: 0.88rem; margin-top: 4px;">Admin akan menugaskan pesanan baru sesuai spesialisasi keahlian Anda.</p>
                    </div>
                <?php else: ?>
                    <div style="padding: 20px; display: grid; grid-template-columns: repeat(auto-fill, minmax(360px, 1fr)); gap: 20px;">
                        <?php foreach ($activeJobs as $job): 
                            $stInfo = Helper::getStatusInfo($job['status']);
                            $jCode = $job['request_code'] ?? ($job['booking_code'] ?? 'SRV');
                            $appName = $job['appliance_name'] ?? ($job['category_name'] ?? 'Peralatan');
                            $complaint = $job['complaint'] ?? ($job['issue_description'] ?? '');
                            $custAddress = $job['address'] ?? ($job['customer_address'] ?? '');
                        ?>
                            <div class="card" style="border-top: 4px solid <?= $stInfo['color'] ?>; padding: 20px;">
                                <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 12px;">
                                    <span style="font-weight: 700; color: var(--primary); font-size: 0.95rem;">
                                        <?= htmlspecialchars($jCode) ?>
                                    </span>
                                    <span class="badge <?= $stInfo['class'] ?>" style="font-size: 0.78rem;">
                                        <i class="<?= $stInfo['icon'] ?>"></i> <?= $stInfo['label'] ?>
                                    </span>
                                </div>

                                <h4 style="font-size: 1.15rem; margin-bottom: 4px;">
                                    <?= htmlspecialchars($appName) ?>
                                    <?php if (!empty($job['brand'] ?? $job['appliance_brand'])): ?>
                                        - <?= htmlspecialchars($job['brand'] ?? $job['appliance_brand']) ?>
                                    <?php endif; ?>
                                </h4>

                                <div style="font-size: 0.88rem; color: #334155; margin-bottom: 12px; background: #f8fafc; padding: 10px 12px; border-radius: 6px;">
                                    <i class="fa-solid fa-triangle-exclamation" style="color:var(--warning); margin-right:4px;"></i>
                                    <strong>Keluhan:</strong> <?= htmlspecialchars(substr($complaint, 0, 90)) ?><?= strlen($complaint) > 90 ? '...' : '' ?>
                                </div>

                                <div style="font-size: 0.85rem; color: var(--text-muted); margin-bottom: 8px;">
                                    <i class="fa-solid fa-calendar-day" style="width: 18px;"></i>
                                    <strong>Jadwal:</strong> <?= Helper::formatDateIndo($job['schedule'] ?? ($job['scheduled_date'] ?? null), true) ?>
                                </div>

                                <div style="font-size: 0.85rem; color: var(--text-muted); margin-bottom: 8px;">
                                    <i class="fa-solid fa-user" style="width: 18px;"></i>
                                    <strong>Pelanggan:</strong> <?= htmlspecialchars($job['customer_name']) ?> (<?= htmlspecialchars($job['customer_phone'] ?? '-') ?>)
                                </div>

                                <div style="font-size: 0.85rem; color: var(--text-muted); margin-bottom: 16px;">
                                    <i class="fa-solid fa-location-dot" style="width: 18px; color: var(--danger);"></i>
                                    <strong>Alamat:</strong> <?= htmlspecialchars(substr($custAddress, 0, 70)) ?>...
                                </div>

                                <div style="display: flex; gap: 8px; flex-wrap: wrap;">
                                    <a href="<?= BASE_URL ?>/index.php?page=tech_job_detail&id=<?= $job['id'] ?>" class="btn btn-primary btn-sm btn-block">
                                        <i class="fa-solid fa-screwdriver-wrench"></i> Buka Detail & Update Pengerjaan
                                    </a>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Recent Completed Jobs Table -->
            <?php if (!empty($recentCompleted)): ?>
                <div class="table-card">
                    <div class="table-header">
                        <h3><i class="fa-solid fa-clock-rotate-left" style="color:var(--success); margin-right:8px;"></i> Riwayat Pekerjaan Selesai Terbaru</h3>
                        <a href="<?= BASE_URL ?>/index.php?page=tech_history" class="btn btn-secondary btn-sm">Lihat Semua</a>
                    </div>
                    <div class="table-responsive">
                        <table class="custom-table">
                            <thead>
                                <tr>
                                    <th>Kode Tiket</th>
                                    <th>Peralatan</th>
                                    <th>Pelanggan</th>
                                    <th>Total Biaya</th>
                                    <th>Waktu Selesai</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($recentCompleted as $rc): ?>
                                    <tr>
                                        <td style="font-weight: 700; color: var(--primary);"><?= htmlspecialchars($rc['request_code'] ?? ($rc['booking_code'] ?? 'SRV')) ?></td>
                                        <td><?= htmlspecialchars($rc['appliance_name'] ?? ($rc['category_name'] ?? 'Alat')) ?></td>
                                        <td><?= htmlspecialchars($rc['customer_name']) ?></td>
                                        <td style="font-weight: 700;"><?= Helper::formatRupiah($rc['total_amount']) ?></td>
                                        <td><?= Helper::formatDateIndo($rc['completed_at'] ?? null, true) ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </main>
</div>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
