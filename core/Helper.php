<?php
// ==========================================================
// Core: Helper Utilities
// ==========================================================

class Helper {
    public static function formatRupiah(float|int|string|null $amount): string {
        $val = floatval($amount ?? 0);
        return 'Rp ' . number_format($val, 0, ',', '.');
    }

    public static function formatDateIndo(string|null $dateStr, bool $withTime = false): string {
        if (empty($dateStr)) return '-';
        
        $months = [
            1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
            5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
            9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
        ];

        $days = [
            'Sunday' => 'Minggu', 'Monday' => 'Senin', 'Tuesday' => 'Selasa',
            'Wednesday' => 'Rabu', 'Thursday' => 'Kamis', 'Friday' => 'Jumat',
            'Saturday' => 'Sabtu'
        ];

        $time = strtotime($dateStr);
        if (!$time) return $dateStr;

        $dayName = $days[date('l', $time)] ?? '';
        $d = date('j', $time);
        $m = $months[(int)date('n', $time)] ?? '';
        $y = date('Y', $time);

        $result = "$dayName, $d $m $y";
        if ($withTime) {
            $result .= ' ' . date('H:i', $time) . ' WIB';
        }
        return $result;
    }

    public static function getStatusInfo(string $status): array {
        $statusKey = strtolower(str_replace([' ', '_', '-'], '', $status));
        switch ($statusKey) {
            case 'pending':
            case 'menunggu':
                return [
                    'label' => 'Menunggu',
                    'class' => 'badge-warning',
                    'icon' => 'fa-solid fa-clock-rotate-left',
                    'step' => 1,
                    'color' => '#f59e0b',
                    'description' => 'Permintaan telah diterima sistem dan sedang menunggu konfirmasi/penugasan teknisi.'
                ];
            case 'confirmed':
            case 'dikonfirmasi':
                return [
                    'label' => 'Dikonfirmasi',
                    'class' => 'badge-info',
                    'icon' => 'fa-solid fa-clipboard-check',
                    'step' => 1,
                    'color' => '#0284c7',
                    'description' => 'Permintaan telah diverifikasi oleh Admin dan siap ditugaskan ke teknisi.'
                ];
            case 'assigned':
            case 'teknisiditugaskan':
                return [
                    'label' => 'Teknisi Ditugaskan',
                    'class' => 'badge-info',
                    'icon' => 'fa-solid fa-user-check',
                    'step' => 2,
                    'color' => '#3b82f6',
                    'description' => 'Teknisi profesional telah ditugaskan dan bersiap menuju lokasi sesuai jadwal.'
                ];
            case 'ontheway':
            case 'dalamperjalanan':
                return [
                    'label' => 'Dalam Perjalanan',
                    'class' => 'badge-primary',
                    'icon' => 'fa-solid fa-motorcycle',
                    'step' => 3,
                    'color' => '#6366f1',
                    'description' => 'Teknisi sedang dalam perjalanan menuju alamat Anda.'
                ];
            case 'inspection':
            case 'pemeriksaan':
                return [
                    'label' => 'Pemeriksaan',
                    'class' => 'badge-indigo',
                    'icon' => 'fa-solid fa-magnifying-glass',
                    'step' => 4,
                    'color' => '#8b5cf6',
                    'description' => 'Teknisi telah tiba di lokasi dan sedang melakukan inspeksi/pemeriksaan fisik unit.'
                ];
            case 'inprogress':
            case 'dalamperbaikan':
                return [
                    'label' => 'Dalam Perbaikan',
                    'class' => 'badge-indigo',
                    'icon' => 'fa-solid fa-screwdriver-wrench',
                    'step' => 4,
                    'color' => '#8b5cf6',
                    'description' => 'Teknisi sedang melakukan tindakan perbaikan dan penanganan kerusakan.'
                ];
            case 'waitingparts':
            case 'menungguparts':
            case 'menunggusparepart':
                return [
                    'label' => 'Menunggu Sparepart',
                    'class' => 'badge-orange',
                    'icon' => 'fa-solid fa-boxes-packing',
                    'step' => 4,
                    'color' => '#ea580c',
                    'description' => 'Perbaikan membutuhkan suku cadang pengganti yang sedang disiapkan.'
                ];
            case 'completed':
            case 'selesai':
                return [
                    'label' => 'Selesai',
                    'class' => 'badge-success',
                    'icon' => 'fa-solid fa-circle-check',
                    'step' => 5,
                    'color' => '#10b981',
                    'description' => 'Pengerjaan perbaikan telah selesai dan uji fungsi peralatan berhasil.'
                ];
            case 'cancelled':
            case 'dibatalkan':
                return [
                    'label' => 'Dibatalkan',
                    'class' => 'badge-danger',
                    'icon' => 'fa-solid fa-circle-xmark',
                    'step' => 0,
                    'color' => '#ef4444',
                    'description' => 'Permintaan perbaikan telah dibatalkan.'
                ];
            default:
                return [
                    'label' => $status,
                    'class' => 'badge-secondary',
                    'icon' => 'fa-solid fa-circle-info',
                    'step' => 1,
                    'color' => '#6b7280',
                    'description' => 'Status layanan'
                ];
        }
    }

