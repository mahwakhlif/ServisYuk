<?php
// ==========================================================
// Controller: AuthController
// ==========================================================

require_once __DIR__ . '/../core/Auth.php';
require_once __DIR__ . '/../core/Helper.php';
require_once __DIR__ . '/../core/Database.php';
require_once __DIR__ . '/../core/OtpService.php';
require_once __DIR__ . '/../core/FonnteService.php';

class AuthController {
    public function login(): void {
        if (Auth::check()) {
            header('Location: ' . Auth::getRedirectPathByRole(Auth::role()));
            exit;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $email = trim($_POST['email'] ?? '');
            $password = trim($_POST['password'] ?? '');

            if (empty($email) || empty($password)) {
                Helper::setFlash('error', 'Silakan isi alamat email dan kata sandi.');
                require __DIR__ . '/../views/auth/login.php';
                return;
            }

            $result = Auth::attempt($email, $password);
            if ($result['success']) {
                Helper::setFlash('success', 'Selamat datang kembali, ' . htmlspecialchars($result['user']['name']) . '!');
                header('Location: ' . $result['redirect']);
                exit;
            } else {
                Helper::setFlash('error', $result['message']);
            }
        }

        require __DIR__ . '/../views/auth/login.php';
    }

    public function register(): void {
        if (Auth::check()) {
            header('Location: ' . Auth::getRedirectPathByRole(Auth::role()));
            exit;
        }

        // Jika user memilih untuk mereset proses pendaftaran yang tertunda
        if (isset($_GET['action']) && $_GET['action'] === 'reset') {
            unset($_SESSION['pending_registration']);
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $name = trim($_POST['name'] ?? '');
            $email = trim($_POST['email'] ?? '');
            $password = trim($_POST['password'] ?? '');
            $passwordConfirm = trim($_POST['password_confirmation'] ?? '');
            $phone = trim($_POST['phone'] ?? '');
            $address = trim($_POST['address'] ?? '');
            $role = trim($_POST['role'] ?? 'customer');

            // Validate role is one of the allowed values
            if (!in_array($role, ['admin', 'technician', 'customer'])) {
                $role = 'customer';
            }

            if (empty($name) || empty($email) || empty($password) || empty($phone)) {
                Helper::setFlash('error', 'Semua kolom bertanda bintang (*) termasuk nomor WhatsApp wajib diisi.');
                require __DIR__ . '/../views/auth/register.php';
                return;
            }

            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                Helper::setFlash('error', 'Format alamat email tidak valid.');
                require __DIR__ . '/../views/auth/register.php';
                return;
            }

            // Validasi format nomor WhatsApp
            $formattedPhone = FonnteService::formatPhoneNumber($phone);
            if (!$formattedPhone) {
                Helper::setFlash('error', 'Format nomor WhatsApp tidak valid. Pastikan nomor diawali 08 atau 628 dan terdiri dari 10-15 digit angka.');
                require __DIR__ . '/../views/auth/register.php';
                return;
            }

            if (strlen($password) < 6) {
                Helper::setFlash('error', 'Kata sandi minimal 6 karakter.');
                require __DIR__ . '/../views/auth/register.php';
                return;
            }

            if ($password !== $passwordConfirm) {
                Helper::setFlash('error', 'Konfirmasi kata sandi tidak cocok.');
                require __DIR__ . '/../views/auth/register.php';
                return;
            }

            // Cek apakah email sudah terdaftar di database
            $db = Database::getInstance();
            $existingEmail = $db->fetch("SELECT id FROM users WHERE email = ? LIMIT 1", [$email]);
            if ($existingEmail) {
                Helper::setFlash('error', 'Alamat email ini sudah terdaftar. Silakan gunakan email lain atau masuk ke akun Anda.');
                require __DIR__ . '/../views/auth/register.php';
                return;
            }

            // Cek apakah nomor telepon sudah terdaftar di database
            $existingPhone = $db->fetch(
                "SELECT id FROM users WHERE phone = ? OR phone = ? OR phone = ? LIMIT 1",
                [$phone, $formattedPhone, '0' . substr($formattedPhone, 2)]
            );
            if ($existingPhone) {
                Helper::setFlash('error', 'Nomor WhatsApp ini sudah terdaftar pada akun lain. Silakan gunakan nomor lain.');
                require __DIR__ . '/../views/auth/register.php';
                return;
            }

            // Validate technician-specific fields
            $specialization = trim($_POST['specialization'] ?? '');
            $experience = trim($_POST['experience'] ?? '3 Tahun');
            if ($role === 'technician' && empty($specialization)) {
                Helper::setFlash('error', 'Spesialisasi wajib dipilih untuk pendaftaran sebagai Teknisi.');
                require __DIR__ . '/../views/auth/register.php';
                return;
            }

            // Simpan data pendaftaran sementara di sesi (belum disimpan ke DB dan belum login!)
            $_SESSION['pending_registration'] = [
                'name'           => $name,
                'email'          => $email,
                'password'       => $password,
                'phone'          => $formattedPhone,
                'raw_phone'      => $phone,
                'address'        => $address,
                'role'           => $role,
                'specialization' => $specialization,
                'experience'     => $experience,
                'created_at'     => time()
            ];

            // Kirim kode OTP via Fonnte WhatsApp
            $otpResult = OtpService::requestOtp($formattedPhone, 'registration', 5);

            if (!$otpResult['success']) {
                // Jika API Fonnte gagal mengirim pesan, batalkan sesi pending agar konsisten
                unset($_SESSION['pending_registration']);
                Helper::setFlash('error', $otpResult['message']);
                require __DIR__ . '/../views/auth/register.php';
                return;
            }

            Helper::setFlash('success', "Kode OTP 6-digit telah dikirimkan ke WhatsApp Anda (" . FonnteService::maskPhoneNumber($formattedPhone) . "). Silakan masukkan kode untuk menyelesaikan pendaftaran.");
            header('Location: ' . BASE_URL . '/index.php?page=verify_otp');
            exit;
        }

