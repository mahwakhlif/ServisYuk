<?php
$pageTitle = 'Laporan Keuangan & Transaksi - ServisYuk Admin';
require_once __DIR__ . '/../layouts/header.php';
$flash = Helper::getFlash();
$selectedMonth = $_GET['month'] ?? date('Y-m');
?>

<div class="dashboard-layout">
    <?php require_once __DIR__ . '/../layouts/sidebar_admin.php'; ?>

    <main class="dashboard-main">
        <header class="dashboard-topbar">
            <div class="topbar-title">
                <h2>Laporan Keuangan & Transaksi Servis</h2>
                <p>Rekapitulasi pendapatan jasa, suku cadang, dan rincian transaksi bulanan.</p>
            </div>
            <div class="topbar-actions">
                <button type="button" class="btn btn-primary btn-sm btn-print-invoice" onclick="window.print()">
                    <i class="fa-solid fa-print"></i> Cetak Laporan Bulanan
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

            <!-- Month Filter -->
            <div class="card no-print" style="margin-bottom: 24px; padding: 18px 24px;">
                <form action="<?= BASE_URL ?>/index.php" method="GET" style="display: flex; align-items: center; gap: 16px; flex-wrap: wrap;">
                    <input type="hidden" name="page" value="admin_reports">
                    <label class="form-label" style="margin-bottom: 0;">Pilih Periode Bulan:</label>
                    <input type="month" name="month" class="form-control" style="max-width: 220px;" value="<?= htmlspecialchars($selectedMonth) ?>">
                    <button type="submit" class="btn btn-primary btn-sm">
                        <i class="fa-solid fa-filter"></i> Tampilkan Data
                    </button>
                </form>
            </div>

            <!-- Financial Summary KPI -->
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-icon success"><i class="fa-solid fa-sack-dollar"></i></div>
                    <div class="stat-info">
                        <div class="stat-value" style="font-size: 1.4rem; color: var(--success);"><?= Helper::formatRupiah($reportSummary['total_income']) ?></div>
                        <div class="stat-label">Total Pendapatan Terverifikasi</div>
                    </div>
                </div>

                <div class="stat-card">
                    <div class="stat-icon primary"><i class="fa-solid fa-circle-check"></i></div>
                    <div class="stat-info">
                        <div class="stat-value"><?= $reportSummary['completed_orders'] ?> Order</div>
                        <div class="stat-label">Perbaikan Selesai</div>
                    </div>
                </div>

                <div class="stat-card">
                    <div class="stat-icon info"><i class="fa-solid fa-hand-holding-dollar"></i></div>
                    <div class="stat-info">
                        <div class="stat-value" style="font-size: 1.3rem;"><?= Helper::formatRupiah($reportSummary['total_service_fee']) ?></div>
                        <div class="stat-label">Total Pendapatan Jasa</div>
                    </div>
                </div>

                <div class="stat-card">
                    <div class="stat-icon warning"><i class="fa-solid fa-boxes-packing"></i></div>
                    <div class="stat-info">
                        <div class="stat-value" style="font-size: 1.3rem;"><?= Helper::formatRupiah($reportSummary['total_sparepart_fee']) ?></div>
                        <div class="stat-label">Total Penjualan Sparepart</div>
                    </div>
                </div>
            </div>

            <!-- Full Monthly Transactions Table -->
            <div class="table-card">
                <div class="table-header">
                    <h3><i class="fa-solid fa-list-ol" style="color:var(--primary); margin-right:8px;"></i> Rincian Buku Kas Transaksi Periode <?= htmlspecialchars($selectedMonth) ?></h3>
                </div>

                <div class="table-responsive">
                    <table class="custom-table">
                        <thead>
                            <tr>
                                <th>No. Faktur</th>
                                <th>Kode Tiket</th>
                                <th>Peralatan</th>
                                <th>Waktu Selesai</th>
                                <th>Pelanggan</th>
                                <th>Teknisi</th>
                                <th>Biaya Jasa</th>
                                <th>Biaya Sparepart</th>
                                <th>Total</th>
                                <th>Status Bayar</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($transactions)): ?>
                                <tr>
                                    <td colspan="10" style="text-align: center; color: var(--text-muted); padding: 30px;">
                                        Tidak ada transaksi laporan perbaikan pada periode bulan ini.
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($transactions as $tr): ?>
                                    <tr>
                                        <td style="font-weight: 700;"><?= htmlspecialchars($tr['invoice_number'] ?: '-') ?></td>
                                        <td style="color: var(--primary); font-weight: 600;"><?= htmlspecialchars($tr['request_code']) ?></td>
                                        <td>
                                            <div style="font-weight: 600;"><?= htmlspecialchars($tr['appliance_name']) ?></div>
                                            <div style="font-size: 0.78rem; color: var(--text-muted);"><?= htmlspecialchars($tr['brand'] ?? '-') ?></div>
                                        </td>
                                        <td><?= Helper::formatDateIndo($tr['completed_at'], true) ?></td>
                                        <td><?= htmlspecialchars($tr['customer_name']) ?></td>
                                        <td><?= htmlspecialchars($tr['technician_name'] ?: '-') ?></td>
                                        <td><?= Helper::formatRupiah($tr['service_fee']) ?></td>
                                        <td><?= Helper::formatRupiah($tr['sparepart_fee']) ?></td>
                                        <td style="font-weight: 800; color: var(--primary);"><?= Helper::formatRupiah($tr['total_amount']) ?></td>
                                        <td>
                                            <span class="badge <?= $tr['payment_status'] === 'paid' ? 'badge-success' : 'badge-warning' ?>" style="font-size: 0.72rem;">
                                                <?= $tr['payment_status'] === 'paid' ? 'LUNAS' : 'PENDING' ?>
                                            </span>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </main>
</div>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
