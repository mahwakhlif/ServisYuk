<?php
// ==========================================================
// Core: TelegramService — Notifikasi via Telegram Bot API
// Bot: @servisyuk_bot
// ==========================================================

class TelegramService {
    /**
     * Token Bot Telegram @servisyuk_bot
     */
    private const BOT_TOKEN = '8850896979:AAFSrLR2zJzGoynQ2h3nmmpnkQmUZ0kuXOo';

    /**
     * Default fallback chat_id untuk testing
     * (Digunakan jika customer belum mengisi telegram_chat_id di profil)
     */
    private const FALLBACK_CHAT_ID = '1988757332';

    /**
     * Timeout cURL (detik) agar website tidak hang jika API Telegram lambat/down
     */
    private const TIMEOUT_SECONDS = 10;
    private const CONNECT_TIMEOUT = 5;

    /**
     * Kirim pesan teks ke Telegram via Bot API (cURL)
     *
     * @param string      $chatId   Chat ID Telegram penerima
     * @param string      $message  Isi pesan (mendukung HTML tags)
     * @param string|null $botToken Opsional, override bot token
     * @return array ['success' => bool, 'response' => mixed, 'error' => string|null]
     */
    public static function sendTelegramNotification(string $chatId, string $message, ?string $botToken = null): array {
        $token = $botToken ?: self::BOT_TOKEN;
        $url = "https://api.telegram.org/bot{$token}/sendMessage";

        $postData = [
            'chat_id'    => $chatId,
            'text'       => $message,
            'parse_mode' => 'HTML',
            'disable_web_page_preview' => true
        ];

        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL            => $url,
            CURLOPT_POST           => true,
            CURLOPT_POSTFIELDS     => $postData,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT        => self::TIMEOUT_SECONDS,
            CURLOPT_CONNECTTIMEOUT => self::CONNECT_TIMEOUT,
            CURLOPT_SSL_VERIFYPEER => true,
            CURLOPT_HTTPHEADER     => ['Content-Type: multipart/form-data']
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError = curl_error($ch);
        $curlErrno = curl_errno($ch);
        curl_close($ch);

        // Error handling
        if ($curlErrno !== 0) {
            $errorMsg = "cURL Error [{$curlErrno}]: {$curlError}";
            error_log("[TelegramService] {$errorMsg}");
            return [
                'success'  => false,
                'response' => null,
                'error'    => $errorMsg,
                'http_code' => 0
            ];
        }

        $decoded = json_decode($response, true);

        if ($httpCode !== 200 || empty($decoded['ok'])) {
            $errorDesc = $decoded['description'] ?? 'Unknown Telegram API error';
            $errorMsg = "Telegram API Error [HTTP {$httpCode}]: {$errorDesc}";
            error_log("[TelegramService] {$errorMsg}");
            return [
                'success'   => false,
                'response'  => $decoded,
                'error'     => $errorMsg,
                'http_code' => $httpCode
            ];
        }

        return [
            'success'   => true,
            'response'  => $decoded,
            'error'     => null,
            'http_code' => $httpCode
        ];
    }

    /**
     * Ambil chat_id Telegram customer dari database.
     * Jika kosong/null, gunakan fallback chat_id untuk testing.
     *
     * @param int $customerId ID user (customer) di tabel users
     * @return string Chat ID Telegram
     */
    public static function getCustomerChatId(int $customerId): string {
        try {
            $db = Database::getInstance();
            $user = $db->fetch(
                "SELECT telegram_chat_id FROM users WHERE id = ? AND role = 'customer' LIMIT 1",
                [$customerId]
            );

            if ($user && !empty($user['telegram_chat_id'])) {
                return $user['telegram_chat_id'];
            }
        } catch (Exception $e) {
            error_log("[TelegramService] DB Error getting chat_id: " . $e->getMessage());
        }

        // Fallback untuk testing
        return self::FALLBACK_CHAT_ID;
    }