        require __DIR__ . '/../views/auth/register.php';
    }

    public function verifyOtp(): void {
        if (Auth::check()) {
            header('Location: ' . Auth::getRedirectPathByRole(Auth::role()));
            exit;
        }

        $pending = $_SESSION['pending_registration'] ?? null;
        if (!$pending) {
            Helper::setFlash('warning', 'Tidak ada proses pendaftaran yang tertunda atau sesi telah berakhir. Silakan isi form pendaftaran.');
            header('Location: ' . BASE_URL . '/index.php?page=register');
            exit;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $rawOtp = trim($_POST['otp'] ?? '');
            $otpCode = preg_replace('/[^0-9]/', '', $rawOtp);

            if (strlen($otpCode) !== 6) {
                Helper::setFlash('error', 'Silakan masukkan 6 digit angka kode OTP verifikasi.');
                require __DIR__ . '/../views/auth/verify_otp.php';
                return;
            }

            // Validasi OTP menggunakan OtpService
            $verifyResult = OtpService::verifyOtp($pending['phone'], $otpCode, 'registration');

            if (!$verifyResult['success']) {
                Helper::setFlash('error', $verifyResult['message']);

                // Jika OTP telah kedaluwarsa atau batas percobaan habis, batalkan sesi pendaftaran
                if ($verifyResult['expired']) {
                    unset($_SESSION['pending_registration']);
                    header('Location: ' . BASE_URL . '/index.php?page=register');
                    exit;
                }

                require __DIR__ . '/../views/auth/verify_otp.php';
                return;
            }

            // ========================================================
            // OTP VALID! Sekarang simpan akun ke database & beri akses login
            // ========================================================
            $result = Auth::register([
                'name'     => $pending['name'],
                'email'    => $pending['email'],
                'password' => $pending['password'],
                'phone'    => $pending['phone'],
                'address'  => $pending['address'],
                'role'     => $pending['role']
            ]);

            if ($result['success']) {
                // Jika teknisi, buat data profil di tabel technicians
                if ($pending['role'] === 'technician' && !empty($result['user_id'])) {
                    try {
                        $db = Database::getInstance();
                        $db->insert('technicians', [
                            'user_id'        => $result['user_id'],
                            'specialization' => $pending['specialization'],
                            'experience'     => $pending['experience'],
                            'availability'   => 'available'
                        ]);
                    } catch (Exception $e) {
                        error_log('Failed to create technician profile: ' . $e->getMessage());
                    }
                }

                // Bersihkan sesi pendaftaran sementara
                unset($_SESSION['pending_registration']);

                $roleLabel = $pending['role'] === 'admin' ? 'Administrator' : ($pending['role'] === 'technician' ? 'Teknisi' : 'Pelanggan');
                Helper::setFlash('success', "Nomor WhatsApp Anda berhasil diverifikasi! Selamat datang di ServisYuk, {$pending['name']}.");
                header('Location: ' . $result['redirect']);
                exit;
            } else {
                Helper::setFlash('error', $result['message']);
                header('Location: ' . BASE_URL . '/index.php?page=register');
                exit;
            }
        }

        require __DIR__ . '/../views/auth/verify_otp.php';
    }

    public function resendOtp(): void {
        if (Auth::check()) {
            header('Location: ' . Auth::getRedirectPathByRole(Auth::role()));
            exit;
        }

        $pending = $_SESSION['pending_registration'] ?? null;
        if (!$pending) {
            Helper::setFlash('warning', 'Sesi pendaftaran telah berakhir. Silakan isi form kembali.');
            header('Location: ' . BASE_URL . '/index.php?page=register');
            exit;
        }

        $otpResult = OtpService::requestOtp($pending['phone'], 'registration', 5);
        if ($otpResult['success']) {
            Helper::setFlash('success', 'Kode OTP baru telah berhasil dikirimkan ke WhatsApp Anda (' . FonnteService::maskPhoneNumber($pending['phone']) . ').');
        } else {
            Helper::setFlash('warning', $otpResult['message']);
        }

        header('Location: ' . BASE_URL . '/index.php?page=verify_otp');
        exit;
    }

    public function logout(): void {
        Auth::logout();
        Helper::setFlash('info', 'Anda telah berhasil keluar dari sistem.');
        header('Location: ' . BASE_URL . '/index.php?page=login');
        exit;
    }
}
