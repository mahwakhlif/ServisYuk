<?php
$pageTitle = 'Pengaturan AI & Basis Pengetahuan - ServisYuk Admin';
require_once __DIR__ . '/../layouts/header.php';
$flash = Helper::getFlash();
?>

<div class="dashboard-layout">
    <?php require_once __DIR__ . '/../layouts/sidebar_admin.php'; ?>

    <main class="dashboard-main">
        <header class="dashboard-topbar">
            <div class="topbar-title">
                <h2>Pengaturan AI & Knowledge Base</h2>
                <p>Konfigurasi model Google Gemini API & basis data aturan diagnosa cerdas.</p>
            </div>
            <div class="topbar-actions">
                <button type="button" class="btn btn-primary btn-sm" data-modal-target="modal_add_kb">
                    <i class="fa-solid fa-plus"></i> Tambah Aturan Diagnosa Baru
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

            <!-- API Configuration Card -->
            <div class="card" style="margin-bottom: 28px; background: linear-gradient(135deg, #1e1b4b 0%, #0f172a 100%); color: #ffffff;">
                <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 20px;">
                    <div>
                        <span class="badge badge-info" style="margin-bottom: 6px;"><i class="fa-solid fa-microchip"></i> AI Engine Core</span>
                        <h3 style="font-size: 1.3rem; color: #ffffff;">Konfigurasi Google Gemini AI</h3>
                        <p style="font-size: 0.88rem; color: #cbd5e1;">Jika API Key kosong, sistem otomatis beralih ke <strong>Built-In Smart Expert Engine (Offline Mode)</strong>.</p>
                    </div>
                    <div style="text-align: right;">
                        <span class="badge badge-success" style="font-size: 0.85rem; padding: 6px 14px;">
                            <i class="fa-solid fa-shield-check"></i> Hybrid Mode: Ready
                        </span>
                    </div>
                </div>

                <form action="<?= BASE_URL ?>/index.php?page=admin_ai_settings" method="POST">
                    <?= Helper::csrfField() ?>
                    <input type="hidden" name="action" value="update_settings">

                    <div style="display: grid; grid-template-columns: 1.5fr 1fr; gap: 20px;">
                        <div class="form-group">
                            <label class="form-label" style="color: #ffffff;">Google Gemini API Key</label>
                            <input type="password" name="gemini_api_key" class="form-control" placeholder="Masukkan AIzaSy..." value="<?= htmlspecialchars($settings['gemini_api_key'] ?? '') ?>" style="background: rgba(255,255,255,0.1); color: #ffffff; border-color: rgba(255,255,255,0.2);">
                            <small style="color: #94a3b8; font-size: 0.78rem;">Dapatkan gratis dari Google AI Studio jika ingin menghubungkan live model.</small>
                        </div>

                        <div class="form-group">
                            <label class="form-label" style="color: #ffffff;">Model Gemini yang Digunakan</label>
                            <select name="gemini_model" class="form-select" style="background: #1e293b; color: #ffffff; border-color: rgba(255,255,255,0.2);">
                                <option value="gemini-1.5-flash" <?= ($settings['gemini_model'] ?? '') === 'gemini-1.5-flash' ? 'selected' : '' ?>>Gemini 1.5 Flash (Super Cepat & Direkomendasikan)</option>
                                <option value="gemini-1.5-pro" <?= ($settings['gemini_model'] ?? '') === 'gemini-1.5-pro' ? 'selected' : '' ?>>Gemini 1.5 Pro (Analisis Komprehensif)</option>
                            </select>
                        </div>
                    </div>

                    <div style="display: flex; align-items: center; justify-content: space-between; padding-top: 14px; border-top: 1px solid rgba(255,255,255,0.1);">
                        <label style="display: flex; align-items: center; gap: 8px; cursor: pointer; font-size: 0.9rem;">
                            <input type="checkbox" name="ai_diagnosis_enabled" value="1" <?= ($settings['ai_diagnosis_enabled'] ?? '1') === '1' ? 'checked' : '' ?> style="width:18px; height:18px;">
                            Aktifkan Modul AI Diagnostic Assistant untuk Customer
                        </label>
                        <button type="submit" class="btn btn-primary btn-sm">
                            <i class="fa-solid fa-floppy-disk"></i> Simpan Konfigurasi AI
                        </button>
                    </div>
                </form>
            </div>

            <!-- Knowledge Base Rules Table -->
            <div class="table-card">
                <div class="table-header">
                    <div>
                        <h3><i class="fa-solid fa-brain" style="color:var(--primary); margin-right:8px;"></i> Basis Aturan Pakar Perbaikan (<?= count($knowledgeBase) ?> Aturan)</h3>
                        <p style="font-size: 0.85rem; color: var(--text-muted); margin-top: 2px;">Aturan diagnosa offline dan referensi estimasi teknisi saat live AI tidak aktif.</p>
                    </div>
                    <button type="button" class="btn btn-primary btn-sm" data-modal-target="modal_add_kb">
                        <i class="fa-solid fa-plus"></i> Tambah Aturan Baru
                    </button>
                </div>

                <div class="table-responsive">
                    <table class="custom-table">
                        <thead>
                            <tr>
                                <th>Kategori</th>
                                <th>Masalah Kerusakan</th>
                                <th>Katakunci Pencocokan Gejala</th>
                                <th>Estimasi Biaya</th>
                                <th>Durasi</th>
                                <th style="text-align: right;">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($knowledgeBase as $kb): ?>
                                <tr>
                                    <td>
                                        <span class="badge badge-secondary"><?= htmlspecialchars($kb['category_name']) ?></span>
                                    </td>
                                    <td style="font-weight: 700; color: var(--text-main); max-width: 200px;">
                                        <?= htmlspecialchars($kb['issue_title']) ?>
                                    </td>
                                    <td style="font-size: 0.82rem; color: var(--text-muted); max-width: 250px;">
                                        <?= htmlspecialchars($kb['symptom_keywords']) ?>
                                    </td>
                                    <td style="font-weight: 700; color: var(--primary);">
                                        <?= Helper::formatRupiah($kb['estimated_cost_min']) ?> - <?= Helper::formatRupiah($kb['estimated_cost_max']) ?>
                                    </td>
                                    <td><?= htmlspecialchars($kb['repair_time_estimate']) ?></td>
                                    <td style="text-align: right;">
                                        <form action="<?= BASE_URL ?>/index.php?page=admin_ai_settings" method="POST" style="display:inline;">
                                            <?= Helper::csrfField() ?>
                                            <input type="hidden" name="action" value="delete_kb">
                                            <input type="hidden" name="kb_id" value="<?= $kb['id'] ?>">
                                            <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Hapus aturan pengetahuan ini?')" title="Hapus">
                                                <i class="fa-solid fa-trash"></i>
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </main>
</div>

