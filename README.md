# Niti Noto Coffee Shop Apps

Sistem Informasi dan Point of Sales (POS) warung kopi **"Niti Noto"** yang modern dan responsif. Sistem ini mencakup alur pemesanan mandiri oleh pelanggan (self-order via QR Code), antrian dapur realtime untuk staff, kasir penjualan (POS) dengan manajemen shift, pencatatan pengeluaran, serta dasbor keuangan terintegrasi bagi Owner.

---

## 🚀 Tech Stack

* **Backend:** Laravel 12 (PHP ^8.2) + Spatie Laravel Permission + Barryvdh Laravel DomPDF
* **Frontend:** Vue 3 (Composition API) + Inertia.js + PrimeVue 4 (Aura Theme) + Tailwind CSS v4 + Motion.js
* **Realtime Server:** Laravel Reverb (WebSockets) + Laravel Echo
* **Database:** PostgreSQL

---

## ✨ Fitur Utama

1. **Multi-Role System:**
   * **Owner:** Mengelola internal staff, menu, meja (generate QR), menyetujui pengeluaran, dan memantau laporan keuangan lengkap.
   * **Cashier:** Membuka/menutup shift kasir, input POS manual, melakukan konfirmasi order masuk, menerima pembayaran, serta mencatat pengeluaran harian.
   * **Staff Dapur:** Memantau antrian pembuatan kopi realtime dan mengubah status pesanan.
   * **Customer (Guest/Login):** Memesan mandiri via scan QR meja (tampilan mobile-first), keranjang belanja, checkout, serta pelacakan status pesanan secara realtime.

2. **Realtime System (Websockets):**
   * Notifikasi instan pesanan masuk untuk Kasir.
   * Pembaruan antrian dapur secara langsung (Kitchen Queue).
   * Live tracking pemesanan untuk pelanggan (konsep *self-pickup*).

3. **Dasbor Keuangan & Laporan Lengkap:**
   * Animasi counter data keuangan dinamis menggunakan `Motion.js`.
   * Visualisasi grafik garis SVG (Revenue vs Expense) dan grafik bar kategori.
   * Fitur ekspor laporan keuangan berformat PDF (kompatibel Dompdf) dan CSV berdasarkan filter periode (Hari ini, Minggu ini, Bulan ini, Kustom).

4. **UI Polish & Premium Layout:**
   * Sidebar navigasi dinamis sesuai role yang dapat di-collapse menjadi mini-sidebar (70px) pada desktop.
   * Profil user dan logout dipindahkan ke header pojok kanan atas dengan dropdown card popup yang elegan.
   * Halaman Profil dilengkapi dengan PrimeVue Tabs untuk memisahkan Edit Profil, Keamanan Kata Sandi, dan Hapus Akun.
   * Halaman visualisasi daftar meja berbasis kartu (*card-based list*) lengkap dengan preview QR code.

---

## 🛠️ Panduan Instalasi & Penggunaan

### 1. Prasyarat
Pastikan Anda sudah menginstal:
* PHP >= 8.2 & Composer
* Node.js & NPM
* PostgreSQL

### 2. Langkah Setup Proyek

1. **Clone repositori dan masuk ke direktori proyek:**
   ```bash
   git clone <repository-url> niti-noto-apps
   cd niti-noto-apps
   ```

2. **Instal dependensi PHP & Node.js:**
   ```bash
   composer install
   npm install
   ```

3. **Salin berkas konfigurasi lingkungan:**
   ```bash
   cp .env.example .env
   ```
   *Buka `.env` dan konfigurasikan koneksi database PostgreSQL (`DB_CONNECTION=pgsql`, `DB_HOST`, `DB_PORT`, `DB_DATABASE`, `DB_USERNAME`, `DB_PASSWORD`), serta port server Reverb.*

4. **Generate Application Key:**
   ```bash
   php artisan key:generate
   ```

5. **Hubungkan direktori penyimpanan (untuk foto staff/menu):**
   ```bash
   php artisan storage:link
   ```

6. **Jalankan Migrasi & Database Seeder:**
   ```bash
   php artisan migrate:fresh --seed
   ```
   *Perintah ini akan membuat semua struktur tabel dan mengisi data awal (roles, akun owner default, contoh menu, data meja, serta kategori pengeluaran).*

---

### 🏃‍♂️ Cara Menjalankan Aplikasi

Jalankan 4 perintah berikut di terminal terpisah (atau gunakan runner):

1. **Jalankan web server Laravel:**
   ```bash
   php artisan serve
   ```

2. **Jalankan Vite development server (untuk frontend):**
   ```bash
   npm run dev
   # Atau untuk compile bundle produksi: npm run build
   ```

3. **Jalankan server WebSockets Laravel Reverb (realtime server):**
   ```bash
   php artisan reverb:start --port=8081
   ```

4. **Jalankan queue worker (untuk memproses broadcast event di background):**
   ```bash
   php artisan queue:work
   ```

---

## 🔑 Akun Uji Coba Default (Seeders)

Setelah menjalankan `php artisan db:seed`, Anda dapat langsung login menggunakan akun default berikut:

* **Owner:**
  * Email: `owner@nitnoto.com`
  * Sandi: `password`

* **Meja Bawaan (Untuk Uji Coba Self-Order):**
  Akses URL pelanggan langsung melalui rute berikut (UUID didapat dari database seeder):
  `http://localhost:8000/order/{qr_code_uuid}`

---

## 🧪 Rangkaian Pengujian (Testing)

Aplikasi dilengkapi dengan rangkaian pengujian fitur (*feature testing*) dan otentikasi. Anda dapat memverifikasi integritas sistem kapan saja dengan perintah:

```bash
php artisan test
```
