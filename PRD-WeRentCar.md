# Product Requirements Document (PRD)
# We Rent Car — Platform Rental Mobil & Paket Wisata Batam

**Versi:** 1.0
**Stack:** Laravel 12 + Vue 3 + Inertia.js + PostgreSQL + TailwindCSS
**Basis:** Revisi dari draft riset "Perancangan Aplikasi Web Rental Mobil Terintegrasi dengan Paket Wisata Lokal pada We Rent Car Kota Batam"

---

## 1. Ringkasan Produk

We Rent Car adalah platform web yang menggabungkan dua layanan dalam satu sistem:

1. **Rental mobil** — lepas kunci (self-drive) atau dengan supir.
2. **Paket wisata lokal Batam** — paket siap pakai yang sudah mencakup mobil, supir, dan destinasi wisata.

Siapa pun (tamu/guest) dapat menjelajahi katalog di homepage, namun untuk melakukan pemesanan wajib memiliki akun (login sebagai pelanggan). Pembayaran dilakukan manual melalui transfer bank dengan upload bukti transfer, yang kemudian diverifikasi oleh admin/staff.

### 1.1 Tujuan Produk
- Mendigitalkan proses pemesanan yang sebelumnya manual (telepon/chat/catatan buku).
- Menyatukan booking mobil dan paket wisata dalam satu alur pemesanan yang mudah dipahami awam.
- Memberi admin/staff kontrol penuh atas armada, jadwal, verifikasi pembayaran, dan pelaporan.
- Tampil elegan dan meyakinkan sebagai brand rental mobil premium, bukan sekadar form pemesanan.

### 1.2 Target Pengguna
| Persona | Deskripsi | Kebutuhan Utama |
|---|---|---|
| Wisatawan domestik/asing | Datang ke Batam, butuh transportasi + panduan wisata | Alur pesan simpel, info transparan, tanpa perlu paham istilah teknis |
| Pelanggan lokal/korporat | Sewa mobil rutin untuk keperluan bisnis/harian | Riwayat sewa, kecepatan booking ulang |
| Admin (pemilik/manajer) | Mengelola seluruh operasional | Dashboard, laporan, kontrol penuh atas semua modul |
| Staff (operasional lapangan) | Verifikasi pembayaran, siapkan armada, handle serah-terima | Antarmuka cepat & terbatas sesuai tugas, tanpa akses sensitif (laporan keuangan, manajemen user) |

---

## 2. Peran Pengguna & RBAC

Satu halaman login untuk semua peran. Backend mendeteksi role setelah autentikasi dan mengarahkan redirect:

- **Customer** → redirect ke `/dashboard` (area pelanggan: booking, riwayat, profil).
- **Admin** → redirect ke `/admin/dashboard`.
- **Staff** → redirect ke `/admin/dashboard` juga (tampilan sama, namun menu & aksi dibatasi permission).

RBAC diimplementasikan dua lapis:
1. **Role** (`admin`, `staff`, `customer`) — menentukan area akses & redirect.
2. **Permission granular** per modul (lihat matriks §5) — menentukan aksi CRUD apa saja yang boleh dilakukan tiap role di dalam area admin. Ini memudahkan pemilik bisnis menambah role baru di masa depan (mis. "Supervisor") tanpa ubah kode, cukup atur permission.

> Rekomendasi implementasi: gunakan struktur permission kustom seperti dirancang pada §Database (tabel `roles`, `permissions`, `role_permissions`), atau bila ingin mempercepat development bisa memakai package `spatie/laravel-permission` dengan skema tabel yang serupa.

### Ringkasan hak akses
- **Admin**: akses penuh ke semua modul, termasuk kelola staff, laporan keuangan, dan pengaturan sistem.
- **Staff**: kelola booking harian, verifikasi pembayaran, update status armada/serah-terima, tidak bisa kelola user/staff lain, tidak bisa akses laporan keuangan detail, tidak bisa ubah pengaturan sistem.
- **Customer**: kelola profil sendiri, buat & lihat booking sendiri, upload bukti transfer, beri ulasan setelah selesai.

---

## 3. Peta Situs (Sitemap)

