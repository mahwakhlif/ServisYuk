<?php
$pageTitle = 'Katalog Layanan & Tarif Transparan - ServisYuk';
require_once __DIR__ . '/../layouts/header.php';
require_once __DIR__ . '/../layouts/navbar.php';
?>

<div class="container" style="padding: 40px 24px 80px;">
    <div style="text-align: center; max-width: 760px; margin: 0 auto 50px;">
        <span class="hero-tag"><i class="fa-solid fa-list-check"></i> Tarif Transparan & Pasti</span>
        <h1 style="font-size: 2.6rem; font-weight: 800; margin-bottom: 12px;">Katalog Layanan & Estimasi Tarif</h1>
        <p style="color: var(--text-muted); font-size: 1.05rem;">
            Seluruh biaya jasa di ServisYuk transparan tanpa biaya tersembunyi. Setiap pengerjaan dilindungi garansi servis resmi hingga 30-60 hari.
        </p>
    </div>

    <?php foreach ($categories as $cat): ?>
        <div id="cat-<?= $cat['id'] ?>" class="card" style="margin-bottom: 40px; border-left: 4px solid var(--primary);">
            <div style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 16px; margin-bottom: 24px; padding-bottom: 16px; border-bottom: 1px solid var(--border-color);">
                <div style="display: flex; align-items: center; gap: 16px;">
                    <div class="category-icon-box" style="margin-bottom: 0;">
                        <i class="<?= htmlspecialchars($cat['icon']) ?>"></i>
                    </div>
                    <div>
                        <h2 style="font-size: 1.5rem; margin-bottom: 4px;"><?= htmlspecialchars($cat['name']) ?></h2>
                        <p style="color: var(--text-muted); font-size: 0.9rem;"><?= htmlspecialchars($cat['description']) ?></p>
                    </div>
                </div>
                <div style="text-align: right;">
                    <span class="badge badge-primary" style="font-size: 0.85rem; padding: 6px 12px;">
                        Biaya Kunjungan & Inspeksi: <?= Helper::formatRupiah($cat['base_inspection_fee']) ?>
                    </span>
                </div>
            </div>

            <div class="table-responsive">
                <table class="custom-table">
                    <thead>
                        <tr>
                            <th>Jenis Layanan Spesifik</th>
                            <th>Deskripsi Pengerjaan</th>
                            <th>Estimasi Biaya Jasa</th>
                            <th>Masa Garansi</th>
                            <th style="text-align: right;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        $catServices = array_filter($services, fn($s) => $s['category_id'] == $cat['id']);
                        if (!empty($catServices)):
                            foreach ($catServices as $s): 
                        ?>
                            <tr>
                                <td style="font-weight: 600;"><?= htmlspecialchars($s['name']) ?></td>
                                <td style="color: var(--text-muted); font-size: 0.88rem; max-width: 320px;"><?= htmlspecialchars($s['description'] ?? '-') ?></td>
                                <td style="font-weight: 700; color: var(--primary);">
                                    <?= Helper::formatRupiah($s['estimated_price_min']) ?> - <?= Helper::formatRupiah($s['estimated_price_max']) ?>
                                </td>
                                <td>
                                    <span class="badge badge-success">
                                        <i class="fa-solid fa-shield-halved"></i> <?= $s['warranty_days'] ?> Hari
                                    </span>
                                </td>
                                <td style="text-align: right;">
                                    <a href="<?= BASE_URL ?>/index.php?page=customer_booking_create&category_id=<?= $cat['id'] ?>&service_id=<?= $s['id'] ?>" class="btn btn-primary btn-sm">
                                        Pesan Layanan
                                    </a>
                                </td>
                            </tr>
                        <?php 
                            endforeach;
                        else:
                        ?>
                            <tr>
                                <td colspan="5" style="text-align: center; color: var(--text-muted); padding: 24px;">
                                    Layanan kustom tersedia berdasarkan hasil inspeksi teknisi di tempat.
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <div style="display: flex; justify-content: flex-end; gap: 12px; margin-top: 20px;">
                <a href="<?= BASE_URL ?>/index.php?page=ai_diagnostic&category=<?= urlencode($cat['name']) ?>" class="btn btn-outline btn-sm">
                    <i class="fa-solid fa-wand-magic-sparkles"></i> Diagnosa AI untuk <?= htmlspecialchars($cat['name']) ?>
                </a>
                <a href="<?= BASE_URL ?>/index.php?page=customer_booking_create&category_id=<?= $cat['id'] ?>" class="btn btn-primary btn-sm">
                    <i class="fa-solid fa-calendar-plus"></i> Pesan Teknisi Kategori Ini
                </a>
            </div>
        </div>
    <?php endforeach; ?>
</div>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