    public static function generateBookingCode(): string {
        $date = date('Ymd');
        $random = strtoupper(substr(bin2hex(random_bytes(3)), 0, 4));
        return "SRV-{$date}-{$random}";
    }

    public static function generateInvoiceNumber(): string {
        $date = date('Ymd');
        $random = strtoupper(substr(bin2hex(random_bytes(3)), 0, 4));
        return "INV/{$date}/{$random}";
    }

    public static function sanitize(?string $str): string {
        if ($str === null) return '';
        return htmlspecialchars(trim($str), ENT_QUOTES, 'UTF-8');
    }

    public static function csrfToken(): string {
        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['csrf_token'];
    }

    public static function csrfField(): string {
        $token = self::csrfToken();
        return '<input type="hidden" name="csrf_token" value="' . $token . '">';
    }

    public static function verifyCsrf(?string $token): bool {
        if (empty($_SESSION['csrf_token']) || empty($token)) {
            return false;
        }
        return hash_equals($_SESSION['csrf_token'], $token);
    }

    public static function validateCsrf(?string $token = null): bool {
        $token = $token ?? ($_POST['csrf_token'] ?? ($_SERVER['HTTP_X_CSRF_TOKEN'] ?? ''));
        return self::verifyCsrf($token);
    }

    public static function redirect(string $url): void {
        header('Location: ' . $url);
        exit;
    }

    public static function setFlash(string $type, string $message): void {
        $_SESSION['flash'] = [
            'type' => $type, // success, error, warning, info
            'message' => $message
        ];
    }

    public static function getFlash(): ?array {
        if (isset($_SESSION['flash'])) {
            $flash = $_SESSION['flash'];
            unset($_SESSION['flash']);
            return $flash;
        }
        return null;
    }

    public static function jsonResponse(array $data, int $statusCode = 200): void {
        while (ob_get_level() > 0) {
            ob_end_clean();
        }
        http_response_code($statusCode);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        exit;
    }

    public static function uploadFile(array $file, string $targetSubDir = 'photos'): array {
        if (!isset($file['error']) || is_array($file['error'])) {
            return ['success' => false, 'message' => 'Parameter file tidak valid.'];
        }

        if ($file['error'] !== UPLOAD_ERR_OK) {
            return ['success' => false, 'message' => 'Gagal mengupload file: Kode error ' . $file['error']];
        }

        if ($file['size'] > 5 * 1024 * 1024) { // Max 5MB
            return ['success' => false, 'message' => 'Ukuran file melebihi batas maksimal (5MB).'];
        }

        $finfo = new finfo(FILEINFO_MIME_TYPE);
        $mime = $finfo->file($file['tmp_name']);
        $allowedMimes = [
            'image/jpeg' => 'jpg',
            'image/png' => 'png',
            'image/webp' => 'webp'
        ];

        if (!array_key_exists($mime, $allowedMimes)) {
            return ['success' => false, 'message' => 'Format file tidak didukung. Harap upload format JPG, PNG, atau WEBP.'];
        }

        $ext = $allowedMimes[$mime];
        $uploadDir = UPLOAD_DIR . '/' . trim($targetSubDir, '/');
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }

        $filename = sprintf('%s_%s.%s', uniqid('up_', true), bin2hex(random_bytes(4)), $ext);
        $targetPath = $uploadDir . '/' . $filename;

        if (!move_uploaded_file($file['tmp_name'], $targetPath)) {
            return ['success' => false, 'message' => 'Gagal menyimpan file ke direktori upload.'];
        }

        return [
            'success' => true,
            'filename' => $filename,
            'relative_path' => $targetSubDir . '/' . $filename,
            'url' => UPLOAD_URL . '/' . $targetSubDir . '/' . $filename
        ];
    }
}
