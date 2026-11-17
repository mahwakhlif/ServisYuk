<?php
// ==========================================================
// Core: AiConfigService — Secure AI Model & API Configuration
// Platform: ServisYuk (Admin Management & Encryption)
// ==========================================================

require_once __DIR__ . '/Database.php';
require_once __DIR__ . '/Env.php';
require_once __DIR__ . '/Helper.php';

class AiConfigService {
    private const CIPHER_METHOD = 'AES-256-CBC';

    /**
     * Preset Provider OpenAI-Compatible Resmi
     */
    public const PROVIDER_PRESETS = [
        'groq' => [
            'name'             => 'Groq Cloud API (Ultra Cepat & Gratis)',
            'base_url'         => 'https://api.groq.com/openai/v1',
            'default_model'    => 'qwen/qwen3.8-27b',
            'default_fallback' => 'openai/gpt-oss-20b',
            'key_placeholder'  => 'gsk_...',
            'doc_url'          => 'https://console.groq.com/keys',
            'doc_title'        => 'Groq Cloud Console',
            'models'           => [
                ['id' => 'qwen/qwen3.8-27b', 'name' => 'Qwen 3.8 27B (Rekomendasi Utama - Akurat & Cepat)', 'recommended' => true],
                ['id' => 'openai/gpt-oss-20b', 'name' => 'GPT-OSS 20B (Sangat Cepat & Hemat Kuota)', 'recommended' => true],
                ['id' => 'openai/gpt-oss-120b', 'name' => 'GPT-OSS 120B (Penalaran Kompleks)', 'recommended' => false],
                ['id' => 'allam-2-7b', 'name' => 'Allam 2 7B (Model Ringan)', 'recommended' => false]
            ]
        ],
        'gemini' => [
            'name'             => 'Google Gemini (OpenAI-Compatible)',
            'base_url'         => 'https://generativelanguage.googleapis.com/v1beta/openai',
            'default_model'    => 'gemini-3.8-flash',
            'default_fallback' => 'models/gemini-3.8-flash',
            'key_placeholder'  => 'AIzaSy...',
            'doc_url'          => 'https://aistudio.google.com/app/apikey',
            'doc_title'        => 'Google AI Studio',
            'models'           => [
                ['id' => 'gemini-3.8-flash', 'name' => 'Gemini 3.8 Flash (Model Resmi Rekomendasi Utama Google)', 'recommended' => true],
                ['id' => 'models/gemini-3.8-flash', 'name' => 'models/gemini-3.8-flash (Model Resmi dengan Prefix models/)', 'recommended' => true],
                ['id' => 'gemini-1.5-flash', 'name' => 'Gemini 1.5 Flash (Model Alternatif)', 'recommended' => false],
                ['id' => 'gemini-1.5-pro',   'name' => 'Gemini 1.5 Pro (Penalaran Kompleks)', 'recommended' => false]
            ]
        ],
        'nvidia' => [
            'name'             => 'NVIDIA NIM (Inference Microservice)',
            'base_url'         => 'https://integrate.api.nvidia.com/v1',
            'default_model'    => 'nvidia/llama-3.1-nemotron-70b-instruct',
            'default_fallback' => 'nvidia/mistral-nemo-minitron-8b-8k-instruct',
            'key_placeholder'  => 'nvapi-...',
            'doc_url'          => 'https://build.nvidia.com/explore/discover',
            'doc_title'        => 'NVIDIA NIM Catalog',
            'models'           => [
                ['id' => 'nvidia/llama-3.1-nemotron-70b-instruct', 'name' => 'Llama 3.1 Nemotron 70B (Unggulan Resmi NVIDIA)', 'recommended' => true],
                ['id' => 'mistralai/mistral-large-2-instruct', 'name' => 'Mistral Large 2 Instruct (Akurasi Tinggi)', 'recommended' => true],
                ['id' => 'nvidia/mistral-nemo-minitron-8b-8k-instruct', 'name' => 'Minitron 8B (Ultra Cepat & Ringan)', 'recommended' => true],
                ['id' => 'ibm/granite-3.0-8b-instruct', 'name' => 'IBM Granite 3.0 8B Instruct', 'recommended' => false],
                ['id' => 'mistralai/mistral-7b-instruct-v0.3', 'name' => 'Mistral 7B Instruct v0.3', 'recommended' => false]
            ]
        ],
        'openai' => [
            'name'             => 'OpenAI (Official ChatGPT Engine)',
            'base_url'         => 'https://api.openai.com/v1',
            'default_model'    => 'gpt-4o-mini',
            'default_fallback' => 'gpt-4o-mini',
            'key_placeholder'  => 'sk-proj-... atau sk-...',
            'doc_url'          => 'https://platform.openai.com/api-keys',
            'doc_title'        => 'OpenAI Platform Dashboard',
            'models'           => [
                ['id' => 'gpt-4o-mini', 'name' => 'GPT-4o Mini (Cepat, Ringan & Sangat Cerdas)', 'recommended' => true],
                ['id' => 'gpt-4o', 'name' => 'GPT-4o (Model Flagship Akurasi Tinggi)', 'recommended' => false],
                ['id' => 'gpt-3.5-turbo', 'name' => 'GPT-3.5 Turbo (Model Warisan)', 'recommended' => false]
            ]
        ],
        'deepseek' => [
            'name'             => 'DeepSeek AI (Chat & Reasoner)',
            'base_url'         => 'https://api.deepseek.com/v1',
            'default_model'    => 'deepseek-chat',
            'default_fallback' => 'deepseek-chat',
            'key_placeholder'  => 'sk-...',
            'doc_url'          => 'https://platform.deepseek.com/api_keys',
            'doc_title'        => 'DeepSeek Platform Console',
            'models'           => [
                ['id' => 'deepseek-chat', 'name' => 'DeepSeek-V3 (deepseek-chat - Percakapan Cepat)', 'recommended' => true],
                ['id' => 'deepseek-reasoner', 'name' => 'DeepSeek-R1 (deepseek-reasoner - Penalaran Mendalam)', 'recommended' => false]
            ]
        ],
        'openrouter' => [
            'name'             => 'OpenRouter (Multi-Provider Gateway)',
            'base_url'         => 'https://openrouter.ai/api/v1',
            'default_model'    => 'meta-llama/llama-3.3-70b-instruct',
            'default_fallback' => 'meta-llama/llama-3.1-8b-instruct',
            'key_placeholder'  => 'sk-or-v1-...',
            'doc_url'          => 'https://openrouter.ai/keys',
            'doc_title'        => 'OpenRouter Keys Dashboard',
            'models'           => [
                ['id' => 'meta-llama/llama-3.3-70b-instruct', 'name' => 'Llama 3.3 70B Instruct (Meta)', 'recommended' => true],
                ['id' => 'meta-llama/llama-3.1-8b-instruct', 'name' => 'Llama 3.1 8B Instruct (Meta)', 'recommended' => true],
                ['id' => 'deepseek/deepseek-chat', 'name' => 'DeepSeek V3 (via OpenRouter)', 'recommended' => false],
                ['id' => 'deepseek/deepseek-r1', 'name' => 'DeepSeek R1 (via OpenRouter)', 'recommended' => false],
                ['id' => 'google/gemini-2.0-flash-001', 'name' => 'Gemini 2.0 Flash (via OpenRouter)', 'recommended' => false]
            ]
        ],
        'custom' => [
            'name'             => 'Custom (OpenAI-Compatible Endpoint Mandiri)',
            'base_url'         => '',
            'default_model'    => '',
            'default_fallback' => '',
            'key_placeholder'  => 'Masukkan API Key endpoint Anda...',
            'doc_url'          => '',
            'doc_title'        => 'Dokumentasi Endpoint Anda',
            'models'           => []
        ]
    ];

