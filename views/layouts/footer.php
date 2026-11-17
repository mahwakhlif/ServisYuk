<?php
// ==========================================================
// Layout: Global Footer
// ==========================================================
?>
<footer class="footer">
    <div class="container">
        <div class="footer-grid">
            <div class="footer-brand">
                <h3>ServisYuk <span style="font-size:0.8rem; background:var(--accent-gradient); padding:2px 8px; border-radius:4px; vertical-align:middle;">AI Powered</span></h3>
                <p>Platform layanan jasa perbaikan peralatan rumah tangga terpercaya dengan integrasi AI Diagnostic Assistant untuk estimasi akurat, cepat, dan bergaransi.</p>
                <div style="margin-top: 16px; display:flex; gap:12px; font-size:1.2rem;">
                    <a href="#" style="color:#94a3b8;"><i class="fa-brands fa-whatsapp"></i></a>
                    <a href="#" style="color:#94a3b8;"><i class="fa-brands fa-instagram"></i></a>
                    <a href="#" style="color:#94a3b8;"><i class="fa-brands fa-facebook"></i></a>
                </div>
            </div>

            <div class="footer-col">
                <h4>Layanan Unggulan</h4>
                <ul class="footer-links">
                    <li><a href="<?= BASE_URL ?>/index.php?page=services#ac">Servis & Cuci AC</a></li>
                    <li><a href="<?= BASE_URL ?>/index.php?page=services#kulkas">Perbaikan Kulkas</a></li>
                    <li><a href="<?= BASE_URL ?>/index.php?page=services#mesin-cuci">Servis Mesin Cuci</a></li>
                    <li><a href="<?= BASE_URL ?>/index.php?page=services#microwave">Microwave & Oven</a></li>
                    <li><a href="<?= BASE_URL ?>/index.php?page=services#tv">Smart TV Elektronik</a></li>
                </ul>
            </div>

            <div class="footer-col">
                <h4>Navigasi Cepat</h4>
                <ul class="footer-links">
                    <li><a href="<?= BASE_URL ?>/index.php?page=ai_diagnostic"><i class="fa-solid fa-wand-magic-sparkles"></i> AI Diagnosa Pintar</a></li>
                    <li><a href="<?= BASE_URL ?>/index.php?page=tracking"><i class="fa-solid fa-location-dot"></i> Lacak Status Tiket</a></li>
                    <li><a href="<?= BASE_URL ?>/index.php?page=login"><i class="fa-solid fa-right-to-bracket"></i> Masuk / Daftar</a></li>
                    <li><a href="<?= BASE_URL ?>/index.php?page=services"><i class="fa-solid fa-list-check"></i> Daftar Tarif Transparan</a></li>
                </ul>
            </div>

            <div class="footer-col">
                <h4>Pusat Bantuan</h4>
                <ul class="footer-links" style="font-size:0.88rem;">
                    <li><i class="fa-solid fa-phone" style="color:var(--primary); margin-right:6px;"></i> 0812-8888-9999</li>
                    <li><i class="fa-solid fa-envelope" style="color:var(--primary); margin-right:6px;"></i> bantuan@servisyuk.id</li>
                    <li><i class="fa-solid fa-clock" style="color:var(--primary); margin-right:6px;"></i> Setiap Hari (08:00 - 20:00 WIB)</li>
                    <li><i class="fa-solid fa-shield-halved" style="color:var(--success); margin-right:6px;"></i> Garansi Perbaikan 30 Hari</li>
                </ul>
            </div>
        </div>

        <div class="footer-bottom">
            <p>&copy; <?= date('Y') ?> ServisYuk. Seluruh Hak Cipta Dilindungi. Sistem Jasa Perbaikan Rumah Tangga Berbasis Web + AI.</p>
        </div>
    </div>
</footer>

<!-- Bootstrap 5.3 JS Bundle -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>

<!-- Core JavaScript Scripts -->
<script src="<?= BASE_URL ?>/assets/js/main.js"></script>
<?php if (isset($extraScript)): ?>
    <script src="<?= BASE_URL ?>/assets/js/<?= $extraScript ?>"></script>
<?php endif; ?>

<!-- AI Chatbot Widget (Node.js & Groq) -->
<?php require_once __DIR__ . '/chatbot_widget.php'; ?>
<script src="<?= BASE_URL ?>/assets/js/chatbot.js?v=<?= file_exists(APP_ROOT . '/assets/js/chatbot.js') ? filemtime(APP_ROOT . '/assets/js/chatbot.js') : time() ?>"></script>
</body>
</html>
