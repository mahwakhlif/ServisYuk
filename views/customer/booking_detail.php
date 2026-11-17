<?php
$code = $booking['request_code'] ?? ($booking['booking_code'] ?? 'SRV');
$pageTitle = 'Detail Permintaan Servis #' . htmlspecialchars($code) . ' - ServisYuk';
require_once __DIR__ . '/../layouts/header.php';
$flash = Helper::getFlash();
$stInfo = Helper::getStatusInfo($booking['status'] ?? 'Menunggu');
?>

<div class="dashboard-layout">
    <?php require_once __DIR__ . '/../layouts/sidebar_customer.php'; ?>

    <main class="dashboard-main">
        <header class="dashboard-topbar">
            <div class="topbar-title">
                <h2>Tiket Servis #<?= htmlspecialchars($code) ?></h2>
                <p>Jadwal Kedatangan: <?= Helper::formatDateIndo($booking['schedule'] ?? ($booking['scheduled_date'] ?? null), true) ?></p>
            </div>
            <div class="topbar-actions">
                <a href="<?= BASE_URL ?>/index.php?page=customer_history" class="btn btn-secondary btn-sm">
                    <i class="fa-solid fa-arrow-left"></i> Kembali ke Riwayat
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

            <!-- Status Banner & Stepper -->
            <div class="card" style="margin-bottom: 28px;">
                <div style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 12px; margin-bottom: 16px;">
                    <div>
                        <span style="font-size: 0.8rem; text-transform: uppercase; color: var(--text-muted);">Status Saat Ini</span>
                        <h3 style="font-size: 1.4rem; color: var(--text-main);"><?= $stInfo['label'] ?></h3>
                    </div>
                    <span class="badge <?= $stInfo['class'] ?>" style="font-size: 0.9rem; padding: 6px 16px;">
                        <i class="<?= $stInfo['icon'] ?>"></i> <?= $stInfo['label'] ?>
                    </span>
                </div>

                <div class="tracking-stepper">
                    <div class="step-item <?= ($stInfo['step'] >= 1 ? ($stInfo['step'] > 1 ? 'completed' : 'active') : '') ?>">
                        <div class="step-icon"><i class="fa-solid fa-clipboard-check"></i></div>
                        <div class="step-title">Diterima</div>
                        <div class="step-time">Tiket Dibuat</div>
                    </div>
                    <div class="step-item <?= ($stInfo['step'] >= 2 ? ($stInfo['step'] > 2 ? 'completed' : 'active') : '') ?>">
                        <div class="step-icon"><i class="fa-solid fa-user-check"></i></div>
                        <div class="step-title">Ditugaskan</div>
                        <div class="step-time"><?= !empty($booking['assigned_technician_name'] ?? ($booking['technician_name'] ?? '')) ? 'Teknisi Siap' : 'Menunggu' ?></div>
                    </div>
                    <div class="step-item <?= ($stInfo['step'] >= 3 ? ($stInfo['step'] > 3 ? 'completed' : 'active') : '') ?>">
                        <div class="step-icon"><i class="fa-solid fa-motorcycle"></i></div>
                        <div class="step-title">Perjalanan</div>
                        <div class="step-time">Menuju Lokasi</div>
                    </div>
                    <div class="step-item <?= ($stInfo['step'] >= 4 ? ($stInfo['step'] > 4 ? 'completed' : 'active') : '') ?>">
                        <div class="step-icon"><i class="fa-solid fa-screwdriver-wrench"></i></div>
                        <div class="step-title">Pengerjaan</div>
                        <div class="step-time">Inspeksi/Servis</div>
                    </div>
                    <div class="step-item <?= ($stInfo['step'] >= 5 ? 'completed' : '') ?>">
                        <div class="step-icon"><i class="fa-solid fa-circle-check"></i></div>
                        <div class="step-title">Selesai</div>
                        <div class="step-time">Uji & Faktur</div>
                    </div>
                </div>

                <div style="background: #f8fafc; padding: 14px 18px; border-radius: var(--radius-md); font-size: 0.9rem; color: var(--text-muted); border-left: 4px solid var(--primary);">
                    <?= $stInfo['description'] ?>
                </div>
            </div>

            <!-- Two-Column Grid: Details & Cost/Timeline -->
            <div style="display: grid; grid-template-columns: 1.2fr 0.8fr; gap: 28px;">
                <!-- Left Column -->
                <div>
                    <!-- Appliance & Problem Card -->
                    <div class="card" style="margin-bottom: 24px;">
                        <h3 style="font-size: 1.15rem; margin-bottom: 16px; padding-bottom: 10px; border-bottom: 1px solid var(--border-color);">
                            <i class="fa-solid fa-wrench" style="color:var(--primary); margin-right:6px;"></i> Detail Peralatan & Keluhan
                        </h3>

                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px; margin-bottom: 16px;">
                            <div>
                                <div style="font-size: 0.78rem; color: var(--text-muted); text-transform: uppercase;">Peralatan & Layanan</div>
                                <div style="font-weight: 700;"><?= htmlspecialchars($booking['appliance_name'] ?? ($booking['category_name'] ?? 'Alat Rumah Tangga')) ?></div>
                                <div style="font-size: 0.85rem; color: var(--text-muted);"><?= htmlspecialchars($booking['service_name'] ?? 'Servis Standar') ?></div>
                            </div>
                            <div>
                                <div style="font-size: 0.78rem; color: var(--text-muted); text-transform: uppercase;">Merk / Model</div>
                                <div style="font-weight: 700;">
                                    <?= htmlspecialchars($booking['brand'] ?? ($booking['appliance_brand'] ?? '-')) ?>
                                    <?= htmlspecialchars($booking['model'] ?? ($booking['appliance_model'] ?? '')) ?>
                                </div>
                            </div>
                        </div>

                        <div style="margin-bottom: 16px;">
                            <div style="font-size: 0.78rem; color: var(--text-muted); text-transform: uppercase; margin-bottom: 4px;">Keluhan Masalah:</div>
                            <div style="background: #f8fafc; padding: 12px 16px; border-radius: var(--radius-md); font-size: 0.92rem; line-height: 1.5;">
                                <?= nl2br(htmlspecialchars($booking['complaint'] ?? ($booking['issue_description'] ?? ''))) ?>
                            </div>
                        </div>

                        <!-- Diagnosis Section -->
                        <?php if (!empty($diagnosis['ai_result']) || !empty($booking['ai_diagnosis_summary'])): ?>
                            <div style="background: linear-gradient(135deg, #eef2ff 0%, #ecfeff 100%); border: 1px solid #c7d2fe; border-radius: var(--radius-md); padding: 14px 18px; margin-bottom: 16px;">
                                <div style="font-weight: 700; font-size: 0.85rem; color: var(--primary); margin-bottom: 4px;">
                                    <i class="fa-solid fa-wand-magic-sparkles"></i> Hasil Diagnosa Awal AI:
                                </div>
                                <div style="font-size: 0.88rem; color: #1e1b4b; line-height: 1.5;">
                                    <?= htmlspecialchars($diagnosis['ai_result'] ?? $booking['ai_diagnosis_summary']) ?>
                                </div>
                            </div>
                        <?php endif; ?>

                        <?php if (!empty($diagnosis['technician_result'])): ?>
                            <div style="background: #ecfdf5; border: 1px solid #a7f3d0; border-radius: var(--radius-md); padding: 14px 18px; margin-bottom: 16px;">
                                <div style="font-weight: 700; font-size: 0.85rem; color: #065f46; margin-bottom: 4px;">
                                    <i class="fa-solid fa-user-check"></i> Konfirmasi Pemeriksaan Fisik Teknisi:
                                </div>
                                <div style="font-size: 0.88rem; color: #047857; line-height: 1.5;">
                                    <?= htmlspecialchars($diagnosis['technician_result']) ?>
                                </div>
                            </div>
                        <?php endif; ?>

                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px; padding-top: 14px; border-top: 1px solid var(--border-color);">
                            <div>
                                <div style="font-size: 0.78rem; color: var(--text-muted); text-transform: uppercase;">Jadwal Kunjungan</div>
                                <div style="font-weight: 700;"><?= Helper::formatDateIndo($booking['schedule'] ?? ($booking['scheduled_date'] ?? null), true) ?></div>
                            </div>
                            <div>
                                <div style="font-size: 0.78rem; color: var(--text-muted); text-transform: uppercase;">Alamat Rumah</div>
                                <div style="font-size: 0.88rem; line-height: 1.4;"><?= nl2br(htmlspecialchars($booking['address'])) ?></div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Right Column -->
                <div>
                    <!-- Assigned Technician Card -->
                    <div class="card" style="margin-bottom: 24px;">
                        <h3 style="font-size: 1.15rem; margin-bottom: 16px; padding-bottom: 10px; border-bottom: 1px solid var(--border-color);">
                            <i class="fa-solid fa-user-gear" style="color:var(--primary); margin-right:6px;"></i> Teknisi Bertugas
                        </h3>

                        <?php 
                        $techName = $booking['assigned_technician_name'] ?? ($booking['technician_name'] ?? '');
                        $techPhone = $booking['assigned_technician_phone'] ?? ($booking['technician_phone'] ?? '');
                        $techSpec = $booking['technician_specialization'] ?? 'Spesialis Servis';
                        ?>

                        <?php if (!empty($techName)): ?>
                            <div style="display: flex; align-items: center; gap: 14px; margin-bottom: 16px;">
                                <div class="user-avatar" style="width: 50px; height: 50px; font-size: 1.2rem;">
                                    <i class="fa-solid fa-user-check"></i>
                                </div>
                                <div>
                                    <div style="font-weight: 700; font-size: 1.05rem;"><?= htmlspecialchars($techName) ?></div>
                                    <div style="font-size: 0.8rem; color: var(--secondary); font-weight: 600;"><?= htmlspecialchars($techSpec) ?></div>
                                    <div style="font-size: 0.85rem; color: var(--text-muted);"><?= htmlspecialchars($techPhone) ?></div>
                                </div>
                            </div>

                            <a href="https://wa.me/<?= preg_replace('/[^0-9]/', '', $techPhone) ?>" target="_blank" class="btn btn-success btn-block">
                                <i class="fa-brands fa-whatsapp"></i> Hubungi WhatsApp Teknisi
                            </a>
                        <?php else: ?>
                            <div style="text-align: center; padding: 20px 0; color: var(--text-muted);">
                                <i class="fa-solid fa-clock-rotate-left" style="font-size: 2rem; color: #cbd5e1; margin-bottom: 8px; display:block;"></i>
                                <p style="font-size: 0.9rem;">Admin sedang menjadwalkan teknisi untuk Anda.</p>
                            </div>
                        <?php endif; ?>
                    </div>

                    <!-- Cost & Payment Card -->
                    <div class="card">
                        <h3 style="font-size: 1.15rem; margin-bottom: 16px; padding-bottom: 10px; border-bottom: 1px solid var(--border-color);">
                            <i class="fa-solid fa-file-invoice-dollar" style="color:var(--primary); margin-right:6px;"></i> Laporan Akhir & Faktur
                        </h3>

                        <?php if (!empty($report)): ?>
                            <div style="margin-bottom: 12px; font-size: 0.88rem;">
                                <div style="font-weight: 600; color: var(--text-main); margin-bottom: 2px;">Tindakan Perbaikan:</div>
                                <div style="background: #f8fafc; padding: 8px 12px; border-radius: 4px;"><?= htmlspecialchars($report['repair_action']) ?></div>
                            </div>
                            <?php if (!empty($report['replaced_component'])): ?>
                                <div style="margin-bottom: 12px; font-size: 0.88rem;">
                                    <div style="font-weight: 600; color: var(--text-main); margin-bottom: 2px;">Komponen Diganti:</div>
                                    <div style="background: #f8fafc; padding: 8px 12px; border-radius: 4px;"><?= htmlspecialchars($report['replaced_component']) ?></div>
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

                            <div style="display: flex; justify-content: space-between; padding-top: 12px; border-top: 2px dashed var(--border-color); font-size: 1.15rem; font-weight: 800; margin-bottom: 16px;">
                                <span>Total Tagihan:</span>
                                <span style="color: var(--primary);"><?= Helper::formatRupiah($report['total_amount']) ?></span>
                            </div>

                            <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 16px;">
                                <span style="font-size: 0.85rem; color: var(--text-muted);">Status Pembayaran:</span>
                                <span class="badge <?= $report['payment_status'] === 'paid' ? 'badge-success' : 'badge-warning' ?>">
                                    <?= $report['payment_status'] === 'paid' ? 'LUNAS (PAID)' : 'MENUNGGU PEMBAYARAN' ?>
                                </span>
                            </div>

                            <a href="<?= BASE_URL ?>/index.php?page=admin_invoice_print&id=<?= $booking['id'] ?>" target="_blank" class="btn btn-secondary btn-block btn-sm">
                                <i class="fa-solid fa-print"></i> Cetak Faktur (<?= htmlspecialchars($report['invoice_number']) ?>)
                            </a>
                        <?php else: ?>
                            <div style="font-size: 0.88rem; color: var(--text-muted); line-height: 1.5;">
                                Estimasi biaya: <strong><?= Helper::formatRupiah($booking['service_estimated_price'] ?? 75000) ?></strong>. Laporan pengerjaan akhir dan rincian suku cadang akan diterbitkan oleh teknisi setelah perbaikan selesai.
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </main>
</div>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
