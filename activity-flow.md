# Flowchart & Activity Diagram (Narasi Teks) - We Rent Car

Dokumen ini berisi alur aktivitas per modul/fitur, ditulis dalam bentuk narasi
langkah-per-langkah, dengan aktor yang terlibat disebutkan di tiap langkah
(Pelanggan / Sistem / Admin / Staff). Tidak semua modul melibatkan ketiga
aktor - disesuaikan kebutuhan masing-masing fitur.

---

## 1. Modul Autentikasi

### 1.1 Registrasi Akun Pelanggan

1. Pelanggan membuka halaman registrasi (`/register`)
2. Pelanggan mengisi form (nama, email, no. telepon, password, konfirmasi password)
3. Pelanggan menekan tombol "Daftar"
4. Sistem memvalidasi input (email unik, format valid, password memenuhi syarat minimum)
5. **Decision**: Apakah validasi lolos?
   - Jika tidak -> Sistem menampilkan pesan error di form -> kembali ke langkah 2
   - Jika ya -> lanjut ke langkah 6
6. Sistem menyimpan data user baru dengan role `customer` (role staff/admin tidak bisa dibuat lewat form ini)
7. Sistem membuat session login otomatis
8. Sistem mengarahkan pelanggan ke halaman dashboard customer (`/dashboard`)

### 1.2 Login

1. User (pelanggan/staff/admin) membuka halaman login (`/login`)
2. User mengisi email dan password, opsional centang "Ingat saya"
3. User menekan tombol "Masuk"
4. Sistem memvalidasi kredensial
5. **Decision**: Apakah kredensial valid?
   - Jika tidak -> Sistem menampilkan pesan "email atau kata sandi salah" -> kembali ke langkah 2
   - Jika ya -> lanjut ke langkah 6
6. Sistem mengecek `role` user yang login
7. **Decision**: Role apa?
   - `admin` atau `staff` -> Sistem arahkan ke `/admin` (dashboard admin)
   - `customer` -> Sistem arahkan ke `/dashboard` (dashboard customer)

### 1.3 Lupa Kata Sandi & Reset

1. Pelanggan membuka halaman "Lupa Kata Sandi" (`/lupa-password`)
2. Pelanggan mengisi email terdaftar, menekan "Kirim Link Reset"
3. Sistem mencari akun berdasarkan email
4. **Decision**: Apakah email terdaftar?
   - Tidak -> Sistem menampilkan pesan error "email tidak ditemukan"
   - Ya -> Sistem membuat token reset dan mengirim link reset (tercatat di log mail lokal, belum terkirim ke email sungguhan)
5. Pelanggan membuka link reset (`/reset-password/{token}`)
6. Pelanggan mengisi kata sandi baru beserta konfirmasi, menekan "Ubah Kata Sandi"
7. Sistem memvalidasi token dan kekuatan kata sandi baru
8. **Decision**: Apakah token valid dan input lolos validasi?
   - Tidak -> Sistem menampilkan pesan error, token dianggap kedaluwarsa
   - Ya -> Sistem memperbarui kata sandi, mengarahkan pelanggan ke halaman login untuk masuk kembali

---

## 2. Modul Katalog Publik (Mobil, Paket Wisata, Destinasi)

### 2.1 Melihat & Memfilter Katalog Mobil

1. Pengunjung membuka halaman "Mobil" (`/mobil`)
2. Sistem mengambil seluruh mobil yang `is_active` dari database
3. Pengunjung memilih kategori (chip filter), transmisi, kapasitas kursi, atau kata kunci pencarian
4. Sistem menerapkan filter secara real-time dan menampilkan hasil (harga, kapasitas kursi, transmisi, status "Tersedia") tanpa menampilkan plat nomor (data internal)
5. Pengunjung memilih salah satu mobil untuk melihat detail (`/mobil/{slug}`)
6. Sistem menampilkan galeri foto, spesifikasi, harga sewa per hari, biaya supir (jika ada), serta ulasan pelanggan yang tidak disembunyikan beserta rata-rata rating
7. **Decision**: Apakah pengunjung sudah login?
   - Ya -> Sistem menampilkan tombol "Sewa Sekarang" -> lanjut ke Modul 3
   - Tidak -> Sistem menampilkan tombol "Masuk untuk Sewa Mobil Ini" -> mengarahkan ke halaman login

