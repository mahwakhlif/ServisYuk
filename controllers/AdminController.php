<?php
// ==========================================================
// Controller: AdminController
// ==========================================================

require_once __DIR__ . '/../core/Auth.php';
require_once __DIR__ . '/../core/Helper.php';
require_once __DIR__ . '/../core/Database.php';
require_once __DIR__ . '/../models/User.php';
require_once __DIR__ . '/../models/Technician.php';
require_once __DIR__ . '/../models/Service.php';
require_once __DIR__ . '/../models/ServiceRequest.php';
require_once __DIR__ . '/../models/TechnicianAssignment.php';
require_once __DIR__ . '/../models/Diagnosis.php';
require_once __DIR__ . '/../models/ServiceReport.php';
require_once __DIR__ . '/../core/TelegramService.php';
require_once __DIR__ . '/../core/FonnteService.php';
require_once __DIR__ . '/../core/AiConfigService.php';

class AdminController {
    public function __construct() {
        Auth::requireRole('admin');
    }

    public function dashboard(): void {
        $db = Database::getInstance();

        // 8 Status Resmi Permintaan Servis (konsisten dengan dropdown Perbarui Status Permintaan)
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

        // Query Agregasi Status Langsung dari service_requests (1 kali hitung, tanpa duplikasi JOIN)
        $statusRows = $db->fetchAll("
            SELECT status, COUNT(*) AS cnt 
            FROM service_requests 
            GROUP BY status
        ");

        $statusCounts = array_fill_keys($officialStatuses, 0);
        foreach ($statusRows as $row) {
            $stName = trim($row['status'] ?? '');
            if (array_key_exists($stName, $statusCounts)) {
                $statusCounts[$stName] = (int)$row['cnt'];
            }
        }

        // Definisi Permintaan Sedang Dikerjakan / Masih Berjalan (Operasional Aktif Lapangan: Tahap 3 s/d 6)
        $inProgressCount = $statusCounts['Teknisi Ditugaskan'] + 
                           $statusCounts['Dalam Perjalanan'] + 
                           $statusCounts['Pemeriksaan'] + 
                           $statusCounts['Dalam Perbaikan'];

        // High-level statistics
        $stats = [
            'total_bookings'       => $db->fetch("SELECT COUNT(*) AS cnt FROM service_requests")['cnt'] ?? 0,
            'pending_bookings'     => $statusCounts['Menunggu'],
            'in_progress_bookings' => $inProgressCount,
            'completed_bookings'   => $statusCounts['Selesai'],
            'cancelled_bookings'   => $statusCounts['Dibatalkan'],
            'status_counts'        => $statusCounts,
            'total_revenue'        => $db->fetch("SELECT SUM(total_amount) AS rev FROM service_reports WHERE payment_status = 'paid'")['rev'] ?? 0,
            'total_technicians'    => $db->fetch("SELECT COUNT(*) AS cnt FROM technicians")['cnt'] ?? 0,
            'total_customers'      => $db->fetch("SELECT COUNT(*) AS cnt FROM users WHERE role = 'customer'")['cnt'] ?? 0,
        ];

        // Recent Service Requests
        $recentBookings = ServiceRequest::all();
        $recentBookings = array_slice($recentBookings, 0, 6);

        // Appliance breakdown
        $categoryBreakdown = $db->fetchAll("
            SELECT appliance_name AS name, COUNT(id) AS count
            FROM service_requests
            GROUP BY appliance_name
            ORDER BY count DESC
        ");

        // Technicians list
        $technicians = Technician::all();

        require __DIR__ . '/../views/admin/dashboard.php';
    }

    public function bookings(): void {
        $statusFilter = $_GET['status'] ?? '';
        $search = trim($_GET['q'] ?? '');

        $filters = [];
        if (!empty($statusFilter)) {
            $filters['status'] = $statusFilter;
        }
        if (!empty($search)) {
            $filters['search'] = $search;
        }

        $bookings = ServiceRequest::all($filters);
        $technicians = Technician::all();
        $services = Service::all();

        require __DIR__ . '/../views/admin/bookings.php';
    }

    public function bookingDetail(): void {
        $requestId = (int)($_GET['id'] ?? 0);
        $code = trim($_GET['code'] ?? '');

        if ($requestId > 0) {
            $booking = ServiceRequest::find($requestId);
        } elseif (!empty($code)) {
            $booking = ServiceRequest::findByCode($code);
            if ($booking) {
                $requestId = (int)$booking['id'];
            }
        } else {
            $booking = null;
        }

        if (!$booking) {
            Helper::setFlash('error', 'Permintaan jasa tidak ditemukan.');
            header('Location: ' . BASE_URL . '/index.php?page=admin_bookings');
            exit;
        }

        $diagnosis = Diagnosis::findByRequestId($requestId);
        $report = ServiceReport::findByRequestId($requestId);
        $technicians = Technician::all();

        require __DIR__ . '/../views/admin/booking_detail.php';
    }

    public function assignTechnician(): void {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . BASE_URL . '/index.php?page=admin_bookings');
            exit;
        }

        $requestId = (int)($_POST['booking_id'] ?? ($_POST['request_id'] ?? 0));
        $technicianId = (int)($_POST['technician_id'] ?? 0);
        $notes = trim($_POST['notes'] ?? '');

        $tech = Technician::find($technicianId);
        if (!$tech) {
            Helper::setFlash('error', 'Teknisi yang dipilih tidak valid.');
            header('Location: ' . BASE_URL . '/index.php?page=admin_booking_detail&id=' . $requestId);
            exit;
        }

        // Assign technician
        TechnicianAssignment::assign($requestId, $technicianId, $notes);

        Helper::setFlash('success', "Teknisi {$tech['name']} berhasil ditugaskan untuk permintaan ini.");
        header('Location: ' . BASE_URL . '/index.php?page=admin_booking_detail&id=' . $requestId);
        exit;
    }

