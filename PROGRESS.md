# We Rent Car — Status Progres Implementasi

**Terakhir diperbarui:** 2026-07-10
**Basis acuan:** `PRD-WeRentCar.md`, `schema-postgres.sql`, `Database-Design-WeRentCar.md`

Dokumen ini merangkum apa yang **sudah** dan **belum** dibangun dari rewrite total aplikasi We Rent Car (dahulu Maarentcar), supaya siapa pun (termasuk sesi kerja berikutnya) bisa langsung tahu titik pijaknya tanpa membaca ulang seluruh riwayat percakapan.

---

## 1. Ringkasan Status

| Fase | Lingkup | Status |
|---|---|---|
| **Fase 1** | Skema database baru, RBAC, autentikasi, CRUD katalog admin, katalog publik | ✅ **Selesai** |
| **Fase 2** | Wizard booking (mobil & paket wisata), pembayaran transfer manual, verifikasi admin | ✅ **Selesai** |
| **Fase 3** | Ulasan, pengaturan sistem, laporan, manajemen akun staff/customer | ✅ **Selesai** |

Stack: **Laravel 12 + Inertia.js + Vue 3 + TailwindCSS v4 + PostgreSQL 15**.

---

## 2. Yang Sudah Dibuat

### 2.1 Database & Skema (sesuai `schema-postgres.sql`)

Skema lama (UUID, Midtrans) **dihapus total** dan diganti skema baru dengan `bigserial` id. Migrasi ada di `database/migrations/2024_01_01_*`:

- `roles`, `permissions`, `role_permissions` — fondasi RBAC
- `users` (+ `role_id`, `phone`, `avatar_path`, `status`, soft delete), `addresses`
- `vehicle_categories`, `vehicles`, `vehicle_images`
- `destinations`, `tour_packages`, `package_destinations`
- `bookings` (+ CHECK constraint tipe booking & tanggal), `booking_destinations`, `booking_status_logs`
- `payments`, `payment_verifications`
- `reviews` (tabel sudah ada, **fitur/CRUD belum dibangun** — lihat §3)
- `settings` (tabel + data seed ada, **UI admin belum dibangun**)

Model Eloquent lengkap untuk semua tabel di atas ada di `app/Models/` (17 file), termasuk relasi, cast, dan konstanta status.

### 2.2 RBAC (Role-Based Access Control)

- Tabel `roles`/`permissions`/`role_permissions`, diseed lewat `RolePermissionSeeder`.
- 3 role: `admin` (semua permission), `staff` (subset: lihat semua katalog, update mobil, lihat & update status booking, verifikasi pembayaran), `customer` (tanpa permission — akses diatur lewat ownership, bukan permission).
- `User::hasPermission($slug)`, `isAdmin()`, `isStaff()`, `isCustomer()`, `isAdminOrStaff()`.
- Middleware `admin` (gerbang masuk `/admin/*` untuk admin+staff) dan `permission:module.action` (gerbang aksi granular, pakai `HasMiddleware` interface Laravel 12 di controller).
- Permission slug diperluas dari contoh di schema-postgres.sql agar granular per modul: `vehicle_categories.*`, `vehicles.*`, `destinations.*`, `tour_packages.*`, `bookings.view`/`update_status`, `payments.verify`, `reviews.moderate` (belum dipakai), `users.manage`/`staff.manage`/`reports.view_full`/`settings.manage` (permission-nya sudah diseed, **tapi belum ada controller/halaman yang memakainya** — lihat §3).

### 2.3 Autentikasi

- Satu halaman login, redirect otomatis: admin/staff → `/admin`, customer → `/dashboard`.
- Registrasi mandiri **khusus customer** (staff/admin dibuat manual via seeder, belum ada UI admin untuk membuat staff — lihat §3).
- Lupa password & reset password berfungsi penuh (pakai `password_reset_tokens`, Laravel `Password` broker, `MAIL_MAILER=log` jadi email masuk log lokal, bukan terkirim sungguhan).
- Ganti profil (nama/email/telepon) & ganti kata sandi di `/profil`.

### 2.4 Katalog Publik