### 3.1 Area Publik (tanpa login)
- `/` — Homepage / landing page
- `/mobil` — Katalog mobil (filter: tipe, kapasitas, transmisi, harga, dengan/tanpa supir)
- `/mobil/{slug}` — Detail mobil
- `/paket-wisata` — Katalog paket wisata
- `/paket-wisata/{slug}` — Detail paket wisata
- `/destinasi` — List destinasi wisata Batam (mis. Pantai Sekilak, Piayu Laut Seafood, dll.)
- `/destinasi/{slug}` — Detail destinasi
- `/tentang-kami`, `/kontak`, `/faq`
- `/login`, `/register`, `/lupa-password`

### 3.2 Area Pelanggan (login role: customer)
- `/dashboard` — Ringkasan booking aktif & rekomendasi
- `/booking/{mobil-atau-paket}/baru` — Wizard pembuatan booking
- `/booking` — Riwayat & status booking
- `/booking/{id}` — Detail booking + upload/lihat bukti transfer
- `/profil` — Data diri, alamat tersimpan, ganti password
- `/ulasan/{booking_id}/tulis` — Form ulasan setelah selesai

### 3.3 Area Admin/Staff (login role: admin/staff)
- `/admin/dashboard` — Statistik ringkas (booking hari ini, menunggu verifikasi, pendapatan, armada tersedia)
- `/admin/mobil` — CRUD data mobil & galeri foto
- `/admin/kategori-mobil` — CRUD kategori mobil
- `/admin/destinasi` — CRUD destinasi wisata
- `/admin/paket-wisata` — CRUD paket wisata
- `/admin/booking` — Kelola semua booking (filter status, tanggal, jenis layanan)
- `/admin/booking/{id}` — Detail booking, ubah status, catatan internal
- `/admin/pembayaran` — Antrian verifikasi bukti transfer
- `/admin/ulasan` — Moderasi ulasan (sembunyikan/tampilkan)
- `/admin/pengguna` *(admin only)* — CRUD akun customer
- `/admin/staff` *(admin only)* — CRUD akun staff & assign permission
- `/admin/laporan` *(admin only)* — Laporan pendapatan, okupansi armada, ekspor
- `/admin/pengaturan` *(admin only)* — Info rekening bank, konten homepage, kebijakan sewa

---

## 4. Alur Utama (User Flows)

### 4.1 Alur Autentikasi
1. Pengguna klik "Masuk" dari header (tersedia di semua halaman publik).
2. Halaman login tunggal — input email/telepon + password.
3. Sistem cek role setelah autentikasi berhasil:
   - `customer` → `/dashboard`
   - `admin` / `staff` → `/admin/dashboard`
4. Registrasi hanya tersedia untuk role `customer` (staff dibuat oleh admin dari panel).

### 4.2 Alur Pemesanan Mobil (Rental Mobil)
1. **Jelajah katalog** — pelanggan pilih mobil dari `/mobil`, bisa filter kapasitas/tipe/harga.
2. **Halaman detail mobil** — foto, spesifikasi, harga/hari, kalender ketersediaan, tombol "Sewa Sekarang".
3. **Wizard Detail Booking** (multi-step, satu langkah per layar agar tidak membingungkan):
   - **Step 1 — Jadwal**: tanggal & jam mulai, tanggal & jam selesai → sistem hitung otomatis durasi & subtotal, serta cek ketersediaan mobil di rentang tanggal tersebut.
   - **Step 2 — Jenis layanan**: pilih *Lepas Kunci* atau *Dengan Supir*.
     - Jika **Lepas Kunci** → muncul pilihan pengambilan:
       - *Ambil di lokasi rental* (tanpa biaya tambahan), atau
       - *Diantar ke alamat* → wajib isi form alamat lengkap (jalan, kelurahan/kecamatan, patokan, titik peta opsional) → biaya antar dihitung otomatis.
     - Jika **Dengan Supir** → wajib isi **alamat penjemputan** (form sama seperti di atas) → muncul opsi **tambah destinasi wisata** (multi-select dari daftar destinasi aktif, masing-masing menampilkan biaya tambahan) → pelanggan bisa memilih 0 atau lebih destinasi sebagai add-on.
   - **Step 3 — Konfirmasi data diri**: nama, nomor telepon (prefill dari profil, bisa diedit), catatan tambahan (opsional).
   - **Step 4 — Ringkasan & Bayar**: rincian biaya (harga sewa × durasi + biaya supir jika ada + biaya antar/add-on wisata jika ada) → total → tombol "Buat Pesanan".
