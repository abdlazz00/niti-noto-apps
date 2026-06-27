# Niti Noto — Sistem Informasi Warung Kopi
**Design Document**
Date: 2026-06-27

---

## 1. Overview

Sistem informasi manajemen warung kopi "Niti Noto" berbasis web dengan fitur POS, self-order via QR code, tracking status order realtime, manajemen internal, dan laporan keuangan lengkap untuk Owner.

---

## 2. Tech Stack

| Layer | Teknologi |
|---|---|
| Backend | Laravel 12+ |
| Frontend | Vue 3 via Inertia.js (monolith) |
| Database | PostgreSQL |
| UI Library | PrimeVue |
| UI Style | Vibrant UI, light mode only, background white/slate-50 |
| Animasi | Motion.js (dashboard & laporan) |
| Realtime | Laravel Reverb (WebSocket) |
| Auth Scaffolding | Laravel Breeze (Inertia + Vue stack) |
| Role & Permission | Spatie Laravel Permission |
| Storage | Laravel Storage (foto struk, foto menu, foto user) |

**Arsitektur:** Single Laravel monolith. Semua halaman dirender via Inertia.js dengan dua layout utama: `AppLayout` untuk user internal dan `CustomerLayout` untuk customer self-order.

---

## 3. Roles

| Role | Deskripsi |
|---|---|
| `owner` | Akses penuh: laporan, manajemen semua entitas, approve expense |
| `cashier` | POS, konfirmasi order, terima pembayaran, input expense, kelola menu & meja |
| `staff` | Lihat antrian order, update status pembuatan |
| `customer` | Self-order via QR (guest/login), tracking status order |

Role dikelola via **Spatie Laravel Permission**.

---

## 4. Database Design

### Tabel Utama

#### `users`
| Kolom | Tipe | Keterangan |
|---|---|---|
| id | bigint PK | |
| name | string | |
| email | string unique | |
| password | string | |
| photo | string nullable | path foto profil |
| is_active | boolean | default true |
| email_verified_at | timestamp nullable | |
| remember_token | string nullable | |
| timestamps | | created_at, updated_at |

#### `staff_profiles`
Tabel tambahan untuk data internal user (Owner, Cashier, Staff) yang dibuat oleh Owner.

| Kolom | Tipe | Keterangan |
|---|---|---|
| id | bigint PK | |
| user_id | bigint FK→users | 1:1 |
| phone | string nullable | |
| address | text nullable | |
| join_date | date nullable | |
| photo | string nullable | foto khusus profil staff |
| notes | text nullable | |
| timestamps | | |

> Customer yang register tidak memiliki `staff_profiles`.

#### `tables`
| Kolom | Tipe | Keterangan |
|---|---|---|
| id | bigint PK | |
| name | string | nama/label meja |
| number | integer | nomor meja |
| qr_code | string | UUID unik untuk URL self-order |
| is_active | boolean | |
| timestamps | | |

#### `categories`
| Kolom | Tipe | Keterangan |
|---|---|---|
| id | bigint PK | |
| name | string | |
| timestamps | | |

#### `menu_items`
| Kolom | Tipe | Keterangan |
|---|---|---|
| id | bigint PK | |
| category_id | bigint FK→categories | |
| name | string | |
| price | decimal(10,2) | |
| image | string nullable | |
| is_active | boolean | nonaktif = tidak tampil di self-order |
| timestamps | | |

#### `shifts`
| Kolom | Tipe | Keterangan |
|---|---|---|
| id | bigint PK | |
| cashier_id | bigint FK→users | |
| started_at | timestamp | |
| ended_at | timestamp nullable | null = shift masih berjalan |
| total_revenue | decimal(12,2) | dihitung saat tutup shift |
| timestamps | | |

#### `orders`
| Kolom | Tipe | Keterangan |
|---|---|---|
| id | bigint PK | |
| order_number | string | format: NNT-YYYYMMDD-XXXX |
| table_id | bigint FK→tables | |
| user_id | bigint FK→users nullable | null = guest |
| shift_id | bigint FK→shifts nullable | diisi saat cashier konfirmasi |
| status | enum | menunggu, diterima, sedang_dibuat, siap_diambil, selesai |
| total | decimal(12,2) | |
| notes | text nullable | |
| timestamps | | |