| Halaman | Route | Status |
|---|---|---|
| Beranda | `/` | ✅ mobil unggulan, paket unggulan, destinasi populer |
| Katalog mobil | `/mobil` | ✅ filter kategori/transmisi/kapasitas/harga, pagination |
| Detail mobil | `/mobil/{slug}` | ✅ galeri foto, spesifikasi, harga, tombol booking |
| Katalog paket wisata | `/paket-wisata` | ✅ filter pencarian/durasi |
| Detail paket wisata | `/paket-wisata/{slug}` | ✅ daftar destinasi, harga, tombol booking |
| Destinasi | `/destinasi`, `/destinasi/{slug}` | ✅ filter kategori/pencarian |
| Tentang Kami / Kontak / FAQ | `/tentang-kami`, `/kontak`, `/faq` | ✅ konten statis |

Field internal (`plate_number`) disembunyikan dari response publik sesuai PRD ("tidak tampil publik").

### 2.5 CRUD Admin — Katalog

| Modul | Route | Admin | Staff |
|---|---|---|---|
| Kategori Mobil | `/admin/kategori-mobil` | CRUD | Lihat saja |
| Mobil (+galeri foto) | `/admin/mobil` | CRUD | Lihat + **Update** (semua field, lihat catatan simplifikasi §4) |
| Destinasi | `/admin/destinasi` | CRUD | Lihat saja |
| Paket Wisata (+pilih destinasi) | `/admin/paket-wisata` | CRUD | Lihat saja |
| Dashboard admin | `/admin` | Statistik ringkas (jumlah mobil, destinasi, paket, booking menunggu verifikasi, booking berlangsung) | Sama |

Upload gambar mobil (multi-foto + hapus per foto), destinasi & paket (single foto) — disimpan di disk `public` (storage:link sudah dibuat), auto-generate slug unik dari nama.

### 2.6 Booking & Pembayaran (Fase 2)

**Wizard booking mobil** (`/booking/mobil/{slug}/baru`, 4 langkah client-side):
1. Jadwal (tanggal & jam mulai/selesai → durasi & subtotal otomatis)
2. Jenis layanan (lepas kunci vs dengan supir → alamat antar/jemput bila perlu, tambah destinasi add-on bila dengan supir)
3. Konfirmasi data diri (nama/telepon dari profil, catatan opsional)
4. Ringkasan & bayar (rincian biaya lengkap)

**Wizard booking paket wisata** (`/booking/paket-wisata/{slug}/baru`, 4 langkah):
1. Tanggal keberangkatan & jumlah peserta
2. Alamat penjemputan (wajib)
3. Catatan tambahan
4. Ringkasan & bayar (harga tetap sesuai paket)

**Logika bisnis** (`app/Services/BookingService.php`):
- Kalkulasi harga snapshot (base_price, driver_fee, delivery_fee, addon_total, total_price) — tidak berubah walau harga master berubah kemudian.
- Cek anti double-booking (hanya untuk booking mobil; booking paket wisata tidak mengunci mobil representatif, sesuai catatan Database Design bahwa admin bisa reassign manual).
- Snapshot alamat penjemputan/pengantaran ke `pickup_address_snapshot` (JSON).
- Generate `booking_code` unik format `WRC-YYMMDD-XXXXX`.

**Pembayaran manual**:
- Halaman detail booking (`/booking/{id}`) menampilkan info rekening (dari tabel `settings`) + form upload bukti transfer.
- Bukti transfer disimpan di **disk privat** (`storage/app/private`, bukan `public`), hanya bisa diakses pemilik booking atau admin/staff lewat route bergerbang (`booking.payment.proof`).
- Riwayat booking (`/booking`) & pembatalan booking (`/booking/{id}/batalkan`, hanya sebelum dikonfirmasi).

**Verifikasi admin** (`/admin/pembayaran`):
- Antrian pembayaran status `menunggu`.
- Verifikasi → booking jadi `dikonfirmasi`. Tolak (wajib isi alasan) → booking balik ke `menunggu_pembayaran`, pelanggan diminta upload ulang.
- Setiap aksi tercatat di `payment_verifications` (siapa, kapan, alasan).

**Manajemen booking admin** (`/admin/booking`, `/admin/booking/{id}`):
- Daftar semua booking (filter status/jenis/pencarian), detail lengkap, ubah status manual sesuai state machine yang diizinkan (`dikonfirmasi→berlangsung/dibatalkan`, `berlangsung→selesai`, `menunggu_*→dibatalkan`), catatan internal.