4. Sistem membuat booking berstatus **`menunggu_pembayaran`**, generate kode booking unik, arahkan ke halaman pembayaran.
5. **Halaman pembayaran**: tampilkan info rekening tujuan transfer + jumlah total + form upload bukti transfer (gambar/PDF) → booking berubah status **`menunggu_verifikasi`**.
6. **Verifikasi admin/staff** (lihat §4.4).
7. Setelah **`dikonfirmasi`**, pelanggan menerima notifikasi (in-app, opsional email) berisi detail final.
8. Pada tanggal mulai sewa, staff mengubah status ke **`berlangsung`** saat serah-terima kendaraan.
9. Pada tanggal selesai/pengembalian, staff mengubah status ke **`selesai`**.
10. Pelanggan dapat memberi **ulasan & rating** setelah status `selesai`.

### 4.3 Alur Pemesanan Paket Wisata
1. Pelanggan pilih paket dari `/paket-wisata` — setiap paket menampilkan kapasitas kursi mobil, ada/tidaknya bagasi besar, daftar destinasi yang termasuk, durasi (harian), dan harga paket.
2. Halaman detail paket → tombol "Pesan Paket Ini".
3. **Wizard Detail Booking Paket**:
   - Step 1 — Tanggal keberangkatan & jumlah hari (jika paket multi-hari), jumlah peserta.
   - Step 2 — **Alamat penjemputan** (wajib, karena paket selalu termasuk supir).
   - Step 3 — Catatan tambahan (opsional, mis. request tujuan tambahan di luar paket — dicatat sebagai catatan, dikonfirmasi manual oleh admin).
   - Step 4 — Ringkasan & Bayar (harga tetap sesuai paket, dikali jumlah hari bila relevan).
4. Alur pembayaran, verifikasi, dan status booking sama persis dengan §4.2 poin 4–10.

### 4.4 Alur Verifikasi Pembayaran (Admin/Staff)
1. Booking berstatus `menunggu_verifikasi` masuk ke antrian `/admin/pembayaran`.
2. Staff/admin membuka detail, melihat bukti transfer, mencocokkan nominal.
3. Aksi:
   - **Verifikasi** → status booking menjadi `dikonfirmasi`, mobil terkait ditandai "dipesan" pada rentang tanggal tersebut.
   - **Tolak** → status kembali ke `menunggu_pembayaran`, wajib isi alasan penolakan (mis. nominal tidak sesuai, bukti tidak jelas), pelanggan diberi notifikasi untuk upload ulang.
4. Semua aksi tercatat (siapa yang verifikasi, kapan) untuk audit.

### 4.5 Diagram Status Booking

```
menunggu_pembayaran → menunggu_verifikasi → dikonfirmasi → berlangsung → selesai
        ↑                     |
        └──── ditolak ────────┘

(dibatalkan) bisa terjadi dari menunggu_pembayaran / menunggu_verifikasi / dikonfirmasi,
baik oleh pelanggan (sebelum dikonfirmasi) maupun admin/staff (kapan pun sebelum berlangsung).
```

### 4.6 Alur Ulasan
1. Setelah booking berstatus `selesai`, sistem menampilkan CTA "Beri Ulasan" di riwayat booking pelanggan.
2. Pelanggan isi rating (1–5) + komentar, terkait ke mobil dan/atau paket yang disewa.
3. Ulasan tampil di halaman detail mobil/paket setelah lolos moderasi ringan (admin bisa sembunyikan ulasan yang melanggar, tidak perlu approve manual satu per satu — default tampil, admin hanya intervensi bila perlu).

---

## 5. Fitur & Matriks CRUD

Legenda: **C**reate, **R**ead, **U**pdate, **D**elete, **-** = tidak ada akses.

