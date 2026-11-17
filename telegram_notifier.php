<?php
/**
 * ============================================================================
 * ServisYuk - Telegram Notification Script
 * ============================================================================
 * Script otomatis mengirim notifikasi via Telegram bot @servisyuk_bot
 * ke customer saat status servis diupdate menjadi "Teknisi Ditugaskan".
 *
 * Berbasis XAMPP (PHP & MySQL/PDO)
 * ============================================================================
 */

// Konfigurasi Kredensial Bot Telegram
define('TELEGRAM_BOT_TOKEN', '8850896979:AAFSrLR2zJzGoynQ2h3nmmpnkQmUZ0kuXOo');
define('TELEGRAM_FALLBACK_CHAT_ID', '1988757332');

// Timeout cURL (detik) agar website tidak hang jika API Telegram lambat/down
define('TELEGRAM_TIMEOUT_SECONDS', 10);
define('TELEGRAM_CONNECT_TIMEOUT', 5);

/**
 * 1. Fungsi Mengirim Notifikasi Telegram via cURL
 *
 * @param string $chat_id   ID chat Telegram tujuan
 * @param string $message   Isi pesan notifikasi (mendukung tag HTML/Markdown)
 * @param string $bot_token Token bot Telegram
 * @return array            Status pengiriman, HTTP code, dan respons JSON
 */
function sendTelegramNotification($chat_id, $message, $bot_token) {
    $url = "https://api.telegram.org/bot{$bot_token}/sendMessage";

    $payload = [
        'chat_id'                  => $chat_id,
        'text'                     => $message,
        'parse_mode'               => 'HTML',
        'disable_web_page_preview' => true
    ];

    $ch = curl_init();
    curl_setopt_array($ch, [
        CURLOPT_URL            => $url,
        CURLOPT_POST           => true,
        CURLOPT_POSTFIELDS     => $payload,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT        => TELEGRAM_TIMEOUT_SECONDS,   // Timeout total 10 detik
        CURLOPT_CONNECTTIMEOUT => TELEGRAM_CONNECT_TIMEOUT,   // Timeout koneksi 5 detik
        CURLOPT_SSL_VERIFYPEER => true,
        CURLOPT_HTTPHEADER     => ['Content-Type: multipart/form-data']
    ]);

    $response  = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curl_errno = curl_errno($ch);
    $curl_error = curl_error($ch);
    curl_close($ch);

    // 5. Error handling cURL: cegah hang jika API lambat / network putus
    if ($curl_errno !== 0) {
        $error_message = "cURL Error [{$curl_errno}]: {$curl_error}";
        error_log("[Telegram Notification Error] " . $error_message);
        return [
            'success'   => false,
            'http_code' => 0,
            'error'     => $error_message,
            'response'  => null
        ];
    }

    $decoded_response = json_decode($response, true);

    // Cek apakah response code bukan 200 atau field 'ok' bernilai false
    if ($http_code !== 200 || empty($decoded_response['ok'])) {
        $api_desc = $decoded_response['description'] ?? 'Unknown Telegram error';
        $error_message = "Telegram API Error [HTTP {$http_code}]: {$api_desc}";
        error_log("[Telegram Notification Error] " . $error_message);
        return [
            'success'   => false,
            'http_code' => $http_code,
            'error'     => $error_message,
            'response'  => $decoded_response
        ];
    }

    return [
        'success'   => true,
        'http_code' => $http_code,
        'error'     => null,
        'response'  => $decoded_response
    ];
}

/**
 * Helper: Koneksi Database PDO untuk Lingkungan XAMPP ServisYuk
 *
 * @return PDO
 */
function getDatabaseConnection() {
    $host = '127.0.0.1';
    $db   = 'db_jasa_perbaikan';
    $user = 'root';
    $pass = '';
    $port = 3306;

    // Cek apakah file konfigurasi ServisYuk tersedia
    $configFile = __DIR__ . '/config/database.php';
    if (file_exists($configFile)) {
        $cfg = require $configFile;
        if (is_array($cfg)) {
            $host = $cfg['host'] ?? $host;
            $db   = $cfg['dbname'] ?? $db;
            $user = $cfg['username'] ?? $user;
            $pass = $cfg['password'] ?? $pass;
            $port = $cfg['port'] ?? $port;
        }
    }

    $dsn = "mysql:host={$host};port={$port};dbname={$db};charset=utf8mb4";
    $options = [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ];

    return new PDO($dsn, $user, $pass, $options);
}

/**
 * 2. Blok Kode Pemicu (Trigger) saat Pembaruan Status Servis
 *
 * @param PDO    $pdo          Instance PDO aktif
 * @param string $status_baru  Status servis baru
 * @param int    $customer_id  ID customer pemilik tiket
 * @param string $kode_tiket   Kode tiket servis (misal: SRV-20261023-001)
 * @param string $nama_teknisi Nama teknisi yang ditugaskan
 * @param string $bot_token    Token Bot Telegram
 * @return array|null          Hasil pengiriman notifikasi, atau null jika status tidak cocok
 */