#### `order_items`
| Kolom | Tipe | Keterangan |
|---|---|---|
| id | bigint PK | |
| order_id | bigint FK→orders | |
| menu_item_id | bigint FK→menu_items | |
| qty | integer | |
| price | decimal(10,2) | harga saat order (snapshot) |
| notes | string nullable | catatan per item |
| timestamps | | |

#### `order_status_logs`
| Kolom | Tipe | Keterangan |
|---|---|---|
| id | bigint PK | |
| order_id | bigint FK→orders | |
| status | enum | status baru |
| changed_by | bigint FK→users nullable | null = sistem/guest |
| changed_at | timestamp | |

#### `expense_categories`
| Kolom | Tipe | Keterangan |
|---|---|---|
| id | bigint PK | |
| name | string | |
| timestamps | | |

#### `expenses`
| Kolom | Tipe | Keterangan |
|---|---|---|
| id | bigint PK | |
| title | string | nama pengeluaran |
| amount | decimal(12,2) | |
| category_id | bigint FK→expense_categories | |
| attachment | string nullable | path foto struk |
| status | enum | pending, approved, rejected |
| created_by | bigint FK→users | |
| approved_by | bigint FK→users nullable | |
| approved_at | timestamp nullable | |
| notes | text nullable | catatan approval/rejection |
| timestamps | | |

### Tabel Spatie (otomatis dari package)
`roles`, `permissions`, `model_has_roles`, `model_has_permissions`, `role_has_permissions`

---

## 5. Order Status Flow

```
[Customer submit / Cashier input POS]
         ↓
    MENUNGGU
         ↓ (Cashier konfirmasi)
    DITERIMA  ←──── shift_id diisi di sini
         ↓ (Staff update)
  SEDANG DIBUAT
         ↓ (Staff update)
  SIAP DIAMBIL  ←── broadcast ke display warung
         ↓ (Cashier terima pembayaran)
    SELESAI
```

Setiap perubahan status tercatat di `order_status_logs`.

---

## 6. Realtime Design (Laravel Reverb)

### Channels

| Channel | Tipe | Listener | Event |
|---|---|---|---|
| `orders` | Public | Display warung | `OrderReadyForPickup` |
| `order.{order-id}` | Private | Customer (HP) | `OrderStatusUpdated` |
| `kitchen` | Private | Staff | `NewOrderReceived`, `OrderStatusUpdated` |
| `cashier` | Private | Cashier | `NewOrderReceived`, `ExpenseSubmitted` |
| `owner` | Private | Owner | `ExpenseSubmitted` |

### Alur Broadcast

1. Customer/Cashier submit order → `NewOrderReceived` → `[cashier]`
2. Cashier konfirmasi → `OrderStatusUpdated` → `[order.{id}]` + `NewOrderReceived` → `[kitchen]`
3. Staff update Sedang Dibuat → `OrderStatusUpdated` → `[order.{id}]`
4. Staff update Siap Diambil → `OrderStatusUpdated` → `[order.{id}]` + `OrderReadyForPickup` → `[orders]`
5. Cashier selesaikan → `OrderStatusUpdated` → `[order.{id}]` + remove dari display → `[orders]`
6. Cashier/Staff input expense → `ExpenseSubmitted` → `[owner]`

---

## 7. Module Breakdown

### Module 1 — Authentication
- Menggunakan **Laravel Breeze** (stack Inertia + Vue) sebagai scaffolding auth
- Halaman login, register, password reset di-generate Breeze lalu di-restyle dengan PrimeVue + Vibrant UI
- Satu halaman login untuk semua role; redirect ke dashboard sesuai role setelah login
- Customer: bisa guest (session anonim dengan UUID) atau register/login
- Password reset via email
- Middleware per route group (`role:owner`, `role:cashier`, `role:staff`)

### Module 2 — Table & QR Management
- CRUD meja (nama, nomor, aktif/nonaktif)
- Generate QR code → berisi URL `/{qr_code}/order` (UUID unik per meja)
- Halaman print-ready: cetak QR satu meja atau semua sekaligus
- Akses: Owner, Cashier

