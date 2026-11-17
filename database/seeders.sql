-- ==========================================================
-- Database Seeders: db_jasa_perbaikan
-- Sistem Jasa Perbaikan Peralatan Rumah Tangga Berbasis Web + AI
-- ==========================================================

USE `db_jasa_perbaikan`;

-- 1. Seeder Users
-- Passwords:
-- admin@jasa.com : admin123
-- budi@jasa.com, agus@jasa.com, hendra@jasa.com : teknisi123
-- customer@jasa.com, dian@jasa.com : customer123
INSERT INTO `users` (`id`, `name`, `email`, `password`, `role`, `phone`, `address`, `avatar`, `specialization`, `is_active`, `created_at`) VALUES
(1, 'Administrator Utama', 'admin@jasa.com', '$2y$10$Y./7nIdsnpg8fw9gPsvOMuXlEMrYfxzQyY02kzwugjb5Unez9ZvUu', 'admin', '081122334455', 'Kantor Pusat ServiceKu, Jl. Kebon Jeruk No. 10, Jakarta Barat', 'admin_avatar.png', 'Head of Operations', 1, NOW()),
(2, 'Budi Santoso', 'budi@jasa.com', '$2y$10$qn6n3NnSyW9yr6tnlmT5R.KBnd7bHnMUpm06jbACgazCRbAmKA89u', 'technician', '081234567890', 'Jl. Palmerah Barat No. 14, Jakarta Barat', 'tech_budi.png', 'Spesialis AC & Sistem Pendingin', 1, NOW()),
(3, 'Agus Pratama', 'agus@jasa.com', '$2y$10$qn6n3NnSyW9yr6tnlmT5R.KBnd7bHnMUpm06jbACgazCRbAmKA89u', 'technician', '081298765432', 'Jl. Tebet Timur Raya No. 45, Jakarta Selatan', 'tech_agus.png', 'Spesialis Mesin Cuci & Motor Listrik', 1, NOW()),
(4, 'Hendra Wijaya', 'hendra@jasa.com', '$2y$10$qn6n3NnSyW9yr6tnlmT5R.KBnd7bHnMUpm06jbACgazCRbAmKA89u', 'technician', '081377889900', 'Jl. Kelapa Gading Boulevard No. 88, Jakarta Utara', 'tech_hendra.png', 'Spesialis Kulkas, Freezer & Kompresor', 1, NOW()),
(5, 'Rina Kusuma', 'customer@jasa.com', '$2y$10$HB4EhLMhpJEuZoCFQTrup.I1WtAW0zrb34gB/27.zuI6P71RlMaRy', 'customer', '085612345678', 'Komplek Harmoni Indah Blok B3 No. 7, Kebayoran Baru, Jakarta Selatan', 'cust_rina.png', NULL, 1, NOW()),
(6, 'Dian Permata', 'dian@jasa.com', '$2y$10$HB4EhLMhpJEuZoCFQTrup.I1WtAW0zrb34gB/27.zuI6P71RlMaRy', 'customer', '087812349988', 'Apartemen Senopati Tower A Lantai 12 Unit 1205, Jakarta Selatan', 'cust_dian.png', NULL, 1, NOW())
ON DUPLICATE KEY UPDATE `name`=VALUES(`name`);