### 2.2 Melihat Paket Wisata & Destinasi

1. Pengunjung membuka halaman "Paket Wisata" (`/paket-wisata`) atau "Destinasi" (`/destinasi`)
2. Sistem menampilkan daftar paket/destinasi aktif dengan pencarian dan filter durasi (khusus paket)
3. Pengunjung membuka detail salah satu paket wisata
4. Sistem menampilkan rincian paket: durasi, kapasitas, destinasi yang termasuk, harga tetap, dan ulasan pelanggan
5. **Decision**: Apakah pengunjung sudah login?
   - Ya -> Sistem menampilkan tombol "Pesan Paket Ini" -> lanjut ke Modul 4
   - Tidak -> Sistem menampilkan tombol "Masuk untuk Pesan Paket Ini" -> mengarahkan ke halaman login

---

## 3. Modul Pemesanan Mobil (Booking Wizard)

### 3.1 Pelanggan Membuat Booking Mobil

1. Pelanggan menekan "Sewa Sekarang" pada halaman detail mobil, sistem membuka wizard booking (`/booking/mobil/{slug}/baru`)
2. **Langkah 1 - Jadwal**: Pelanggan memilih tanggal & jam mulai serta selesai sewa
3. Sistem menghitung otomatis durasi (hari) dan subtotal harga sewa berdasarkan `price_per_day`
4. **Langkah 2 - Jenis Layanan**: Pelanggan memilih "Lepas Kunci" atau "Dengan Supir"
5. **Decision**: Apakah pelanggan memilih layanan yang memerlukan alamat (dengan supir/antar-jemput)?
   - Ya -> Pelanggan mengisi alamat penjemputan/pengantaran, dapat menambahkan destinasi add-on beserta biayanya
   - Tidak -> lanjut tanpa alamat (ambil langsung di kantor)
6. **Langkah 3 - Konfirmasi Data Diri**: Sistem menampilkan nama & telepon dari profil pelanggan, pelanggan dapat menambahkan catatan opsional
7. **Langkah 4 - Ringkasan & Bayar**: Sistem menampilkan rincian biaya lengkap (harga sewa, biaya supir, biaya antar, add-on destinasi, total)
8. Pelanggan menekan tombol "Buat Pesanan"
9. Sistem mengecek ketersediaan mobil pada rentang tanggal yang dipilih (anti double-booking)
10. **Decision**: Apakah mobil tersedia di rentang tanggal tersebut?
    - Tidak -> Sistem menampilkan error "mobil sudah dipesan pada tanggal tersebut" -> pelanggan kembali ke langkah 2
    - Ya -> lanjut ke langkah 11
11. Sistem menyimpan `bookings` (status `menunggu_pembayaran`) dengan kode booking unik (format `WRC-YYMMDD-XXXXX`), snapshot alamat, dan harga yang dikunci (tidak berubah walau harga master berubah kemudian)
12. Sistem mengarahkan pelanggan ke halaman detail booking untuk melakukan pembayaran -> lanjut ke Modul 5

---

## 4. Modul Pemesanan Paket Wisata (Booking Wizard)

### 4.1 Pelanggan Memesan Paket Wisata

1. Pelanggan menekan "Pesan Paket Ini" pada halaman detail paket, sistem membuka wizard booking (`/booking/paket-wisata/{slug}/baru`)
2. **Langkah 1**: Pelanggan memilih tanggal keberangkatan dan jumlah peserta
3. **Langkah 2**: Pelanggan mengisi alamat penjemputan (wajib diisi)
4. **Langkah 3**: Pelanggan menambahkan catatan tambahan (opsional)
5. **Langkah 4 - Ringkasan & Bayar**: Sistem menampilkan harga tetap sesuai paket (tidak dihitung per hari)
6. Pelanggan menekan tombol "Buat Pesanan"
7. Sistem menyimpan `bookings` (order type `paket_wisata`, status `menunggu_pembayaran`) beserta snapshot alamat penjemputan dan kode booking unik
8. Catatan: booking paket wisata tidak mengunci mobil representatif secara otomatis (tidak ada pengecekan double-booking untuk mobil paket) - admin dapat menugaskan ulang mobil secara manual saat verifikasi
9. Sistem mengarahkan pelanggan ke halaman detail booking untuk melakukan pembayaran -> lanjut ke Modul 5

