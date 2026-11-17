<?php
// ==========================================================
// Core: AI Diagnostic Service (Hybrid Gemini API + Expert Rules)
// ==========================================================

require_once __DIR__ . '/Database.php';
require_once __DIR__ . '/Helper.php';

class AiService {
    private static ?string $apiKey = null;
    private static ?string $model = null;

    private static function initConfig(): void {
        $db = Database::getInstance();
        $settings = $db->fetchAll("SELECT setting_key, setting_value FROM system_settings WHERE setting_group = 'ai'");
        $configMap = [];
        foreach ($settings as $row) {
            $configMap[$row['setting_key']] = $row['setting_value'];
        }

        $defaultConfig = require __DIR__ . '/../config/ai_config.php';
        self::$apiKey = !empty($configMap['gemini_api_key']) ? $configMap['gemini_api_key'] : ($defaultConfig['gemini_api_key'] ?? '');
        self::$model = !empty($configMap['gemini_model']) ? $configMap['gemini_model'] : 'gemini-1.5-flash';
    }

    /**
     * Diagnose an appliance issue
     */
    public static function diagnose(array $params): array {
        self::initConfig();

        $categoryName = trim($params['category_name'] ?? '');
        $brand = trim($params['brand'] ?? 'Umum');
        $model = trim($params['model'] ?? '');
        $symptoms = trim($params['symptoms'] ?? '');
        $soundBehavior = trim($params['sound_behavior'] ?? '');
        $ageYears = trim($params['age_years'] ?? '');

        if (empty($symptoms)) {
            return [
                'success' => false,
                'message' => 'Deskripsi gejala atau keluhan kerusakan belum diisi.'
            ];
        }

        // Try Gemini AI if API key is provided
        if (!empty(self::$apiKey) && strlen(self::$apiKey) > 10) {
            $geminiResult = self::callGeminiApi($categoryName, $brand, $model, $symptoms, $soundBehavior, $ageYears);
            if ($geminiResult['success']) {
                return $geminiResult;
            }
        }

        // Fallback to Built-in Smart Expert Engine
        return self::diagnoseWithExpertEngine($categoryName, $brand, $model, $symptoms, $soundBehavior, $ageYears);
    }

    /**
     * Call Google Gemini API
     */
    private static function callGeminiApi(string $category, string $brand, string $model, string $symptoms, string $sound, string $age): array {
        $endpoint = "https://generativelanguage.googleapis.com/v1beta/models/" . self::$model . ":generateContent?key=" . self::$apiKey;

        $prompt = "Anda adalah Pakar Teknisi Peralatan Rumah Tangga Senior & AI Diagnostic Specialist.
Analisis keluhan kerusakan peralatan berikut dan kembalikan HANYA JSON murni tanpa markdown formatting atau backticks:

Data Kerusakan:
- Kategori Peralatan: {$category}
- Merk/Model: {$brand} {$model}
- Gejala & Keluhan: {$symptoms}
- Perilaku Suara/Bau: {$sound}
- Estimasi Usia Pemakaian: {$age}

Format JSON yang WAJIB dihasilkan:
{
  \"issue_title\": \"Judul singkat ringkasan diagnosa kerusakan\",
  \"probable_causes\": [\"Penyebab utama 1\", \"Penyebab 2\", \"Penyebab 3\"],
  \"urgency_level\": \"Tinggi\" / \"Sedang\" / \"Rendah\" / \"Darurat\",
  \"safety_warning\": \"Instruksi keselamatan penting sebelum teknisi datang (misal: cabut stop kontak, jangan congkel benda tajam, matikan kran)\",
  \"diy_recommendations\": [\"Langkah pengecekan awal yang aman 1\", \"Langkah 2\"],
  \"estimated_cost_min\": 80000,
  \"estimated_cost_max\": 250000,
  \"estimated_cost_formatted\": \"Rp 80.000 - Rp 250.000\",
  \"estimated_repair_time\": \"45 - 90 Menit\",
  \"recommended_action\": \"Penjelasan tindakan perbaikan teknisi yang disarankan\",
  \"ai_summary\": \"Penjelasan ramah untuk customer mengenai apa yang terjadi dan estimasi penanganan.\"
}";

        $payload = [
            'contents' => [
                [
                    'parts' => [
                        ['text' => $prompt]
                    ]
                ]
            ],
            'generationConfig' => [
                'temperature' => 0.2,
                'maxOutputTokens' => 1200,
                'responseMimeType' => 'application/json'
            ]
        ];

        $ch = curl_init($endpoint);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
        curl_setopt($ch, CURLOPT_TIMEOUT, 12);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError = curl_error($ch);
        curl_close($ch);

        if ($curlError || $httpCode !== 200) {
            return ['success' => false, 'error' => 'Gemini API Error: ' . ($curlError ?: "HTTP $httpCode")];
        }

        $resData = json_decode($response, true);
        $rawText = $resData['candidates'][0]['content']['parts'][0]['text'] ?? '';

        // Clean any code block markup if present
        $cleanJson = preg_replace('/^```json\s*/i', '', trim($rawText));
        $cleanJson = preg_replace('/```$/i', '', $cleanJson);

        $parsed = json_decode($cleanJson, true);
        if (!$parsed || !isset($parsed['issue_title'])) {
            return ['success' => false, 'error' => 'Invalid JSON from Gemini API'];
        }

        $parsed['source'] = 'Google Gemini AI (Live)';
        return [
            'success' => true,
            'data' => $parsed
        ];
    }

