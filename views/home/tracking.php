<?php
$pageTitle = 'Lacak Status Perbaikan - ServisYuk';
require_once __DIR__ . '/../layouts/header.php';
require_once __DIR__ . '/../layouts/navbar.php';
$extraScript = 'tracking.js';
?>

<div class="container" style="padding: 40px 24px 80px; max-width: 900px;">
    <div style="text-align: center; margin-bottom: 40px;">
        <span class="hero-tag"><i class="fa-solid fa-radar"></i> Pelacakan Real-Time</span>
        <h1 style="font-size: 2.5rem; font-weight: 800; margin-bottom: 12px;">Lacak Status Perbaikan</h1>
        <p style="color: var(--text-muted); font-size: 1.05rem;">
            Pantau tahapan pengerjaan perbaikan peralatan rumah tangga Anda mulai dari penugasan hingga selesai.
        </p>
    </div>

    <!-- Search Form -->
    <div class="card" style="margin-bottom: 32px; box-shadow: var(--shadow-lg);">
        <form id="tracking_form" style="display: flex; gap: 12px; flex-wrap: wrap;">
            <div style="flex-grow: 1; min-width: 260px;">
                <label class="form-label">Nomor Tiket (Kode Booking) atau Nomor Telepon</label>
                <div style="position: relative;">
                    <input type="text" id="tracking_code_input" class="form-control" placeholder="Contoh: SRV-20260901-001 atau 081234567890" value="<?= htmlspecialchars($_GET['code'] ?? '') ?>" required>
                </div>
            </div>
            <div style="display: flex; align-items: flex-end;">
                <button type="submit" class="btn btn-primary btn-lg" style="height: 44px; padding: 0 28px;">
                    <i class="fa-solid fa-magnifying-glass"></i> Lacak Sekarang
                </button>
            </div>
        </form>
    </div>

    <!-- Loading State -->
    <div id="tracking_loading" style="display: none; text-align: center; padding: 40px;">
        <div style="font-size: 2rem; color: var(--primary); margin-bottom: 12px;">
            <i class="fa-solid fa-circle-notch fa-spin"></i>
        </div>
        <p style="color: var(--text-muted); font-weight: 500;">Sedang mencari data pesanan...</p>
    </div>

    <!-- Not Found State -->
    <div id="tracking_not_found" class="alert alert-error" style="display: none;"></div>

    <!-- Tracking Result Container -->
    <div id="tracking_result_container" class="card" style="display: none; animation: fadeIn 0.3s ease-out;">
        <div style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 12px; padding-bottom: 20px; border-bottom: 1px solid var(--border-color); margin-bottom: 24px;">
            <div>
                <span style="font-size: 0.8rem; color: var(--text-muted); text-transform: uppercase; letter-spacing: 0.05em;">Nomor Tiket Pesanan</span>
                <h3 id="track_code_display" style="font-size: 1.6rem; color: var(--primary); margin-top: 2px;">-</h3>
            </div>
            <div id="track_status_badge" class="badge badge-info" style="font-size: 0.9rem; padding: 6px 14px;">-</div>
        </div>

        <!-- Progress Stepper -->
        <div class="tracking-stepper">
            <div id="track_step_1" class="step-item">
                <div class="step-icon"><i class="fa-solid fa-clipboard-check"></i></div>
                <div class="step-title">Diterima</div>
                <div class="step-time">Menunggu Admin</div>
            </div>
            <div id="track_step_2" class="step-item">
                <div class="step-icon"><i class="fa-solid fa-user-check"></i></div>
                <div class="step-title">Ditugaskan</div>
                <div class="step-time">Teknisi Siap</div>
            </div>
            <div id="track_step_3" class="step-item">
                <div class="step-icon"><i class="fa-solid fa-motorcycle"></i></div>
                <div class="step-title">Perjalanan</div>
                <div class="step-time">Menuju Lokasi</div>
            </div>
            <div id="track_step_4" class="step-item">
                <div class="step-icon"><i class="fa-solid fa-screwdriver-wrench"></i></div>
                <div class="step-title">Pengerjaan</div>
                <div class="step-time">Inspeksi/Servis</div>
            </div>
            <div id="track_step_5" class="step-item">
                <div class="step-icon"><i class="fa-solid fa-circle-check"></i></div>
                <div class="step-title">Selesai</div>
                <div class="step-time">Uji Fungsi & Garansi</div>
            </div>
        </div>

        <!-- Status Summary Note -->
        <div style="background: #f8fafc; border-left: 4px solid var(--primary); padding: 16px 20px; border-radius: var(--radius-md); margin-bottom: 28px;">
            <div style="font-weight: 700; font-size: 0.95rem; margin-bottom: 4px;">Status Terkini:</div>
            <div id="track_status_desc" style="color: var(--text-muted); font-size: 0.9rem;">-</div>
        </div>

        <!-- Grid Detail -->
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 24px;">
            <div style="background: #ffffff; border: 1px solid var(--border-color); border-radius: var(--radius-md); padding: 16px;">
                <div style="font-size: 0.78rem; color: var(--text-muted); text-transform: uppercase;">Peralatan & Jadwal</div>
                <div id="track_category_display" style="font-weight: 700; font-size: 1.05rem; margin: 4px 0;">-</div>
                <div id="track_schedule_display" style="font-size: 0.88rem; color: var(--text-muted);">-</div>
            </div>

            <div style="background: #ffffff; border: 1px solid var(--border-color); border-radius: var(--radius-md); padding: 16px;">
                <div style="font-size: 0.78rem; color: var(--text-muted); text-transform: uppercase;">Keluhan Masalah</div>
                <div id="track_issue_display" style="font-size: 0.9rem; color: var(--text-main); margin-top: 4px; line-height: 1.4;">-</div>
            </div>
        </div>

        <!-- Technician Box (If assigned) -->
        <div id="track_technician_box" style="display: none; align-items: center; justify-content: space-between; background: linear-gradient(135deg, #f0fdf4 0%, #ecfdf5 100%); border: 1px solid #bbf7d0; border-radius: var(--radius-lg); padding: 20px;">
            <div style="display: flex; align-items: center; gap: 14px;">
                <div class="user-avatar" style="background: var(--success);"><i class="fa-solid fa-user-check"></i></div>
                <div>
                    <span style="font-size: 0.75rem; color: var(--success); font-weight: 700; text-transform: uppercase;">Teknisi yang Bertugas</span>
                    <h4 id="track_tech_name" style="font-size: 1.1rem; margin: 2px 0;">-</h4>
                    <span id="track_tech_phone" style="font-size: 0.85rem; color: var(--text-muted);">-</span>
                </div>
            </div>

            <a id="track_tech_wa_btn" href="#" target="_blank" class="btn btn-success btn-sm">
                <i class="fa-brands fa-whatsapp"></i> Hubungi WhatsApp
            </a>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
