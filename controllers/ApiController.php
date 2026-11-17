<?php
// ==========================================================
// Controller: ApiController (JSON API Endpoints)
// ==========================================================

require_once __DIR__ . '/../core/Auth.php';
require_once __DIR__ . '/../core/Helper.php';
require_once __DIR__ . '/../core/Database.php';
require_once __DIR__ . '/../core/AiService.php';
require_once __DIR__ . '/../core/ChatbotService.php';
require_once __DIR__ . '/../models/ServiceRequest.php';
require_once __DIR__ . '/../models/Service.php';

class ApiController {
    public function diagnose(): void {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            Helper::jsonResponse(['success' => false, 'message' => 'Metode HTTP tidak diizinkan.'], 405);
        }

        // Parse JSON or form post
        $input = json_decode(file_get_contents('php://input'), true);
        if (!$input) {
            $input = $_POST;
        }

        $categoryName = trim($input['category_name'] ?? ($input['appliance_name'] ?? ''));
        $brand = trim($input['brand'] ?? '');
        $model = trim($input['model'] ?? '');
        $symptoms = trim($input['symptoms'] ?? ($input['complaint'] ?? ''));
        $soundBehavior = trim($input['sound_behavior'] ?? '');
        $ageYears = trim($input['age_years'] ?? '');

        if (empty($symptoms)) {
            Helper::jsonResponse(['success' => false, 'message' => 'Deskripsi keluhan atau gejala kerusakan belum diisi.'], 400);
        }

        $result = AiService::diagnose([
            'category_name' => $categoryName,
            'brand' => $brand,
            'model' => $model,
            'symptoms' => $symptoms,
            'sound_behavior' => $soundBehavior,
            'age_years' => $ageYears
        ]);

