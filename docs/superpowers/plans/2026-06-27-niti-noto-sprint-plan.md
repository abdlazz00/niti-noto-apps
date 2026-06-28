# Niti Noto — Sprint Plan

**Goal:** Bangun sistem informasi warung kopi Niti Noto secara bertahap, per sprint kecil yang masing-masing menghasilkan deliverable yang bisa ditest.

**Stack:** Laravel 12 + Inertia.js + Vue 3 + PrimeVue + PostgreSQL + Reverb + Spatie Permission + Motion.js + Laravel Breeze (auth scaffolding)

**Referensi:** `docs/superpowers/specs/2026-06-27-niti-noto-design.md`

**Architecture Patterns (wajib di semua sprint):**
- **BE:** Service Repository Pattern — `Controller → Service → Repository → Model`
- **FE:** Feature-Based Component Architecture — komponen di `Components/{Role}/{Feature}/`, page di `Pages/` harus thin

---

## Sprint 0 — Project Setup & Foundation

**Goal:** Laravel project berjalan dengan Inertia + Vue + PrimeVue + Reverb + Spatie terpasang.

### Task 0.1 — Init Laravel Project
- [x] `composer create-project laravel/laravel niti-noto "^12.0"`
- [x] Konfigurasi `.env` → DB_CONNECTION=pgsql, set DB_HOST, DB_PORT, DB_DATABASE, DB_USERNAME, DB_PASSWORD
- [x] `php artisan migrate` (default migrations)
- [x] Commit: `chore: init laravel 12 project`

### Task 0.2 — Install Inertia.js (Server Side)
- [x] `composer require inertiajs/inertia-laravel`
- [x] Publish middleware: `php artisan inertia:middleware`
- [x] Tambah `HandleInertiaRequests` ke `bootstrap/app.php` middleware web group
- [x] Commit: `chore: install inertia server-side`

### Task 0.3 — Install Frontend Dependencies
- [x] `npm install @inertiajs/vue3 vue@^3`
- [x] `npm install primevue @primevue/themes primeicons`
- [x] `npm install motion`
- [x] `npm install laravel-echo pusher-js`
- [x] Commit: `chore: install frontend dependencies`

### Task 0.4 — Konfigurasi Vite + Vue + Inertia
- [x] Update `vite.config.js`:
```js
import { defineConfig } from 'vite'
import laravel from 'laravel-vite-plugin'
import vue from '@vitejs/plugin-vue'

export default defineConfig({
    plugins: [
        laravel({ input: ['resources/js/app.js'], refresh: true }),
        vue({ template: { transformAssetUrls: { base: null, includeAbsolute: false } } }),
    ],
})
```
- [ ] Update `resources/js/app.js`:
```js
import { createApp, h } from 'vue'
import { createInertiaApp } from '@inertiajs/vue3'
import PrimeVue from 'primevue/config'
import Aura from '@primevue/themes/aura'
import ToastService from 'primevue/toastservice'
import ConfirmationService from 'primevue/confirmationservice'
import '../css/app.css'

createInertiaApp({
    resolve: name => {
        const pages = import.meta.glob('./Pages/**/*.vue', { eager: true })
        return pages[`./Pages/${name}.vue`]
    },
    setup({ el, App, props, plugin }) {
        createApp({ render: () => h(App, props) })
            .use(plugin)
            .use(PrimeVue, { theme: { preset: Aura }, ripple: true })
            .use(ToastService)
            .use(ConfirmationService)
            .mount(el)
    },
})
```
- [x] `npm run dev` — pastikan tidak ada error
- [x] Commit: `chore: configure vite, vue, inertia, primevue`

### Task 0.5 — Install Spatie Laravel Permission
- [x] `composer require spatie/laravel-permission`
- [x] `php artisan vendor:publish --provider="Spatie\Permission\PermissionServiceProvider"`
- [x] Tambah `HasRoles` trait ke `User` model
- [x] Commit: `chore: install spatie laravel permission`

### Task 0.6 — Install Laravel Reverb
- [x] `composer require laravel/reverb`
- [x] `php artisan reverb:install`
- [x] Konfigurasi `.env` Reverb keys (REVERB_APP_ID, REVERB_APP_KEY, REVERB_APP_SECRET)
- [x] Update `resources/js/bootstrap.js` — setup Echo dengan Reverb:
```js
import Echo from 'laravel-echo'
import Pusher from 'pusher-js'

window.Pusher = Pusher
window.Echo = new Echo({
    broadcaster: 'reverb',
    key: import.meta.env.VITE_REVERB_APP_KEY,
    wsHost: import.meta.env.VITE_REVERB_HOST,
    wsPort: import.meta.env.VITE_REVERB_PORT,
    wssPort: import.meta.env.VITE_REVERB_PORT,
    forceTLS: false,
    enabledTransports: ['ws', 'wss'],
})
```
- [x] Import `bootstrap.js` di `app.js`
- [x] Commit: `chore: install and configure laravel reverb`

### Task 0.7 — Buat 3 Layout Vue
- [x] Buat `resources/js/Layouts/AppLayout.vue` — sidebar + header untuk internal user
- [x] Buat `resources/js/Layouts/CustomerLayout.vue` — mobile-first, minimal navbar
- [x] Buat `resources/js/Layouts/DisplayLayout.vue` — fullscreen, no auth, font besar
- [x] Buat `resources/js/Pages/Home.vue` (placeholder) untuk verify Inertia berjalan
- [x] Tambah route `Route::get('/', fn() => inertia('Home'))` di `routes/web.php`
- [x] `npm run dev` + buka browser — pastikan PrimeVue komponen render
- [x] Commit: `feat: add app, customer, display layouts`

