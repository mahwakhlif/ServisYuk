# ServisYuk — Platform Jasa Perbaikan Peralatan Rumah Tangga Berbasis Web & AI

[![PHP Version](https://img.shields.io/badge/PHP-8.1%2B-777BB4?style=for-the-badge&logo=php&logoColor=white)](https://www.php.net/)
[![Node.js Version](https://img.shields.io/badge/Node.js-16%2B-339933?style=for-the-badge&logo=nodedotjs&logoColor=white)](https://nodejs.org/)
[![MySQL](https://img.shields.io/badge/MySQL-InnoDB-4479A1?style=for-the-badge&logo=mysql&logoColor=white)](https://www.mysql.com/)
[![Bootstrap](https://img.shields.io/badge/Bootstrap-5.3-7952B3?style=for-the-badge&logo=bootstrap&logoColor=white)](https://getbootstrap.com/)
[![AI Multi-Provider](https://img.shields.io/badge/AI-Groq%20%7C%20Gemini%20%7C%20NVIDIA%20NIM-7E22CE?style=for-the-badge&logo=openai&logoColor=white)](https://github.com/mahwakhlif/ServisYuk)
[![Figma Ready](https://img.shields.io/badge/Figma-UI%2FUX%20Design%20Kit-F24E1E?style=for-the-badge&logo=figma&logoColor=white)](https://www.figma.com/)

> **Tugas Besar / Ujian Akhir Semester (UAS)**  
> **Mata Kuliah:** Rekayasa Perangkat Lunak (RPL)  
> **Tahun Akademik:** 2026/2027  

---

## 📌 1. Identitas Anggota Kelompok

* **Nomor Kelompok:** [KELOMPOK ...] *(Silakan isi nomor kelompok Anda, contoh: Kelompok 04)*
* **Dosen Pengampu:** [NAMA DOSEN PENGAMPU, GELAR]
* **Program Studi / Kelas:** [PROGRAM STUDI] / [KELAS]

| No | Nama Lengkap Anggota | NIM | Peran / Kontribusi dalam Tim |
|:--:|:---------------------|:---:|:-----------------------------|
| 1. | **[NAMA ANGGOTA 1]** | [NIM ANGGOTA 1] | *Project Manager & Fullstack Developer* |
| 2. | **[NAMA ANGGOTA 2]** | [NIM ANGGOTA 2] | *Frontend & UI/UX Designer* |
| 3. | **[NAMA ANGGOTA 3]** | [NIM ANGGOTA 3] | *Backend & AI Service Integrator* |
| 4. | **[NAMA ANGGOTA 4]** | [NIM ANGGOTA 4] | *Database Administrator & QA Tester* |

---

## 🎯 2. Tema / Topik Kelompok

**Tema Project:**  
> **"Rancang Bangun Sistem Informasi Layanan Jasa Perbaikan Peralatan Rumah Tangga (*Home Appliance Repair*) Terintegrasi Kecerdasan Buatan (AI Multi-Provider) dan Gateway WhatsApp Real-Time."**

---

## 📖 3. Deskripsi Aplikasi

### 3.1 Gambaran Umum
**ServisYuk** adalah platform layanan *on-demand home appliance repair* yang mempertemukan pelanggan rumah tangga dengan teknisi spesialis elektronik tersertifikasi (AC, lemari es, mesin cuci, smart TV, pompa air, microwave, dll.). Platform ini dilengkapi dengan **Asisten AI 24 Jam** yang mampu melakukan triase diagnosa kerusakan awal dan estimasi biaya objektif sebelum pelanggan memesan teknisi.

### 3.2 Latar Belakang & Permasalahan yang Diselesaikan
1. **Ketidakpastian Biaya & Asimetri Informasi:** Pelanggan sering kali merasa cemas akan biaya perbaikan yang dimanipulasi (*mark-up*) atau diagnosa palsu. ServisYuk mengatasi hal ini melalui sistem transparansi harga dan estimasi awal berbasis data AI.
2. **Ketiadaan Layanan Darurat 24/7:** Kerusakan peralatan elektronik sering terjadi di luar jam kerja teknisi konvensional. Fitur diagnosa AI ServisYuk siaga 24 jam untuk memberikan panduan keselamatan dan tindakan mandiri (*DIY safe checks*).
3. **Sulitnya Menemukan Teknisi Terpercaya:** ServisYuk memberlakukan verifikasi keahlian teknisi, ulasan transparan dari pelanggan sebelumnya, serta garansi pengerjaan 30 hari.
4. **Pelacakan Layanan yang Manual:** ServisYuk mengotomatiskan seluruh alur kerja mulai dari pemesanan, verifikasi admin, disposisi teknisi, pelacakan perjalanan teknisi secara *live stepper*, hingga penerbitan faktur digital (*invoice*) yang dikirimkan via WhatsApp.

### 3.3 Tujuan Pembuatan Aplikasi
* Menyediakan media pemesanan servis elektronik rumah tangga yang cepat, transparan, dan terpercaya.
* Mengimplementasikan teknologi AI modern (*Generative AI & RAG Knowledge Base*) untuk memecahkan masalah riil di masyarakat.
* Menerapkan metodologi Rekayasa Perangkat Lunak (RPL) berstandar industri dengan pola arsitektur **MVC (Model-View-Controller)**, pemodelan **UML 2.5**, dan perancangan basis data **Relasional Fisik (ERD)**.

---

## 🚀 4. Fitur-Fitur Utama Aplikasi

Sistem ServisYuk dirancang dengan hak akses berbasis peran (*Role-Based Access Control* / RBAC):

### A. Fitur Publik & Pelanggan (*Customer*)
* **Beranda Interaktif (*Landing Page*):** Showcase kategori layanan, ulasan pelanggan, jaminan garansi, dan widget *AI Quick Triage*.
* **Konsultasi & Diagnosa AI 24 Jam:** Asisten interaktif untuk mendeteksi potensi kerusakan dan perkiraan biaya tanpa harus langsung login.
* **Autentikasi & Registrasi Aman:** Login, registrasi akun pelanggan, enkripsi password `Bcrypt`, dan verifikasi OTP nomor telepon.
* **Pemesanan Servis (*Booking Management*):** Formulir pemesanan cerdas dengan pilihan kategori, keluhan, jadwal kunjungan, dan lokasi pengerjaan.
* **Live Stepper Tracking:** Pelacakan status pengerjaan secara *real-time* (Menunggu $\rightarrow$ Dikonfirmasi $\rightarrow$ Teknisi Ditugaskan $\rightarrow$ Dalam Perjalanan $\rightarrow$ Pemeriksaan $\rightarrow$ Selesai).
* **Profil Teknisi Bertugas:** Menampilkan foto, nama, spesialisasi, rating bintang, dan tombol kontak langsung via WhatsApp/Telepon.
* **Invoice Digital & Bukti Bayar:** Rincian biaya pengerjaan fisik, biaya suku cadang resmi, serta upload bukti pembayaran digital.
* **Rating & Ulasan:** Memberikan ulasan kepuasan dan rating 1-5 bintang untuk teknisi setelah pengerjaan selesai.

### B. Fitur Teknisi Lapangan (*Technician Workbench*)
* **Dashboard Kinerja Teknisi:** Ringkasan pekerjaan hari ini, tugas aktif, dan pekerjaan yang telah selesai.
* **Manajemen Tugas Lapangan:** Menerima disposisi pekerjaan, melihat alamat pelanggan, dan mengubah status ke *Dalam Perjalanan* (*On the Way*).
* **Laporan Servis Digital (*Digital Service Report*):** Menginput hasil inspeksi teknis, tindakan perbaikan, penggantian suku cadang, dan rincian biaya akhir.
* **Manajemen Ketersediaan (*Availability Toggle*):** Mengatur status kesiapan kerja (*Available*, *Busy*, atau *Off*).

### C. Fitur Administrator (*Admin Control Panel*)
* **Pusat Kontrol Operasional:** Analitik statistik pemesanan servis, teknisi aktif, dan pendapatan layanan.
* **Manajemen Pengguna:** Pengelolaan akun pelanggan dan akun teknisi.
* **Disposisi & Penugasan Teknisi (*Dispatching*):** Menetapkan teknisi terbaik yang tersedia sesuai bidang keahlian ke tiket pemesanan yang masuk.
* **Katalog Layanan & Tarif Dasar:** Manajemen master kategori perangkat dan biaya inspeksi dasar.
* **Konfigurasi AI Multi-Provider (*AI Control Center*):** Pengaturan dinamis provider AI aktif, API key terenkripsi AES-256, model LLM, Base URL, dan tombol *Test Connection*.
* **Laporan Transaksi & Keuangan:** Cetak invoice resmi dan rekapitulasi riwayat transaksi operasional.

### D. Fitur Notifikasi Otomatis (*WhatsApp Integration*)
* Integrasi **Fonnte API** untuk mengirim pesan otomatis secara *real-time*:
  * Konfirmasi pembuatan tiket pemesanan servis kepada pelanggan.
  * Disposisi penugasan pekerjaan baru kepada nomor WhatsApp teknisi.
  * Notifikasi bahwa teknisi sedang dalam perjalanan ke alamat pelanggan.
  * Notifikasi penerbitan invoice pembayaran digital setelah perbaikan selesai.

---

## 🤖 5. Fitur Kecerdasan Buatan (AI Multi-Provider)

Platform ServisYuk tidak terpaku pada satu vendor AI saja, melainkan mengadopsi arsitektur **Multi-Provider AI Gateway**:

```
                       ┌─────────────────────────┐
                       │   Pelanggan ServisYuk   │
                       └────────────┬────────────┘
                                    │ (Chat Gejala Kerusakan)
                                    ▼
                       ┌─────────────────────────┐
                       │  Node.js Microservice   │
                       │    (Port 5005 / RAG)    │
                       └────────────┬────────────┘
                                    │
    ┌───────────────────────────────┼───────────────────────────────┐
    ▼                               ▼                               ▼
┌──────────────┐            ┌──────────────┐            ┌──────────────┐
│  Groq Cloud  │            │ Google Gemini│            │  NVIDIA NIM  │
│  (Qwen /     │            │  (Gemini     │            │ (Llama 3.1   │
│   GPT-OSS)   │            │   3.8 Flash) │            │     70B)     │
└──────────────┘            └──────────────┘            └──────────────┘
```

### 5.1 Model AI yang Didukung
1. **Groq Cloud API (Default - Ultra Cepat):** Model `qwen/qwen3.8-27b` dan `openai/gpt-oss-20b` dengan latensi inferensi sub-detik.
2. **Google Gemini (OpenAI-Compatible Endpoint):** Model `gemini-3.8-flash` dan `gemini-1.5-flash` untuk penalaran analitis mendalam.
3. **NVIDIA NIM (Inference Microservice):** Model `meta/llama-3.1-70b-instruct` untuk inferensi berkinerja tinggi.
4. **OpenAI API:** Model `gpt-4o-mini`.
5. **Custom OpenAI-Compatible:** Mendukung integrasi ke self-hosted LLM mandiri (*vLLM*, *Ollama*, atau proxy internal).

### 5.2 Cara Kerja & Integrasi AI
1. **Triase Gejala & RAG Knowledge Base:** Sistem mencocokkan kata kunci keluhan ke tabel `ai_knowledge_base` dan berkas `database_knowledge.json` untuk mengekstrak data gejala, penyebab potensial, dan tips darurat.
2. **Pengamanan Kredensial (AES-256-CBC):** Kunci API disimpan secara terenkripsi di tabel database `system_settings` menggunakan cipher simetris AES-256-CBC. API Key dimasking pada tampilan Admin dan tidak pernah terekspos ke frontend.
3. **Microservice AI Chatbot (`chatbot/server.js`):** Microservice berbasis Node.js yang berjalan di port `5005` bertindak sebagai backend proxy asinkron yang melayani widget obrolan tanpa membebani server utama PHP.

---

## 🎥 6. Link Video Demonstrasi Aplikasi

Tautan video demonstrasi pengerjaan, presentasi fitur, dan pengujian sistem:

* **Link YouTube:** `[MASUKKAN LINK VIDEO YOUTUBE DI SINI]`  
  *(Contoh: https://www.youtube.com/watch?v=xxxxxxxxxxx)*

---

## 📊 7. Perancangan Sistem & Diagram Rekayasa Perangkat Lunak

Repositori ini menyertakan dokumentasi diagram UML 2.5 dan rancangan basis data lengkap:

| Diagram | Deskripsi | File Dokumentasi di Repositori |
|:---|:---|:---:|
| **UML Use Case Diagram** | Pemetaan 4 aktor (Customer, Teknisi, Admin di kiri, Asisten AI 24 Jam di kanan) dengan 28 use case lengkap. | [`use_case_servisyuk.png`](use_case_servisyuk.png) |
| **UML Activity Diagram** | Alur kerja bisnis terpadu dalam 4 swimlane (Customer $\rightarrow$ AI $\rightarrow$ Admin $\rightarrow$ Teknisi). | [`activity_diagram_servisyuk.png`](activity_diagram_servisyuk.png) |
| **Entity-Relationship Diagram (ERD)** | Skema fisik relasional MySQL 15 tabel operasional (Users, Bookings, Costs, Reports, Knowledge Base). | [`erd_servisyuk.png`](erd_servisyuk.png) |
| **UML Class Diagram** | Struktur berorientasi objek (MVC Controllers, Core Services, Domain Models, dan Node.js Microservice). | [`class_diagram_servisyuk.png`](class_diagram_servisyuk.png) |
| **UML Sequence Diagram** | Alur interaksi waktu nyata (30 pesan kronologis lintas 7 lifeline dan 5 fase pengerjaan). | [`sequence_diagram_servisyuk.png`](sequence_diagram_servisyuk.png) |
| **UI/UX Design System Figma** | 5 Artboard Figma vector-ready (Landing Page, AI Wizard, Tracking, Admin Panel, dan UI Kit Tokens). | [`ui_ux_servisyuk_figma_preview.png`](ui_ux_servisyuk_figma_preview.png) & folder [`figma_export/`](figma_export/) |

---

## 💻 8. Panduan Instalasi & Menjalankan Aplikasi

Ikuti langkah-langkah di bawah ini untuk menjalankan project ServisYuk di lingkungan lokal:

### 8.1 Persyaratan Perangkat Lunak (*Prerequisites*)
* **Web Server & PHP:** XAMPP / Laragon dengan **PHP versi 8.1 atau lebih tinggi**.
* **Ekstensi PHP Aktif:** `pdo_mysql`, `curl`, `openssl`, `mbstring`, `json`.
* **Database Server:** MySQL 5.7+ atau MariaDB 10.4+.
* **Runtime:** Node.js v16.0+ (diperlukan untuk menjalankan microservice AI Chatbot 24 jam).
* **Web Browser:** Google Chrome / Edge / Mozilla Firefox.

---

### 8.2 Langkah 1: Clone Repositori
Buka terminal (Git Bash / PowerShell / Command Prompt) dan jalankan:
```bash
cd c:\xampp\htdocs
git clone https://github.com/mahwakhlif/ServisYuk.git jasa
cd jasa
```

---

### 8.3 Langkah 2: Konfigurasi Basis Data MySQL
1. Buka browser dan akses **phpMyAdmin** di `http://localhost/phpmyadmin`.
2. Buat database baru dengan nama:
   ```sql
   CREATE DATABASE db_jasa_perbaikan CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
   ```
3. Import file struktur tabel:
   * Masuk ke database `db_jasa_perbaikan` $\rightarrow$ klik menu **Import** $\rightarrow$ pilih file **`database/schema.sql`** $\rightarrow$ klik **Go / Kirim**.
4. Import data seeder awal:
   * Pada database yang sama, klik menu **Import** $\rightarrow$ pilih file **`database/seeders.sql`** $\rightarrow$ klik **Go / Kirim**.

---

### 8.4 Langkah 3: Konfigurasi Environment Variable (`.env`)
1. Salin berkas template `.env.example` menjadi `.env`:
   ```bash
   cp .env.example .env
   ```
2. Buka file `.env` dan sesuaikan kredensial database lokal Anda:
   ```env
   APP_ENV=local
   APP_DEBUG=true
   APP_NAME="ServisYuk - Jasa Perbaikan Rumah Tangga Berbasis AI"
   APP_URL=http://localhost/jasa

   DB_HOST=127.0.0.1
   DB_PORT=3306
   DB_DATABASE=db_jasa_perbaikan
   DB_USERNAME=root
   DB_PASSWORD=

   # Masukkan API Key Provider AI yang Anda miliki (contoh: Groq)
   GROQ_API_KEY=your_groq_api_key_here
   AI_CHATBOT_PROVIDER=groq
   AI_CHATBOT_MODEL=qwen/qwen3.8-27b
   AI_CHATBOT_BASE_URL=https://api.groq.com/openai/v1

   # Token WhatsApp Gateway (Fonnte API)
   FONNTE_TOKEN=your_fonnte_token_here
   ```

---

### 8.5 Langkah 4: Menjalankan Server Aplikasi

Aplikasi ServisYuk terdiri dari dua komponen server:
1. **Server Utama (Apache PHP MVC):**
   * Pastikan modul **Apache** dan **MySQL** pada XAMPP Control Panel dalam kondisi **Running**.
2. **Server AI Chatbot Microservice (Node.js):**
   * Buka jendela terminal baru di folder project, lalu jalankan:
     ```bash
     cd chatbot
     node server.js
     ```
   * *Atau di Windows:* Klik ganda pada berkas **`chatbot/run_chatbot.bat`**.
   * Console akan menampilkan:
     ```text
     [ServisYuk] AI Chatbot Microservice running on http://localhost:5005
     ```

---

### 8.6 Langkah 5: Mengakses Aplikasi di Browser

Buka web browser Anda dan akses tautan:
```
http://localhost/jasa
```

#### 🔑 Akun Demo Pengujian Bawaan (*Seeded Accounts*):
| Peran (Role) | Email | Password | Halaman Akses |
|:---|:---|:---|:---|
| **Administrator** | `admin@servisyuk.com` | `admin123` | Dashboard Admin & Konfigurasi AI |
| **Teknisi Lapangan** | `budi.teknisi@servisyuk.com` | `teknisi123` | Portal Tugas & Laporan Servis |
| **Pelanggan (Customer)** | `pelanggan@gmail.com` | `customer123` | Portal Pemesanan & Tracking Status |

---

## 📁 9. Struktur Direktori Project

```text
jasa/
├── assets/                 # Berkas statis: CSS kustom, JavaScript, dan library vendor
│   ├── css/                # main.css, dashboard.css, ai-diagnosis.css, chatbot.css
│   └── js/                 # main.js, ai-diagnostic.js, chatbot.js
├── chatbot/                # Microservice AI Chatbot 24 Jam (Node.js)
│   ├── server.js           # Server HTTP proxy inferensi LLM multi-provider
│   ├── ai_config.json      # Metadata model dan provider aktif tersinkronisasi
│   ├── database_knowledge.json # Basis pengetahuan lokal gejala dan troubleshooting
│   └── run_chatbot.bat     # Skrip peluncuran otomatis microservice di Windows
├── config/                 # Konfigurasi sistem (database.php, app.php, ai_config.php)
├── controllers/            # Controller MVC (Admin, Auth, Customer, Teknisi, Api)
├── core/                   # Layanan inti sistem (Database PDO, Auth, AiConfig, Fonnte, Otp)
├── database/               # Skrip DDL & DML MySQL (schema.sql, seeders.sql)
├── figma_export/           # Berkas vektor SVG Artboard siap import ke Figma
├── models/                 # Model entitas bisnis (User, Technician, Service, ServiceRequest, dll.)
├── views/                  # Tampilan antarmuka HTML/PHP berdasarkan modul dan peran
├── .env.example            # Template variabel lingkungan aman
├── .gitignore              # Daftar berkas rahasia & cache yang diabaikan Git
├── README.md               # Dokumentasi lengkap sistem ServisYuk
├── index.php               # Front-controller dan router utama aplikasi
└── *.png                   # Berkas diagram arsitektur perangkat lunak UML & ERD
```

---

## 📄 10. Lisensi

Project ini dikembangkan sebagai karya akademik untuk pemenuhan tugas mata kuliah **Rekayasa Perangkat Lunak (RPL)**. Seluruh kode sumber terbuka untuk tujuan edukasi dan pembelajaran.