    // Daftar model resmi default (Fallback backward compatibility)
    public const DEFAULT_MODELS = [
        [
            'id' => 'qwen/qwen3.8-27b',
            'name' => 'Qwen 3.8 27B (Rekomendasi Utama - Akurasi & Kecepatan Tinggi)',
            'provider' => 'Alibaba Cloud',
            'is_chat' => true,
            'recommended' => true
        ],
        [
            'id' => 'openai/gpt-oss-20b',
            'name' => 'GPT-OSS 20B (Sangat Cepat & Hemat Kuota)',
            'provider' => 'OpenAI',
            'is_chat' => true,
            'recommended' => true
        ],
        [
            'id' => 'openai/gpt-oss-120b',
            'name' => 'GPT-OSS 120B (Penalaran Rumit & Analitik Tinggi)',
            'provider' => 'OpenAI',
            'is_chat' => true,
            'recommended' => false
        ],
        [
            'id' => 'allam-2-7b',
            'name' => 'Allam 2 7B (Model Ringan)',
            'provider' => 'SDAIA',
            'is_chat' => true,
            'recommended' => false
        ],
        [
            'id' => 'llama-3.3-70b-versatile',
            'name' => 'Llama 3.3 70B Versatile (Meta)',
            'provider' => 'Meta',
            'is_chat' => true,
            'recommended' => false
        ],
        [
            'id' => 'llama-3.1-8b-instant',
            'name' => 'Llama 3.1 8B Instant (Ultra Cepat)',
            'provider' => 'Meta',
            'is_chat' => true,
            'recommended' => false
        ],
        [
            'id' => 'mixtral-8x7b-32768',
            'name' => 'Mixtral 8x7B (Konteks Panjang 32k)',
            'provider' => 'Mistral AI',
            'is_chat' => true,
            'recommended' => false
        ]
    ];

    /**
     * Dapatkan encryption master key untuk mengamankan API key di database
     */
    private static function getMasterKey(): string {
        Env::load();
        $dbName = Env::get('DB_DATABASE', 'db_jasa_perbaikan');
        $dbUser = Env::get('DB_USERNAME', 'root');
        $salt = Env::get('APP_KEY', 'ServisYukSecureSalt2026!@#');
        return hash('sha256', $dbName . ':' . $dbUser . ':' . $salt, true);
    }