---

## Sprint 1 — Database: Migrations & Models

**Goal:** Semua tabel terbuat di PostgreSQL, semua Model dengan relasi siap.

### Task 1.1 — Migration: users & staff_profiles
- [x] Update migration `create_users_table` — tambah kolom `photo` (string nullable) dan `is_active` (boolean default true)
- [x] Buat migration: `php artisan make:migration create_staff_profiles_table`
```php
Schema::create('staff_profiles', function (Blueprint $table) {
    $table->id();
    $table->foreignId('user_id')->constrained()->cascadeOnDelete();
    $table->string('phone')->nullable();
    $table->text('address')->nullable();
    $table->date('join_date')->nullable();
    $table->string('photo')->nullable();
    $table->text('notes')->nullable();
    $table->timestamps();
});
```
- [x] `php artisan migrate`
- [x] Commit: `feat: migration users and staff_profiles`

### Task 1.2 — Migration: categories & menu_items
- [x] `php artisan make:migration create_categories_table`
- [x] `php artisan make:migration create_menu_items_table`
```php
// categories
Schema::create('categories', function (Blueprint $table) {
    $table->id();
    $table->string('name');
    $table->timestamps();
});

// menu_items
Schema::create('menu_items', function (Blueprint $table) {
    $table->id();
    $table->foreignId('category_id')->constrained()->restrictOnDelete();
    $table->string('name');
    $table->decimal('price', 10, 2);
    $table->string('image')->nullable();
    $table->boolean('is_active')->default(true);
    $table->timestamps();
});
```
- [x] `php artisan migrate`
- [x] Commit: `feat: migration categories and menu_items`

### Task 1.3 — Migration: tables
- [x] `php artisan make:migration create_tables_table`
```php
Schema::create('tables', function (Blueprint $table) {
    $table->id();
    $table->string('name');
    $table->integer('number');
    $table->string('qr_code')->unique();
    $table->boolean('is_active')->default(true);
    $table->timestamps();
});
```
- [x] `php artisan migrate`
- [x] Commit: `feat: migration tables`

### Task 1.4 — Migration: shifts & orders & order_items
- [x] `php artisan make:migration create_shifts_table`
- [x] `php artisan make:migration create_orders_table`
- [x] `php artisan make:migration create_order_items_table`
```php
// shifts
Schema::create('shifts', function (Blueprint $table) {
    $table->id();
    $table->foreignId('cashier_id')->constrained('users');
    $table->timestamp('started_at');
    $table->timestamp('ended_at')->nullable();
    $table->decimal('total_revenue', 12, 2)->default(0);
    $table->timestamps();
});

// orders
Schema::create('orders', function (Blueprint $table) {
    $table->id();
    $table->string('order_number')->unique();
    $table->foreignId('table_id')->constrained()->restrictOnDelete();
    $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
    $table->foreignId('shift_id')->nullable()->constrained()->nullOnDelete();
    $table->enum('status', ['menunggu','diterima','sedang_dibuat','siap_diambil','selesai'])->default('menunggu');
    $table->decimal('total', 12, 2)->default(0);
    $table->text('notes')->nullable();
    $table->timestamps();
});

// order_items
Schema::create('order_items', function (Blueprint $table) {
    $table->id();
    $table->foreignId('order_id')->constrained()->cascadeOnDelete();
    $table->foreignId('menu_item_id')->constrained()->restrictOnDelete();
    $table->integer('qty');
    $table->decimal('price', 10, 2);
    $table->string('notes')->nullable();
    $table->timestamps();
});
```
- [x] `php artisan migrate`
- [x] Commit: `feat: migration shifts, orders, order_items`

### Task 1.5 — Migration: order_status_logs
- [x] `php artisan make:migration create_order_status_logs_table`
```php
Schema::create('order_status_logs', function (Blueprint $table) {
    $table->id();
    $table->foreignId('order_id')->constrained()->cascadeOnDelete();
    $table->enum('status', ['menunggu','diterima','sedang_dibuat','siap_diambil','selesai']);
    $table->foreignId('changed_by')->nullable()->constrained('users')->nullOnDelete();
    $table->timestamp('changed_at');
});
```
- [x] `php artisan migrate`
- [x] Commit: `feat: migration order_status_logs`

### Task 1.6 — Migration: expense_categories & expenses
- [x] `php artisan make:migration create_expense_categories_table`
- [x] `php artisan make:migration create_expenses_table`
```php
// expense_categories
Schema::create('expense_categories', function (Blueprint $table) {
    $table->id();
    $table->string('name');
    $table->timestamps();
});

// expenses
Schema::create('expenses', function (Blueprint $table) {
    $table->id();
    $table->string('title');
    $table->decimal('amount', 12, 2);
    $table->foreignId('category_id')->constrained('expense_categories')->restrictOnDelete();
    $table->string('attachment')->nullable();
    $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
    $table->foreignId('created_by')->constrained('users');
    $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
    $table->timestamp('approved_at')->nullable();
    $table->text('notes')->nullable();
    $table->timestamps();
});
```
- [x] `php artisan migrate`
- [x] Commit: `feat: migration expense_categories and expenses`

