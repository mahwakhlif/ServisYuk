<?php
// ==========================================================
// Layout: Floating AI Chatbot Widget (Node.js + Groq / Nous Research)
// ==========================================================
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$currentChatUser = null;
$sessionUserId = $_SESSION['user_id'] ?? (class_exists('Auth') && Auth::check() ? Auth::id() : null);

if ($sessionUserId) {
    try {
        $dbInstance = Database::getInstance();
        $u = $dbInstance->fetch("SELECT * FROM users WHERE id = ? LIMIT 1", [$sessionUserId]);
        if ($u) {
            $currentChatUser = [
                'logged_in' => true,
                'id' => (int)$u['id'],
                'name' => $u['name'],
                'email' => $u['email'],
                'role' => $u['role'],
                'phone' => $u['phone'] ?? '',
                'address' => $u['address'] ?? ''
            ];

            // If technician, fetch specialization & status
            if ($u['role'] === 'technician') {
                $tData = $dbInstance->fetch("SELECT * FROM technicians WHERE user_id = ? LIMIT 1", [$u['id']]);
                if ($tData) {
                    $currentChatUser['technician_id'] = $tData['id'];
                    $currentChatUser['specialization'] = $tData['specialization'] ?? '';
                    $currentChatUser['experience'] = $tData['experience'] ?? '';
                    $currentChatUser['availability'] = $tData['availability'] ?? '';
                }
            }

            // If customer, fetch recent requests
            if ($u['role'] === 'customer') {
                $reqs = $dbInstance->fetchAll("SELECT request_code, appliance_name, brand, complaint, status, schedule FROM service_requests WHERE customer_id = ? ORDER BY id DESC LIMIT 5", [$u['id']]);
                $currentChatUser['service_requests'] = $reqs ?: [];
            }
        }
    } catch (Exception $e) {}
}

$isUserLoggedIn = !empty($currentChatUser['logged_in']);
$chatUserDataJson = json_encode($currentChatUser ?: ['logged_in' => false], JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT);
?>

<!-- Serialized User Data for Chatbot Client -->
<script id="servisyuk-chat-user-data" type="application/json">
<?= $chatUserDataJson ?>
</script>
<script>
window.SERVISYUK_USER = <?= json_encode($currentChatUser ?: ['logged_in' => false]) ?>;
window.BASE_URL = <?= json_encode(BASE_URL) ?>;
</script>

<!-- Floating Chatbot Toggle Button -->
<button id="servisyuk-chat-toggle" aria-label="Buka Chatbot AI ServisYuk" title="Konsultasi & Tanya AI ServisYuk">
    <span class="chat-toggle-pulse"></span>
    <i class="fa-solid fa-comments chat-open-icon"></i>
    <i class="fa-solid fa-xmark chat-close-icon"></i>
    <span class="chat-toggle-badge">
        <i class="fa-solid fa-sparkles" style="color: #60a5fa; margin-right: 4px;"></i> Tanya AI ServisYuk
    </span>
</button>

<!-- Chatbot Window -->
<div id="servisyuk-chat-window" role="dialog" aria-label="Jendela Chatbot ServisYuk AI"
     data-logged-in="<?= $isUserLoggedIn ? '1' : '0' ?>"
     data-user-name="<?= htmlspecialchars($currentChatUser['name'] ?? '') ?>"
     data-user-role="<?= htmlspecialchars($currentChatUser['role'] ?? '') ?>">
    <!-- Header -->
    <div class="chat-header">
        <div class="chat-header-info">
            <div class="chat-avatar-wrapper">
                <i class="fa-solid fa-headset"></i>
                <span class="chat-status-dot" title="Online"></span>
            </div>
            <div class="chat-header-text">
                <h4>ServisYuk AI</h4>
                <p>
                    <i class="fa-solid fa-circle" style="font-size: 7px; color: #10b981;"></i>
                    Online • Respon Cepat (Groq)
                </p>
            </div>
        </div>
        <div class="chat-header-actions">
            <button id="chat-clear-btn" class="chat-header-btn" title="Bersihkan Chat">
                <i class="fa-solid fa-trash-can"></i>
            </button>
            <button id="chat-close-btn" class="chat-header-btn" title="Tutup Chat">
                <i class="fa-solid fa-minus"></i>
            </button>
        </div>
    </div>

    <!-- Messages Container -->
    <div id="chat-messages-container" class="chat-messages">
        <!-- Messages will be injected by JavaScript -->
    </div>

    <!-- Quick Suggestion Chips -->
    <div id="chat-quick-chips" class="chat-chips-container">
        <button type="button" class="chat-chip" data-prompt="Siapa saya?">
            👤 Siapa Saya?
        </button>
        <?php if ($isUserLoggedIn && $currentChatUser['role'] === 'customer'): ?>
            <button type="button" class="chat-chip" data-prompt="Apa saja tiket servis dan pesanan saya?">
                📋 Tiket Saya
            </button>
        <?php endif; ?>
        <?php if ($isUserLoggedIn && $currentChatUser['role'] === 'admin'): ?>
            <button type="button" class="chat-chip" data-prompt="Tampilkan ringkasan status semua pesanan servis dan teknisi di database">
                📊 Ringkasan Admin
            </button>
        <?php endif; ?>
        <?php if ($isUserLoggedIn && $currentChatUser['role'] === 'technician'): ?>
            <button type="button" class="chat-chip" data-prompt="Apa saja tugas dan penugasan servis saya saat ini?">
                🛠️ Tugas Saya
            </button>
        <?php endif; ?>
        <button type="button" class="chat-chip" data-prompt="Berapa estimasi biaya servis dan cuci AC Split?">
            ❄️ Biaya Cuci AC
        </button>
        <button type="button" class="chat-chip" data-prompt="Kulkas bagian bawah tidak dingin, apa penyebabnya?">
            🧊 Kulkas Rusak
        </button>
        <button type="button" class="chat-chip" data-prompt="Mesin cuci tidak mau berputar saat mengeringkan pakaian">
            🧺 Mesin Cuci Macet
        </button>
        <button type="button" class="chat-chip" data-prompt="Sebutkan semua data tabel dan teknisi yang ada di database ServisYuk">
            🗄️ Cek Database
        </button>
    </div>

    <!-- Input Footer -->
    <div class="chat-footer">
        <div class="chat-input-wrapper">
            <input type="text" id="chat-input-field" class="chat-input" placeholder="Tanya keluhan atau tarif perbaikan..." autocomplete="off">
        </div>
        <button id="chat-send-trigger" class="chat-send-btn" title="Kirim Pesan">
            <i class="fa-solid fa-paper-plane"></i>
        </button>
    </div>
</div>
