<?php
$pageTitle = 'Konfigurasi Model AI Chatbot - ServisYuk Admin';
require_once __DIR__ . '/../layouts/header.php';
$flash = Helper::getFlash();
$presets = $presets ?? AiConfigService::PROVIDER_PRESETS;
?>

<div class="dashboard-layout">
    <?php require_once __DIR__ . '/../layouts/sidebar_admin.php'; ?>

    <main class="dashboard-main">
        <header class="dashboard-topbar">
            <div class="topbar-title">
                <h2>Konfigurasi Universal AI Chatbot</h2>
                <p>Kelola provider OpenAI-compatible (Groq, OpenAI, DeepSeek, OpenRouter, NVIDIA NIM, atau Custom), API Key terenkripsi, dan pilihan model AI aktif.</p>
            </div>
            <div class="topbar-actions">
                <a href="<?= BASE_URL ?>/index.php?page=admin_dashboard" class="btn btn-secondary btn-sm">
                    <i class="fa-solid fa-arrow-left"></i> Kembali ke Dashboard
                </a>
            </div>
        </header>

        <div class="dashboard-content">
            <?php if ($flash): ?>
                <div class="alert alert-<?= $flash['type'] ?>" style="margin-bottom: 24px;">
                    <i class="fa-solid fa-<?= $flash['type'] === 'success' ? 'circle-check' : 'circle-exclamation' ?>"></i>
                    <?= htmlspecialchars($flash['message']) ?>
                </div>
            <?php endif; ?>

            <!-- Live Status Banner -->
            <div class="card" style="margin-bottom: 24px; background: linear-gradient(135deg, #0f172a 0%, #1e1b4b 50%, #172554 100%); color: #ffffff; border: 1px solid rgba(255,255,255,0.1); box-shadow: var(--shadow-md);">
                <div style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 16px;">
                    <div>
                        <div style="display: flex; align-items: center; gap: 8px; margin-bottom: 6px;">
                            <span class="badge" style="background: rgba(99, 102, 241, 0.2); color: #818cf8; border: 1px solid rgba(99, 102, 241, 0.4); font-size: 0.75rem;">
                                <i class="fa-solid fa-microchip"></i> ENGINE CHATBOT AKTIF
                            </span>
                            <?php if ($aiConfig['status'] === 'connected'): ?>
                                <span class="badge badge-success" style="font-size: 0.75rem;">
                                    <i class="fa-solid fa-circle-check"></i> Terhubung & Siap (<?= $aiConfig['latency_ms'] ?> ms)
                                </span>
                            <?php elseif ($aiConfig['has_api_key']): ?>
                                <span class="badge badge-info" style="font-size: 0.75rem;">
                                    <i class="fa-solid fa-shield-halved"></i> API Key Terkonfigurasi
                                </span>
                            <?php else: ?>
                                <span class="badge badge-warning" style="font-size: 0.75rem;">
                                    <i class="fa-solid fa-triangle-exclamation"></i> API Key Belum Diatur
                                </span>
                            <?php endif; ?>
                        </div>
                        <h3 style="font-size: 1.35rem; color: #ffffff; margin: 0 0 6px 0;">
                            <?= htmlspecialchars($aiConfig['model']) ?>
                        </h3>
                        <p style="font-size: 0.88rem; color: #94a3b8; margin: 0;">
                            Provider Aktif: <strong style="color:#e2e8f0;"><?= strtoupper(htmlspecialchars($aiConfig['provider'])) ?></strong> &bull; 
                            Endpoint: <span style="color:#cbd5e1;"><?= htmlspecialchars($aiConfig['base_url']) ?></span>
                        </p>
                    </div>

                    <div style="display: flex; align-items: center; gap: 10px;">
                        <button type="button" id="btnQuickTest" class="btn btn-sm" style="background: rgba(255,255,255,0.12); color: #ffffff; border: 1px solid rgba(255,255,255,0.25);">
                            <i class="fa-solid fa-bolt"></i> Tes Respon Cepat
                        </button>
                    </div>
                </div>

                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 12px; margin-top: 18px; padding-top: 16px; border-top: 1px solid rgba(255,255,255,0.1); font-size: 0.85rem;">
                    <div>
                        <span style="color: #94a3b8;">Status Kredensial:</span>
                        <div style="font-weight: 700; color: <?= $aiConfig['has_api_key'] ? '#4ade80' : '#f87171' ?>;">
                            <i class="fa-solid fa-<?= $aiConfig['has_api_key'] ? 'lock' : 'lock-open' ?>"></i> 
                            <?= htmlspecialchars($aiConfig['api_key_masked']) ?>
                        </div>
                    </div>
                    <div>
                        <span style="color: #94a3b8;">Model Cadangan (Fallback):</span>
                        <div style="font-weight: 700; color: #e2e8f0;">
                            <?= !empty($aiConfig['fallback_model']) ? htmlspecialchars($aiConfig['fallback_model']) : 'Sama dengan model utama' ?>
                        </div>
                    </div>
                    <div>
                        <span style="color: #94a3b8;">Terakhir Diuji:</span>
                        <div style="font-weight: 600; color: #e2e8f0;">
                            <?= $aiConfig['last_tested'] ? htmlspecialchars($aiConfig['last_tested']) : 'Belum pernah diuji' ?>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Two Columns: Settings Form & Information/Status Panel -->
            <div style="display: grid; grid-template-columns: 1.4fr 0.9fr; gap: 24px; align-items: start;">
                <!-- Left: Configuration Form -->
                <div class="card">
                    <div style="border-bottom: 1px solid var(--border-color); padding-bottom: 14px; margin-bottom: 20px;">
                        <h3 style="font-size: 1.15rem; font-weight: 700; margin: 0 0 4px 0; display: flex; align-items: center; gap: 8px;">
                            <i class="fa-solid fa-sliders" style="color: var(--primary);"></i> Pengaturan Universal AI Chatbot
                        </h3>
                        <p style="font-size: 0.84rem; color: var(--text-muted); margin: 0;">
                            Mendukung seluruh provider kompatibel OpenAI API. Hanya satu provider aktif dalam satu waktu.
                        </p>
                    </div>

                    <form action="<?= BASE_URL ?>/index.php?page=admin_ai_config" method="POST" id="aiConfigForm">
                        <?= Helper::csrfField() ?>

                        <!-- Provider Selection -->
                        <div class="form-group" style="margin-bottom: 18px;">
                            <label class="form-label" style="font-weight: 700;">Provider AI Aktif:</label>
                            <select name="provider" id="provider_select" class="form-select" style="font-weight: 600;">
                                <?php foreach ($presets as $pKey => $preset): ?>
                                    <option value="<?= htmlspecialchars($pKey) ?>" <?= $aiConfig['provider'] === $pKey ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($preset['name']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <small class="form-text" style="color: var(--text-muted); font-size: 0.8rem;">
                                Memilih provider akan otomatis menyesuaikan Base URL default dan rekomendasi model.
                            </small>
                        </div>

                        <!-- Base URL Endpoint -->
                        <div class="form-group" style="margin-bottom: 18px;">
                            <label class="form-label" style="font-weight: 700;">Base URL / Endpoint:</label>
                            <div class="input-group" style="display: flex;">
                                <span class="input-group-text" style="background: #f1f5f9; border: 1px solid var(--border-color); border-right: none; padding: 10px 12px; border-radius: var(--radius-md) 0 0 var(--radius-md); color: var(--text-muted);">
                                    <i class="fa-solid fa-globe"></i>
                                </span>
                                <input type="url" name="base_url" id="base_url_input" class="form-control" 
                                       value="<?= htmlspecialchars($aiConfig['base_url']) ?>" 
                                       style="border-radius: 0 var(--radius-md) var(--radius-md) 0;" required>
                            </div>
                            <small class="form-text" id="baseUrlHelp" style="color: var(--text-muted); font-size: 0.8rem;">
                                Format standar: <code>https://domain.com/v1</code> (tanpa slash di akhir)
                            </small>
                        </div>

                        <!-- API Key Field -->
                        <div class="form-group" style="margin-bottom: 20px;">
                            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 6px;">
                                <label class="form-label" style="font-weight: 700; margin: 0;">API Key:</label>
                                <span id="apiKeyStatusBadge">
                                    <?php if ($aiConfig['has_api_key']): ?>
                                        <span class="badge badge-success" style="font-size: 0.76rem;">
                                            <i class="fa-solid fa-lock"></i> Tersimpan: <?= htmlspecialchars($aiConfig['api_key_masked']) ?>
                                        </span>
                                    <?php endif; ?>
                                </span>
                            </div>
                            <div style="position: relative;">
                                <input type="password" name="api_key" id="api_key_input" class="form-control" 
                                       placeholder="<?= $aiConfig['has_api_key'] ? 'Biarkan kosong jika tidak ingin mengubah API Key tersimpan' : 'Masukkan API Key Anda...' ?>" 
                                       style="padding-right: 44px;" autocomplete="new-password">
                                <button type="button" id="btnToggleApiKey" 
                                        style="position: absolute; right: 10px; top: 50%; transform: translateY(-50%); background: none; border: none; color: var(--text-muted); cursor: pointer; padding: 6px;">
                                    <i class="fa-solid fa-eye" id="toggleApiKeyIcon"></i>
                                </button>
                            </div>
                            <small class="form-text" style="color: var(--text-muted); font-size: 0.8rem; display: block; margin-top: 5px;">
                                <i class="fa-solid fa-shield-halved" style="color: var(--success);"></i> 
                                Keamanan Terjamin: Nilai API Key dienkripsi dengan <strong>AES-256-CBC</strong> di server. Jika dikosongkan, API Key tersimpan tetap aktif.
                            </small>
                        </div>

                        <!-- Active Primary Model Selection (with Datalist & Free Text Input) -->
                        <div class="form-group" style="margin-bottom: 20px;">
                            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 6px;">
                                <label class="form-label" style="font-weight: 700; margin: 0;">Model AI Utama (Chatbot):</label>
                                <button type="button" id="btnFetchModels" class="btn btn-secondary btn-sm" style="padding: 4px 10px; font-size: 0.8rem;">
                                    <i class="fa-solid fa-cloud-arrow-down"></i> <span id="btnFetchText">Muat Model dari Provider</span>
                                </button>
                            </div>
                            <input type="text" name="model" id="model_input" list="model_list" class="form-control" 
                                   value="<?= htmlspecialchars($aiConfig['model']) ?>" 
                                   placeholder="Ketik atau pilih model, misal: qwen/qwen3.8-27b, gpt-4o-mini, deepseek-chat" 
                                   required style="font-weight: 600;">
                            <datalist id="model_list">
                                <?php 
                                $currModels = $presets[$aiConfig['provider']]['models'] ?? $defaultModels;
                                foreach ($currModels as $m): 
                                ?>
                                    <option value="<?= htmlspecialchars($m['id']) ?>"><?= htmlspecialchars($m['name'] ?? $m['id']) ?></option>
                                <?php endforeach; ?>
                            </datalist>
                            <div id="modelsNote" style="margin-top: 6px; font-size: 0.8rem; color: var(--text-muted);">
                                <i class="fa-solid fa-keyboard"></i> Anda dapat memilih dari daftar saran otomatis di atas atau mengetikkan model ID kustom mana pun yang didukung oleh API provider.
                            </div>
                        </div>

                        <!-- Fallback Model Selection -->
                        <div class="form-group" style="margin-bottom: 24px;">
                            <label class="form-label" style="font-weight: 700;">Model Cadangan (Fallback pada Provider yang Sama):</label>
                            <input type="text" name="fallback_model" id="fallback_model_input" list="fallback_model_list" class="form-control" 
                                   value="<?= htmlspecialchars($aiConfig['fallback_model']) ?>" 
                                   placeholder="Model cadangan pada provider yang sama...">
                            <datalist id="fallback_model_list">
                                <?php foreach ($currModels as $m): ?>
                                    <option value="<?= htmlspecialchars($m['id']) ?>"><?= htmlspecialchars($m['name'] ?? $m['id']) ?></option>
                                <?php endforeach; ?>
                            </datalist>
                            <small class="form-text" style="color: var(--text-muted); font-size: 0.8rem;">
                                Model cadangan otomatis digunakan jika model utama mengalami rate limit sementara (HTTP 429). Model cadangan wajib didukung oleh provider aktif yang sama.
                            </small>
                        </div>

                        <!-- Form Action Buttons -->
                        <div style="display: flex; gap: 12px; align-items: center; justify-content: flex-end; padding-top: 16px; border-top: 1px solid var(--border-color); flex-wrap: wrap;">
                            <button type="button" id="btnTestConnection" class="btn btn-secondary">
                                <i class="fa-solid fa-bolt"></i> <span id="btnTestText">Uji Koneksi</span>
                            </button>
                            <button type="submit" class="btn btn-primary" id="btnSaveConfig">
                                <i class="fa-solid fa-floppy-disk"></i> Simpan Konfigurasi
                            </button>
                        </div>
                    </form>
                </div>

                <!-- Right: Test Result & Live Provider Guidance -->
                <div>
                    <!-- Test Connection Live Result Box -->
                    <div class="card" id="testResultCard" style="margin-bottom: 24px; display: <?= !empty($aiConfig['last_error']) ? 'block' : 'none' ?>;">
                        <h4 style="font-size: 1.05rem; font-weight: 700; margin: 0 0 10px 0; display: flex; align-items: center; gap: 8px;">
                            <i class="fa-solid fa-square-poll-vertical" style="color: var(--primary);"></i> Hasil Uji Koneksi API
                        </h4>
                        <div id="testResultContent">
                            <?php if (!empty($aiConfig['last_error'])): ?>
                                <div class="alert alert-danger" style="margin: 0; font-size: 0.88rem;">
                                    <i class="fa-solid fa-circle-xmark"></i> 
                                    <?= htmlspecialchars($aiConfig['last_error']) ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- Dynamic Provider Documentation & Recommendations Card -->
                    <div class="card" id="providerInfoCard" style="margin-bottom: 24px;">
                        <h4 style="font-size: 1.05rem; font-weight: 700; margin: 0 0 12px 0; display: flex; align-items: center; gap: 8px;">
                            <i class="fa-solid fa-circle-info" style="color: var(--primary);"></i> Panduan & Rekomendasi Provider
                        </h4>
                        <div id="providerGuideContent" style="font-size: 0.86rem; line-height: 1.6; color: var(--text-main);">
                            <!-- Injected dynamically via JS based on selected provider -->
                        </div>
                    </div>

                    <!-- Security & Data Grounding Guarantee -->
                    <div class="card" style="background: #f8fafc; border: 1px dashed var(--border-color);">
                        <h4 style="font-size: 0.95rem; font-weight: 700; margin: 0 0 8px 0; color: var(--text-main);">
                            <i class="fa-solid fa-shield-check" style="color: var(--success);"></i> Garansi Akurasi Database & Keamanan
                        </h4>
                        <ul style="margin: 0; padding-left: 18px; font-size: 0.82rem; line-height: 1.6; color: var(--text-muted);">
                            <li>Chatbot terikat 100% pada data database MySQL ServisYuk (harga resmi, teknisi, status tiket, invoice).</li>
                            <li>Model AI reasoning (seperti DeepSeek-R1) secara otomatis dibersihkan dari tag internal <code>&lt;think&gt;</code> sebelum ditampilkan ke pelanggan.</li>
                            <li>Tepat satu provider AI aktif dalam satu waktu untuk stabilitas operasional.</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </main>
</div>

<!-- Interactive Client-side Script -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    const providerPresets = <?= json_encode($presets, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>;
    const providerKeys = <?= json_encode($aiConfig['provider_keys'] ?? [], JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>;

    const btnToggle = document.getElementById('btnToggleApiKey');
    const inputApiKey = document.getElementById('api_key_input');
    const iconToggle = document.getElementById('toggleApiKeyIcon');

    const btnTest = document.getElementById('btnTestConnection');
    const btnTestText = document.getElementById('btnTestText');
    const btnQuickTest = document.getElementById('btnQuickTest');

    const btnFetch = document.getElementById('btnFetchModels');
    const btnFetchText = document.getElementById('btnFetchText');
    const modelInput = document.getElementById('model_input');
    const modelList = document.getElementById('model_list');
    const fallbackModelInput = document.getElementById('fallback_model_input');
    const fallbackModelList = document.getElementById('fallback_model_list');
    const modelsNote = document.getElementById('modelsNote');

    const testResultCard = document.getElementById('testResultCard');
    const testResultContent = document.getElementById('testResultContent');

    const providerSelect = document.getElementById('provider_select');
    const baseUrlInput = document.getElementById('base_url_input');
    const baseUrlHelp = document.getElementById('baseUrlHelp');
    const providerGuideContent = document.getElementById('providerGuideContent');

    // 1. Toggle Password Visibility
    if (btnToggle && inputApiKey && iconToggle) {
        btnToggle.addEventListener('click', function() {
            if (inputApiKey.type === 'password') {
                inputApiKey.type = 'text';
                iconToggle.classList.replace('fa-eye', 'fa-eye-slash');
            } else {
                inputApiKey.type = 'password';
                iconToggle.classList.replace('fa-eye-slash', 'fa-eye');
            }
        });
    }

    // 2. Render Provider Info & Update Datalists
    function updateProviderView(providerKey, isInitialLoad = false) {
        const preset = providerPresets[providerKey];
        if (!preset) return;

        // Auto-fill Base URL & models jika provider berganti
        if (!isInitialLoad) {
            if (preset.base_url) {
                baseUrlInput.value = preset.base_url;
            }
            if (preset.default_model) {
                modelInput.value = preset.default_model;
            }
            if (preset.default_fallback) {
                fallbackModelInput.value = preset.default_fallback;
            }
        }

        // Update API Key placeholder & status badge khusus provider yang dipilih
        const savedMaskedKey = providerKeys[providerKey] || null;
        const apiKeyBadge = document.getElementById('apiKeyStatusBadge');

        if (inputApiKey) {
            if (savedMaskedKey) {
                inputApiKey.placeholder = `Biarkan kosong jika tetap menggunakan API Key tersimpan (${savedMaskedKey})`;
            } else {
                inputApiKey.placeholder = preset.key_placeholder || 'Masukkan API Key baru...';
            }
        }

        if (apiKeyBadge) {
            if (savedMaskedKey) {
                apiKeyBadge.innerHTML = `<span class="badge badge-success" style="font-size: 0.76rem;"><i class="fa-solid fa-lock"></i> Tersimpan: ${savedMaskedKey}</span>`;
            } else {
                apiKeyBadge.innerHTML = `<span class="badge badge-warning" style="font-size: 0.76rem;"><i class="fa-solid fa-key"></i> Perlu API Key untuk ${preset.name}</span>`;
            }
        }

        // Update Base URL help text
        if (baseUrlHelp && preset.base_url) {
            baseUrlHelp.innerHTML = `Default ${preset.name}: <code>${preset.base_url}</code>`;
        }

        // Update Datalists
        if (preset.models && Array.isArray(preset.models)) {
            modelList.innerHTML = '';
            fallbackModelList.innerHTML = '';
            preset.models.forEach(m => {
                const opt1 = document.createElement('option');
                opt1.value = m.id;
                opt1.textContent = m.name || m.id;
                modelList.appendChild(opt1);

                const opt2 = document.createElement('option');
                opt2.value = m.id;
                opt2.textContent = m.name || m.id;
                fallbackModelList.appendChild(opt2);
            });
        }

        // Update Right Guidance Card
        if (providerGuideContent) {
            let guideHtml = '';
            if (providerKey === 'groq') {
                guideHtml = `
                    <div style="margin-bottom:12px; padding:10px 12px; background:#f8fafc; border-radius:var(--radius-md); border-left:3px solid #6366f1;">
                        <strong style="color:#4338ca;">Groq Cloud (Sangat Cepat & Gratis)</strong>
                        <p style="margin:3px 0 0 0; font-size:0.82rem; color:var(--text-muted);">
                            Kecepatan inferensi tinggi (> 400 token/detik). Model unggulan: <strong>qwen/qwen3.8-27b</strong> dan <strong>openai/gpt-oss-20b</strong>.
                        </p>
                    </div>
                    <div style="font-size:0.82rem; line-height:1.6; color:var(--text-muted);">
                        Dapatkan API Key di: <a href="https://console.groq.com/keys" target="_blank" rel="noopener" style="color:var(--primary); font-weight:600;">console.groq.com/keys</a>.
                    </div>
                `;
            } else if (providerKey === 'gemini') {
                guideHtml = `
                    <div style="margin-bottom:12px; padding:10px 12px; background:#f8fafc; border-radius:var(--radius-md); border-left:3px solid #ea4335;">
                        <strong style="color:#b91c1c;">Google Gemini (OpenAI-Compatible)</strong>
                        <p style="margin:3px 0 0 0; font-size:0.82rem; color:var(--text-muted);">
                            Model Resmi Aktif: <strong>gemini-3.8-flash</strong> (Generasi Terbaru - Super Cepat & Cerdas).
                        </p>
                    </div>
                    <div style="font-size:0.82rem; line-height:1.6; color:var(--text-muted);">
                        Dapatkan API Key di: <a href="https://aistudio.google.com/app/apikey" target="_blank" rel="noopener" style="color:var(--primary); font-weight:600;">Google AI Studio (aistudio.google.com)</a>.
                        <div style="margin-top:8px; padding:8px 10px; background:#eff6ff; border:1px solid #bfdbfe; border-radius:6px; font-size:0.78rem; color:#1e40af;">
                            <strong><i class="fa-solid fa-circle-info"></i> Endpoint OpenAI-Compatible:</strong><br>
                            Endpoint: <code>https://generativelanguage.googleapis.com/v1beta/openai</code>.<br>
                            Model aktif yang didukung: <code>gemini-3.8-flash</code> (atau <code>models/gemini-3.8-flash</code>).
                        </div>
                    </div>
                `;
            } else if (providerKey === 'openai') {
                guideHtml = `
                    <div style="margin-bottom:12px; padding:10px 12px; background:#f8fafc; border-radius:var(--radius-md); border-left:3px solid #10b981;">
                        <strong style="color:#047857;">OpenAI Official</strong>
                        <p style="margin:3px 0 0 0; font-size:0.82rem; color:var(--text-muted);">
                            Model standar industri: <strong>gpt-4o-mini</strong> (hemat biaya & akurat) atau <strong>gpt-4o</strong>.
                        </p>
                    </div>
                    <div style="font-size:0.82rem; line-height:1.6; color:var(--text-muted);">
                        Dapatkan API Key di: <a href="https://platform.openai.com/api-keys" target="_blank" rel="noopener" style="color:var(--primary); font-weight:600;">platform.openai.com/api-keys</a>.
                    </div>
                `;
            } else if (providerKey === 'deepseek') {
                guideHtml = `
                    <div style="margin-bottom:12px; padding:10px 12px; background:#f8fafc; border-radius:var(--radius-md); border-left:3px solid #0284c7;">
                        <strong style="color:#0369a1;">DeepSeek AI</strong>
                        <p style="margin:3px 0 0 0; font-size:0.82rem; color:var(--text-muted);">
                            Mendukung <strong>deepseek-chat</strong> (V3) dan <strong>deepseek-reasoner</strong> (R1). Tag penalaran internal otomatis dibersihkan oleh sistem ServisYuk.
                        </p>
                    </div>
                    <div style="font-size:0.82rem; line-height:1.6; color:var(--text-muted);">
                        Dapatkan API Key di: <a href="https://platform.deepseek.com/api_keys" target="_blank" rel="noopener" style="color:var(--primary); font-weight:600;">platform.deepseek.com/api_keys</a>.
                    </div>
                `;
            } else if (providerKey === 'openrouter') {
                guideHtml = `
                    <div style="margin-bottom:12px; padding:10px 12px; background:#f8fafc; border-radius:var(--radius-md); border-left:3px solid #f59e0b;">
                        <strong style="color:#b45309;">OpenRouter Multi-Model</strong>
                        <p style="margin:3px 0 0 0; font-size:0.82rem; color:var(--text-muted);">
                            Satu API Key untuk mengakses ratusan model open-source dan proprietary dari Meta, Mistral, Google, dll.
                        </p>
                    </div>
                    <div style="font-size:0.82rem; line-height:1.6; color:var(--text-muted);">
                        Dapatkan API Key di: <a href="https://openrouter.ai/keys" target="_blank" rel="noopener" style="color:var(--primary); font-weight:600;">openrouter.ai/keys</a>.
                    </div>
                `;
            } else if (providerKey === 'nvidia') {
                guideHtml = `
                    <div style="margin-bottom:12px; padding:10px 12px; background:#f8fafc; border-radius:var(--radius-md); border-left:3px solid #76b900;">
                        <strong style="color:#4d7c0f;">NVIDIA NIM (Inference Microservice)</strong>
                        <p style="margin:3px 0 0 0; font-size:0.82rem; color:var(--text-muted);">
                            Inference terakselerasi GPU NVIDIA untuk model Llama 3.1 Nemotron dan Mistral.
                        </p>
                    </div>
                    <div style="font-size:0.82rem; line-height:1.6; color:var(--text-muted);">
                        Dapatkan API Key di: <a href="https://build.nvidia.com/explore/discover" target="_blank" rel="noopener" style="color:var(--primary); font-weight:600;">build.nvidia.com</a>.
                        <div style="margin-top:8px; padding:8px 10px; background:#fef3c7; border:1px solid #fde68a; border-radius:6px; font-size:0.78rem; color:#92400e;">
                            <strong><i class="fa-solid fa-lightbulb"></i> Tips Pembuatan API Key NVIDIA:</strong><br>
                            Pastikan Anda mengklik tombol <strong>"Get API Key"</strong> langsung dari halaman model spesifik (misal di halaman <em>Llama 3.1 Nemotron 70B</em>) pada <a href="https://build.nvidia.com/nvidia/llama-3-1-nemotron-70b-instruct" target="_blank" rel="noopener" style="color:#b45309; text-decoration:underline;">build.nvidia.com</a> agar key tersebut terikat dengan izin serverless (Public API Endpoints).
                        </div>
                    </div>
                `;
            } else {
                guideHtml = `
                    <div style="margin-bottom:12px; padding:10px 12px; background:#f8fafc; border-radius:var(--radius-md); border-left:3px solid #64748b;">
                        <strong style="color:#334155;">Custom OpenAI-Compatible</strong>
                        <p style="margin:3px 0 0 0; font-size:0.82rem; color:var(--text-muted);">
                            Gunakan endpoint mandiri (vLLM, Ollama, LocalAI, LM Studio, LiteLLM, dsb.) dengan format OpenAI standard <code>/chat/completions</code>.
                        </p>
                    </div>
                `;
            }
            providerGuideContent.innerHTML = guideHtml;
        }
    }

    // Initialize UI on page load
    updateProviderView(providerSelect ? providerSelect.value : 'groq', true);

    if (providerSelect) {
        ['change', 'input'].forEach(evt => {
            providerSelect.addEventListener(evt, function() {
                updateProviderView(this.value, false);
            });
        });
    }

    if (inputApiKey) {
        inputApiKey.addEventListener('input', function() {
            const currentProvider = providerSelect ? providerSelect.value : 'groq';
            const preset = providerPresets[currentProvider];
            const savedMaskedKey = providerKeys[currentProvider] || null;
            const apiKeyBadge = document.getElementById('apiKeyStatusBadge');
            if (!apiKeyBadge) return;

            if (this.value.trim().length > 0) {
                apiKeyBadge.innerHTML = `<span class="badge badge-info" style="font-size: 0.76rem;"><i class="fa-solid fa-pen"></i> API Key Baru Dimasukkan</span>`;
            } else {
                if (savedMaskedKey) {
                    apiKeyBadge.innerHTML = `<span class="badge badge-success" style="font-size: 0.76rem;"><i class="fa-solid fa-lock"></i> Tersimpan: ${savedMaskedKey}</span>`;
                } else {
                    apiKeyBadge.innerHTML = `<span class="badge badge-warning" style="font-size: 0.76rem;"><i class="fa-solid fa-key"></i> Perlu API Key untuk ${preset ? preset.name : currentProvider}</span>`;
                }
            }
        });
    }

    // 3. Helper: Perform Connection Test
    async function performTest() {
        const provider = providerSelect ? providerSelect.value : 'groq';
        let baseUrl = baseUrlInput ? baseUrlInput.value.trim() : '';
        const apiKey = inputApiKey ? inputApiKey.value.trim() : '';
        const model = modelInput ? modelInput.value.trim() : '';

        // Auto-fill preset base_url if empty
        if (!baseUrl && providerPresets[provider] && providerPresets[provider].base_url) {
            baseUrl = providerPresets[provider].base_url;
            if (baseUrlInput) baseUrlInput.value = baseUrl;
        }

        if (!model) {
            alert('Silakan tentukan model AI utama terlebih dahulu.');
            return;
        }

        // Tampilkan loading state
        btnTest.disabled = true;
        if (btnQuickTest) btnQuickTest.disabled = true;
        btnTestText.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Menguji...';

        testResultCard.style.display = 'block';
        testResultContent.innerHTML = `
            <div style="display:flex; align-items:center; gap:10px; color:var(--text-muted); font-size:0.88rem; padding:10px;">
                <i class="fa-solid fa-circle-notch fa-spin" style="font-size:1.2rem; color:var(--primary);"></i>
                Menguji koneksi ke endpoint <strong>${baseUrl || 'default'}</strong> dengan model <strong>${model}</strong>...
            </div>
        `;

        try {
            const formData = new FormData();
            formData.append('provider', provider);
            formData.append('base_url', baseUrl);
            formData.append('api_key', apiKey);
            formData.append('model', model);

            const res = await fetch('<?= BASE_URL ?>/index.php?page=admin_ai_test_connection', {
                method: 'POST',
                body: formData,
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            });

            const data = await res.json();
            if (data.success) {
                testResultContent.innerHTML = `
                    <div class="alert alert-success" style="margin:0; font-size:0.88rem;">
                        <div style="font-weight:700; margin-bottom:4px; display:flex; align-items:center; gap:6px;">
                            <i class="fa-solid fa-circle-check"></i> ${data.message}
                        </div>
                        <div style="font-size:0.8rem; color:#14532d;">
                            Model: <strong>${data.model}</strong> &bull; Latensi: <strong>${data.latency_ms} ms</strong> &bull; Status HTTP: 200 OK
                        </div>
                    </div>
                `;
            } else {
                testResultContent.innerHTML = `
                    <div class="alert alert-danger" style="margin:0; font-size:0.88rem;">
                        <div style="font-weight:700; margin-bottom:4px; display:flex; align-items:center; gap:6px;">
                            <i class="fa-solid fa-circle-xmark"></i> Pengujian Koneksi Gagal
                        </div>
                        <div style="font-size:0.82rem;">
                            ${data.message || 'Terjadi kesalahan saat menghubungi API provider.'}
                        </div>
                    </div>
                `;
            }
        } catch (err) {
            testResultContent.innerHTML = `
                <div class="alert alert-danger" style="margin:0; font-size:0.88rem;">
                    <i class="fa-solid fa-triangle-exclamation"></i> Terjadi kesalahan jaringan saat melakukan request uji koneksi: ${err.message}
                </div>
            `;
        } finally {
            btnTest.disabled = false;
            if (btnQuickTest) btnQuickTest.disabled = false;
            btnTestText.innerHTML = 'Uji Koneksi';
        }
    }

    if (btnTest) {
        btnTest.addEventListener('click', performTest);
    }
    if (btnQuickTest) {
        btnQuickTest.addEventListener('click', performTest);
    }

    // 4. Helper: Fetch Models List
    if (btnFetch) {
        btnFetch.addEventListener('click', async function() {
            const provider = providerSelect ? providerSelect.value : 'groq';
            const baseUrl = baseUrlInput ? baseUrlInput.value.trim() : '';
            const apiKey = inputApiKey ? inputApiKey.value.trim() : '';

            btnFetch.disabled = true;
            btnFetchText.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Memuat...';

            try {
                const formData = new FormData();
                formData.append('provider', provider);
                formData.append('base_url', baseUrl);
                formData.append('api_key', apiKey);

                const res = await fetch('<?= BASE_URL ?>/index.php?page=admin_ai_fetch_models', {
                    method: 'POST',
                    body: formData,
                    headers: { 'X-Requested-With': 'XMLHttpRequest' }
                });

                const data = await res.json();
                if (data.models && Array.isArray(data.models)) {
                    modelList.innerHTML = '';
                    fallbackModelList.innerHTML = '';

                    data.models.forEach(m => {
                        const opt = document.createElement('option');
                        opt.value = m.id;
                        opt.textContent = m.name || m.id;
                        modelList.appendChild(opt);

                        const optFallback = document.createElement('option');
                        optFallback.value = m.id;
                        optFallback.textContent = m.name || m.id;
                        fallbackModelList.appendChild(optFallback);
                    });

                    // Jika input model kosong, pasang model pertama
                    if (!modelInput.value && data.models.length > 0) {
                        modelInput.value = data.models[0].id;
                    }

                    modelsNote.innerHTML = `
                        <div style="margin-top:6px; color:${data.source === 'live' ? '#15803d' : '#b45309'}; font-weight:600;">
                            <i class="fa-solid fa-check-double"></i> ${data.message}
                        </div>
                    `;
                } else {
                    modelsNote.innerHTML = `<span style="color:var(--danger);"><i class="fa-solid fa-circle-exclamation"></i> ${data.message || 'Gagal memuat model.'}</span>`;
                }
            } catch (err) {
                modelsNote.innerHTML = `<span style="color:var(--danger);"><i class="fa-solid fa-circle-exclamation"></i> Gagal menghubungi endpoint models: ${err.message}</span>`;
            } finally {
                btnFetch.disabled = false;
                btnFetchText.innerHTML = 'Muat Model dari Provider';
            }
        });
    }
});
</script>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