-- 2. Seeder Kategori
INSERT INTO `categories` (`id`, `name`, `slug`, `icon`, `description`, `base_inspection_fee`, `is_active`) VALUES
(1, 'AC & Sistem Pendingin', 'ac-pendingin', 'fa-solid fa-snowflake', 'Layanan cuci AC, perbaikan AC bocor, isi freon, penggantian kapasitor, dan masalah kompresor.', 50000.00, 1),
(2, 'Kulkas & Freezer', 'kulkas-freezer', 'fa-solid fa-cube', 'Perbaikan kulkas tidak dingin, bunga es tebal, bau freon bocor, pergantian gasket pintu & relay thermostat.', 60000.00, 1),
(3, 'Mesin Cuci & Pengering', 'mesin-cuci', 'fa-solid fa-soap', 'Perbaikan mesin cuci 1 tabung/2 tabung tidak berputar, air tidak mau keluar, getaran kuat, dan modul error.', 60000.00, 1),
(4, 'Microwave & Oven Listrik', 'microwave-oven', 'fa-solid fa-fire-burner', 'Perbaikan piringan tidak berputar, pemanas mati, percikan api di dalam ruang microwave, dan tombol error.', 50000.00, 1),
(5, 'TV & Audio Visual', 'tv-audio', 'fa-solid fa-tv', 'Perbaikan TV LED/Smart TV layar blank, gambar bergaris, mati total, backlight mati, suara tidak keluar.', 50000.00, 1),
(6, 'Dispenser & Pompa Air', 'dispenser-pompa', 'fa-solid fa-faucet-drip', 'Perbaikan dispenser air tidak panas/dingin, kebocoran kran galon bawah, serta pompa air tersumbat/macet.', 45000.00, 1),
(7, 'Kompor Gas & Induksi', 'kompor-gas-induksi', 'fa-solid fa-fire', 'Perbaikan pemantik kompor gas tidak menyala, api merah/kecil, kebocoran pipa burner, dan error modul induksi.', 45000.00, 1),
(8, 'Rice Cooker & Air Fryer', 'ricecooker-airfryer', 'fa-solid fa-bowl-rice', 'Perbaikan nasi cepat basi, elemen pemanas mati, sekering thermal putus, dan error digital timer.', 35000.00, 1)
ON DUPLICATE KEY UPDATE `name`=VALUES(`name`);

-- 3. Seeder Layanan Spesifik
INSERT INTO `services` (`id`, `category_id`, `name`, `description`, `estimated_price_min`, `estimated_price_max`, `warranty_days`, `is_active`) VALUES
(1, 1, 'Cuci AC Split Standard (0.5 - 1 PK)', 'Pembersihan evaporator, kondensor, blower indoor, dan pembuangan drainase.', 75000.00, 100000.00, 14, 1),
(2, 1, 'Tambah / Isi Ulang Freon R32 / R410A', 'Pengisian freon berkualitas dan pengecekan tekanan manifold.', 150000.00, 275000.00, 30, 1),
(3, 1, 'Perbaikan Kebocoran Air / Ganti Pipa AC', 'Penanganan saluran drainase tersumbat atau penggantian isolasi pipa indoor.', 120000.00, 250000.00, 30, 1),
(4, 1, 'Ganti Kapasitor / Overhaul Kompresor AC', 'Penggantian sparepart kapasitor fan/kompresor asli bergaransi.', 200000.00, 450000.00, 60, 1),
(5, 2, 'Perbaikan Kulkas 1/2 Pintu Tidak Dingin', 'Pengecekan thermostat, relay starting, dan siklus refrigerasi.', 150000.00, 350000.00, 30, 1),
(6, 2, 'Penggantian Defrost Timer / Bimetal Sensor', 'Mengatasi masalah penumpukan bunga es berlebih dan sirkulasi udara dingin.', 175000.00, 320000.00, 30, 1),
(7, 2, 'Isi Ulang Freon Kulkas + Las Kebocoran', 'Penambalan pipa evaporator yang bocor dan vacuum pengisian freon baru.', 250000.00, 480000.00, 45, 1),
(8, 3, 'Perbaikan Mesin Cuci Top/Front Load Tidak Berputar', 'Pengecekan vanbelt, gearbox pengering, kapasitor motor, atau brush carbon.', 140000.00, 320000.00, 30, 1),
(9, 3, 'Perbaikan Saluran Pembuangan / Drain Valve', 'Mengatasi air tidak mau terbuang atau bocor terus menerus.', 100000.00, 220000.00, 30, 1),
(10, 3, 'Ganti Water Level Sensor / Modul PCB', 'Perbaikan kode error digital E1/E2/IE/OE pada mesin cuci otomatis.', 220000.00, 500000.00, 45, 1),
(11, 4, 'Perbaikan Microwave Tidak Mau Memanaskan Makanan', 'Pengecekan high voltage diode, kapasitor, dan tabung Magnetron.', 180000.00, 400000.00, 30, 1),
(12, 5, 'Perbaikan Smart TV Mati Total / Ganti Backlight', 'Pergantian lampu strip backlight LED atau power supply board.', 250000.00, 600000.00, 45, 1),
(13, 6, 'Servis Dispenser Galon Bawah (Air Tidak Dingin/Panas)', 'Perbaikan elemen pemanas, peltier pendingin atau pompa hisap galon.', 120000.00, 280000.00, 30, 1),
(14, 7, 'Servis Kompor Gas Api Merah / Pipa Mampet', 'Pembersihan burner, spuyer, dan kalibrasi pasokan udara gas LPG.', 85000.00, 180000.00, 30, 1)
ON DUPLICATE KEY UPDATE `name`=VALUES(`name`);

