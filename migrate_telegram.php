<?php
// ==========================================================
// Migration: Tambah kolom telegram_chat_id ke tabel users
// Jalankan sekali: php migrate_telegram.php
// ==========================================================

require_once __DIR__ . '/config/app.php';
require_once __DIR__ . '/core/Database.php';

$db = Database::getInstance();
$pdo = $db->getConnection();

echo "=== ServisYuk: Migrasi Kolom Telegram Chat ID ===\n\n";

// Cek apakah kolom sudah ada
$cols = $db->fetchAll("SHOW COLUMNS FROM users LIKE 'telegram_chat_id'");
if (!empty($cols)) {
    echo "✅ Kolom 'telegram_chat_id' sudah ada di tabel 'users'. Tidak perlu migrasi.\n";
} else {
    try {
        $pdo->exec("ALTER TABLE `users` ADD COLUMN `telegram_chat_id` VARCHAR(50) DEFAULT NULL AFTER `avatar`");
        echo "✅ Kolom 'telegram_chat_id' berhasil ditambahkan ke tabel 'users'.\n";
    } catch (PDOException $e) {
        echo "❌ Gagal menambahkan kolom: " . $e->getMessage() . "\n";
        exit(1);
    }
}

// Tampilkan struktur tabel users setelah migrasi
echo "\n=== Struktur Tabel Users (Setelah Migrasi) ===\n";
$allCols = $db->fetchAll("SHOW COLUMNS FROM users");
foreach ($allCols as $col) {
    echo sprintf("  %-22s %-25s %s\n", $col['Field'], $col['Type'], $col['Default'] ?? 'NULL');
}

echo "\nMigrasi selesai.\n";
