<?php
// ==========================================================
// Model: ServiceReport (Laporan Pengerjaan & Invoice)
// ==========================================================

require_once __DIR__ . '/../core/Database.php';

class ServiceReport {
    public static function find(int $id): ?array {
        $db = Database::getInstance();
        return $db->fetch("
            SELECT rep.*, sr.request_code, sr.appliance_name, sr.brand, sr.model, sr.complaint,
                   u.name AS customer_name, u.phone AS customer_phone, u.address AS customer_address,
                   tu.name AS technician_name, tu.phone AS technician_phone, t.specialization
            FROM service_reports rep
            JOIN service_requests sr ON rep.request_id = sr.id
            JOIN users u ON sr.customer_id = u.id
            JOIN technicians t ON rep.technician_id = t.id
            JOIN users tu ON t.user_id = tu.id
            WHERE rep.id = ?
            LIMIT 1
        ", [$id]);
    }

    public static function findByRequestId(int $requestId): ?array {
        $db = Database::getInstance();
        return $db->fetch("
            SELECT rep.*, sr.request_code, sr.appliance_name, sr.brand, sr.model,
                   tu.name AS technician_name, tu.phone AS technician_phone
            FROM service_reports rep
            JOIN service_requests sr ON rep.request_id = sr.id
            JOIN technicians t ON rep.technician_id = t.id
            JOIN users tu ON t.user_id = tu.id
            WHERE rep.request_id = ?
            LIMIT 1
        ", [$requestId]);
    }

    public static function findByInvoice(string $invoiceNumber): ?array {
        $db = Database::getInstance();
        return $db->fetch("
            SELECT rep.*, sr.request_code, sr.appliance_name, sr.brand, sr.model,
                   u.name AS customer_name, u.phone AS customer_phone, u.address AS customer_address,
                   tu.name AS technician_name
            FROM service_reports rep
            JOIN service_requests sr ON rep.request_id = sr.id
            JOIN users u ON sr.customer_id = u.id
            JOIN technicians t ON rep.technician_id = t.id
            JOIN users tu ON t.user_id = tu.id
            WHERE rep.invoice_number = ?
            LIMIT 1
        ", [$invoiceNumber]);
    }

    public static function all(): array {
        $db = Database::getInstance();
        return $db->fetchAll("
            SELECT rep.*, sr.request_code, sr.appliance_name,
                   u.name AS customer_name, tu.name AS technician_name
            FROM service_reports rep
            JOIN service_requests sr ON rep.request_id = sr.id
            JOIN users u ON sr.customer_id = u.id
            JOIN technicians t ON rep.technician_id = t.id
            JOIN users tu ON t.user_id = tu.id
            ORDER BY rep.completed_at DESC
        ");
    }

    public static function create(array $data): int {
        $db = Database::getInstance();
        return $db->insert('service_reports', $data);
    }

    public static function update(int $id, array $data): int {
        $db = Database::getInstance();
        return $db->update('service_reports', $data, 'id = ?', [$id]);
    }

    public static function updatePaymentStatus(int $id, string $status): int {
        $db = Database::getInstance();
        return $db->update('service_reports', ['payment_status' => $status], 'id = ?', [$id]);
    }
}