-- 4. Seeder Basis Pengetahuan AI & Expert System
INSERT INTO `ai_knowledge_base` (`id`, `category_id`, `category_name`, `symptom_keywords`, `issue_title`, `probable_causes`, `diy_check`, `safety_warning`, `estimated_cost_min`, `estimated_cost_max`, `repair_time_estimate`) VALUES
(1, 1, 'AC & Sistem Pendingin', 'tidak dingin, hanya angin, kipas saja, panas, kurang dingin, freon habis', 'AC Tidak Dingin (Hanya Keluar Hembusan Angin)', 
'1. Tekanan gas refrigeran (Freon) berkurang/bocor\n2. Filter udara dan evaporator tertutup debu tebal\n3. Kapasitor kompresor outdoor melemah/mati\n4. Fan motor outdoor tidak berputar normal',
'1. Periksa apakah remote AC disetel pada mode COOL (bukan Fan/Dry).\n2. Buka penutup indoor dan cek filter udara, jika sangat kotor bersihkan dengan air mengalir.\n3. Periksa apakah unit outdoor berputar dan mengeluarkan hembusan hangat.',
'Matikan unit AC jika terdengar suara dengung keras tanpa putaran kompresor untuk mencegah motor kompresor terbakar.',
75000.00, 300000.00, '45 - 90 Menit'),

(2, 1, 'AC & Sistem Pendingin', 'bocor air, netes, menetes, banjir indoor, bau apek', 'Kebocoran Air pada Unit Indoor AC',
'1. Saluran pembuangan (drain pipe) tersumbat lendir atau kotoran\n2. Kemiringan instalasi indoor AC kurang tepat\n3. Pembekuan es pada evaporator akibat freon berkurang lalu mencair',
'1. Siapkan wadah/ember penampung tetesan air.\n2. Coba tiup atau bersihkan ujung selang pembuangan di luar jika terjangkau.\n3. Jangan gunakan benda tajam menusuk talang air.',
'Hati-hati tetesan air mengenai stop kontak atau peralatan elektronik lain di bawah unit AC.',
90000.00, 180000.00, '30 - 60 Menit'),

(3, 2, 'Kulkas & Freezer', 'tidak dingin, lampu nyala tapi tidak dingin, kompresor mati, bau freon', 'Kulkas Tidak Dingin Meskipun Lampu Menyala',
'1. Overload relay kompresor putus/rusak\n2. Thermostat suhu rusak atau mati\n3. Terjadi kebocoran pada pipa freon\n4. Kipas evaporator macet',
'1. Putar kenop pengatur suhu ke posisi maksimal (Coldest) dan dengarkan apakah ada suara klik kompresor.\n2. Cek karet (gasket) pintu kulkas apakah masih rapat menutup.\n3. Pastikan ventilasi belakang kulkas berjarak minimal 10cm dari dinding.',
'Segera selamatkan makanan basah/daging ke cooler box agar tidak membusuk.',
150000.00, 450000.00, '1 - 2 Jam'),

(4, 2, 'Kulkas & Freezer', 'bunga es tebal, beku tebal, es menumpuk, pintu tidak rapat, es batu keras', 'Penumpukan Bunga Es Berlebih (Defrost Rusak)',
'1. Sensor Defrost Bimetal rusak\n2. Defrost Timer macet di posisi cooling\n3. Elemen pemanas (heater glass) putus\n4. Karet pintu sobek/kendor sehingga udara luar masuk',
'1. Jangan pernah mencongkel bunga es dengan pisau/benda tajam karena dapat menusuk pipa evaporator!\n2. Matikan kulkas dan biarkan pintu terbuka dengan lap kain di bawahnya.',
'BAHAYA: Menusuk freezer dengan pisau berisiko tinggi menusuk pipa freon dan menyebabkan kulkas rusak permanen.',
160000.00, 350000.00, '1 Jam'),