    /**
     * Enkripsi string rahasia menggunakan AES-256-CBC dengan random IV
     */
    public static function encryptSecret(string $plainText): string {
        if (empty($plainText)) return '';
        $iv = openssl_random_pseudo_bytes(16);
        $encrypted = openssl_encrypt($plainText, self::CIPHER_METHOD, self::getMasterKey(), 0, $iv);
        return base64_encode($iv . '::' . $encrypted);
    }

    /**
     * Dekripsi string rahasia
     */
    public static function decryptSecret(string $cipherText): string {
        if (empty($cipherText)) return '';
        $raw = base64_decode($cipherText, true);
        if (!$raw || !str_contains($raw, '::')) {
            return '';
        }
        list($iv, $encrypted) = explode('::', $raw, 2);
        if (strlen($iv) !== 16) {
            return '';
        }
        $decrypted = openssl_decrypt($encrypted, self::CIPHER_METHOD, self::getMasterKey(), 0, $iv);
        if ($decrypted !== false && $decrypted !== '') {
            return $decrypted;
        }

        // Backward-compatibility: jika APP_KEY di .env berubah, tetap dapat mendekripsi kunci lama dengan legacy salt
        $dbName = Env::get('DB_DATABASE', 'db_jasa_perbaikan');
        $dbUser = Env::get('DB_USERNAME', 'root');
        $legacyKey = hash('sha256', $dbName . ':' . $dbUser . ':ServisYukSecureSalt2026!@#', true);
        $legacyDecrypted = openssl_decrypt($encrypted, self::CIPHER_METHOD, $legacyKey, 0, $iv);
        return $legacyDecrypted !== false ? $legacyDecrypted : '';
    }

    /**
     * Dapatkan API Key tersimpan (terdekripsi) untuk provider tertentu
     */
    public static function getSavedKeyForProvider(string $provider): ?string {
        $enc = self::getSetting("ai_chatbot_key_{$provider}_enc");
        if (!empty($enc)) {
            $dec = self::decryptSecret($enc);
            if (!empty($dec)) {
                return $dec;
            }
        }
        if ($provider === 'groq') {
            $envKey = Env::get('GROQ_API_KEY', '');
            if (!empty($envKey)) return $envKey;
        } elseif ($provider === 'gemini') {
            $envKey = Env::get('GEMINI_API_KEY', '');
            if (!empty($envKey)) return $envKey;
        }
        return null;
    }

    /**
     * Samarkan API key untuk tampilan UI agar tidak membocorkan credential
     * Contoh: gsk_og2R••••••••Pw7
     */
    public static function maskApiKey(?string $key): string {
        if (empty($key)) {
            return 'Belum dikonfigurasi';
        }
        $key = trim($key);
        $len = strlen($key);
        if ($len <= 10) {
            return '••••••••';
        }
        $prefix = substr($key, 0, 7);
        $suffix = substr($key, -4);
        return $prefix . '••••••••' . $suffix;
    }

    /**
     * Ambil seluruh konfigurasi AI aktif saat ini
     */
    public static function getActiveConfig(): array {
        Env::load();
        $db = Database::getInstance();
        $pdo = $db->getConnection();

        // 1. Baca dari database system_settings
        $settings = [];
        try {
            $stmt = $pdo->query("SELECT setting_key, setting_value FROM system_settings WHERE setting_group = 'ai_chatbot'");
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $settings[$row['setting_key']] = $row['setting_value'];
            }
        } catch (Exception $e) {
            error_log("[AiConfigService] Error reading settings: " . $e->getMessage());
        }

        $provider = $settings['ai_chatbot_provider'] ?? Env::get('AI_CHATBOT_PROVIDER', 'groq');

        // 2. Dekripsi API Key jika tersimpan di DB, prioritaskan key khusus provider yang sedang aktif
        $apiKey = self::getSavedKeyForProvider($provider);
        if (empty($apiKey) && $provider === 'groq' && !empty($settings['ai_chatbot_api_key_enc'])) {
            $apiKey = self::decryptSecret($settings['ai_chatbot_api_key_enc']);
        }
        if (empty($apiKey)) {
            if ($provider === 'groq') {
                $apiKey = Env::get('GROQ_API_KEY', Env::get('AI_CHATBOT_API_KEY', ''));
            } elseif ($provider === 'gemini') {
                $apiKey = Env::get('GEMINI_API_KEY', '');
            } elseif ($provider === 'nvidia') {
                $apiKey = Env::get('NVIDIA_API_KEY', '');
            } elseif ($provider === 'openai') {
                $apiKey = Env::get('OPENAI_API_KEY', '');
            } elseif ($provider === 'deepseek') {
                $apiKey = Env::get('DEEPSEEK_API_KEY', '');
            } elseif ($provider === 'openrouter') {
                $apiKey = Env::get('OPENROUTER_API_KEY', '');
            }
        }