### Task 1.7 — Models & Relationships
- [x] Update `User` model — tambah `HasRoles` (Spatie), fillable, relasi `staffProfile()`, `orders()`, `shifts()`
- [x] Buat `StaffProfile` model — fillable, relasi `user()`
- [x] Buat `Category` model — fillable, relasi `menuItems()`
- [x] Buat `MenuItem` model — fillable, `scopeActive()`, relasi `category()`
- [x] Buat `Table` model — fillable, `scopeActive()`, relasi `orders()`
- [x] Buat `Shift` model — fillable, relasi `cashier()`, `orders()`
- [x] Buat `Order` model — fillable, casts `status` ke enum, relasi `table()`, `user()`, `shift()`, `items()`, `statusLogs()`
- [x] Buat `OrderItem` model — fillable, relasi `order()`, `menuItem()`
- [x] Buat `OrderStatusLog` model — fillable, relasi `order()`, `changedBy()`
- [x] Buat `ExpenseCategory` model — fillable, relasi `expenses()`
- [x] Buat `Expense` model — fillable, relasi `category()`, `createdBy()`, `approvedBy()`
- [x] Commit: `feat: all models with relationships`

### Task 1.8 — Seeders
- [x] `RoleSeeder` — buat roles: `owner`, `cashier`, `staff`, `customer`
- [x] `UserSeeder` — buat user owner default (email: `owner@nitnoto.com`, pass: `password`) + assign role owner
- [x] `CategorySeeder` — 3-4 kategori sample (Kopi, Non-Kopi, Makanan, Snack)
- [x] `MenuItemSeeder` — 5-8 item sample per kategori
- [x] `TableSeeder` — 5 meja sample dengan qr_code UUID
- [x] `ExpenseCategorySeeder` — bahan baku, operasional, gaji, lain-lain
- [x] Update `DatabaseSeeder` — jalankan semua seeder
- [x] `php artisan db:seed`
- [x] Commit: `feat: database seeders with sample data`

---

## Sprint 2 — Authentication

**Goal:** Login berfungsi untuk semua role, redirect ke dashboard masing-masing. Customer bisa register/login atau guest.

### Task 2.0 — Install Laravel Breeze
- [x] `composer require laravel/breeze --dev`
- [x] `php artisan breeze:install vue` (stack Vue + Inertia, tanpa flag --inertia di v2)
- [x] `npm install` (Breeze menambah beberapa dev dependencies)
- [x] `npm run build` untuk verify kompilasi
- [x] `php artisan migrate`
- [x] Replace halaman auth default Breeze (`Login.vue`, `Register.vue`) dengan versi PrimeVue
- [x] Fix Tailwind v4: update `vite.config.js`, `app.css` (@import "tailwindcss"), clear `postcss.config.js`
- [x] Commit: `chore: install laravel breeze with inertia vue stack`

### Task 2.1 — Route Groups & Middleware
- [x] Setup route groups di `routes/web.php`:
```php
// Guest/Customer routes
Route::get('/order/{qrCode}', [CustomerOrderController::class, 'index'])->name('order.menu');
Route::get('/display', [DisplayController::class, 'index'])->name('display');

// Auth routes
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);
    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'register']);
});
Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth')->name('logout');

// Owner routes
Route::middleware(['auth', 'role:owner'])->prefix('owner')->name('owner.')->group(function () {
    Route::get('/dashboard', [OwnerDashboardController::class, 'index'])->name('dashboard');
});

// Cashier routes
Route::middleware(['auth', 'role:cashier'])->prefix('cashier')->name('cashier.')->group(function () {
    Route::get('/dashboard', [CashierDashboardController::class, 'index'])->name('dashboard');
});

// Staff routes
Route::middleware(['auth', 'role:staff'])->prefix('staff')->name('staff.')->group(function () {
    Route::get('/dashboard', [StaffDashboardController::class, 'index'])->name('dashboard');
});
```
- [x] Commit: `feat: route groups with role middleware`

### Task 2.2 — AuthController
- [x] Modifikasi `AuthenticatedSessionController::store()` — redirect ke dashboard sesuai role setelah login
- [x] Modifikasi `RegisteredUserController::store()` — assign role `customer` saat register
- [x] Update `HandleInertiaRequests` — share `auth.user` dengan roles + flash messages
- [x] Commit: `feat: auth controller login logout register`

### Task 2.3 — Halaman Login (Vue)
- [x] Buat `resources/js/Pages/Auth/Login.vue`
  - Form: email, password, remember me
  - PrimeVue: `InputText`, `Password`, `Button`, `Checkbox`
  - Tampilkan error validasi
  - Link ke `/register`
  - Logo Niti Noto di atas form
  - Centered card, bg putih, vibrant accent color
- [x] Commit: `feat: login page with primevue`

### Task 2.4 — Halaman Register Customer (Vue)
- [x] Buat `resources/js/Pages/Auth/Register.vue`
  - Form: name, email, password, password_confirmation
  - Mobile-friendly (customer sering register dari HP)
  - Link balik ke login
- [x] Commit: `feat: customer register page`

### Task 2.5 — Placeholder Dashboard per Role
- [x] Buat `resources/js/Pages/Owner/Dashboard.vue` (stat cards placeholder + chart placeholder)
- [x] Buat `resources/js/Pages/Cashier/Dashboard.vue` (stat cards placeholder)
- [x] Buat `resources/js/Pages/Staff/Dashboard.vue` (stat cards placeholder)
- [x] Pasang `AppLayout` di setiap halaman via `defineOptions({ layout: AppLayout })`
- [x] Commit: `feat: placeholder dashboards per role`

