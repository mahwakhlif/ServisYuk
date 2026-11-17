<?php
$pageTitle = 'Verifikasi OTP WhatsApp - ServisYuk';
require_once __DIR__ . '/../layouts/header.php';
require_once __DIR__ . '/../layouts/navbar.php';
$flash = Helper::getFlash();

$pending = $_SESSION['pending_registration'] ?? null;
$maskedPhone = $pending ? FonnteService::maskPhoneNumber($pending['phone']) : '';
$rawPhone = $pending['raw_phone'] ?? ($pending['phone'] ?? '');
?>

<div class="container" style="padding: 60px 24px 80px; max-width: 520px;">
    <div class="card" style="box-shadow: var(--shadow-xl); border-radius: var(--radius-xl); text-align: center; padding: 36px 28px;">
        <!-- Icon WhatsApp -->
        <div style="width: 64px; height: 64px; margin: 0 auto 16px; border-radius: 50%; background: rgba(37,211,102,0.12); color: #25D366; display: flex; align-items: center; justify-content: center; font-size: 2rem;">
            <i class="fa-brands fa-whatsapp"></i>
        </div>

        <h1 style="font-size: 1.6rem; font-weight: 800; margin-bottom: 8px;">Verifikasi Nomor WhatsApp</h1>
        <p style="color: var(--text-muted); font-size: 0.9rem; line-height: 1.5; margin-bottom: 24px;">
            Masukkan 6 digit kode OTP yang telah dikirimkan ke WhatsApp Anda:<br>
            <strong style="color: var(--text-primary); font-size: 1.05rem;"><i class="fa-solid fa-phone" style="font-size:0.85rem; margin-right:4px;"></i> <?= htmlspecialchars($maskedPhone) ?></strong>
        </p>

        <?php if ($flash): ?>
            <div class="alert alert-<?= $flash['type'] ?>" style="text-align: left; margin-bottom: 20px;">
                <i class="fa-solid <?= $flash['type'] === 'success' ? 'fa-circle-check' : 'fa-circle-exclamation' ?>"></i>
                <?= htmlspecialchars($flash['message']) ?>
            </div>
        <?php endif; ?>

        <form action="<?= BASE_URL ?>/index.php?page=verify_otp" method="POST" id="otp-form">
            <?= Helper::csrfField() ?>

            <div class="form-group" style="margin-bottom: 20px;">
                <label class="form-label" style="font-size: 0.85rem; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px; color: var(--text-muted);">
                    Kode Verifikasi (OTP)
                </label>
                <input 
                    type="text" 
                    name="otp" 
                    id="otp-input" 
                    class="form-control" 
                    placeholder="______" 
                    maxlength="6" 
                    inputmode="numeric" 
                    pattern="[0-9]*" 
                    autocomplete="one-time-code" 
                    required 
                    autofocus 
                    style="text-align: center; font-size: 1.9rem; letter-spacing: 12px; font-weight: 800; height: 60px; font-family: monospace; border-radius: var(--radius-lg); border: 2px solid var(--border-color); color: var(--text-primary);"
                >
            </div>

            <!-- Timer Kedaluwarsa -->
            <div style="font-size: 0.85rem; color: var(--text-muted); margin-bottom: 24px;">
                <i class="fa-regular fa-clock" style="margin-right: 4px;"></i> Masa berlaku kode: 
                <span id="countdown" style="font-weight: 700; color: var(--primary);">05:00</span>
            </div>

            <button type="submit" class="btn btn-primary btn-block btn-lg" style="height: 48px; font-weight: 700;">
                <i class="fa-solid fa-check"></i> Verifikasi & Selesaikan Pendaftaran
            </button>
        </form>

        <!-- Kirim Ulang OTP -->
        <div style="margin-top: 28px; padding-top: 20px; border-top: 1px solid var(--border-color); font-size: 0.88rem; color: var(--text-muted);">
            Tidak menerima kode OTP? <br>
            <div style="margin-top: 8px;">
                <a href="<?= BASE_URL ?>/index.php?page=resend_otp" id="resend-link" class="btn btn-sm btn-outline" style="border-radius: var(--radius-full); padding: 6px 16px;">
                    <i class="fa-solid fa-rotate-right"></i> Kirim Ulang OTP
                </a>
                <span id="resend-timer" style="display: none; font-size: 0.85rem; color: var(--text-muted); margin-left: 6px;"></span>
            </div>
        </div>

        <div style="margin-top: 18px; font-size: 0.82rem;">
            <a href="<?= BASE_URL ?>/index.php?page=register&action=reset" style="color: var(--text-muted); text-decoration: underline;">
                <i class="fa-solid fa-arrow-left"></i> Ganti nomor atau ubah data pendaftaran
            </a>
        </div>
    </div>
</div>

<script>
(function() {
    // Format input hanya angka
    const otpInput = document.getElementById('otp-input');
    if (otpInput) {
        otpInput.addEventListener('input', function(e) {
            this.value = this.value.replace(/[^0-9]/g, '');
        });
    }

    // Hitung mundur 5 menit
    let totalSeconds = 300;
    const countdownEl = document.getElementById('countdown');

    const timer = setInterval(() => {
        totalSeconds--;
        if (totalSeconds <= 0) {
            clearInterval(timer);
            if (countdownEl) {
                countdownEl.innerText = "Kedaluwarsa";
                countdownEl.style.color = "var(--danger)";
            }
        } else {
            const minutes = Math.floor(totalSeconds / 60);
            const seconds = totalSeconds % 60;
            if (countdownEl) {
                countdownEl.innerText = 
                    (minutes < 10 ? '0' : '') + minutes + ':' + 
                    (seconds < 10 ? '0' : '') + seconds;
            }
        }
    }, 1000);
})();
</script>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