    /**
     * Built-in Offline Expert Rule Engine (Knowledge-base Matching & NLP Triage)
     */
    public static function diagnoseWithExpertEngine(string $category, string $brand, string $model, string $symptoms, string $sound, string $age): array {
        $db = Database::getInstance();
        $kbList = $db->fetchAll("SELECT * FROM ai_knowledge_base");

        $combinedText = strtolower("$category $brand $model $symptoms $sound $age");
        $tokens = preg_split('/[\s,\.\?\!\-\/]+/', $combinedText, -1, PREG_SPLIT_NO_EMPTY);

        $bestMatch = null;
        $highestScore = 0;

        foreach ($kbList as $kb) {
            $score = 0;
            $catMatch = false;

            // Category matching
            if (!empty($category) && stripos($kb['category_name'], $category) !== false) {
                $score += 15;
                $catMatch = true;
            }

            // Keyword matching
            $keywords = array_map('trim', explode(',', strtolower($kb['symptom_keywords'])));
            foreach ($keywords as $kw) {
                if (empty($kw)) continue;
                if (stripos($combinedText, $kw) !== false) {
                    $score += 20;
                }
                foreach ($tokens as $t) {
                    if (strlen($t) >= 3 && stripos($kw, $t) !== false) {
                        $score += 5;
                    }
                }
            }

            if ($score > $highestScore) {
                $highestScore = $score;
                $bestMatch = $kb;
            }
        }

        // If strong match found in knowledge base
        if ($bestMatch && $highestScore >= 15) {
            $causes = array_filter(array_map('trim', explode("\n", $bestMatch['probable_causes'])));
            $diyList = array_filter(array_map('trim', explode("\n", $bestMatch['diy_check'])));

            $costMin = (float)$bestMatch['estimated_cost_min'];
            $costMax = (float)$bestMatch['estimated_cost_max'];
            $formattedCost = Helper::formatRupiah($costMin) . ' - ' . Helper::formatRupiah($costMax);

            $summary = "Berdasarkan diagnosa cerdas untuk unit {$brand} ({$category}), gejala yang Anda alami teridentifikasi sebagai: \"{$bestMatch['issue_title']}\". ";
            $summary .= "Masalah ini umumnya dapat diperbaiki dalam rentang estimasi {$bestMatch['repair_time_estimate']} oleh teknisi spesialis.";

            return [
                'success' => true,
                'data' => [
                    'issue_title' => $bestMatch['issue_title'],
                    'probable_causes' => array_values($causes),
                    'urgency_level' => (stripos($symptoms, 'bocor') !== false || stripos($symptoms, 'mati') !== false || stripos($symptoms, 'asap') !== false) ? 'Tinggi' : 'Sedang',
                    'safety_warning' => $bestMatch['safety_warning'],
                    'diy_recommendations' => array_values($diyList),
                    'estimated_cost_min' => $costMin,
                    'estimated_cost_max' => $costMax,
                    'estimated_cost_formatted' => $formattedCost,
                    'estimated_repair_time' => $bestMatch['repair_time_estimate'],
                    'recommended_action' => "Pemeriksaan teknis langsung oleh Teknisi Spesialis {$category}. Segera lakukan pemesanan agar tidak memicu kerusakan komponen lainnya.",
                    'ai_summary' => $summary,
                    'source' => 'ServisYuk Smart Expert Engine (Offline Mode)'
                ]
            ];
        }

        // Generic intelligent fallback
        $costMin = 75000;
        $costMax = 250000;
        return [
            'success' => true,
            'data' => [
                'issue_title' => "Indikasi Masalah Mekanikal/Kelistrikan pada " . ($category ?: 'Peralatan'),
                'probable_causes' => [
                    "Penurunan kinerja komponen elektrikal atau kapasitor",
                    "Penumpukan kotoran, debu, atau penyumbatan sirkulasi",
                    "Kelonggaran sambungan perkabelan atau keausan suku cadang"
                ],
                'urgency_level' => 'Sedang',
                'safety_warning' => 'Cabut sumber listrik utama apabila terdengar suara aneh, bau terbakar, atau timbul percikan sebelum diperiksa oleh teknisi.',
                'diy_recommendations' => [
                    'Pastikan suplai tegangan listrik di rumah Anda stabil.',
                    'Bersihkan permukaan luar unit dari debu atau genangan cairan.',
                    'Jangan membongkar casing kelistrikan tanpa peralatan proteksi keselamatan standar.'
                ],
                'estimated_cost_min' => $costMin,
                'estimated_cost_max' => $costMax,
                'estimated_cost_formatted' => Helper::formatRupiah($costMin) . ' - ' . Helper::formatRupiah($costMax),
                'estimated_repair_time' => '1 - 2 Jam',
                'recommended_action' => 'Disarankan untuk memesan kunjungan teknisi guna melakukan inspeksi menyeluruh (multimeter test & load test).',
                'ai_summary' => "Sistem telah menganalisis keluhan peralatan {$brand} Anda. Teknisi terverifikasi kami siap melakukan inspeksi langsung dan perbaikan bergaransi.",
                'source' => 'ServisYuk Smart Diagnostic System'
            ]
        ];
    }
}
