<?php
// ==========================================================
// Core: FonnteService — Integrasi Notifikasi WhatsApp Fonnte
// ServisYuk Platform
// ==========================================================

require_once __DIR__ . '/Env.php';
require_once __DIR__ . '/Database.php';

class FonnteService {
    /**
     * Endpoint API Fonnte untuk mengirim pesan
     */
    private const API_ENDPOINT = 'https://api.fonnte.com/send';

    /**
     * Default timeout (detik)
     */
    private const DEFAULT_TIMEOUT = 15;
    private const DEFAULT_CONNECT_TIMEOUT = 5;

    /**
     * Format nomor telepon ke standar internasional Indonesia (628...)
     * Mendukung format masukan:
     * - 081234567890 -> 6281234567890
     * - +6281234567890 -> 6281234567890
     * - 81234567890 -> 6281234567890
     * - 0812-3456-7890 (dengan spasi/tanda hubung) -> 6281234567890
     *
     * @param string|null $phone Nomor telepon input
     * @return string|null Nomor tervalidasi atau null jika tidak valid
     */
    public static function formatPhoneNumber(?string $phone): ?string {
        if ($phone === null) {
            return null;
        }

        // Hapus karakter non-angka
        $cleaned = preg_replace('/[^0-9]/', '', trim($phone));

        if (empty($cleaned)) {
            return null;
        }

        // Jika diawali '08', ubah awalan '0' menjadi '62'
        if (str_starts_with($cleaned, '08')) {
            $cleaned = '62' . substr($cleaned, 1);
        }
        // Jika diawali '8', tambahkan '62'
        elseif (str_starts_with($cleaned, '8')) {
            $cleaned = '62' . $cleaned;
        }
        // Jika diawali '628', biarkan apa adanya

        // Validasi panjang nomor ponsel Indonesia (umumnya 10-15 digit)
        if (str_starts_with($cleaned, '628') && strlen($cleaned) >= 10 && strlen($cleaned) <= 16) {
            return $cleaned;
        }

        return null;
    }

    /**
     * Masking nomor telepon untuk keperluan logging yang aman (misal: 62812****789)
     */
    public static function maskPhoneNumber(string $phone): string {
        $len = strlen($phone);
        if ($len <= 6) {
            return '***';
        }
        return substr($phone, 0, 5) . str_repeat('*', max(3, $len - 8)) . substr($phone, -3);
    }

    /**
     * Ambil token API Fonnte dari Environment
     */
    private static function getToken(): string {
        return trim((string)Env::get('FONNTE_TOKEN', ''));
    }

