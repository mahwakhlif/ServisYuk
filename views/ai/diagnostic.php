<?php
$pageTitle = 'AI Diagnostic Assistant - Analisis Kerusakan Peralatan Rumah Tangga';
require_once __DIR__ . '/../layouts/header.php';
require_once __DIR__ . '/../layouts/navbar.php';
$extraScript = 'ai-diagnostic.js';
$initialCat = $_GET['category'] ?? '';
$initialSymptom = $_GET['symptom'] ?? '';
?>

<div class="container" style="padding: 40px 24px 80px;">
    <div class="ai-page-header">
        <span class="hero-tag"><i class="fa-solid fa-wand-magic-sparkles"></i> AI Powered Triage</span>
        <h1>AI Diagnostic Assistant</h1>
        <p>
            Dapatkan analisis awal kerusakan peralatan rumah tangga Anda, langkah pengamanan darurat, serta estimasi biaya perbaikan secara cerdas dan akurat dalam hitungan detik.
        </p>
    </div>

    <div class="ai-wizard-container">
        <div class="ai-wizard-header">
            <div class="ai-header-badge">
                <i class="fa-solid fa-microchip"></i> Smart Diagnostic Engine
            </div>
            <h2 style="font-size: 1.6rem; color: #ffffff; margin-bottom: 6px;">Form Konsultasi AI</h2>
            <p style="font-size: 0.9rem; color: #cbd5e1;">Lengkapi data di bawah ini untuk memulai pemindaian dan analisis teknis cerdas.</p>
        </div>

        <div class="ai-wizard-body">
            <!-- Form Container -->
            <div id="ai_form_container">
                <form id="ai_diagnostic_form">
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                        <div class="form-group">
                            <label class="form-label">Kategori Peralatan <span class="required">*</span></label>
                            <select id="ai_category" class="form-select" required>
                                <option value="">-- Pilih Jenis Peralatan --</option>
                                <?php foreach ($categories as $cat): ?>
                                    <option value="<?= $cat['id'] ?>" <?= (stripos($cat['name'], $initialCat) !== false && !empty($initialCat)) ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($cat['name']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="form-group">
                            <label class="form-label">Merk / Brand Peralatan</label>
                            <input type="text" id="ai_brand" class="form-control" placeholder="Contoh: Daikin, Samsung, LG, Sharp, Philips">
                        </div>
                    </div>

                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                        <div class="form-group">
                            <label class="form-label">Tipe / Model (Opsional)</label>
                            <input type="text" id="ai_model" class="form-control" placeholder="Contoh: Inverter 1 PK / Front Loading 8Kg">
                        </div>

                        <div class="form-group">
                            <label class="form-label">Perkiraan Usia Pemakaian</label>
                            <select id="ai_age" class="form-select">
                                <option value="Kurang dari 1 tahun">Kurang dari 1 tahun (Masih baru)</option>
                                <option value="1 - 3 tahun" selected>1 - 3 tahun</option>
                                <option value="3 - 5 tahun">3 - 5 tahun</option>
                                <option value="Lebih dari 5 tahun">Lebih dari 5 tahun</option>
                            </select>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Gejala & Masalah yang Dialami <span class="required">*</span></label>
                        <textarea id="ai_symptoms" class="form-control" placeholder="Jelaskan secara detail apa yang terjadi pada alat Anda..." rows="3" required><?= htmlspecialchars($initialSymptom) ?></textarea>
                        
                        <!-- Dynamic Symptom Chips -->
                        <div class="symptom-chips-wrapper">
                            <span class="chips-label"><i class="fa-solid fa-lightbulb" style="color:var(--warning);"></i> Saran Gejala Cepat (Klik untuk memilih):</span>
                            <div id="ai_symptom_chips" class="chips-container">
                                <!-- Dynamically populated by ai-diagnostic.js -->
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Perilaku Khusus Tambahan (Suara / Bau / Indikator)</label>
                        <input type="text" id="ai_sound" class="form-control" placeholder="Contoh: Ada suara mendengung keras, bau kabel hangus, atau lampu error berkedip">
                    </div>

                    <div style="margin-top: 28px;">
                        <button type="submit" class="btn btn-primary btn-lg btn-block" style="box-shadow: 0 4px 20px var(--primary-glow);">
                            <i class="fa-solid fa-wand-magic-sparkles"></i> Jalankan Analisis AI Sekarang
                        </button>
                    </div>
                </form>
            </div>

            <!-- Loading Scanner State -->
            <div id="ai_loading_state" class="ai-loading-state">
                <div class="ai-scanner-orb">
                    <i class="fa-solid fa-brain"></i>
                </div>
                <div class="loading-text-headline">AI Sedang Menganalisis Kerusakan...</div>
                <p class="loading-text-sub">Mencocokkan gejala dengan basis pengetahuan teknis & kalkulasi estimasi biaya perbaikan...</p>
            </div>

            <!-- AI Result Card -->
            <div id="ai_result_card" class="ai-result-card">
                <div class="result-header">
                    <div class="result-title-box">
                        <div style="font-size: 0.8rem; font-weight: 700; color: var(--primary); text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: 4px;">
                            Hasil Diagnosa Cerdas
                        </div>
                        <h3 id="res_title">-</h3>
                        <div id="res_source" class="result-source-tag">-</div>
                    </div>
                    <div>
                        <div id="res_urgency" class="badge badge-warning" style="font-size: 0.85rem; padding: 6px 14px;">-</div>
                    </div>
                </div>

                <!-- Highlight Estimasi Biaya -->
                <div class="cost-estimate-highlight">
                    <div class="cost-estimate-label"><i class="fa-solid fa-calculator"></i> Perkiraan Rentang Biaya Servis</div>
                    <div id="res_cost" class="cost-estimate-value">-</div>
                    <div id="res_time" class="cost-estimate-note">-</div>
                </div>

                <!-- AI Summary Statement -->
                <div class="diagnosis-section" style="background: #f8fafc; padding: 18px 20px; border-radius: var(--radius-md); border-left: 4px solid var(--secondary);">
                    <div class="diagnosis-section-title" style="margin-bottom: 6px;">
                        <i class="fa-solid fa-robot" style="color:var(--secondary);"></i> Ringkasan Analisis AI
                    </div>
                    <p id="res_summary" style="font-size: 0.95rem; color: #334155; line-height: 1.6;">-</p>
                </div>

                <!-- Kemungkinan Penyebab -->
                <div class="diagnosis-section">
                    <div class="diagnosis-section-title">
                        <i class="fa-solid fa-triangle-exclamation" style="color:var(--warning);"></i> Kemungkinan Penyebab Kerusakan:
                    </div>
                    <ul id="res_causes" class="diagnosis-list">
                        <!-- Populated dynamically -->
                    </ul>
                </div>

                <!-- Safety Warning Callout -->
                <div class="safety-callout">
                    <div class="safety-callout-title">
                        <i class="fa-solid fa-shield-halved"></i> Petunjuk Keselamatan (Safety First):
                    </div>
                    <div id="res_safety" class="safety-callout-text">-</div>
                </div>

                <!-- DIY Safe Checklist -->
                <div class="diagnosis-section">
                    <div class="diagnosis-section-title">
                        <i class="fa-solid fa-list-check" style="color:var(--success);"></i> Tindakan Awal Mandiri yang Aman (DIY Pre-Check):
                    </div>
                    <ul id="res_diy" class="diagnosis-list">
                        <!-- Populated dynamically -->
                    </ul>
                </div>

                <!-- Action Footer -->
                <div class="ai-action-footer">
                    <button type="button" id="btn_re_diagnose" class="btn btn-secondary">
                        <i class="fa-solid fa-rotate-left"></i> Diagnosa Masalah Lain
                    </button>
                    
                    <button type="button" id="btn_book_with_diagnosis" class="btn btn-primary btn-lg" style="box-shadow: 0 4px 18px var(--primary-glow);">
                        <i class="fa-solid fa-calendar-check"></i> Pesan Teknisi dengan Diagnosa Ini <i class="fa-solid fa-arrow-right"></i>
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
