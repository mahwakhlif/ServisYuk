<?php
// ==========================================================
// ServisYuk: Alat Uji Validitas Token & Koneksi WhatsApp Fonnte
// Jalankan via CLI: php check_fonnte.php
// ==========================================================

require_once __DIR__ . '/core/Env.php';
require_once __DIR__ . '/core/FonnteService.php';

echo "\n";
echo "========================================================\n";
echo "   PEMERIKSAAN KONFIGURASI WHATSAPP FONNTE SERVISYUK   \n";
echo "========================================================\n\n";

// 1. Periksa pembacaan environment
$token = (string)Env::get('FONNTE_TOKEN', '');
$tokenLength = strlen(trim($token));

echo "[1] Status File .env & Konfigurasi:\n";
if ($tokenLength === 0) {
    echo "    ❌ FONNTE_TOKEN saat ini MASIH KOSONG.\n";
    echo "    👉 Silakan buka file '.env' di baris 27.\n";
    echo "       Isi dengan: FONNTE_TOKEN=token_fonnte_anda_di_sini\n";
    echo "       Lalu simpan file dan jalankan script ini kembali.\n\n";
    echo "========================================================\n";
    exit(0);
}

// Token terbaca, tampilkan indikator aman tanpa membocorkan token asli
$masked = substr($token, 0, 3) . str_repeat('*', max(4, $tokenLength - 6)) . substr($token, -3);
echo "    ✅ FONNTE_TOKEN berhasil terbaca oleh sistem.\n";
echo "    🔒 Nilai token aman (Tersamarkan): {$masked} ({$tokenLength} karakter)\n\n";

// 2. Uji permintaan API yang aman (tanpa mengirim pesan / spam)
echo "[2] Menguji Validitas Token ke Server Fonnte (POST /device)...\n";
$status = FonnteService::checkDeviceStatus();

if ($status['success']) {
    echo "    ✅ HASIL: TOKEN VALID & TERVERIFIKASI OLEH FONNTE!\n";
    echo "    📱 Nama Perangkat  : {$status['device_name']}\n";
    echo "    📞 Nomor WhatsApp  : {$status['device_phone']}\n";
    echo "    📶 Status Koneksi  : " . strtoupper($status['device_status']) . "\n";
    echo "    📦 Paket Akun      : {$status['package']}\n";
    echo "    📊 Sisa Kuota      : {$status['quota']}\n\n";

    if ($status['connected']) {
        echo "    🎉 STATUS AKHIR: SIAP DIGUNAKAN! Sistem ServisYuk siap\n";
        echo "       mengirim notifikasi otomatis ke WhatsApp pelanggan.\n";
    } else {
        echo "    ⚠️ PERINGATAN: Token valid, tetapi nomor WhatsApp Anda di\n";
        echo "       dashboard Fonnte berstatus '" . $status['device_status'] . "'.\n";
        echo "       Buka https://fonnte.com dan scan QR Code untuk menghubungkan WA.\n";
    }
} else {
    echo "    ❌ HASIL: PENGUJIAN API GAGAL!\n";
    echo "    Pesan Error: {$status['message']}\n";
    echo "    Pastikan token yang Anda masukkan ke file .env sudah benar dan aktif.\n";
}

echo "\n========================================================\n\n";
