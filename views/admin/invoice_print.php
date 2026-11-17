<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Faktur Invoice #<?= htmlspecialchars($report['invoice_number'] ?? ($booking['request_code'] ?? 'SRV')) ?> - ServisYuk</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@400;600;700;800&family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background-color: #f8fafc;
            color: #0f172a;
            padding: 40px 20px;
        }
        .invoice-wrapper {
            max-width: 800px;
            margin: 0 auto;
            background: #ffffff;
            border-radius: 12px;
            padding: 40px 48px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.08);
            border: 1px solid #e2e8f0;
        }
        .invoice-header {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            padding-bottom: 24px;
            border-bottom: 2px solid #e2e8f0;
            margin-bottom: 24px;
        }
        .brand-title {
            font-family: 'Outfit', sans-serif;
            font-size: 1.8rem;
            font-weight: 800;
            color: #4f46e5;
        }
        .brand-subtitle {
            font-size: 0.85rem;
            color: #64748b;
            margin-top: 4px;
            line-height: 1.4;
        }
        .invoice-meta {
            text-align: right;
        }
        .invoice-title {
            font-family: 'Outfit', sans-serif;
            font-size: 1.8rem;
            font-weight: 800;
            color: #0f172a;
        }
        .meta-line {
            font-size: 0.88rem;
            color: #64748b;
            margin-top: 3px;
        }
        .meta-line strong {
            color: #0f172a;
        }
        .customer-tech-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 30px;
            margin-bottom: 30px;
        }
        .info-box-title {
            font-size: 0.75rem;
            font-weight: 700;
            color: #4f46e5;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            margin-bottom: 6px;
        }
        .info-box-name {
            font-weight: 700;
            font-size: 1.05rem;
            margin-bottom: 4px;
        }
        .info-box-text {
            font-size: 0.88rem;
            color: #64748b;
            line-height: 1.4;
        }
        .invoice-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 28px;
        }
        .invoice-table th {
            background: #f1f5f9;
            padding: 12px 16px;
            font-size: 0.82rem;
            font-weight: 700;
            text-transform: uppercase;
            color: #475569;
            border-bottom: 1px solid #cbd5e1;
            text-align: left;
        }
        .invoice-table td {
            padding: 14px 16px;
            border-bottom: 1px solid #e2e8f0;
            font-size: 0.92rem;
        }
        .invoice-totals {
            margin-left: auto;
            width: 320px;
            margin-bottom: 32px;
        }
        .total-row {
            display: flex;
            justify-content: space-between;
            padding: 6px 0;
            font-size: 0.9rem;
            color: #64748b;
        }
        .total-row.grand-total {
            border-top: 2px solid #0f172a;
            padding-top: 10px;
            font-size: 1.2rem;
            font-weight: 800;
            color: #0f172a;
        }
        .paid-stamp {
            display: inline-block;
            padding: 6px 18px;
            border: 2px solid #10b981;
            color: #10b981;
            border-radius: 8px;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 0.08em;
            font-size: 1rem;
            transform: rotate(-5deg);
        }
        .unpaid-stamp {
            display: inline-block;
            padding: 6px 18px;
            border: 2px solid #f59e0b;
            color: #f59e0b;
            border-radius: 8px;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 0.08em;
            font-size: 1rem;
            transform: rotate(-5deg);
        }
        .invoice-footer {
            padding-top: 24px;
            border-top: 1px solid #e2e8f0;
            display: flex;
            align-items: center;
            justify-content: space-between;
            font-size: 0.85rem;
            color: #94a3b8;
        }
        .print-btn-bar {
            text-align: center;
            margin-bottom: 24px;
        }
        .btn {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 10px 24px;
            font-weight: 600;
            border-radius: 8px;
            border: none;
            cursor: pointer;
            text-decoration: none;
            font-size: 0.95rem;
        }
        .btn-primary {
            background: #4f46e5;
            color: #ffffff;
        }
        @media print {
            body { background: #ffffff; padding: 0; }
            .invoice-wrapper { box-shadow: none; border: none; padding: 20px; }
            .print-btn-bar { display: none; }
        }
    </style>
</head>
<body>

<div class="print-btn-bar">
    <button onclick="window.print()" class="btn btn-primary">
        <i class="fa-solid fa-print"></i> Cetak / Simpan sebagai PDF
    </button>
    <a href="javascript:window.close()" class="btn" style="background:#e2e8f0; color:#334155; margin-left:10px;">
        Tutup Jendela
    </a>
</div>

<div class="invoice-wrapper">
    <div class="invoice-header">
        <div>
            <div class="brand-title"><i class="fa-solid fa-screwdriver-wrench"></i> ServisYuk</div>
            <div class="brand-subtitle">
                PT SERVIS RUMAH PINTAR INDONESIA<br>
                Jl. Kebon Jeruk Raya No. 10, Jakarta Barat<br>
                WhatsApp: 0812-8888-9999 &bull; Email: bantuan@servisyuk.id
            </div>
        </div>

        <div class="invoice-meta">
            <div class="invoice-title">FAKTUR INVOICE</div>
            <div class="meta-line">No. Faktur: <strong><?= htmlspecialchars($report['invoice_number'] ?? 'INV-' . $booking['id']) ?></strong></div>
            <div class="meta-line">No. Tiket: <strong><?= htmlspecialchars($booking['request_code']) ?></strong></div>
            <div class="meta-line">Tanggal Selesai: <strong><?= Helper::formatDateIndo($report['completed_at'] ?? $booking['created_at']) ?></strong></div>
            <div style="margin-top: 10px;">
                <?php if (($report['payment_status'] ?? '') === 'paid'): ?>
                    <div class="paid-stamp">LUNAS / PAID</div>
                <?php else: ?>
                    <div class="unpaid-stamp">BELUM LUNAS</div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <div class="customer-tech-grid">
        <div>
            <div class="info-box-title">DITAGIHKAN KEPADA:</div>
            <div class="info-box-name"><?= htmlspecialchars($booking['customer_name'] ?? 'Pelanggan') ?></div>
            <div class="info-box-text">
                <?= htmlspecialchars($booking['customer_phone'] ?? '-') ?><br>
                <?= nl2br(htmlspecialchars($booking['address'])) ?>
            </div>
        </div>

        <div>
            <div class="info-box-title">TEKNISI PENANGGUNG JAWAB:</div>
            <div class="info-box-name"><?= htmlspecialchars($booking['assigned_technician_name'] ?? ($booking['technician_name'] ?? 'Teknisi ServisYuk')) ?></div>
            <div class="info-box-text">
                <?= htmlspecialchars($booking['technician_specialization'] ?? 'Spesialis Perbaikan') ?><br>
                Kontak: <?= htmlspecialchars($booking['assigned_technician_phone'] ?? ($booking['technician_phone'] ?? '-')) ?>
            </div>
        </div>
    </div>

    <!-- Table of Charges -->
    <table class="invoice-table">
        <thead>
            <tr>
                <th>Deskripsi Layanan & Tindakan Perbaikan</th>
                <th>Peralatan</th>
                <th style="text-align: right;">Jumlah (IDR)</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>
                    <strong>Jasa Servis & Perbaikan</strong><br>
                    <span style="font-size: 0.82rem; color: #64748b;">
                        <?= htmlspecialchars($report['repair_action'] ?? ($booking['complaint'] ?? 'Tindakan perbaikan')) ?>
                    </span>
                </td>
                <td><?= htmlspecialchars($booking['appliance_name']) ?> (<?= htmlspecialchars($booking['brand'] ?: '-') ?>)</td>
                <td style="text-align: right; font-weight: 600;"><?= Helper::formatRupiah($report['service_fee'] ?? ($booking['service_estimated_price'] ?? 75000)) ?></td>
            </tr>
            <?php if (!empty($report['sparepart_fee']) && $report['sparepart_fee'] > 0): ?>
                <tr>
                    <td>
                        <strong>Suku Cadang / Sparepart Pengganti</strong><br>
                        <span style="font-size: 0.82rem; color: #64748b;"><?= htmlspecialchars($report['replaced_component'] ?: 'Sparepart resmi terpasang') ?></span>
                    </td>
                    <td>Suku Cadang Bergaransi</td>
                    <td style="text-align: right; font-weight: 600;"><?= Helper::formatRupiah($report['sparepart_fee']) ?></td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>

    <div class="invoice-totals">
        <div class="total-row">
            <span>Biaya Jasa:</span>
            <span><?= Helper::formatRupiah($report['service_fee'] ?? ($booking['service_estimated_price'] ?? 75000)) ?></span>
        </div>
        <div class="total-row">
            <span>Biaya Sparepart:</span>
            <span><?= Helper::formatRupiah($report['sparepart_fee'] ?? 0) ?></span>
        </div>
        <div class="total-row grand-total">
            <span>Total Pembayaran:</span>
            <span style="color:#4f46e5;"><?= Helper::formatRupiah($report['total_amount'] ?? ($booking['service_estimated_price'] ?? 75000)) ?></span>
        </div>
    </div>

    <div style="background: #f8fafc; border: 1px dashed #cbd5e1; border-radius: 8px; padding: 14px 18px; margin-bottom: 28px; font-size: 0.85rem; line-height: 1.5;">
        <strong>Informasi Pembayaran & Rekening Resmi:</strong><br>
        Bank: <strong>BCA</strong> &bull; No. Rekening: <strong>8830-192-881</strong> &bull; A.N: <strong>PT SERVIS RUMAH PINTAR</strong><br>
        <em>*Pekerjaan ini dilindungi garansi servis resmi selama 30 hari terhitung sejak tanggal faktur diterbitkan.</em>
    </div>

    <div class="invoice-footer">
        <div>Dicetak otomatis oleh Sistem ServisYuk AI pada <?= date('d/m/Y H:i') ?> WIB</div>
        <div>Terima kasih atas kepercayaan Anda!</div>
    </div>
</div>

</body>
</html>
