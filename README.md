# Wistek Topup ⚡

<p align="center">
  <strong>Platform Top-up Game Online Tercepat, Terpercaya, dan Terlengkap di Indonesia</strong>
</p>

<p align="center">
  <img src="https://img.shields.io/badge/Laravel-11.x-red?style=for-the-badge&logo=laravel" alt="Laravel 11.x">
  <img src="https://img.shields.io/badge/PHP-8.3-blue?style=for-the-badge&logo=php" alt="PHP 8.3">
  <img src="https://img.shields.io/badge/Database-MySQL-blue?style=for-the-badge&logo=mysql" alt="MySQL">
  <img src="https://img.shields.io/badge/Admin%20Panel-Filament%20v4-orange?style=for-the-badge&logo=laravel" alt="Filament v4">
</p>

---

## 📌 Tentang Wistek Topup

**Wistek Topup** adalah platform penyedia layanan top-up game online otomatis 24 jam nonstop yang dirancang dengan arsitektur modern berbasis **Laravel 11** dan **PHP 8.3**. Aplikasi ini mengintegrasikan layanan pemrosesan produk game otomatis dari **Digiflazz**, gerbang pembayaran otomatis dari **Duitku**, serta sistem notifikasi OTP WhatsApp instan. 

Platform ini juga dilengkapi dengan **Sistem Keanggotaan (Member)** yang memiliki program loyalitas poin, kode referral, profil terverifikasi, dan tampilan antarmuka (UI/UX) premium yang sepenuhnya responsif di semua jenis perangkat (Desktop, Tablet, dan Mobile).

---

## 🚀 Fitur Utama

### 🎮 1. Transaksi & Produk Game Otomatis (Digiflazz)
*   Integrasi API Digiflazz untuk pemrosesan order top-up instan secara otomatis (Mobile Legends, Free Fire, PUBG Mobile, Valorant, dll).
*   Manajemen kategori produk, sub-kategori, nominal pembelian, dan harga secara fleksibel.

### 💳 2. Gerbang Pembayaran Otomatis (Duitku)
*   Sistem pembayaran instan otomatis menggunakan IPN (Instant Payment Notification) callback dari Duitku.
*   Dukungan pembayaran terlengkap:
    *   **QRIS** (QRIS+)
    *   **E-Wallet** (DANA, OVO, ShopeePay, LinkAja)
    *   **Virtual Account** (BCA, Mandiri, BNI, BRI, Permata, dll)
    *   **Convenience Store** (Indomaret)

### 👥 3. Sistem Keanggotaan & Loyalitas Poin (Member Loyalty)
*   **Registrasi & OTP WhatsApp**: Pendaftaran akun instan yang aman dengan verifikasi kode OTP otomatis ke nomor WhatsApp member.
*   **Poin Loyalti**: Dapatkan poin setiap kali melakukan transaksi sukses (Rasio perolehan poin dapat disesuaikan via Admin Panel).
*   **Kedaluwarsa Poin Otomatis**: Poin loyalti memiliki masa kedaluwarsa yang diproses harian secara terjadwal melalui perintah cron `points:expire`.
*   **Program Referral**: Dapatkan bonus poin instan (contoh: 1.000 poin) ketika berhasil mengundang teman yang melakukan transaksi sukses pertamanya.
*   **Manajemen Profil & Foto Profil (Avatar)**: Unggah foto profil member dengan validasi file (Maks 2MB, JPG/JPEG/PNG) dan preview instan.

### 🛠️ 4. Panel Admin Premium (Filament v4)
*   Dashboard admin modern berbasis **Filament v4**.
*   Manajemen pengguna, produk, kategori game, sub-kategori, kode voucher diskon, banner promo, dan konfigurasi umum sistem.
*   Widget visual ringkasan statistik transaksi harian, bulanan, dan total pendapatan.

### 📱 5. Desain UI/UX Premium & Responsif
*   Tema gelap (*dark mode*) yang mewah dengan desain modern, dinamis, dan terpusat.
*   Navbar mobile interaktif dengan sistem **Hamburger Menu tersembunyi** (gaya *Ourastore*) untuk estetika terbaik pada perangkat ponsel.
*   Tata letak formulir dan tabel data yang secara cerdas melipat (*stack*) secara vertikal pada perangkat seluler.

---

## 🛠️ Stack Teknologi

*   **Backend Framework:** Laravel 11.x
*   **Language:** PHP 8.3
*   **Database:** MySQL / MariaDB
*   **Admin Panel:** Filament PHP v4
*   **Frontend Tools:** Blade Templating, Vanilla CSS, Alpine.js, Tailwind CSS (Vite Bundler)
*   **Payment Gateway:** Duitku
*   **Provider API:** Digiflazz
*   **WhatsApp Gateway:** Fonnte / Custom API Gateway

---

## 💾 Langkah Instalasi Lokal

### 1. Klon Repositori
```bash
git clone https://github.com/deyarcipta/wistek-topup.git
cd wistek-topup
```

### 2. Instal Dependencies (Composer & NPM)
```bash
composer install
npm install
npm run build
```

### 3. Konfigurasi Environment File
Salin file `.env.example` ke `.env` dan lengkapi konfigurasi database serta API keys Anda:
```bash
cp .env.example .env
```
Isi konfigurasi penting berikut di `.env`:
```env
APP_TIMEZONE=Asia/Jakarta

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=topup_wistek
DB_USERNAME=root
DB_PASSWORD=

DUITKU_MERCHANT_CODE=your_merchant_code
DUITKU_API_KEY=your_api_key
DUITKU_SANDBOX_MODE=true

DIGIFLAZZ_USERNAME=your_username
DIGIFLAZZ_API_KEY=your_api_key
DIGIFLAZZ_SANDBOX_MODE=true

WHATSAPP_API_URL=https://api.fonnte.com/send
WHATSAPP_TOKEN=your_whatsapp_token
```

### 4. Buat Symlink Storage
```bash
php artisan storage:link
```

### 5. Jalankan Migrasi & Seeder Database
```bash
php artisan migrate --seed
```

### 6. Jalankan Dev Server & Scheduler
Jalankan server aplikasi:
```bash
php artisan serve
```
Untuk menguji scheduler poin kedaluwarsa secara berkala pada komputer lokal:
```bash
php artisan schedule:work
```

---

## 🧪 Pengujian Sistem
Aplikasi ini dilengkapi dengan pengujian otomatis (*Feature Tests*) untuk memastikan keandalan sistem loyalitas poin, validasi OTP, registrasi member, dan fungsi upload foto profil. 

Jalankan pengujian menggunakan PHPUnit:
```bash
php artisan test
```

---

## 📝 Lisensi
Platform ini didistribusikan di bawah lisensi **MIT**. Silakan gunakan dan sesuaikan sesuai kebutuhan proyek Anda.

---
<p align="center">
  Made with ❤️ by <strong>WISTEK</strong>
</p>