    /**
     * Kirim notifikasi "Teknisi Ditugaskan" ke customer.
     *
     * @param int    $customerId  ID customer
     * @param string $kodeTiket   Kode tiket servis (misal: SRV-20261023-XXXX)
     * @param string $namaTeknisi Nama teknisi yang ditugaskan
     * @param string $statusBaru  Status baru (default: "Teknisi Ditugaskan")
     * @param string|null $jadwal Jadwal pengerjaan (opsional)
     * @return array Hasil pengiriman
     */
    public static function notifyTechnicianAssigned(
        int    $customerId,
        string $kodeTiket,
        string $namaTeknisi,
        string $statusBaru = 'Teknisi Ditugaskan',
        ?string $jadwal = null
    ): array {
        $chatId = self::getCustomerChatId($customerId);

        // Susun pesan informatif dengan HTML formatting
        $message  = "🔔 <b>Notifikasi ServisYuk</b>\n";
        $message .= "━━━━━━━━━━━━━━━━━━━━\n\n";
        $message .= "📋 <b>Kode Tiket:</b> <code>{$kodeTiket}</code>\n";
        $message .= "🔧 <b>Status Terbaru:</b> {$statusBaru}\n";
        $message .= "👨‍🔧 <b>Teknisi Ditugaskan:</b> {$namaTeknisi}\n";

        if ($jadwal) {
            $message .= "📅 <b>Jadwal:</b> {$jadwal}\n";
        }

        $message .= "\n✅ Teknisi profesional kami telah ditugaskan untuk menangani permintaan servis Anda. ";
        $message .= "Silakan tunggu konfirmasi kedatangan teknisi.\n\n";
        $message .= "📞 Jika ada pertanyaan, hubungi CS ServisYuk: <b>0812-8888-9999</b>\n\n";
        $message .= "━━━━━━━━━━━━━━━━━━━━\n";
        $message .= "🤖 <i>Pesan otomatis dari @servisyuk_bot</i>";

        $result = self::sendTelegramNotification($chatId, $message);

        // Log hasil pengiriman
        $logStatus = $result['success'] ? 'BERHASIL' : 'GAGAL';
        error_log("[TelegramService] Notif '{$statusBaru}' ke chat_id={$chatId} tiket={$kodeTiket}: {$logStatus}");

        return $result;
    }

    /**
     * Kirim notifikasi update status generik ke customer.
     *
     * @param int    $customerId ID customer
     * @param string $kodeTiket  Kode tiket servis
     * @param string $statusBaru Status baru
     * @param string $pesan      Pesan tambahan (opsional)
     * @return array Hasil pengiriman
     */
    public static function notifyStatusUpdate(
        int    $customerId,
        string $kodeTiket,
        string $statusBaru,
        string $pesan = ''
    ): array {
        $chatId = self::getCustomerChatId($customerId);

        // Emoji per status
        $statusEmojis = [
            'Menunggu'            => '⏳',
            'Dikonfirmasi'        => '✅',
            'Teknisi Ditugaskan'  => '👨‍🔧',
            'Dalam Perjalanan'    => '🚗',
            'Pemeriksaan'         => '🔍',
            'Dalam Perbaikan'     => '🔧',
            'Selesai'             => '🎉',
            'Dibatalkan'          => '❌'
        ];
        $emoji = $statusEmojis[$statusBaru] ?? '📌';

        $message  = "🔔 <b>Update Status ServisYuk</b>\n";
        $message .= "━━━━━━━━━━━━━━━━━━━━\n\n";
        $message .= "📋 <b>Kode Tiket:</b> <code>{$kodeTiket}</code>\n";
        $message .= "{$emoji} <b>Status Terbaru:</b> {$statusBaru}\n";

        if (!empty($pesan)) {
            $message .= "\n💬 {$pesan}\n";
        }

        $message .= "\n📞 CS ServisYuk: <b>0812-8888-9999</b>\n\n";
        $message .= "━━━━━━━━━━━━━━━━━━━━\n";
        $message .= "🤖 <i>Pesan otomatis dari @servisyuk_bot</i>";

        return self::sendTelegramNotification($chatId, $message);
    }
}