| Modul | Admin | Staff | Customer |
|---|:---:|:---:|:---:|
| Kategori Mobil | CRUD | R | R (publik) |
| Data Mobil & Galeri Foto | CRUD | R, U (status ketersediaan) | R (publik) |
| Destinasi Wisata | CRUD | R | R (publik) |
| Paket Wisata | CRUD | R | R (publik) |
| Booking (miliknya sendiri) | CRUD | R, U (status) | C, R, U (batasan: hanya sebelum dikonfirmasi), D (batal, sebelum dikonfirmasi) |
| Booking (semua milik pelanggan) | CRUD | R, U (status) | - |
| Verifikasi Pembayaran | CRUD | C, R, U | C (upload bukti), R (miliknya) |
| Ulasan | R, U (moderasi/sembunyikan), D | R | C, R (miliknya), U (miliknya, dalam batas waktu), D (miliknya) |
| Akun Customer | CRUD | R | R, U (profil sendiri) |
| Akun Staff & Permission | CRUD | - | - |
| Pengaturan Sistem (rekening, konten homepage) | CRUD | - | - |
| Laporan (pendapatan, okupansi) | R (penuh) | R (terbatas: booking harian) | - |

### Detail per modul

**Kategori Mobil** (mis. City Car, MPV, SUV, Minibus)
- Field: nama, deskripsi, ikon/gambar.
- Digunakan untuk filter katalog & pengelompokan kapasitas di paket wisata.

**Data Mobil**
- Field: nama, kategori, merek, tahun, plat nomor (internal, tidak tampil publik), transmisi, bahan bakar, kapasitas kursi, harga sewa/hari, biaya supir/hari (opsional), biaya antar-jemput dasar, status (`tersedia`, `disewa`, `perawatan`, `nonaktif`), deskripsi, galeri foto (banyak gambar), slug (SEO).
- Ketersediaan riil dihitung dari overlap tanggal booking aktif, bukan hanya field status manual.

**Destinasi Wisata**
- Field: nama, deskripsi, lokasi/alamat, foto, kategori (pantai, kuliner, sejarah, dll.), harga add-on (biaya tambahan bila dipilih sebagai add-on saat booking dengan supir).

**Paket Wisata**
- Field: nama, deskripsi, durasi (hari), kapasitas kursi mobil yang termasuk, mobil yang ditautkan (opsional — bisa spesifik atau "sesuai kategori"), harga paket, foto, daftar destinasi yang termasuk (relasi many-to-many), status aktif/nonaktif.

**Booking**
- Mendukung dua jenis pemesanan: `mobil` dan `paket_wisata` (lihat rancangan database §Booking untuk field lengkap).
- Pelanggan hanya bisa mengubah/membatalkan booking miliknya sendiri selama masih di status `menunggu_pembayaran` atau `menunggu_verifikasi`. Setelah `dikonfirmasi`, perubahan/pembatalan hanya lewat admin/staff (mis. karena butuh penyesuaian jadwal armada).

**Pembayaran**
- Satu booking bisa punya lebih dari satu record pembayaran jika bukti pertama ditolak dan pelanggan upload ulang (histori tersimpan, tidak ditimpa).

**Ulasan**
- Hanya bisa dibuat oleh pelanggan yang benar memiliki booking berstatus `selesai` untuk mobil/paket tersebut (validasi anti-fake-review).

---

## 6. Kebutuhan Non-Fungsional

- **Responsif penuh** — mobile-first, karena mayoritas wisatawan mengakses dari HP.
- **Keamanan**: hashing password, validasi upload file (tipe & ukuran, hanya jpg/png/pdf, maks 5MB), rate limiting login, CSRF protection bawaan Laravel, penyimpanan file bukti transfer di storage privat (bukan public path langsung).
- **Ketersediaan data real-time**: kalender ketersediaan mobil harus mencerminkan booking yang sedang berjalan/dikonfirmasi, mencegah double booking.
- **Audit trail**: siapa mengubah status booking/pembayaran dan kapan (tabel log ringan).
- **Aksesibilitas & UX awam**: bahasa Indonesia sederhana, hindari istilah teknis, wizard step-by-step dengan indikator progres, konfirmasi ulang sebelum aksi penting (submit booking, batalkan), pesan error yang jelas dan actionable.
- **Notifikasi**: minimal in-app notification untuk perubahan status booking; email opsional (Laravel Notification/Mail) untuk konfirmasi booking & hasil verifikasi.
- **Performa**: lazy-load galeri gambar, pagination di semua listing admin, caching untuk data katalog publik yang jarang berubah (destinasi, kategori).

---

## 7. Arahan Desain & UX

