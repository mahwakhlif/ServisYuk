<?php
// ==========================================================
// Core: Authentication & Session Management
// ==========================================================

require_once __DIR__ . '/Database.php';

class Auth {
    public static function attempt(string $email, string $password): array {
        $db = Database::getInstance();
        $user = $db->fetch("SELECT * FROM users WHERE email = ? AND (status = 'active' OR status IS NULL) LIMIT 1", [$email]);

        if (!$user) {
            return ['success' => false, 'message' => 'Email tidak terdaftar atau akun dinonaktifkan.'];
        }

        if (!password_verify($password, $user['password'])) {
            return ['success' => false, 'message' => 'Kata sandi yang Anda masukkan salah.'];
        }

        // Regenerate session id safely for security
        if (session_status() === PHP_SESSION_ACTIVE && !headers_sent()) {
            session_regenerate_id(true);
        }

        // Store user in session
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_name'] = $user['name'];
        $_SESSION['user_email'] = $user['email'];
        $_SESSION['user_role'] = $user['role'];
        $_SESSION['user_avatar'] = $user['avatar'] ?? 'default_avatar.png';

        return [
            'success' => true,
            'user' => $user,
            'redirect' => self::getRedirectPathByRole($user['role'])
        ];
    }

    public static function register(array $data): array {
        $db = Database::getInstance();

        // Check if email already exists
        $existing = $db->fetch("SELECT id FROM users WHERE email = ? LIMIT 1", [$data['email']]);
        if ($existing) {
            return ['success' => false, 'message' => 'Alamat email sudah digunakan. Silakan gunakan email lain.'];
        }

        $passwordHash = password_hash($data['password'], PASSWORD_DEFAULT);
        $role = in_array($data['role'] ?? '', ['admin', 'technician', 'customer']) ? $data['role'] : 'customer';

        $userId = $db->insert('users', [
            'name' => trim($data['name']),
            'email' => trim($data['email']),
            'password' => $passwordHash,
            'role' => $role,
            'phone' => trim($data['phone'] ?? ''),
            'address' => trim($data['address'] ?? ''),
            'status' => 'active',
            'avatar' => 'default_avatar.png'
        ]);

        // Auto login securely
        if (session_status() === PHP_SESSION_ACTIVE && !headers_sent()) {
            session_regenerate_id(true);
        }
        $_SESSION['user_id'] = $userId;
        $_SESSION['user_name'] = $data['name'];
        $_SESSION['user_email'] = $data['email'];
        $_SESSION['user_role'] = $role;
        $_SESSION['user_avatar'] = 'default_avatar.png';

        return [
            'success' => true,
            'user_id' => $userId,
            'redirect' => self::getRedirectPathByRole($role)
        ];
    }

    public static function logout(): void {
        $_SESSION = [];
        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000,
                $params["path"], $params["domain"],
                $params["secure"], $params["httponly"]
            );
        }
        session_destroy();
    }

    public static function check(): bool {
        return isset($_SESSION['user_id']);
    }

    public static function user(): ?array {
        if (!self::check()) {
            return null;
        }
        $db = Database::getInstance();
        return $db->fetch("SELECT * FROM users WHERE id = ? LIMIT 1", [$_SESSION['user_id']]);
    }

    public static function id(): ?int {
        return $_SESSION['user_id'] ?? null;
    }

    public static function role(): ?string {
        return $_SESSION['user_role'] ?? null;
    }

    public static function name(): string {
        return $_SESSION['user_name'] ?? 'Tamu';
    }

    public static function isAdmin(): bool {
        return self::role() === 'admin';
    }

    public static function isTechnician(): bool {
        return self::role() === 'technician';
    }

    public static function isCustomer(): bool {
        return self::role() === 'customer';
    }

    public static function requireAuth(): void {
        if (!self::check()) {
            header('Location: ' . BASE_URL . '/index.php?page=login&notice=login_required');
            exit;
        }
    }

    public static function requireRole(array|string $roles): void {
        self::requireAuth();
        $allowed = is_array($roles) ? $roles : [$roles];
        if (!in_array(self::role(), $allowed)) {
            header('Location: ' . BASE_URL . '/index.php?page=unauthorized');
            exit;
        }
    }

    public static function getRedirectPathByRole(string $role): string {
        switch ($role) {
            case 'admin':
                return BASE_URL . '/index.php?page=admin_dashboard';
            case 'technician':
                return BASE_URL . '/index.php?page=tech_dashboard';
            case 'customer':
            default:
                return BASE_URL . '/index.php?page=customer_dashboard';
        }
    }
}