(5, 3, 'Mesin Cuci & Pengering', 'tidak berputar, motor dengung, tabung macet, dinamo mati', 'Tabung Mesin Cuci Tidak Mau Berputar',
'1. Tali vanbelt (V-belt) putus atau kendor\n2. Kapasitor motor penggerak melemah/rusak\n3. Gearbox aus atau macet akibat beban berlebih\n4. Switch pintu (door lock) tidak terdeteksi menutup',
'1. Coba putar tabung dengan tangan saat mesin mati; jika sangat berat kemungkinan gearbox/laher macet.\n2. Pastikan pintu mesin cuci tertutup rapat.\n3. Kurangi beban pakaian jangan melebihi kapasitas.',
'Cabut stop kontak sebelum memeriksa bagian belakang atau bawah mesin cuci.',
120000.00, 350000.00, '1 - 2 Jam'),

(6, 3, 'Mesin Cuci & Pengering', 'air tidak keluar, error oe, saluran mampet, air tidak masuk, error ie', 'Air Mesin Cuci Tidak Mau Keluar atau Tidak Mengisi',
'1. Filter kran inlet air tersumbat pasir/lumut\n2. Solenoid valve inlet terbakar\n3. Katup pembuangan (drain valve) tersumbat koin/kancing baju\n4. Sensor ketinggian air (pressure switch) kotor',
'1. Cek selang pembuangan, pastikan tidak terlipat atau terjepit.\n2. Lepas filter saringan inlet di belakang dan bersihkan kotoran dengan sikat kecil.\n3. Buka tutup filter drain di depan bawah (untuk front-load) dan buang kotoran/koin.',
'Hati-hati genangan air di sekitar colokan listrik!',
100000.00, 250000.00, '45 - 60 Menit'),

(7, 4, 'Microwave & Oven Listrik', 'tidak panas, lampu nyala tidak panas, percikan api, piring tidak putar', 'Microwave Bekerja Tapi Makanan Tidak Panas',
'1. Tabung pemancar gelombang mikro (Magnetron) rusak\n2. High Voltage Diode atau Kapasitor HV short-circuit\n3. Pintu microswitch interlock rusak',
'1. Pastikan wadah makanan menggunakan material ramah microwave (Microwave Safe).\n2. Jangan memasukkan wadah logam/aluminium foil karena memicu percikan api.\n3. Bersihkan lempengan mika (waveguide cover) di dinding dalam jika ada sisa minyak.',
'PERINGATAN TEGANGAN TINGGI: Microwave menyimpan muatan ribuan volt bahkan setelah dimatikan. Jangan membongkar casing sendiri.',
180000.00, 420000.00, '1 Jam'),

(8, 5, 'TV & Audio Visual', 'layar blank, suara ada gambar hilang, garis layar, mati total, backlight', 'Smart TV Suara Ada Tapi Layar Gelap (Black Screen)',
'1. Lampu Backlight LED mati/putus satu deret\n2. Inverter board / modul T-Con bermasalah\n3. Power supply board drop tegangan',
'1. Sorotkan senter HP dari dekat ke layar yang menyala; jika terlihat bayangan gambar samar berarti backlight mati.\n2. Coba cabut colokan kabel power selama 5 menit lalu nyalakan kembali (Hard Reset).',
'Hindari menekan permukaan panel layar LCD/LED karena dapat merusak kristal layar.',
200000.00, 550000.00, '1.5 - 2 Jam')
ON DUPLICATE KEY UPDATE `issue_title`=VALUES(`issue_title`);

