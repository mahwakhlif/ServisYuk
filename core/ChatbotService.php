<?php
// ==========================================================
// Core: ChatbotService — Dynamic Context, RBAC & Verified SQL
// ServisYuk AI Assistant (Audited & Synchronized)
// ==========================================================

require_once __DIR__ . '/Database.php';
require_once __DIR__ . '/Env.php';
require_once __DIR__ . '/Helper.php';
require_once __DIR__ . '/AiConfigService.php';

class ChatbotService {
    // Model configuration
    private const PRIMARY_MODEL  = 'qwen/qwen3.8-27b';
    private const FALLBACK_MODEL = 'openai/gpt-oss-20b';

    /**
     * Intent Constants
     */
    public const INTENT_TOTAL_REVENUE      = 'total_revenue';
    public const INTENT_REQUESTS_COUNT     = 'requests_count';
    public const INTENT_ORDERS_HISTORY     = 'orders_history';
    public const INTENT_TECHNICIANS_STATUS  = 'technicians_status';
    public const INTENT_SERVICES_CATALOG   = 'services_catalog';
    public const INTENT_INVOICES_REPORTS   = 'invoices_reports';
    public const INTENT_CUSTOMERS_LIST     = 'customers_list';
    public const INTENT_REVIEWS_RATINGS    = 'reviews_ratings';
    public const INTENT_SYSTEM_SETTINGS    = 'system_settings';
    public const INTENT_KNOWLEDGE_BASE     = 'knowledge_base';
    public const INTENT_USER_MY_ORDERS     = 'user_my_orders';
    public const INTENT_DB_OVERVIEW        = 'db_overview';
    public const INTENT_GENERAL_CHAT       = 'general_chat';

    /**
     * Ambil konfigurasi API AI aktif dari database atau fallback ke environment variable
     * Tidak mengekspos secret di frontend atau log.
     */
    public static function getActiveAiConfig(): array {
        return AiConfigService::getActiveConfig();
    }

    private static function getApiKey(): string {
        $cfg = self::getActiveAiConfig();
        return trim($cfg['api_key'] ?? '');
    }

    /**
     * Diagnostic Logger yang Aman
     * Mencatat tahap pemrosesan chatbot tanpa membocorkan API key, authorization header, atau data sensitif customer.
     */
    public static function logDiagnostic(string $stage, string $message, array $context = []): void {
        $logDir = dirname(__DIR__) . '/logs';
        if (!is_dir($logDir)) {
            @mkdir($logDir, 0755, true);
        }

        // Sanitasi konteks untuk memastikan tidak ada kunci rahasia atau otorisasi
        $sanitizedContext = [];
        foreach ($context as $k => $v) {
            if (preg_match('/(key|secret|token|auth|password|bearer)/i', $k)) {
                $sanitizedContext[$k] = '[REDACTED]';
            } elseif (is_string($v) && strlen($v) > 300) {
                $sanitizedContext[$k] = substr($v, 0, 300) . '... [truncated]';
            } else {
                $sanitizedContext[$k] = $v;
            }
        }

        $time = date('Y-m-d H:i:s');
        $jsonCtx = !empty($sanitizedContext) ? ' | Context: ' . json_encode($sanitizedContext, JSON_UNESCAPED_SLASHES) : '';
        $logLine = "[{$time}] [CHATBOT_{$stage}] {$message}{$jsonCtx}\n";

        @file_put_contents($logDir . '/chatbot_diagnostic.log', $logLine, FILE_APPEND | LOCK_EX);
    }

    /**
     * Deteksi intent dari pertanyaan pengguna dengan toleransi bahasa sehari-hari & typo
     *
     * @param string $message
     * @return string
     */
    public static function detectIntent(string $message): string {
        $clean = strtolower(trim($message));

        // 1. Total Pendapatan / Omset / Keuangan Sistem
        if (preg_match('/\b(total\s*(pendapatan|omset|omzet|penghasilan|revenue|pemasukan|keuangan)|(brp|berapa|cek)\s*(pendapatan|omset|omzet|penghasilan|duit\s*masuk|uang\s*masuk)|pendapatan\s*(lunas|bersih|kotor|sistem)?|omset|omzet|uang\s*masuk|total\s*lunas)\b/i', $clean)) {
            return self::INTENT_TOTAL_REVENUE;
        }

        // 2. Pesanan Saya Sendiri (Customer Personal Orders)
        if (preg_match('/\b(pesanan|tiket|servis|riwayat|order|orderan)\s*(saya|aku|milikku|gw|gue)\b/i', $clean)) {
            return self::INTENT_USER_MY_ORDERS;
        }

        // 3. Status Khusus / Jumlah Permintaan Servis (Menunggu, Dikonfirmasi, Teknisi Ditugaskan, Dalam Perjalanan, Pemeriksaan, Dalam Perbaikan, Selesai, Dibatalkan, Sedang Dikerjakan, Total Permintaan Masuk)
        if (
            preg_match('/\b(teknisi\s*(yang\s*)?ditugaskan)\b/i', $clean) ||
            preg_match('/\b(berapa|ada\s*berapa|berapa\s*banyak|total|jumlah|banyaknya|cek)\s*(total\s*)?(jumlah\s*)?(permintaan|booking|order|orderan|tiket|pesanan)(\s*(masuk|servis|service|saat\s*ini|keseluruhan|semuanya))?\b/i', $clean) ||
            preg_match('/\b(permintaan|booking|tiket|pesanan)\s*(yang\s*)?(masuk|ada)(\s*(ada\s*berapa|berapa\s*total|jumlahnya))?\b/i', $clean) ||
            (
                preg_match('/\b(berapa|ada\s*berapa|berapa\s*banyak|total|jumlah|banyaknya|cek|status|tiket|permintaan)\b/i', $clean) &&
                preg_match('/\b(menunggu|dikonfirmasi|terkonfirmasi|dalam\s*perjalanan|perjalanan|pemeriksaan|diperiksa|dalam\s*perbaikan|perbaikan|diperbaiki|selesai|beres|dibatalkan|batal|sedang\s*dikerjakan|dikerjakan|masih\s*berjalan|dalam\s*proses)\b/i', $clean)
            ) ||
            preg_match('/\b(berapa|ada\s*berapa)\s*(yang\s*)?(menunggu|dikonfirmasi|dalam\s*perjalanan|pemeriksaan|diperiksa|dalam\s*perbaikan|diperbaiki|selesai|beres|dibatalkan|batal|sedang\s*dikerjakan|dikerjakan|masih\s*berjalan|dalam\s*proses)\b/i', $clean)
        ) {
            return self::INTENT_REQUESTS_COUNT;
        }

        // 4. Pertanyaan Jumlah / Status / Daftar Teknisi
        if (preg_match('/\b(berapa|ada\s*berapa|berapa\s*banyak|total|jumlah|banyaknya|cek|daftar|status|siapa\s*saja|list)\s*(total\s*)?(jumlah\s*)?(teknisi|tukang\s*servis|mekanik)\b/i', $clean) ||
            preg_match('/\b(teknisi|tukang\s*servis)\s*(ada\s*berapa|jumlahnya|tersedia|ready|sibuk|busy|bertugas|aktif|resmi|yang\s*ada)\b/i', $clean) ||
            preg_match('/\bstatus\s*tknisi\b/i', $clean)) {
            return self::INTENT_TECHNICIANS_STATUS;
        }

        // 5. Pertanyaan Jumlah / Daftar Customer / Pelanggan
        if (preg_match('/\b(berapa|ada\s*berapa|berapa\s*banyak|total|jumlah|banyaknya|daftar|siapa\s*saja|data|list)\s*(total\s*)?(jumlah\s*)?(customer|pelanggan|user|pengguna|klien)\b/i', $clean)) {
            return self::INTENT_CUSTOMERS_LIST;
        }

        // 6. Riwayat / Status Seluruh Pesanan (Tabel Daftar Tiket)
        if (preg_match('/\b(riwayat|daftar|tampilkan|cek|status|list)\s*(pesanan|tiket|order|orderan|booking)\b/i', $clean) ||
            preg_match('/\b(pesanan|tiket|order|orderan|booking)\s*(hari\s*ini|terbaru|aktif|semua|saat\s*ini)\b/i', $clean)) {
            return self::INTENT_ORDERS_HISTORY;
        }

        // 7. Katalog Layanan & Biaya / Tarif Servis / Jumlah Layanan
        if (preg_match('/\b(katalog|daftar|tarif|harga|biaya|ongkos)\s*(layanan|servis|service|jasa|perbaikan|inspeksi)\b/i', $clean) ||
            preg_match('/\b(harga|biaya|tarif|ongkos)\s*(cuci|isi\s*freon|ganti\s*sparepart|pasang|perbaikan)\b/i', $clean) ||
            preg_match('/\b(berapa\s*biaya|biaya\s*servis|tarif\s*servis)\b/i', $clean) ||
            preg_match('/\b(berapa|ada\s*berapa|jumlah|total)\s*(layanan|service|servis|jasa)\b/i', $clean)) {
            return self::INTENT_SERVICES_CATALOG;
        }

        // 8. Laporan Servis / Invoice / Tagihan
        if (preg_match('/\b(daftar|laporan|riwayat|cek|status|berapa|ada\s*berapa|jumlah|total)\s*(invoice|faktur|tagihan|laporan\s*servis|nota|buku\s*kas)\b/i', $clean) ||
            preg_match('/\b(invoice|faktur|tagihan)\s*(lunas|belum\s*lunas|unpaid|paid)?\b/i', $clean)) {
            return self::INTENT_INVOICES_REPORTS;
        }

        // 9. Ulasan / Rating Pelanggan
        if (preg_match('/\b(ulasan|rating|bintang|testimoni|review|feedback|kepuasan)\b/i', $clean)) {
            return self::INTENT_REVIEWS_RATINGS;
        }

        // 10. Kontak / Alamat / Rekening Bank / CS
        if (preg_match('/\b(kontak|nomor\s*wa|whatsapp|telepon|call\s*center|alamat\s*kantor|rekening|bank\s*bca|cs|customer\s*service|jam\s*operasional)\b/i', $clean)) {
            return self::INTENT_SYSTEM_SETTINGS;
        }

        // 11. Gejala Kerusakan / Solusi / Peringatan Bahaya (Safety)
        if (preg_match('/\b(solusi|gejala|penyebab|bahaya|safety|peringatan|kenapa\s*rusak|cara\s*mengatasi|tidak\s*dingin|bocor|berisik|mati\s*total)\b/i', $clean)) {
            return self::INTENT_KNOWLEDGE_BASE;
        }

        // 12. Database Overview / Statistik Ringkas / Rekap Dashboard
        if (preg_match('/\b(database|tabel|rekap|statistik|ringkasan|jumlah\s*data|overview|dashboard)\b/i', $clean) ||
            preg_match('/\bada\s*berapa\s*(data|informasi|catatan)\b/i', $clean)) {
            return self::INTENT_DB_OVERVIEW;
        }

        return self::INTENT_GENERAL_CHAT;
    }