    public function updateBookingStatus(): void {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . BASE_URL . '/index.php?page=admin_bookings');
            exit;
        }

        $requestId = (int)($_POST['booking_id'] ?? ($_POST['request_id'] ?? 0));
        $status = trim($_POST['status'] ?? '');
        $notes = trim($_POST['notes'] ?? '');

        if (!empty($status) && $requestId > 0) {
            $currentBooking = ServiceRequest::find($requestId);
            if (!$currentBooking) {
                Helper::setFlash('error', 'Permintaan jasa tidak ditemukan.');
                header('Location: ' . BASE_URL . '/index.php?page=admin_bookings');
                exit;
            }

            // Cegah pengiriman notifikasi berulang jika status tidak berubah
            if ($currentBooking['status'] === $status) {
                Helper::setFlash('info', "Status permintaan saat ini sudah '{$status}'. Tidak ada pembaruan status.");
                header('Location: ' . BASE_URL . '/index.php?page=admin_booking_detail&id=' . $requestId);
                exit;
            }

            $affected = ServiceRequest::updateStatus($requestId, $status);

            if ($affected > 0) {
                Helper::setFlash('success', "Status permintaan berhasil diperbarui menjadi '$status'.");

                // ========================================================
                // Trigger Utama: Notifikasi WhatsApp via Fonnte API
                // ========================================================
                try {
                    FonnteService::notifyStatusUpdate($requestId, $status, $notes ?: null);
                } catch (\Throwable $e) {
                    error_log("[AdminController] WhatsApp notifyStatusUpdate error: " . $e->getMessage());
                }

                // ========================================================
                // Secondary / Legacy: Bot Telegram (hanya jika chat_id tersedia)
                // ========================================================
                try {
                    $customerChatId = TelegramService::getCustomerChatId((int)$currentBooking['customer_id']);
                    if (!empty($customerChatId)) {
                        TelegramService::notifyStatusUpdate(
                            (int)$currentBooking['customer_id'],
                            $currentBooking['request_code'],
                            $status,
                            $notes
                        );
                    }
                } catch (\Throwable $e) {
                    // Abaikan jika Telegram down agar transaksi utama tetap lancar
                    error_log("[AdminController] Telegram fallback error: " . $e->getMessage());
                }
            } else {
                Helper::setFlash('error', 'Gagal memperbarui status permintaan pada database.');
            }
        }

        header('Location: ' . BASE_URL . '/index.php?page=admin_booking_detail&id=' . $requestId);
        exit;
    }

    public function technicians(): void {
        $db = Database::getInstance();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $action = $_POST['action'] ?? '';
            if ($action === 'create') {
                $name = trim($_POST['name'] ?? '');
                $email = trim($_POST['email'] ?? '');
                $password = trim($_POST['password'] ?? '');
                $phone = trim($_POST['phone'] ?? '');
                $specialization = trim($_POST['specialization'] ?? 'Spesialis Umum');
                $experience = trim($_POST['experience'] ?? '3 Tahun');
                $address = trim($_POST['address'] ?? '');

                if (empty($name) || empty($email) || empty($password)) {
                    Helper::setFlash('error', 'Nama, email, dan kata sandi wajib diisi.');
                } else {
                    $existing = User::findByEmail($email);
                    if ($existing) {
                        Helper::setFlash('error', 'Email sudah terdaftar untuk pengguna lain.');
                    } else {
                        // Create User
                        $userId = User::create([
                            'name' => $name,
                            'email' => $email,
                            'password' => password_hash($password, PASSWORD_DEFAULT),
                            'role' => 'technician',
                            'phone' => $phone,
                            'address' => $address,
                            'status' => 'active',
                            'avatar' => 'default_avatar.png'
                        ]);

                        // Create Technician profile
                        Technician::create([
                            'user_id' => $userId,
                            'specialization' => $specialization,
                            'experience' => $experience,
                            'availability' => 'available'
                        ]);

                        Helper::setFlash('success', 'Teknisi baru berhasil ditambahkan.');
                    }
                }
            } elseif ($action === 'toggle_status') {
                $techId = (int)($_POST['technician_id'] ?? 0);
                $tech = Technician::find($techId);
                if ($tech) {
                    $newAvailability = $tech['availability'] === 'available' ? 'busy' : 'available';
                    Technician::update($techId, ['availability' => $newAvailability]);
                    Helper::setFlash('success', 'Ketersediaan teknisi berhasil diperbarui.');
                }
            }
            header('Location: ' . BASE_URL . '/index.php?page=admin_technicians');
            exit;
        }

        $technicians = Technician::all();

        require __DIR__ . '/../views/admin/technicians.php';
    }

    public function customers(): void {
        $db = Database::getInstance();

        $customers = $db->fetchAll("
            SELECT u.*,
                   COUNT(sr.id) AS total_orders
            FROM users u
            LEFT JOIN service_requests sr ON u.id = sr.customer_id
            WHERE u.role = 'customer'
            GROUP BY u.id
            ORDER BY u.created_at DESC
        ");

        require __DIR__ . '/../views/admin/customers.php';
    }

    public function services(): void {
        $db = Database::getInstance();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $action = $_POST['action'] ?? '';

            if ($action === 'create_service') {
                $serviceName = trim($_POST['service_name'] ?? ($_POST['name'] ?? ''));
                $desc = trim($_POST['description'] ?? '');
                $price = floatval($_POST['estimated_price'] ?? 75000);

                if (!empty($serviceName)) {
                    Service::create([
                        'service_name' => $serviceName,
                        'description' => $desc,
                        'estimated_price' => $price,
                        'status' => 'active'
                    ]);
                    Helper::setFlash('success', 'Jenis layanan baru berhasil ditambahkan.');
                }
            }
            header('Location: ' . BASE_URL . '/index.php?page=admin_services');
            exit;
        }

        $services = Service::all(false);

        require __DIR__ . '/../views/admin/services.php';
    }

    public function aiConfig(): void {
        Auth::requireRole('admin');

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!Helper::validateCsrf()) {
                Helper::setFlash('danger', 'Sesi keamanan CSRF tidak valid atau telah kedaluwarsa. Silakan coba kembali.');
                Helper::redirect(BASE_URL . '/index.php?page=admin_ai_config');
                return;
            }

            try {
                $newApiKey = trim($_POST['api_key'] ?? '');
                $provider = trim($_POST['provider'] ?? 'groq');
                $preset = AiConfigService::PROVIDER_PRESETS[$provider] ?? null;
                $defaultModel = $preset['default_model'] ?? 'qwen/qwen3.8-27b';
                $defaultBaseUrl = $preset['base_url'] ?? 'https://api.groq.com/openai/v1';

                $model = trim($_POST['model'] ?? '') ?: $defaultModel;
                $baseUrl = trim($_POST['base_url'] ?? '') ?: $defaultBaseUrl;

                // Simpan konfigurasi ke database, .env, dan ai_config.json
                AiConfigService::saveConfig($_POST);

                // Perbarui status koneksi dan berikan umpan balik langsung ke admin
                $testRes = AiConfigService::testConnection($provider, $newApiKey, $model, $baseUrl);
                if ($testRes['success']) {
                    Helper::setFlash('success', "Konfigurasi AI Chatbot berhasil diperbarui dan aktif! Terhubung ke model '{$testRes['model']}' ({$testRes['latency_ms']} ms).");
                } else {
                    Helper::setFlash('warning', "Konfigurasi berhasil disimpan. Catatan koneksi: {$testRes['message']}");
                }
            } catch (Exception $e) {
                Helper::setFlash('danger', "Gagal menyimpan konfigurasi: " . $e->getMessage());
            }

            Helper::redirect(BASE_URL . '/index.php?page=admin_ai_config');
            return;
        }

        $aiConfig = AiConfigService::getActiveConfig();
        $defaultModels = AiConfigService::DEFAULT_MODELS;
        $presets = AiConfigService::PROVIDER_PRESETS;

        require __DIR__ . '/../views/admin/ai_config.php';
    }

    public function testAiConnection(): void {
        Auth::requireRole('admin');

        $provider = trim($_POST['provider'] ?? 'groq');
        $preset = AiConfigService::PROVIDER_PRESETS[$provider] ?? null;
        $defaultModel = $preset['default_model'] ?? 'qwen/qwen3.8-27b';
        $defaultBaseUrl = $preset['base_url'] ?? 'https://api.groq.com/openai/v1';

        $baseUrl = trim($_POST['base_url'] ?? '') ?: $defaultBaseUrl;
        $apiKey = trim($_POST['api_key'] ?? '');
        $model = trim($_POST['model'] ?? '') ?: $defaultModel;

        $res = AiConfigService::testConnection($provider, $apiKey, $model, $baseUrl);
        Helper::jsonResponse($res);
    }

    public function fetchAiModels(): void {
        Auth::requireRole('admin');

        $provider = trim($_POST['provider'] ?? 'groq');
        $preset = AiConfigService::PROVIDER_PRESETS[$provider] ?? null;
        $defaultBaseUrl = $preset['base_url'] ?? 'https://api.groq.com/openai/v1';

        $baseUrl = trim($_POST['base_url'] ?? '') ?: $defaultBaseUrl;
        $apiKey = trim($_POST['api_key'] ?? '');

        $res = AiConfigService::fetchModels($provider, $apiKey, $baseUrl);
        Helper::jsonResponse($res);
    }

    public function aiSettings(): void {
        $this->aiConfig();
    }

    public function reports(): void {
        $db = Database::getInstance();

        $month = $_GET['month'] ?? date('Y-m');
        $monthStart = $month . '-01 00:00:00';
        $monthEnd = date('Y-m-t', strtotime($monthStart)) . ' 23:59:59';

        $reportSummary = [
            'total_income' => $db->fetch("SELECT SUM(total_amount) AS total FROM service_reports WHERE payment_status = 'paid' AND completed_at BETWEEN ? AND ?", [$monthStart, $monthEnd])['total'] ?? 0,
            'completed_orders' => $db->fetch("SELECT COUNT(*) AS cnt FROM service_requests WHERE status = 'Selesai' AND updated_at BETWEEN ? AND ?", [$monthStart, $monthEnd])['cnt'] ?? 0,
            'total_service_fee' => $db->fetch("SELECT SUM(service_fee) AS total FROM service_reports WHERE payment_status = 'paid' AND completed_at BETWEEN ? AND ?", [$monthStart, $monthEnd])['total'] ?? 0,
            'total_sparepart_fee' => $db->fetch("SELECT SUM(sparepart_fee) AS total FROM service_reports WHERE payment_status = 'paid' AND completed_at BETWEEN ? AND ?", [$monthStart, $monthEnd])['total'] ?? 0,
        ];

        $transactions = $db->fetchAll("
            SELECT sr.request_code, sr.appliance_name, sr.brand, sr.model,
                   u.name AS customer_name,
                   tu.name AS technician_name,
                   rep.invoice_number, rep.service_fee, rep.sparepart_fee, rep.total_amount, rep.payment_status, rep.completed_at
            FROM service_reports rep
            JOIN service_requests sr ON rep.request_id = sr.id
            JOIN users u ON sr.customer_id = u.id
            JOIN technicians t ON rep.technician_id = t.id
            JOIN users tu ON t.user_id = tu.id
            WHERE rep.completed_at BETWEEN ? AND ?
            ORDER BY rep.completed_at DESC
        ", [$monthStart, $monthEnd]);

        require __DIR__ . '/../views/admin/reports.php';
    }

    public function printInvoice(): void {
        $requestId = (int)($_GET['id'] ?? 0);

        $booking = ServiceRequest::find($requestId);
        if (!$booking) {
            die("Faktur / Invoice tidak ditemukan.");
        }

        $report = ServiceReport::findByRequestId($requestId);

        require __DIR__ . '/../views/admin/invoice_print.php';
    }

    public function profile(): void {
        $adminId = Auth::id();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $name = trim($_POST['name'] ?? '');
            $phone = trim($_POST['phone'] ?? '');
            $address = trim($_POST['address'] ?? '');
            $newPassword = trim($_POST['new_password'] ?? '');

            $updateData = [
                'name' => $name,
                'phone' => $phone,
                'address' => $address
            ];

            if (!empty($newPassword)) {
                if (strlen($newPassword) < 6) {
                    Helper::setFlash('error', 'Kata sandi baru minimal 6 karakter.');
                    header('Location: ' . BASE_URL . '/index.php?page=admin_profile');
                    exit;
                }
                $updateData['password'] = password_hash($newPassword, PASSWORD_DEFAULT);
            }

            User::update($adminId, $updateData);
            $_SESSION['user_name'] = $name;

            Helper::setFlash('success', 'Profil Administrator berhasil diperbarui.');
            header('Location: ' . BASE_URL . '/index.php?page=admin_profile');
            exit;
        }

        $user = Auth::user();
        require __DIR__ . '/../views/admin/profile.php';
    }
}