---

## Sprint 3 — Staff Management (Owner)

**Goal:** Owner bisa CRUD internal user (owner/cashier/staff) lengkap dengan foto dan profil.

### Task 3.1 — StaffController & Routes
- [x] Buat `app/Http/Controllers/Owner/StaffController.php` (resource controller)
- [x] Refaktor web.php ke proper owner/cashier/staff route groups dengan prefix + name
- [x] Tambah `Route::resource('staff', StaffController::class)` + toggle-active route
- [x] Commit: `feat: staff routes`

### Task 3.2 — StaffController Logic
- [x] `index()` — return list semua internal user (role: owner/cashier/staff) dengan staffProfile
- [x] `create()` — return form create
- [x] `store(Request $request)` — validasi, upload foto ke storage/staff, buat User + role + StaffProfile
- [x] `edit($id)` — return user + staffProfile
- [x] `update(Request $request, $id)` — password nullable, ganti foto jika ada, syncRoles, updateOrCreate staffProfile
- [x] `destroy($id)` — soft-delete: `is_active = false`
- [x] `toggleActive($id)` — toggle is_active
- [x] Commit: `feat: staff controller crud logic`

### Task 3.3 — Halaman List Staff (Vue)
- [x] Buat `resources/js/Pages/Owner/Staff/Index.vue`
  - PrimeVue `DataTable` dengan kolom: foto, nama/email, role badge (Tag), status badge, aksi
  - Search filter nama/email, paginator
  - Tombol tambah, edit, toggle aktif, nonaktifkan (ConfirmDialog)
- [x] Commit: `feat: staff index page`

### Task 3.4 — Halaman Create/Edit Staff (Vue)
- [x] Buat `resources/js/Pages/Owner/Staff/Create.vue`
- [x] Buat `resources/js/Pages/Owner/Staff/Edit.vue`
  - Form: nama, email, password, role (Select), foto upload + preview, phone, address, join_date (DatePicker), notes
- [x] Commit: `feat: staff create and edit pages`

### Task 3.5 — Storage Link & Foto
- [x] `php artisan storage:link` — public/storage → storage/app/public
- [x] Foto staff di-serve via `/storage/staff/filename`
- [x] `npm run build` — build sukses
- [x] Commit: `feat: sprint 3 - staff management`

---

## Sprint 4 — Menu Management

**Goal:** Owner dan Cashier bisa kelola kategori dan menu item lengkap dengan foto.

### Task 4.1 — Category CRUD
- [x] Buat `app/Http/Controllers/Owner/CategoryController.php`
- [x] Routes di owner group: `Route::resource('categories', CategoryController::class)->except(['show'])`
- [x] Logic: index, store (validasi name), update, destroy (cek jika ada menu item → tolak delete)
- [x] Buat `resources/js/Pages/Owner/Menu/Categories.vue`
  - DataTable kategori, inline edit atau modal, tombol hapus dengan konfirmasi
- [x] Commit: `feat: category crud`

### Task 4.2 — MenuItem CRUD - Controller
- [x] Buat `app/Http/Controllers/Owner/MenuItemController.php` (resource)
- [x] Routes: `Route::resource('menu-items', MenuItemController::class)->except(['show'])`
- [x] `store()`: validasi name, category_id, price, image (nullable, image), is_active; upload foto ke `storage/app/public/menu/`
- [x] `update()`: sama, foto baru replace foto lama
- [x] `destroy()`: hapus foto dari storage, hapus record
- [x] `toggleActive()`: toggle is_active
- [x] Commit: `feat: menu item controller`

### Task 4.3 — Halaman Menu Items (Vue)
- [x] Buat `resources/js/Pages/Owner/Menu/Index.vue`
  - Grid atau DataTable menu item dengan gambar thumbnail
  - Filter per kategori, search nama
  - Badge aktif/nonaktif, toggle switch
  - Tombol tambah, edit, hapus
- [x] Commit: `feat: menu item index page`

### Task 4.4 — Create/Edit Menu Item (Vue)
- [x] Buat `resources/js/Pages/Owner/Menu/Create.vue`
- [x] Buat `resources/js/Pages/Owner/Menu/Edit.vue`
  - Form: nama, kategori (dropdown), harga, foto (upload + preview), status aktif
- [x] Commit: `feat: menu create and edit pages`

### Task 4.5 — Sinkronisasi Cashier
- [x] Duplikasi route menu management ke cashier group (atau gunakan shared controller)
- [x] Pastikan Cashier juga bisa akses Menu Management
- [x] Commit: `feat: cashier can access menu management`

---

## Sprint 5 — Table & QR Management

**Goal:** Owner/Cashier bisa kelola meja, generate QR code, dan print.

### Task 5.1 — Install QR Code Package
- [x] `npm install qrcode` (client-side generation, simplesoftwareio tidak tersedia offline)
- [x] Commit: `chore: install qr code package`

