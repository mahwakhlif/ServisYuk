<?php
// ==========================================================
// Controller: TechnicianController
// ==========================================================

require_once __DIR__ . '/../core/Auth.php';
require_once __DIR__ . '/../core/Helper.php';
require_once __DIR__ . '/../core/Database.php';
require_once __DIR__ . '/../models/User.php';
require_once __DIR__ . '/../models/Technician.php';
require_once __DIR__ . '/../models/ServiceRequest.php';
require_once __DIR__ . '/../models/TechnicianAssignment.php';
require_once __DIR__ . '/../models/Diagnosis.php';
require_once __DIR__ . '/../models/ServiceReport.php';
require_once __DIR__ . '/../core/FonnteService.php';

class TechnicianController {
    public function __construct() {
        Auth::requireRole('technician');
    }

    private function getTechProfile(): ?array {
        $userId = Auth::id();
        return Technician::findByUserId($userId);
    }

    public function dashboard(): void {
        $db = Database::getInstance();
        $tech = $this->getTechProfile();
        $techId = $tech['id'] ?? 0;

        // Technician Stats
        $stats = [
            'active_jobs' => $db->fetch("
                SELECT COUNT(*) AS cnt 
                FROM technician_assignments ta
                JOIN service_requests sr ON ta.request_id = sr.id
                WHERE ta.technician_id = ? AND sr.status NOT IN ('Selesai', 'Dibatalkan')
            ", [$techId])['cnt'] ?? 0,
            'today_jobs' => $db->fetch("
                SELECT COUNT(*) AS cnt 
                FROM technician_assignments ta
                JOIN service_requests sr ON ta.request_id = sr.id
                WHERE ta.technician_id = ? AND DATE(sr.schedule) = CURDATE()
            ", [$techId])['cnt'] ?? 0,
            'completed_jobs' => $db->fetch("
                SELECT COUNT(*) AS cnt 
                FROM service_reports 
                WHERE technician_id = ?
            ", [$techId])['cnt'] ?? 0,
        ];

        // Active Tasks Assigned to this technician
        $activeJobs = $db->fetchAll("
            SELECT sr.*, s.service_name,
                   u.name AS customer_name, u.phone AS customer_phone, u.address AS customer_address,
                   ta.id AS assignment_id, ta.status AS assignment_status
            FROM technician_assignments ta
            JOIN service_requests sr ON ta.request_id = sr.id
            JOIN users u ON sr.customer_id = u.id
            LEFT JOIN services s ON sr.service_id = s.id
            WHERE ta.technician_id = ? AND sr.status NOT IN ('Selesai', 'Dibatalkan')
            ORDER BY sr.schedule ASC
        ", [$techId]);

        // Recent Completed Jobs
        $recentCompleted = $db->fetchAll("
            SELECT sr.*, s.service_name,
                   u.name AS customer_name,
                   rep.total_amount, rep.payment_status, rep.completed_at
            FROM service_reports rep
            JOIN service_requests sr ON rep.request_id = sr.id
            JOIN users u ON sr.customer_id = u.id
            LEFT JOIN services s ON sr.service_id = s.id
            WHERE rep.technician_id = ?
            ORDER BY rep.completed_at DESC
            LIMIT 5
        ", [$techId]);

        require __DIR__ . '/../views/technician/dashboard.php';
    }

    public function jobDetail(): void {
        $tech = $this->getTechProfile();
        $techId = $tech['id'] ?? 0;
        $requestId = (int)($_GET['id'] ?? 0);

        $booking = ServiceRequest::find($requestId);

        if (!$booking) {
            Helper::setFlash('error', 'Pekerjaan tidak ditemukan.');
            header('Location: ' . BASE_URL . '/index.php?page=tech_dashboard');
            exit;
        }

        $diagnosis = Diagnosis::findByRequestId($requestId);
        $report = ServiceReport::findByRequestId($requestId);

        require __DIR__ . '/../views/technician/job_detail.php';
    }

    public function updateStatus(): void {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . BASE_URL . '/index.php?page=tech_dashboard');
            exit;
        }

        $tech = $this->getTechProfile();
        $techId = $tech['id'] ?? 0;
        $requestId = (int)($_POST['booking_id'] ?? ($_POST['request_id'] ?? 0));
        $newStatus = trim($_POST['status'] ?? '');
        $notes = trim($_POST['notes'] ?? '');

        if (!$requestId || empty($newStatus)) {
            Helper::setFlash('error', 'Data status tidak valid.');
            header('Location: ' . BASE_URL . '/index.php?page=tech_dashboard');
            exit;
        }

        // Handle Completed status & Service Report submission
        if ($newStatus === 'Selesai' || $newStatus === 'completed' || isset($_POST['submit_report'])) {
            $inspectionResult = trim($_POST['inspection_result'] ?? 'Pemeriksaan fisik unit dan pengujian fungsional.');
            $repairAction = trim($_POST['repair_action'] ?? 'Perbaikan komponen dan pengetesan operasional unit.');
            $replacedComponent = trim($_POST['replaced_component'] ?? '');
            $serviceFee = floatval($_POST['service_fee'] ?? 75000);
            $sparepartFee = floatval($_POST['sparepart_fee'] ?? 0);
            $totalAmount = $serviceFee + $sparepartFee;
            $paymentStatus = ($_POST['payment_status'] ?? 'paid') === 'unpaid' ? 'unpaid' : 'paid';
            $invoiceNumber = Helper::generateInvoiceNumber();

            // Check if report already exists
            $existingReport = ServiceReport::findByRequestId($requestId);
            if ($existingReport) {
                ServiceReport::update($existingReport['id'], [
                    'inspection_result' => $inspectionResult,
                    'repair_action' => $repairAction,
                    'replaced_component' => $replacedComponent,
                    'service_fee' => $serviceFee,
                    'sparepart_fee' => $sparepartFee,
                    'total_amount' => $totalAmount,
                    'payment_status' => $paymentStatus,
                    'notes' => $notes
                ]);
            } else {
                ServiceReport::create([
                    'request_id' => $requestId,
                    'technician_id' => $techId,
                    'inspection_result' => $inspectionResult,
                    'repair_action' => $repairAction,
                    'replaced_component' => $replacedComponent,
                    'service_fee' => $serviceFee,
                    'sparepart_fee' => $sparepartFee,
                    'total_amount' => $totalAmount,
                    'payment_status' => $paymentStatus,
                    'invoice_number' => $invoiceNumber,
                    'notes' => $notes
                ]);
            }

            // Update physical finding to diagnoses table if present
            $diagnosis = Diagnosis::findByRequestId($requestId);
            if ($diagnosis) {
                Diagnosis::updateTechnicianResult($diagnosis['id'], $inspectionResult);
            }

            $currentBooking = ServiceRequest::find($requestId);
            $previousStatus = $currentBooking['status'] ?? '';

            ServiceRequest::updateStatus($requestId, 'Selesai');

            if ($previousStatus !== 'Selesai') {
                try {
                    FonnteService::notifyStatusUpdate($requestId, 'Selesai', $notes ?: null);
                } catch (\Throwable $e) {
                    error_log("[TechnicianController] WhatsApp notify Selesai error: " . $e->getMessage());
                }
            }

            Helper::setFlash('success', "Pekerjaan berhasil diselesaikan! Laporan perbaikan dan Faktur ($invoiceNumber) telah diterbitkan.");
        } else {
            // Update to intermediary statuses: 'Dalam Perjalanan', 'Pemeriksaan', 'Dalam Perbaikan'
            $currentBooking = ServiceRequest::find($requestId);
            $previousStatus = $currentBooking['status'] ?? '';

            if ($previousStatus !== $newStatus) {
                ServiceRequest::updateStatus($requestId, $newStatus);
                try {
                    FonnteService::notifyStatusUpdate($requestId, $newStatus, $notes ?: null);
                } catch (\Throwable $e) {
                    error_log("[TechnicianController] WhatsApp notify {$newStatus} error: " . $e->getMessage());
                }
                Helper::setFlash('success', "Status pekerjaan berhasil diperbarui menjadi '$newStatus'.");
            } else {
                Helper::setFlash('info', "Status pekerjaan saat ini sudah '$newStatus'.");
            }
        }

        header('Location: ' . BASE_URL . '/index.php?page=tech_job_detail&id=' . $requestId);
        exit;
    }

    public function jobHistory(): void {
        $tech = $this->getTechProfile();
        $techId = $tech['id'] ?? 0;

        $db = Database::getInstance();
        $jobs = $db->fetchAll("
            SELECT sr.*, s.service_name,
                   u.name AS customer_name, u.phone AS customer_phone,
                   rep.total_amount, rep.payment_status, rep.invoice_number, rep.completed_at
            FROM service_reports rep
            JOIN service_requests sr ON rep.request_id = sr.id
            JOIN users u ON sr.customer_id = u.id
            LEFT JOIN services s ON sr.service_id = s.id
            WHERE rep.technician_id = ?
            ORDER BY rep.completed_at DESC
        ", [$techId]);

        require __DIR__ . '/../views/technician/job_history.php';
    }

    public function profile(): void {
        $tech = $this->getTechProfile();
        $userId = Auth::id();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $name = trim($_POST['name'] ?? '');
            $phone = trim($_POST['phone'] ?? '');
            $specialization = trim($_POST['specialization'] ?? '');
            $experience = trim($_POST['experience'] ?? '3 Tahun');
            $address = trim($_POST['address'] ?? '');
            $newPassword = trim($_POST['new_password'] ?? '');

            $updateUserData = [
                'name' => $name,
                'phone' => $phone,
                'address' => $address
            ];

            if (!empty($newPassword)) {
                if (strlen($newPassword) < 6) {
                    Helper::setFlash('error', 'Kata sandi baru minimal 6 karakter.');
                    header('Location: ' . BASE_URL . '/index.php?page=tech_profile');
                    exit;
                }
                $updateUserData['password'] = password_hash($newPassword, PASSWORD_DEFAULT);
            }

            User::update($userId, $updateUserData);

            if ($tech) {
                Technician::update($tech['id'], [
                    'specialization' => $specialization,
                    'experience' => $experience
                ]);
            }

            $_SESSION['user_name'] = $name;

            Helper::setFlash('success', 'Profil teknisi berhasil diperbarui.');
            header('Location: ' . BASE_URL . '/index.php?page=tech_profile');
            exit;
        }

        $user = Auth::user();
        require __DIR__ . '/../views/technician/profile.php';
    }
}