---

## 5. Modul Pembayaran Manual & Verifikasi

### 5.1 Pelanggan Mengunggah Bukti Transfer

1. Pelanggan membuka halaman detail booking (`/booking/{id}`) dengan status `menunggu_pembayaran`
2. Sistem menampilkan informasi rekening tujuan transfer (diambil dari Modul Pengaturan)
3. Pelanggan mengisi nama & no. rekening pengirim (opsional), lalu mengunggah foto/PDF bukti transfer melalui kotak upload (drag & drop atau klik untuk memilih file)
4. Pelanggan menekan tombol "Unggah Bukti Transfer"
5. Sistem memvalidasi berkas (format JPG/PNG/PDF, maksimal 5MB)
6. **Decision**: Apakah validasi lolos?
   - Tidak -> Sistem menampilkan pesan error di form
   - Ya -> lanjut ke langkah 7
7. Sistem menyimpan bukti transfer ke disk privat (`payments`, status `menunggu`), hanya dapat diakses oleh pemilik booking atau admin/staff
8. Sistem mengubah status `bookings` menjadi `menunggu_verifikasi`
9. Sistem mencatat perubahan status ke `booking_status_logs` dan mengirim notifikasi in-app ke pelanggan

### 5.2 Admin/Staff Memverifikasi Pembayaran

1. Admin/staff membuka halaman "Verifikasi Pembayaran" (`/admin/pembayaran`)
2. Sistem menampilkan antrean pembayaran dengan status `menunggu`
3. Admin/staff membuka bukti transfer yang diunggah pelanggan
4. **Decision**: Apakah pembayaran sesuai dan sah?
   - **Tolak** -> Admin/staff mengisi alasan penolakan (wajib) -> Sistem mengubah status `payments` menjadi `ditolak`, status `bookings` kembali ke `menunggu_pembayaran`, mencatat alasan di `payment_verifications`, mengirim notifikasi ke pelanggan untuk mengunggah ulang -> kembali ke Modul 5.1
   - **Verifikasi** -> lanjut ke langkah 5
5. Sistem mengubah status `payments` menjadi `terverifikasi`
6. Sistem mengubah status `bookings` menjadi `dikonfirmasi`
7. Sistem mencatat aksi verifikasi (siapa, kapan) di `payment_verifications` dan `booking_status_logs`
8. Sistem mengirim notifikasi in-app ke pelanggan bahwa booking telah dikonfirmasi

---

## 6. Modul Manajemen Booking (Admin/Staff)

### 6.1 Admin/Staff Mengelola Status Booking

1. Admin/staff membuka halaman "Semua Booking" (`/admin/booking`), dapat memfilter berdasarkan status, jenis booking, atau kata kunci
2. Admin/staff membuka detail salah satu booking
3. Sistem menampilkan rincian booking, riwayat pembayaran, dan log perubahan status
4. Admin/staff memilih status baru sesuai alur yang diizinkan (state machine): `dikonfirmasi -> berlangsung`, `berlangsung -> selesai`, atau `menunggu_pembayaran/menunggu_verifikasi -> dibatalkan`
5. **Decision**: Apakah transisi status yang dipilih diizinkan dari status saat ini?
   - Tidak -> Sistem menolak perubahan dan menampilkan pesan error
   - Ya -> lanjut ke langkah 6
6. Admin/staff dapat menambahkan catatan internal (tidak terlihat pelanggan)
7. Sistem memperbarui status `bookings`, mencatat perubahan ke `booking_status_logs`
8. Sistem mengirim notifikasi in-app ke pelanggan sesuai status baru

### 6.2 Pelanggan Membatalkan Booking

1. Pelanggan membuka halaman detail booking miliknya
2. **Decision**: Apakah status booking masih `menunggu_pembayaran` atau `menunggu_verifikasi`?
   - Tidak -> Tombol batalkan tidak tersedia, pelanggan diarahkan menghubungi admin secara langsung
   - Ya -> Sistem menampilkan tombol "Batalkan Booking"
3. Pelanggan menekan tombol tersebut, sistem menampilkan dialog konfirmasi
4. Pelanggan mengonfirmasi pembatalan
5. Sistem mengubah status `bookings` menjadi `dibatalkan`, mencatat ke `booking_status_logs`

