<?php
// ==========================================================
// Model: Diagnosis (Diagnosa AI & Teknisi)
// ==========================================================

require_once __DIR__ . '/../core/Database.php';

class Diagnosis {
    public static function find(int $id): ?array {
        $db = Database::getInstance();
        return $db->fetch("SELECT * FROM diagnoses WHERE id = ? LIMIT 1", [$id]);
    }

    public static function findByRequestId(int $requestId): ?array {
        $db = Database::getInstance();
        return $db->fetch("
            SELECT d.*, sr.request_code, sr.appliance_name, sr.brand, sr.model
            FROM diagnoses d
            LEFT JOIN service_requests sr ON d.request_id = sr.id
            WHERE d.request_id = ?
            ORDER BY d.created_at DESC
            LIMIT 1
        ", [$requestId]);
    }

    public static function getByRequest(int $requestId): array {
        $db = Database::getInstance();
        return $db->fetchAll("
            SELECT * FROM diagnoses 
            WHERE request_id = ? 
            ORDER BY created_at DESC
        ", [$requestId]);
    }

    public static function all(): array {
        $db = Database::getInstance();
        return $db->fetchAll("
            SELECT d.*, sr.request_code, sr.appliance_name, u.name AS customer_name
            FROM diagnoses d
            LEFT JOIN service_requests sr ON d.request_id = sr.id
            LEFT JOIN users u ON sr.customer_id = u.id
            ORDER BY d.created_at DESC
        ");
    }

    public static function create(array $data): int {
        $db = Database::getInstance();
        return $db->insert('diagnoses', $data);
    }

    public static function update(int $id, array $data): int {
        $db = Database::getInstance();
        return $db->update('diagnoses', $data, 'id = ?', [$id]);
    }

    public static function updateTechnicianResult(int $id, string $result): int {
        $db = Database::getInstance();
        return $db->update('diagnoses', [
            'technician_result' => $result
        ], 'id = ?', [$id]);
    }
}
