<?php
// ==========================================================
// Model: Technician (1:1 users)
// ==========================================================

require_once __DIR__ . '/../core/Database.php';

class Technician {
    public static function all(): array {
        $db = Database::getInstance();
        return $db->fetchAll("
            SELECT t.*, u.name, u.email, u.phone, u.address, u.status AS user_status, u.avatar
            FROM technicians t
            JOIN users u ON t.user_id = u.id
            ORDER BY u.name ASC
        ");
    }

    public static function find(int $id): ?array {
        $db = Database::getInstance();
        return $db->fetch("
            SELECT t.*, u.name, u.email, u.phone, u.address, u.status AS user_status, u.avatar
            FROM technicians t
            JOIN users u ON t.user_id = u.id
            WHERE t.id = ?
            LIMIT 1
        ", [$id]);
    }

    public static function findByUserId(int $userId): ?array {
        $db = Database::getInstance();
        return $db->fetch("
            SELECT t.*, u.name, u.email, u.phone, u.address, u.status AS user_status, u.avatar
            FROM technicians t
            JOIN users u ON t.user_id = u.id
            WHERE t.user_id = ?
            LIMIT 1
        ", [$userId]);
    }

    public static function getAvailable(): array {
        $db = Database::getInstance();
        return $db->fetchAll("
            SELECT t.*, u.name, u.phone
            FROM technicians t
            JOIN users u ON t.user_id = u.id
            WHERE t.availability = 'available' AND u.status = 'active'
            ORDER BY u.name ASC
        ");
    }

    public static function create(array $data): int {
        $db = Database::getInstance();
        return $db->insert('technicians', $data);
    }

    public static function update(int $id, array $data): int {
        $db = Database::getInstance();
        return $db->update('technicians', $data, 'id = ?', [$id]);
    }
}