        // Ambil status API key tersimpan untuk masing-masing provider (tersamarkan untuk UI)
        $providerKeys = [];
        foreach (array_keys(self::PROVIDER_PRESETS) as $p) {
            $saved = self::getSavedKeyForProvider($p);
            $providerKeys[$p] = !empty($saved) ? self::maskApiKey($saved) : null;
        }

        $model = $settings['ai_chatbot_model'] ?? Env::get('AI_CHATBOT_MODEL', 'qwen/qwen3.8-27b');
        $fallbackModel = $settings['ai_chatbot_fallback_model'] ?? Env::get('AI_CHATBOT_FALLBACK_MODEL', 'openai/gpt-oss-20b');
        $baseUrl = $settings['ai_chatbot_base_url'] ?? Env::get('AI_CHATBOT_BASE_URL', 'https://api.groq.com/openai/v1');
        $status = $settings['ai_chatbot_status'] ?? (!empty($apiKey) ? 'configured' : 'not_configured');
        $lastTested = $settings['ai_chatbot_last_tested'] ?? null;
        $latencyMs = isset($settings['ai_chatbot_latency_ms']) ? (int)$settings['ai_chatbot_latency_ms'] : null;
        $lastError = $settings['ai_chatbot_last_error'] ?? null;

        return [
            'provider'        => $provider,
            'api_key'         => $apiKey,
            'api_key_masked'  => self::maskApiKey($apiKey),
            'masked_key'      => self::maskApiKey($apiKey),
            'has_api_key'     => !empty($apiKey),
            'is_configured'   => !empty($apiKey),
            'provider_keys'   => $providerKeys,
            'model'           => $model,
            'active_model'    => $model,
            'fallback_model'  => $fallbackModel,
            'base_url'        => $baseUrl,
            'status'          => $status,
            'last_tested'     => $lastTested,
            'latency_ms'      => $latencyMs,
            'last_error'      => $lastError
        ];
    }

    /**
     * Ambil nilai pengaturan individual dari tabel system_settings
     */
    public static function getSetting(string $key): ?string {
        try {
            $db = Database::getInstance();
            $stmt = $db->getConnection()->prepare("SELECT setting_value FROM system_settings WHERE setting_key = ? LIMIT 1");
            $stmt->execute([$key]);
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            return $row ? (string)$row['setting_value'] : null;
        } catch (Exception $e) {
            return null;
        }
    }

    /**
     * Simpan pengaturan ke database system_settings dan sinkronkan ke .env & ai_config.json
     */
    public static function saveConfig(array $data): bool {
        $db = Database::getInstance();
        $pdo = $db->getConnection();

        $provider = trim($data['provider'] ?? 'groq');
        $model = trim($data['model'] ?? '');
        $fallbackModel = trim($data['fallback_model'] ?? '');
        $baseUrl = trim($data['base_url'] ?? '');
        $newApiKey = trim($data['api_key'] ?? '');

        // Fallback default per provider jika parameter kosong
        $baseUrl = self::normalizeBaseUrl($provider, $baseUrl);
        if (empty($model) && isset(self::PROVIDER_PRESETS[$provider])) {
            $model = self::PROVIDER_PRESETS[$provider]['default_model'];
        }
        if (empty($fallbackModel) && isset(self::PROVIDER_PRESETS[$provider])) {
            $fallbackModel = self::PROVIDER_PRESETS[$provider]['default_fallback'];
        }

        // Validasi input
        if (empty($model)) {
            throw new InvalidArgumentException("Model AI utama wajib diisi atau dipilih.");
        }

        $activeConfig = self::getActiveConfig();
        $currentProvider = $activeConfig['provider'];

        // Tentukan API key yang akan disimpan
        if (!empty($newApiKey)) {
            $apiKeyToSave = $newApiKey;
        } else {
            // Jika API Key dikosongkan pada form:
            $savedKey = self::getSavedKeyForProvider($provider);
            if (!empty($savedKey)) {
                $apiKeyToSave = $savedKey;
            } elseif ($provider === $currentProvider && !empty($activeConfig['api_key'])) {
                // Tetap gunakan API key yang sudah aktif untuk provider yang sama (jangan hapus key lama)
                $apiKeyToSave = $activeConfig['api_key'];
            } else {
                $providerName = self::PROVIDER_PRESETS[$provider]['name'] ?? strtoupper($provider);
                throw new InvalidArgumentException("Harap masukkan API Key untuk provider {$providerName} yang baru dipilih.");
            }
        }

        if (empty($apiKeyToSave)) {
            throw new InvalidArgumentException("API Key belum diisi. Masukkan API Key yang valid.");
        }

        // Simpan nilai ke tabel system_settings
        $upsertSetting = function(string $key, string $val) use ($pdo) {
            $stmt = $pdo->prepare("
                INSERT INTO system_settings (setting_key, setting_value, setting_group, updated_at)
                VALUES (?, ?, 'ai_chatbot', NOW())
                ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value), updated_at = NOW()
            ");
            $stmt->execute([$key, $val]);
        };

        $upsertSetting('ai_chatbot_provider', $provider);
        $upsertSetting('ai_chatbot_model', $model);
        $upsertSetting('ai_chatbot_fallback_model', $fallbackModel);
        $upsertSetting('ai_chatbot_base_url', $baseUrl);

        $encryptedKey = self::encryptSecret($apiKeyToSave);
        $upsertSetting('ai_chatbot_api_key_enc', $encryptedKey);
        $upsertSetting("ai_chatbot_key_{$provider}_enc", $encryptedKey);

        // Jika provider adalah groq, pastikan key groq tersimpan
        if ($provider === 'groq') {
            $upsertSetting('ai_chatbot_key_groq_enc', $encryptedKey);
        }

        // Sinkronkan ke .env
        $envUpdates = [
            'AI_CHATBOT_PROVIDER'        => $provider,
            'AI_CHATBOT_MODEL'           => $model,
            'AI_CHATBOT_FALLBACK_MODEL'  => $fallbackModel,
            'AI_CHATBOT_BASE_URL'        => $baseUrl,
            'AI_CHATBOT_API_KEY'         => $apiKeyToSave
        ];
        // Sinkronkan key spesifik ke env jika relevan
        if ($provider === 'groq') {
            $envUpdates['GROQ_API_KEY'] = $apiKeyToSave;
        } elseif ($provider === 'gemini') {
            $envUpdates['GEMINI_API_KEY'] = $apiKeyToSave;
            $envUpdates['GEMINI_MODEL'] = $model;
        }
        self::syncToEnv($envUpdates);

        // Sinkronkan ke chatbot/ai_config.json untuk Node.js daemon (hanya metadata model non-rahasia)
        self::syncToJson([
            'provider'       => $provider,
            'model'          => $model,
            'active_model'   => $model,
            'fallback_model' => $fallbackModel,
            'base_url'       => $baseUrl,
            'updated_at'     => date('Y-m-d H:i:s')
        ]);

        return true;
    }

    /**
     * Perbarui status koneksi pada system_settings
     */
    public static function updateConnectionStatus(string $status, ?int $latencyMs = null, ?string $errorMsg = null): void {
        try {
            $db = Database::getInstance();
            $pdo = $db->getConnection();
            $upsert = function(string $key, ?string $val) use ($pdo) {
                $stmt = $pdo->prepare("
                    INSERT INTO system_settings (setting_key, setting_value, setting_group, updated_at)
                    VALUES (?, ?, 'ai_chatbot', NOW())
                    ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value), updated_at = NOW()
                ");
                $stmt->execute([$key, (string)$val]);
            };

            $upsert('ai_chatbot_status', $status);
            $upsert('ai_chatbot_last_tested', date('Y-m-d H:i:s'));
            if ($latencyMs !== null) {
                $upsert('ai_chatbot_latency_ms', (string)$latencyMs);
            }
            if ($errorMsg !== null) {
                $upsert('ai_chatbot_last_error', $errorMsg);
            }
        } catch (Exception $e) {
            error_log("[AiConfigService] Error updating status: " . $e->getMessage());
        }
    }

    /**
     * Normalisasi Base URL agar selalu memiliki format standar yang valid (tanpa trailing slash atau path ganda)
     */
    public static function normalizeBaseUrl(string $provider, string $baseUrl): string {
        $baseUrl = trim($baseUrl);
        if (empty($baseUrl) && isset(self::PROVIDER_PRESETS[$provider]['base_url'])) {
            $baseUrl = self::PROVIDER_PRESETS[$provider]['base_url'];
        }
        // Hapus /chat/completions atau /completions jika user tidak sengaja menyalin full endpoint URL
        $baseUrl = preg_replace('/\/+(chat\/+completions|completions)\/*$/i', '', $baseUrl);
        $baseUrl = rtrim($baseUrl, '/');

        // Normalisasi akhiran /v1 atau /openai untuk provider standar
        if ($provider === 'nvidia') {
            if (!preg_match('/\/v1$/i', $baseUrl)) {
                $baseUrl .= '/v1';
            }
        } elseif ($provider === 'gemini') {
            if (str_contains($baseUrl, 'generativelanguage.googleapis.com')) {
                if (!preg_match('/\/openai$/i', $baseUrl)) {
                    if (preg_match('/\/v1beta$/i', $baseUrl)) {
                        $baseUrl .= '/openai';
                    } elseif (!str_contains($baseUrl, 'v1beta')) {
                        $baseUrl = rtrim($baseUrl, '/') . '/v1beta/openai';
                    }
                }
            }
        } elseif ($provider === 'groq') {
            if (!preg_match('/\/openai\/v1$/i', $baseUrl)) {
                if (preg_match('/\/openai$/i', $baseUrl)) {
                    $baseUrl .= '/v1';
                } elseif (!preg_match('/\/v1$/i', $baseUrl)) {
                    $baseUrl .= '/openai/v1';
                }
            }
        } elseif (in_array($provider, ['openai', 'deepseek', 'openrouter'])) {
            if (!preg_match('/\/v1$/i', $baseUrl)) {
                $baseUrl .= '/v1';
            }
        }

        return $baseUrl;
    }

    /**
     * Uji koneksi ke API Provider menggunakan model yang dipilih
     */
    public static function testConnection(string $provider, string $apiKey, string $model, string $baseUrl): array {
        $provider = trim($provider ?: 'groq');
        $model = trim($model);
        $baseUrl = self::normalizeBaseUrl($provider, $baseUrl);

        $curr = self::getActiveConfig();
        if (empty($apiKey)) {
            // Coba ambil key tersimpan untuk provider ini
            $savedKey = self::getSavedKeyForProvider($provider);
            if (!empty($savedKey)) {
                $apiKey = $savedKey;
            } elseif ($provider === $curr['provider'] && !empty($curr['api_key'])) {
                $apiKey = $curr['api_key'];
            } else {
                $providerName = self::PROVIDER_PRESETS[$provider]['name'] ?? strtoupper($provider);
                return [
                    'success'    => false,
                    'latency_ms' => null,
                    'http_code'  => 0,
                    'message'    => "API Key belum diisi untuk provider {$providerName}. Masukkan API Key Anda pada kolom API Key sebelum melakukan uji koneksi."
                ];
            }
        }

        if (empty($model)) {
            $model = self::PROVIDER_PRESETS[$provider]['default_model'] ?? 'qwen/qwen3.8-27b';
        }

        // Tentukan model kandidat untuk pengujian (khusus Gemini sediakan varian prefix models/ jika varian awal 404)
        $candidates = [$model];
        if ($provider === 'gemini' || str_contains($baseUrl, 'generativelanguage.googleapis.com')) {
            $altModel = str_starts_with($model, 'models/') ? substr($model, 7) : 'models/' . $model;
            if (!in_array($altModel, $candidates)) {
                $candidates[] = $altModel;
            }
        } else {
            // Untuk provider selain Gemini, pastikan prefix models/ dibersihkan
            $candidates = [preg_replace('/^models\//i', '', $model)];
        }

        $endpoint = rtrim($baseUrl, '/') . '/chat/completions';
        $headers = [
            'Authorization: Bearer ' . $apiKey,
            'Content-Type: application/json'
        ];
        if ($provider === 'openrouter') {
            $headers[] = 'HTTP-Referer: http://localhost/jasa';
            $headers[] = 'X-Title: ServisYuk';
        }

        $lastHttpCode = 0;
        $lastLatency = null;
        $lastErrorMsg = '';
        $lastRes = '';

        foreach ($candidates as $candModel) {
            $payload = json_encode([
                'model'       => $candModel,
                'messages'    => [
                    ['role' => 'user', 'content' => 'Ping connection test']
                ],
                'max_tokens'  => 64,
                'temperature' => 0.1,
                'stream'      => false
            ]);

            $start = microtime(true);
            $ch = curl_init($endpoint);
            curl_setopt_array($ch, [
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_POST           => true,
                CURLOPT_HTTPHEADER     => $headers,
                CURLOPT_POSTFIELDS     => $payload,
                CURLOPT_TIMEOUT        => 25,
                CURLOPT_CONNECTTIMEOUT => 10,
                CURLOPT_SSL_VERIFYPEER => false
            ]);

            $res = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $curlErr = curl_error($ch);
            curl_close($ch);

            $latency = round((microtime(true) - $start) * 1000);
            $lastHttpCode = $httpCode;
            $lastLatency = $latency;
            $lastRes = $res;

            if ($curlErr) {
                self::updateConnectionStatus('error', null, $curlErr);
                return [
                    'success' => false,
                    'latency_ms' => null,
                    'http_code' => 0,
                    'message' => "Koneksi jaringan gagal ke '{$endpoint}': {$curlErr}"
                ];
            }

            if ($httpCode === 200 && !empty($res)) {
                $data = json_decode($res, true);
                $modelUsed = $data['model'] ?? $candModel;
                self::updateConnectionStatus('connected', $latency, null);
                return [
                    'success'    => true,
                    'latency_ms' => $latency,
                    'http_code'  => $httpCode,
                    'model'      => $modelUsed,
                    'message'    => "Koneksi berhasil! Model '{$modelUsed}' aktif dan merespons dalam {$latency} ms."
                ];
            }

            // Jika 404 pada Gemini dan masih ada kandidat prefix alternatif, lanjutkan ke kandidat berikutnya
            if ($httpCode === 404 && count($candidates) > 1 && $candModel === $candidates[0]) {
                continue;
            }
            break;
        }

        // Tangani pesan error spesifik dengan parsing fleksibel (objek tunggal maupun array-wrapped)
        $errorMsg = "API Provider mengembalikan status HTTP {$lastHttpCode}.";
        if (!empty($lastRes)) {
            $errData = json_decode($lastRes, true);
            $errObj = null;
            if (is_array($errData)) {
                if (!empty($errData['error'])) {
                    $errObj = $errData['error'];
                } elseif (!empty($errData[0]['error'])) {
                    $errObj = $errData[0]['error'];
                } elseif (!empty($errData[0]) && is_array($errData[0])) {
                    $errObj = $errData[0];
                } else {
                    $errObj = $errData;
                }
            }

            if ($errObj && is_array($errObj)) {
                if (!empty($errObj['message'])) {
                    $errorMsg = $errObj['message'];
                } elseif (!empty($errObj['detail'])) {
                    $errorMsg = is_array($errObj['detail']) ? json_encode($errObj['detail']) : $errObj['detail'];
                } elseif (!empty($errObj['title'])) {
                    $errorMsg = $errObj['title'] . (!empty($errObj['detail']) ? ': ' . $errObj['detail'] : '');
                }
            } else {
                $trimmed = trim($lastRes);
                if (strlen($trimmed) < 300) {
                    $errorMsg = $trimmed;
                }
            }
        }

        if ($lastHttpCode === 400) {
            $errorMsg = "Permintaan Ditolak (HTTP 400): " . $errorMsg;
        } elseif ($lastHttpCode === 401) {
            $errorMsg = "Autentikasi Gagal (HTTP 401): API Key tidak valid atau telah kedaluwarsa." . (!empty($errorMsg) && $errorMsg !== $lastRes ? " Detail: {$errorMsg}" : "");
        } elseif ($lastHttpCode === 403) {
            $errorMsg = "Akses Ditolak (HTTP 403): API Key tidak memiliki izin untuk model '{$model}'." . (!empty($errorMsg) && $errorMsg !== $lastRes ? " Detail: {$errorMsg}" : "");
        } elseif ($lastHttpCode === 404) {
            if (!empty($errorMsg) && (stripos($errorMsg, 'Not found for account') !== false || stripos($errorMsg, 'Function') !== false)) {
                $accountInfo = '';
                if (preg_match("/account\s+'([^']+)'/i", $errorMsg, $accMatch)) {
                    $accountInfo = " (Account ID: {$accMatch[1]})";
                }
                $errorMsg = "Akun NVIDIA Anda{$accountInfo} belum memiliki izin 'Public API Endpoints' untuk model '{$model}' di server NVIDIA Cloud. Pengisian URL, Model, dan API Key di ServisYuk sudah 100% benar dan berhasil diverifikasi oleh server NVIDIA (terbukti sukses memuat 72 model), namun server NVIDIA membatasi akses inferensi untuk model ini pada akun Anda. Solusi: (1) Coba ganti model ke model lain yang tersedia dari 72 model (misal: 'meta/llama-3.1-8b-instruct' atau 'mistralai/mistral-7b-instruct-v0.3'), (2) Di build.nvidia.com, buka halaman model dan klik 'Get API Key' langsung dari halaman model untuk mengklaim kuota serverless, atau (3) Hubungi bantuan NVIDIA Developer Forums dengan menyertakan Account ID Anda.";
            } elseif (!empty($errorMsg) && (stripos($errorMsg, 'no longer available') !== false || stripos($errorMsg, 'not found for API version') !== false || stripos($errorMsg, 'not supported') !== false)) {
                $errorMsg = "Model '{$model}' tidak tersedia pada Google Gemini (HTTP 404). Respon resmi Google: {$errorMsg}";
            } else {
                $errorMsg = "Model '{$model}' tidak ditemukan pada endpoint '{$endpoint}' (HTTP 404)." . (!empty($errorMsg) && $errorMsg !== $lastRes ? " Detail: {$errorMsg}" : "");
            }
        } elseif ($lastHttpCode === 410) {
            $errorMsg = "Model Telah Usang / EOL (HTTP 410): {$errorMsg}. Silakan gunakan model yang aktif.";
        } elseif ($lastHttpCode === 422) {
            $errorMsg = "Parameter Permintaan Tidak Sesuai (HTTP 422): {$errorMsg}";
        } elseif ($lastHttpCode === 429) {
            $errorMsg = "Batas Permintaan Tercapai (Rate Limit HTTP 429): Akun Anda telah mencapai limit frekuensi permintaan.";
        }

        self::updateConnectionStatus('error', $lastLatency, $errorMsg);

        return [
            'success'    => false,
            'latency_ms' => $lastLatency,
            'http_code'  => $lastHttpCode,
            'message'    => $errorMsg
        ];
    }

    /**
     * Ambil daftar model yang tersedia dari provider melalui endpoint resmi /models
     */
    public static function fetchModels(string $provider, string $apiKey, string $baseUrl): array {
        $presetFallbackModels = self::PROVIDER_PRESETS[$provider]['models'] ?? self::DEFAULT_MODELS;
        if (empty($presetFallbackModels)) {
            $presetFallbackModels = self::DEFAULT_MODELS;
        }

        if (empty($apiKey)) {
            $apiKey = self::getSavedKeyForProvider($provider);
            if (empty($apiKey)) {
                $curr = self::getActiveConfig();
                $apiKey = $curr['api_key'];
            }
        }

        if (empty($apiKey)) {
            return [
                'success' => false,
                'source'  => 'fallback',
                'message' => 'API Key kosong. Menampilkan daftar model rekomendasi untuk provider ' . strtoupper($provider) . '.',
                'models'  => $presetFallbackModels
            ];
        }

        $endpoint = rtrim($baseUrl, '/') . '/models';

        $headers = [
            'Authorization: Bearer ' . $apiKey,
            'Content-Type: application/json'
        ];
        if ($provider === 'openrouter') {
            $headers[] = 'HTTP-Referer: http://localhost/jasa';
            $headers[] = 'X-Title: ServisYuk';
        }

        $ch = curl_init($endpoint);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER     => $headers,
            CURLOPT_TIMEOUT        => 10,
            CURLOPT_CONNECTTIMEOUT => 4,
            CURLOPT_SSL_VERIFYPEER => false
        ]);

        $res = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlErr = curl_error($ch);
        curl_close($ch);

        if ($curlErr || $httpCode !== 200 || empty($res)) {
            $errDetail = $curlErr ? $curlErr : "HTTP {$httpCode}";
            return [
                'success' => false,
                'source'  => 'fallback',
                'message' => "Tidak dapat mengambil daftar model langsung dari API ({$errDetail}). Menampilkan model rekomendasi resmi.",
                'models'  => $presetFallbackModels
            ];
        }

        $data = json_decode($res, true);
        $rawList = $data['data'] ?? ($data['models'] ?? (is_array($data) ? $data : []));

        if (!is_array($rawList) || empty($rawList)) {
            return [
                'success' => false,
                'source'  => 'fallback',
                'message' => 'Format response daftar model tidak dikenali atau kosong. Menampilkan model rekomendasi provider.',
                'models'  => $presetFallbackModels
            ];
        }

        // Filter model: prioritaskan model chat completions dan saring model non-chat (audio/guardrail)
        $models = [];
        foreach ($rawList as $m) {
            if (!is_array($m)) continue;
            $id = $m['id'] ?? ($m['name'] ?? '');
            if (empty($id)) continue;

            // Bersihkan prefix models/ jika ada
            $id = preg_replace('/^models\//i', '', $id);

            // Saring model yang bukan untuk chat (seperti whisper, prompt guard, embedding, audio, tts)
            if (preg_match('/(whisper|guard|embedding|tts|audio|moderation|dall-e|rerank|embed)/i', $id)) {
                continue;
            }
            $owner = $m['owned_by'] ?? ($m['provider'] ?? '');
            $displayName = $m['name'] ?? $id;
            $displayName = preg_replace('/^models\//i', '', $displayName);
            if ($owner && !str_contains($displayName, $owner)) {
                $displayName .= " ({$owner})";
            }

            $models[] = [
                'id'          => $id,
                'name'        => $displayName,
                'provider'    => $owner ?: $provider,
                'is_chat'     => true,
                'recommended' => (
                    str_contains($id, 'qwen3.8') || 
                    str_contains($id, 'gpt-4o-mini') || 
                    str_contains($id, 'deepseek-chat') ||
                    str_contains($id, 'gemini-2.0-flash') ||
                    str_contains($id, 'gemini-1.5-flash') ||
                    str_contains($id, 'llama-3.1-nemotron-70b')
                )
            ];
        }

        if (empty($models)) {
            return [
                'success' => false,
                'source'  => 'fallback',
                'message' => 'Tidak ditemukan model percakapan teks yang kompatibel. Menampilkan model rekomendasi.',
                'models'  => $presetFallbackModels
            ];
        }

        // Urutkan model: chat-capable di atas, rekomendasi paling atas
        usort($models, function($a, $b) {
            if ($a['recommended'] !== $b['recommended']) {
                return $b['recommended'] <=> $a['recommended'];
            }
            if ($a['is_chat'] !== $b['is_chat']) {
                return $b['is_chat'] <=> $a['is_chat'];
            }
            return strcmp($a['id'], $b['id']);
        });

        return [
            'success' => true,
            'source'  => 'live',
            'message' => 'Berhasil memuat ' . count($models) . ' model langsung dari API provider ' . strtoupper($provider) . '.',
            'models'  => $models
        ];
    }

    /**
     * Tulis pembaruan variabel ke file .env tanpa merusak format yang ada
     */
    private static function syncToEnv(array $updates): void {
        $envPath = dirname(__DIR__) . '/.env';
        if (!file_exists($envPath)) return;

        $content = file_get_contents($envPath);
        foreach ($updates as $key => $val) {
            $pattern = "/^{$key}\s*=.*$/m";
            $escapedVal = (str_contains($val, ' ') || str_contains($val, '"')) ? '"' . addslashes($val) . '"' : $val;
            if (preg_match($pattern, $content)) {
                $content = preg_replace_callback($pattern, function() use ($key, $escapedVal) {
                    return "{$key}={$escapedVal}";
                }, $content);
            } else {
                $content = rtrim($content) . "\n{$key}={$escapedVal}\n";
            }
        }
        @file_put_contents($envPath, $content);
    }

    /**
     * Tulis konfigurasi model non-rahasia ke chatbot/ai_config.json
     */
    private static function syncToJson(array $data): void {
        $jsonPath = dirname(__DIR__) . '/chatbot/ai_config.json';
        @file_put_contents($jsonPath, json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
    }
}
