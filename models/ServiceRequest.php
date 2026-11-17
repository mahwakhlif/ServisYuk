<?php
// ==========================================================
// Model: ServiceRequest (Permintaan Jasa)
// ==========================================================

require_once __DIR__ . '/../core/Database.php';

class ServiceRequest {
    public static function all(array $filters = []): array {
        $db = Database::getInstance();
        $where = ["1=1"];
        $params = [];

        if (!empty($filters['status'])) {
            $where[] = "sr.status = ?";
            $params[] = $filters['status'];
        }

        if (!empty($filters['customer_id'])) {
            $where[] = "sr.customer_id = ?";
            $params[] = $filters['customer_id'];
        }

        if (!empty($filters['search'])) {
            $where[] = "(sr.request_code LIKE ? OR u.name LIKE ? OR sr.appliance_name LIKE ? OR sr.complaint LIKE ?)";
            $term = "%" . $filters['search'] . "%";
            $params[] = $term; $params[] = $term; $params[] = $term; $params[] = $term;
        }

        $whereSql = implode(' AND ', $where);

        return $db->fetchAll("
            SELECT sr.*, u.name AS customer_name, u.phone AS customer_phone, u.email AS customer_email,
                   s.service_name, s.estimated_price AS service_estimated_price,
                   t.id AS assigned_technician_id, tu.name AS assigned_technician_name, tu.phone AS assigned_technician_phone,
                   rep.total_amount, rep.payment_status, rep.invoice_number
            FROM service_requests sr
            JOIN users u ON sr.customer_id = u.id
            LEFT JOIN services s ON sr.service_id = s.id
            LEFT JOIN technician_assignments ta ON sr.id = ta.request_id AND ta.status IN ('assigned', 'accepted', 'in_progress', 'completed')
            LEFT JOIN technicians t ON ta.technician_id = t.id
            LEFT JOIN users tu ON t.user_id = tu.id
            LEFT JOIN service_reports rep ON sr.id = rep.request_id
            WHERE $whereSql
            ORDER BY sr.created_at DESC
        ", $params);
    }

    public static function find(int $id): ?array {
        $db = Database::getInstance();
        return $db->fetch("
            SELECT sr.*, u.name AS customer_name, u.phone AS customer_phone, u.email AS customer_email,
                   s.service_name, s.estimated_price AS service_estimated_price,
                   t.id AS assigned_technician_id, t.specialization AS technician_specialization,
                   tu.name AS assigned_technician_name, tu.phone AS assigned_technician_phone,
                   rep.id AS report_id, rep.inspection_result, rep.repair_action, rep.replaced_component,
                   rep.service_fee, rep.sparepart_fee, rep.total_amount, rep.payment_status, rep.invoice_number
            FROM service_requests sr
            JOIN users u ON sr.customer_id = u.id
            LEFT JOIN services s ON sr.service_id = s.id
            LEFT JOIN technician_assignments ta ON sr.id = ta.request_id
            LEFT JOIN technicians t ON ta.technician_id = t.id
            LEFT JOIN users tu ON t.user_id = tu.id
            LEFT JOIN service_reports rep ON sr.id = rep.request_id
            WHERE sr.id = ?
            ORDER BY ta.id DESC
            LIMIT 1
        ", [$id]);
    }

    public static function findByCode(string $code): ?array {
        $db = Database::getInstance();
        return $db->fetch("
            SELECT sr.*, u.name AS customer_name, u.phone AS customer_phone,
                   s.service_name,
                   t.id AS assigned_technician_id, tu.name AS assigned_technician_name, tu.phone AS assigned_technician_phone,
                   rep.total_amount, rep.payment_status, rep.invoice_number
            FROM service_requests sr
            JOIN users u ON sr.customer_id = u.id
            LEFT JOIN services s ON sr.service_id = s.id
            LEFT JOIN technician_assignments ta ON sr.id = ta.request_id
            LEFT JOIN technicians t ON ta.technician_id = t.id
            LEFT JOIN users tu ON t.user_id = tu.id
            LEFT JOIN service_reports rep ON sr.id = rep.request_id
            WHERE sr.request_code = ? OR u.phone = ?
            ORDER BY sr.created_at DESC
            LIMIT 1
        ", [$code, $code]);
    }

    public static function create(array $data): int {
        $db = Database::getInstance();
        return $db->insert('service_requests', $data);
    }

    public static function updateStatus(int $id, string $status): int {
        $db = Database::getInstance();
        return $db->update('service_requests', ['status' => $status], 'id = ?', [$id]);
    }
}