-- 5. Seeder Pemesanan & Alur Demo (Bookings)
INSERT INTO `bookings` (`id`, `booking_code`, `customer_id`, `technician_id`, `category_id`, `service_id`, `appliance_brand`, `appliance_model`, `issue_description`, `ai_diagnosis_summary`, `initial_cost_estimate`, `scheduled_date`, `scheduled_time`, `address`, `address_notes`, `photo_url`, `status`, `cancellation_reason`, `created_at`) VALUES
(1, 'SRV-20260901-001', 5, 2, 1, 1, 'Daikin', 'Inverter Flash 1 PK', 'AC kamar utama tidak dingin, hanya berhembus angin biasa dan ada suara mendesis halus.', 'Kemungkinan besar filter kotor dan tekanan freon R32 berkurang. Disarankan cuci AC dan cek kebocoran nepel pipa.', 'Rp 150.000 - Rp 275.000', '2026-09-02', '09:00 - 11:00 WIB', 'Komplek Harmoni Indah Blok B3 No. 7, Kebayoran Baru, Jakarta Selatan', 'Pagar hitam, rumah cat krem samping taman', 'sample_ac.jpg', 'completed', NULL, '2026-09-01 08:30:00'),
(2, 'SRV-20260903-002', 6, 3, 3, 8, 'LG', 'Smart Inverter 8.5 Kg', 'Mesin cuci top loading saat proses pengeringan (spin) mengeluarkan suara getaran keras dan tabung tidak mau berputar.', 'Kemungkinan V-belt kendor/putus atau bantalan shock absorber aus. Perlu pengecekan mekanis.', 'Rp 140.000 - Rp 300.000', '2026-09-04', '13:00 - 15:00 WIB', 'Apartemen Senopati Tower A Lantai 12 Unit 1205, Jakarta Selatan', 'Izin resepsionis untuk akses teknisi sudah siap', 'sample_washer.jpg', 'in_progress', NULL, '2026-09-03 10:15:00'),
(3, 'SRV-20260904-003', 5, 4, 2, 5, 'Samsung', 'Digital Inverter 2 Pintu RT22', 'Freezer dingin dan beku normal, tapi rak bawah makanan tidak dingin sama sekali.', 'Indikasi kerusakan sistem defrost (Bimetal sensor/Defrost heater) atau saluran sirkulasi udara dingin terhalang bunga es.', 'Rp 160.000 - Rp 350.000', '2026-09-05', '10:00 - 12:00 WIB', 'Komplek Harmoni Indah Blok B3 No. 7, Kebayoran Baru, Jakarta Selatan', 'Dekat pos satpam gerbang utama', NULL, 'assigned', NULL, '2026-09-04 09:00:00'),
(4, 'SRV-20260904-004', 6, NULL, 4, 11, 'Sharp', 'R-728IN-ST 28 Liter', 'Microwave menyala dan piring berputar normal, namun makanan yang dimasukkan tidak hangat sama sekali.', 'Diagnosis AI: Kemungkinan kerusakan Magnetron atau High Voltage Diode putus. Diperlukan pengetesan tegangan teknisi.', 'Rp 180.000 - Rp 400.000', '2026-09-06', '14:00 - 16:00 WIB', 'Apartemen Senopati Tower A Lantai 12 Unit 1205, Jakarta Selatan', 'Mohon konfirmasi 1 jam sebelum tiba', NULL, 'pending', NULL, '2026-09-04 11:30:00')
ON DUPLICATE KEY UPDATE `booking_code`=VALUES(`booking_code`);

-- 6. Seeder Status Logs
INSERT INTO `booking_status_logs` (`id`, `booking_id`, `status`, `notes`, `updated_by_user_id`, `created_at`) VALUES
(1, 1, 'pending', 'Pesanan dibuat oleh pelanggan dengan rekomendasi Diagnosa AI.', 5, '2026-09-01 08:30:00'),
(2, 1, 'assigned', 'Teknisi Budi Santoso ditugaskan oleh Admin.', 1, '2026-09-01 09:00:00'),
(3, 1, 'on_the_way', 'Teknisi sedang dalam perjalanan menuju lokasi pelanggan.', 2, '2026-09-02 08:45:00'),
(4, 1, 'in_progress', 'Teknisi tiba dan memulai inspeksi & pencucian unit AC Daikin.', 2, '2026-09-02 09:15:00'),
(5, 1, 'completed', 'Perbaikan selesai. Cuci AC + Pengisian Freon R32 30 psi. Kondisi AC kembali dingin maksimal.', 2, '2026-09-02 10:45:00'),