---

## 7. Modul Ulasan

### 7.1 Pelanggan Menulis Ulasan

1. Pelanggan membuka riwayat booking miliknya
2. **Decision**: Apakah status booking `selesai` dan belum pernah diberi ulasan?
   - Tidak -> Tombol "Tulis Ulasan" tidak ditampilkan
   - Ya -> Sistem menampilkan tombol "Tulis Ulasan" -> pelanggan menekannya
3. Pelanggan memberi rating bintang (1-5) dan komentar opsional
4. Pelanggan menekan tombol "Kirim Ulasan"
5. Sistem memvalidasi bahwa booking benar milik pelanggan, berstatus `selesai`, dan belum memiliki ulasan
6. **Decision**: Apakah validasi lolos?
   - Tidak -> Sistem menolak permintaan (403)
   - Ya -> Sistem menyimpan `reviews` terhubung ke mobil/paket wisata terkait, mengarahkan kembali ke detail booking dengan pesan terima kasih

### 7.2 Admin Memoderasi Ulasan

1. Admin membuka halaman "Ulasan" (`/admin/ulasan`) - staff tidak memiliki akses ke halaman ini
2. Sistem menampilkan seluruh ulasan dengan filter rating, status tampil/sembunyi, dan pencarian
3. Admin menekan tombol "Sembunyikan" atau "Tampilkan" pada salah satu ulasan
4. Sistem membalik nilai `is_hidden` pada ulasan tersebut
5. **Decision**: Apakah ulasan disembunyikan?
   - Ya -> Ulasan tidak lagi tampil di halaman detail mobil/paket wisata publik
   - Tidak -> Ulasan kembali tampil di halaman publik beserta rata-rata rating

---

## 8. Modul Manajemen Katalog (Admin)

### 8.1 Admin Menambah/Mengubah Mobil

1. Admin membuka halaman "Kelola Mobil" (`/admin/mobil`) - staff hanya dapat melihat dan mengubah status ketersediaan
2. Admin menekan tombol "Tambah Mobil" atau memilih mobil untuk diubah
3. Admin mengisi data mobil (kategori, nama, merek, model, tahun, plat nomor internal, transmisi, bahan bakar, kapasitas kursi, harga sewa/hari, biaya supir, biaya antar, status, deskripsi)
4. Admin mengunggah satu atau beberapa foto mobil melalui kotak upload (drag & drop, mendukung multi-file)
5. Admin menekan tombol "Simpan"
6. Sistem memvalidasi input (field wajib, plat nomor unik, format & ukuran foto)
7. **Decision**: Apakah validasi lolos?
   - Tidak -> Sistem menampilkan pesan error di form -> admin memperbaiki input
   - Ya -> lanjut ke langkah 8
8. Sistem menyimpan data mobil beserta foto ke storage publik, slug dibuat otomatis dari nama
9. Sistem menampilkan pesan sukses dan kembali ke daftar mobil

### 8.2 Admin Mengelola Kategori Mobil, Destinasi, dan Paket Wisata

1. Admin membuka salah satu halaman kelola (Kategori Mobil / Destinasi / Paket Wisata) - staff hanya dapat melihat
2. Admin menambah atau mengubah data (nama, deskripsi, harga/biaya add-on, foto, status aktif); khusus paket wisata, admin memilih destinasi yang termasuk dalam paket
3. Admin menekan tombol "Simpan"
4. Sistem memvalidasi dan menyimpan data, slug dibuat otomatis
5. **Decision**: Apakah item dinonaktifkan (`is_active = false`)?
   - Ya -> Sistem memastikan item tidak lagi muncul di katalog publik, namun tetap muncul di riwayat booking lama
   - Tidak -> Item tetap/kembali muncul di katalog publik
6. Admin dapat menghapus data (dengan dialog konfirmasi) jika tidak lagi diperlukan

---

## 9. Modul Manajemen Akun (Admin)

### 9.1 Admin Mengelola Akun Pelanggan