function handleStatusUpdateTrigger($pdo, $status_baru, $customer_id, $kode_tiket, $nama_teknisi, $bot_token = TELEGRAM_BOT_TOKEN) {
    // Cek kondisi status baru
    if ($status_baru == "Teknisi Ditugaskan") {
        try {
            // 2. Simulasi Query PDO: Mengambil nilai chat_id Telegram dari tabel customers
            // Catatan: Mendukung query langsung ke tabel/view 'customers'
            // serta fallback ke tabel 'users' jika sistem menggunakan satu tabel akun.
            $stmt = $pdo->prepare("SELECT chat_id FROM customers WHERE id = :customer_id LIMIT 1");
            $stmt->execute(['customer_id' => $customer_id]);
            $customer = $stmt->fetch();

            $chat_id = null;
            if ($customer && !empty($customer['chat_id'])) {
                $chat_id = trim($customer['chat_id']);
            } else {
                // Fallback query jika kolom bernama telegram_chat_id di tabel users
                $stmt2 = $pdo->prepare("SELECT telegram_chat_id FROM users WHERE id = :user_id LIMIT 1");
                $stmt2->execute(['user_id' => $customer_id]);
                $user = $stmt2->fetch();
                if ($user && !empty($user['telegram_chat_id'])) {
                    $chat_id = trim($user['telegram_chat_id']);
                }
            }

            // 3. Fallback untuk testing jika chat_id dari database kosong/null
            $is_fallback = false;
            if (empty($chat_id)) {
                $chat_id = TELEGRAM_FALLBACK_CHAT_ID; // "1988757332"
                $is_fallback = true;
            }

            // 4. Susun isi pesan yang informatif dengan tag HTML (menebalkan kode tiket)
            $pesan  = "🔔 <b>NOTIFIKASI STATUS SERVISYUK</b>\n";
            $pesan .= "━━━━━━━━━━━━━━━━━━━━\n\n";
            $pesan .= "📋 Kode Tiket: <b>{$kode_tiket}</b>\n";
            $pesan .= "👨‍🔧 Teknisi Ditugaskan: <b>{$nama_teknisi}</b>\n";
            $pesan .= "🔧 Status Terbaru: <b>{$status_baru}</b>\n\n";
            $pesan .= "Halo! Teknisi profesional ServisYuk telah ditugaskan untuk menangani perangkat Anda. ";
            $pesan .= "Silakan tunggu kedatangan teknisi sesuai jadwal yang telah ditentukan.\n\n";
            if ($is_fallback) {
                $pesan .= "<i>(Info: Notifikasi testing dikirim menggunakan fallback ID)</i>\n\n";
            }
            $pesan .= "━━━━━━━━━━━━━━━━━━━━\n";
            $pesan .= "Pesan otomatis dari @servisyuk_bot";

            // Eksekusi pengiriman notifikasi via cURL
            $result = sendTelegramNotification($chat_id, $pesan, $bot_token);
            $result['used_chat_id']  = $chat_id;
            $result['is_fallback']   = $is_fallback;
            $result['status_baru']   = $status_baru;
            $result['kode_tiket']    = $kode_tiket;
            $result['nama_teknisi']  = $nama_teknisi;

            return $result;

        } catch (PDOException $e) {
            error_log("[Database Error Trigger] " . $e->getMessage());
            return [
                'success'   => false,
                'http_code' => 500,
                'error'     => "Database Error: " . $e->getMessage()
            ];
        } catch (Exception $e) {
            error_log("[General Error Trigger] " . $e->getMessage());
            return [
                'success'   => false,
                'http_code' => 500,
                'error'     => "Error: " . $e->getMessage()
            ];
        }
    }

    return [
        'success'   => false,
        'message'   => "Status bukan 'Teknisi Ditugaskan', notifikasi dilewati.",
        'status'    => $status_baru
    ];
}

// ============================================================================
// SIMULASI / PENGUJIAN OTOMATIS (Bisa dijalankan via CLI atau Browser)
// ============================================================================
$is_cli = (php_sapi_name() === 'cli');
$is_direct_access = (isset($_SERVER['SCRIPT_FILENAME']) && realpath(__FILE__) === realpath($_SERVER['SCRIPT_FILENAME']));

