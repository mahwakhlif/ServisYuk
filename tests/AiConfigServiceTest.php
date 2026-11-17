<?php
// ==========================================================
// Unit Test: AiConfigService (Security, Presets & Encryption)
// ServisYuk Platform Testing Suite
// ==========================================================

use PHPUnit\Framework\TestCase;

class AiConfigServiceTest extends TestCase {

    public function testProviderPresetsContainEssentialProviders(): void {
        $presets = AiConfigService::PROVIDER_PRESETS;
        $this->assertIsArray($presets);

        $expectedProviders = ['groq', 'gemini', 'nvidia', 'openai', 'deepseek', 'openrouter'];
        foreach ($expectedProviders as $provider) {
            $this->assertArrayHasKey($provider, $presets);
            $this->assertNotEmpty($presets[$provider]['name']);
            $this->assertNotEmpty($presets[$provider]['default_model']);
        }
    }

    public function testDefaultModelsListIsNotEmpty(): void {
        $models = AiConfigService::DEFAULT_MODELS;
        $this->assertIsArray($models);
        $this->assertNotEmpty($models);

        // Pastikan setiap model memiliki id dan name
        foreach ($models as $m) {
            $this->assertArrayHasKey('id', $m);
            $this->assertArrayHasKey('name', $m);
        }
    }

    public function testEncryptAndDecryptSecretRoundTrip(): void {
        $originalSecret = 'gsk_test_api_key_servisyuk_super_secret_12345';
        
        $encrypted = AiConfigService::encryptSecret($originalSecret);
        $this->assertNotEmpty($encrypted);
        $this->assertNotEquals($originalSecret, $encrypted);

        $decrypted = AiConfigService::decryptSecret($encrypted);
        $this->assertEquals($originalSecret, $decrypted);
    }

    public function testEncryptionRandomIvProducesDifferentOutputs(): void {
        $plain = 'same_secret_api_key';
        $enc1 = AiConfigService::encryptSecret($plain);
        $enc2 = AiConfigService::encryptSecret($plain);

        // Dua enkripsi dengan random IV harus menghasilkan cipher text berbeda
        $this->assertNotEquals($enc1, $enc2);

        // Namun keduanya harus berhasil didekripsi ke nilai asli
        $this->assertEquals($plain, AiConfigService::decryptSecret($enc1));
        $this->assertEquals($plain, AiConfigService::decryptSecret($enc2));
    }

    public function testDecryptEmptyOrInvalidSecret(): void {
        $this->assertEquals('', AiConfigService::decryptSecret(''));
        $this->assertEquals('', AiConfigService::decryptSecret('invalid_not_base64_string'));
        $this->assertEquals('', AiConfigService::decryptSecret(base64_encode('no_iv_delimiter')));
    }
}
