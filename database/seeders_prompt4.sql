-- ==========================================================
-- Database Relational Seeders (PROMPT 4)
-- ==========================================================

USE `db_jasa_perbaikan`;

-- 1. Seeder Users
-- Passwords:
-- admin@jasa.com : admin123
-- budi@jasa.com, agus@jasa.com, hendra@jasa.com : teknisi123
-- customer@jasa.com, dian@jasa.com : customer123
INSERT INTO `users` (`id`, `name`, `email`, `password`, `role`, `phone`, `address`, `status`) VALUES
(1, 'Administrator Utama', 'admin@jasa.com', '$2y$10$Y./7nIdsnpg8fw9gPsvOMuXlEMrYfxzQyY02kzwugjb5Unez9ZvUu', 'admin', '081122334455', 'Kantor Pusat ServisYuk, Jakarta Barat', 'active'),
(2, 'Budi Santoso', 'budi@jasa.com', '$2y$10$qn6n3NnSyW9yr6tnlmT5R.KBnd7bHnMUpm06jbACgazCRbAmKA89u', 'technician', '081234567890', 'Jl. Palmerah Barat No. 14, Jakarta Barat', 'active'),
(3, 'Agus Pratama', 'agus@jasa.com', '$2y$10$qn6n3NnSyW9yr6tnlmT5R.KBnd7bHnMUpm06jbACgazCRbAmKA89u', 'technician', '081298765432', 'Jl. Tebet Timur Raya No. 45, Jakarta Selatan', 'active'),
(4, 'Hendra Wijaya', 'hendra@jasa.com', '$2y$10$qn6n3NnSyW9yr6tnlmT5R.KBnd7bHnMUpm06jbACgazCRbAmKA89u', 'technician', '081377889900', 'Jl. Kelapa Gading Boulevard No. 88, Jakarta Utara', 'active'),
(5, 'Rina Kusuma', 'customer@jasa.com', '$2y$10$HB4EhLMhpJEuZoCFQTrup.I1WtAW0zrb34gB/27.zuI6P71RlMaRy', 'customer', '085612345678', 'Komplek Harmoni Indah Blok B3 No. 7, Kebayoran Baru, Jakarta Selatan', 'active'),
(6, 'Dian Permata', 'dian@jasa.com', '$2y$10$HB4EhLMhpJEuZoCFQTrup.I1WtAW0zrb34gB/27.zuI6P71RlMaRy', 'customer', '087812349988', 'Apartemen Senopati Tower A Unit 1205, Jakarta Selatan', 'active');

-- 2. Seeder Technicians (1:1 with users)
INSERT INTO `technicians` (`id`, `user_id`, `specialization`, `experience`, `availability`) VALUES
(1, 2, 'Spesialis AC & Pendingin Ruangan', '5 Tahun', 'available'),
(2, 3, 'Spesialis Mesin Cuci & Motor Listrik', '4 Tahun', 'busy'),
(3, 4, 'Spesialis Kulkas, Freezer & Kompresor', '6 Tahun', 'available');

-- 3. Seeder Services
INSERT INTO `services` (`id`, `service_name`, `description`, `estimated_price`, `status`) VALUES
(1, 'Cuci & Servis Berkala AC Split', 'Pembersihan evaporator, filter, blower, dan cuci outdoor.', 85000.00, 'active'),
(2, 'Tambah / Isi Freon R32 / R410A', 'Pengisian freon berkualitas dan cek tekanan pipa refrigerasi.', 175000.00, 'active'),
(3, 'Servis Kulkas Tidak Dingin / Ganti Thermostat', 'Pengecekan siklus refrigeran, overload relay, dan thermostat.', 160000.00, 'active'),
(4, 'Perbaikan Mesin Cuci Tidak Berputar / Ganti Vanbelt', 'Perbaikan dinamo pengering/pencuci, vanbelt kendor atau kapasitor.', 140000.00, 'active'),
(5, 'Servis Smart TV Layar Gelap / Ganti Backlight LED', 'Perbaikan power supply TV dan pergantian lampu strip backlight.', 250000.00, 'active'),
(6, 'Servis Kompor Gas Api Merah / Pemantik Macet', 'Pembersihan burner, spuyer, dan kalibrasi pasokan udara gas.', 90000.00, 'active'),
(7, 'Servis Dispenser Galon Bawah (Air Tidak Dingin/Panas)', 'Perbaikan elemen pemanas, peltier pendingin, atau pompa galon.', 120000.00, 'active');

