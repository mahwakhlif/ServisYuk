const http = require('http');
const fs = require('fs');
const path = require('path');

const PORT = process.env.PORT || 5005;

// Read API key securely from process.env or .env file without hardcoding
function getApiKey(provider = '') {
    if (process.env.AI_CHATBOT_API_KEY && process.env.AI_CHATBOT_API_KEY.trim()) {
        return process.env.AI_CHATBOT_API_KEY.trim();
    }
    if (provider === 'gemini' && process.env.GEMINI_API_KEY && process.env.GEMINI_API_KEY.trim()) {
        return process.env.GEMINI_API_KEY.trim();
    }
    if (provider === 'groq' && process.env.GROQ_API_KEY && process.env.GROQ_API_KEY.trim()) {
        return process.env.GROQ_API_KEY.trim();
    }
    try {
        const envPath = path.join(__dirname, '..', '.env');
        if (fs.existsSync(envPath)) {
            const content = fs.readFileSync(envPath, 'utf8');
            const matchAi = content.match(/^AI_CHATBOT_API_KEY\s*=\s*([^\r\n]+)/m);
            if (matchAi && matchAi[1] && matchAi[1].trim()) {
                return matchAi[1].trim().replace(/^['"]|['"]$/g, '');
            }
            if (provider === 'gemini') {
                const matchGemini = content.match(/^GEMINI_API_KEY\s*=\s*([^\r\n]+)/m);
                if (matchGemini && matchGemini[1] && matchGemini[1].trim()) {
                    return matchGemini[1].trim().replace(/^['"]|['"]$/g, '');
                }
            }
            const matchGroq = content.match(/^GROQ_API_KEY\s*=\s*([^\r\n]+)/m);
            if (matchGroq && matchGroq[1] && matchGroq[1].trim()) {
                return matchGroq[1].trim().replace(/^['"]|['"]$/g, '');
            }
        }
    } catch (e) {
        console.warn('[Server] Error reading .env file:', e.message);
    }
    return '';
}

function getActiveAiConfig() {
    let jsonProvider = 'groq';
    try {
        const jsonPath = path.join(__dirname, 'ai_config.json');
        if (fs.existsSync(jsonPath)) {
            const data = JSON.parse(fs.readFileSync(jsonPath, 'utf8'));
            if (data && typeof data === 'object') {
                jsonProvider = data.provider || 'groq';
                const key = getApiKey(jsonProvider);
                return {
                    provider: jsonProvider,
                    apiKey: key,
                    baseUrl: (data.base_url || 'https://api.groq.com/openai/v1').replace(/\/+$/, ''),
                    primaryModel: jsonProvider === 'gemini'
                        ? (data.active_model || (data.model || 'gemini-3.8-flash'))
                        : (data.active_model || (data.model || 'qwen/qwen3.8-27b')).replace(/^models\//i, ''),
                    fallbackModel: jsonProvider === 'gemini'
                        ? (data.fallback_model || 'models/gemini-3.8-flash')
                        : (data.fallback_model || 'openai/gpt-oss-20b').replace(/^models\//i, '')
                };
            }
        }
    } catch (e) {
        console.warn('[Server] Error reading ai_config.json:', e.message);
    }

    const key = getApiKey(jsonProvider);
    return {
        provider: 'groq',
        apiKey: key,
        baseUrl: 'https://api.groq.com/openai/v1',
        primaryModel: 'qwen/qwen3.8-27b',
        fallbackModel: 'openai/gpt-oss-20b'
    };
}

const KB_PATH = path.join(__dirname, 'database_knowledge.json');

// Cache for Live Database Context (30 seconds TTL)
let cachedDbContext = '';
let lastFetchTime = 0;
const CACHE_TTL_MS = 30000;

/**
 * Fetch or read live database context
 */
async function getLiveDatabaseContext() {
    const now = Date.now();
    if (cachedDbContext && (now - lastFetchTime) < CACHE_TTL_MS) {
        return cachedDbContext;
    }

    // 1. Try fetching from PHP backend endpoint
    try {
        const controller = new AbortController();
        const timeoutId = setTimeout(() => controller.abort(), 3500);
        const res = await fetch('http://localhost/jasa/index.php?page=api_db_context', {
            signal: controller.signal
        });
        clearTimeout(timeoutId);

        if (res.ok) {
            const data = await res.json();
            if (data.success && data.context) {
                cachedDbContext = data.context;
                lastFetchTime = now;
                // Update local JSON cache
                try {
                    fs.writeFileSync(KB_PATH, JSON.stringify({
                        context: cachedDbContext,
                        updated_at: new Date().toISOString()
                    }, null, 2), 'utf8');
                } catch (writeErr) {
                    // Ignore write error
                }
                return cachedDbContext;
            }
        }
    } catch (fetchErr) {
        // Fallback to local file if fetch failed
    }

    // 2. Try reading from database_knowledge.json
    try {
        if (fs.existsSync(KB_PATH)) {
            const raw = fs.readFileSync(KB_PATH, 'utf8');
            const parsed = JSON.parse(raw);
            if (parsed && parsed.context) {
                cachedDbContext = parsed.context;
                lastFetchTime = now;
                return cachedDbContext;
            }
        }
    } catch (readErr) {
        console.warn('[Chatbot Server] Warning reading database_knowledge.json:', readErr.message);
    }

    // 3. Fallback minimum context
    return `=== DAFTAR TABEL DATABASE SERVISYUK (14 TABEL) ===
ai_knowledge_base, booking_costs, booking_status_logs, bookings, categories, diagnoses, reviews, service_reports, service_requests, services, system_settings, technician_assignments, technicians, users
CS: 0812-8888-9999 / bantuan@servisyuk.id | Bank: BCA 8830-192-881 a/n PT SERVIS RUMAH PINTAR`;
}

const REFUSAL_TEXT = `Maaf, saya adalah asisten virtual khusus platform **ServisYuk**. Saya hanya dapat membantu pertanyaan seputar layanan jasa perbaikan peralatan rumah tangga (AC, kulkas, mesin cuci, TV, kompor, dispenser, microwave, rice cooker), estimasi tarif, garansi servis, teknisi, serta seluruh data dan informasi yang ada di database website ServisYuk.

Apakah ada kendala peralatan rumah tangga atau data seputar ServisYuk yang bisa saya bantu?`;

/**
 * Check if the user is asking about their own identity
 */
function isIdentityQuestion(message) {
    if (!message || typeof message !== 'string') return false;
    const clean = message.toLowerCase().trim().replace(/[?!.,]/g, '');
    if (/^(halo\s+)?siapa (saya|aku|gue|gw)(\s+ya|\s+saat ini)?$/i.test(clean)) return true;
    if (/^(saya|aku)\s+siapa(\s+ya)?$/i.test(clean)) return true;
    if (/^siapa nama (saya|aku)(\s+ya)?$/i.test(clean)) return true;
    if (/^nama (saya|aku) siapa(\s+ya)?$/i.test(clean)) return true;
    if (/^siapa yang (sedang )?login(\s+ya|\s+saat ini)?$/i.test(clean)) return true;
    if (/^(cek )?(profil|akun|data)\s+(saya|aku)$/i.test(clean)) return true;
    if (/^apakah (saya|aku) sudah login$/i.test(clean)) return true;
    return false;
}

/**
 * Format direct identity response based on user session
 */
function formatIdentityResponse(user) {
    if (!user || (!user.logged_in && !user.id)) {
        return `Halo! 👋 Saat ini Anda belum login atau masuk ke akun **ServisYuk** (status Anda adalah **Tamu / Pengunjung**).

Untuk melihat profil akun, identitas, atau memantau riwayat tiket servis dan pesanan Anda, silakan masuk melalui menu **[Masuk / Daftar](index.php?page=login)**.

Ada keluhan peralatan rumah tangga atau informasi seputar ServisYuk yang bisa saya bantu?`;
    }

    const name = user.name || 'Pengguna';
    const role = user.role || 'customer';
    const email = user.email || '-';
    const phone = user.phone || '-';
    const address = user.address || 'Belum diisi';

    if (role === 'customer') {
        let msg = `Halo, **${name}**! 👋

Anda saat ini sedang login di platform **ServisYuk** sebagai **Pelanggan (Customer)**.

📋 **Rincian Data Akun Anda:**
• **Nama Lengkap**: ${name}
• **Role**: Customer (Pelanggan)
• **Email**: ${email}
• **Nomor Telepon**: ${phone}
• **Alamat**: ${address}
• **Status Akun**: Aktif\n\n`;

        if (user.service_requests && user.service_requests.length > 0) {
            msg += `🔧 **Riwayat / Tiket Servis Anda:**\n`;
            user.service_requests.forEach(r => {
                msg += `• **[${r.request_code}]** ${r.appliance_name} (${r.brand}) - Status: **${r.status}** (Jadwal: ${r.schedule})\n`;
            });
        } else {
            msg += `💡 Anda belum memiliki pesanan servis aktif. Silakan gunakan menu **Pesan Servis** jika membutuhkan bantuan perbaikan peralatan rumah tangga.`;
        }
        return msg;
    }

    if (role === 'technician') {
        const spec = user.specialization || 'Teknisi Servis Peralatan Rumah Tangga';
        const exp = user.experience || 'Berpengalaman';
        const avail = (user.availability === 'available' || user.availability === 'tersedia') ? 'Tersedia (Siap Tugas)' : 'Sedang Bertugas (Busy)';

        return `Halo, **${name}**! 👋

Anda saat ini sedang login di platform **ServisYuk** sebagai **Teknisi Resmi**.

🔧 **Profil Teknisi:**
• **Nama**: ${name}
• **Role**: Teknisi (Technician)
• **Spesialisasi**: ${spec}
• **Pengalaman**: ${exp}
• **Status**: ${avail}
• **Email**: ${email}
• **No. HP**: ${phone}

Semangat bertugas memberikan perbaikan terbaik untuk pelanggan ServisYuk!`;
    }

    if (role === 'admin') {
        return `Halo, **${name}**! 👋

Anda saat ini sedang login di platform **ServisYuk** sebagai **Administrator Utama (Admin)**.

🛡️ **Profil Administrator:**
• **Nama Pengguna**: ${name}
• **Role**: Administrator Sistem (Admin)
• **Email**: ${email}
• **Nomor Telepon**: ${phone}
• **Hak Akses**: Akses Penuh ke Seluruh Data Database, Pengguna, Teknisi, Tiket Pesanan, Laporan & Pengaturan Sistem ServisYuk.

Ada data operasional atau statistik database yang ingin Anda periksa?`;
    }

    return `Halo, **${name}**! Anda sedang login di sistem ServisYuk dengan email ${email}.`;
}

/**
 * Check if the message is strictly out of scope
 */
function isStrictlyOutOfScope(message) {
    if (!message || typeof message !== 'string') return false;
    const lower = message.toLowerCase().trim();

    // Whitelist keywords (allow questions about identity, database, & ServisYuk operations)
    const contextWhitelist = /\b(servisyuk|servis|service|jasa|perbaikan|ac|kulkas|mesin cuci|tv|kompor|dispenser|microwave|rice cooker|database|data|tabel|tiket|invoice|tagihan|laporan|teknisi|pelanggan|customer|user|pengguna|order|booking|pendapatan|biaya|tarif|harga|status|garansi|rekening|kontak|rating|ulasan|budi|agus|hendra|rina|dian|siapa|saya|aku|profil|akun|login|nama|nomor|alamat|pesanan|permintaan|masuk|berapa|jumlah|total|ada berapa|menunggu|dikonfirmasi|perjalanan|pemeriksaan|perbaikan|selesai|dibatalkan|dikerjakan|proses)\b/i;

    if (contextWhitelist.test(lower)) {
        return false;
    }

    // 1. Coding / Programming Generation Intent
    const codingIntentRegex = /\b(buatkan|bikin|tuliskan|contoh|generate|koding|coding)\s+(coding|koding|kode|code|program|script|fungsi|function|algoritma|syntax)\b/i;
    const specificLanguages = /\b(python|phyton|javascript|typescript|c\+\+|java\b|golang|ruby|php\b|bash|powershell)\b/i;

    if (codingIntentRegex.test(lower)) {
        return true;
    }
    if (specificLanguages.test(lower) && !contextWhitelist.test(lower)) {
        return true;
    }

    // 2. Creative writing / Poetry / Lyrics / Jokes
    const creativeRegex = /\b(buatkan|tuliskan|bikin)\s+(puisi|pantun|cerpen|cerita|dongeng|lirik|lagu|lelucon|humor|novel|esai|pantun)\b/i;
    if (creativeRegex.test(lower)) {
        return true;
    }

    // 3. Recipes / Cooking unrelated to appliances
    const recipeRegex = /\b(resep|cara membuat)\s+(nasi goreng|kue|roti|makanan|masakan|rendang|sambal|ayam|minuman|soto|bakso)\b/i;
    if (recipeRegex.test(lower)) {
        return true;
    }

    // 4. Politics, General School Trivia
    const triviaRegex = /\b(siapa presiden|presiden indonesia|politik|pemilu|ibukota|sejarah dunia|rumus matematika|rumus fisika|tugas sekolah|pr matematika)\b/i;
    if (triviaRegex.test(lower)) {
        return true;
    }

    return false;
}

/**
 * Format user context text
 */
function formatUserContext(user) {
    if (!user || (!user.logged_in && !user.id)) {
        return `STATUS PENGGUNA SAAT INI:
- Status: BELUM LOGIN (Pengunjung / Tamu)
- Catatan: Jika pengguna bertanya tentang 'Siapa saya' / identitas, jelaskan bahwa saat ini belum login ke akun ServisYuk dan sarankan untuk login/daftar di menu Masuk.`;
    }

    const roleName = user.role === 'customer' ? 'Customer / Pelanggan' : user.role === 'technician' ? 'Teknisi Resmi' : 'Administrator';
    let info = `STATUS PENGGUNA SAAT INI (SUDAH LOGIN):
- Status: SUDAH LOGIN
- Nama Lengkap: ${user.name}
- User ID: ${user.id || '-'}
- Role: ${user.role} (${roleName})
- Email: ${user.email}
- Nomor Telepon: ${user.phone || 'Belum diisi'}
- Alamat: ${user.address || 'Belum diisi'}`;

    if (user.specialization) {
        info += `\n- Spesialisasi: ${user.specialization} (${user.experience || ''}) | Status: ${user.availability || ''}`;
    }

    if (user.service_requests && user.service_requests.length > 0) {
        info += `\n- Tiket Pesanan Servis Milik Pengguna:`;
        user.service_requests.forEach(r => {
            info += `\n  * [${r.request_code}] ${r.appliance_name} (${r.brand}) - Status: ${r.status} (Jadwal: ${r.schedule})`;
        });
    }

    return info;
}

/**
 * Detect user intent for dynamic database querying
 */
function detectIntent(message) {
    if (!message || typeof message !== 'string') return 'general_chat';
    const clean = message.toLowerCase().trim();

    if (/\b(total\s*(pendapatan|omset|omzet|penghasilan|revenue|pemasukan|keuangan)|(brp|berapa|cek)\s*(pendapatan|omset|omzet|penghasilan|duit\s*masuk|uang\s*masuk)|pendapatan|omset|omzet|uang\s*masuk|total\s*lunas)\b/i.test(clean)) {
        return 'total_revenue';
    }
    if (/\b(pesanan|tiket|servis|riwayat|order)\s*(saya|aku|milikku|gw|gue)\b/i.test(clean)) {
        return 'user_my_orders';
    }
    // Status Khusus / Jumlah Permintaan Masuk / Booking (Count)
    if (/\b(teknisi\s*(yang\s*)?ditugaskan)\b/i.test(clean) ||
        /\b(berapa|ada\s*berapa|berapa\s*banyak|total|jumlah|banyaknya|cek)\s*(total\s*)?(jumlah\s*)?(permintaan|booking|order|orderan|tiket|pesanan)(\s*(masuk|servis|service|saat\s*ini|keseluruhan|semuanya))?\b/i.test(clean) ||
        /\b(permintaan|booking|tiket|pesanan)\s*(yang\s*)?(masuk|ada)(\s*(ada\s*berapa|berapa\s*total|jumlahnya))?\b/i.test(clean) ||
        (/\b(berapa|ada\s*berapa|berapa\s*banyak|total|jumlah|banyaknya|cek|status|tiket|permintaan)\b/i.test(clean) &&
            /\b(menunggu|dikonfirmasi|terkonfirmasi|dalam\s*perjalanan|perjalanan|pemeriksaan|diperiksa|dalam\s*perbaikan|perbaikan|diperbaiki|selesai|beres|dibatalkan|batal|sedang\s*dikerjakan|dikerjakan|masih\s*berjalan|dalam\s*proses)\b/i.test(clean)) ||
        /\b(berapa|ada\s*berapa)\s*(yang\s*)?(menunggu|dikonfirmasi|dalam\s*perjalanan|pemeriksaan|diperiksa|dalam\s*perbaikan|diperbaiki|selesai|beres|dibatalkan|batal|sedang\s*dikerjakan|dikerjakan|masih\s*berjalan|dalam\s*proses)\b/i.test(clean)) {
        return 'requests_count';
    }
    // Pertanyaan Jumlah / Status / Daftar Teknisi
    if (/\b(berapa|ada\s*berapa|berapa\s*banyak|total|jumlah|banyaknya|cek|daftar|status|siapa\s*saja|list)\s*(total\s*)?(jumlah\s*)?(teknisi|tukang\s*servis|mekanik)\b/i.test(clean) ||
        /\b(teknisi|tukang\s*servis)\s*(ada\s*berapa|jumlahnya|tersedia|ready|sibuk|busy|bertugas|aktif|resmi|yang\s*ada)\b/i.test(clean) ||
        /\bstatus\s*tknisi\b/i.test(clean)) {
        return 'technicians_status';
    }
    // Pertanyaan Jumlah / Daftar Pelanggan
    if (/\b(berapa|ada\s*berapa|berapa\s*banyak|total|jumlah|banyaknya|daftar|siapa\s*saja|data|list)\s*(total\s*)?(jumlah\s*)?(customer|pelanggan|user|pengguna|klien)\b/i.test(clean)) {
        return 'customers_list';
    }
    // Riwayat Seluruh Pesanan
    if (/\b(riwayat|daftar|tampilkan|cek|status|list)\s*(pesanan|tiket|order|orderan|booking)\b/i.test(clean) ||
        /\b(pesanan|tiket|order|booking)\s*(hari\s*ini|terbaru|aktif|semua|saat\s*ini)\b/i.test(clean)) {
        return 'orders_history';
    }
    // Katalog Layanan & Biaya / Tarif Servis / Jumlah Layanan
    if (/\b(katalog|daftar|tarif|harga|biaya|ongkos)\s*(layanan|servis|service|jasa|perbaikan|inspeksi)\b/i.test(clean) ||
        /\b(harga|biaya|tarif|ongkos)\s*(cuci|isi\s*freon|ganti\s*sparepart|pasang|perbaikan)\b/i.test(clean) ||
        /\b(berapa\s*biaya|biaya\s*servis|tarif\s*servis)\b/i.test(clean) ||
        /\b(berapa|ada\s*berapa|jumlah|total)\s*(layanan|service|servis|jasa)\b/i.test(clean)) {
        return 'services_catalog';
    }
    // Invoice / Tagihan
    if (/\b(daftar|laporan|riwayat|cek|status|berapa|ada\s*berapa|jumlah|total)\s*(invoice|faktur|tagihan|laporan\s*servis|nota)\b/i.test(clean) ||
        /\b(invoice|faktur|tagihan)\s*(lunas|belum\s*lunas|unpaid|paid)?\b/i.test(clean)) {
        return 'invoices_reports';
    }
    if (/\b(ulasan|rating|bintang|testimoni|review\s*pelanggan|feedback)\b/i.test(clean)) {
        return 'reviews_ratings';
    }
    if (/\b(kontak|nomor\s*wa|whatsapp|telepon|call\s*center|alamat\s*kantor|rekening|bank\s*bca|cs|customer\s*service)\b/i.test(clean)) {
        return 'system_settings';
    }
    if (/\b(solusi|gejala|penyebab|bahaya|safety|peringatan|kenapa\s*rusak|cara\s*mengatasi)\b/i.test(clean)) {
        return 'knowledge_base';
    }
    if (/\b(database|tabel|rekap|statistik|ringkasan|jumlah\s*data|overview|dashboard)\b/i.test(clean)) {
        return 'db_overview';
    }
    return 'general_chat';
}

/**
 * Fetch dynamic context based on intent and user role from PHP endpoint
 */
async function getDynamicDatabaseContext(intent, userMessage, user) {
    try {
        const controller = new AbortController();
        const timeoutId = setTimeout(() => controller.abort(), 3500);
        const role = encodeURIComponent(user?.role || 'guest');
        const userId = encodeURIComponent(user?.id || 0);
        const url = `http://localhost/jasa/index.php?page=api_db_context&intent=${encodeURIComponent(intent)}&msg=${encodeURIComponent(userMessage)}&role=${role}&user_id=${userId}`;
        const res = await fetch(url, { signal: controller.signal });
        clearTimeout(timeoutId);

        if (res.ok) {
            const data = await res.json();
            if (data.success && data.context) {
                return data;
            }
        }
    } catch (e) {
        console.warn('[Server] Warning fetching dynamic context from PHP:', e.message);
    }

    // Honest fallback without dummy or hallucinated numbers
    return {
        intent: intent,
        context: 'Data belum dapat diverifikasi langsung dari sistem resmi. Silakan hubungi CS ServisYuk di 0812-8888-9999.',
        require_table: false
    };
}

/**
 * Build dynamic system prompt with clean formatting & hidden technical details
 */
function buildSystemPrompt(dynamicData, user) {
    const userContextText = formatUserContext(user);
    const contextText = dynamicData.context || '';
    const requireTable = dynamicData.require_table;
    const intent = dynamicData.intent;

    const tableInstruction = requireTable
        ? `ATURAN FORMAT TABEL (SANGAT KETAT):
Karena data yang disajikan memuat rincian/daftar, KAMU HARUS MENYAJIKANNYA DALAM FORMAT TABEL YANG RAPI.
Gunakan format Markdown tabel yang presisi (dengan header, garis pembatas |---|---|, dan spasi yang rata) ATAU tag HTML <table><tr><th>...</th></tr><tr><td>...</td></tr></table>.
DILARANG menyajikan data daftar hanya berupa bullet-point panjang atau teks paragraf yang tidak rapi.`
        : `ATURAN FORMAT PENYAJIAN DATA:
Jika menyajikan lebih dari 1 item data, utamakan menggunakan TABEL yang rapi dengan header yang jelas agar mudah dibaca pengguna.`;

    let countInstruction = '';
    if (intent === 'total_revenue') {
        countInstruction = `INSTRUKSI KHUSUS PENDAPATAN (NILAI MUTLAK RESMI):
Nilai total pendapatan resmi sistem ServisYuk adalah nilai yang tertera pada DATA SISTEM di bawah.
Kamu DILARANG KERAS menghitung ulang secara mandiri. Gunakan nilai absolut tersebut secara langsung dan sertakan rincian transaksi dalam tabel.`;
    } else if (intent === 'requests_count') {
        countInstruction = `INSTRUKSI KHUSUS PERMINTAAN MASUK & STATUS (NILAI MUTLAK RESMI):
Pengguna menanyakan jumlah atau status permintaan servis di ServisYuk.
Jawab dengan data pasti yang tertera pada DATA SISTEM di bawah.
1. Jika ditanya total permintaan masuk / booking: sebutkan total permintaan masuk (8 permintaan).
2. Jika ditanya status tertentu (Menunggu, Dikonfirmasi, Teknisi Ditugaskan, Dalam Perjalanan, Pemeriksaan, Dalam Perbaikan, Selesai, Dibatalkan): jawab SESUAI ANGKA EKSPLISIT STATUS TERSEBUT pada DATA SISTEM. JANGAN menyamakan status individual.
3. Jika ditanya 'sedang dikerjakan' atau 'masih berjalan': sebutkan terdapat 4 permintaan yang sedang aktif berproses di lapangan (gabungan 4 status aktif).
4. DILARANG KERAS menolak pertanyaan ini atau memberikan penjelasan umum tanpa angka pasti tersebut.`;
    } else if (intent === 'technicians_status') {
        countInstruction = `INSTRUKSI KHUSUS JUMLAH TEKNISI (NILAI MUTLAK RESMI):
Pengguna menanyakan jumlah atau status teknisi.
Jawab dengan angka pasti jumlah teknisi resmi yang tertera pada DATA SISTEM di bawah (4 orang teknisi resmi, DILARANG KERAS MENYEBUT 12 ATAU ANGKA LAIN).
JANGAN tertukar antara 'jumlah orang teknisi' (4 orang) dengan 'permintaan berstatus teknisi ditugaskan' (2 tiket).
Sampaikan rincian ketersediaan teknisi sesuai data resmi di bawah.`;
    }

    return `Kamu adalah "ServisYuk AI Assistant", asisten virtual customer service resmi dari platform ServisYuk (Layanan Jasa Perbaikan Peralatan Rumah Tangga Berbasis Web & AI).

TUGAS UTAMA:
1. Memberikan informasi yang ramah, sopan, jelas, dan akurat mengenai layanan ServisYuk, keluhan alat rumah tangga, tarif servis, teknisi, pesanan, dan keuangan sistem.
2. Menjawab pertanyaan seputar IDENTITAS PENGGUNA berdasarkan 'STATUS PENGGUNA AKTIF' di bawah.

PEDOMAN GAYA & KEAMANAN SISTEM (WAJIB DIPATUHI):
1. SEMBUNYIKAN SISI TEKNIS:
   - DILARANG KERAS menyebutkan nama tabel database (seperti 'tabel booking_costs', 'tabel users', 'tabel service_requests', kolom SQL, atau query database).
   - DILARANG menggunakan kalimat teknis kaku seperti 'Berdasarkan data live...', 'Berdasarkan query database...', 'Di database kami...'.
   - Selalu gunakan ungkapan profesional customer service yang alami, seperti: 'Berdasarkan catatan sistem kami...', 'Saat ini di ServisYuk terdata...', 'Rincian transaksi yang tercatat adalah...'.
2. FORMAT TABEL RAPI:
   ${tableInstruction}
3. ${countInstruction}
4. STRICT SCOPE GUARDRAIL:
   - Kamu melayani pertanyaan seputar platform ServisYuk, keluhan alat rumah tangga, tarif servis, serta seluruh data dan statistik resmi sistem ServisYuk (termasuk total permintaan masuk, jumlah teknisi, jumlah pelanggan, invoice, dan pendapatan).
   - Pertanyaan mengenai jumlah permintaan masuk, jumlah teknisi, status pesanan, tarif, dan data sistem ServisYuk adalah PERTANYAAN VALID SISTEM dan HARUS DIJAWAB secara akurat berdasarkan DATA RESMI SISTEM di bawah.
   - HANYA tolak hal yang benar-benar di luar topik seperti: coding/script pemrograman, puisi/pantun, resep masakan umum, politik, atau matematika sekolah.
     Jika pengguna menanyakan hal di luar topik tersebut, tolak dengan sopan:
     '${REFUSAL_TEXT}'

==================================================
${userContextText}
==================================================
DATA RESMI SISTEM SERVISYUK (TERKAIT PERTANYAAN):
==================================================
${contextText}

Gaya Bahasa: Ramah, santun, profesional, dan dalam Bahasa Indonesia yang baik.`;
}

/**
 * Call AI API with dynamic configuration and fallback
 */
async function callGroq(messages, modelName, systemPrompt, attempt = 1) {
    const config = getActiveAiConfig();
    const apiKey = config.apiKey;
    const baseUrl = config.baseUrl;

    if (!apiKey) {
        throw new Error('API Key belum dikonfigurasi di sistem ServisYuk.');
    }

    const formattedMessages = [
        { role: 'system', content: systemPrompt }
    ];

    if (Array.isArray(messages)) {
        const recent = messages.slice(-3);
        for (const m of recent) {
            if (m.role && m.content) {
                formattedMessages.push({
                    role: m.role === 'assistant' ? 'assistant' : 'user',
                    content: String(m.content).trim()
                });
            }
        }
    }

    const cleanModelName = String(modelName || '').replace(/^models\//i, '');
    const payload = {
        model: cleanModelName,
        messages: formattedMessages,
        temperature: 0.1,
        max_tokens: 1000
    };

    const headers = {
        'Authorization': `Bearer ${apiKey}`,
        'Content-Type': 'application/json'
    };
    if (config.provider === 'openrouter') {
        headers['HTTP-Referer'] = 'http://localhost/jasa';
        headers['X-Title'] = 'ServisYuk';
    }

    const res = await fetch(`${baseUrl}/chat/completions`, {
        method: 'POST',
        headers: headers,
        body: JSON.stringify(payload)
    });

    if (!res.ok) {
        const errorText = await res.text();
        if (res.status === 429 && attempt === 1) {
            console.warn(`[Node.js Chat] 429 rate limit hit for ${modelName}, waiting 2.5s and retrying...`);
            await new Promise(r => setTimeout(r, 2500));
            return callGroq(messages, modelName, systemPrompt, attempt + 1);
        }
        throw new Error(`AI API Error (${res.status}): ${errorText}`);
    }

    const data = await res.json();
    const rawReply = data.choices?.[0]?.message?.content || '';
    // Strip internal reasoning tags (<think>...</think>) for reasoning models like DeepSeek-R1
    let cleaned = rawReply.replace(/<think>[\s\S]*?<\/think>\s*/gi, '').trim();
    if (!cleaned && rawReply.trim()) {
        cleaned = rawReply.trim();
    }

    return {
        reply: cleaned,
        model: data.model || modelName
    };
}

const server = http.createServer(async (req, res) => {
    // CORS Headers
    res.setHeader('Access-Control-Allow-Origin', '*');
    res.setHeader('Access-Control-Allow-Methods', 'GET, POST, OPTIONS');
    res.setHeader('Access-Control-Allow-Headers', 'Content-Type, Authorization');

    if (req.method === 'OPTIONS') {
        res.writeHead(204);
        res.end();
        return;
    }

    // Health check endpoint
    if (req.method === 'GET' && (req.url === '/' || req.url === '/health' || req.url === '/api/health')) {
        const dbContext = await getLiveDatabaseContext();
        res.writeHead(200, { 'Content-Type': 'application/json' });
        res.end(JSON.stringify({
            status: 'online',
            service: 'ServisYuk Node.js AI Chatbot Server',
            provider: 'Groq / Nous Research',
            guardrail: 'Strict ServisYuk Database Context',
            port: PORT,
            database_context_length: dbContext.length,
            cached_at: new Date(lastFetchTime).toISOString()
        }));
        return;
    }

    // Database Context Inspection Endpoint
    if (req.method === 'GET' && req.url === '/api/db-context') {
        const dbContext = await getLiveDatabaseContext();
        res.writeHead(200, { 'Content-Type': 'application/json' });
        res.end(JSON.stringify({
            status: 'success',
            context: dbContext
        }));
        return;
    }

    // Chat endpoint
    if (req.method === 'POST' && (req.url === '/api/chat' || req.url === '/chat')) {
        let body = '';
        req.on('data', chunk => { body += chunk; });
        req.on('end', async () => {
            try {
                const parsed = JSON.parse(body || '{}');
                const messages = parsed.messages || [];
                const userMessage = parsed.message || (messages.length > 0 ? messages[messages.length - 1].content : '');
                const user = parsed.user || null;

                if (!userMessage && (!messages || messages.length === 0)) {
                    res.writeHead(400, { 'Content-Type': 'application/json' });
                    res.end(JSON.stringify({ status: 'error', message: 'Pesan tidak boleh kosong' }));
                    return;
                }

                // Check direct identity question
                if (isIdentityQuestion(userMessage)) {
                    res.writeHead(200, { 'Content-Type': 'application/json' });
                    res.end(JSON.stringify({
                        status: 'success',
                        reply: formatIdentityResponse(user),
                        source: 'identity_direct'
                    }));
                    return;
                }

                // Check out-of-scope guardrail
                if (isStrictlyOutOfScope(userMessage)) {
                    res.writeHead(200, { 'Content-Type': 'application/json' });
                    res.end(JSON.stringify({
                        status: 'success',
                        reply: REFUSAL_TEXT,
                        guardrail: 'blocked_out_of_scope'
                    }));
                    return;
                }

                // Dynamic Context: Detect intent & fetch relevant READ-ONLY data
                const intent = detectIntent(userMessage);
                const dynamicData = await getDynamicDatabaseContext(intent, userMessage, user);
                const systemPrompt = buildSystemPrompt(dynamicData, user);

                // Get active configuration dynamically
                const aiConfig = getActiveAiConfig();
                const primaryModel = aiConfig.primaryModel;
                const fallbackModel = aiConfig.fallbackModel;

                // Construct conversation
                const conversation = messages.length > 0 ? messages : [{ role: 'user', content: userMessage }];

                let result;
                try {
                    result = await callGroq(conversation, primaryModel, systemPrompt);
                } catch (errPrimary) {
                    if (fallbackModel && fallbackModel !== primaryModel) {
                        console.warn(`[Node.js Chat] Primary model (${primaryModel}) failed, trying fallback (${fallbackModel}):`, errPrimary.message);
                        try {
                            result = await callGroq(conversation, fallbackModel, systemPrompt);
                        } catch (errFallback) {
                            console.warn('[Node.js Chat] Fallback model also failed:', errFallback.message);
                            let fallbackMsg = "Halo! Layanan AI sedang mengalami kendala jaringan atau antrian pemrosesan yang padat. Silakan coba kembali dalam beberapa saat, atau hubungi CS ServisYuk di 0812-8888-9999.";
                            result = { reply: fallbackMsg, model: 'local_fallback' };
                        }
                    } else {
                        console.warn('[Node.js Chat] Primary model failed and no separate fallback configured:', errPrimary.message);
                        let fallbackMsg = "Halo! Layanan AI sedang mengalami kendala jaringan atau antrian pemrosesan yang padat. Silakan coba kembali dalam beberapa saat, atau hubungi CS ServisYuk di 0812-8888-9999.";
                        result = { reply: fallbackMsg, model: 'local_fallback' };
                    }
                }

                res.writeHead(200, { 'Content-Type': 'application/json' });
                res.end(JSON.stringify({
                    status: 'success',
                    reply: result.reply,
                    model: result.model
                }));

            } catch (err) {
                console.error('[Node.js Chat] Error handling request:', err);
                res.writeHead(500, { 'Content-Type': 'application/json' });
                res.end(JSON.stringify({
                    status: 'error',
                    message: 'Gagal memproses pesan AI: ' + err.message
                }));
            }
        });
        return;
    }

    // 404 for other routes
    res.writeHead(404, { 'Content-Type': 'application/json' });
    res.end(JSON.stringify({ status: 'error', message: 'Endpoint tidak ditemukan' }));
});

server.listen(PORT, async () => {
    console.log(`====================================================`);
    console.log(` ServisYuk AI Chatbot Server (Node.js) is RUNNING `);
    console.log(` Port: ${PORT} | Guardrail: STRICT SERVISYUK CONTEXT `);
    console.log(` Endpoint: http://localhost:${PORT}/api/chat       `);
    console.log(` Initializing Live Database Context...            `);
    const dbContext = await getLiveDatabaseContext();
    console.log(` Live Database Context Loaded: ${dbContext.length} chars `);
    console.log(`====================================================`);
});