        Helper::jsonResponse($result);
    }

    public function storeAiPrefill(): void {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            Helper::jsonResponse(['success' => false, 'message' => 'Metode HTTP tidak diizinkan.'], 405);
        }

        $input = json_decode(file_get_contents('php://input'), true) ?: $_POST;

        $db = Database::getInstance();
        $appliance = trim($input['category_name'] ?? ($input['appliance_name'] ?? 'AC'));
        $srv = $db->fetch("SELECT id FROM services WHERE service_name LIKE ? LIMIT 1", ["%$appliance%"]);

        $_SESSION['ai_prefill'] = [
            'service_id' => $srv['id'] ?? '',
            'appliance_name' => $appliance,
            'brand' => trim($input['brand'] ?? ''),
            'model' => trim($input['model'] ?? ''),
            'complaint' => trim($input['symptoms'] ?? ($input['complaint'] ?? '')),
            'ai_diagnosis_summary' => trim($input['ai_summary'] ?? ($input['issue_title'] ?? '')),
            'initial_cost_estimate' => trim($input['estimated_cost_formatted'] ?? '')
        ];

        Helper::jsonResponse([
            'success' => true,
            'redirect_url' => BASE_URL . '/index.php?page=customer_booking_create'
        ]);
    }

    public function quickTrack(): void {
        $code = trim($_GET['code'] ?? ($_POST['code'] ?? ''));

        if (empty($code)) {
            Helper::jsonResponse(['success' => false, 'message' => 'Kode tiket atau nomor telepon tidak boleh kosong.'], 400);
        }

        $booking = ServiceRequest::findByCode($code);

        if (!$booking) {
            Helper::jsonResponse(['success' => false, 'message' => 'Permintaan dengan kode atau nomor telepon tersebut tidak ditemukan. Silakan cek kembali input Anda.'], 404);
        }

        $statusInfo = Helper::getStatusInfo($booking['status']);
        $booking['status_info'] = $statusInfo;
        $booking['scheduled_date_formatted'] = Helper::formatDateIndo($booking['schedule'], true);

        Helper::jsonResponse([
            'success' => true,
            'data' => $booking
        ]);
    }

    public function getServices(): void {
        $services = Service::all();
        Helper::jsonResponse(['success' => true, 'data' => $services]);
    }

    public function dbContext(): void {
        $intent = trim($_GET['intent'] ?? '');
        $msg = trim($_GET['msg'] ?? '');

        if (!empty($intent) || !empty($msg)) {
            $user = null;
            if (class_exists('Auth') && Auth::check()) {
                $user = Auth::user();
            } elseif (!empty($_GET['user_id'])) {
                $uid = (int)$_GET['user_id'];
                $dbUser = Database::getInstance()->fetch("SELECT * FROM users WHERE id = ? LIMIT 1", [$uid]);
                if ($dbUser) {
                    $user = [
                        'logged_in' => true,
                        'id' => (int)$dbUser['id'],
                        'name' => $dbUser['name'],
                        'email' => $dbUser['email'],
                        'role' => $dbUser['role'],
                        'phone' => $dbUser['phone'] ?? '',
                        'address' => $dbUser['address'] ?? ''
                    ];
                    if ($dbUser['role'] === 'technician') {
                        $t = Database::getInstance()->fetch("SELECT * FROM technicians WHERE user_id = ? LIMIT 1", [$dbUser['id']]);
                        if ($t) {
                            $user['technician_id'] = (int)$t['id'];
                        }
                    }
                }
            } elseif (!empty($_GET['role']) && in_array($_GET['role'], ['admin', 'technician', 'customer', 'guest'], true)) {
                $user = ['role' => $_GET['role']];
            }
            if (empty($intent)) {
                $intent = ChatbotService::detectIntent($msg);
            }
            $dyn = ChatbotService::fetchDynamicContext($intent, $user, $msg);
            Helper::jsonResponse([
                'success' => true,
                'intent' => $intent,
                'context' => $dyn['context_text'],
                'require_table' => $dyn['require_table'],
                'absolute_data' => $dyn['absolute_data'],
                'timestamp' => date('Y-m-d H:i:s')
            ]);
            return;
        }

        $summary = $this->getLiveDatabaseSummary();
        
        // Also save to database_knowledge.json for Node.js fallback
        $kbFile = __DIR__ . '/../chatbot/database_knowledge.json';
        @file_put_contents($kbFile, json_encode(['context' => $summary, 'updated_at' => date('Y-m-d H:i:s')], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

        Helper::jsonResponse([
            'success' => true,
            'context' => $summary,
            'timestamp' => date('Y-m-d H:i:s')
        ]);
    }

    public function currentUser(): void {
        $userId = $_SESSION['user_id'] ?? (class_exists('Auth') ? Auth::id() : null);
        if (!$userId) {
            Helper::jsonResponse(['logged_in' => false]);
            return;
        }

        $db = Database::getInstance();
        $u = $db->fetch("SELECT * FROM users WHERE id = ? LIMIT 1", [$userId]);
        if (!$u) {
            Helper::jsonResponse(['logged_in' => false]);
            return;
        }

        $userData = [
            'logged_in' => true,
            'id' => (int)$u['id'],
            'name' => $u['name'],
            'email' => $u['email'],
            'role' => $u['role'],
            'phone' => $u['phone'] ?? '',
            'address' => $u['address'] ?? ''
        ];

        if ($u['role'] === 'technician') {
            $t = $db->fetch("SELECT * FROM technicians WHERE user_id = ? LIMIT 1", [$u['id']]);
            if ($t) {
                $userData['technician_id'] = $t['id'];
                $userData['specialization'] = $t['specialization'] ?? '';
                $userData['experience'] = $t['experience'] ?? '';
                $userData['availability'] = $t['availability'] ?? '';
            }
        } elseif ($u['role'] === 'customer') {
            $reqs = $db->fetchAll("SELECT request_code, appliance_name, brand, complaint, status, schedule FROM service_requests WHERE customer_id = ? ORDER BY id DESC LIMIT 5", [$u['id']]);
            $userData['service_requests'] = $reqs ?: [];
        }

        Helper::jsonResponse($userData);
    }

    public function getLiveDatabaseSummary(): string {
        $db = Database::getInstance();
        $pdo = $db->getConnection();

        // 0. Tables List
        $tables = $pdo->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
        $tablesList = implode(", ", $tables);

        // 1. System Settings
        $settings = $pdo->query("SELECT setting_key, setting_value FROM system_settings ORDER BY id")->fetchAll(PDO::FETCH_ASSOC);
        $setItems = [];
        foreach ($settings as $s) {
            if (!empty($s['setting_value']) && !str_contains($s['setting_key'], 'key')) {
                $setItems[] = "{$s['setting_key']}: {$s['setting_value']}";
            }
        }
        $setStr = implode(" | ", $setItems);

        // 2. Users
        $users = $pdo->query("SELECT id, name, email, role, phone, address, status FROM users ORDER BY id")->fetchAll(PDO::FETCH_ASSOC);
        $userStr = implode("\n", array_map(fn($u) => "• [U{$u['id']}] {$u['name']} ({$u['role']}) | {$u['email']} | HP: {$u['phone']}" . ($u['address'] ? " | Almt: {$u['address']}" : "") . " | Status: {$u['status']}", $users));

        // 3. Technicians
        $techs = $pdo->query("SELECT t.id, t.user_id, u.name, u.phone, t.specialization, t.experience, t.availability FROM technicians t JOIN users u ON t.user_id = u.id ORDER BY t.id")->fetchAll(PDO::FETCH_ASSOC);
        $techStr = implode("\n", array_map(fn($t) => "• [T{$t['id']}] {$t['name']} (U{$t['user_id']}) | Spesialis: {$t['specialization']} ({$t['experience']}) | Status: {$t['availability']} | HP: {$t['phone']}", $techs));

        // 4. Categories
        $cats = $pdo->query("SELECT id, name, base_inspection_fee, description FROM categories ORDER BY id")->fetchAll(PDO::FETCH_ASSOC);
        $catStr = implode(", ", array_map(fn($c) => "[C{$c['id']}] {$c['name']} (Inspeksi: Rp " . number_format($c['base_inspection_fee'], 0, ',', '.') . ")", $cats));

        // 5. Services
        $services = $pdo->query("SELECT id, service_name, estimated_price FROM services ORDER BY id")->fetchAll(PDO::FETCH_ASSOC);
        $srvStr = implode("\n", array_map(fn($s) => "• [S{$s['id']}] {$s['service_name']}: Rp " . number_format($s['estimated_price'], 0, ',', '.'), $services));

        // 6. Service Requests
        $reqs = $pdo->query("SELECT sr.request_code, u.name AS cust_name, u.phone, sr.appliance_name, sr.brand, sr.complaint, sr.status, sr.schedule FROM service_requests sr LEFT JOIN users u ON sr.customer_id = u.id ORDER BY sr.id")->fetchAll(PDO::FETCH_ASSOC);
        $reqStr = implode("\n", array_map(fn($r) => "• [{$r['request_code']}] {$r['cust_name']} ({$r['phone']}) | {$r['appliance_name']} ({$r['brand']}) | Keluhan: \"{$r['complaint']}\" | Status: {$r['status']} | Jadwal: {$r['schedule']}", $reqs));

        // 7. Service Reports (Invoices)
        $reps = $pdo->query("SELECT rep.invoice_number, sr.request_code, tu.name AS tech_name, rep.repair_action, rep.replaced_component, rep.service_fee, rep.sparepart_fee, rep.total_amount, rep.payment_status, rep.completed_at FROM service_reports rep LEFT JOIN service_requests sr ON rep.request_id = sr.id LEFT JOIN technicians t ON rep.technician_id = t.id LEFT JOIN users tu ON t.user_id = tu.id ORDER BY rep.id")->fetchAll(PDO::FETCH_ASSOC);
        $repStr = implode("\n", array_map(fn($rp) => "• [{$rp['invoice_number']}] Tiket: {$rp['request_code']} | Teknisi: {$rp['tech_name']} | Tindakan: {$rp['repair_action']} | Part: {$rp['replaced_component']} | Total: Rp " . number_format($rp['total_amount'], 0, ',', '.') . " (Jasa: Rp " . number_format($rp['service_fee'], 0, ',', '.') . " + Part: Rp " . number_format($rp['sparepart_fee'], 0, ',', '.') . ") | Status: {$rp['payment_status']} | Selesai: {$rp['completed_at']}", $reps));

        // 8. Bookings
        $bookings = $pdo->query("SELECT b.booking_code, u.name AS cust_name, tu.name AS tech_name, b.appliance_brand, b.appliance_model, b.issue_description, b.status, b.scheduled_date FROM bookings b LEFT JOIN users u ON b.customer_id = u.id LEFT JOIN technicians t ON b.technician_id = t.id LEFT JOIN users tu ON t.user_id = tu.id ORDER BY b.id")->fetchAll(PDO::FETCH_ASSOC);
        $bkgStr = implode("\n", array_map(fn($b) => "• [{$b['booking_code']}] Cust: {$b['cust_name']} | Teknisi: " . ($b['tech_name'] ?: 'Belum Ada') . " | {$b['appliance_brand']} {$b['appliance_model']} | Keluhan: \"{$b['issue_description']}\" | Status: {$b['status']} ({$b['scheduled_date']})", $bookings));

        // 9. Booking Costs
        $costs = $pdo->query("SELECT c.invoice_number, b.booking_code, c.total_amount, c.payment_status, c.payment_method, c.paid_at FROM booking_costs c LEFT JOIN bookings b ON c.booking_id = b.id ORDER BY c.id")->fetchAll(PDO::FETCH_ASSOC);
        $costStr = implode("\n", array_map(fn($c) => "• [{$c['invoice_number']}] Booking: {$c['booking_code']} | Total: Rp " . number_format($c['total_amount'], 0, ',', '.') . " ({$c['payment_status']}, Metode: {$c['payment_method']}, Tgl: {$c['paid_at']})", $costs));

        // 10. Technician Assignments
        $assigns = $pdo->query("SELECT sr.request_code, tu.name AS tech_name, a.status, a.notes, a.assigned_at FROM technician_assignments a LEFT JOIN service_requests sr ON a.request_id = sr.id LEFT JOIN technicians t ON a.technician_id = t.id LEFT JOIN users tu ON t.user_id = tu.id ORDER BY a.id")->fetchAll(PDO::FETCH_ASSOC);
        $asgStr = implode("\n", array_map(fn($a) => "• [{$a['request_code']}] -> Teknisi: {$a['tech_name']} | Status: {$a['status']} | Catatan: {$a['notes']} ({$a['assigned_at']})", $assigns));

        // 11. Booking Status Logs
        $logs = $pdo->query("SELECT b.booking_code, l.status, l.notes, u.name AS updated_by, l.created_at FROM booking_status_logs l LEFT JOIN bookings b ON l.booking_id = b.id LEFT JOIN users u ON l.updated_by_user_id = u.id ORDER BY l.id DESC LIMIT 5")->fetchAll(PDO::FETCH_ASSOC);
        $logStr = implode("\n", array_map(fn($l) => "• [{$l['booking_code']}] -> {$l['status']} (oleh " . ($l['updated_by'] ?: 'Sistem') . ") - \"{$l['notes']}\" ({$l['created_at']})", $logs));

        // 12. Diagnoses
        $diag = $pdo->query("SELECT sr.request_code, d.complaint, d.ai_result, d.technician_result FROM diagnoses d LEFT JOIN service_requests sr ON d.request_id = sr.id ORDER BY d.id DESC LIMIT 4")->fetchAll(PDO::FETCH_ASSOC);
        $diagStr = implode("\n", array_map(fn($d) => "• [{$d['request_code']}] Keluhan: \"{$d['complaint']}\" | AI: \"{$d['ai_result']}\" | Teknisi: \"{$d['technician_result']}\"", $diag));

        // 13. AI Knowledge Base
        $kb = $pdo->query("SELECT category_name, issue_title, estimated_cost_min, estimated_cost_max, safety_warning FROM ai_knowledge_base ORDER BY id")->fetchAll(PDO::FETCH_ASSOC);
        $kbStr = implode("\n", array_map(fn($k) => "• [{$k['category_name']}] {$k['issue_title']} (Biaya: Rp " . number_format($k['estimated_cost_min'], 0, ',', '.') . " - Rp " . number_format($k['estimated_cost_max'], 0, ',', '.') . ") | Safety: {$k['safety_warning']}", $kb));

        // 14. Reviews
        $revs = $pdo->query("SELECT u.name AS cust_name, tu.name AS tech_name, r.rating, r.comment FROM reviews r LEFT JOIN users u ON r.customer_id = u.id LEFT JOIN technicians t ON r.technician_id = t.id LEFT JOIN users tu ON t.user_id = tu.id ORDER BY r.id")->fetchAll(PDO::FETCH_ASSOC);
        $revStr = implode("\n", array_map(fn($r) => "• {$r['cust_name']}" . ($r['tech_name'] ? " ke Teknisi {$r['tech_name']}" : "") . ": {$r['rating']}/5 Bintang - \"{$r['comment']}\"", $revs));

        return "=== DAFTAR SEMUA TABEL DALAM DATABASE SISTEM SERVISYUK (" . count($tables) . " TABEL) ===\n{$tablesList}\n\n" .
               "=== TABEL SYSTEM_SETTINGS (PENGATURAN SISTEM) ===\n{$setStr}\n\n" .
               "=== TABEL USERS (SEMUA PENGGUNA TERDAFTAR: " . count($users) . " ORANG) ===\n{$userStr}\n\n" .
               "=== TABEL TECHNICIANS (TIM TEKNISI RESMI) ===\n{$techStr}\n\n" .
               "=== TABEL CATEGORIES (KATEGORI PERALATAN & BIAYA INSPEKSI) ===\n{$catStr}\n\n" .
               "=== TABEL SERVICES (KATALOG LAYANAN & ESTIMASI HARGA) ===\n{$srvStr}\n\n" .
               "=== TABEL SERVICE_REQUESTS (TIKET PESANAN SERVIS) ===\n{$reqStr}\n\n" .
               "=== TABEL SERVICE_REPORTS (INVOICE & LAPORAN PENGERJAAN) ===\n{$repStr}\n\n" .
               "=== TABEL BOOKINGS (DATA BOOKING SERVIS) ===\n{$bkgStr}\n\n" .
               "=== TABEL BOOKING_COSTS (BIAYA & STATUS PEMBAYARAN) ===\n{$costStr}\n\n" .
               "=== TABEL TECHNICIAN_ASSIGNMENTS (PENUGASAN TEKNISI) ===\n{$asgStr}\n\n" .
               "=== TABEL BOOKING_STATUS_LOGS (LOG STATUS) ===\n{$logStr}\n\n" .
               "=== TABEL DIAGNOSES (RIWAYAT DIAGNOSA) ===\n{$diagStr}\n\n" .
               "=== TABEL AI_KNOWLEDGE_BASE (DIAGNOSA & SAFETY) ===\n{$kbStr}\n\n" .
               "=== TABEL REVIEWS (ULASAN PELANGGAN) ===\n{$revStr}";
    }

    private function isIdentityQuestion(string $message): bool {
        $clean = strtolower(trim(preg_replace('/[?!.,]/', '', $message)));
        if (preg_match('/^(halo\s+)?siapa (saya|aku|gue|gw)(\s+ya|\s+saat ini)?$/i', $clean)) return true;
        if (preg_match('/^(saya|aku)\s+siapa(\s+ya)?$/i', $clean)) return true;
        if (preg_match('/^siapa nama (saya|aku)(\s+ya)?$/i', $clean)) return true;
        if (preg_match('/^nama (saya|aku) siapa(\s+ya)?$/i', $clean)) return true;
        if (preg_match('/^siapa yang (sedang )?login(\s+ya|\s+saat ini)?$/i', $clean)) return true;
        if (preg_match('/^(cek )?(profil|akun|data)\s+(saya|aku)$/i', $clean)) return true;
        if (preg_match('/^apakah (saya|aku) sudah login$/i', $clean)) return true;
        return false;
    }

    private function formatIdentityResponse(?array $user): string {
        if (!$user || empty($user['logged_in']) && empty($user['id'])) {
            return "Halo! 👋 Saat ini Anda belum login atau masuk ke akun **ServisYuk** (status Anda adalah **Tamu / Pengunjung**).\n\n" .
                   "Untuk melihat profil akun, identitas, atau memantau riwayat tiket servis dan pesanan Anda, silakan masuk melalui menu **[Masuk / Daftar](" . BASE_URL . "/index.php?page=login)**.\n\n" .
                   "Ada keluhan peralatan rumah tangga atau informasi seputar ServisYuk yang bisa saya bantu?";
        }

        $name = htmlspecialchars($user['name'] ?? 'Pengguna');
        $role = $user['role'] ?? 'customer';
        $email = htmlspecialchars($user['email'] ?? '-');
        $phone = htmlspecialchars($user['phone'] ?? '-');
        $address = !empty($user['address']) ? htmlspecialchars($user['address']) : 'Belum diisi';

        if ($role === 'customer') {
            $msg = "Halo, **{$name}**! 👋\n\n" .
                   "Anda saat ini sedang login di platform **ServisYuk** sebagai **Pelanggan (Customer)**.\n\n" .
                   "📋 **Rincian Data Akun Anda:**\n" .
                   "• **Nama Lengkap**: {$name}\n" .
                   "• **Role**: Customer (Pelanggan)\n" .
                   "• **Email**: {$email}\n" .
                   "• **Nomor Telepon**: {$phone}\n" .
                   "• **Alamat**: {$address}\n" .
                   "• **Status Akun**: Aktif\n\n";

            // If there are customer requests
            $reqs = $user['service_requests'] ?? [];
            if (empty($reqs) && !empty($user['id'])) {
                try {
                    $db = Database::getInstance();
                    $reqs = $db->fetchAll("SELECT request_code, appliance_name, brand, complaint, status, schedule FROM service_requests WHERE customer_id = ? ORDER BY id DESC LIMIT 5", [$user['id']]);
                } catch (Exception $e) {}
            }

            if (!empty($reqs)) {
                $msg .= "🔧 **Riwayat / Tiket Servis Anda:**\n";
                foreach ($reqs as $r) {
                    $msg .= "• **[{$r['request_code']}]** {$r['appliance_name']} ({$r['brand']}) - Status: **{$r['status']}** (Jadwal: {$r['schedule']})\n";
                }
            } else {
                $msg .= "💡 Anda belum memiliki pesanan servis aktif. Silakan gunakan menu **Pesan Servis** jika membutuhkan bantuan perbaikan peralatan rumah tangga.";
            }

            return $msg;
        }

        if ($role === 'technician') {
            $spec = htmlspecialchars($user['specialization'] ?? 'Teknisi Servis Peralatan Rumah Tangga');
            $exp = htmlspecialchars($user['experience'] ?? 'Berpengalaman');
            $avail = htmlspecialchars($user['availability'] ?? 'available');
            $availLabel = ($avail === 'available' || $avail === 'tersedia') ? 'Tersedia (Siap Tugas)' : 'Sedang Bertugas (Busy)';

            return "Halo, **{$name}**! 👋\n\n" .
                   "Anda saat ini sedang login di platform **ServisYuk** sebagai **Teknisi Resmi**.\n\n" .
                   "🔧 **Profil Teknisi:**\n" .
                   "• **Nama**: {$name}\n" .
                   "• **Role**: Teknisi (Technician)\n" .
                   "• **Spesialisasi**: {$spec}\n" .
                   "• **Pengalaman**: {$exp}\n" .
                   "• **Status**: {$availLabel}\n" .
                   "• **Email**: {$email}\n" .
                   "• **No. HP**: {$phone}\n\n" .
                   "Semangat bertugas memberikan perbaikan terbaik untuk pelanggan ServisYuk!";
        }

        if ($role === 'admin') {
            return "Halo, **{$name}**! 👋\n\n" .
                   "Anda saat ini sedang login di platform **ServisYuk** sebagai **Administrator Utama (Admin)**.\n\n" .
                   "🛡️ **Profil Administrator:**\n" .
                   "• **Nama Pengguna**: {$name}\n" .
                   "• **Role**: Administrator Sistem (Admin)\n" .
                   "• **Email**: {$email}\n" .
                   "• **Nomor Telepon**: {$phone}\n" .
                   "• **Hak Akses**: Akses Penuh ke Seluruh Data Database, Pengguna, Teknisi, Tiket Pesanan, Laporan & Pengaturan Sistem ServisYuk.\n\n" .
                   "Ada data operasional atau statistik database yang ingin Anda periksa?";
        }

        return "Halo, **{$name}**! Anda sedang login di sistem ServisYuk dengan email {$email}.";
    }

    public function chat(): void {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            Helper::jsonResponse(['status' => 'error', 'message' => 'Metode HTTP tidak diizinkan.'], 405);
        }

        $input = json_decode(file_get_contents('php://input'), true) ?: $_POST;
        $messages = $input['messages'] ?? [];
        $userMessage = trim($input['message'] ?? '');

        if (empty($userMessage) && !empty($messages) && is_array($messages)) {
            $lastMsg = end($messages);
            $userMessage = trim($lastMsg['content'] ?? '');
        }

        if (empty($userMessage) && empty($messages)) {
            Helper::jsonResponse(['status' => 'error', 'message' => 'Pesan tidak boleh kosong.'], 400);
        }

        // Identify current user (from Session or Client JSON payload)
        $currentUser = null;
        if (class_exists('Auth') && Auth::check()) {
            $u = Auth::user();
            if ($u) {
                $currentUser = [
                    'logged_in' => true,
                    'id' => (int)$u['id'],
                    'name' => $u['name'],
                    'email' => $u['email'],
                    'role' => $u['role'],
                    'phone' => $u['phone'] ?? '',
                    'address' => $u['address'] ?? ''
                ];
                if ($u['role'] === 'technician') {
                    $t = Database::getInstance()->fetch("SELECT * FROM technicians WHERE user_id = ? LIMIT 1", [$u['id']]);
                    if ($t) {
                        $currentUser['specialization'] = $t['specialization'] ?? '';
                        $currentUser['experience'] = $t['experience'] ?? '';
                        $currentUser['availability'] = $t['availability'] ?? '';
                    }
                } elseif ($u['role'] === 'customer') {
                    $reqs = Database::getInstance()->fetchAll("SELECT request_code, appliance_name, brand, complaint, status, schedule FROM service_requests WHERE customer_id = ? ORDER BY id DESC LIMIT 5", [$u['id']]);
                    $currentUser['service_requests'] = $reqs ?: [];
                }
            }
        }
        if (!$currentUser && !empty($input['user']) && is_array($input['user'])) {
            $rawUser = $input['user'];
            $uid = (int)($rawUser['id'] ?? 0);
            if ($uid > 0) {
                $dbUser = Database::getInstance()->fetch("SELECT * FROM users WHERE id = ? LIMIT 1", [$uid]);
                if ($dbUser) {
                    $currentUser = [
                        'logged_in' => true,
                        'id' => (int)$dbUser['id'],
                        'name' => $dbUser['name'],
                        'email' => $dbUser['email'],
                        'role' => $dbUser['role'], // Verified from DB
                        'phone' => $dbUser['phone'] ?? '',
                        'address' => $dbUser['address'] ?? ''
                    ];
                    if ($dbUser['role'] === 'technician') {
                        $t = Database::getInstance()->fetch("SELECT * FROM technicians WHERE user_id = ? LIMIT 1", [$dbUser['id']]);
                        if ($t) {
                            $currentUser['technician_id'] = (int)$t['id'];
                            $currentUser['specialization'] = $t['specialization'] ?? '';
                            $currentUser['experience'] = $t['experience'] ?? '';
                            $currentUser['availability'] = $t['availability'] ?? '';
                        }
                    } elseif ($dbUser['role'] === 'customer') {
                        $reqs = Database::getInstance()->fetchAll("SELECT request_code, appliance_name, brand, complaint, status, schedule FROM service_requests WHERE customer_id = ? ORDER BY id DESC LIMIT 5", [$dbUser['id']]);
                        $currentUser['service_requests'] = $reqs ?: [];
                    }
                }
            }
        }

        // Direct high-speed identity responder
        if ($this->isIdentityQuestion($userMessage)) {
            Helper::jsonResponse([
                'status' => 'success',
                'reply' => $this->formatIdentityResponse($currentUser),
                'source' => 'identity_direct'
            ]);
            return;
        }

        $refusalText = "Maaf, saya adalah asisten virtual khusus platform **ServisYuk**. Saya hanya dapat membantu pertanyaan seputar layanan jasa perbaikan peralatan rumah tangga (AC, kulkas, mesin cuci, TV, kompor, dispenser, microwave, rice cooker), estimasi tarif, garansi servis, teknisi, serta seluruh data dan informasi yang ada di database website ServisYuk.\n\nApakah ada pertanyaan terkait layanan atau data ServisYuk yang bisa saya bantu?";

        // Strict out-of-scope pre-filter
        $lower = strtolower($userMessage);
        $isIdentityOrContext = preg_match('/\b(siapa|saya|aku|profil|akun|login|nama|nomor|alamat|pesanan|tiket|invoice|database|tabel|servisyuk|teknisi|user|budi|agus|hendra|rina|dian|permintaan|booking|layanan|pelanggan|customer|pendapatan|omset|tarif|biaya|berapa|jumlah|total|ada berapa|status|menunggu|dikonfirmasi|perjalanan|pemeriksaan|perbaikan|selesai|dibatalkan|dikerjakan|proses)\b/i', $lower);
        
        $isCoding = preg_match('/\b(coding|koding|phyton|python|javascript|typescript|c\+\+|java|html|css|php|sql|powershell|bash|script|program)\b/i', $lower)
            && !$isIdentityOrContext && !preg_match('/\b(ac|kulkas|mesin|cuci|tv|kompor|dispenser|microwave|servis|service|rusak)\b/i', $lower);
        $isCodingIntent = preg_match('/\b(buatkan|bikin|tuliskan|contoh)\s+(coding|koding|kode|code|program|script|fungsi|function|algoritma)\b/i', $lower);
        $isCreative = preg_match('/\b(buatkan|tuliskan|bikin)\s+(puisi|pantun|cerpen|cerita|dongeng|lirik|lagu|lelucon|humor|novel|esai)\b/i', $lower);
        $isTrivia = preg_match('/\b(siapa presiden|presiden indonesia|politik|pemilu|ibukota|sejarah dunia|rumus matematika|rumus fisika|tugas sekolah|pr matematika|resep makanan|resep masakan)\b/i', $lower);

        if (($isCoding || $isCodingIntent || $isCreative || $isTrivia) && !$isIdentityOrContext) {
            Helper::jsonResponse(['status' => 'success', 'reply' => $refusalText, 'guardrail' => 'blocked_out_of_scope']);
            return;
        }

        // Process Chat via ChatbotService (Dynamic Context, Intent-to-SQL, Absolute Calculations & Clean Formatting)
        try {
            $chatResult = ChatbotService::processChat($userMessage, $messages, $currentUser);
            Helper::jsonResponse($chatResult);
        } catch (Throwable $e) {
            ChatbotService::logDiagnostic('CONTROLLER_EXCEPTION', 'Exception in chat processing: ' . $e->getMessage(), [
                'user_id' => $currentUser['id'] ?? null,
                'role'    => $currentUser['role'] ?? 'guest'
            ]);
            Helper::jsonResponse([
                'status'  => 'error',
                'reply'   => 'Maaf, terjadi kendala saat memproses pesan Anda: ' . htmlspecialchars($e->getMessage()),
                'message' => $e->getMessage()
            ], 500);
        }
    }
}
