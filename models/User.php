<?php
// ==========================================================
// Model: User
// ==========================================================

require_once __DIR__ . '/../core/Database.php';

class User {
    public static function find(int $id): ?array {
        $db = Database::getInstance();
        return $db->fetch("SELECT * FROM users WHERE id = ? LIMIT 1", [$id]);
    }

    public static function findByEmail(string $email): ?array {
        $db = Database::getInstance();
        return $db->fetch("SELECT * FROM users WHERE email = ? LIMIT 1", [$email]);
    }

    public static function getByRole(string $role): array {
        $db = Database::getInstance();
        return $db->fetchAll("SELECT * FROM users WHERE role = ? ORDER BY created_at DESC", [$role]);
    }

    public static function create(array $data): int {
        $db = Database::getInstance();
        return $db->insert('users', $data);
    }

    public static function update(int $id, array $data): int {
        $db = Database::getInstance();
        return $db->update('users', $data, 'id = ?', [$id]);
    }

    public static function delete(int $id): int {
        $db = Database::getInstance();
        return $db->delete('users', 'id = ?', [$id]);
    }
}
