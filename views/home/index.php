<?php
$pageTitle = 'ServisYuk - Jasa Perbaikan Peralatan Rumah Tangga Berbasis Web + AI';
require_once __DIR__ . '/../layouts/header.php';
require_once __DIR__ . '/../layouts/navbar.php';
$flash = Helper::getFlash();
?>

<?php if ($flash): ?>
    <div class="container" style="margin-top: 20px;">
        <div class="alert alert-<?= $flash['type'] ?>">
            <i class="fa-solid fa-circle-info"></i>
            <?= htmlspecialchars($flash['message']) ?>
        </div>
    </div>
<?php endif; ?>

<!-- Hero Section -->
<section class="hero-section">
    <div class="container">
        <div class="hero-grid">
            <div class="hero-content">
                <div class="hero-tag">
                    <i class="fa-solid fa-wand-magic-sparkles"></i>
                    Didukung AI Diagnostic Triage
                </div>
                <h1 class="hero-title">
                    Solusi Perbaikan Peralatan Rumah Tangga <span class="gradient-text">Cepat, Transparan & Bergaransi</span>
                </h1>
                <p class="hero-subtitle">
                    Ketahui penyebab kerusakan dan estimasi biaya secara instan lewat bantuan AI. Teknisi tersertifikasi kami siap datang langsung ke rumah Anda.
                </p>

                <div class="hero-actions">
                    <a href="<?= BASE_URL ?>/index.php?page=ai_diagnostic" class="btn btn-primary btn-lg">
                        <i class="fa-solid fa-wand-magic-sparkles"></i> Coba AI Diagnosa Gratis
                    </a>
                    <a href="<?= BASE_URL ?>/index.php?page=customer_booking_create" class="btn btn-secondary btn-lg">
                        <i class="fa-solid fa-calendar-check"></i> Pesan Teknisi
                    </a>
                </div>

                <div class="hero-features">
                    <div class="feature-pill">
                        <i class="fa-solid fa-circle-check"></i> 100% Teknisi Terverifikasi
                    </div>
                    <div class="feature-pill">
                        <i class="fa-solid fa-shield-halved"></i> Garansi Servis 30 Hari
                    </div>
                    <div class="feature-pill">
                        <i class="fa-solid fa-money-bill-transfer"></i> Tarif Transparan
                    </div>
                </div>
            </div>

            <!-- Quick AI Diagnosis Widget in Hero -->
            <div class="hero-widget">
                <div class="ai-interactive-card">
                    <div class="ai-card-badge">
                        <i class="fa-solid fa-bolt"></i> AI Quick Triage
                    </div>
                    <h3 style="font-size: 1.3rem; margin-bottom: 8px;">Ada Masalah Alat Rumah Tangga?</h3>
                    <p style="font-size: 0.88rem; color: var(--text-muted); margin-bottom: 20px;">
                        Pilih jenis alat dan ketik gejalanya untuk analisis otomatis dalam hitungan detik.
                    </p>

                    <form action="<?= BASE_URL ?>/index.php?page=ai_diagnostic" method="GET">
                        <input type="hidden" name="page" value="ai_diagnostic">
                        <div class="form-group">
                            <label class="form-label">Jenis Peralatan</label>
                            <select name="category" class="form-select" required>
                                <option value="">-- Pilih Jenis Peralatan --</option>
                                <?php foreach ($categories as $cat): ?>
                                    <option value="<?= htmlspecialchars($cat['name']) ?>"><?= htmlspecialchars($cat['name']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="form-group">
                            <label class="form-label">Keluhan / Gejala Utama</label>
                            <input type="text" name="symptom" class="form-control" placeholder="Contoh: AC tidak dingin hanya keluar angin" required>
                        </div>

                        <button type="submit" class="btn btn-primary btn-block">
                            <i class="fa-solid fa-magnifying-glass-chart"></i> Analisis Kerusakan Sekarang
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Quick Tracking Lookup Banner -->
<section style="padding: 40px 0; background: #ffffff; border-bottom: 1px solid var(--border-color);">
    <div class="container">
        <div style="background: linear-gradient(135deg, #1e1b4b 0%, #0f172a 100%); border-radius: var(--radius-xl); padding: 32px 40px; color: #ffffff; display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 24px;">
            <div>
                <span class="badge badge-info" style="margin-bottom: 8px;"><i class="fa-solid fa-radar"></i> Live Tracking</span>
                <h3 style="font-size: 1.5rem; color:#ffffff; margin-bottom: 4px;">Lacak Status Perbaikan Anda</h3>
                <p style="font-size: 0.9rem; color: #94a3b8;">Masukkan Nomor Tiket (Booking Code) untuk memantau proses teknisi secara real-time.</p>
            </div>
            <form action="<?= BASE_URL ?>/index.php" method="GET" style="display: flex; gap: 10px; width: 100%; max-width: 440px;">
                <input type="hidden" name="page" value="tracking">
                <input type="text" name="code" class="form-control" placeholder="Contoh: SRV-20260901-001" style="background:#ffffff; color:#0f172a;" required>
                <button type="submit" class="btn btn-primary" style="white-space:nowrap;">
                    <i class="fa-solid fa-location-crosshairs"></i> Lacak
                </button>
            </form>
        </div>
    </div>
</section>

<!-- Categories & Services Section -->
<section style="padding: 80px 0;">
    <div class="container">
        <div style="text-align: center; max-width: 680px; margin: 0 auto 50px;">
            <span class="hero-tag"><i class="fa-solid fa-toolbox"></i> Katalog Layanan</span>
            <h2 style="font-size: 2.3rem; margin-bottom: 12px;">Kategori Peralatan yang Kami Layani</h2>
            <p style="color: var(--text-muted);">Pilih jenis peralatan rumah tangga Anda untuk melihat detail layanan, estimasi biaya, dan teknisi spesialis kami.</p>
        </div>

        <div class="category-grid">
            <?php foreach ($categories as $cat): ?>
                <a href="<?= BASE_URL ?>/index.php?page=services#cat-<?= $cat['id'] ?>" class="category-card">
                    <div class="category-icon-box">
                        <i class="<?= htmlspecialchars($cat['icon']) ?>"></i>
                    </div>
                    <div class="category-title"><?= htmlspecialchars($cat['name']) ?></div>
                    <div class="category-desc"><?= htmlspecialchars($cat['description']) ?></div>
                    <div class="category-fee">
                        <i class="fa-solid fa-tag"></i> Biaya Cek Awal: <?= Helper::formatRupiah($cat['base_inspection_fee']) ?>
                    </div>
                </a>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- 4-Step Smart Workflow Section -->
<section style="padding: 80px 0; background: #ffffff; border-top: 1px solid var(--border-color); border-bottom: 1px solid var(--border-color);">
    <div class="container">
        <div style="text-align: center; max-width: 680px; margin: 0 auto 60px;">
            <span class="hero-tag"><i class="fa-solid fa-diagram-project"></i> Alur Pemesanan</span>
            <h2 style="font-size: 2.3rem; margin-bottom: 12px;">Bagaimana ServisYuk Bekerja?</h2>
            <p style="color: var(--text-muted);">Hanya 4 langkah mudah untuk mengembalikan peralatan rumah Anda berfungsi normal seperti baru.</p>
        </div>

        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap: 30px;">
            <div class="card" style="border: none; background: #f8fafc; text-align: center;">
                <div style="width: 60px; height: 60px; border-radius: 50%; background: var(--primary); color: #ffffff; display: flex; align-items: center; justify-content: center; font-size: 1.5rem; font-weight: 800; margin: 0 auto 20px; box-shadow: 0 4px 14px var(--primary-glow);">
                    1
                </div>
                <h4 style="font-size: 1.2rem; margin-bottom: 8px;">Diagnosa dengan AI</h4>
                <p style="font-size: 0.9rem; color: var(--text-muted);">Masukkan gejala kerusakan dan dapatkan estimasi biaya serta tips keselamatan awal secara instan.</p>
            </div>

            <div class="card" style="border: none; background: #f8fafc; text-align: center;">
                <div style="width: 60px; height: 60px; border-radius: 50%; background: var(--secondary); color: #ffffff; display: flex; align-items: center; justify-content: center; font-size: 1.5rem; font-weight: 800; margin: 0 auto 20px;">
                    2
                </div>
                <h4 style="font-size: 1.2rem; margin-bottom: 8px;">Pilih Jadwal Fleksibel</h4>
                <p style="font-size: 0.9rem; color: var(--text-muted);">Tentukan tanggal dan sesi waktu yang Anda inginkan agar teknisi datang sesuai jadwal kenyamanan Anda.</p>
            </div>

            <div class="card" style="border: none; background: #f8fafc; text-align: center;">
                <div style="width: 60px; height: 60px; border-radius: 50%; background: var(--accent-purple); color: #ffffff; display: flex; align-items: center; justify-content: center; font-size: 1.5rem; font-weight: 800; margin: 0 auto 20px;">
                    3
                </div>
                <h4 style="font-size: 1.2rem; margin-bottom: 8px;">Perbaikan Profesional</h4>
                <p style="font-size: 0.9rem; color: var(--text-muted);">Teknisi spesialis datang ke lokasi, melakukan perbaikan transparan dengan suku cadang bergaransi.</p>
            </div>

            <div class="card" style="border: none; background: #f8fafc; text-align: center;">
                <div style="width: 60px; height: 60px; border-radius: 50%; background: var(--success); color: #ffffff; display: flex; align-items: center; justify-content: center; font-size: 1.5rem; font-weight: 800; margin: 0 auto 20px;">
                    4
                </div>
                <h4 style="font-size: 1.2rem; margin-bottom: 8px;">Selesai & Bergaransi</h4>
                <p style="font-size: 0.9rem; color: var(--text-muted);">Uji fungsi peralatan bersama teknisi, bayar sesuai invoice resmi, dan nikmati garansi pengerjaan 30 hari.</p>
            </div>
        </div>
    </div>
</section>

<!-- Customer Reviews Section -->
<?php if (!empty($reviews)): ?>
<section style="padding: 80px 0;">
    <div class="container">
        <div style="text-align: center; max-width: 680px; margin: 0 auto 50px;">
            <span class="hero-tag"><i class="fa-solid fa-star"></i> Testimoni Pelanggan</span>
            <h2 style="font-size: 2.3rem; margin-bottom: 12px;">Kepuasan Nyata dari Pelanggan Kami</h2>
            <p style="color: var(--text-muted);">Dengarkan pengalaman pelanggan yang telah mempercayakan perbaikan alat rumah tangga mereka kepada kami.</p>
        </div>

        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 24px;">
            <?php foreach ($reviews as $rev): ?>
                <div class="card" style="display: flex; flex-direction: column; justify-content: space-between;">
                    <div>
                        <div style="display: flex; gap: 4px; color: #f59e0b; margin-bottom: 14px; font-size: 1.1rem;">
                            <?php for ($i = 1; $i <= 5; $i++): ?>
                                <i class="<?= $i <= $rev['rating'] ? 'fa-solid fa-star' : 'fa-regular fa-star' ?>"></i>
                            <?php endfor; ?>
                        </div>
                        <p style="font-style: italic; color: #334155; margin-bottom: 20px; line-height: 1.6;">
                            "<?= htmlspecialchars($rev['comment']) ?>"
                        </p>
                    </div>

                    <div style="display: flex; align-items: center; gap: 12px; padding-top: 16px; border-top: 1px solid var(--border-color);">
                        <div class="user-avatar" style="width: 40px; height: 40px; font-size: 0.95rem;">
                            <?= strtoupper(substr($rev['customer_name'], 0, 1)) ?>
                        </div>
                        <div>
                            <div style="font-weight: 700; font-size: 0.95rem;"><?= htmlspecialchars($rev['customer_name']) ?></div>
                            <div style="font-size: 0.78rem; color: var(--text-muted);">
                                Servis <?= htmlspecialchars($rev['category_name']) ?> &bull; Teknisi: <?= htmlspecialchars($rev['technician_name'] ?? 'Teknisi ServisYuk') ?>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php endif; ?>

<!-- Call To Action -->
<section style="padding: 70px 0; background: linear-gradient(135deg, #4f46e5 0%, #06b6d4 100%); color: #ffffff; text-align: center;">
    <div class="container" style="max-width: 720px;">
        <h2 style="font-size: 2.6rem; color: #ffffff; margin-bottom: 16px;">Peralatan Rumah Anda Rusak Hari Ini?</h2>
        <p style="font-size: 1.15rem; color: rgba(255, 255, 255, 0.9); margin-bottom: 32px;">
            Gunakan AI Diagnostic Assistant kami sekarang untuk mengetahui estimasi biaya atau langsung hubungi teknisi terbaik kami.
        </p>
        <div style="display: flex; justify-content: center; gap: 16px; flex-wrap: wrap;">
            <a href="<?= BASE_URL ?>/index.php?page=ai_diagnostic" class="btn btn-secondary btn-lg" style="color: var(--primary);">
                <i class="fa-solid fa-wand-magic-sparkles"></i> Mulai Diagnosa AI
            </a>
            <a href="<?= BASE_URL ?>/index.php?page=customer_booking_create" class="btn btn-primary btn-lg" style="background: #0f172a; border-color: #0f172a;">
                <i class="fa-solid fa-calendar-plus"></i> Pesan Teknisi Langsung
            </a>
        </div>
    </div>
</section>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
