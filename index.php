<?php
// ==========================================================
// Front Controller & Router
// Sistem Jasa Perbaikan Peralatan Rumah Tangga Berbasis Web + AI
// ==========================================================

require_once __DIR__ . '/config/app.php';
require_once __DIR__ . '/core/Database.php';
require_once __DIR__ . '/core/Auth.php';
require_once __DIR__ . '/core/Helper.php';
require_once __DIR__ . '/core/AiService.php';

// Route dispatcher
$page = $_GET['page'] ?? 'home';

switch ($page) {
    // ------------------------------------------------------
    // Public Pages
    // ------------------------------------------------------
    case 'home':
        require_once __DIR__ . '/models/Service.php';
        $services = Service::all();
        $categories = [
            ['id' => 1, 'name' => 'AC & Sistem Pendingin', 'icon' => 'fa-solid fa-snowflake', 'description' => 'Servis AC tidak dingin, bocor air, berisik, isi freon, dan cuci AC rutin.', 'base_inspection_fee' => 75000],
            ['id' => 2, 'name' => 'Kulkas & Freezer', 'icon' => 'fa-solid fa-temperature-arrow-down', 'description' => 'Perbaikan kulkas tidak beku, kompresor mati, bocor gas, dan bau.', 'base_inspection_fee' => 75000],
            ['id' => 3, 'name' => 'Mesin Cuci', 'icon' => 'fa-solid fa-soap', 'description' => 'Servis mesin cuci tidak berputar, air tidak keluar, getar berlebih, dan error modul.', 'base_inspection_fee' => 75000],
            ['id' => 4, 'name' => 'Televisi (TV LED/Smart)', 'icon' => 'fa-solid fa-tv', 'description' => 'Perbaikan TV mati total, layar gelap, suara hilang, dan ganti backlight.', 'base_inspection_fee' => 75000],
            ['id' => 5, 'name' => 'Kompor Gas & Listrik', 'icon' => 'fa-solid fa-fire-burner', 'description' => 'Servis api merah, pemantik mati, kebocoran gas, dan kompor induksi mati.', 'base_inspection_fee' => 75000],
            ['id' => 6, 'name' => 'Dispenser Air', 'icon' => 'fa-solid fa-glass-water', 'description' => 'Perbaikan dispenser tidak panas/dingin, bocor, dan kran rusak.', 'base_inspection_fee' => 75000],
        ];
        $reviews = [
            ['rating' => 5, 'comment' => 'Teknisi AC datang tepat waktu, sopan, dan pengerjaannya sangat rapi. AC langsung dingin kembali!', 'customer_name' => 'Rina Kusuma', 'category_name' => 'AC & Pendingin', 'technician_name' => 'Budi Santoso'],
            ['rating' => 5, 'comment' => 'Fitur AI Diagnosa sangat membantu mengetahui estimasi biaya sebelum teknisi datang. Sangat transparan!', 'customer_name' => 'Dian Permata', 'category_name' => 'Mesin Cuci', 'technician_name' => 'Agus Pratama'],
            ['rating' => 5, 'comment' => 'Kulkas dua pintu saya dingin kembali setelah ganti thermostat oleh teknisi Hendra. Mantap!', 'customer_name' => 'Bambang Sudiro', 'category_name' => 'Kulkas & Freezer', 'technician_name' => 'Hendra Wijaya']
        ];
        require __DIR__ . '/views/home/index.php';
        break;

    case 'services':
        require_once __DIR__ . '/models/Service.php';
        $services = Service::all();
        $categories = [
            ['id' => 1, 'name' => 'AC & Sistem Pendingin', 'icon' => 'fa-solid fa-snowflake', 'description' => 'Servis AC tidak dingin, bocor air, berisik, isi freon, dan cuci AC rutin.', 'base_inspection_fee' => 75000],
            ['id' => 2, 'name' => 'Kulkas & Freezer', 'icon' => 'fa-solid fa-temperature-arrow-down', 'description' => 'Perbaikan kulkas tidak beku, kompresor mati, bocor gas, dan bau.', 'base_inspection_fee' => 75000],
            ['id' => 3, 'name' => 'Mesin Cuci', 'icon' => 'fa-solid fa-soap', 'description' => 'Servis mesin cuci tidak berputar, air tidak keluar, getar berlebih, dan error modul.', 'base_inspection_fee' => 75000],
            ['id' => 4, 'name' => 'Televisi (TV LED/Smart)', 'icon' => 'fa-solid fa-tv', 'description' => 'Perbaikan TV mati total, layar gelap, suara hilang, dan ganti backlight.', 'base_inspection_fee' => 75000],
            ['id' => 5, 'name' => 'Kompor Gas & Listrik', 'icon' => 'fa-solid fa-fire-burner', 'description' => 'Servis api merah, pemantik mati, kebocoran gas, dan kompor induksi mati.', 'base_inspection_fee' => 75000],
            ['id' => 6, 'name' => 'Dispenser Air', 'icon' => 'fa-solid fa-glass-water', 'description' => 'Perbaikan dispenser tidak panas/dingin, bocor, dan kran rusak.', 'base_inspection_fee' => 75000],
        ];
        require __DIR__ . '/views/home/services.php';
        break;

    case 'tracking':
        require __DIR__ . '/views/home/tracking.php';
        break;

    case 'ai_diagnostic':
        require_once __DIR__ . '/models/Service.php';
        $services = Service::all();
        $categories = [
            ['id' => 'AC', 'name' => 'AC & Sistem Pendingin'],
            ['id' => 'Kulkas', 'name' => 'Kulkas & Freezer'],
            ['id' => 'Mesin Cuci', 'name' => 'Mesin Cuci'],
            ['id' => 'TV', 'name' => 'Televisi (TV)'],
            ['id' => 'Kompor', 'name' => 'Kompor Gas & Listrik'],
            ['id' => 'Dispenser', 'name' => 'Dispenser Air'],
            ['id' => 'Microwave', 'name' => 'Microwave & Oven']
        ];
        require __DIR__ . '/views/ai/diagnostic.php';
        break;

    // ------------------------------------------------------
    // Authentication
    // ------------------------------------------------------
    case 'login':
        require_once __DIR__ . '/controllers/AuthController.php';
        (new AuthController())->login();
        break;

    case 'register':
        require_once __DIR__ . '/controllers/AuthController.php';
        (new AuthController())->register();
        break;

    case 'verify_otp':
        require_once __DIR__ . '/controllers/AuthController.php';
        (new AuthController())->verifyOtp();
        break;

    case 'resend_otp':
        require_once __DIR__ . '/controllers/AuthController.php';
        (new AuthController())->resendOtp();
        break;

    case 'logout':
        require_once __DIR__ . '/controllers/AuthController.php';
        (new AuthController())->logout();
        break;

    // ------------------------------------------------------
    // Customer Portal
    // ------------------------------------------------------
    case 'customer_dashboard':
        require_once __DIR__ . '/controllers/CustomerController.php';
        (new CustomerController())->dashboard();
        break;

    case 'customer_booking_create':
        require_once __DIR__ . '/controllers/CustomerController.php';
        (new CustomerController())->createBooking();
        break;

    case 'customer_booking_detail':
        require_once __DIR__ . '/controllers/CustomerController.php';
        (new CustomerController())->viewBooking();
        break;

    case 'customer_history':
        require_once __DIR__ . '/controllers/CustomerController.php';
        (new CustomerController())->bookingHistory();
        break;

    case 'customer_review':
        require_once __DIR__ . '/controllers/CustomerController.php';
        (new CustomerController())->submitReview();
        break;

    case 'customer_profile':
        require_once __DIR__ . '/controllers/CustomerController.php';
        (new CustomerController())->profile();
        break;

    // ------------------------------------------------------
    // Technician Portal
    // ------------------------------------------------------
    case 'tech_dashboard':
        require_once __DIR__ . '/controllers/TechnicianController.php';
        (new TechnicianController())->dashboard();
        break;

    case 'tech_job_detail':
        require_once __DIR__ . '/controllers/TechnicianController.php';
        (new TechnicianController())->jobDetail();
        break;

    case 'tech_update_status':
        require_once __DIR__ . '/controllers/TechnicianController.php';
        (new TechnicianController())->updateStatus();
        break;

    case 'tech_history':
        require_once __DIR__ . '/controllers/TechnicianController.php';
        (new TechnicianController())->jobHistory();
        break;

    case 'tech_profile':
        require_once __DIR__ . '/controllers/TechnicianController.php';
        (new TechnicianController())->profile();
        break;

    // ------------------------------------------------------
    // Admin Portal
    // ------------------------------------------------------
    case 'admin_dashboard':
        require_once __DIR__ . '/controllers/AdminController.php';
        (new AdminController())->dashboard();
        break;

    case 'admin_bookings':
        require_once __DIR__ . '/controllers/AdminController.php';
        (new AdminController())->bookings();
        break;

    case 'admin_booking_detail':
        require_once __DIR__ . '/controllers/AdminController.php';
        (new AdminController())->bookingDetail();
        break;

    case 'admin_assign_technician':
        require_once __DIR__ . '/controllers/AdminController.php';
        (new AdminController())->assignTechnician();
        break;

    case 'admin_update_status':
        require_once __DIR__ . '/controllers/AdminController.php';
        (new AdminController())->updateBookingStatus();
        break;

    case 'admin_technicians':
        require_once __DIR__ . '/controllers/AdminController.php';
        (new AdminController())->technicians();
        break;

    case 'admin_customers':
        require_once __DIR__ . '/controllers/AdminController.php';
        (new AdminController())->customers();
        break;

    case 'admin_services':
        require_once __DIR__ . '/controllers/AdminController.php';
        (new AdminController())->services();
        break;

    case 'admin_ai_config':
    case 'admin_ai_settings':
        require_once __DIR__ . '/controllers/AdminController.php';
        (new AdminController())->aiConfig();
        break;

    case 'admin_ai_test_connection':
        require_once __DIR__ . '/controllers/AdminController.php';
        (new AdminController())->testAiConnection();
        break;

    case 'admin_ai_fetch_models':
        require_once __DIR__ . '/controllers/AdminController.php';
        (new AdminController())->fetchAiModels();
        break;

    case 'admin_reports':
        require_once __DIR__ . '/controllers/AdminController.php';
        (new AdminController())->reports();
        break;

    case 'admin_invoice_print':
        require_once __DIR__ . '/controllers/AdminController.php';
        (new AdminController())->printInvoice();
        break;

    case 'admin_profile':
        require_once __DIR__ . '/controllers/AdminController.php';
        (new AdminController())->profile();
        break;

    // ------------------------------------------------------
    // API Endpoints
    // ------------------------------------------------------
    case 'api_diagnose':
        require_once __DIR__ . '/controllers/ApiController.php';
        (new ApiController())->diagnose();
        break;

    case 'api_store_prefill':
        require_once __DIR__ . '/controllers/ApiController.php';
        (new ApiController())->storeAiPrefill();
        break;

    case 'api_quick_track':
        require_once __DIR__ . '/controllers/ApiController.php';
        (new ApiController())->quickTrack();
        break;

    case 'api_services':
        require_once __DIR__ . '/controllers/ApiController.php';
        (new ApiController())->getServicesByCategory();
        break;

    case 'api_chatbot':
        require_once __DIR__ . '/controllers/ApiController.php';
        (new ApiController())->chat();
        break;

    case 'api_db_context':
        require_once __DIR__ . '/controllers/ApiController.php';
        (new ApiController())->dbContext();
        break;

    case 'api_current_user':
        require_once __DIR__ . '/controllers/ApiController.php';
        (new ApiController())->currentUser();
        break;

    // ------------------------------------------------------
    // Error / Fallback
    // ------------------------------------------------------
    case 'unauthorized':
        http_response_code(403);
        echo "<div style='font-family:sans-serif; text-align:center; padding:50px;'><h2>403 - Akses Ditolak</h2><p>Anda tidak memiliki hak akses ke halaman ini.</p><a href='" . BASE_URL . "'>Kembali ke Beranda</a></div>";
        break;

    default:
        http_response_code(404);
        echo "<div style='font-family:sans-serif; text-align:center; padding:50px;'><h2>404 - Halaman Tidak Ditemukan</h2><p>Halaman yang Anda tuju tidak tersedia.</p><a href='" . BASE_URL . "'>Kembali ke Beranda</a></div>";
        break;
}