### Task 5.2 — TableController & Routes
- [x] Buat `app/Http/Controllers/Owner/TableController.php`
- [x] Routes (print-all sebelum resource untuk hindari konflik):
```php
Route::get('tables/print-all', [TableController::class, 'printAll'])->name('tables.print-all');
Route::resource('tables', TableController::class)->except(['show']);
Route::get('tables/{table}/qr', [TableController::class, 'qr'])->name('tables.qr');
Route::patch('tables/{table}/toggle-active', [TableController::class, 'toggleActive'])->name('tables.toggle-active');
```
- [x] `store()`: validasi name, number; generate UUID untuk qr_code; simpan
- [x] `qr($id)`: return halaman print QR satu meja
- [x] `printAll()`: return halaman print semua meja aktif
- [x] Commit: `feat: table controller with qr generation`

### Task 5.3 — Halaman Table Management (Vue)
- [x] Buat `resources/js/Pages/Owner/Table/Index.vue`
  - DataTable: nomor, nama, QR preview kecil, status aktif, aksi
  - Tombol "Print QR", toggle aktif, edit, hapus
- [x] Buat `Components/Owner/Table/QRCanvas.vue` — client-side QR via npm:qrcode
- [x] Buat `Components/Owner/Table/TableList.vue` — DataTable dengan QR preview
- [x] Commit: `feat: table index page`

### Task 5.4 — Halaman Print QR (Vue)
- [x] Buat `resources/js/Pages/Owner/Table/Print.vue`
  - Layout print-friendly (A4 / kartu grid)
  - Tampilkan QR code + nama meja + nomor meja
  - Tombol "Print" → trigger `window.print()`
  - CSS `@media print` — sembunyikan controls, tampilkan QR penuh
- [x] QR code di-generate client-side via QRCanvas component
- [x] Commit: `feat: qr print page`

---

## Sprint 6 — Self Order: Menu & Cart (Customer)

**Goal:** Customer scan QR → landing di halaman menu mobile-friendly → bisa pilih menu & keranjang.

### Task 6.1 — CustomerOrderController
- [x] Buat `app/Http/Controllers/Customer/OrderController.php`
- [x] Route: `Route::get('/order/{qrCode}', [CustomerOrderController::class, 'menu'])->name('order.menu')` (public)
- [x] `menu($qrCode)`: cari Table by qr_code+is_active, load active items + categories, return Inertia
- [x] Commit: `feat: customer order controller menu`

### Task 6.2 — Halaman Menu Customer (Vue)
- [x] Buat `resources/js/Pages/Customer/Order/Menu.vue` — gunakan `CustomerLayout`
- [x] CustomerLayout diupdate: auto-show table info (nomor + nama) dari Inertia page props
- [x] Tabs/filter kategori horizontal (scroll), `Semua` default
- [x] Grid 2-kolom menu items: foto, nama, harga, tombol tambah + inline qty control
- [x] Floating cart button di bawah (badge qty + total), animasi Transition
- [x] State cart dikelola via `useCart` composable (sessionStorage per-table)
- [x] Commit: `feat: customer menu page mobile-first`

### Task 6.3 — Cart Component
- [x] Buat `resources/js/Components/Customer/Cart.vue`
  - PrimeVue Drawer (slide-up bottom)
  - List item di cart: thumbnail, nama, harga, qty control (+/-)
  - Total harga
  - Tombol "Pesan Sekarang" → emit checkout → navigate ke checkout (Sprint 7)
- [x] Buat `resources/js/composables/useCart.js` — cart state + sessionStorage persistence
- [x] Commit: `feat: cart component`

---

## Sprint 7 — Self Order: Checkout & Order Submission

**Goal:** Customer bisa review order, submit, dan mendapat nomor order.

### Task 7.1 — Checkout Route & Controller
- [x] Route: `GET /order/{qrCode}/checkout` → `checkout()` (return page, cart di client)
- [x] Route: `POST /order/{qrCode}` → `store()` (submit order)
- [x] Route: `GET /order/track/{order}` → `TrackController::show()` (placeholder, Sprint 8 add realtime)
- [x] `checkout($qrCode)`: find table, return Checkout page (cart dari client sessionStorage)
- [x] `store()`: validate items, delegate ke CustomerOrderService, redirect ke order.track
- [x] CustomerOrderService: price snapshot dari DB, hitung total, create order+items+statusLog
- [x] Commit: `feat: order checkout and store`

### Task 7.2 — Order Number Generator
- [x] Buat `app/Services/OrderNumberService.php` — generate `NNT-YYYYMMDD-XXXX`
- [x] Buat `app/Repositories/OrderRepository.php` — create, createItems (bulk insert), createStatusLog, findWithDetails
- [x] Commit: `feat: order number generator service`

### Task 7.3 — Halaman Checkout (Vue)
- [x] Buat `resources/js/Pages/Customer/Order/Checkout.vue`
  - Baca cart dari `useCart(qrCode)` composable (sessionStorage)
  - Readonly list: thumbnail, nama, qty × harga, subtotal
  - Field catatan (Textarea, opsional)
  - `router.post()` ke order.store, clear cart on success
- [x] Buat `resources/js/Pages/Customer/Order/Track.vue` (placeholder Sprint 8)
  - Status stepper 5 langkah (menunggu→selesai) dengan highlight current step
  - Daftar item + total + catatan
  - Sprint 8 akan tambah Reverb realtime subscription
- [x] Commit: `feat: checkout page`

---

## Sprint 8 — Order Tracking & Realtime

**Goal:** Customer bisa track status order realtime di HP, display warung update otomatis.

