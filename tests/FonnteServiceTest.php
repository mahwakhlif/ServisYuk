<?php
// ==========================================================
// Unit Test: FonnteService (WhatsApp Phone Formatter & Utilities)
// ServisYuk Platform Testing Suite
// ==========================================================

use PHPUnit\Framework\TestCase;

class FonnteServiceTest extends TestCase {

    public function testFormatPhoneNumberStandard08(): void {
        $result = FonnteService::formatPhoneNumber('081234567890');
        $this->assertEquals('6281234567890', $result);
    }

    public function testFormatPhoneNumberInternationalPlus62(): void {
        $result = FonnteService::formatPhoneNumber('+6281234567890');
        $this->assertEquals('6281234567890', $result);
    }

    public function testFormatPhoneNumberLeading8(): void {
        $result = FonnteService::formatPhoneNumber('81234567890');
        $this->assertEquals('6281234567890', $result);
    }

    public function testFormatPhoneNumberWithDashesAndSpaces(): void {
        $result = FonnteService::formatPhoneNumber('0812-3456-7890');
        $this->assertEquals('6281234567890', $result);

        $resultWithSpaces = FonnteService::formatPhoneNumber('  0812 3456 7890  ');
        $this->assertEquals('6281234567890', $resultWithSpaces);
    }

    public function testFormatPhoneNumberInvalidInputs(): void {
        $this->assertNull(FonnteService::formatPhoneNumber(null));
        $this->assertNull(FonnteService::formatPhoneNumber(''));
        $this->assertNull(FonnteService::formatPhoneNumber('abcde'));
        $this->assertNull(FonnteService::formatPhoneNumber('12345')); // Terlalu pendek
        $this->assertNull(FonnteService::formatPhoneNumber('0217654321')); // Bukan nomor HP 08...
    }

    public function testMaskPhoneNumber(): void {
        $masked = FonnteService::maskPhoneNumber('6281234567890');
        $this->assertStringStartsWith('62812', $masked);
        $this->assertStringEndsWith('890', $masked);
        $this->assertStringContainsString('*', $masked);

        $this->assertEquals('***', FonnteService::maskPhoneNumber('12345'));
    }
}