<!-- Modal: Tambah Aturan AI Baru -->
<div id="modal_add_kb" class="modal-backdrop">
    <div class="modal-dialog" style="max-width: 650px;">
        <form action="<?= BASE_URL ?>/index.php?page=admin_ai_settings" method="POST">
            <?= Helper::csrfField() ?>
            <input type="hidden" name="action" value="add_kb">

            <div class="modal-header">
                <h4><i class="fa-solid fa-brain" style="color:var(--primary); margin-right:6px;"></i> Tambah Aturan Diagnosa Cerdas</h4>
                <button type="button" class="modal-close" data-modal-close>&times;</button>
            </div>

            <div class="modal-body">
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px;">
                    <div class="form-group">
                        <label class="form-label">Kategori Peralatan <span class="required">*</span></label>
                        <select name="category_name" class="form-select" required>
                            <option value="">-- Pilih Kategori --</option>
                            <?php foreach ($categories as $cat): ?>
                                <option value="<?= htmlspecialchars($cat['name']) ?>"><?= htmlspecialchars($cat['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Judul Masalah Kerusakan <span class="required">*</span></label>
                        <input type="text" name="issue_title" class="form-control" placeholder="Contoh: AC Mati Total / PCB Konslet" required>
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label">Katakunci Pencocokan Gejala (Pisahkan dengan koma) <span class="required">*</span></label>
                    <input type="text" name="symptom_keywords" class="form-control" placeholder="mati total, konslet, bau kabel terbakar, tidak mau hidup" required>
                </div>

                <div class="form-group">
                    <label class="form-label">Kemungkinan Penyebab Teknis (Satu per baris)</label>
                    <textarea name="probable_causes" class="form-control" rows="2" placeholder="1. Sekering PCB putus&#10;2. Tegangan listrik drop&#10;3. Modul power supply rusak"></textarea>
                </div>

                <div class="form-group">
                    <label class="form-label">Tindakan Awal Mandiri Customer (DIY Check)</label>
                    <textarea name="diy_check" class="form-control" rows="2" placeholder="1. Periksa stop kontak&#10;2. Cek MCB panel listrik"></textarea>
                </div>

                <div class="form-group">
                    <label class="form-label">Peringatan Keselamatan Darurat (Safety First)</label>
                    <input type="text" name="safety_warning" class="form-control" placeholder="Cabut steker listrik segera dan jangan dinyalakan kembali sampai teknisi memeriksa.">
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 14px;">
                    <div class="form-group">
                        <label class="form-label">Estimasi Min (Rp)</label>
                        <input type="number" name="estimated_cost_min" class="form-control" value="100000" min="0" step="5000">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Estimasi Max (Rp)</label>
                        <input type="number" name="estimated_cost_max" class="form-control" value="350000" min="0" step="5000">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Estimasi Durasi</label>
                        <input type="text" name="repair_time_estimate" class="form-control" value="1 - 2 Jam">
                    </div>
                </div>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-modal-close>Batal</button>
                <button type="submit" class="btn btn-primary">Simpan Aturan</button>
            </div>
        </form>
    </div>
</div>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