1. Admin membuka halaman "Pengguna" (`/admin/pengguna`)
2. Sistem menampilkan daftar akun customer dengan filter status dan pencarian
3. Admin menekan tombol "Nonaktifkan"/"Aktifkan" atau "Hapus" pada salah satu akun
4. **Decision**: Aksi apa yang dipilih?
   - Nonaktifkan/Aktifkan -> Sistem membalik status akun (`active`/`inactive`)
   - Hapus -> Sistem menampilkan dialog konfirmasi -> admin mengonfirmasi -> Sistem menghapus akun secara soft-delete
5. Catatan: admin tidak dapat mengubah data identitas (nama/email) customer secara langsung - customer tetap mengelola profilnya sendiri

### 9.2 Admin Mengelola Akun Staff & Permission

1. Admin membuka halaman "Staff" (`/admin/staff`)
2. **Decision**: Aksi apa yang dilakukan admin?
   - **Tambah Staff** -> Admin mengisi nama, email, telepon, kata sandi -> Sistem membuat akun baru dengan role `staff`
   - **Ubah Staff** -> Admin mengubah data staff, kata sandi baru bersifat opsional (dikosongkan berarti tidak berubah)
   - **Nonaktifkan/Hapus** -> sama seperti alur pada Modul 9.1
   - **Kelola Permission** -> lanjut ke langkah 3
3. Admin mencentang/menghapus centang permission pada panel "Permission Role Staff" (dikelompokkan per modul)
4. Admin menekan tombol "Simpan Permission"
5. Sistem menyinkronkan daftar permission tersebut ke role `staff` - berlaku untuk seluruh akun staff, berlaku seketika tanpa perlu staff login ulang

---

## 10. Modul Pengaturan Sistem (Admin)

### 10.1 Admin Mengubah Pengaturan

1. Admin membuka halaman "Pengaturan" (`/admin/pengaturan`)
2. Sistem menampilkan form berkelompok: Rekening Bank, Kontak (WhatsApp), Homepage (teks hero), dan Kebijakan Pembatalan
3. Admin mengubah nilai yang diinginkan
4. Admin menekan tombol "Simpan Pengaturan"
5. Sistem memvalidasi dan menyimpan tiap nilai ke tabel `settings`
6. Sistem menampilkan pesan sukses - perubahan langsung berlaku, misalnya info rekening baru langsung tampil di halaman pembayaran booking (Modul 5.1)

---

## 11. Modul Laporan (Admin)

### 11.1 Admin Melihat Laporan

1. Admin membuka halaman "Laporan" (`/admin/laporan`) - staff tidak memiliki akses ke halaman ini
2. Admin memilih rentang tanggal (default: awal bulan berjalan sampai hari ini)
3. Sistem mengambil data pembayaran terverifikasi dan booking dalam rentang tanggal tersebut
4. Sistem mengagregasi data: total pendapatan, breakdown pendapatan harian, jumlah booking per status, okupansi armada, serta 5 mobil dan 5 paket wisata terlaris
5. Sistem menampilkan hasil dalam bentuk kartu statistik, grafik batang sederhana, dan tabel
6. **Decision**: Admin ingin mengekspor laporan?
   - Ya -> Admin menekan tombol "Ekspor CSV" -> Sistem menghasilkan berkas CSV sesuai filter yang aktif -> berkas terunduh otomatis
   - Tidak -> Admin selesai meninjau laporan di halaman

---

## Catatan

- Setiap "Decision" di atas merepresentasikan simbol **Decision** pada flowchart/Activity Diagram (percabangan ya/tidak).
- Setiap langkah yang diawali "Sistem ..." merepresentasikan simbol **Action/Activity** yang dieksekusi oleh sistem (backend Laravel + Inertia/Vue di sisi frontend), bukan interaksi manual manusia.
- Middleware `permission:{slug}` mengatur akses granular per aksi (mis. `vehicles.update`, `payments.verify`, `reviews.moderate`); middleware `admin` menjadi gerbang masuk seluruh area `/admin/*` untuk role `admin` dan `staff`. Permission Fase 3 (`reviews.moderate`, `users.manage`, `staff.manage`, `reports.view_full`, `settings.manage`) bersifat admin-only secara default dan dapat diberikan ke staff kapan saja lewat Modul 9.2.
- Dokumen ini menjadi acuan untuk digambar ulang ke bentuk visual Activity Diagram/Flowchart (draw.io, Figma, atau Mermaid) pada tahap BAB IV (Analisis dan Perancangan).
