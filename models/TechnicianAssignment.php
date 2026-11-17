<?php
// ==========================================================
// Model: TechnicianAssignment (Penugasan Teknisi)
// ==========================================================

require_once __DIR__ . '/../core/Database.php';
require_once __DIR__ . '/../core/TelegramService.php';
require_once __DIR__ . '/../core/FonnteService.php';

class TechnicianAssignment {
    public static function getByTechnician(int $technicianId, ?string $status = null): array {
        $db = Database::getInstance();
        $where = ["ta.technician_id = ?"];
        $params = [$technicianId];

        if ($status !== null) {
            $where[] = "ta.status = ?";
            $params[] = $status;
        }

        $whereSql = implode(' AND ', $where);

        return $db->fetchAll("
            SELECT ta.*, sr.request_code, sr.appliance_name, sr.brand, sr.model, sr.complaint,
                   sr.photo, sr.address, sr.schedule, sr.status AS request_status,
                   u.name AS customer_name, u.phone AS customer_phone,
                   rep.total_amount, rep.payment_status
            FROM technician_assignments ta
            JOIN service_requests sr ON ta.request_id = sr.id
            JOIN users u ON sr.customer_id = u.id
            LEFT JOIN service_reports rep ON sr.id = rep.request_id
            WHERE $whereSql
            ORDER BY sr.schedule ASC
        ", $params);
    }

    public static function assign(int $requestId, int $technicianId, ?string $notes = null): int {
        $db = Database::getInstance();

        // Update request status to 'Teknisi Ditugaskan'
        $db->update('service_requests', ['status' => 'Teknisi Ditugaskan'], 'id = ?', [$requestId]);

        // Insert or update assignment
        $assignmentId = $db->insert('technician_assignments', [
            'request_id'   => $requestId,
            'technician_id' => $technicianId,
            'status'       => 'assigned',
            'notes'        => $notes
        ]);

        // ============================================================
        // TRIGGER 1: Kirim notifikasi WhatsApp via Fonnte API
        // ============================================================
        try {
            FonnteService::notifyStatusUpdate($requestId, 'Teknisi Ditugaskan', $notes);
        } catch (\Throwable $e) {
            error_log("[TechnicianAssignment] WhatsApp notification error: " . $e->getMessage());
        }

        // ============================================================
        // TRIGGER 2 (Legacy): Kirim notifikasi Telegram ke customer jika ada chat_id
        // ============================================================
        try {
            // Ambil data tiket servis + customer + teknisi sekaligus
            $ticketData = $db->fetch("
                SELECT sr.request_code, sr.customer_id, sr.schedule,
                       u.name AS customer_name,
                       tu.name AS technician_name
                FROM service_requests sr
                JOIN users u ON sr.customer_id = u.id
                JOIN technicians t ON t.id = ?
                JOIN users tu ON t.user_id = tu.id
                WHERE sr.id = ?
                LIMIT 1
            ", [$technicianId, $requestId]);

            if ($ticketData) {
                $customerChatId = TelegramService::getCustomerChatId((int)$ticketData['customer_id']);
                if (!empty($customerChatId)) {
                    $jadwalFormatted = null;
                    if (!empty($ticketData['schedule'])) {
                        $jadwalFormatted = date('d M Y, H:i', strtotime($ticketData['schedule'])) . ' WIB';
                    }

                    TelegramService::notifyTechnicianAssigned(
                        (int)$ticketData['customer_id'],
                        $ticketData['request_code'],
                        $ticketData['technician_name'],
                        'Teknisi Ditugaskan',
                        $jadwalFormatted
                    );
                }
            }
        } catch (\Throwable $e) {
            error_log("[TechnicianAssignment] Telegram notification fallback error: " . $e->getMessage());
        }

        return $assignmentId;
    }

    public static function updateStatus(int $assignmentId, string $status): int {
        $db = Database::getInstance();
        return $db->update('technician_assignments', ['status' => $status], 'id = ?', [$assignmentId]);
    }
}