if ($is_cli || $is_direct_access) {
    if (!$is_cli) {
        header('Content-Type: text/html; charset=utf-8');
        echo '<!DOCTYPE html><html lang="id"><head><meta charset="UTF-8"><title>Uji Notifikasi Telegram ServisYuk</title>';
        echo '<style>body{font-family:Segoe UI,sans-serif;background:#0f172a;color:#f8fafc;padding:30px;line-height:1.6;} .card{background:#1e293b;border-radius:12px;padding:24px;max-width:700px;margin:auto;box-shadow:0 10px 25px rgba(0,0,0,0.3);border:1px solid #334155;} .badge{display:inline-block;padding:4px 10px;border-radius:6px;font-size:12px;font-weight:600;} .badge-success{background:#059669;color:#fff;} .badge-warn{background:#d97706;color:#fff;} pre{background:#090d16;padding:12px;border-radius:8px;overflow:auto;font-size:13px;color:#38bdf8;} hr{border:none;border-top:1px solid #334155;margin:20px 0;}</style></head><body>';
        echo '<div class="card">';
        echo '<h2>🤖 Uji Notifikasi Bot Telegram ServisYuk (@servisyuk_bot)</h2>';
        echo '<p>Menguji pengiriman notifikasi otomatis saat status servis berubah menjadi <b>Teknisi Ditugaskan</b>.</p><hr>';
    } else {
        echo "\n=======================================================\n";
        echo " 🤖 UJI NOTIFIKASI TELEGRAM SERVISYUK (@servisyuk_bot)\n";
        echo "=======================================================\n\n";
    }

    try {
        // 1. Inisialisasi Database
        $pdo = getDatabaseConnection();

        // 2. Data Simulasi Sesuai Kasus Pembaruan Status
        $status_baru  = "Teknisi Ditugaskan";
        $customer_id  = 5; // ID Customer di database
        $kode_tiket   = "SRV-20261023-009";
        $nama_teknisi = "Agus Prasetyo (Spesialis Mesin Cuci)";

        if ($is_cli) {
            echo "Memproses simulasi update status...\n";
            echo "- Status Baru : {$status_baru}\n";
            echo "- Customer ID : {$customer_id}\n";
            echo "- Kode Tiket  : {$kode_tiket}\n";
            echo "- Teknisi     : {$nama_teknisi}\n\n";
            echo "Menghubungi Telegram API via cURL...\n";
        }

        // 3. Jalankan Blok Pemicu (Trigger)
        $hasil = handleStatusUpdateTrigger(
            $pdo,
            $status_baru,
            $customer_id,
            $kode_tiket,
            $nama_teknisi,
            TELEGRAM_BOT_TOKEN
        );

        // 4. Output Hasil
        if ($is_cli) {
            echo "HASIL PENGIRIMAN:\n";
            echo "Status       : " . ($hasil['success'] ? "✅ SUKSES" : "❌ GAGAL") . "\n";
            echo "HTTP Code    : " . $hasil['http_code'] . "\n";
            echo "Chat ID      : " . ($hasil['used_chat_id'] ?? '-') . ($hasil['is_fallback'] ? " (Fallback Testing)" : " (Dari Database)") . "\n";
            if (!empty($hasil['error'])) {
                echo "Error        : " . $hasil['error'] . "\n";
            }
            if (!empty($hasil['response']['result']['message_id'])) {
                echo "Message ID   : " . $hasil['response']['result']['message_id'] . "\n";
            }
            echo "\nRespons Mentah JSON Telegram:\n";
            echo json_encode($hasil['response'], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) . "\n";
            echo "=======================================================\n";
        } else {
            $badge = $hasil['success'] ? '<span class="badge badge-success">✅ SUKSES TERKIRIM</span>' : '<span class="badge badge-warn">❌ GAGAL</span>';
            echo "<p>Status Eksekusi: {$badge}</p>";
            echo "<ul>";
            echo "<li><b>HTTP Response Code:</b> {$hasil['http_code']}</li>";
            echo "<li><b>Chat ID Tujuan:</b> " . htmlspecialchars($hasil['used_chat_id'] ?? '-') . ($hasil['is_fallback'] ? ' <i>(Menggunakan Fallback Testing: 1988757332)</i>' : ' <i>(Diambil dari Database)</i>') . "</li>";
            echo "<li><b>Kode Tiket:</b> " . htmlspecialchars($hasil['kode_tiket'] ?? '-') . "</li>";
            echo "<li><b>Nama Teknisi:</b> " . htmlspecialchars($hasil['nama_teknisi'] ?? '-') . "</li>";
            echo "<li><b>Status Baru:</b> " . htmlspecialchars($hasil['status_baru'] ?? '-') . "</li>";
            if (!empty($hasil['error'])) {
                echo "<li style='color:#f87171;'><b>Pesan Error:</b> " . htmlspecialchars($hasil['error']) . "</li>";
            }
            echo "</ul>";
            echo "<h4>Respons Mentah dari API Telegram:</h4>";
            echo "<pre>" . htmlspecialchars(json_encode($hasil['response'], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE)) . "</pre>";
            echo "<p style='font-size:12px;color:#94a3b8;'>Silakan periksa aplikasi Telegram pada akun dengan chat_id 1988757332 untuk melihat pesan notifikasi langsung dari @servisyuk_bot.</p>";
            echo '</div></body></html>';
        }

    } catch (Exception $e) {
        if ($is_cli) {
            echo "ERROR: " . $e->getMessage() . "\n";
        } else {
            echo "<p style='color:#f87171;'><b>Fatal Error:</b> " . htmlspecialchars($e->getMessage()) . "</p></div></body></html>";
        }
    }
}
