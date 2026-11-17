<?php
$code = $booking['request_code'] ?? ($booking['booking_code'] ?? 'SRV');
$pageTitle = 'Kelola Permintaan Servis #' . htmlspecialchars($code) . ' - ServisYuk Admin';
require_once __DIR__ . '/../layouts/header.php';
$flash = Helper::getFlash();
$stInfo = Helper::getStatusInfo($booking['status'] ?? 'Menunggu');
?>

<div class="dashboard-layout">
    <?php require_once __DIR__ . '/../layouts/sidebar_admin.php'; ?>

    <main class="dashboard-main">
        <header class="dashboard-topbar">
            <div class="topbar-title">
                <h2>Kelola Permintaan Servis #<?= htmlspecialchars($code) ?></h2>
                <p>Status: <span class="badge <?= $stInfo['class'] ?>"><?= $stInfo['label'] ?></span></p>
            </div>
            <div class="topbar-actions">
                <?php if (!empty($report)): ?>
                    <a href="<?= BASE_URL ?>/index.php?page=admin_invoice_print&id=<?= $booking['id'] ?>" target="_blank" class="btn btn-secondary btn-sm">
                        <i class="fa-solid fa-print"></i> Cetak Invoice (<?= htmlspecialchars($report['invoice_number']) ?>)
                    </a>
                <?php endif; ?>
                <a href="<?= BASE_URL ?>/index.php?page=admin_bookings" class="btn btn-secondary btn-sm">
                    <i class="fa-solid fa-arrow-left"></i> Kembali
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

            <!-- Action Controls Row -->
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 24px; margin-bottom: 28px;">
                <!-- Assign Technician Box -->
                <div class="card" style="background: linear-gradient(135deg, #f8fafc 0%, #eef2ff 100%); border-left: 4px solid var(--primary);">
                    <h3 style="font-size: 1.1rem; margin-bottom: 12px;">
                        <i class="fa-solid fa-user-plus" style="color:var(--primary); margin-right:6px;"></i> Penugasan Teknisi
                    </h3>

                    <form action="<?= BASE_URL ?>/index.php?page=admin_assign_technician" method="POST">
                        <?= Helper::csrfField() ?>
                        <input type="hidden" name="booking_id" value="<?= $booking['id'] ?>">

                        <div class="form-group">
                            <label class="form-label">Pilih Teknisi untuk Tugas Ini:</label>
                            <select name="technician_id" class="form-select" required>
                                <option value="">-- Pilih Teknisi Tersedia --</option>
                                <?php foreach ($technicians as $tech): ?>
                                    <option value="<?= $tech['id'] ?>" <?= (($booking['assigned_technician_id'] ?? 0) == $tech['id']) ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($tech['name']) ?> (<?= htmlspecialchars($tech['specialization']) ?>) - <?= $tech['availability'] === 'available' ? 'Tersedia' : 'Sibuk' ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="form-group">
                            <label class="form-label">Catatan Tambahan untuk Teknisi:</label>
                            <input type="text" name="notes" class="form-control" placeholder="Contoh: Harap membawa peralatan manifold gauge / tangga lipat">
                        </div>

                        <button type="submit" class="btn btn-primary btn-sm">
                            <i class="fa-solid fa-check"></i> Tugaskan Teknisi Sekarang
                        </button>
                    </form>
                </div>

                <!-- Update Status Box -->
                <div class="card" style="background: linear-gradient(135deg, #f8fafc 0%, #f0fdf4 100%); border-left: 4px solid var(--success);">
                    <h3 style="font-size: 1.1rem; margin-bottom: 12px;">
                        <i class="fa-solid fa-sliders" style="color:var(--success); margin-right:6px;"></i> Perbarui Status Permintaan
                    </h3>

                    <form action="<?= BASE_URL ?>/index.php?page=admin_update_status" method="POST">
                        <?= Helper::csrfField() ?>
                        <input type="hidden" name="booking_id" value="<?= $booking['id'] ?>">

                        <div class="form-group">
                            <label class="form-label">Status Permintaan:</label>
                            <select name="status" class="form-select">
                                <option value="Menunggu" <?= $booking['status'] === 'Menunggu' ? 'selected' : '' ?>>Menunggu</option>
                                <option value="Dikonfirmasi" <?= $booking['status'] === 'Dikonfirmasi' ? 'selected' : '' ?>>Dikonfirmasi</option>
                                <option value="Teknisi Ditugaskan" <?= $booking['status'] === 'Teknisi Ditugaskan' ? 'selected' : '' ?>>Teknisi Ditugaskan</option>
                                <option value="Dalam Perjalanan" <?= $booking['status'] === 'Dalam Perjalanan' ? 'selected' : '' ?>>Dalam Perjalanan</option>
                                <option value="Pemeriksaan" <?= $booking['status'] === 'Pemeriksaan' ? 'selected' : '' ?>>Pemeriksaan</option>
                                <option value="Dalam Perbaikan" <?= $booking['status'] === 'Dalam Perbaikan' ? 'selected' : '' ?>>Dalam Perbaikan</option>
                                <option value="Selesai" <?= $booking['status'] === 'Selesai' ? 'selected' : '' ?>>Selesai</option>
                                <option value="Dibatalkan" <?= $booking['status'] === 'Dibatalkan' ? 'selected' : '' ?>>Dibatalkan</option>
                            </select>
                        </div>

                        <button type="submit" class="btn btn-success btn-sm" style="margin-top: 28px;">
                            <i class="fa-solid fa-floppy-disk"></i> Simpan Pembaruan Status
                        </button>
                    </form>
                </div>
            </div>

            <!-- Two Columns: Information Details -->
            <div style="display: grid; grid-template-columns: 1.1fr 0.9fr; gap: 24px;">
                <!-- Left Details -->
                <div>
                    <!-- Order & Customer Info -->
                    <div class="card" style="margin-bottom: 24px;">
                        <h3 style="font-size: 1.15rem; margin-bottom: 16px; padding-bottom: 10px; border-bottom: 1px solid var(--border-color);">
                            <i class="fa-solid fa-circle-info" style="color:var(--primary); margin-right:6px;"></i> Informasi Permintaan & Pelanggan
                        </h3>

                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px; margin-bottom: 16px;">
                            <div>
                                <div style="font-size: 0.78rem; color: var(--text-muted); text-transform: uppercase;">Pelanggan</div>
                                <div style="font-weight: 700;"><?= htmlspecialchars($booking['customer_name'] ?? 'Pelanggan') ?></div>
                                <div style="font-size: 0.85rem; color: var(--text-muted);"><?= htmlspecialchars($booking['customer_phone'] ?? '-') ?> | <?= htmlspecialchars($booking['customer_email'] ?? '-') ?></div>
                            </div>
                            <div>
                                <div style="font-size: 0.78rem; color: var(--text-muted); text-transform: uppercase;">Peralatan</div>
                                <div style="font-weight: 700;"><?= htmlspecialchars($booking['appliance_name'] ?? ($booking['category_name'] ?? 'Alat')) ?></div>
                                <div style="font-size: 0.85rem; color: var(--text-muted);"><?= htmlspecialchars($booking['brand'] ?? '-') ?> <?= htmlspecialchars($booking['model'] ?? '') ?></div>
                            </div>
                        </div>

                        <div style="margin-bottom: 16px;">
                            <div style="font-size: 0.78rem; color: var(--text-muted); text-transform: uppercase; margin-bottom: 4px;">Keluhan Pelanggan:</div>
                            <div style="background: #f8fafc; padding: 12px 14px; border-radius: var(--radius-md); font-size: 0.9rem; line-height: 1.5;">
                                <?= nl2br(htmlspecialchars($booking['complaint'] ?? ($booking['issue_description'] ?? '-'))) ?>
                            </div>
                        </div>

                        <?php if (!empty($diagnosis['ai_result']) || !empty($booking['ai_diagnosis_summary'])): ?>
                            <div style="background: linear-gradient(135deg, #eef2ff 0%, #ecfeff 100%); border: 1px solid #c7d2fe; border-radius: var(--radius-md); padding: 14px 16px; margin-bottom: 16px;">
                                <div style="font-weight: 700; font-size: 0.82rem; color: var(--primary); margin-bottom: 4px;">
                                    <i class="fa-solid fa-wand-magic-sparkles"></i> Hasil Diagnosa Awal AI:
                                </div>
                                <div style="font-size: 0.88rem; color: #1e1b4b; line-height: 1.4;">
                                    <?= htmlspecialchars($diagnosis['ai_result'] ?? $booking['ai_diagnosis_summary']) ?>
                                </div>
                            </div>
                        <?php endif; ?>

                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px; padding-top: 14px; border-top: 1px solid var(--border-color);">
                            <div>
                                <div style="font-size: 0.78rem; color: var(--text-muted); text-transform: uppercase;">Jadwal Kedatangan</div>
                                <div style="font-weight: 700;"><?= Helper::formatDateIndo($booking['schedule'] ?? ($booking['scheduled_date'] ?? null), true) ?></div>
                            </div>
                            <div>
                                <div style="font-size: 0.78rem; color: var(--text-muted); text-transform: uppercase;">Alamat Rumah</div>
                                <div style="font-size: 0.88rem; line-height: 1.4;"><?= nl2br(htmlspecialchars($booking['address'])) ?></div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Right Details -->
                <div>
                    <!-- Cost & Invoice Card -->
                    <div class="card" style="margin-bottom: 24px;">
                        <h3 style="font-size: 1.15rem; margin-bottom: 16px; padding-bottom: 10px; border-bottom: 1px solid var(--border-color);">
                            <i class="fa-solid fa-file-invoice-dollar" style="color:var(--primary); margin-right:6px;"></i> Laporan Akhir & Faktur
                        </h3>

                        <?php if ($report): ?>
                            <div style="display: flex; justify-content: space-between; margin-bottom: 8px; font-size: 0.9rem;">
                                <span style="color: var(--text-muted);">No. Invoice:</span>
                                <span style="font-weight: 700; color: var(--primary);"><?= htmlspecialchars($report['invoice_number']) ?></span>
                            </div>
                            <div style="display: flex; justify-content: space-between; margin-bottom: 8px; font-size: 0.9rem;">
                                <span style="color: var(--text-muted);">Biaya Jasa:</span>
                                <span style="font-weight: 600;"><?= Helper::formatRupiah($report['service_fee']) ?></span>
                            </div>
                            <div style="display: flex; justify-content: space-between; margin-bottom: 8px; font-size: 0.9rem;">
                                <span style="color: var(--text-muted);">Biaya Sparepart:</span>
                                <span style="font-weight: 600;"><?= Helper::formatRupiah($report['sparepart_fee']) ?></span>
                            </div>
                            <?php if (!empty($report['replaced_component'])): ?>
                                <div style="font-size: 0.8rem; color: var(--text-muted); background: #f8fafc; padding: 6px 10px; border-radius: 4px; margin-bottom: 10px;">
                                    Komponen: <?= htmlspecialchars($report['replaced_component']) ?>
                                </div>
                            <?php endif; ?>

                            <div style="display: flex; justify-content: space-between; padding-top: 12px; border-top: 2px dashed var(--border-color); font-size: 1.15rem; font-weight: 800; margin-bottom: 16px;">
                                <span>Total Tagihan:</span>
                                <span style="color: var(--primary);"><?= Helper::formatRupiah($report['total_amount']) ?></span>
                            </div>

                            <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 16px;">
                                <span style="font-size: 0.85rem; color: var(--text-muted);">Status Pembayaran:</span>
                                <span class="badge <?= $report['payment_status'] === 'paid' ? 'badge-success' : 'badge-warning' ?>">
                                    <?= $report['payment_status'] === 'paid' ? 'LUNAS (PAID)' : 'BELUM LUNAS' ?>
                                </span>
                            </div>

                            <a href="<?= BASE_URL ?>/index.php?page=admin_invoice_print&id=<?= $booking['id'] ?>" target="_blank" class="btn btn-secondary btn-block btn-sm">
                                <i class="fa-solid fa-print"></i> Cetak / Tampilkan Faktur PDF
                            </a>
                        <?php else: ?>
                            <div style="color: var(--text-muted); font-size: 0.88rem; padding: 16px 0;">
                                Rincian biaya belum diinput oleh teknisi. Estimasi dasar: <?= Helper::formatRupiah($booking['service_estimated_price'] ?? 75000) ?>.
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </main>
</div>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
