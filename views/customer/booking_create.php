<?php
$pageTitle = 'Ajukan Permintaan Jasa Servis - ServisYuk';
require_once __DIR__ . '/../layouts/header.php';
$flash = Helper::getFlash();
?>

<div class="dashboard-layout">
    <?php require_once __DIR__ . '/../layouts/sidebar_customer.php'; ?>

    <main class="dashboard-main">
        <header class="dashboard-topbar">
            <div class="topbar-title">
                <h2>Form Pengajuan Jasa Servis</h2>
                <p>Isi formulir di bawah ini untuk menjadwalkan kedatangan teknisi ke rumah Anda.</p>
            </div>
            <div class="topbar-actions">
                <a href="<?= BASE_URL ?>/index.php?page=customer_dashboard" class="btn btn-secondary btn-sm">
                    <i class="fa-solid fa-arrow-left"></i> Kembali
                </a>
            </div>
        </header>

        <div class="dashboard-content" style="max-width: 900px;">
            <?php if ($flash): ?>
                <div class="alert alert-<?= $flash['type'] ?>">
                    <i class="fa-solid fa-circle-info"></i>
                    <?= htmlspecialchars($flash['message']) ?>
                </div>
            <?php endif; ?>

            <!-- Attached AI Diagnosis Summary Banner (if available) -->
            <?php if (!empty($prefill['ai_diagnosis_summary'])): ?>
                <div style="background: linear-gradient(135deg, #eef2ff 0%, #ecfeff 100%); border: 1px solid #c7d2fe; border-radius: var(--radius-lg); padding: 20px 24px; margin-bottom: 24px;">
                    <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 8px;">
                        <span class="badge badge-primary"><i class="fa-solid fa-wand-magic-sparkles"></i> Terlampir Hasil Diagnosa AI</span>
                        <?php if (!empty($prefill['initial_cost_estimate'])): ?>
                            <span style="font-weight: 700; color: var(--primary); font-size: 0.95rem;">
                                Estimasi AI: <?= htmlspecialchars($prefill['initial_cost_estimate']) ?>
                            </span>
                        <?php endif; ?>
                    </div>
                    <p style="font-size: 0.92rem; color: #1e1b4b; line-height: 1.5; margin: 0;">
                        <?= htmlspecialchars($prefill['ai_diagnosis_summary']) ?>
                    </p>
                </div>
            <?php endif; ?>

            <div class="card">
                <form action="<?= BASE_URL ?>/index.php?page=customer_booking_create" method="POST" enctype="multipart/form-data">
                    <?= Helper::csrfField() ?>
                    <input type="hidden" name="ai_diagnosis_summary" value="<?= htmlspecialchars($prefill['ai_diagnosis_summary'] ?? '') ?>">
                    <input type="hidden" name="initial_cost_estimate" value="<?= htmlspecialchars($prefill['initial_cost_estimate'] ?? '') ?>">

                    <h3 style="font-size: 1.15rem; margin-bottom: 16px; padding-bottom: 10px; border-bottom: 1px solid var(--border-color);">
                        1. Informasi Peralatan & Kerusakan
                    </h3>

                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                        <div class="form-group">
                            <label class="form-label">Jenis Peralatan Rumah Tangga <span class="required">*</span></label>
                            <select name="appliance_name" id="appliance_name_select" class="form-select" required>
                                <?php
                                $appliances = ['AC (Air Conditioner)', 'Kulkas & Freezer', 'Mesin Cuci', 'Televisi (TV)', 'Kompor Gas / Listrik', 'Dispenser Air', 'Microwave & Oven', 'Pompa Air'];
                                foreach ($appliances as $app):
                                    $selected = (isset($prefill['appliance_name']) && stripos($app, $prefill['appliance_name']) !== false) ? 'selected' : '';
                                ?>
                                    <option value="<?= htmlspecialchars($app) ?>" <?= $selected ?>><?= htmlspecialchars($app) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="form-group">
                            <label class="form-label">Layanan Servis <span class="required">*</span></label>
                            <select name="service_id" id="service_select" class="form-select" required>
                                <option value="">-- Pilih Paket Layanan --</option>
                                <?php foreach ($services as $srv): ?>
                                    <option value="<?= $srv['id'] ?>" <?= ($prefill['service_id'] == $srv['id']) ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($srv['service_name']) ?> (Estimasi: <?= Helper::formatRupiah($srv['estimated_price']) ?>)
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>

                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                        <div class="form-group">
                            <label class="form-label">Merk / Brand Peralatan</label>
                            <input type="text" name="brand" class="form-control" placeholder="Contoh: Daikin, Samsung, LG, Panasonic" value="<?= htmlspecialchars($prefill['brand'] ?? '') ?>">
                        </div>

                        <div class="form-group">
                            <label class="form-label">Tipe / Model Peralatan</label>
                            <input type="text" name="model" class="form-control" placeholder="Contoh: Inverter 1 PK / Front Loading" value="<?= htmlspecialchars($prefill['model'] ?? '') ?>">
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Keluhan Kerusakan <span class="required">*</span></label>
                        <textarea name="complaint" class="form-control" rows="3" placeholder="Jelaskan kendala atau gejala kerusakan yang dialami..." required><?= htmlspecialchars($prefill['complaint'] ?? ($prefill['issue_description'] ?? '')) ?></textarea>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Upload Foto Peralatan / Kerusakan (Opsional, Max 5MB)</label>
                        <input type="file" name="photo" class="form-control" accept="image/*">
                        <small style="color: var(--text-muted); font-size: 0.8rem; margin-top: 4px; display: block;">Membantu teknisi membawa suku cadang & peralatan yang sesuai.</small>
                    </div>

                    <h3 style="font-size: 1.15rem; margin: 32px 0 16px; padding-bottom: 10px; border-bottom: 1px solid var(--border-color);">
                        2. Jadwal Kunjungan & Lokasi
                    </h3>

                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                        <div class="form-group">
                            <label class="form-label">Tanggal Kunjungan <span class="required">*</span></label>
                            <input type="date" name="schedule_date" class="form-control" min="<?= date('Y-m-d') ?>" value="<?= date('Y-m-d', strtotime('+1 day')) ?>" required>
                        </div>

                        <div class="form-group">
                            <label class="form-label">Jam Kunjungan <span class="required">*</span></label>
                            <select name="schedule_time" class="form-select" required>
                                <option value="09:00:00">Pagi (09:00 WIB)</option>
                                <option value="11:00:00">Siang (11:00 WIB)</option>
                                <option value="13:30:00" selected>Siang-Sore (13:30 WIB)</option>
                                <option value="15:30:00">Sore (15:30 WIB)</option>
                                <option value="19:00:00">Malam (19:00 WIB)</option>
                            </select>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Alamat Lengkap Kunjungan <span class="required">*</span></label>
                        <textarea name="address" class="form-control" rows="2" placeholder="Nama jalan, nomor rumah, RT/RW, kelurahan, kecamatan..." required><?= htmlspecialchars($prefill['address'] ?? '') ?></textarea>
                    </div>

                    <div style="margin-top: 32px; display: flex; justify-content: flex-end; gap: 14px;">
                        <a href="<?= BASE_URL ?>/index.php?page=customer_dashboard" class="btn btn-secondary">Batal</a>
                        <button type="submit" class="btn btn-primary btn-lg" style="box-shadow: 0 4px 16px var(--primary-glow);">
                            <i class="fa-solid fa-paper-plane"></i> Kirim Permintaan Servis
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </main>
</div>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