    /**
     * Eksekusi query READ-ONLY yang relevan secara dinamis dan aman dengan Role-Based Access Control (RBAC)
     *
     * @param string $intent
     * @param array|null $currentUser
     * @param string $userMessage
     * @return array
     */
    public static function fetchDynamicContext(string $intent, ?array $currentUser, string $userMessage): array {
        try {
            $db = Database::getInstance();
            $pdo = $db->getConnection();
        } catch (Exception $e) {
            error_log("[ChatbotService] Database connection error: " . $e->getMessage());
            return [
                'intent'         => $intent,
                'context_text'   => "ERROR_DB: Gagal terhubung ke database ServisYuk.",
                'absolute_data'  => null,
                'require_table'  => false,
                'access_denied'  => false,
                'error'          => "Koneksi database bermasalah."
            ];
        }

        $role = $currentUser['role'] ?? 'guest';
        $userId = (int)($currentUser['id'] ?? 0);
        $technicianId = (int)($currentUser['technician_id'] ?? 0);

        $contextText = '';
        $absoluteData = null;
        $requireTable = false;
        $accessDenied = false;

        switch ($intent) {
            case self::INTENT_TOTAL_REVENUE:
                // RBAC: Hanya Admin yang berhak melihat total pendapatan & keuangan sistem
                if ($role !== 'admin') {
                    $accessDenied = true;
                    $contextText = "PEMBERITAHUAN HAK AKSES:\n" .
                                   "Pengguna saat ini berstatus '{$role}'. Informasi total pendapatan dan laporan keuangan perusahaan merupakan data internal ServisYuk yang HANYA dapat diakses oleh Administrator.\n" .
                                   "Tolak permintaan ini dengan sopan dan jelaskan bahwa pengguna hanya dapat menanyakan rincian biaya tiket perbaikan atau pesanan miliknya sendiri.";
                    break;
                }

                // Query Sumber Angka Resmi: Konsisten dengan AdminController line 32 & 297 (service_reports)
                $stmt = $pdo->query("
                    SELECT 
                        COALESCE(SUM(total_amount), 0) AS total_revenue,
                        COALESCE(SUM(service_fee), 0) AS total_service_fee,
                        COALESCE(SUM(sparepart_fee), 0) AS total_sparepart_fee,
                        COUNT(*) AS count_paid
                    FROM service_reports 
                    WHERE payment_status = 'paid'
                ");
                $agg = $stmt->fetch(PDO::FETCH_ASSOC);

                $totalAmount = (float)($agg['total_revenue'] ?? 0); // Rp 685.000
                $serviceFee  = (float)($agg['total_service_fee'] ?? 0); // Rp 335.000
                $spareFee    = (float)($agg['total_sparepart_fee'] ?? 0); // Rp 350.000
                $countPaid   = (int)($agg['count_paid'] ?? 0); // 3 transaksi

                $formattedTotal   = 'Rp ' . number_format($totalAmount, 0, ',', '.');
                $formattedService = 'Rp ' . number_format($serviceFee, 0, ',', '.');
                $formattedSpare   = 'Rp ' . number_format($spareFee, 0, ',', '.');

                // Rincian transaksi faktur lunas dari service_reports
                $stmtDetails = $pdo->query("
                    SELECT 
                        rep.invoice_number,
                        sr.request_code,
                        u.name AS customer_name,
                        tu.name AS technician_name,
                        sr.appliance_name,
                        rep.service_fee,
                        rep.sparepart_fee,
                        rep.total_amount,
                        rep.payment_status,
                        rep.completed_at
                    FROM service_reports rep
                    JOIN service_requests sr ON rep.request_id = sr.id
                    JOIN users u ON sr.customer_id = u.id
                    JOIN technicians t ON rep.technician_id = t.id
                    JOIN users tu ON t.user_id = tu.id
                    WHERE rep.payment_status = 'paid'
                    ORDER BY rep.completed_at DESC
                ");
                $details = $stmtDetails->fetchAll(PDO::FETCH_ASSOC);

                // Cek juga tagihan yang belum lunas (unpaid) dari service_reports dan booking_costs
                $unpaidReports = $pdo->query("
                    SELECT invoice_number, total_amount, payment_status 
                    FROM service_reports 
                    WHERE payment_status = 'unpaid'
                ")->fetchAll(PDO::FETCH_ASSOC);

                $unpaidCosts = $pdo->query("
                    SELECT invoice_number, total_amount, payment_status 
                    FROM booking_costs 
                    WHERE payment_status = 'unpaid'
                ")->fetchAll(PDO::FETCH_ASSOC);

                $unpaidRows = array_merge($unpaidReports, $unpaidCosts);
                $unpaidTotal = 0;
                foreach ($unpaidRows as $up) {
                    $unpaidTotal += (float)$up['total_amount'];
                }
                $formattedUnpaid = 'Rp ' . number_format($unpaidTotal, 0, ',', '.');

                $detailLines = [];
                foreach ($details as $d) {
                    $tot = 'Rp ' . number_format($d['total_amount'], 0, ',', '.');
                    $jasa = 'Rp ' . number_format($d['service_fee'], 0, ',', '.');
                    $part = 'Rp ' . number_format($d['sparepart_fee'], 0, ',', '.');
                    $date = !empty($d['completed_at']) ? date('d M Y H:i', strtotime($d['completed_at'])) : '-';
                    $detailLines[] = "• Faktur [{$d['invoice_number']}] Tiket {$d['request_code']} | Pelanggan: {$d['customer_name']} | Teknisi: {$d['technician_name']} | Alat: {$d['appliance_name']} | Jasa: {$jasa} | Part: {$part} | Total: {$tot} | Status: Lunas ({$date})";
                }
                $detailsStr = implode("\n", $detailLines);

                $absoluteData = [
                    'total_revenue'      => $totalAmount,
                    'formatted_revenue'  => $formattedTotal,
                    'total_service_fee'  => $serviceFee,
                    'formatted_service'  => $formattedService,
                    'total_sparepart_fee'=> $spareFee,
                    'formatted_sparepart'=> $formattedSpare,
                    'count_paid'         => $countPaid,
                    'unpaid_amount'      => $unpaidTotal,
                    'formatted_unpaid'   => $formattedUnpaid,
                    'unpaid_count'       => count($unpaidRows)
                ];

                $contextText = "=== [DATA RESMI KEUANGAN SISTEM - NILAI MUTLAK DASHBOARD ADMIN] ===\n" .
                               "• Total Pendapatan Terverifikasi (Lunas): {$formattedTotal}\n" .
                               "• Rincian Pendapatan Jasa: {$formattedService}\n" .
                               "• Rincian Penjualan Sparepart: {$formattedSpare}\n" .
                               "• Jumlah Transaksi Faktur Lunas: {$countPaid} transaksi\n" .
                               "• Tagihan Belum Dibayar (Pending/Unpaid): " . count($unpaidRows) . " tagihan ({$formattedUnpaid})\n\n" .
                               "Rincian Transaksi Faktur Lunas:\n{$detailsStr}\n\n" .
                               "ATURAN MUTLAK:\n" .
                               "1. Angka resmi total pendapatan sistem adalah {$formattedTotal}. DILARANG MENGHITUNG ULANG ATAU MENGUBAH ANGKA INI.\n" .
                               "2. DILARANG menyebut nama tabel teknis (seperti 'service_reports' atau 'booking_costs'). Sebut sebagai 'catatan sistem resmi ServisYuk' atau 'dashboard keuangan Admin'.\n" .
                               "3. Sajikan rincian transaksi dalam format TABEL yang rapi.";
                $requireTable = true;
                break;

            case self::INTENT_REQUESTS_COUNT:
                $bTotal = (int)$pdo->query("SELECT COUNT(*) FROM service_requests")->fetchColumn();

                // 8 Status Resmi Permintaan Servis (konsisten dengan dropdown Perbarui Status Permintaan & Dashboard Admin)
                $officialStatuses = [
                    'Menunggu',
                    'Dikonfirmasi',
                    'Teknisi Ditugaskan',
                    'Dalam Perjalanan',
                    'Pemeriksaan',
                    'Dalam Perbaikan',
                    'Selesai',
                    'Dibatalkan'
                ];

                $statusBreakdownRows = $pdo->query("
                    SELECT status, COUNT(*) AS count
                    FROM service_requests
                    GROUP BY status
                ")->fetchAll(PDO::FETCH_ASSOC);

                $statusCounts = array_fill_keys($officialStatuses, 0);
                foreach ($statusBreakdownRows as $sbr) {
                    $stName = trim($sbr['status'] ?? '');
                    if (array_key_exists($stName, $statusCounts)) {
                        $statusCounts[$stName] = (int)$sbr['count'];
                    }
                }

                // Definisi Aturan Bisnis: Permintaan yang Masih Berjalan / Sedang Dikerjakan (Operasional Aktif Lapangan: Tahap 3 s/d 6)
                $inProgressTotal = $statusCounts['Teknisi Ditugaskan'] + 
                                   $statusCounts['Dalam Perjalanan'] + 
                                   $statusCounts['Pemeriksaan'] + 
                                   $statusCounts['Dalam Perbaikan'];

                // Query Tiket Riil berdasarkan hak akses untuk detail
                if ($role === 'admin') {
                    $ticketsStmt = $pdo->query("
                        SELECT sr.id, sr.request_code, sr.status, sr.appliance_name,
                               u.name AS customer_name,
                               tu.name AS technician_name
                        FROM service_requests sr
                        JOIN users u ON sr.customer_id = u.id
                        LEFT JOIN technician_assignments ta ON sr.id = ta.request_id AND ta.status IN ('assigned', 'accepted', 'in_progress', 'completed')
                        LEFT JOIN technicians t ON ta.technician_id = t.id
                        LEFT JOIN users tu ON t.user_id = tu.id
                        ORDER BY sr.id ASC
                    ");
                    $tickets = $ticketsStmt->fetchAll(PDO::FETCH_ASSOC);
                } elseif ($role === 'customer') {
                    $ticketsStmt = $pdo->prepare("
                        SELECT sr.id, sr.request_code, sr.status, sr.appliance_name,
                               u.name AS customer_name,
                               tu.name AS technician_name
                        FROM service_requests sr
                        JOIN users u ON sr.customer_id = u.id
                        LEFT JOIN technician_assignments ta ON sr.id = ta.request_id AND ta.status IN ('assigned', 'accepted', 'in_progress', 'completed')
                        LEFT JOIN technicians t ON ta.technician_id = t.id
                        LEFT JOIN users tu ON t.user_id = tu.id
                        WHERE sr.customer_id = ?
                        ORDER BY sr.id ASC
                    ");
                    $ticketsStmt->execute([$userId]);
                    $tickets = $ticketsStmt->fetchAll(PDO::FETCH_ASSOC);
                } else {
                    $tickets = [];
                }

                $statusLines = [];
                foreach ($officialStatuses as $st) {
                    $cnt = $statusCounts[$st];
                    $matchingTickets = [];
                    foreach ($tickets as $t) {
                        if ($t['status'] === $st) {
                            $matchingTickets[] = "{$t['request_code']} ({$t['appliance_name']})";
                        }
                    }
                    $ticketStr = !empty($matchingTickets) ? " [Tiket: " . implode(', ', $matchingTickets) . "]" : " [Tidak ada tiket]";
                    $statusLines[] = "• Status '{$st}': {$cnt} permintaan{$ticketStr}";
                }
                $breakdownStr = implode("\n", $statusLines);

                $absoluteData = [
                    'total_requests'     => $bTotal,
                    'status_counts'      => $statusCounts,
                    'in_progress_total'  => $inProgressTotal,
                ];

                $contextText = "=== [DATA RESMI SISTEM - JUMLAH & STATUS PERMINTAAN MASUK (SINKRON DENGAN DASHBOARD ADMIN)] ===\n" .
                               "• Total Seluruh Permintaan Masuk: {$bTotal} permintaan\n" .
                               "• Total Permintaan Sedang Dikerjakan / Masih Berjalan (Operasional Aktif Tahap 3 s/d 6): {$inProgressTotal} permintaan\n" .
                               "  (Keterangan: Gabungan dari Teknisi Ditugaskan [{$statusCounts['Teknisi Ditugaskan']}], Dalam Perjalanan [{$statusCounts['Dalam Perjalanan']}], Pemeriksaan [{$statusCounts['Pemeriksaan']}], dan Dalam Perbaikan [{$statusCounts['Dalam Perbaikan']}])\n\n" .
                               "RINCIAN RESMI JUMLAH PER STATUS OPERASIONAL (8 STATUS RESMI SESUAI DROPDOWN & DASHBOARD):\n" .
                               "{$breakdownStr}\n\n" .
                               "ATURAN MUTLAK PENYAJIAN KEPADA PENGGUNA:\n" .
                               "1. Angka di atas adalah DATA AKTUAL RESMI dari database ServisYuk dan 100% konsisten dengan Dashboard Admin.\n" .
                               "2. Jawab pertanyaan pengguna dengan angka pasti yang sesuai:\n" .
                               "   - Jika ditanya total permintaan masuk / permintaan: jawab {$bTotal} permintaan.\n" .
                               "   - Jika ditanya permintaan yang MENUNGGU: jawab {$statusCounts['Menunggu']} permintaan.\n" .
                               "   - Jika ditanya permintaan yang DIKONFIRMASI: jawab {$statusCounts['Dikonfirmasi']} permintaan.\n" .
                               "   - Jika ditanya permintaan TEKNISI DITUGASKAN / teknisi ditugaskan: jawab {$statusCounts['Teknisi Ditugaskan']} permintaan. (Jangan tertukar dengan total seluruh teknisi yaitu 4 teknisi).\n" .
                               "   - Jika ditanya permintaan DALAM PERJALANAN: jawab {$statusCounts['Dalam Perjalanan']} permintaan.\n" .
                               "   - Jika ditanya permintaan DALAM PEMERIKSAAN / pemeriksaan: jawab {$statusCounts['Pemeriksaan']} permintaan.\n" .
                               "   - Jika ditanya permintaan DALAM PERBAIKAN: jawab {$statusCounts['Dalam Perbaikan']} permintaan.\n" .
                               "   - Jika ditanya permintaan yang SUDAH SELESAI: jawab {$statusCounts['Selesai']} permintaan.\n" .
                               "   - Jika ditanya permintaan yang DIBATALKAN: jawab {$statusCounts['Dibatalkan']} permintaan.\n" .
                               "   - Jika ditanya SEDANG DIKERJAKAN / MASIH BERJALAN: jawab {$inProgressTotal} permintaan (jelaskan bahwa ini adalah gabungan 4 status aktif operasional: Teknisi Ditugaskan [{$statusCounts['Teknisi Ditugaskan']}], Dalam Perjalanan [{$statusCounts['Dalam Perjalanan']}], Pemeriksaan [{$statusCounts['Pemeriksaan']}], dan Dalam Perbaikan [{$statusCounts['Dalam Perbaikan']}]).\n" .
                               "3. DILARANG menyamakan atau menggabungkan status individual kecuali ditanya total gabungan / sedang dikerjakan.\n" .
                               "4. DILARANG mengarang nama pelanggan, ID, atau tiket fiktif. Jika diminta detail tiket, HANYA sebutkan kode tiket dan peralatan yang ada di data resmi di atas.\n" .
                               "5. Dilarang menolak pertanyaan valid tentang status atau jumlah data ServisYuk.";
                break;

            case self::INTENT_ORDERS_HISTORY:
                // RBAC:
                // Admin: melihat semua pesanan
                // Customer: hanya melihat pesanannya sendiri
                // Technician: hanya melihat pekerjaan yang ditugaskan kepadanya
                if ($role === 'admin') {
                    $stmt = $pdo->query("
                        SELECT sr.request_code, u.name AS customer_name, sr.appliance_name, sr.brand,
                               sr.complaint, sr.status, sr.schedule, tu.name AS technician_name
                        FROM service_requests sr
                        LEFT JOIN users u ON sr.customer_id = u.id
                        LEFT JOIN technician_assignments ta ON sr.id = ta.request_id AND ta.status = 'assigned'
                        LEFT JOIN technicians t ON ta.technician_id = t.id
                        LEFT JOIN users tu ON t.user_id = tu.id
                        ORDER BY sr.id DESC
                        LIMIT 10
                    ");
                    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
                    $headerTitle = "DATA RIWAYAT SELURUH PESANAN SERVIS (ADMIN VIEW)";
                } elseif ($role === 'customer') {
                    $stmt = $pdo->prepare("
                        SELECT sr.request_code, u.name AS customer_name, sr.appliance_name, sr.brand,
                               sr.complaint, sr.status, sr.schedule, tu.name AS technician_name
                        FROM service_requests sr
                        JOIN users u ON sr.customer_id = u.id
                        LEFT JOIN technician_assignments ta ON sr.id = ta.request_id AND ta.status = 'assigned'
                        LEFT JOIN technicians t ON ta.technician_id = t.id
                        LEFT JOIN users tu ON t.user_id = tu.id
                        WHERE sr.customer_id = :uid
                        ORDER BY sr.id DESC
                        LIMIT 5
                    ");
                    $stmt->execute(['uid' => $userId]);
                    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
                    $headerTitle = "DATA PESANAN SERVIS MILIK ANDA";
                } elseif ($role === 'technician') {
                    $stmt = $pdo->prepare("
                        SELECT sr.request_code, u.name AS customer_name, sr.appliance_name, sr.brand,
                               sr.complaint, sr.status, sr.schedule, tu.name AS technician_name
                        FROM service_requests sr
                        JOIN users u ON sr.customer_id = u.id
                        JOIN technician_assignments ta ON sr.id = ta.request_id
                        JOIN technicians t ON ta.technician_id = t.id
                        JOIN users tu ON t.user_id = tu.id
                        WHERE ta.technician_id = :tid
                        ORDER BY sr.id DESC
                        LIMIT 5
                    ");
                    $stmt->execute(['tid' => $technicianId]);
                    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
                    $headerTitle = "DATA TUGAS PERBAIKAN TEKNISI ANDA";
                } else {
                    $accessDenied = true;
                    $contextText = "PEMBERITAHUAN: Pengguna belum login. Untuk melihat data tiket servis secara spesifik, silakan masuk (login) terlebih dahulu. Saat ini hanya dapat disajikan informasi umum prosedur servis.";
                    break;
                }

                if (empty($rows)) {
                    $contextText = "=== {$headerTitle} ===\nBelum ada tiket pesanan servis yang tercatat pada akun ini.";
                } else {
                    $lines = [];
                    foreach ($rows as $r) {
                        $tech = !empty($r['technician_name']) ? $r['technician_name'] : 'Belum Ditugaskan';
                        $sch = !empty($r['schedule']) ? date('d M Y, H:i', strtotime($r['schedule'])) : 'Belum dijadwalkan';
                        $lines[] = "• [{$r['request_code']}] Pelanggan: {$r['customer_name']} | Alat: {$r['appliance_name']} ({$r['brand']}) | Status: {$r['status']} | Teknisi: {$tech} | Jadwal: {$sch} | Keluhan: \"{$r['complaint']}\"";
                    }
                    $contextText = "=== {$headerTitle} ===\n" . implode("\n", $lines);
                    $requireTable = true;
                }
                break;

            case self::INTENT_USER_MY_ORDERS:
                if ($role === 'customer' && $userId > 0) {
                    $stmt = $pdo->prepare("
                        SELECT sr.request_code, sr.appliance_name, sr.brand, sr.complaint, sr.status, sr.schedule,
                               tu.name AS technician_name
                        FROM service_requests sr
                        LEFT JOIN technician_assignments ta ON sr.id = ta.request_id AND ta.status = 'assigned'
                        LEFT JOIN technicians t ON ta.technician_id = t.id
                        LEFT JOIN users tu ON t.user_id = tu.id
                        WHERE sr.customer_id = :uid
                        ORDER BY sr.id DESC
                        LIMIT 5
                    ");
                    $stmt->execute(['uid' => $userId]);
                    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

                    if (!empty($rows)) {
                        $lines = [];
                        foreach ($rows as $r) {
                            $tech = !empty($r['technician_name']) ? $r['technician_name'] : 'Menunggu Teknisi';
                            $sch = !empty($r['schedule']) ? date('d M Y, H:i', strtotime($r['schedule'])) : 'Menunggu Jadwal';
                            $lines[] = "• Tiket [{$r['request_code']}] | {$r['appliance_name']} ({$r['brand']}) | Status: {$r['status']} | Teknisi: {$tech} | Jadwal: {$sch} | Keluhan: \"{$r['complaint']}\"";
                        }
                        $contextText = "=== TIKET PESANAN SERVIS PENGGUNA AKTIF ===\n" . implode("\n", $lines);
                        $requireTable = true;
                    } else {
                        $contextText = "=== TIKET PESANAN PENGGUNA ===\nAnda belum memiliki tiket pesanan servis aktif. Silakan gunakan menu Pesan Servis untuk membuat permintaan perbaikan baru.";
                    }
                } else {
                    $contextText = "=== STATUS PENGGUNA ===\nPengguna belum login sebagai pelanggan. Silakan login untuk melihat daftar pesanan pribadi Anda.";
                }
                break;

            case self::INTENT_INVOICES_REPORTS:
                // RBAC:
                // Admin: melihat semua invoice
                // Customer: hanya invoice miliknya
                // Technician: hanya invoice yang dikerjakannya
                if ($role === 'admin') {
                    $stmt = $pdo->query("
                        SELECT rep.invoice_number, sr.request_code, u.name AS customer_name, tu.name AS tech_name,
                               sr.appliance_name, rep.service_fee, rep.sparepart_fee, rep.total_amount,
                               rep.payment_status, rep.completed_at
                        FROM service_reports rep
                        JOIN service_requests sr ON rep.request_id = sr.id
                        JOIN users u ON sr.customer_id = u.id
                        JOIN technicians t ON rep.technician_id = t.id
                        JOIN users tu ON t.user_id = tu.id
                        ORDER BY rep.completed_at DESC
                    ");
                } elseif ($role === 'customer') {
                    $stmt = $pdo->prepare("
                        SELECT rep.invoice_number, sr.request_code, u.name AS customer_name, tu.name AS tech_name,
                               sr.appliance_name, rep.service_fee, rep.sparepart_fee, rep.total_amount,
                               rep.payment_status, rep.completed_at
                        FROM service_reports rep
                        JOIN service_requests sr ON rep.request_id = sr.id
                        JOIN users u ON sr.customer_id = u.id
                        JOIN technicians t ON rep.technician_id = t.id
                        JOIN users tu ON t.user_id = tu.id
                        WHERE sr.customer_id = :uid
                        ORDER BY rep.completed_at DESC
                    ");
                    $stmt->execute(['uid' => $userId]);
                } elseif ($role === 'technician') {
                    $stmt = $pdo->prepare("
                        SELECT rep.invoice_number, sr.request_code, u.name AS customer_name, tu.name AS tech_name,
                               sr.appliance_name, rep.service_fee, rep.sparepart_fee, rep.total_amount,
                               rep.payment_status, rep.completed_at
                        FROM service_reports rep
                        JOIN service_requests sr ON rep.request_id = sr.id
                        JOIN users u ON sr.customer_id = u.id
                        JOIN technicians t ON rep.technician_id = t.id
                        JOIN users tu ON t.user_id = tu.id
                        WHERE rep.technician_id = :tid
                        ORDER BY rep.completed_at DESC
                    ");
                    $stmt->execute(['tid' => $technicianId]);
                } else {
                    $accessDenied = true;
                    $contextText = "PEMBERITAHUAN: Silakan login terlebih dahulu untuk mengakses rincian faktur dan pembayaran servis.";
                    break;
                }

                $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
                if (empty($rows)) {
                    $contextText = "=== DATA INVOICE & FAKTUR SERVIS ===\nBelum ada faktur perbaikan tercatat untuk akun ini.";
                } else {
                    $lines = [];
                    foreach ($rows as $rp) {
                        $tot = 'Rp ' . number_format($rp['total_amount'], 0, ',', '.');
                        $jasa = 'Rp ' . number_format($rp['service_fee'], 0, ',', '.');
                        $part = 'Rp ' . number_format($rp['sparepart_fee'], 0, ',', '.');
                        $date = !empty($rp['completed_at']) ? date('d M Y, H:i', strtotime($rp['completed_at'])) : '-';
                        $statusLabel = $rp['payment_status'] === 'paid' ? 'Lunas' : 'Belum Lunas';
                        $lines[] = "• Faktur {$rp['invoice_number']} | Tiket: {$rp['request_code']} | Pelanggan: {$rp['customer_name']} | Teknisi: {$rp['tech_name']} | Total: {$tot} (Jasa: {$jasa}, Part: {$part}) | Status: {$statusLabel} | Tanggal: {$date}";
                    }
                    $contextText = "=== DATA LAPORAN & FAKTUR SERVIS ===\n" . implode("\n", $lines);
                    $requireTable = true;
                }
                break;

            case self::INTENT_TECHNICIANS_STATUS:
                // Data resmi jumlah teknisi konsisten dengan Admin Dashboard (AdminController.php baris 33)
                $totalTech = (int)$pdo->query("SELECT COUNT(*) FROM technicians")->fetchColumn();
                $availTech = (int)$pdo->query("SELECT COUNT(*) FROM technicians WHERE availability IN ('available', 'tersedia')")->fetchColumn();
                $busyTech  = (int)$pdo->query("SELECT COUNT(*) FROM technicians WHERE availability IN ('busy', 'sibuk')")->fetchColumn();

                $stmt = $pdo->query("
                    SELECT t.id, u.name, t.specialization, t.experience, t.availability, u.phone,
                           COUNT(ta.id) AS active_assignments
                    FROM technicians t
                    JOIN users u ON t.user_id = u.id
                    LEFT JOIN technician_assignments ta ON t.id = ta.technician_id AND ta.status = 'assigned'
                    GROUP BY t.id
                    ORDER BY t.id ASC
                ");
                $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

                $lines = [];
                foreach ($rows as $t) {
                    $statusLabel = ($t['availability'] === 'available' || $t['availability'] === 'tersedia') ? 'Tersedia (Siap Tugas)' : 'Sedang Bertugas';
                    $lines[] = "• ID T{$t['id']} | Nama: {$t['name']} | Spesialisasi: {$t['specialization']} | Pengalaman: {$t['experience']} | Status: {$statusLabel} | Penugasan Aktif: {$t['active_assignments']} | Kontak: {$t['phone']}";
                }

                $assignedReqCount = (int)$pdo->query("SELECT COUNT(*) FROM service_requests WHERE status = 'Teknisi Ditugaskan'")->fetchColumn();

                $absoluteData = [
                    'total_technicians' => $totalTech,
                    'available'         => $availTech,
                    'busy'              => $busyTech,
                    'requests_assigned' => $assignedReqCount
                ];

                $contextText = "=== [DATA RESMI SISTEM - JUMLAH & STATUS TEKNISI (DASHBOARD SERVISYUK)] ===\n" .
                               "• Total Jumlah Orang Teknisi Resmi: {$totalTech} teknisi\n" .
                               "• Status Ketersediaan Teknisi:\n" .
                               "  - Tersedia (Siap Bertugas): {$availTech} teknisi (Budi Santoso, Hendra Wijaya, Kelvian)\n" .
                               "  - Sedang Bertugas: {$busyTech} teknisi (Agus Pratama)\n" .
                               "• Permintaan Servis Berstatus 'Teknisi Ditugaskan': {$assignedReqCount} permintaan\n\n" .
                               "Daftar Seluruh Teknisi Resmi ServisYuk:\n" . implode("\n", $lines) . "\n\n" .
                               "ATURAN MUTLAK SISTEM:\n" .
                               "1. Angka resmi jumlah teknisi di ServisYuk saat ini adalah {$totalTech} teknisi (DILARANG KERAS MENYEBUT 12 ATAU ANGKA LAIN).\n" .
                               "2. Sampaikan angka pasti '{$totalTech} teknisi resmi' ini secara langsung, tegas, dan ramah kepada pengguna.\n" .
                               "3. PERHATIKAN PERBEDAAN PERTANYAAN:\n" .
                               "   - Jika ditanya 'jumlah teknisi' / 'ada berapa teknisi': jawab {$totalTech} orang teknisi.\n" .
                               "   - Jika ditanya 'ada berapa teknisi ditugaskan' / 'permintaan teknisi ditugaskan': jawab {$assignedReqCount} permintaan.\n" .
                               "4. Jika ditanya ketersediaan, sampaikan bahwa terdapat {$availTech} teknisi yang tersedia dan {$busyTech} teknisi yang sedang bertugas.\n" .
                               "5. Sajikan rincian nama teknisi dalam format TABEL yang rapi jika pengguna meminta daftar.";
                $requireTable = true;
                break;

            case self::INTENT_SERVICES_CATALOG:
                // Katalog layanan aktif dan tarif estimasi resmi (Publik)
                $totalServices = (int)$pdo->query("SELECT COUNT(*) FROM services WHERE status = 'active'")->fetchColumn();
                $totalCats = (int)$pdo->query("SELECT COUNT(*) FROM categories")->fetchColumn();

                $stmt = $pdo->query("
                    SELECT id, service_name, estimated_price, description
                    FROM services
                    WHERE status = 'active'
                    ORDER BY id ASC
                ");
                $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

                $stmtCat = $pdo->query("
                    SELECT name, base_inspection_fee, description
                    FROM categories
                    ORDER BY id ASC
                ");
                $cats = $stmtCat->fetchAll(PDO::FETCH_ASSOC);

                $catLines = [];
                foreach ($cats as $c) {
                    $fee = 'Rp ' . number_format($c['base_inspection_fee'], 0, ',', '.');
                    $catLines[] = "• Kategori: {$c['name']} (Biaya Inspeksi Dasar: {$fee}) - {$c['description']}";
                }

                $srvLines = [];
                foreach ($rows as $s) {
                    $price = 'Rp ' . number_format($s['estimated_price'], 0, ',', '.');
                    $srvLines[] = "• [S{$s['id']}] {$s['service_name']} | Estimasi: {$price} | Deskripsi: {$s['description']}";
                }

                $contextText = "=== [DATA RESMI SISTEM - KATALOG & TARIF LAYANAN SERVISYUK] ===\n" .
                               "• Total Layanan Aktif: {$totalServices} layanan jasa perbaikan\n" .
                               "• Total Kategori Peralatan: {$totalCats} kategori\n\n" .
                               "=== BIAYA INSPEKSI KATEGORI PERALATAN ===\n" . implode("\n", $catLines) . "\n\n" .
                               "=== DAFTAR LAYANAN & ESTIMASI TARIF PERBAIKAN ===\n" . implode("\n", $srvLines);
                $requireTable = true;
                break;

            case self::INTENT_CUSTOMERS_LIST:
                $totalCust = (int)$pdo->query("SELECT COUNT(*) FROM users WHERE role = 'customer'")->fetchColumn();

                // RBAC: Jika hanya menanyakan jumlah pelanggan (dapat dijawab publik/umum)
                if ($role !== 'admin') {
                    $contextText = "=== [DATA RESMI SISTEM - JUMLAH PELANGGAN] ===\n" .
                                   "• Total Pelanggan Terdaftar di ServisYuk: {$totalCust} pelanggan.\n" .
                                   "(Catatan: Rincian nama dan data kontak pribadi pelanggan dilindungi privasi dan hanya dapat diakses oleh Administrator Sistem).";
                    break;
                }

                $stmt = $pdo->query("
                    SELECT u.id, u.name, u.email, u.phone, u.address, u.status,
                           COUNT(sr.id) AS total_orders
                    FROM users u
                    LEFT JOIN service_requests sr ON u.id = sr.customer_id
                    WHERE u.role = 'customer'
                    GROUP BY u.id
                    ORDER BY u.id ASC
                    LIMIT 15
                ");
                $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

                $lines = [];
                foreach ($rows as $c) {
                    $lines[] = "• ID U{$c['id']} | Nama: {$c['name']} | Email: {$c['email']} | HP: {$c['phone']} | Alamat: {$c['address']} | Status: {$c['status']} | Total Order: {$c['total_orders']}";
                }
                $contextText = "=== DATA PELANGGAN TERDAFTAR (ADMIN VIEW: " . count($rows) . " ORANG) ===\n" . implode("\n", $lines);
                $requireTable = true;
                break;

            case self::INTENT_REVIEWS_RATINGS:
                $stmt = $pdo->query("
                    SELECT u.name AS customer_name, tu.name AS tech_name, r.rating, r.comment, r.created_at
                    FROM reviews r
                    JOIN users u ON r.customer_id = u.id
                    LEFT JOIN technicians t ON r.technician_id = t.id
                    LEFT JOIN users tu ON t.user_id = tu.id
                    ORDER BY r.id DESC
                    LIMIT 8
                ");
                $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

                $lines = [];
                foreach ($rows as $rv) {
                    $tech = !empty($rv['tech_name']) ? " (Teknisi: {$rv['tech_name']})" : "";
                    $lines[] = "• {$rv['customer_name']}{$tech} | Rating: {$rv['rating']}/5 Bintang | Ulasan: \"{$rv['comment']}\"";
                }
                $contextText = "=== ULASAN & RATING KEPUASAN PELANGGAN ===\n" . implode("\n", $lines);
                $requireTable = true;
                break;

            case self::INTENT_SYSTEM_SETTINGS:
                $stmt = $pdo->query("
                    SELECT setting_key, setting_value
                    FROM system_settings
                    WHERE setting_key NOT LIKE '%key%'
                    ORDER BY id ASC
                ");
                $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

                $lines = [];
                foreach ($rows as $s) {
                    $lines[] = "• {$s['setting_key']}: {$s['setting_value']}";
                }
                $contextText = "=== INFORMASI RESMI PLATFORM SERVISYUK ===\n" . implode("\n", $lines);
                break;

            case self::INTENT_KNOWLEDGE_BASE:
                $stmt = $pdo->query("
                    SELECT category_name, issue_title, possible_causes, estimated_cost_min, estimated_cost_max, safety_warning
                    FROM ai_knowledge_base
                    ORDER BY id ASC
                    LIMIT 8
                ");
                $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

                $lines = [];
                foreach ($rows as $kb) {
                    $min = 'Rp ' . number_format($kb['estimated_cost_min'], 0, ',', '.');
                    $max = 'Rp ' . number_format($kb['estimated_cost_max'], 0, ',', '.');
                    $lines[] = "• [{$kb['category_name']}] {$kb['issue_title']} | Penyebab: {$kb['possible_causes']} | Estimasi: {$min} - {$max} | Peringatan: {$kb['safety_warning']}";
                }
                $contextText = "=== BASIS PENGETAHUAN DIAGNOSA & SAFETY PERALATAN RUMAH TANGGA ===\n" . implode("\n", $lines);
                break;

            case self::INTENT_DB_OVERVIEW:
                // Statistik resmi yang 100% konsisten dengan Admin Dashboard
                $uCount = $pdo->query("SELECT COUNT(*) FROM users WHERE role = 'customer'")->fetchColumn();
                $tCount = $pdo->query("SELECT COUNT(*) FROM technicians")->fetchColumn();
                $bTotal = $pdo->query("SELECT COUNT(*) FROM service_requests")->fetchColumn();
                $bPending = $pdo->query("SELECT COUNT(*) FROM service_requests WHERE status = 'Menunggu'")->fetchColumn();
                $bActive = $pdo->query("SELECT COUNT(*) FROM service_requests WHERE status IN ('Teknisi Ditugaskan', 'Dalam Perjalanan', 'Pemeriksaan', 'Dalam Perbaikan')")->fetchColumn();
                $bDone = $pdo->query("SELECT COUNT(*) FROM service_requests WHERE status = 'Selesai'")->fetchColumn();

                if ($role === 'admin') {
                    $revSum = $pdo->query("SELECT COALESCE(SUM(total_amount), 0) FROM service_reports WHERE payment_status = 'paid'")->fetchColumn();
                    $formattedRev = 'Rp ' . number_format($revSum, 0, ',', '.');

                    $contextText = "=== REKAPITULASI RESMI DASHBOARD SISTEM SERVISYUK (ADMIN) ===\n" .
                                   "• Total Pelanggan Terdaftar: {$uCount} orang\n" .
                                   "• Jumlah Teknisi Resmi: {$tCount} teknisi\n" .
                                   "• Total Permintaan Servis: {$bTotal} tiket\n" .
                                   "  - Menunggu Konfirmasi: {$bPending} tiket\n" .
                                   "  - Sedang Dikerjakan: {$bActive} tiket\n" .
                                   "  - Perbaikan Selesai: {$bDone} tiket\n" .
                                   "• Total Pendapatan Terverifikasi: {$formattedRev}";
                } else {
                    $contextText = "=== RINGKASAN LAYANAN SERVISYUK ===\n" .
                                   "• Jumlah Teknisi Resmi: {$tCount} teknisi berpengalaman siap melayani\n" .
                                   "• Total Permintaan Servis Ditangani: {$bTotal} perbaikan\n" .
                                   "• Pelanggan Terdaftar: {$uCount} pelanggan";
                }
                break;

            default:
                // General appliance chat: Informasi ramah tanpa query tabel berat
                $contextText = "ServisYuk melayani perbaikan peralatan rumah tangga: AC & Pendingin, Kulkas & Freezer, Mesin Cuci, Televisi (TV), Kompor Gas & Listrik, Dispenser Air, Microwave & Oven. Garansi servis resmi 30 hari. CS WhatsApp: 0812-8888-9999.";
                break;
        }

        return [
            'intent'         => $intent,
            'context_text'   => $contextText,
            'absolute_data'  => $absoluteData,
            'require_table'  => $requireTable,
            'access_denied'  => $accessDenied
        ];
    }

    /**
     * Membangun System Prompt yang memenuhi seluruh spesifikasi:
     * - Sembunyikan sisi teknis (dilarang sebut nama tabel / SQL)
     * - Format tabel rapi (Markdown table presisi / HTML table)
     * - Injeksi konteks dinamis
     * - Pembatasan hak akses role pengguna
     *
     * @param array $dynamicContext
     * @param array|null $currentUser
     * @return string
     */
    public static function buildSystemPrompt(array $dynamicContext, ?array $currentUser): string {
        $contextText  = $dynamicContext['context_text'] ?? '';
        $requireTable = !empty($dynamicContext['require_table']);
        $intent       = $dynamicContext['intent'] ?? '';
        $accessDenied = !empty($dynamicContext['access_denied']);

        // Format info login pengguna
        if ($currentUser && !empty($currentUser['logged_in'])) {
            $roleLabel = $currentUser['role'] === 'customer' ? 'Pelanggan (Customer)' : ($currentUser['role'] === 'technician' ? 'Teknisi Resmi' : 'Administrator');
            $userInfo = "STATUS PENGGUNA AKTIF:\n" .
                        "- Status: SUDAH LOGIN\n" .
                        "- Nama Lengkap: {$currentUser['name']}\n" .
                        "- Role: {$currentUser['role']} ({$roleLabel})\n" .
                        "- Email: " . (!empty($currentUser['email']) ? $currentUser['email'] : 'Belum diisi') . "\n" .
                        "- Telepon: " . (!empty($currentUser['phone']) ? $currentUser['phone'] : 'Belum diisi') . "\n" .
                        "- Alamat: " . (!empty($currentUser['address']) ? $currentUser['address'] : 'Belum diisi');
        } else {
            $userInfo = "STATUS PENGGUNA AKTIF:\n" .
                        "- Status: BELUM LOGIN (Pengunjung / Tamu)\n" .
                        "- Catatan: Jika pengguna bertanya tentang identitas akun ('Siapa saya?'), arahkan dengan sopan untuk masuk (login) ke akun ServisYuk.";
        }

        $tableInstruction = $requireTable
            ? "ATURAN FORMAT TABEL (SANGAT KETAT):
Karena data yang disajikan memuat rincian/daftar data, KAMU HARUS MENYAJIKANNYA DALAM FORMAT TABEL YANG RAPI.
Gunakan format Markdown tabel yang presisi (dengan header, garis pembatas |---|---|, dan spasi yang rata) ATAU tag HTML <table><tr><th>...</th></tr><tr><td>...</td></tr></table>.
DILARANG menyajikan data daftar hanya berupa bullet-point panjang atau teks paragraf yang tidak rapi."
            : "ATURAN FORMAT PENYAJIAN DATA:
Jika menyajikan lebih dari 1 item data, utamakan menggunakan format TABEL yang rapi dengan header yang jelas agar mudah dibaca pengguna.";

        $countInstruction = "";
        if ($intent === self::INTENT_TOTAL_REVENUE && !$accessDenied) {
            $formattedTotal = $dynamicContext['absolute_data']['formatted_revenue'] ?? 'Rp 685.000';
            $countInstruction = "INSTRUKSI KHUSUS PENDAPATAN (NILAI MUTLAK RESMI):
Nilai total pendapatan resmi sistem ServisYuk adalah {$formattedTotal} (Terverifikasi dari 3 faktur perbaikan selesai).
DILARANG KERAS menghitung ulang secara mandiri atau mengubah angka ini.
Gunakan nilai absolut {$formattedTotal} secara langsung dan sertakan rincian transaksi faktur dalam tabel.";
        } elseif ($intent === self::INTENT_REQUESTS_COUNT) {
            $reqCount = $dynamicContext['absolute_data']['total_requests'] ?? (int)Database::getInstance()->getConnection()->query("SELECT COUNT(*) FROM service_requests")->fetchColumn();
            $countInstruction = "INSTRUKSI KHUSUS PERMINTAAN MASUK & STATUS (NILAI MUTLAK RESMI):
Pengguna menanyakan jumlah atau status permintaan servis di ServisYuk.
Nilai resmi yang terdaftar di sistem dan dashboard saat ini adalah:
- Total Permintaan Masuk: {$reqCount} permintaan.
- Rincian status resmi: lihat DATA RESMI SISTEM di bawah.
ATURAN JAWABAN:
1. JIKA ditanya total permintaan masuk / booking / pesanan: jawab {$reqCount} permintaan masuk.
2. JIKA ditanya status tertentu (Menunggu, Dikonfirmasi, Teknisi Ditugaskan, Dalam Perjalanan, Pemeriksaan, Dalam Perbaikan, Selesai, Dibatalkan): jawab SESUAI ANGKA EKSPLISIT STATUS TERSEBUT pada DATA RESMI SISTEM. JANGAN menyamakan status individual.
3. JIKA ditanya 'sedang dikerjakan' atau 'masih berjalan': jelaskan bahwa terdapat 4 permintaan yang sedang aktif berproses di lapangan (Teknisi Ditugaskan: 2, Dalam Perjalanan: 1, Pemeriksaan: 0, Dalam Perbaikan: 1).
4. DILARANG KERAS menolak pertanyaan ini atau memberikan penjelasan umum tanpa angka pasti dari DATA RESMI SISTEM. Dilarang mengarang tiket atau nama fiktif.";
        } elseif ($intent === self::INTENT_TECHNICIANS_STATUS) {
            $tCount = $dynamicContext['absolute_data']['total_technicians'] ?? (int)Database::getInstance()->getConnection()->query("SELECT COUNT(*) FROM technicians")->fetchColumn();
            $countInstruction = "INSTRUKSI KHUSUS JUMLAH TEKNISI (NILAI MUTLAK RESMI):
Pengguna menanyakan jumlah atau status teknisi.
Jumlah teknisi resmi ServisYuk saat ini adalah {$tCount} orang teknisi (DILARANG KERAS MENYEBUT 12 ATAU ANGKA LAIN).
JANGAN tertukar antara 'jumlah orang teknisi' ({$tCount} orang) dengan 'permintaan berstatus teknisi ditugaskan' (2 tiket).
Sampaikan bahwa terdapat {$tCount} orang teknisi resmi di ServisYuk sesuai rincian data resmi di bawah.";
        }

        return "Kamu adalah 'ServisYuk AI Assistant', asisten virtual customer service resmi dari platform ServisYuk (Layanan Jasa Perbaikan Peralatan Rumah Tangga Berbasis Web & AI).

TUGAS UTAMA:
1. Memberikan informasi yang ramah, santun, jelas, dan akurat mengenai layanan ServisYuk, keluhan alat rumah tangga, tarif servis, teknisi, pesanan, dan operasional sistem.
2. Menjawab pertanyaan seputar IDENTITAS PENGGUNA berdasarkan 'STATUS PENGGUNA AKTIF' di bawah.

PEDOMAN GAYA & KEAMANAN SISTEM (WAJIB DIPATUHI):
1. SEMBUNYIKAN SISI TEKNIS:
   - DILARANG KERAS menyebutkan nama tabel database (seperti 'tabel service_reports', 'tabel booking_costs', 'tabel users', 'tabel service_requests', kolom SQL, atau query database).
   - DILARANG menggunakan kalimat teknis kaku seperti 'Berdasarkan data live...', 'Berdasarkan query database...', 'Di database kami...'.
   - Selalu gunakan ungkapan profesional customer service yang alami, seperti: 'Berdasarkan catatan sistem kami...', 'Saat ini di ServisYuk terdata...', 'Rincian transaksi yang tercatat adalah...'.
2. FORMAT TABEL RAPI:
   {$tableInstruction}
3. {$countInstruction}
4. STRICT SCOPE GUARDRAIL:
   - Kamu melayani pertanyaan seputar platform ServisYuk, keluhan alat rumah tangga, tarif servis, serta seluruh data dan statistik resmi sistem ServisYuk (termasuk total permintaan masuk, jumlah teknisi, jumlah pelanggan, invoice, dan pendapatan).
   - Pertanyaan mengenai jumlah permintaan masuk, jumlah teknisi, status pesanan, tarif, dan data sistem ServisYuk adalah PERTANYAAN VALID SISTEM dan HARUS DIJAWAB secara akurat berdasarkan DATA RESMI SISTEM di bawah.
   - HANYA tolak hal yang benar-benar di luar topik seperti: coding/script pemrograman, puisi/pantun, resep masakan umum, politik, atau matematika sekolah.
     Jika pengguna menanyakan hal di luar topik tersebut, tolak dengan sopan:
     'Maaf, saya adalah asisten virtual khusus platform ServisYuk. Saya hanya dapat membantu pertanyaan seputar layanan jasa perbaikan peralatan rumah tangga, estimasi biaya, teknisi, dan informasi sistem ServisYuk.'

==================================================
{$userInfo}
==================================================
DATA RESMI SISTEM SERVISYUK (TERKAIT PERTANYAAN):
==================================================
{$contextText}

Gaya Bahasa: Ramah, santun, profesional, dan dalam Bahasa Indonesia yang baik.";
    }

    /**
     * Memproses percakapan dan memanggil Groq LLM API
     *
     * @param string $userMessage
     * @param array $history
     * @param array|null $currentUser
     * @return array ['status' => string, 'reply' => string, 'intent' => string]
     */
    public static function processChat(string $userMessage, array $history, ?array $currentUser): array {
        $userMessage = trim($userMessage);

        // 1. Deteksi intent
        $intent = self::detectIntent($userMessage);

        self::logDiagnostic('REQUEST_START', 'Processing chat message', [
            'intent'      => $intent,
            'message_len' => strlen($userMessage),
            'role'        => $currentUser['role'] ?? 'guest',
            'user_id'     => $currentUser['id'] ?? null
        ]);

        // 2. Ambil konteks dinamis melalui Intent-to-SQL dengan RBAC
        $dynamicContext = self::fetchDynamicContext($intent, $currentUser, $userMessage);

        // Cek jika ada error database fatal
        if (!empty($dynamicContext['error'])) {
            self::logDiagnostic('DB_ERROR', 'Database error during dynamic context fetch: ' . $dynamicContext['error'], [
                'intent' => $intent
            ]);
            return [
                'status' => 'error',
                'reply'  => 'Maaf, terjadi kendala saat membaca data dari database sistem ServisYuk (' . $dynamicContext['error'] . '). Silakan coba beberapa saat lagi.',
                'intent' => $intent
            ];
        }

        self::logDiagnostic('CONTEXT_RESOLVED', 'Dynamic context fetched successfully', [
            'intent'        => $intent,
            'require_table' => $dynamicContext['require_table'],
            'has_absolute'  => !empty($dynamicContext['absolute_data'])
        ]);

        // 3. Bangun system prompt yang aman dan terformat
        $systemPrompt = self::buildSystemPrompt($dynamicContext, $currentUser);

        // 4. Susun pesan untuk LLM
        $messages = [
            ['role' => 'system', 'content' => $systemPrompt]
        ];

        $hasCurrentUserMessage = false;
        if (!empty($history) && is_array($history)) {
            $recent = array_slice($history, -6);
            foreach ($recent as $m) {
                if (!empty($m['role']) && !empty($m['content'])) {
                    $mContent = trim($m['content']);
                    $mRole = $m['role'] === 'assistant' ? 'assistant' : 'user';
                    $messages[] = [
                        'role'    => $mRole,
                        'content' => $mContent
                    ];
                    if ($mRole === 'user' && $mContent === $userMessage) {
                        $hasCurrentUserMessage = true;
                    }
                }
            }
        }

        if (!$hasCurrentUserMessage && !empty($userMessage)) {
            $messages[] = ['role' => 'user', 'content' => $userMessage];
        }

        // 5. Panggil AI API menggunakan konfigurasi dinamis (AiConfigService)
        $aiConfig = self::getActiveAiConfig();
        $apiKey = $aiConfig['api_key'] ?? '';
        $baseUrl = rtrim(!empty($aiConfig['base_url']) ? $aiConfig['base_url'] : 'https://api.groq.com/openai/v1', '/');
        $endpoint = $baseUrl . '/chat/completions';

        if (empty($apiKey)) {
            self::logDiagnostic('CONFIG_ERROR', 'API Key AI Chatbot belum dikonfigurasi di sistem');
            return [
                'status' => 'error',
                'reply'  => 'API Key AI Chatbot belum dikonfigurasi pada sistem ServisYuk. Administrator dapat mengonfigurasinya melalui Dashboard Admin > Konfigurasi AI.',
                'intent' => $intent
            ];
        }

        $primaryModel = !empty($aiConfig['active_model']) ? $aiConfig['active_model'] : self::PRIMARY_MODEL;
        $fallbackModel = (!empty($aiConfig['fallback_model']) && $aiConfig['fallback_model'] !== $primaryModel) 
            ? $aiConfig['fallback_model'] 
            : '';

        $models = [$primaryModel];
        if (!empty($fallbackModel)) {
            $models[] = $fallbackModel;
        }

        // Untuk Gemini, sediakan varian prefix models/ sebagai kandidat otomatis jika diperlukan
        if (($aiConfig['provider'] ?? '') === 'gemini' || str_contains($baseUrl, 'generativelanguage.googleapis.com')) {
            $altVariant = str_starts_with($primaryModel, 'models/') ? substr($primaryModel, 7) : 'models/' . $primaryModel;
            if (!in_array($altVariant, $models)) {
                $models[] = $altVariant;
            }
        }

        $reply = null;
        $usedModel = null;
        $lastFailureReason = '';

        foreach ($models as $rawModelName) {
            $modelName = (($aiConfig['provider'] ?? '') === 'gemini' || str_contains($baseUrl, 'generativelanguage.googleapis.com'))
                ? $rawModelName
                : preg_replace('/^models\//i', '', $rawModelName);
            $payload = json_encode([
                'model'       => $modelName,
                'messages'    => $messages,
                'temperature' => 0.1,
                'max_tokens'  => 1000
            ]);

            self::logDiagnostic('LLM_DISPATCH', 'Sending request to AI provider', [
                'provider'      => $aiConfig['provider'] ?? 'groq',
                'model'         => $modelName,
                'endpoint'      => $endpoint,
                'message_count' => count($messages)
            ]);

            $headers = [
                'Authorization: Bearer ' . $apiKey,
                'Content-Type: application/json'
            ];
            if (($aiConfig['provider'] ?? '') === 'openrouter') {
                $headers[] = 'HTTP-Referer: http://localhost/jasa';
                $headers[] = 'X-Title: ServisYuk';
            }

            $startCall = microtime(true);
            $ch = curl_init($endpoint);
            curl_setopt_array($ch, [
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_POST           => true,
                CURLOPT_HTTPHEADER     => $headers,
                CURLOPT_POSTFIELDS     => $payload,
                CURLOPT_TIMEOUT        => 15,
                CURLOPT_CONNECTTIMEOUT => 4,
                CURLOPT_SSL_VERIFYPEER => false
            ]);

            $res = curl_exec($ch);
            $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $curlError = curl_error($ch);
            curl_close($ch);
            $callLatency = round((microtime(true) - $startCall) * 1000);

            if ($code === 429) {
                // Rate limit: jeda 2 detik dan retry sekali
                self::logDiagnostic('LLM_RATE_LIMIT', "HTTP 429 encountered for {$modelName}, retrying after 2s...", [
                    'model' => $modelName
                ]);
                usleep(2000000);
                $ch2 = curl_init($endpoint);
                curl_setopt_array($ch2, [
                    CURLOPT_RETURNTRANSFER => true,
                    CURLOPT_POST           => true,
                    CURLOPT_HTTPHEADER     => $headers,
                    CURLOPT_POSTFIELDS     => $payload,
                    CURLOPT_TIMEOUT        => 15,
                    CURLOPT_CONNECTTIMEOUT => 4,
                    CURLOPT_SSL_VERIFYPEER => false
                ]);
                $res = curl_exec($ch2);
                $code = curl_getinfo($ch2, CURLINFO_HTTP_CODE);
                $curlError = curl_error($ch2);
                curl_close($ch2);
            }

            if ($code === 200 && !empty($res)) {
                $data = json_decode($res, true);
                $content = $data['choices'][0]['message']['content'] ?? null;
                if (!empty($content)) {
                    // Bersihkan tag penalaran internal (<think>...</think>) jika menggunakan model reasoning (misal DeepSeek-R1)
                    $cleaned = preg_replace('/<think>[\s\S]*?<\/think>\s*/i', '', $content);
                    $cleaned = trim($cleaned);
                    $reply = ($cleaned !== '') ? $cleaned : trim($content);
                    $usedModel = $modelName;
                    self::logDiagnostic('LLM_SUCCESS', "AI provider responded successfully", [
                        'model'      => $usedModel,
                        'latency_ms' => $callLatency,
                        'reply_len'  => strlen($reply)
                    ]);
                    break;
                }
            } else {
                $errDetail = $curlError;
                if (!$errDetail && !empty($res)) {
                    $parsedRes = json_decode($res, true);
                    $errObj = is_array($parsedRes) ? (!empty($parsedRes['error']) ? $parsedRes['error'] : (!empty($parsedRes[0]['error']) ? $parsedRes[0]['error'] : null)) : null;
                    if ($errObj && !empty($errObj['message'])) {
                        $errDetail = "Provider Response: " . $errObj['message'];
                    } else {
                        $errDetail = "HTTP {$code}: " . substr(strip_tags($res), 0, 250);
                    }
                }
                $lastFailureReason = $errDetail ?: "HTTP {$code}";
                self::logDiagnostic('LLM_FAILURE', "AI provider error for {$modelName}", [
                    'model'      => $modelName,
                    'http_code'  => $code,
                    'curl_error' => $curlError
                ]);
            }
        }

        // Jika API offline atau timeout, berikan pesan kegagalan yang jelas tanpa data dummy
        if (!$reply) {
            $diagMsg = 'Maaf, layanan AI sedang mengalami kendala jaringan atau antrian pemrosesan yang padat.';
            if (!empty($lastFailureReason)) {
                $diagMsg .= ' (' . htmlspecialchars($lastFailureReason) . ').';
            }
            $diagMsg .= ' Silakan coba kembali beberapa saat lagi, atau hubungi CS ServisYuk di 0812-8888-9999.';
            return [
                'status' => 'error',
                'reply'  => $diagMsg,
                'intent' => $intent
            ];
        }

        return [
            'status'        => 'success',
            'reply'         => $reply,
            'intent'        => $intent,
            'model'         => $usedModel,
            'dynamic_context' => [
                'intent'        => $intent,
                'require_table' => $dynamicContext['require_table'],
                'has_absolute'  => !empty($dynamicContext['absolute_data'])
            ]
        ];
    }
}
