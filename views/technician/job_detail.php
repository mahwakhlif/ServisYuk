<?php
$code = $booking['request_code'] ?? ($booking['booking_code'] ?? 'SRV');
$pageTitle = 'Pengerjaan Tugas #' . htmlspecialchars($code) . ' - ServisYuk';
require_once __DIR__ . '/../layouts/header.php';
$flash = Helper::getFlash();
$stInfo = Helper::getStatusInfo($booking['status'] ?? 'Teknisi Ditugaskan');
?>

<div class="dashboard-layout">
    <?php require_once __DIR__ . '/../layouts/sidebar_technician.php'; ?>

    <main class="dashboard-main">
        <header class="dashboard-topbar">
            <div class="topbar-title">
                <h2>Kelola Tugas Servis #<?= htmlspecialchars($code) ?></h2>
                <p>Status: <span class="badge <?= $stInfo['class'] ?>"><?= $stInfo['label'] ?></span></p>
            </div>
            <div class="topbar-actions">
                <a href="<?= BASE_URL ?>/index.php?page=tech_dashboard" class="btn btn-secondary btn-sm">
                    <i class="fa-solid fa-arrow-left"></i> Kembali ke Dashboard
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

            <!-- Action Center: Quick Status Switcher Banner -->
            <div class="card" style="background: linear-gradient(135deg, #1e1b4b 0%, #0f172a 100%); color: #ffffff; margin-bottom: 28px;">
                <div style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 16px;">
                    <div>
                        <span class="badge badge-info" style="margin-bottom: 6px;"><i class="fa-solid fa-bolt"></i> Aksi Alur Kerja Teknisi</span>
                        <h3 style="font-size: 1.3rem; color: #ffffff; margin-bottom: 2px;">Tahapan Pengerjaan Lapangan</h3>
                        <p style="font-size: 0.88rem; color: #94a3b8;">Perbarui status pekerjaan secara berurutan sesuai progres lapangan.</p>
                    </div>

                    <div style="display: flex; gap: 10px; flex-wrap: wrap;">
                        <?php if (in_array($booking['status'], ['Teknisi Ditugaskan', 'assigned', 'Dikonfirmasi', 'Menunggu'])): ?>
                            <form action="<?= BASE_URL ?>/index.php?page=tech_update_status" method="POST" style="display:inline;">
                                <?= Helper::csrfField() ?>
                                <input type="hidden" name="booking_id" value="<?= $booking['id'] ?>">
                                <input type="hidden" name="status" value="Dalam Perjalanan">
                                <input type="hidden" name="notes" value="Teknisi sedang dalam perjalanan menuju lokasi pelanggan.">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fa-solid fa-motorcycle"></i> 1. Berangkat Menuju Lokasi
                                </button>
                            </form>
                        <?php endif; ?>

                        <?php if (in_array($booking['status'], ['Dalam Perjalanan', 'Teknisi Ditugaskan', 'assigned'])): ?>
                            <form action="<?= BASE_URL ?>/index.php?page=tech_update_status" method="POST" style="display:inline;">
                                <?= Helper::csrfField() ?>
                                <input type="hidden" name="booking_id" value="<?= $booking['id'] ?>">
                                <input type="hidden" name="status" value="Pemeriksaan">
                                <input type="hidden" name="notes" value="Teknisi tiba di lokasi dan mulai inspeksi/pemeriksaan fisik.">
                                <button type="submit" class="btn btn-warning" style="color:#000;">
                                    <i class="fa-solid fa-magnifying-glass"></i> 2. Mulai Pemeriksaan
                                </button>
                            </form>
                        <?php endif; ?>

                        <?php if (in_array($booking['status'], ['Pemeriksaan', 'Dalam Perjalanan'])): ?>
                            <form action="<?= BASE_URL ?>/index.php?page=tech_update_status" method="POST" style="display:inline;">
                                <?= Helper::csrfField() ?>
                                <input type="hidden" name="booking_id" value="<?= $booking['id'] ?>">
                                <input type="hidden" name="status" value="Dalam Perbaikan">
                                <input type="hidden" name="notes" value="Teknisi sedang melakukan tindakan perbaikan dan penanganan kerusakan.">
                                <button type="submit" class="btn btn-info" style="color:#fff;">
                                    <i class="fa-solid fa-screwdriver-wrench"></i> 3. Masuk Pengerjaan Perbaikan
                                </button>
                            </form>
                        <?php endif; ?>

                        <?php if ($booking['status'] !== 'Selesai'): ?>
                            <button type="button" class="btn btn-success" data-modal-target="modal_complete_job">
                                <i class="fa-solid fa-circle-check"></i> 4. Selesai & Buat Laporan Akhir
                            </button>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Two Columns -->
            <div style="display: grid; grid-template-columns: 1.1fr 0.9fr; gap: 28px;">
                <!-- Left: Job & Customer Details -->
                <div>
                    <!-- Customer Information Card -->
                    <div class="card" style="margin-bottom: 24px;">
                        <h3 style="font-size: 1.15rem; margin-bottom: 16px; padding-bottom: 10px; border-bottom: 1px solid var(--border-color);">
                            <i class="fa-solid fa-user-tag" style="color:var(--primary); margin-right:6px;"></i> Informasi Pelanggan & Alamat
                        </h3>

                        <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 16px;">
                            <div>
                                <h4 style="font-size: 1.1rem; margin-bottom: 2px;"><?= htmlspecialchars($booking['customer_name'] ?? 'Pelanggan') ?></h4>
                                <div style="font-size: 0.88rem; color: var(--text-muted);"><?= htmlspecialchars($booking['customer_phone'] ?? '-') ?></div>
                            </div>
                            <div style="display: flex; gap: 8px;">
                                <?php if (!empty($booking['customer_phone'])): ?>
                                    <a href="https://wa.me/<?= preg_replace('/[^0-9]/', '', $booking['customer_phone']) ?>" target="_blank" class="btn btn-success btn-sm">
                                        <i class="fa-brands fa-whatsapp"></i> Chat WhatsApp
                                    </a>
                                    <a href="tel:<?= htmlspecialchars($booking['customer_phone']) ?>" class="btn btn-secondary btn-sm">
                                        <i class="fa-solid fa-phone"></i> Telepon
                                    </a>
                                <?php endif; ?>
                            </div>
                        </div>

                        <div style="background: #f8fafc; padding: 14px 16px; border-radius: var(--radius-md); margin-bottom: 14px;">
                            <div style="font-size: 0.78rem; font-weight: 700; color: var(--text-muted); text-transform: uppercase;">Alamat Lengkap:</div>
                            <div style="font-size: 0.92rem; color: var(--text-main); margin: 4px 0 8px; line-height: 1.4;">
                                <?= nl2br(htmlspecialchars($booking['address'] ?? '-')) ?>
                            </div>
                        </div>

                        <a href="https://maps.google.com/?q=<?= urlencode($booking['address'] ?? '') ?>" target="_blank" class="btn btn-outline btn-sm btn-block">
                            <i class="fa-solid fa-diamond-turn-right"></i> Buka Rute di Google Maps
                        </a>
                    </div>

                    <!-- Problem & AI Diagnosis Card -->
                    <div class="card" style="margin-bottom: 24px;">
                        <h3 style="font-size: 1.15rem; margin-bottom: 16px; padding-bottom: 10px; border-bottom: 1px solid var(--border-color);">
                            <i class="fa-solid fa-microscope" style="color:var(--primary); margin-right:6px;"></i> Detail Kerusakan & Hasil Diagnosa AI
                        </h3>

                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px; margin-bottom: 16px;">
                            <div>
                                <div style="font-size: 0.78rem; color: var(--text-muted); text-transform: uppercase;">Peralatan</div>
                                <div style="font-weight: 700;"><?= htmlspecialchars($booking['appliance_name'] ?? ($booking['category_name'] ?? 'Alat')) ?></div>
                                <div style="font-size: 0.85rem; color: var(--text-muted);"><?= htmlspecialchars($booking['brand'] ?? '') ?> <?= htmlspecialchars($booking['model'] ?? '') ?></div>
                            </div>
                            <div>
                                <div style="font-size: 0.78rem; color: var(--text-muted); text-transform: uppercase;">Jadwal Kedatangan</div>
                                <div style="font-weight: 700;"><?= Helper::formatDateIndo($booking['schedule'] ?? ($booking['scheduled_date'] ?? null), true) ?></div>
                            </div>
                        </div>

                        <div style="margin-bottom: 16px;">
                            <div style="font-size: 0.78rem; color: var(--text-muted); text-transform: uppercase; margin-bottom: 4px;">Deskripsi Keluhan Customer:</div>
                            <div style="background: #f8fafc; padding: 12px 14px; border-radius: var(--radius-md); font-size: 0.9rem; line-height: 1.5;">
                                <?= nl2br(htmlspecialchars($booking['complaint'] ?? ($booking['issue_description'] ?? '-'))) ?>
                            </div>
                        </div>

                        <?php if (!empty($diagnosis['ai_result']) || !empty($booking['ai_diagnosis_summary'])): ?>
                            <div style="background: linear-gradient(135deg, #eef2ff 0%, #ecfeff 100%); border: 1px solid #c7d2fe; border-radius: var(--radius-md); padding: 14px 16px;">
                                <div style="font-weight: 700; font-size: 0.82rem; color: var(--primary); margin-bottom: 4px;">
                                    <i class="fa-solid fa-wand-magic-sparkles"></i> Rekomendasi Diagnosa Awal AI:
                                </div>
                                <div style="font-size: 0.88rem; color: #1e1b4b; line-height: 1.4;">
                                    <?= htmlspecialchars($diagnosis['ai_result'] ?? $booking['ai_diagnosis_summary']) ?>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Right: Reports & Invoices -->
                <div>
                    <!-- Report Card -->
                    <div class="card" style="margin-bottom: 24px;">
                        <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 16px; padding-bottom: 10px; border-bottom: 1px solid var(--border-color);">
                            <h3 style="font-size: 1.15rem;">
                                <i class="fa-solid fa-receipt" style="color:var(--primary); margin-right:6px;"></i> Laporan Akhir & Faktur
                            </h3>
                            <button type="button" class="btn btn-secondary btn-sm" data-modal-target="modal_complete_job">
                                <i class="fa-solid fa-pen-to-square"></i> <?= $report ? 'Edit Laporan' : 'Isi Laporan' ?>
                            </button>
                        </div>

                        <?php if ($report): ?>
                            <div style="margin-bottom: 10px; font-size: 0.88rem;">
                                <div style="font-weight: 600; color: var(--text-muted); font-size: 0.78rem; text-transform: uppercase;">Hasil Inspeksi:</div>
                                <div style="background: #f8fafc; padding: 8px 12px; border-radius: 4px; margin-top: 2px;">
                                    <?= htmlspecialchars($report['inspection_result']) ?>
                                </div>
                            </div>

                            <div style="margin-bottom: 10px; font-size: 0.88rem;">
                                <div style="font-weight: 600; color: var(--text-muted); font-size: 0.78rem; text-transform: uppercase;">Tindakan Perbaikan:</div>
                                <div style="background: #f8fafc; padding: 8px 12px; border-radius: 4px; margin-top: 2px;">
                                    <?= htmlspecialchars($report['repair_action']) ?>
                                </div>
                            </div>

                            <?php if (!empty($report['replaced_component'])): ?>
                                <div style="margin-bottom: 10px; font-size: 0.88rem;">
                                    <div style="font-weight: 600; color: var(--text-muted); font-size: 0.78rem; text-transform: uppercase;">Komponen Diganti:</div>
                                    <div style="background: #f8fafc; padding: 8px 12px; border-radius: 4px; margin-top: 2px;">
                                        <?= htmlspecialchars($report['replaced_component']) ?>
                                    </div>
                                </div>
                            <?php endif; ?>

                            <div style="display: flex; justify-content: space-between; margin-bottom: 8px; font-size: 0.9rem;">
                                <span style="color: var(--text-muted);">Biaya Jasa:</span>
                                <span style="font-weight: 600;"><?= Helper::formatRupiah($report['service_fee']) ?></span>
                            </div>
                            <div style="display: flex; justify-content: space-between; margin-bottom: 8px; font-size: 0.9rem;">
                                <span style="color: var(--text-muted);">Biaya Suku Cadang:</span>
                                <span style="font-weight: 600;"><?= Helper::formatRupiah($report['sparepart_fee']) ?></span>
                            </div>

                            <div style="display: flex; justify-content: space-between; padding-top: 12px; border-top: 2px dashed var(--border-color); font-size: 1.15rem; font-weight: 800; margin-bottom: 14px;">
                                <span>Total Tagihan:</span>
                                <span style="color: var(--primary);"><?= Helper::formatRupiah($report['total_amount']) ?></span>
                            </div>

                            <div style="display: flex; align-items: center; justify-content: space-between;">
                                <span style="font-size: 0.85rem; color: var(--text-muted);">Status Bayar:</span>
                                <span class="badge <?= $report['payment_status'] === 'paid' ? 'badge-success' : 'badge-warning' ?>">
                                    <?= $report['payment_status'] === 'paid' ? 'LUNAS (PAID)' : 'BELUM LUNAS' ?>
                                </span>
                            </div>
                        <?php else: ?>
                            <div style="padding: 20px 0; text-align: center; color: var(--text-muted); font-size: 0.88rem;">
                                Belum ada laporan akhir yang diisi. Klik tombol "4. Selesai & Buat Laporan Akhir" setelah pengerjaan selesai.
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </main>
</div>

