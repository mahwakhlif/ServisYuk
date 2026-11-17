<?php
// ==========================================================
// Core: OtpService — Manajemen Siklus Hidup OTP WhatsApp
// ServisYuk Platform
// ==========================================================

require_once __DIR__ . '/FonnteService.php';

class OtpService {
    public const DEFAULT_EXPIRY_MINUTES = 5;
    public const MAX_ATTEMPTS = 3;
    public const RESEND_COOLDOWN_SECONDS = 60;

    /**
     * Generate dan kirim kode OTP via WhatsApp
     *
     * @param string $phone          Nomor WhatsApp tujuan
     * @param string $action         Tujuan OTP (e.g. 'register', 'login', 'verification')
     * @param int    $expiryMinutes  Masa berlaku dalam menit
     * @return array ['success' => bool, 'message' => string, 'cooldown' => int]
     */
    public static function requestOtp(string $phone, string $action = 'verification', int $expiryMinutes = self::DEFAULT_EXPIRY_MINUTES): array {
        if (session_status() === PHP_SESSION_NONE) {
            @session_start();
        }

        $formattedPhone = FonnteService::formatPhoneNumber($phone);
        if (!$formattedPhone) {
            return [
                'success'  => false,
                'message'  => 'Format nomor WhatsApp tidak valid.',
                'cooldown' => 0
            ];
        }

        $sessionKey = "otp_{$action}_{$formattedPhone}";
        $now = time();

        // Cek cooldown pengiriman ulang
        if (isset($_SESSION[$sessionKey])) {
            $lastSent = $_SESSION[$sessionKey]['created_at'] ?? 0;
            $elapsed = $now - $lastSent;
            if ($elapsed < self::RESEND_COOLDOWN_SECONDS) {
                $remaining = self::RESEND_COOLDOWN_SECONDS - $elapsed;
                return [
                    'success'  => false,
                    'message'  => "Mohon tunggu {$remaining} detik sebelum meminta kode OTP kembali.",
                    'cooldown' => $remaining
                ];
            }
        }

        // Generate 6 digit angka OTP acak
        $otpCode = (string)random_int(100000, 999999);
        $expiresAt = $now + ($expiryMinutes * 60);

        // Simpan hash OTP di session untuk keamanan (raw code tidak disimpan permanen)
        $_SESSION[$sessionKey] = [
            'hash'        => password_hash($otpCode, PASSWORD_DEFAULT),
            'expires_at'  => $expiresAt,
            'attempts'    => 0,
            'created_at'  => $now,
            'phone'       => $formattedPhone
        ];

        // Kirim via Fonnte WhatsApp
        $sendResult = FonnteService::sendOtp($formattedPhone, $otpCode, $expiryMinutes);

        if (!$sendResult['success']) {
            unset($_SESSION[$sessionKey]);
            return [
                'success'  => false,
                'message'  => 'Gagal mengirim kode OTP melalui WhatsApp: ' . ($sendResult['error'] ?? 'Gangguan API Fonnte'),
                'cooldown' => 0
            ];
        }

        return [
            'success'  => true,
            'message'  => 'Kode OTP berhasil dikirim ke nomor WhatsApp Anda.',
            'cooldown' => self::RESEND_COOLDOWN_SECONDS
        ];
    }

    /**
     * Validasi kode OTP yang dimasukkan pengguna
     *
     * @param string $phone      Nomor telepon
     * @param string $inputCode  Kode OTP masukan
     * @param string $action     Tujuan OTP
     * @return array ['success' => bool, 'message' => string, 'expired' => bool, 'attempts_left' => int]
     */
    public static function verifyOtp(string $phone, string $inputCode, string $action = 'verification'): array {
        if (session_status() === PHP_SESSION_NONE) {
            @session_start();
        }

        $formattedPhone = FonnteService::formatPhoneNumber($phone);
        if (!$formattedPhone) {
            return [
                'success'       => false,
                'message'       => 'Format nomor WhatsApp tidak valid.',
                'expired'       => false,
                'attempts_left' => 0
            ];
        }

        $sessionKey = "otp_{$action}_{$formattedPhone}";

        if (!isset($_SESSION[$sessionKey])) {
            return [
                'success'       => false,
                'message'       => 'Kode OTP belum diminta atau sudah kedaluwarsa. Silakan minta kode baru.',
                'expired'       => true,
                'attempts_left' => 0
            ];
        }

        $otpData = $_SESSION[$sessionKey];
        $now = time();

        // 1. Cek kedaluwarsa waktu
        if ($now > $otpData['expires_at']) {
            unset($_SESSION[$sessionKey]);
            return [
                'success'       => false,
                'message'       => 'Kode OTP telah kedaluwarsa. Silakan minta kode baru.',
                'expired'       => true,
                'attempts_left' => 0
            ];
        }

        // 2. Cek batas percobaan
        $attempts = ($otpData['attempts'] ?? 0) + 1;
        $_SESSION[$sessionKey]['attempts'] = $attempts;

        if ($attempts > self::MAX_ATTEMPTS) {
            unset($_SESSION[$sessionKey]);
            return [
                'success'       => false,
                'message'       => 'Batas percobaan memasukkan OTP telah terlampaui. Kode dibatalkan demi keamanan.',
                'expired'       => true,
                'attempts_left' => 0
            ];
        }

        // 3. Verifikasi kecocokan kode
        if (!password_verify($inputCode, $otpData['hash'])) {
            $attemptsLeft = self::MAX_ATTEMPTS - $attempts;
            return [
                'success'       => false,
                'message'       => "Kode OTP salah. Sisa kesempatan mencoba: {$attemptsLeft} kali.",
                'expired'       => false,
                'attempts_left' => $attemptsLeft
            ];
        }

        // 4. Jika berhasil diverifikasi, hapus sesi OTP (one-time use)
        unset($_SESSION[$sessionKey]);

        return [
            'success'       => true,
            'message'       => 'Kode OTP berhasil diverifikasi!',
            'expired'       => false,
            'attempts_left' => 0
        ];
    }
}
