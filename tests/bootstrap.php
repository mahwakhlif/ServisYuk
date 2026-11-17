<?php
// ==========================================================
// Tests Bootstrap: ServisYuk Test Environment Setup
// ==========================================================

define('BASEPATH', dirname(__DIR__));

// Set dummy testing environment variables (No real secrets needed!)
putenv('APP_ENV=testing');
putenv('APP_KEY=ServisYukTestingSecretKey2026!@#');
putenv('DB_DATABASE=db_jasa_perbaikan_test');
putenv('DB_USERNAME=root');

$_ENV['APP_ENV'] = 'testing';
$_ENV['APP_KEY'] = 'ServisYukTestingSecretKey2026!@#';
$_ENV['DB_DATABASE'] = 'db_jasa_perbaikan_test';
$_ENV['DB_USERNAME'] = 'root';

// Fallback base TestCase shim jika dijalankan mandiri tanpa vendor/phpunit
if (!class_exists('PHPUnit\Framework\TestCase')) {
    abstract class BaseTestCaseShim {
        public static function assertEquals($expected, $actual, string $message = ''): void {
            if ($expected !== $actual) {
                throw new \AssertionError($message ?: sprintf("Gagal: Nilai '%s' tidak sama dengan expected '%s'.", var_export($actual, true), var_export($expected, true)));
            }
        }

        public static function assertNotEquals($expected, $actual, string $message = ''): void {
            if ($expected === $actual) {
                throw new \AssertionError($message ?: sprintf("Gagal: Nilai '%s' seharusnya tidak sama dengan '%s'.", var_export($actual, true), var_export($expected, true)));
            }
        }

        public static function assertTrue($condition, string $message = ''): void {
            if ($condition !== true) {
                throw new \AssertionError($message ?: "Gagal: Kondisi bernilai false, diharapkan true.");
            }
        }

        public static function assertFalse($condition, string $message = ''): void {
            if ($condition !== false) {
                throw new \AssertionError($message ?: "Gagal: Kondisi bernilai true, diharapkan false.");
            }
        }

        public static function assertNull($actual, string $message = ''): void {
            if ($actual !== null) {
                throw new \AssertionError($message ?: sprintf("Gagal: Nilai '%s' tidak null.", var_export($actual, true)));
            }
        }

        public static function assertNotNull($actual, string $message = ''): void {
            if ($actual === null) {
                throw new \AssertionError($message ?: "Gagal: Nilai adalah null, diharapkan not null.");
            }
        }

        public static function assertIsArray($actual, string $message = ''): void {
            if (!is_array($actual)) {
                throw new \AssertionError($message ?: sprintf("Gagal: '%s' bukan merupakan array.", var_export($actual, true)));
            }
        }

        public static function assertArrayHasKey(string|int $key, array $array, string $message = ''): void {
            if (!array_key_exists($key, $array)) {
                throw new \AssertionError($message ?: "Gagal: Array tidak memiliki key '$key'.");
            }
        }

        public static function assertMatchesRegularExpression(string $pattern, string $string, string $message = ''): void {
            if (preg_match($pattern, $string) !== 1) {
                throw new \AssertionError($message ?: "Gagal: String '$string' tidak cocok dengan regex pattern '$pattern'.");
            }
        }

        public static function assertStringContainsString(string $needle, string $haystack, string $message = ''): void {
            if (!str_contains($haystack, $needle)) {
                throw new \AssertionError($message ?: "Gagal: String '$haystack' tidak mengandung substring '$needle'.");
            }
        }

        public static function assertStringStartsWith(string $prefix, string $string, string $message = ''): void {
            if (!str_starts_with($string, $prefix)) {
                throw new \AssertionError($message ?: "Gagal: String '$string' tidak diawali dengan prefix '$prefix'.");
            }
        }

        public static function assertStringEndsWith(string $suffix, string $string, string $message = ''): void {
            if (!str_ends_with($string, $suffix)) {
                throw new \AssertionError($message ?: "Gagal: String '$string' tidak diakhiri dengan suffix '$suffix'.");
            }
        }

        public static function assertNotEmpty($actual, string $message = ''): void {
            if (empty($actual)) {
                throw new \AssertionError($message ?: "Gagal: Nilai kosong.");
            }
        }
    }

    class_alias('BaseTestCaseShim', 'PHPUnit\Framework\TestCase');
}

// Load Core Libraries yang diuji
require_once BASEPATH . '/core/Env.php';
require_once BASEPATH . '/core/Helper.php';
require_once BASEPATH . '/core/FonnteService.php';
require_once BASEPATH . '/core/AiConfigService.php';