(6, 2, 'pending', 'Pesanan dibuat oleh pelanggan Dian Permata.', 6, '2026-09-03 10:15:00'),
(7, 2, 'assigned', 'Teknisi Agus Pratama ditugaskan.', 1, '2026-09-03 11:00:00'),
(8, 2, 'on_the_way', 'Teknisi menuju Apartemen Senopati.', 3, '2026-09-04 12:30:00'),
(9, 2, 'in_progress', 'Sedang melakukan pembongkaran casing belakang dan penggantian vanbelt.', 3, '2026-09-04 13:10:00'),

(10, 3, 'pending', 'Pesanan kulkas Samsung dibuat oleh Rina Kusuma.', 5, '2026-09-04 09:00:00'),
(11, 3, 'assigned', 'Teknisi Hendra Wijaya ditugaskan untuk jadwal besok.', 1, '2026-09-04 09:30:00'),

(12, 4, 'pending', 'Pesanan baru perbaikan Microwave Sharp menunggu konfirmasi admin.', 6, '2026-09-04 11:30:00')
ON DUPLICATE KEY UPDATE `status`=VALUES(`status`);

-- 7. Seeder Rincian Biaya & Invoice
INSERT INTO `booking_costs` (`id`, `booking_id`, `service_fee`, `sparepart_fee`, `sparepart_details`, `discount`, `total_amount`, `payment_status`, `payment_method`, `proof_of_payment`, `invoice_number`, `paid_at`, `created_at`) VALUES
(1, 1, 85000.00, 150000.00, 'Pengisian Freon Daikin R32 Full + Pembersihan Talang Drainase', 0.00, 235000.00, 'paid', 'Transfer QRIS BCA', 'qris_proof_001.jpg', 'INV/20260902/001', '2026-09-02 10:50:00', '2026-09-02 10:45:00'),
(2, 2, 90000.00, 75000.00, 'Vanbelt Mesin Cuci LG Original Type M-21', 0.00, 165000.00, 'unpaid', 'Tunai / Transfer', NULL, 'INV/20260904/002', NULL, '2026-09-04 13:15:00')
ON DUPLICATE KEY UPDATE `invoice_number`=VALUES(`invoice_number`);

-- 8. Seeder Reviews
INSERT INTO `reviews` (`id`, `booking_id`, `customer_id`, `technician_id`, `rating`, `comment`, `created_at`) VALUES
(1, 1, 5, 2, 5, 'Pelayanan Pak Budi sangat memuaskan! Datang tepat waktu, pengerjaannya sangat rapi dan AC sekarang dingin banget seperti baru beli. Diagnosa AI di web juga sangat akurat.', '2026-09-02 11:30:00')
ON DUPLICATE KEY UPDATE `rating`=VALUES(`rating`);

-- 9. Seeder Pengaturan Sistem
INSERT INTO `system_settings` (`id`, `setting_key`, `setting_value`, `setting_group`) VALUES
(1, 'app_name', 'ServisYuk - Layanan Servis Rumah Pintar', 'general'),
(2, 'app_tagline', 'Solusi Cepat, Transparan & Bergaransi dengan Bantuan AI', 'general'),
(3, 'company_phone', '0812-8888-9999', 'general'),
(4, 'company_whatsapp', '6281288889999', 'general'),
(5, 'company_email', 'bantuan@servisyuk.id', 'general'),
(6, 'company_address', 'Jl. Kebon Jeruk Raya No. 10, Kebon Jeruk, Jakarta Barat 11530', 'general'),
(7, 'gemini_api_key', '', 'ai'),
(8, 'gemini_model', 'gemini-1.5-flash', 'ai'),
(9, 'ai_diagnosis_enabled', '1', 'ai'),
(10, 'bank_name', 'BCA (Bank Central Asia)', 'payment'),
(11, 'bank_account_number', '8830-192-881', 'payment'),
(12, 'bank_account_name', 'PT SERVIS RUMAH PINTAR', 'payment'),
(13, 'qris_code_image', 'qris_company.png', 'payment')
ON DUPLICATE KEY UPDATE `setting_value`=VALUES(`setting_value`);
