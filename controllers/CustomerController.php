<?php
// ==========================================================
// Controller: CustomerController
// ==========================================================

require_once __DIR__ . '/../core/Auth.php';
require_once __DIR__ . '/../core/Helper.php';
require_once __DIR__ . '/../core/Database.php';
require_once __DIR__ . '/../models/ServiceRequest.php';
require_once __DIR__ . '/../models/Service.php';
require_once __DIR__ . '/../models/Diagnosis.php';
require_once __DIR__ . '/../models/ServiceReport.php';
require_once __DIR__ . '/../models/User.php';
require_once __DIR__ . '/../core/FonnteService.php';

class CustomerController {
    public function __construct() {
        Auth::requireRole('customer');
    }

    public function dashboard(): void {
        $db = Database::getInstance();
        $userId = Auth::id();

        // Customer stats
        $stats = [
            'total_bookings' => $db->fetch("SELECT COUNT(*) AS cnt FROM service_requests WHERE customer_id = ?", [$userId])['cnt'] ?? 0,
            'active_bookings' => $db->fetch("SELECT COUNT(*) AS cnt FROM service_requests WHERE customer_id = ? AND status NOT IN ('Selesai', 'Dibatalkan')", [$userId])['cnt'] ?? 0,
            'completed_bookings' => $db->fetch("SELECT COUNT(*) AS cnt FROM service_requests WHERE customer_id = ? AND status = 'Selesai'", [$userId])['cnt'] ?? 0,
        ];

        // Active service requests
        $activeBookings = $db->fetchAll("
            SELECT sr.*, s.service_name, s.estimated_price AS service_estimated_price,
                   tu.name AS technician_name, tu.phone AS technician_phone, tu.avatar AS technician_avatar
            FROM service_requests sr
            LEFT JOIN services s ON sr.service_id = s.id
            LEFT JOIN technician_assignments ta ON sr.id = ta.request_id AND ta.status IN ('assigned', 'accepted', 'in_progress', 'completed')
            LEFT JOIN technicians t ON ta.technician_id = t.id
            LEFT JOIN users tu ON t.user_id = tu.id
            WHERE sr.customer_id = ? AND sr.status NOT IN ('Selesai', 'Dibatalkan')
            ORDER BY sr.created_at DESC
        ", [$userId]);

        // Recent requests
        $recentBookings = $db->fetchAll("
            SELECT sr.*, s.service_name,
                   rep.total_amount, rep.payment_status, rep.invoice_number
            FROM service_requests sr
            LEFT JOIN services s ON sr.service_id = s.id
            LEFT JOIN service_reports rep ON sr.id = rep.request_id
            WHERE sr.customer_id = ?
            ORDER BY sr.created_at DESC
            LIMIT 5
        ", [$userId]);

        $services = Service::all();

        require __DIR__ . '/../views/customer/dashboard.php';
    }

    public function createBooking(): void {
        $db = Database::getInstance();
        $userId = Auth::id();
        $user = Auth::user();

        $services = Service::all();

        // Check if prefilled from AI diagnosis
        $prefill = [
            'service_id' => $_GET['service_id'] ?? ($_SESSION['ai_prefill']['service_id'] ?? ''),
            'appliance_name' => $_GET['appliance_name'] ?? ($_SESSION['ai_prefill']['appliance_name'] ?? ''),
            'brand' => $_GET['brand'] ?? ($_SESSION['ai_prefill']['brand'] ?? ''),
            'model' => $_GET['model'] ?? ($_SESSION['ai_prefill']['model'] ?? ''),
            'complaint' => $_GET['complaint'] ?? ($_SESSION['ai_prefill']['complaint'] ?? ''),
            'ai_diagnosis_summary' => $_SESSION['ai_prefill']['ai_diagnosis_summary'] ?? '',
            'initial_cost_estimate' => $_SESSION['ai_prefill']['initial_cost_estimate'] ?? '',
            'address' => $user['address'] ?? '',
            'phone' => $user['phone'] ?? ''
        ];

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $serviceId = !empty($_POST['service_id']) ? (int)$_POST['service_id'] : null;
            $applianceName = trim($_POST['appliance_name'] ?? '');
            $brand = trim($_POST['brand'] ?? '');
            $model = trim($_POST['model'] ?? '');
            $complaint = trim($_POST['complaint'] ?? '');
            $address = trim($_POST['address'] ?? '');
            $scheduleDate = trim($_POST['schedule_date'] ?? '');
            $scheduleTime = trim($_POST['schedule_time'] ?? '');
            $aiDiagnosisSummary = trim($_POST['ai_diagnosis_summary'] ?? '');

            if (empty($applianceName) || empty($complaint) || empty($scheduleDate) || empty($address)) {
                Helper::setFlash('error', 'Harap lengkapi semua kolom wajib (Peralatan, Keluhan, Alamat, dan Jadwal).');
                require __DIR__ . '/../views/customer/booking_create.php';
                return;
            }

            $schedule = $scheduleDate . ' ' . (!empty($scheduleTime) ? $scheduleTime : '09:00:00');

            // Photo upload handling
            $photoUrl = null;
            if (!empty($_FILES['photo']['name'])) {
                $uploadResult = Helper::uploadFile($_FILES['photo'], 'bookings');
                if ($uploadResult['success']) {
                    $photoUrl = $uploadResult['filename'];
                } else {
                    Helper::setFlash('warning', $uploadResult['message'] . ' Permintaan tetap diproses tanpa foto.');
                }
            }

            $requestCode = Helper::generateBookingCode();

            $requestId = ServiceRequest::create([
                'request_code' => $requestCode,
                'customer_id' => $userId,
                'service_id' => $serviceId,
                'appliance_name' => $applianceName,
                'brand' => $brand,
                'model' => $model,
                'complaint' => $complaint,
                'photo' => $photoUrl,
                'address' => $address,
                'schedule' => $schedule,
                'status' => 'Menunggu'
            ]);

            // If AI diagnosis summary exists, save to diagnoses table
            if (!empty($aiDiagnosisSummary)) {
                Diagnosis::create([
                    'request_id' => $requestId,
                    'complaint' => $complaint,
                    'ai_result' => $aiDiagnosisSummary,
                    'technician_result' => null
                ]);
            }

            // Clear AI session prefill
            unset($_SESSION['ai_prefill']);

            // ========================================================
            // Trigger: Notifikasi WhatsApp 'Menunggu' ke Pelanggan
            // ========================================================
            try {
                FonnteService::notifyStatusUpdate($requestId, 'Menunggu');
            } catch (\Throwable $e) {
                error_log("[CustomerController] WhatsApp booking notification error: " . $e->getMessage());
            }

            Helper::setFlash('success', "Permintaan servis berhasil diajukan! Kode Tiket: $requestCode");
            header('Location: ' . BASE_URL . '/index.php?page=customer_booking_detail&id=' . $requestId);
            exit;
        }

        require __DIR__ . '/../views/customer/booking_create.php';
    }

    public function viewBooking(): void {
        $userId = Auth::id();
        $requestId = (int)($_GET['id'] ?? 0);

        $booking = ServiceRequest::find($requestId);

        if (!$booking || $booking['customer_id'] != $userId) {
            Helper::setFlash('error', 'Permintaan jasa tidak ditemukan atau Anda tidak memiliki akses.');
            header('Location: ' . BASE_URL . '/index.php?page=customer_history');
            exit;
        }

        $diagnosis = Diagnosis::findByRequestId($requestId);
        $report = ServiceReport::findByRequestId($requestId);

        require __DIR__ . '/../views/customer/booking_detail.php';
    }

    public function bookingHistory(): void {
        $userId = Auth::id();
        $statusFilter = $_GET['status'] ?? '';

        $filters = ['customer_id' => $userId];
        if (!empty($statusFilter)) {
            $filters['status'] = $statusFilter;
        }

        $bookings = ServiceRequest::all($filters);

        require __DIR__ . '/../views/customer/booking_history.php';
    }

    public function submitReview(): void {
        Helper::setFlash('info', 'Ulasan perbaikan telah tercatat.');
        header('Location: ' . BASE_URL . '/index.php?page=customer_dashboard');
        exit;
    }

    public function profile(): void {
        $userId = Auth::id();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $name = trim($_POST['name'] ?? '');
            $phone = trim($_POST['phone'] ?? '');
            $address = trim($_POST['address'] ?? '');
            $telegramChatId = trim($_POST['telegram_chat_id'] ?? '');
            $newPassword = trim($_POST['new_password'] ?? '');

            $updateData = [
                'name' => $name,
                'phone' => $phone,
                'address' => $address,
                'telegram_chat_id' => $telegramChatId ?: null
            ];

            if (!empty($newPassword)) {
                if (strlen($newPassword) < 6) {
                    Helper::setFlash('error', 'Kata sandi baru minimal 6 karakter.');
                    header('Location: ' . BASE_URL . '/index.php?page=customer_profile');
                    exit;
                }
                $updateData['password'] = password_hash($newPassword, PASSWORD_DEFAULT);
            }

            User::update($userId, $updateData);
            $_SESSION['user_name'] = $name;

            Helper::setFlash('success', 'Profil Anda berhasil diperbarui.');
            header('Location: ' . BASE_URL . '/index.php?page=customer_profile');
            exit;
        }

        $user = Auth::user();
        require __DIR__ . '/../views/customer/profile.php';
    }
}