### 7.1 Prinsip Desain
- **Elegan & meyakinkan** — brand rental mobil premium, bukan template generik. Gunakan foto mobil & destinasi berkualitas tinggi, banyak whitespace, tipografi yang tegas.
- **Palet warna**: dasar netral gelap (charcoal/navy tua) dipadu aksen hangat (amber/gold) untuk CTA — kombinasi ini menimbulkan kesan "premium travel & automotive" sekaligus mudah dibaca. Warna sukses/gagal tetap pakai konvensi hijau/merah standar agar tidak membingungkan pengguna awam.
- **Tipografi**: satu font sans-serif modern untuk judul (medium/semibold) dan satu untuk body (regular), hindari lebih dari dua ukuran berat huruf agar tetap bersih.
- **Fotografi sebagai elemen utama** — hero section homepage pakai foto mobil unggulan/destinasi ikonik Batam sebagai daya tarik utama, bukan ilustrasi generik.

### 7.2 Homepage (Landing Page) — struktur yang disarankan
1. **Hero** — headline singkat + foto mobil/destinasi + 2 CTA utama ("Sewa Mobil" & "Lihat Paket Wisata") + search bar cepat (tanggal + jenis layanan).
2. **Kenapa pilih kami** — 3–4 poin kepercayaan (armada terawat, harga transparan, proses mudah, dukungan lokal Batam).
3. **Mobil unggulan** — carousel/grid 4–6 mobil populer dengan harga mulai dari.
4. **Paket wisata unggulan** — grid paket populer dengan highlight destinasi.
5. **Destinasi populer di Batam** — galeri visual destinasi (Pantai Sekilak, Piayu Laut Seafood, dll.) yang mengarah ke paket terkait.
6. **Cara kerja / how it works** — 4 langkah sederhana bergambar: pilih → booking → transfer → jalan-jalan.
7. **Testimoni/ulasan pelanggan**.
8. **CTA penutup + footer** (kontak, sosial media, info perusahaan).

### 7.3 Prinsip UX untuk pengguna awam
- **Wizard, bukan form panjang** — setiap step hanya menampilkan pertanyaan yang relevan (progressive disclosure): pertanyaan "dengan supir atau tidak" baru memunculkan field alamat yang sesuai, tidak menampilkan semua field sekaligus.
- **Indikator progres** yang jelas (mis. "Langkah 2 dari 4") supaya pengguna tahu berapa lama lagi.
- **Kalkulasi harga real-time** ditampilkan di sisi/bawah form setiap ada perubahan pilihan, agar tidak ada kejutan di akhir.
- **Bahasa manusiawi**, bukan istilah sistem (gunakan "Menunggu konfirmasi kami" bukan "status: pending_verification").
- **Bantuan kontekstual**: tombol/link "Hubungi kami via WhatsApp" selalu terlihat di halaman booking & pembayaran untuk pengguna yang bingung.
- **Konfirmasi visual** setelah tiap aksi penting (toast/modal "Bukti transfer berhasil diunggah, kami akan verifikasi dalam 1x24 jam").
- **Kalender ketersediaan visual** (bukan hanya date picker biasa) — tanggal yang sudah penuh ditandai berbeda agar pengguna tidak perlu coba-coba.

---

## 8. Ringkasan Teknis

| Layer | Teknologi |
|---|---|
| Backend framework | Laravel 12 (PHP 8.2+) |
| Frontend | Vue 3 (Composition API) + Inertia.js |
| Styling | TailwindCSS |
| Build tool | Vite |
| Database | PostgreSQL 15+ |
| Autentikasi | Laravel Breeze/Fortify (adaptasi Inertia-Vue) + role & permission kustom |
| File storage | Laravel Filesystem (local/S3-compatible) untuk foto mobil & bukti transfer |
| Queue/Notification | Laravel Queue + Notification (email, opsional WhatsApp gateway di masa depan) |

---

## 9. Ruang Lingkup Lanjutan (Out of Scope v1, opsional untuk iterasi berikutnya)
- Integrasi payment gateway otomatis (saat ini transfer manual + verifikasi manual sesuai kebutuhan riset awal).
- Aplikasi mobile native.
- Live chat real-time (cukup link WhatsApp di v1).
- Multi-cabang/multi-kota (sistem saat ini fokus Kota Batam).
