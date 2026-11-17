<?php
// ==========================================================
// Model: Service
// ==========================================================

require_once __DIR__ . '/../core/Database.php';

class Service {
    public static function all(bool $onlyActive = true): array {
        $db = Database::getInstance();
        $where = $onlyActive ? "WHERE status = 'active'" : "";
        return $db->fetchAll("SELECT * FROM services $where ORDER BY service_name ASC");
    }

    public static function find(int $id): ?array {
        $db = Database::getInstance();
        return $db->fetch("SELECT * FROM services WHERE id = ? LIMIT 1", [$id]);
    }

    public static function create(array $data): int {
        $db = Database::getInstance();
        return $db->insert('services', $data);
    }

    public static function update(int $id, array $data): int {
        $db = Database::getInstance();
        return $db->update('services', $data, 'id = ?', [$id]);
    }

    public static function delete(int $id): int {
        $db = Database::getInstance();
        return $db->delete('services', 'id = ?', [$id]);
    }
}