**Audit trail & notifikasi**:
- Setiap perubahan status booking tercatat di `booking_status_logs` (dari, ke, siapa, catatan) lewat `Booking::transitionTo()`.
- Notifikasi in-app (database channel) otomatis ke pelanggan di setiap perubahan status.

### 2.7 Ulasan, Pengaturan, Manajemen Akun, Laporan (Fase 3)

- **Ulasan**: pelanggan menulis ulasan (`/booking/{id}/ulasan/tulis`) untuk booking berstatus `selesai` yang belum diulas (`Booking::canBeReviewedBy()`), satu ulasan per booking (unique constraint DB). Moderasi admin di `/admin/ulasan` (permission `reviews.moderate`, staff tidak diberi akses) — sembunyikan/tampilkan ulasan. Ulasan yang tidak disembunyikan tampil di halaman detail mobil & paket wisata publik beserta rata-rata rating (komponen `RatingStars.vue`).
- **Pengaturan**: `/admin/pengaturan` (permission `settings.manage`) — form untuk mengubah 7 key di tabel `settings` (rekening bank, WhatsApp, teks hero homepage, kebijakan pembatalan), dikelompokkan per section di `AdminSettingController`.
- **Manajemen Akun**:
  - Customer — `/admin/pengguna` (permission `users.manage`): lihat daftar, aktifkan/nonaktifkan, hapus (soft delete). Tidak ada create/edit identitas customer dari admin (customer tetap daftar sendiri).
  - Staff — `/admin/staff` (permission `staff.manage`): CRUD penuh akun staff (create/edit/nonaktifkan/hapus) **plus** panel assign permission untuk role staff (checklist semua permission → sync ke role staff), menutup gap "assign permission per role" yang sebelumnya tidak ada UI-nya.
- **Laporan**: `/admin/laporan` (permission `reports.view_full`) — filter rentang tanggal, stat-card (total pendapatan, total booking, mobil aktif, mobil sedang disewa), bar chart CSS sederhana untuk pendapatan harian (tanpa dependency chart library baru), booking per status, top 5 mobil & paket wisata, ekspor CSV.
- Kelima permission baru ini **tetap admin-only** (staff tidak diberi akses secara default, konsisten dengan `RolePermissionSeeder`) — nav sidebar admin memfilter kelima menu ini berdasarkan `role === 'admin'`.

### 2.8 Testing Otomatis

29 test (179 assertion) di `tests/Feature/`, semua **lolos**:
- `AuthAndRbacTest` — login/redirect per role, staff diblokir create tapi bisa lihat, admin bisa create.
- `BookingFlowTest` — kalkulasi harga (lepas kunci & dengan supir), anti double-booking, booking paket harga tetap, alur verifikasi & penolakan pembayaran end-to-end, customer diblokir dari route admin.
- `InertiaPageRenderTest` — memastikan nama komponen Inertia yang dikirim backend cocok dengan file Vue yang benar-benar ada (mencegah halaman blank akibat typo path).
- `ReviewFlowTest` — aturan siapa-boleh-mengulas-apa, satu ulasan per booking, staff diblokir dari moderasi, toggle visibility, ulasan tersembunyi tidak tampil publik.
- `AccountAndSettingsTest` — staff diblokir dari 4 area admin-only baru, update pengaturan, aktifkan/hapus akun customer, buat & login akun staff baru, assign permission ke role staff.
- `ReportTest` — staff diblokir, angka pendapatan laporan cocok dengan payment terverifikasi.

Database testing terpisah (`maarentcar_test` di Postgres yang sama) — `phpunit.xml` diperbaiki dari SQLite `:memory:` (tidak kompatibel dengan `jsonb`/CHECK constraint kita) ke Postgres.

### 2.8 Perbaikan Bug Ditemukan Selama Proses

- Token warna Tailwind v4 (`--primary`, `--accent`, dst.) sebelumnya tidak dipetakan ke namespace `--color-*`, sehingga seluruh komponen `components/ui/*` (Button, Input, Card...) render tanpa styling. **Sudah diperbaiki** di `resources/css/app.css`.
- `resources/js/components/ui/badge/index.ts` mengekspor tipe yang tidak ada di `Badge.vue` (dead code lama, tidak pernah dipakai) — dibersihkan.
- `lodash`/`axios` dipakai di kode lama tapi tidak pernah jadi dependency resmi — diganti `@vueuse/core` (`watchDebounced`) yang sudah terpasang.