    /**
     * Memeriksa validitas token dan status koneksi perangkat ke API Fonnte tanpa mengirim pesan
     * Endpoint: POST https://api.fonnte.com/device
     *
     * @return array ['success' => bool, 'connected' => bool, 'device_name' => string|null, 'device_phone' => string|null, 'quota' => mixed, 'message' => string]
     */
    public static function checkDeviceStatus(): array {
        $token = self::getToken();
        if (empty($token) || $token === 'your_fonnte_token_here') {
            return [
                'success'       => false,
                'connected'     => false,
                'device_name'   => null,
                'device_phone'  => null,
                'device_status' => 'unconfigured',
                'package'       => '-',
                'quota'         => '-',
                'message'       => 'FONNTE_TOKEN belum diisi di file .env'
            ];
        }

        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL            => 'https://api.fonnte.com/device',
            CURLOPT_POST           => true,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT        => 10,
            CURLOPT_CONNECTTIMEOUT => 5,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_HTTPHEADER     => [
                "Authorization: {$token}"
            ]
        ]);

        $rawResponse = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlErrno = curl_errno($ch);
        $curlError = curl_error($ch);
        curl_close($ch);

        if ($curlErrno !== 0) {
            return [
                'success'       => false,
                'connected'     => false,
                'device_name'   => null,
                'device_phone'  => null,
                'device_status' => 'curl_error',
                'package'       => '-',
                'quota'         => '-',
                'message'       => "Gagal menghubungi server Fonnte: cURL Error [{$curlErrno}] {$curlError}"
            ];
        }

        $decoded = json_decode($rawResponse, true);

        if ($httpCode !== 200 || empty($decoded['status'])) {
            $reason = $decoded['reason'] ?? ($decoded['message'] ?? "HTTP Status {$httpCode}");
            return [
                'success'       => false,
                'connected'     => false,
                'device_name'   => null,
                'device_phone'  => null,
                'device_status' => 'rejected',
                'package'       => '-',
                'quota'         => '-',
                'message'       => "Token tidak valid atau ditolak oleh Fonnte: {$reason}"
            ];
        }

        $deviceStatus = strtolower((string)($decoded['device_status'] ?? ''));
        $isConnected = ($deviceStatus === 'connect');
        $deviceName = $decoded['name'] ?? 'WhatsApp Device';
        $devicePhone = !empty($decoded['device']) ? self::maskPhoneNumber((string)$decoded['device']) : '-';
        $quota = $decoded['quota'] ?? '-';
        $pkg = $decoded['package'] ?? '-';

        return [
            'success'       => true,
            'connected'     => $isConnected,
            'device_name'   => $deviceName,
            'device_phone'  => $devicePhone,
            'device_status' => $decoded['device_status'] ?? 'unknown',
            'package'       => $pkg,
            'quota'         => $quota,
            'message'       => $isConnected 
                ? "Token valid dan perangkat WhatsApp terhubung ({$devicePhone})." 
                : "Token valid, namun status perangkat: {$deviceStatus} (belum terhubung)."
        ];
    }

    /**
     * Tulis log aktivitas WhatsApp ke file logs/whatsapp.log
     */
    private static function log(string $message): void {
        $logDir = dirname(__DIR__) . '/logs';
        if (!is_dir($logDir)) {
            @mkdir($logDir, 0755, true);
        }
        $logFile = $logDir . '/whatsapp.log';
        $timestamp = date('Y-m-d H:i:s');
        @file_put_contents($logFile, "[{$timestamp}] {$message}\n", FILE_APPEND | LOCK_EX);
    }

    /**
     * Kirim pesan teks WhatsApp melalui Fonnte API
     *
     * @param string $target  Nomor telepon tujuan (akan diformat otomatis)
     * @param string $message Isi pesan teks (mendukung format WhatsApp: *tebal*, _miring_, ```kode```)
     * @return array ['success' => bool, 'response' => mixed, 'error' => string|null, 'http_code' => int]
     */
    public static function sendWhatsApp(string $target, string $message): array {
        $formattedPhone = self::formatPhoneNumber($target);
        if (!$formattedPhone) {
            $maskedInput = self::maskPhoneNumber($target);
            self::log("GAGAL: Format nomor telepon tidak valid ({$maskedInput})");
            return [
                'success'   => false,
                'response'  => null,
                'error'     => 'Format nomor telepon tujuan tidak valid.',
                'http_code' => 0
            ];
        }

        $token = self::getToken();
        if (empty($token) || $token === 'your_fonnte_token_here') {
            self::log("PERINGATAN: FONNTE_TOKEN belum disetel di file .env. Notifikasi WhatsApp ke " . self::maskPhoneNumber($formattedPhone) . " dilewati.");
            return [
                'success'   => false,
                'response'  => null,
                'error'     => 'FONNTE_TOKEN belum dikonfigurasi di .env',
                'http_code' => 0
            ];
        }

        $timeout = (int)Env::get('FONNTE_TIMEOUT', self::DEFAULT_TIMEOUT);
        $countryCode = (string)Env::get('FONNTE_COUNTRY_CODE', '62');

        $postData = [
            'target'      => $formattedPhone,
            'message'     => $message,
            'countryCode' => $countryCode
        ];

        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL            => self::API_ENDPOINT,
            CURLOPT_POST           => true,
            CURLOPT_POSTFIELDS     => $postData,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT        => $timeout > 0 ? $timeout : self::DEFAULT_TIMEOUT,
            CURLOPT_CONNECTTIMEOUT => self::DEFAULT_CONNECT_TIMEOUT,
            CURLOPT_SSL_VERIFYPEER => false, // Aman untuk lingkungan XAMPP lokal
            CURLOPT_HTTPHEADER     => [
                "Authorization: {$token}"
            ]
        ]);

        $rawResponse = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError = curl_error($ch);
        $curlErrno = curl_errno($ch);
        curl_close($ch);

        $maskedPhone = self::maskPhoneNumber($formattedPhone);

        // Penanganan kendala cURL / jaringan
        if ($curlErrno !== 0) {
            $errorMsg = "cURL Error [{$curlErrno}]: {$curlError}";
            self::log("ERROR ke {$maskedPhone}: {$errorMsg}");
            return [
                'success'   => false,
                'response'  => null,
                'error'     => $errorMsg,
                'http_code' => 0
            ];
        }

        $decoded = json_decode($rawResponse, true);

        // Fonnte mengembalikan response berformat JSON: {"status": true/false, ...}
        if ($httpCode !== 200 || empty($decoded['status'])) {
            $reason = $decoded['reason'] ?? ($decoded['message'] ?? 'Unknown Fonnte API error');
            $errorMsg = "Fonnte API Error [HTTP {$httpCode}]: {$reason}";
            self::log("GAGAL ke {$maskedPhone}: {$errorMsg}");
            return [
                'success'   => false,
                'response'  => $decoded,
                'error'     => $errorMsg,
                'http_code' => $httpCode
            ];
        }

        self::log("BERHASIL kirim WhatsApp ke {$maskedPhone} [HTTP {$httpCode}]");
        return [
            'success'   => true,
            'response'  => $decoded,
            'error'     => null,
            'http_code' => $httpCode
        ];
    }

    /**
     * Kirim kode OTP via WhatsApp
     *
     * @param string $targetPhone  Nomor telepon penerima
     * @param string $otpCode      Kode angka/karakter OTP
     * @param int    $expiryMinutes Masa berlaku dalam menit (default 5 menit)
     * @return array
     */
    public static function sendOtp(string $targetPhone, string $otpCode, int $expiryMinutes = 5): array {
        $formattedPhone = self::formatPhoneNumber($targetPhone);
        if (!$formattedPhone) {
            return [
                'success' => false,
                'error'   => 'Nomor telepon tujuan tidak valid.'
            ];
        }

        $message  = "🔐 *KODE VERIFIKASI SERVISYUK*\n";
        $message .= "━━━━━━━━━━━━━━━━━━━━\n\n";
        $message .= "Kode OTP Anda: *`{$otpCode}`*\n\n";
        $message .= "⚠️ *PENTING:*\n";
        $message .= "• Kode ini berlaku selama *{$expiryMinutes} menit*.\n";
        $message .= "• JANGAN bagikan kode ini kepada siapa pun, termasuk pihak ServisYuk.\n\n";
        $message .= "━━━━━━━━━━━━━━━━━━━━\n";
        $message .= "_Pesan otomatis sistem ServisYuk_";

        // Kirim tanpa mencatat kode OTP ke log produksi
        return self::sendWhatsApp($formattedPhone, $message);
    }

    /**
     * Ambil data lengkap pemesanan untuk notifikasi status
     */
    public static function getTicketData(int $requestId): ?array {
        try {
            $db = Database::getInstance();
            return $db->fetch("
                SELECT sr.id, sr.request_code, sr.customer_id, sr.status, sr.schedule,
                       sr.appliance_name, sr.brand, sr.model, sr.complaint, sr.address,
                       u.name AS customer_name, u.phone AS customer_phone,
                       tu.name AS technician_name, tu.phone AS technician_phone
                FROM service_requests sr
                JOIN users u ON sr.customer_id = u.id
                LEFT JOIN technician_assignments ta ON sr.id = ta.request_id
                LEFT JOIN technicians t ON ta.technician_id = t.id
                LEFT JOIN users tu ON t.user_id = tu.id
                WHERE sr.id = ?
                ORDER BY ta.id DESC
                LIMIT 1
            ", [$requestId]);
        } catch (\Throwable $e) {
            self::log("Database Error getTicketData: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Kirim notifikasi WhatsApp pembaruan status layanan ke pelanggan
     *
     * Status yang didukung:
     * - Menunggu
     * - Dikonfirmasi
     * - Teknisi Ditugaskan
     * - Dalam Perjalanan
     * - Pemeriksaan
     * - Dalam Perbaikan
     * - Selesai
     * - Dibatalkan
     *
     * @param int         $requestId    ID tiket service_requests
     * @param string      $newStatus    Status terbaru
     * @param string|null $customNotes  Catatan tambahan khusus (opsional)
     * @return array
     */
    public static function notifyStatusUpdate(int $requestId, string $newStatus, ?string $customNotes = null): array {
        try {
            $ticket = self::getTicketData($requestId);
            if (!$ticket) {
                return ['success' => false, 'error' => 'Data tiket tidak ditemukan.'];
            }

            $customerPhone = $ticket['customer_phone'] ?? '';
            if (empty($customerPhone)) {
                self::log("Peringatan: Pelanggan tiket {$ticket['request_code']} tidak memiliki nomor telepon terdaftar.");
                return ['success' => false, 'error' => 'Nomor WhatsApp pelanggan tidak tersedia.'];
            }

            $customerName = !empty($ticket['customer_name']) ? $ticket['customer_name'] : 'Pelanggan Setia';
            $requestCode = $ticket['request_code'];
            $appliance = trim(($ticket['brand'] ? $ticket['brand'] . ' ' : '') . $ticket['appliance_name']);
            $techName = !empty($ticket['technician_name']) ? $ticket['technician_name'] : 'Tim Teknisi ServisYuk';
            $techPhone = !empty($ticket['technician_phone']) ? $ticket['technician_phone'] : null;

            $jadwalFormatted = null;
            if (!empty($ticket['schedule'])) {
                $jadwalFormatted = date('d M Y, H:i', strtotime($ticket['schedule'])) . ' WIB';
            }

            // Susun template pesan berdasarkan status
            $message = self::buildStatusMessage([
                'status'        => $newStatus,
                'customer_name' => $customerName,
                'request_code'  => $requestCode,
                'appliance'     => $appliance,
                'technician'    => $techName,
                'tech_phone'    => $techPhone,
                'schedule'      => $jadwalFormatted,
                'notes'         => $customNotes
            ]);

            return self::sendWhatsApp($customerPhone, $message);
        } catch (\Throwable $e) {
            self::log("Exception notifyStatusUpdate: " . $e->getMessage());
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Kirim notifikasi penugasan teknisi secara spesifik
     */
    public static function notifyTechnicianAssigned(
        int    $requestId,
        string $techName,
        ?string $techPhone = null,
        ?string $customNotes = null
    ): array {
        return self::notifyStatusUpdate($requestId, 'Teknisi Ditugaskan', $customNotes);
    }

    /**
     * Membangun format pesan WhatsApp sesuai status layanan
     */
    private static function buildStatusMessage(array $data): string {
        $status = $data['status'];
        $customer = $data['customer_name'];
        $code = $data['request_code'];
        $appliance = !empty($data['appliance']) ? $data['appliance'] : 'Peralatan Rumah Tangga';
        $tech = $data['technician'];
        $schedule = $data['schedule'];
        $notes = $data['notes'];

        $msg = "Halo *{$customer}*,\n\n";

        switch ($status) {
            case 'Menunggu':
                $msg .= "Permintaan servis Anda telah kami terima dan saat ini berstatus: *MENUNGGU VERIFIKASI* ⏳\n\n";
                $msg .= "📋 *Kode Tiket:* `{$code}`\n";
                $msg .= "🔧 *Peralatan:* {$appliance}\n";
                if ($schedule) {
                    $msg .= "📅 *Jadwal Diajukan:* {$schedule}\n";
                }
                $msg .= "\nAdmin ServisYuk akan segera memverifikasi pesanan Anda.";
                break;

            case 'Dikonfirmasi':
                $msg .= "Kabar baik! Permintaan servis Anda telah *DIKONFIRMASI* oleh Admin ServisYuk ✅\n\n";
                $msg .= "📋 *Kode Tiket:* `{$code}`\n";
                $msg .= "🔧 *Peralatan:* {$appliance}\n";
                if ($schedule) {
                    $msg .= "📅 *Jadwal Servis:* {$schedule}\n";
                }
                $msg .= "\nKami sedang mengatur teknisi terbaik untuk menangani perbaikan Anda.";
                break;

            case 'Teknisi Ditugaskan':
                $msg .= "Teknisi profesional telah *DITUGASKAN* untuk menangani peralatan Anda 👨‍🔧\n\n";
                $msg .= "📋 *Kode Tiket:* `{$code}`\n";
                $msg .= "🔧 *Peralatan:* {$appliance}\n";
                $msg .= "👨‍🔧 *Nama Teknisi:* {$tech}\n";
                if ($schedule) {
                    $msg .= "📅 *Jadwal Kedatangan:* {$schedule}\n";
                }
                $msg .= "\nTeknisi kami akan segera mengonfirmasi dan bersiap menuju lokasi Anda.";
                break;

            case 'Dalam Perjalanan':
                $msg .= "Teknisi ServisYuk saat ini *SEDANG DALAM PERJALANAN* menuju lokasi Anda 🚗💨\n\n";
                $msg .= "📋 *Kode Tiket:* `{$code}`\n";
                $msg .= "🔧 *Peralatan:* {$appliance}\n";
                $msg .= "👨‍🔧 *Teknisi:* {$tech}\n";
                $msg .= "\nMohon pastikan ada orang di rumah dan unit peralatan siap diperiksa.";
                break;

            case 'Pemeriksaan':
                $msg .= "Teknisi telah tiba di lokasi dan sedang melakukan *PEMERIKSAAN / DIAGNOSIS* unit 🔍\n\n";
                $msg .= "📋 *Kode Tiket:* `{$code}`\n";
                $msg .= "🔧 *Peralatan:* {$appliance}\n";
                $msg .= "👨‍🔧 *Teknisi:* {$tech}\n";
                $msg .= "\nHasil pemeriksaan komponen dan estimasi biaya perbaikan akan diinformasikan langsung kepada Anda.";
                break;

            case 'Dalam Perbaikan':
                $msg .= "Unit peralatan Anda saat ini sedang *DALAM PROSES PERBAIKAN* 🔧\n\n";
                $msg .= "📋 *Kode Tiket:* `{$code}`\n";
                $msg .= "🔧 *Peralatan:* {$appliance}\n";
                $msg .= "👨‍🔧 *Teknisi:* {$tech}\n";
                $msg .= "\nTeknisi kami bekerja dengan teliti sesuai standar operasional ServisYuk.";
                break;

            case 'Selesai':
                $msg .= "Perbaikan peralatan Anda telah *SELESAI DILAKUKAN* 🎉\n\n";
                $msg .= "📋 *Kode Tiket:* `{$code}`\n";
                $msg .= "🔧 *Peralatan:* {$appliance}\n";
                $msg .= "👨‍🔧 *Teknisi:* {$tech}\n";
                $msg .= "\nLaporan servis dan faktur pembayaran telah diterbitkan pada sistem. Terima kasih telah mempercayakan perbaikan Anda kepada *ServisYuk*!";
                break;

            case 'Dibatalkan':
                $msg .= "Permintaan servis Anda dengan kode tiket *`{$code}`* telah *DIBATALKAN* ❌\n\n";
                $msg .= "🔧 *Peralatan:* {$appliance}\n";
                if (!empty($notes)) {
                    $msg .= "📝 *Alasan / Catatan:* {$notes}\n";
                }
                $msg .= "\nJika Anda tidak merasa mengajukan pembatalan atau membutuhkan bantuan, silakan hubungi tim kami.";
                break;

            default:
                $msg .= "Update status terbaru untuk tiket servis Anda: *{$status}* 📌\n\n";
                $msg .= "📋 *Kode Tiket:* `{$code}`\n";
                $msg .= "🔧 *Peralatan:* {$appliance}\n";
                break;
        }

        if (!empty($notes) && $status !== 'Dibatalkan') {
            $msg .= "\n\n💬 *Catatan:* {$notes}";
        }

        $msg .= "\n\n━━━━━━━━━━━━━━━━━━━━\n";
        $msg .= "📞 Butuh bantuan? Hubungi CS ServisYuk: *0812-8888-9999*\n";
        $msg .= "_Pesan otomatis sistem ServisYuk via WhatsApp_";

        return $msg;
    }
}