### Task 8.1 — Reverb Events
- [x] `NewOrderReceived` — ShouldBroadcastNow, PrivateChannel('cashier')
- [x] `OrderStatusUpdated` — ShouldBroadcastNow, public Channel('order.{id}') — guest-safe, no auth needed
- [x] `OrderReadyForPickup` — ShouldBroadcastNow, public Channel('orders')
- [x] `OrderCompleted` — ShouldBroadcastNow, public Channel('orders')
- [x] Channels: cashier + kitchen private di routes/channels.php
- [x] CustomerOrderService dispatch NewOrderReceived after placeOrder
- [x] Commit: `feat: reverb broadcast events`

### Task 8.2 — TrackController
- [x] Sudah dibuat di Sprint 7: TrackController::show(), GET /order/track/{order} (signed)
- [x] Commit: already committed in Sprint 7

### Task 8.3 — Halaman Tracking Customer (Vue)
- [x] Track.vue diupdate:
  - `currentStatus` ref (reactive, diupdate oleh event)
  - `Echo.channel('order.{id}').listen('OrderStatusUpdated', ...)` — public channel (guest-safe)
  - Status stepper update realtime dengan transisi smooth
  - Full-screen amber overlay ketika status `siap_diambil` dengan tombol dismiss
  - Live indicator pulse dot di bawah stepper
- [x] Commit: `feat: order tracking page with realtime`

### Task 8.4 — Display Warung
- [x] `DisplayController::index()` — load `siap_diambil` orders dengan table
- [x] Route: `GET /display` (public)
- [x] `Display/Index.vue` (DisplayLayout):
  - Grid amber cards: nomor order besar (clamp font untuk TV), nama meja
  - TransitionGroup scale animation saat card masuk/keluar
  - `Echo.channel('orders').listen('OrderReadyForPickup', ...)` → tambah ke grid
  - `Echo.channel('orders').listen('OrderCompleted', ...)` → hapus dari grid
- [x] Commit: `feat: display warung with realtime`

---

## Sprint 9 — POS (Cashier)

**Goal:** Cashier bisa input order manual, konfirmasi order masuk, terima pembayaran, dan kelola shift.

### Task 9.1 — Shift Management
- [x] `ShiftRepository` — findActiveForCashier, create, end
- [x] `ShiftService` — start (guard active), end (sum revenue), getCurrent
- [x] `ShiftController` — POST start/end, redirect back with flash
- [x] Routes: cashier.shift.start, cashier.shift.end
- [x] Commit: part of `feat: sprint 9`

### Task 9.2 — POS Order Input Controller
- [x] `KitchenNewOrder` event — PrivateChannel('kitchen'), payload: order + items list
- [x] `OrderRepository` extended — updateStatus, assignShift, getByStatuses
- [x] `CashierOrderService` — placeOrder (with shift_id), confirmOrder (diterima + broadcast), completeOrder (selesai + broadcast)
- [x] `StorePosOrderRequest` — validate table_id, items, notes
- [x] `PosController` — index (menu+tables+shift), store (place order from POS)
- [x] Routes: cashier.pos, cashier.pos.store
- [x] Commit: part of `feat: sprint 9`

### Task 9.3 — Halaman POS (Vue)
- [x] `Cashier/Pos/Index.vue`:
  - Shift indicator bar: mulai/tutup shift via router.post
  - Category filter tabs
  - Menu item grid: klik tambah ke cart, badge qty di gambar
  - Panel kanan: meja Select, cart list dengan +/- qty, notes Textarea, total, tombol submit
- [x] Commit: part of `feat: sprint 9`

### Task 9.4 — Order Confirmation Controller
- [x] `CashierOrderController` — index (menunggu + siap_diambil), confirm, complete
- [x] `confirmOrder`: menunggu → diterima, assign shift, log, broadcast OrderStatusUpdated + KitchenNewOrder
- [x] `completeOrder`: siap_diambil → selesai, log, broadcast OrderStatusUpdated + OrderCompleted
- [x] Routes: cashier.orders, cashier.orders.confirm, cashier.orders.complete
- [x] Commit: part of `feat: sprint 9`

### Task 9.5 — Halaman Order Aktif Cashier (Vue)
- [x] `Cashier/Order/Index.vue`:
  - Tab "Menunggu Konfirmasi" / "Siap Diambil" dengan badge count
  - Card per order: nomor, meja, item list, total, waktu
  - Tombol "Konfirmasi" → PATCH confirm, hapus dari list (client-side)
  - Tombol "Selesai / Terima Bayar" → PATCH complete, hapus dari list
  - Subscribe `cashier` private → NewOrderReceived: Toast + tambah ke pending list
  - Subscribe `orders` public → OrderReadyForPickup: Toast + tambah ke siap_diambil; OrderCompleted: hapus
  - TransitionGroup slide/scale animation
- [x] Commit: `feat: sprint 9 - POS cashier with shift management and order confirmation`

---

## Sprint 10 — Order Queue (Staff)

**Goal:** Staff lihat antrian realtime dan bisa update status pembuatan.

### Task 10.1 — Staff Queue Controller
- [x] `StaffQueueService` — getQueue (split diterima/sedang_dibuat), updateStatus (guard valid transitions, broadcast)
- [x] `UpdateQueueStatusRequest` — validate status in [sedang_dibuat, siap_diambil]
- [x] `QueueController` — index (pass diterima/sedangDibuat props), updateStatus (delegate to service)
- [x] Routes: staff.queue, staff.queue.update
- [x] Commit: part of `feat: sprint 10`

