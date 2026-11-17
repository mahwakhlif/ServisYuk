<?php
$pageTitle = 'Dashboard Pelanggan - ServisYuk';
require_once __DIR__ . '/../layouts/header.php';
$flash = Helper::getFlash();
?>

<div class="dashboard-layout">
    <?php require_once __DIR__ . '/../layouts/sidebar_customer.php'; ?>

    <main class="dashboard-main">
        <header class="dashboard-topbar">
            <div class="topbar-title">
                <h2>Portal Pelanggan</h2>
                <p>Selamat datang kembali, <?= htmlspecialchars(Auth::name()) ?>!</p>
            </div>
            <div class="topbar-actions">
                <a href="<?= BASE_URL ?>/index.php?page=customer_booking_create" class="btn btn-primary btn-sm">
                    <i class="fa-solid fa-plus"></i> Pesan Perbaikan
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

            <!-- Stats Grid -->
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-icon primary"><i class="fa-solid fa-calendar-check"></i></div>
                    <div class="stat-info">
                        <div class="stat-value"><?= $stats['total_bookings'] ?></div>
                        <div class="stat-label">Total Pemesanan</div>
                    </div>
                </div>

                <div class="stat-card">
                    <div class="stat-icon warning"><i class="fa-solid fa-clock-rotate-left"></i></div>
                    <div class="stat-info">
                        <div class="stat-value"><?= $stats['active_bookings'] ?></div>
                        <div class="stat-label">Sedang Diproses</div>
                    </div>
                </div>

                <div class="stat-card">
                    <div class="stat-icon success"><i class="fa-solid fa-circle-check"></i></div>
                    <div class="stat-info">
                        <div class="stat-value"><?= $stats['completed_bookings'] ?></div>
                        <div class="stat-label">Perbaikan Selesai</div>
                    </div>
                </div>
            </div>

            <!-- Active Bookings Banner & List -->
            <div class="table-card">
                <div class="table-header">
                    <h3><i class="fa-solid fa-spinner fa-spin" style="color:var(--primary); margin-right:8px;"></i> Perbaikan yang Sedang Berjalan</h3>
                    <a href="<?= BASE_URL ?>/index.php?page=customer_booking_create" class="btn btn-primary btn-sm">
                        <i class="fa-solid fa-plus"></i> Pesan Layanan Baru
                    </a>
                </div>

                <?php if (empty($activeBookings)): ?>
                    <div style="padding: 40px; text-align: center; color: var(--text-muted);">
                        <i class="fa-solid fa-clipboard-check" style="font-size: 2.5rem; color: #cbd5e1; margin-bottom: 12px; display:block;"></i>
                        <p style="font-weight: 600;">Tidak ada perbaikan yang sedang berjalan saat ini.</p>
                        <p style="font-size: 0.88rem; margin-top: 4px;">Peralatan rumah tangga Anda bermasalah? Buat pesanan baru sekarang.</p>
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="custom-table">
                            <thead>
                                <tr>
                                    <th>Kode Tiket</th>
                                    <th>Peralatan</th>
                                    <th>Jadwal Kunjungan</th>
                                    <th>Status Terkini</th>
                                    <th>Teknisi</th>
                                    <th style="text-align: right;">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($activeBookings as $b): 
                                    $stInfo = Helper::getStatusInfo($b['status']);
                                ?>
                                    <tr>
                                        <td style="font-weight: 700; color: var(--primary);">
                                            <?= htmlspecialchars($b['booking_code']) ?>
                                        </td>
                                        <td>
                                            <div style="font-weight: 600;"><?= htmlspecialchars($b['category_name']) ?></div>
                                            <div style="font-size: 0.8rem; color: var(--text-muted);"><?= htmlspecialchars($b['appliance_brand'] . ' ' . $b['appliance_model']) ?></div>
                                        </td>
                                        <td>
                                            <div><?= Helper::formatDateIndo($b['scheduled_date']) ?></div>
                                            <div style="font-size: 0.8rem; color: var(--text-muted);"><?= htmlspecialchars($b['scheduled_time']) ?></div>
                                        </td>
                                        <td>
                                            <span class="badge <?= $stInfo['class'] ?>">
                                                <i class="<?= $stInfo['icon'] ?>"></i> <?= $stInfo['label'] ?>
                                            </span>
                                        </td>
                                        <td>
                                            <?php if (!empty($b['technician_name'])): ?>
                                                <div style="font-weight: 600;"><?= htmlspecialchars($b['technician_name']) ?></div>
                                                <a href="https://wa.me/<?= preg_replace('/[^0-9]/', '', $b['technician_phone']) ?>" target="_blank" style="font-size: 0.8rem; color: var(--success);">
                                                    <i class="fa-brands fa-whatsapp"></i> Chat WA
                                                </a>
                                            <?php else: ?>
                                                <span style="color: var(--text-muted); font-size: 0.85rem;">Menunggu Penugasan</span>
                                            <?php endif; ?>
                                        </td>
                                        <td style="text-align: right;">
                                            <a href="<?= BASE_URL ?>/index.php?page=customer_booking_detail&id=<?= $b['id'] ?>" class="btn btn-secondary btn-sm">
                                                <i class="fa-solid fa-eye"></i> Detail & Tracking
                                            </a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Quick AI Assistant Banner -->
            <div style="background: linear-gradient(135deg, #1e1b4b 0%, #312e81 100%); border-radius: var(--radius-lg); padding: 24px 30px; color: #ffffff; display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 16px; margin-bottom: 28px;">
                <div>
                    <span class="badge badge-info" style="margin-bottom: 8px;"><i class="fa-solid fa-wand-magic-sparkles"></i> AI Triage Tool</span>
                    <h3 style="font-size: 1.3rem; color: #ffffff; margin-bottom: 4px;">Punya Masalah Baru pada Peralatan Lain?</h3>
                    <p style="font-size: 0.88rem; color: #cbd5e1;">Gunakan asisten diagnosa AI untuk mengecek penyebab kerusakan dan estimasi biaya sebelum memesan.</p>
                </div>
                <a href="<?= BASE_URL ?>/index.php?page=ai_diagnostic" class="btn btn-primary" style="white-space: nowrap;">
                    <i class="fa-solid fa-wand-magic-sparkles"></i> Konsultasi AI Sekarang
                </a>
            </div>
        </div>
    </main>
</div>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
