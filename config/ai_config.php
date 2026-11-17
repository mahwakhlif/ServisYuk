<?php
// ==========================================================
// AI Service Configuration (Modular & Environment-Aware)
// ==========================================================

require_once __DIR__ . '/../core/Env.php';
Env::load();

return [
    'provider' => Env::get('AI_PROVIDER', 'gemini'), // 'gemini' or 'offline_expert'
    'gemini_api_key' => Env::get('GEMINI_API_KEY', ''),
    'gemini_model' => Env::get('GEMINI_MODEL', 'gemini-1.5-flash'),
    'gemini_endpoint' => 'https://generativelanguage.googleapis.com/v1beta/models/' . Env::get('GEMINI_MODEL', 'gemini-1.5-flash') . ':generateContent',
    'temperature' => 0.4,
    'max_output_tokens' => 1024,
    'offline_fallback_enabled' => (bool)Env::get('AI_OFFLINE_FALLBACK', true),
    'system_instruction' => 'Anda adalah Asisten Pakar Diagnosa Kerusakan Peralatan Rumah Tangga (Home Appliance Diagnostic AI) yang profesional, ramah, dan teliti dalam Bahasa Indonesia. Tugas Anda menganalisis keluhan peralatan rumah tangga, menjelaskan kemungkinan penyebab teknis, memberikan langkah pengamanan awal (safety check), estimasi rentang biaya servis (dalam Rupiah), serta saran rekomendasi teknisi.'
];