<!-- Modal: Selesai Pengerjaan & Input Laporan Hasil Perbaikan -->
<div id="modal_complete_job" class="modal-backdrop">
    <div class="modal-dialog">
        <form action="<?= BASE_URL ?>/index.php?page=tech_update_status" method="POST">
            <?= Helper::csrfField() ?>
            <input type="hidden" name="booking_id" value="<?= $booking['id'] ?>">
            <input type="hidden" name="status" value="Selesai">
            <input type="hidden" name="submit_report" value="1">

            <div class="modal-header">
                <h4><i class="fa-solid fa-clipboard-check" style="color:var(--success); margin-right:6px;"></i> Laporan Hasil Pemeriksaan & Perbaikan</h4>
                <button type="button" class="modal-close" data-modal-close>&times;</button>
            </div>

            <div class="modal-body">
                <div class="form-group">
                    <label class="form-label">Hasil Pemeriksaan / Temuan Fisik <span class="required">*</span></label>
                    <textarea name="inspection_result" class="form-control" rows="2" placeholder="Hasil pemeriksaan fisik komponen yang mengalami kerusakan..." required><?= htmlspecialchars($report['inspection_result'] ?? 'Pemeriksaan fisik unit dan verifikasi titik kerusakan komponen.') ?></textarea>
                </div>

                <div class="form-group">
                    <label class="form-label">Tindakan Perbaikan yang Dilakukan <span class="required">*</span></label>
                    <textarea name="repair_action" class="form-control" rows="2" placeholder="Tindakan perbaikan atau penggantian yang dilakukan..." required><?= htmlspecialchars($report['repair_action'] ?? 'Perbaikan komponen, pembersihan menyeluruh, dan pengujian operasional unit.') ?></textarea>
                </div>

                <div class="form-group">
                    <label class="form-label">Komponen / Suku Cadang yang Diganti (Opsional)</label>
                    <input type="text" name="replaced_component" class="form-control" placeholder="Contoh: Kapasitor 25uF, Thermostat, Selang Pembuangan" value="<?= htmlspecialchars($report['replaced_component'] ?? '') ?>">
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px;">
                    <div class="form-group">
                        <label class="form-label">Biaya Jasa Servis (Rp) <span class="required">*</span></label>
                        <input type="number" name="service_fee" class="form-control" value="<?= $report['service_fee'] ?? 75000 ?>" min="0" step="5000" required>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Biaya Suku Cadang (Rp)</label>
                        <input type="number" name="sparepart_fee" class="form-control" value="<?= $report['sparepart_fee'] ?? 0 ?>" min="0" step="5000">
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label">Status Pembayaran dari Pelanggan</label>
                    <select name="payment_status" class="form-select">
                        <option value="paid" <?= ($report['payment_status'] ?? '') === 'paid' ? 'selected' : '' ?>>Sudah Diterima (Lunas)</option>
                        <option value="unpaid" <?= ($report['payment_status'] ?? '') === 'unpaid' ? 'selected' : '' ?>>Belum Dibayar</option>
                    </select>
                </div>

                <div class="form-group">
                    <label class="form-label">Catatan Tambahan untuk Pelanggan</label>
                    <textarea name="notes" class="form-control" rows="2" placeholder="Petunjuk perawatan atau garansi..."><?= htmlspecialchars($report['notes'] ?? 'Perbaikan selesai dan peralatan telah diuji coba berjalan normal.') ?></textarea>
                </div>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-modal-close>Batal</button>
                <button type="submit" class="btn btn-success">
                    <i class="fa-solid fa-floppy-disk"></i> Simpan Laporan & Selesaikan Tugas
                </button>
            </div>
        </form>
    </div>
</div>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