-- 4. Seeder Service Requests
INSERT INTO `service_requests` (`id`, `request_code`, `customer_id`, `service_id`, `appliance_name`, `brand`, `model`, `complaint`, `photo`, `address`, `schedule`, `status`, `created_at`) VALUES
(1, 'SRV-20260901-001', 5, 1, 'AC', 'Daikin', 'Inverter Flash 1 PK', 'AC kamar tidur tidak dingin hanya hembusan angin biasa.', 'sample_ac.jpg', 'Komplek Harmoni Indah Blok B3 No. 7, Jakarta Selatan', '2026-09-02 09:00:00', 'Selesai', '2026-09-01 08:30:00'),
(2, 'SRV-20260903-002', 6, 4, 'Mesin Cuci', 'LG', 'Smart Inverter 8.5 Kg', 'Mesin cuci tabung tidak mau berputar saat proses pengeringan.', 'sample_washer.jpg', 'Apartemen Senopati Tower A Unit 1205, Jakarta Selatan', '2026-09-04 13:00:00', 'Dalam Perbaikan', '2026-09-03 10:15:00'),
(3, 'SRV-20260904-003', 5, 3, 'Kulkas', 'Samsung', 'Digital Inverter 2 Pintu', 'Freezer beku normal tapi rak bawah makanan tidak dingin sama sekali.', NULL, 'Komplek Harmoni Indah Blok B3 No. 7, Jakarta Selatan', '2026-09-05 10:00:00', 'Teknisi Ditugaskan', '2026-09-04 09:00:00'),
(4, 'SRV-20260904-004', 6, NULL, 'Microwave', 'Sharp', 'R-728IN 28L', 'Microwave menyala dan berputar tetapi makanan tidak panas sama sekali.', NULL, 'Apartemen Senopati Tower A Unit 1205, Jakarta Selatan', '2026-09-06 14:00:00', 'Menunggu', '2026-09-04 11:30:00');

-- 5. Seeder Technician Assignments
INSERT INTO `technician_assignments` (`id`, `request_id`, `technician_id`, `assigned_at`, `status`, `notes`) VALUES
(1, 1, 1, '2026-09-01 09:00:00', 'completed', 'Teknisi Budi Santoso ditugaskan untuk cuci AC dan isi freon.'),
(2, 2, 2, '2026-09-03 11:00:00', 'in_progress', 'Teknisi Agus Pratama sedang memperbaiki gearbox & vanbelt mesin cuci.'),
(3, 3, 3, '2026-09-04 09:30:00', 'assigned', 'Teknisi Hendra Wijaya ditugaskan untuk inspeksi kulkas Samsung.');

-- 6. Seeder Diagnoses (AI & Technician Results)
INSERT INTO `diagnoses` (`id`, `request_id`, `complaint`, `ai_result`, `technician_result`) VALUES
(1, 1, 'AC kamar tidur tidak dingin hanya hembusan angin biasa.', 
'{"issue_title": "Filter Udara Kotor & Tekanan Freon R32 Berkurang", "urgency_level": "Sedang", "safety_warning": "Matikan unit jika terdengar suara dengung kompresor keras", "estimated_cost_formatted": "Rp 85.000 - Rp 250.000", "source": "Google Gemini AI"}', 
'Ditemukan filter tersumbat debu dan tekanan freon turun menjadi 90 psi. Dilakukan cuci unit & penambahan freon 40 psi.'),

(2, 2, 'Mesin cuci tabung tidak mau berputar saat proses pengeringan.', 
'{"issue_title": "Tali Vanbelt Aus / Kapasitor Melemah", "urgency_level": "Sedang", "safety_warning": "Cabut steker sebelum memeriksa bagian belakang mesin", "estimated_cost_formatted": "Rp 120.000 - Rp 300.000", "source": "ServisYuk Smart Expert Engine"}', 
'Vanbelt kendor dan slip saat putaran beban tinggi. Dilakukan penggantian tali vanbelt baru type M-21.'),

(3, 3, 'Freezer beku normal tapi rak bawah makanan tidak dingin sama sekali.', 
'{"issue_title": "Sistem Defrost Bermasalah / Sensor Bimetal Putus", "urgency_level": "Sedang", "safety_warning": "Jangan menusuk bunga es dengan benda tajam", "estimated_cost_formatted": "Rp 160.000 - Rp 350.000", "source": "Google Gemini AI"}', 
NULL);

-- 7. Seeder Service Reports (1:1 with service_requests)
INSERT INTO `service_reports` (`id`, `request_id`, `technician_id`, `inspection_result`, `repair_action`, `replaced_component`, `service_fee`, `sparepart_fee`, `total_amount`, `payment_status`, `invoice_number`, `notes`, `completed_at`) VALUES
(1, 1, 1, 
'Evaporator dan filter indoor tertutup debu tebal, tekanan freon R32 pada manifold 90 psi.', 
'Pembersihan menyeluruh unit indoor & outdoor serta penambahan freon R32 hingga 140 psi normal.', 
'Freon R32 Original Daikin', 
85000.00, 150000.00, 235000.00, 'paid', 'INV/20260902/001', 
'AC diuji selama 30 menit suhu hembusan mencapai 16°C dingin maksimal. Garansi servis 30 hari.', 
'2026-09-02 10:45:00');
