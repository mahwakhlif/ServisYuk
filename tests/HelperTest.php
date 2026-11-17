<?php
// ==========================================================
// Unit Test: Helper Utilities
// ServisYuk Platform Testing Suite
// ==========================================================

use PHPUnit\Framework\TestCase;

class HelperTest extends TestCase {

    public function testFormatRupiahWithStandardAmount(): void {
        $result = Helper::formatRupiah(150000);
        $this->assertEquals('Rp 150.000', $result);
    }

    public function testFormatRupiahWithZeroAndNull(): void {
        $this->assertEquals('Rp 0', Helper::formatRupiah(0));
        $this->assertEquals('Rp 0', Helper::formatRupiah(null));
    }

    public function testFormatRupiahWithStringNumeric(): void {
        $this->assertEquals('Rp 75.500', Helper::formatRupiah('75500'));
    }

    public function testFormatDateIndoWithValidDate(): void {
        // 2026-11-17 adalah hari Selasa
        $formatted = Helper::formatDateIndo('2026-11-17');
        $this->assertStringContainsString('17 November 2026', $formatted);
        $this->assertStringContainsString('Selasa', $formatted);
    }

    public function testFormatDateIndoWithTime(): void {
        $formatted = Helper::formatDateIndo('2026-11-17 14:30:00', true);
        $this->assertStringContainsString('14:30 WIB', $formatted);
    }

    public function testFormatDateIndoWithEmptyOrNull(): void {
        $this->assertEquals('-', Helper::formatDateIndo(null));
        $this->assertEquals('-', Helper::formatDateIndo(''));
    }

    public function testGetStatusInfoReturnsExpectedArrayStructure(): void {
        $pending = Helper::getStatusInfo('pending');
        $this->assertIsArray($pending);
        $this->assertArrayHasKey('label', $pending);
        $this->assertArrayHasKey('class', $pending);
        $this->assertArrayHasKey('icon', $pending);
        $this->assertArrayHasKey('step', $pending);
        $this->assertArrayHasKey('color', $pending);
        $this->assertArrayHasKey('description', $pending);

        $this->assertEquals('Menunggu', $pending['label']);
        $this->assertEquals(1, $pending['step']);
    }

    public function testGetStatusInfoDifferentStatuses(): void {
        $inprogress = Helper::getStatusInfo('inprogress');
        $this->assertEquals('Dalam Perbaikan', $inprogress['label']);
        $this->assertEquals(4, $inprogress['step']);

        $completed = Helper::getStatusInfo('completed');
        $this->assertEquals('Selesai', $completed['label']);
        $this->assertEquals(5, $completed['step']);

        $cancelled = Helper::getStatusInfo('cancelled');
        $this->assertEquals('Dibatalkan', $cancelled['label']);
        $this->assertEquals(0, $cancelled['step']);

        $unknown = Helper::getStatusInfo('unknown_status_xyz');
        $this->assertEquals('unknown_status_xyz', $unknown['label']);
    }

    public function testGenerateBookingCodeFormat(): void {
        $code = Helper::generateBookingCode();
        $today = date('Ymd');
        $this->assertMatchesRegularExpression('/^SRV-' . $today . '-[A-F0-9]{4}$/', $code);
    }

    public function testGenerateInvoiceNumberFormat(): void {
        $invoice = Helper::generateInvoiceNumber();
        $today = date('Ymd');
        $this->assertMatchesRegularExpression('/^INV\/' . $today . '\/[A-F0-9]{4}$/', $invoice);
    }

    public function testSanitizeInput(): void {
        $dirty = "<script>alert('xss')</script>";
        $clean = Helper::sanitize($dirty);
        $this->assertEquals("&lt;script&gt;alert(&#039;xss&#039;)&lt;/script&gt;", $clean);
        $this->assertEquals('', Helper::sanitize(null));
    }
}