---

## 3. Yang BELUM Dibuat (sisa dari PRD)

Fase 3 (Ulasan, Pengaturan, Laporan, Manajemen Akun — lihat §2.7) sudah selesai. Sisa item di bawah ini adalah nice-to-have/pelengkap yang belum digarap.

### 3.1 Buku Alamat (Address Book)
- Tabel `addresses` ada di database tapi **tidak dipakai** di mana pun. Wizard booking mengumpulkan alamat langsung inline dan hanya menyimpannya sebagai snapshot JSON di booking (`pickup_address_snapshot`), bukan sebagai entri alamat tersimpan yang bisa dipakai ulang di booking berikutnya (nice-to-have di PRD §2.5, bukan wajib).

### 3.2 Notifikasi Email
- Notifikasi status booking hanya lewat channel **database** (in-app, muncul di ikon notifikasi navbar). **Belum ada** pengiriman email (PRD §6 menyebutnya "opsional").

### 3.3 Lain-lain dari PRD yang Belum Tersentuh
- Kalender ketersediaan **visual** (PRD §7.3) — saat ini hanya date/time picker biasa, tanggal yang penuh tidak ditandai visual sebelum submit (validasi tetap terjadi di server saat submit).
- Rate limiting khusus untuk login (Laravel punya throttle default tapi belum dikonfigurasi eksplisit sesuai NFR §6).
- Field kontak WhatsApp di halaman booking/pembayaran masih tautan statis di halaman `/kontak`, belum floating/contextual di setiap langkah wizard seperti disebut PRD §7.3.

---

## 4. Simplifikasi & Keputusan yang Perlu Diketahui

1. **Staff & update mobil**: PRD §5 bilang staff hanya boleh update *status ketersediaan* mobil, tapi implementasi saat ini staff bisa edit semua field mobil (permission `vehicles.update` belum dipecah granular per field).
2. **Status `ditolak` pada booking**: enum ini ada di kolom `bookings.status` tapi tidak dipakai sebagai status singgah — saat pembayaran ditolak, booking langsung balik ke `menunggu_pembayaran` (sesuai alur PRD §4.4), bukan berhenti di status `ditolak`.
3. **Booking paket wisata & ketersediaan mobil**: tidak ada pengecekan double-booking untuk mobil representatif paket wisata (by design, sesuai catatan Database-Design bahwa admin reassign manual saat verifikasi).
4. **Wizard booking**: state wizard hanya di sisi client (Vue), tidak disimpan di server — jika pelanggan reload halaman di tengah wizard, progres hilang (tidak masalah karena belum submit apa pun ke server).
5. **Permission Fase 3 admin-only**: `reviews.moderate`, `users.manage`, `staff.manage`, `reports.view_full`, `settings.manage` sengaja tidak diberikan ke role staff sama sekali (keputusan default, `RolePermissionSeeder` tidak diubah) — admin bisa mengubah ini kapan saja lewat panel assign-permission di `/admin/staff`.
6. **Manajemen akun customer**: admin hanya bisa aktifkan/nonaktifkan/hapus (soft delete) akun customer dari `/admin/pengguna`, tidak ada create/edit identitas — customer tetap daftar & edit profil sendiri.
7. **Laporan tanpa chart library**: grafik pendapatan harian di `/admin/laporan` dibuat dengan bar CSS sederhana (width % dari nilai maksimum), bukan library seperti Chart.js, supaya tidak menambah dependency baru.

---

## 5. Akun Demo

Semua password: `password`

| Role | Email |
|---|---|
| Admin | `admin@werentcar.com` |
| Staff | `staff@werentcar.com` |
| Customer | `customer@example.com` |

## 6. Menjalankan Proyek

```bash
composer install
npm install
cp .env.example .env   # sesuaikan DB_* untuk Postgres lokal
php artisan key:generate
php artisan migrate --seed
php artisan storage:link
composer run dev       # jalankan server + queue + vite bersamaan
```

Jalankan test: `php artisan test`
