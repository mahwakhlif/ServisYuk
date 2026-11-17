<?php
// ==========================================================
// Standalone Test Runner untuk ServisYuk
// Dapat dijalankan langsung dengan: php tests/run_tests.php
// Tanpa memerlukan Composer atau instalasi PHPUnit lokal.
// ==========================================================

require_once __DIR__ . '/bootstrap.php';

$testFiles = glob(__DIR__ . '/*Test.php');
$totalTests = 0;
$passedTests = 0;
$failedTests = 0;
$failures = [];
$startTime = microtime(true);

echo "=========================================================\n";
echo "   ServisYuk Automated Unit Test Suite\n";
echo "   PHP Version: " . PHP_VERSION . "\n";
echo "   Date: " . date('Y-m-d H:i:s') . "\n";
echo "=========================================================\n\n";

foreach ($testFiles as $filePath) {
    require_once $filePath;
    $className = basename($filePath, '.php');

    if (!class_exists($className)) {
        continue;
    }

    echo "Running Suite: {$className}\n";
    $testInstance = new $className();
    $reflection = new ReflectionClass($className);
    $methods = $reflection->getMethods(ReflectionMethod::IS_PUBLIC);

    foreach ($methods as $method) {
        $methodName = $method->getName();
        if (!str_starts_with($methodName, 'test')) {
            continue;
        }

        $totalTests++;

        try {
            if ($reflection->hasMethod('setUp')) {
                $testInstance->setUp();
            }

            $testInstance->{$methodName}();

            if ($reflection->hasMethod('tearDown')) {
                $testInstance->tearDown();
            }

            $passedTests++;
            echo "  [PASS] {$methodName}\n";
        } catch (\Throwable $e) {
            $failedTests++;
            $errorInfo = [
                'suite'   => $className,
                'test'    => $methodName,
                'message' => $e->getMessage(),
                'file'    => $e->getFile(),
                'line'    => $e->getLine()
            ];
            $failures[] = $errorInfo;
            echo "  [FAIL] {$methodName}\n";
        }
    }
    echo "\n";
}

$duration = round((microtime(true) - $startTime) * 1000, 2);

echo "=========================================================\n";
echo "Ringkasan Hasil Pengujian (Test Summary):\n";
echo "  Total Pengujian : {$totalTests}\n";
echo "  Berhasil (PASS) : {$passedTests}\n";
echo "  Gagal (FAIL)    : {$failedTests}\n";
echo "  Durasi Eksekusi : {$duration} ms\n";
echo "=========================================================\n";

if ($failedTests > 0) {
    echo "\nDetail Kegagalan:\n";
    foreach ($failures as $f) {
        echo "- [{$f['suite']}::{$f['test']}] {$f['message']}\n";
        echo "  Lokasi: {$f['file']}:{$f['line']}\n";
    }
    echo "\nHASIL: GAGAL (Ada tes yang tidak memenuhi syarat)\n";
    exit(1);
} else {
    echo "\nHASIL: SUKSES (Semua unit test berhasil 100%)\n";
    exit(0);
}