### Task 10.2 — Halaman Queue Staff (Vue)
- [x] `Staff/Queue.vue`:
  - 2-column layout: Diterima (amber) | Sedang Dibuat (blue)
  - Card per order: nomor, meja, item list + catatan, badge status
  - Tombol "Mulai Buat" (diterima → sedang_dibuat) — memindah card antar kolom client-side
  - Tombol "Siap Diambil" (sedang_dibuat → siap_diambil) — hapus dari queue
  - Subscribe `kitchen` private channel → KitchenNewOrder: push ke diterima + Motion.js animate-in
  - Motion.js `animate()` dengan opacity+y+scale ease-out pada setiap card yang masuk
- [x] Commit: `feat: sprint 10 - staff queue with realtime and motion animations`

---

## Sprint 11 — Expense Management

**Goal:** Cashier/Staff input expense dengan foto, Owner approve/reject.

### Task 11.1 — ExpenseCategory Management (Owner)
- [x] `ExpenseCategoryRepository` — getAll, create, update, delete, hasExpenses
- [x] `ExpenseCategoryController` — store/update/destroy (back with flash, guard hasExpenses on delete)
- [x] Routes: owner.expense-categories.store/update/destroy
- [x] `owner` PrivateChannel registered in routes/channels.php

### Task 11.2 — Expense Input Controller
- [x] `ExpenseRepository` — getByUser, getFiltered (status/category_id/from/to), create, approve, reject
- [x] `ExpenseSubmitted` event — PrivateChannel('owner'), payload: id, title, amount, category_name, submitted_by_name
- [x] `ExpenseService` — getMyExpenses, getAllForOwner, store (upload attachment + broadcast), approve/reject (guard pending)
- [x] `StoreExpenseRequest` — validate title, amount, category_id, attachment (image, max 2MB)
- [x] `CashierExpenseController` — index (own expenses + categories), store
- [x] Routes: cashier.expenses, cashier.expenses.store

### Task 11.3 — Expense Approval Controller (Owner)
- [x] `RejectExpenseRequest` — validate notes required
- [x] `OwnerExpenseController` — index (filtered), approve, reject
- [x] Routes: owner.expenses, owner.expenses.approve, owner.expenses.reject

### Task 11.4 — Halaman Expense (Vue)
- [x] `Cashier/Expense/Index.vue`:
  - Form: title, amount (Rp prefix), category Select, file input + preview
  - List: attachment thumbnail, title, category, date, status badge (pending/disetujui/ditolak)
  - Alasan ditolak shown bila status rejected
- [x] `Owner/Expense/Index.vue`:
  - Status filter tabs + category Select (reactive, auto-applies on change)
  - List: thumbnail dengan lightbox click, info submitter+tanggal, badge status
  - Tombol Setujui + Tolak (pending only)
  - Reject Dialog dengan Textarea alasan
  - Category management Dialog: inline add/edit/delete
- [x] Commit: `feat: sprint 11 - expense management with owner approval flow`

---

## Sprint 12 — Dashboard (Semua Role)

**Goal:** Dashboard informatif per role dengan animasi Motion.js untuk Owner.

### Task 12.1 — Dashboard Data (Owner)
- [x] `OwnerDashboardController` — revenue today, orders today, expense pending, laba bersih (revenue - approved expenses), 7-day chartData array [{label, revenue}]
- [x] `CashierDashboardController` — shift aktif, orders_today, completed_today, active_orders, revenue_shift
- [x] `StaffDashboardController` — active_queue, completed_today
- [x] Routes updated: all 3 dashboard routes now use dedicated controllers
- [x] Commit: part of sprint 12

### Task 12.2 — Dashboard Owner (Vue)
- [x] 4 stat cards: Revenue, Total Order, Expense Pending, Laba Bersih
- [x] Motion.js `animate(0, target, { onUpdate })` counter animation pada semua stat values
- [x] SVG inline bar chart (7 hari): bar hari ini = amber-500, hari sebelumnya = amber-200
- [x] Quick link cards: Expense Pending, Kelola Menu, Kelola Staff
- [x] Commit: part of sprint 12

### Task 12.3 — Dashboard Cashier (Vue)
- [x] Shift panel: status aktif (green pulse) + tombol Mulai/Tutup Shift langsung dari dashboard
- [x] 4 stat cards: Order Hari Ini, Selesai, Aktif, Revenue Shift — semua dengan Motion.js counter
- [x] Quick link: CTA "Buka POS" (amber) + "Order Masuk"
- [x] Commit: part of sprint 12

### Task 12.4 — Dashboard Staff (Vue)
- [x] 2 stat cards besar: Antrian Aktif (amber, extra large) + Selesai Hari Ini (green) — Motion.js counter
- [x] CTA amber besar: "Buka Antrian Dapur" dengan shortcut ke staff.queue
- [x] Commit: `feat: sprint 12 - real-data dashboards with motion.js animations and svg chart`

---

## Sprint 13 — Laporan Keuangan (Owner)

**Goal:** Owner bisa lihat laporan lengkap, grafik, komparasi periode, dan export.