### Module 3 — Menu Management
- CRUD menu item: nama, kategori, harga, foto, aktif/nonaktif
- CRUD kategori menu
- Item nonaktif tidak tampil di self-order customer
- Tidak ada manajemen stok
- Akses: Owner, Cashier

### Module 4 — Self Order (Customer)
- Mobile-first, `CustomerLayout`
- Landing page: `/order/{qr_code}` → tampil nama meja + daftar menu aktif
- Filter menu per kategori
- Keranjang belanja → checkout → submit order
- Setelah submit: redirect ke `/order/{order-id}/track` (tracking realtime)
- Guest: session anonim. Login: bisa lihat history order

### Module 5 — POS (Cashier)
- Input order manual: pilih meja → pilih menu → submit
- Daftar order masuk (status `menunggu`) → tombol konfirmasi
- Daftar order siap diambil (status `siap_diambil`) → tombol selesai + terima pembayaran
- Buka/tutup shift harian

### Module 6 — Order Queue (Staff)
- Tampilan antrian: kolom per status (`diterima`, `sedang_dibuat`, `siap_diambil`)
- Tombol update status di setiap order card
- Update realtime via Reverb tanpa refresh

### Module 7 — Display Warung
- Route: `/display` (tidak perlu login)
- Tampilkan `order_number` yang berstatus `siap_diambil`
- Font besar, terbaca dari jarak jauh
- Update realtime via Reverb

### Module 8 — Expense Management
- Input expense: judul, jumlah, kategori, foto struk (upload)
- Akses input: Cashier dan Staff
- Owner mendapat notifikasi → approve atau reject + catatan
- Filter list: tanggal, kategori, status approval
- Akses approve: Owner

### Module 9 — Staff Management (Owner)
- CRUD internal user: nama, email, password, role (owner/cashier/staff), foto
- Otomatis buat `staff_profiles` saat user internal dibuat
- Aktif/nonaktif user tanpa hapus data
- List staff dengan foto, nama, role badge, status

### Module 10 — Dashboard

| Role | Widget |
|---|---|
| Owner | Revenue hari ini, total order hari ini, expense pending, grafik tren 7 hari (Motion.js) |
| Cashier | Ringkasan shift berjalan, order aktif, total revenue shift |
| Staff | Jumlah antrian saat ini, order selesai hari ini |

### Module 11 — Laporan Keuangan (Owner)
- Revenue vs expense per periode → laba bersih
- Breakdown penjualan per kategori menu + top menu terlaris
- Grafik tren harian/mingguan/bulanan dengan animasi Motion.js
- Komparasi periode: bulan ini vs bulan lalu
- Ringkasan per shift dan per Cashier
- Export PDF dan Excel

---

## 8. UI Structure

### Layouts

| Layout | Digunakan | Karakteristik |
|---|---|---|
| `AppLayout` | Owner, Cashier, Staff | Sidebar navigasi, header, responsive |
| `CustomerLayout` | Self-order, tracking | Mobile-first, minimal, no sidebar |
| `DisplayLayout` | `/display` | Fullscreen, font besar, no auth |

### Navigasi Sidebar per Role

**Owner:** Dashboard · Laporan Keuangan · Menu Management · Table Management · Staff Management · Expense Management

**Cashier:** Dashboard · POS · Order Aktif · Expense

**Staff:** Antrian Order

### Routes Customer

| Route | Halaman |
|---|---|
| `/order/{qr_code}` | Menu + keranjang |
| `/order/{qr_code}/checkout` | Review & konfirmasi |
| `/order/{order-id}/track` | Tracking status realtime |
| `/display` | Layar display warung |

### UI Style
- PrimeVue sebagai komponen dasar
- Vibrant UI: aksen warna kuat (amber/orange/green — coffee theme), background `white` / `slate-50`
- Motion.js: animasi counter angka dan chart entrance di dashboard Owner dan halaman laporan
- Light mode only, tidak ada dark mode

---

## 9. Scope Boundaries (Tidak Termasuk MVP)

- Payment gateway (pembayaran manual)
- Inventory / stok bahan baku
- Loyalty program / poin customer
- Multi-branch / multi-outlet
- Notifikasi push (hanya realtime via Reverb dalam app)