### Task 13.1 — ReportService
- [x] Buat `app/Services/ReportService.php` dengan methods:
  - `revenueByPeriod(Carbon $from, Carbon $to): array` — total revenue, total expense, laba bersih
  - `revenueByDay(Carbon $from, Carbon $to): array` — data per hari untuk grafik
  - `topMenuItems(Carbon $from, Carbon $to, int $limit = 10): array` — menu terlaris
  - `revenueByCategory(Carbon $from, Carbon $to): array` — breakdown per kategori
  - `shiftSummary(Carbon $from, Carbon $to): array` — ringkasan per shift + cashier
  - `periodComparison(Carbon $currentFrom, Carbon $currentTo): array` — bulan ini vs bulan lalu
- [x] Commit: `feat: report service`

### Task 13.2 — ReportController
- [x] Buat `app/Http/Controllers/Owner/ReportController.php`
- [x] Routes:
```php
Route::get('/reports', [ReportController::class, 'index'])->name('owner.reports');
Route::get('/reports/export-pdf', [ReportController::class, 'exportPdf'])->name('owner.reports.pdf');
Route::get('/reports/export-csv', [ReportController::class, 'exportCsv'])->name('owner.reports.csv');
```
- [x] `index(Request $request)`: terima filter `period` (today/week/month/custom), `from`, `to`; return semua data report
- [x] Commit: `feat: report controller`

### Task 13.3 — Export PDF & CSV
- [x] Install: `composer require barryvdh/laravel-dompdf` (PDF)
- [x] Buat `resources/views/reports/pdf.blade.php` (DomPDF view)
- [x] `exportPdf()` dan `exportCsv()` gunakan filter periode yang sama
- [x] Commit: `feat: pdf and csv export`

### Task 13.4 — Halaman Laporan (Vue)
- [x] Buat `resources/js/Pages/Owner/Report/Index.vue`
  - Filter periode: preset (Hari Ini, Minggu Ini, Bulan Ini) + custom range
  - Summary cards: Revenue, Expense, Laba Bersih — animasi counter Motion.js
  - Line chart: revenue vs expense per hari — animasi masuk Motion.js
  - Bar chart: penjualan per kategori
  - Tabel top 10 menu terlaris
  - Tabel ringkasan per shift + cashier
  - Section komparasi: bulan ini vs bulan lalu (persentase naik/turun)
  - Tombol Export PDF dan Export CSV
- [x] Commit: `feat: financial report page`

---

## Sprint 14 — Polish & Finishing

**Goal:** Sidebar navigation lengkap, notifikasi, UX polish, dan siap production.

### Task 14.1 — AppLayout Sidebar Lengkap
- [x] Update `AppLayout.vue` — sidebar navigasi sesuai role (gunakan `$page.props.auth.user.roles`)
- [x] Active state highlight pada nav item aktif
- [x] Mobile: sidebar collapsible (hamburger menu)
- [x] Commit: `feat: complete sidebar navigation per role`

### Task 14.2 — Toast Notifications
- [x] Setup global PrimeVue Toast di `AppLayout.vue`
- [x] Flash message dari Laravel → tampil sebagai Toast (success/error/info)
- [x] Reverb events (order baru, expense submitted) → Toast di halaman yang relevan
- [x] Commit: `feat: toast notifications`

### Task 14.3 — Halaman Profile & Ganti Password
- [x] Route: `Route::get('/profile', [ProfileController::class, 'show'])` (GET /profile maps to edit)
- [x] Cashier/Staff/Owner bisa update nama, foto profil, ganti password
- [x] Commit: `feat: profile page`

### Task 14.4 — Validasi & Error Handling
- [x] Semua form request memiliki `FormRequest` class tersendiri (bukan inline validate)
- [x] 404 page custom untuk QR code tidak ditemukan
- [x] Unauthorized redirect yang jelas
- [x] Commit: `refactor: form requests and error handling`

### Task 14.5 — Mobile Responsiveness Check
- [x] Test semua halaman Customer di viewport mobile (375px)
- [x] Test AppLayout di tablet (768px)
- [x] Fix layout issues
- [x] Commit: `fix: mobile responsiveness`

### Task 14.6 — Production Config
- [x] `php artisan config:cache && php artisan route:cache && php artisan view:cache`
- [x] `npm run build`
- [x] Set APP_ENV=production, APP_DEBUG=false di `.env`
- [x] Queue worker untuk broadcast events: `php artisan queue:work`
- [x] Reverb server: `php artisan reverb:start`
- [x] Commit: `chore: production configuration`

---

## Ringkasan Sprint

| Sprint | Fokus | Estimasi |
|---|---|---|
| Sprint 0 | Project setup & foundation | 1 hari |
| Sprint 1 | Database migrations & models | 1 hari |
| Sprint 2 | Authentication | 1 hari |
| Sprint 3 | Staff management | 1 hari |
| Sprint 4 | Menu management | 1 hari |
| Sprint 5 | Table & QR management | 1 hari |
| Sprint 6 | Self order - menu & cart | 1 hari |
| Sprint 7 | Self order - checkout & submit | 1 hari |
| Sprint 8 | Tracking & realtime | 1-2 hari |
| Sprint 9 | POS Cashier | 2 hari |
| Sprint 10 | Queue Staff + Display | 1 hari |
| Sprint 11 | Expense management | 1 hari |
| Sprint 12 | Dashboard semua role | 1 hari |
| Sprint 13 | Laporan keuangan | 2 hari |
| Sprint 14 | Polish & finishing | 1 hari |
| **Total** | | **~17 hari** |
