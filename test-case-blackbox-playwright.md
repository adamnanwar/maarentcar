# Test Case Blackbox — We Rent Car (untuk dibuatkan skrip Playwright)

Dokumen ini adalah daftar 63 skenario pengujian Blackbox dari skripsi (Tabel 5.2 sampai 5.25). Tujuannya: **membuat skrip Playwright yang menjalankan tiap skenario secara otomatis dan menghasilkan foto bukti (screenshot)** untuk dimasukkan ke bagian "Bukti Hasil Pengujian" di skripsi.

## 1. Konteks sistem

- Aplikasi web rental mobil dan paket wisata **We Rent Car** (Kota Batam).
- Stack: Laravel 12, PHP 8.3, **Vue 3** (bukan React - dikoreksi dari versi dokumen sebelumnya), Inertia JS, TailwindCSS, Vite, **PostgreSQL** (bukan MySQL - lihat `config/database.php` & `.env`).
- Peran: **Admin** (termasuk staff dengan permission) dan **Pelanggan**. Katalog dan destinasi boleh diakses publik.

## 2. Tugas

1. Baca kode aplikasi (routes, controller, halaman React/Inertia) untuk menemukan URL, label tombol, dan selector yang sebenarnya. **Jangan menebak** selector.
2. Buat proyek Playwright (`@playwright/test`, TypeScript) berisi satu test per ID di bagian 5, dengan nama test = ID (mis. `BB-02-01`).
3. Tiap test: jalankan langkah pada kolom *Test Case*, **assert** bahwa *Ekspektasi* benar-benar terjadi, lalu ambil screenshot.
4. Hasilkan folder `bukti/` dan berkas laporan (bagian 4).

## 3. Aturan teknis

**Konfigurasi**
- `baseURL` dan kredensial dari environment, jangan di-hardcode:
  `BASE_URL`, `ADMIN_EMAIL`, `ADMIN_PASSWORD`, `CUSTOMER_EMAIL`, `CUSTOMER_PASSWORD`. Sediakan `.env.test.example`.
- Viewport 1366×768, locale `id-ID`. Satu worker (`workers: 1`), tes dijalankan berurutan karena sebagian saling bergantung data.
- Gunakan **database uji** atau seeder khusus. Jangan menjalankan di data produksi.

**Screenshot**
- Simpan di `bukti/`, nama file **tepat** `<ID>.png`. Jika satu skenario butuh beberapa foto, pakai akhiran `-a`, `-b`, `-c` (mis. `BB-04-01-a.png`, `BB-04-01-b.png`). Petunjuk jumlah foto ada di kolom *Catatan* bagian 5.
- Ambil foto **setelah** ekspektasi terbukti terlihat di layar (gunakan `expect(...).toBeVisible()` lebih dulu).
- Untuk skenario yang menampilkan **toast/notifikasi atau modal**, pakai screenshot viewport (`fullPage: false`) karena elemen fixed bisa bergeser di fullPage. Untuk halaman daftar/dashboard, `fullPage: true`.
- Toast yang hilang sendiri: tunggu elemennya terlihat lalu langsung screenshot. Jika durasinya terlalu singkat, jangan ubah kode aplikasi tanpa melapor; laporkan dan usulkan perubahan.

**Dialog konfirmasi (mis. hapus)**
- Periksa kode: jika memakai modal HTML buatan sendiri, screenshot bisa menangkapnya.
- Jika memakai `window.confirm()` bawaan browser, dialog **tidak muncul di screenshot**. Tangani dengan `page.once('dialog', ...)`, catat teks dialog di laporan, ambil foto sebelum klik dan sesudah hasilnya. Tandai skenario ini `perlu-foto-manual` di laporan.

**Data dan keadaan**
- Skenario bertanda **DATA KHUSUS** atau **LINTAS-PERAN** butuh persiapan: buat data lewat seeder/API/akun lain sebelum langkah utama.
- Alur dua peran memakai **dua browser context** (admin dan pelanggan) dalam satu test.
- Email (BB-17): gunakan `MAIL_MAILER=log` atau Mailpit untuk mengambil tautan reset.
- Data unik per run (email, nama) memakai timestamp agar tes bisa diulang. Kembalikan data yang diubah (pengaturan, profil, kata sandi) atau pakai akun uji khusus.
- File upload memakai fixture di folder `fixtures/` (PNG valid kecil, berkas tidak valid seperti `.txt`, dan berkas melebihi batas ukuran).

**Kejujuran hasil**
- Jika sebuah skenario **gagal**, jangan memaksa lolos dan jangan mengubah ekspektasi. Tandai `gagal`, tetap ambil screenshot kondisi nyata, dan tulis penyebabnya.
- Jangan mengubah kode aplikasi. Jika perlu `data-testid` atau perubahan lain, laporkan dan minta persetujuan.

## 4. Keluaran yang diharapkan

- `bukti/<ID>.png` (dan `-a`, `-b` bila perlu).
- `bukti/manifest.json`: daftar objek `{ "id", "status": "lolos|gagal|perlu-foto-manual", "files": [...], "catatan" }`.
- `bukti/hasil.md`: tabel ringkas ID, status, catatan.
- `README.md`: cara menjalankan (`npx playwright install chromium`, `npx playwright test`).

Folder `bukti/` beserta `manifest.json` nanti dipakai untuk memasukkan foto otomatis ke dokumen skripsi, jadi **penamaan harus persis**.


## 5. Daftar test case

Format ID: `BB-<nomor tabel dua digit>-<urutan dua digit>`. Kolom *Tabel* mengacu ke nomor tabel di skripsi.

### Ringkasan

| Tabel | Halaman / Fitur | Peran | Jumlah skenario |
|---|---|---|---|
| 5.2 | Halaman Login Admin | Admin | 4 |
| 5.3 | Dashboard Admin | Admin | 1 |
| 5.4 | Kelola Kategori Mobil | Admin | 4 |
| 5.5 | Kelola Mobil | Admin | 4 |
| 5.6 | Kelola Destinasi | Admin | 3 |
| 5.7 | Kelola Paket Wisata | Admin | 3 |
| 5.8 | Verifikasi Pembayaran | Admin | 2 |
| 5.9 | Kelola Booking | Admin | 3 |
| 5.10 | Moderasi Ulasan | Admin | 2 |
| 5.11 | Kelola Akun Pelanggan | Admin | 2 |
| 5.12 | Kelola Staff & Permission | Admin | 3 |
| 5.13 | Pengaturan Sistem | Admin | 2 |
| 5.14 | Laporan | Admin | 2 |
| 5.15 | Registrasi Akun | Pelanggan | 3 |
| 5.16 | Halaman Login Pelanggan | Pelanggan | 2 |
| 5.17 | Lupa Kata Sandi & Reset | Pelanggan | 3 |
| 5.18 | Lihat Katalog Mobil & Paket Wisata | Publik / Pelanggan | 3 |
| 5.19 | Sewa Mobil | Pelanggan | 6 |
| 5.20 | Sewa Paket Wisata | Pelanggan | 3 |
| 5.21 | Upload Bukti Pembayaran | Pelanggan | 4 |
| 5.22 | Riwayat & Kelola Booking | Pelanggan | 3 |
| 5.23 | Tulis Ulasan | Pelanggan | 2 |
| 5.24 | Edit Profil Saya | Pelanggan | 3 |
| 5.25 | Lihat Destinasi | Publik / Pelanggan | 2 |
| 5.26 | Notifikasi (BARU) | Pelanggan | 2 |
| | **Total** | | **71** |

> Catatan pembaruan: 8 skenario baru ditandai "(BARU)" ditambahkan setelah
> penelusuran kode menemukan fitur yang sudah berjalan di aplikasi namun
> belum punya skenario uji di versi dokumen sebelumnya (lihat BB-19-05,
> BB-19-06, BB-20-03, BB-21-03, BB-21-04, BB-09-03, BB-26-01, BB-26-02).
> Jumlah skenario per tabel di atas sudah termasuk penambahan ini.

### Tabel 5.2 — Pengujian Halaman Login Admin

**Peran:** Admin

| ID | Skenario | Test Case (langkah) | Ekspektasi | Catatan teknis / foto |
|---|---|---|---|---|
| `BB-02-01` | Login dengan data valid | Masukkan email dan password admin yang benar, klik Masuk | Berhasil login dan diarahkan ke halaman Dashboard Admin | Tanpa sesi login. Isi kredensial admin valid; foto = halaman Dashboard Admin setelah redirect. |
| `BB-02-02` | Login dengan email tidak terdaftar | Masukkan email yang tidak terdaftar | Muncul pesan error "Email atau password salah" | Pesan error tampil inline/toast. Tunggu teks pesan terlihat, baru foto. |
| `BB-02-03` | Login dengan password salah | Masukkan email benar dan password salah | Muncul pesan error "Email atau password salah" | Gunakan email admin asli + password salah. Tunggu pesan error terlihat, baru foto. |
| `BB-02-04` | Login dengan field kosong | Kosongkan email/password, klik Masuk | Muncul pesan validasi bahwa field wajib diisi | Klik Masuk dengan field kosong. Foto saat pesan validasi tampil (bisa validasi browser `required`; jika begitu, catat di laporan). |

### Tabel 5.3 — Pengujian Dashboard Admin

**Peran:** Admin

| ID | Skenario | Test Case (langkah) | Ekspektasi | Catatan teknis / foto |
|---|---|---|---|---|
| `BB-03-01` | Menampilkan ringkasan data | Admin membuka halaman Dashboard | Sistem menampilkan total mobil, mobil tersedia, destinasi, paket wisata, booking menunggu verifikasi, dan booking berlangsung | Login admin. Pastikan ada data seed agar kartu ringkasan terisi. Foto = Dashboard penuh. |

### Tabel 5.4 — Pengujian Kelola Kategori Mobil

**Peran:** Admin

| ID | Skenario | Test Case (langkah) | Ekspektasi | Catatan teknis / foto |
|---|---|---|---|---|
| `BB-04-01` | Tambah kategori | Isi nama dan deskripsi kategori, klik Simpan | Kategori baru tersimpan dan tampil pada daftar kategori | Foto `-a` form terisi, foto `-b` daftar kategori yang memuat data baru (setelah notifikasi sukses). |
| `BB-04-02` | Ubah kategori | Ubah nama/deskripsi kategori, klik Simpan | Perubahan tersimpan dan tampil pada daftar | Ubah kategori yang dibuat di BB-04-01. Foto hasil pada daftar. |
| `BB-04-03` | Hapus kategori | Klik ikon hapus pada kategori | Kategori terhapus dari daftar | Hapus kategori uji (jangan data penting). CEK apakah tombol hapus memakai `window.confirm()` atau modal buatan; lihat aturan dialog. |
| `BB-04-04` | Tambah kategori tanpa nama | Kosongkan nama kategori, klik Simpan | Muncul pesan validasi bahwa nama kategori wajib diisi | Nama dikosongkan. Foto saat pesan validasi nama wajib tampil. |

### Tabel 5.5 — Pengujian Kelola Mobil

**Peran:** Admin

| ID | Skenario | Test Case (langkah) | Ekspektasi | Catatan teknis / foto |
|---|---|---|---|---|
| `BB-05-01` | Tambah mobil | Isi seluruh data mobil dan unggah foto, klik Simpan | Mobil baru tersimpan dan tampil pada katalog | Upload foto memakai file fixture (PNG kecil) via `setInputFiles`. Foto `-a` form, `-b` mobil tampil di katalog. |
| `BB-05-02` | Ubah mobil | Ubah data mobil yang sudah ada, klik Simpan | Perubahan data mobil tersimpan | Ubah data mobil uji dari BB-05-01. Foto hasil. |
| `BB-05-03` | Hapus mobil | Klik ikon hapus pada data mobil | Mobil terhapus dari daftar | Hapus mobil uji. Cek jenis dialog konfirmasi (lihat aturan dialog). |
| `BB-05-04` | Menonaktifkan status mobil | Ubah status mobil menjadi tidak tersedia | Mobil tidak tampil pada katalog publik | Foto `-a` form status tidak tersedia, foto `-b` katalog publik (konteks tanpa login) yang TIDAK memuat mobil tsb. |

### Tabel 5.6 — Pengujian Kelola Destinasi

**Peran:** Admin

| ID | Skenario | Test Case (langkah) | Ekspektasi | Catatan teknis / foto |
|---|---|---|---|---|
| `BB-06-01` | Tambah destinasi | Isi nama, kategori, biaya add-on, alamat, deskripsi, dan foto, klik Simpan | Destinasi baru tersimpan dan tampil pada daftar | Upload foto fixture. Foto `-a` form, `-b` daftar destinasi memuat data baru. |
| `BB-06-02` | Ubah destinasi | Ubah data destinasi, klik Simpan | Perubahan data destinasi tersimpan | Ubah destinasi uji dari BB-06-01. |
| `BB-06-03` | Hapus destinasi | Klik ikon hapus pada destinasi | Destinasi terhapus dari daftar | Hapus destinasi uji. Cek dialog konfirmasi. |

### Tabel 5.7 — Pengujian Kelola Paket Wisata

**Peran:** Admin

| ID | Skenario | Test Case (langkah) | Ekspektasi | Catatan teknis / foto |
|---|---|---|---|---|
| `BB-07-01` | Tambah paket wisata | Isi nama, durasi, harga, dan destinasi yang termasuk, klik Simpan | Paket wisata baru tersimpan dan tampil pada katalog | Perlu minimal satu destinasi sebagai pilihan. Foto `-a` form, `-b` katalog paket memuat data baru. |
| `BB-07-02` | Ubah paket wisata | Ubah data paket wisata, klik Simpan | Perubahan data paket wisata tersimpan | Ubah paket uji dari BB-07-01. |
| `BB-07-03` | Hapus paket wisata | Klik ikon hapus pada paket wisata | Paket wisata terhapus dari daftar | Hapus paket uji. Cek dialog konfirmasi. |

### Tabel 5.8 — Pengujian Verifikasi Pembayaran

**Peran:** Admin

| ID | Skenario | Test Case (langkah) | Ekspektasi | Catatan teknis / foto |
|---|---|---|---|---|
| `BB-08-01` | Menyetujui bukti transfer valid | Admin memeriksa bukti transfer, klik Verifikasi | Status booking berubah menjadi Dikonfirmasi dan notifikasi terkirim ke pelanggan | LINTAS-PERAN. Prasyarat: booking berstatus menunggu verifikasi + bukti transfer (buat lewat alur BB-19/BB-21 atau seeder). Foto `-a` sisi admin setelah verifikasi, `-b` notifikasi/status di sisi pelanggan (context kedua). |
| `BB-08-02` | Menolak bukti transfer | Admin klik Tolak pada bukti transfer | Status booking berubah menjadi Ditolak dan notifikasi terkirim ke pelanggan | LINTAS-PERAN. Butuh booking menunggu verifikasi lain (data terpisah dari BB-08-01). Foto `-a` admin, `-b` sisi pelanggan. |

### Tabel 5.9 — Pengujian Kelola Booking

**Peran:** Admin

| ID | Skenario | Test Case (langkah) | Ekspektasi | Catatan teknis / foto |
|---|---|---|---|---|
| `BB-09-01` | Mengubah status booking | Admin memilih status baru pada halaman detail booking, klik Simpan | Status booking diperbarui sesuai alur yang diizinkan | Prasyarat: booking yang statusnya masih boleh diubah. Pilih status yang diizinkan alur. Foto detail booking setelah disimpan. |
| `BB-09-02` | Mencari/memfilter booking | Admin mengisi kata kunci kode booking/nama pelanggan atau memilih filter status | Sistem menampilkan data booking sesuai kata kunci/filter | Foto `-a` hasil pencarian kode/nama, `-b` hasil filter status. |
| `BB-09-03` (BARU) | Melihat KTP jaminan pelanggan | Admin membuka detail booking sewa mobil | Sistem menampilkan foto KTP yang diunggah pelanggan sebagai jaminan, beserta tautan untuk melihat berkas aslinya | Prasyarat: booking sewa mobil yang sudah melalui wizard dengan upload KTP (fitur baru). Foto bagian "KTP Jaminan" pada detail booking. |

### Tabel 5.10 — Pengujian Moderasi Ulasan

**Peran:** Admin

| ID | Skenario | Test Case (langkah) | Ekspektasi | Catatan teknis / foto |
|---|---|---|---|---|
| `BB-10-01` | Melihat daftar ulasan | Admin membuka halaman Ulasan | Sistem menampilkan seluruh ulasan pelanggan beserta rating | Prasyarat: ada ulasan (buat lewat BB-23-01 atau seeder). Foto daftar ulasan + rating. |
| `BB-10-02` | Memoderasi ulasan | Admin memproses ulasan yang tidak sesuai ketentuan | Ulasan diproses sesuai aksi yang dipilih admin | Aksi moderasi mengikuti UI yang ada (cek tombol yang tersedia). Foto hasil setelah aksi. |

### Tabel 5.11 — Pengujian Kelola Akun Pelanggan

**Peran:** Admin

| ID | Skenario | Test Case (langkah) | Ekspektasi | Catatan teknis / foto |
|---|---|---|---|---|
| `BB-11-01` | Menonaktifkan akun pelanggan | Admin klik Nonaktifkan pada akun pelanggan | Status akun berubah nonaktif dan pelanggan tidak dapat login | Gunakan AKUN PELANGGAN KHUSUS UJI. Foto `-a` admin setelah nonaktif, `-b` percobaan login pelanggan tsb ditolak (context kedua). **Catatan hasil pengujian: skenario ini GAGAL di implementasi saat ini** - status nonaktif tersimpan tapi tidak benar-benar memblokir login (`AuthController@login` tidak memeriksa kolom `status`). |
| `BB-11-02` | Menghapus akun pelanggan | Admin klik Hapus pada akun pelanggan | Akun pelanggan terhapus dari sistem | Gunakan akun pelanggan khusus uji (bukan yang dipakai tes lain). Cek dialog konfirmasi. |

### Tabel 5.12 — Pengujian Kelola Staff & Permission

**Peran:** Admin

| ID | Skenario | Test Case (langkah) | Ekspektasi | Catatan teknis / foto |
|---|---|---|---|---|
| `BB-12-01` | Tambah staff | Isi nama, email, telepon, password, klik Simpan | Akun staff baru tersimpan | Email staff unik per run (timestamp). Foto daftar staff memuat akun baru. |
| `BB-12-02` | Nonaktifkan/hapus staff | Admin klik Nonaktifkan/Hapus pada akun staff | Akun staff dinonaktifkan/terhapus | Pakai staff yang dibuat di BB-12-01. Cek dialog konfirmasi bila hapus. |
| `BB-12-03` | Mengatur permission staff | Admin mencentang/menghapus centang hak akses, klik Simpan Permission | Permission diperbarui dan berlaku untuk seluruh akun staff | Centang/hapus centang permission lalu simpan. Foto `-a` sebelum simpan, `-b` notifikasi sukses. |

### Tabel 5.13 — Pengujian Pengaturan Sistem

**Peran:** Admin

| ID | Skenario | Test Case (langkah) | Ekspektasi | Catatan teknis / foto |
|---|---|---|---|---|
| `BB-13-01` | Mengubah rekening bank & kontak | Admin mengubah data rekening/nomor WhatsApp, klik Simpan Pengaturan | Data pengaturan tersimpan dan digunakan oleh sistem | Ubah rekening/WhatsApp lalu simpan; KEMBALIKAN nilai semula setelah tes. Foto notifikasi sukses. |
| `BB-13-02` | Mengubah konten homepage | Admin mengubah judul dan subjudul hero, klik Simpan Pengaturan | Perubahan tampil pada halaman Beranda | Ubah judul/subjudul hero; foto `-a` pengaturan, `-b` Beranda publik memuat perubahan. Kembalikan nilai semula. **Catatan hasil pengujian: skenario ini GAGAL di implementasi saat ini** - pengaturan tersimpan tapi `HomeController` tidak mengirimkannya ke halaman Beranda, yang memakai teks hero statis. |

### Tabel 5.14 — Pengujian Laporan

**Peran:** Admin

| ID | Skenario | Test Case (langkah) | Ekspektasi | Catatan teknis / foto |
|---|---|---|---|---|
| `BB-14-01` | Memfilter rentang tanggal | Admin memilih tanggal dari-sampai, klik Terapkan | Sistem menampilkan data laporan sesuai rentang tanggal | Isi tanggal dari–sampai (rentang yang ada datanya). Foto laporan terfilter. |
| `BB-14-02` | Mengekspor laporan | Admin klik Ekspor CSV | Berkas CSV laporan berhasil terunduh | Gunakan `page.waitForEvent("download")`. Foto halaman setelah klik + catat nama/ukuran file di laporan. Pastikan file terunduh (>0 byte). **Catatan hasil pengujian: skenario ini GAGAL di implementasi saat ini** - endpoint ekspor selalu error HTTP 500 (TypeError tipe kembalian method `AdminReportController::export()` salah). |

### Tabel 5.15 — Pengujian Registrasi Akun

**Peran:** Pelanggan

| ID | Skenario | Test Case (langkah) | Ekspektasi | Catatan teknis / foto |
|---|---|---|---|---|
| `BB-15-01` | Registrasi dengan data valid | Isi nama, email, telepon, password, klik Daftar | Akun tersimpan dan pelanggan otomatis login lalu diarahkan ke Dashboard Pelanggan | Email UNIK per run (mis. `uji+<timestamp>@example.com`). Foto Dashboard Pelanggan setelah otomatis login. Koreksi dari versi dokumen sebelumnya: redirect sesungguhnya menuju `/dashboard`, bukan Beranda (`/`) - lihat `AuthController@register` dan `activity-flow.md` modul 1.1. |
| `BB-15-02` | Registrasi dengan email sudah terdaftar | Isi email yang sudah digunakan akun lain | Muncul pesan error bahwa email sudah terdaftar | Pakai email yang sudah ada (mis. akun uji BB-16). Foto pesan email sudah terdaftar. |
| `BB-15-03` | Registrasi dengan field kosong | Kosongkan salah satu field wajib, klik Daftar | Muncul pesan validasi bahwa field wajib diisi | Kosongkan satu field wajib. Foto pesan validasi. |

### Tabel 5.16 — Pengujian Halaman Login Pelanggan

**Peran:** Pelanggan

| ID | Skenario | Test Case (langkah) | Ekspektasi | Catatan teknis / foto |
|---|---|---|---|---|
| `BB-16-01` | Login dengan data valid | Masukkan email dan password yang terdaftar | Berhasil login dan diarahkan ke halaman Dashboard Pelanggan | Pakai akun pelanggan uji. Foto Dashboard Pelanggan dalam keadaan login. Koreksi dari versi dokumen sebelumnya: redirect sesungguhnya menuju `/dashboard`, bukan Beranda - lihat `AuthController@login` dan `activity-flow.md` modul 1.2. |
| `BB-16-02` | Login dengan kredensial salah | Masukkan email/password yang salah | Muncul pesan error bahwa kredensial tidak valid | Password salah. Foto pesan error kredensial. |

### Tabel 5.17 — Pengujian Lupa Kata Sandi & Reset

**Peran:** Pelanggan

| ID | Skenario | Test Case (langkah) | Ekspektasi | Catatan teknis / foto |
|---|---|---|---|---|
| `BB-17-01` | Mengirim link reset | Masukkan email terdaftar, klik Kirim Link Reset | Email berisi tautan reset kata sandi terkirim | Butuh email: set `MAIL_MAILER=log` (baca tautan dari `storage/logs`) atau Mailpit. Foto `-a` notifikasi di halaman, `-b` isi email/log (jika memungkinkan). |
| `BB-17-02` | Reset kata sandi baru | Buka tautan reset, isi kata sandi baru, klik simpan | Kata sandi diperbarui dan pelanggan diarahkan ke halaman Login | Ambil tautan reset dari email/log. Isi kata sandi baru; setelah selesai, kembalikan/gunakan akun uji khusus. Foto halaman Login setelah redirect. |
| `BB-17-03` | Reset dengan email tidak terdaftar | Masukkan email yang tidak terdaftar | Muncul pesan error bahwa email tidak ditemukan | Email tidak terdaftar. Foto pesan error tidak ditemukan. |

### Tabel 5.18 — Pengujian Lihat Katalog Mobil & Paket Wisata

**Peran:** Publik / Pelanggan

| ID | Skenario | Test Case (langkah) | Ekspektasi | Catatan teknis / foto |
|---|---|---|---|---|
| `BB-18-01` | Melihat katalog | Membuka halaman Mobil / Paket Wisata | Sistem menampilkan katalog yang berstatus aktif | Boleh tanpa login. Foto `-a` katalog mobil, `-b` katalog paket wisata. |
| `BB-18-02` | Memfilter katalog | Menggunakan filter kategori/kata kunci pencarian | Sistem menampilkan hasil sesuai filter/kata kunci | Gunakan filter kategori atau kata kunci. Foto hasil terfilter. |
| `BB-18-03` | Melihat detail item | Mengeklik salah satu mobil/paket wisata | Sistem menampilkan detail beserta ulasan pelanggan | Buka detail satu item. Foto detail + bagian ulasan. |

### Tabel 5.19 — Pengujian Sewa Mobil

**Peran:** Pelanggan

| ID | Skenario | Test Case (langkah) | Ekspektasi | Catatan teknis / foto |
|---|---|---|---|---|
| `BB-19-01` | Mengisi jadwal sewa | Memilih tanggal & jam mulai-selesai, klik Lanjut | Jadwal tersimpan dan proses lanjut ke langkah berikutnya | Login pelanggan. Wizard multi-langkah: foto langkah jadwal setelah klik Lanjut. |
| `BB-19-02` | Memilih metode serah terima | Memilih layanan lepas kunci atau dengan supir | Sistem menghitung biaya tambahan sesuai layanan yang dipilih | Pilih lepas kunci vs dengan supir; foto `-a` tiap pilihan yang menampilkan biaya tambahan berbeda. |
| `BB-19-03` | Konfirmasi dan membuat pesanan | Memeriksa ringkasan data, klik Buat Pesanan | Booking tersimpan dan pelanggan diarahkan ke halaman pembayaran | Foto `-a` ringkasan sebelum klik, `-b` halaman pembayaran setelah Buat Pesanan. |
| `BB-19-04` | Memesan pada tanggal yang tidak tersedia | Memilih tanggal yang bertabrakan dengan sewa lain | Muncul pesan bahwa mobil tidak tersedia pada tanggal tersebut | DATA KHUSUS. Prasyarat: mobil yang sama sudah dibooking pada tanggal tsb (buat lewat akun lain/seeder). Foto pesan tidak tersedia. **Catatan hasil pengujian: skenario ini GAGAL di implementasi saat ini** - lihat README suite Playwright untuk rincian akar masalah (`Vehicle::isAvailableFor()` tidak menghitung status `menunggu_pembayaran` sebagai penahan tanggal). |
| `BB-19-05` (BARU) | Upload KTP sebagai syarat sewa | Melanjutkan wizard tanpa mengunggah KTP, lalu mengunggah KTP dan melanjutkan | Tombol Lanjut tidak dapat diklik tanpa KTP; setelah KTP diunggah proses dapat dilanjutkan | Fitur baru: Langkah 3 wizard Sewa Mobil mewajibkan unggah foto KTP sebagai jaminan. Foto `-a` tombol Lanjut nonaktif tanpa KTP, `-b` setelah KTP diunggah. |
| `BB-19-06` (BARU) | Batas satu sewa mobil aktif | Pelanggan yang masih memiliki sewa mobil aktif mencoba membuka wizard sewa mobil baru | Pelanggan diarahkan kembali ke booking aktifnya dengan pesan bahwa hanya boleh 1 sewa mobil aktif | Fitur baru: 1 pelanggan hanya boleh punya 1 booking sewa mobil aktif pada satu waktu (tidak berlaku untuk paket wisata). Foto pesan peringatan pada booking lama. |

### Tabel 5.20 — Pengujian Sewa Paket Wisata

**Peran:** Pelanggan

| ID | Skenario | Test Case (langkah) | Ekspektasi | Catatan teknis / foto |
|---|---|---|---|---|
| `BB-20-01` | Mengisi jadwal dan alamat penjemputan | Memilih tanggal keberangkatan, mengisi alamat penjemputan | Data tersimpan dan sistem menampilkan ringkasan harga | Pilih tanggal dan isi alamat penjemputan. Foto ringkasan harga. |
| `BB-20-02` | Konfirmasi dan membuat pesanan | Klik Buat Pesanan | Booking paket wisata tersimpan dan diarahkan ke halaman pembayaran | Foto `-a` ringkasan, `-b` halaman pembayaran. |
| `BB-20-03` (BARU) | Peringatan sudah punya pesanan paket wisata aktif | Pelanggan yang sudah memiliki pesanan paket wisata aktif membuka wizard paket wisata lain | Muncul dialog konfirmasi bahwa pelanggan sudah memesan paket sebelumnya, dengan pilihan lanjut atau batal | Fitur baru: tidak ada batas jumlah paket wisata aktif, tapi sistem memberi peringatan konfirmasi. Foto dialog konfirmasi. |

### Tabel 5.21 — Pengujian Upload Bukti Pembayaran

**Peran:** Pelanggan

| ID | Skenario | Test Case (langkah) | Ekspektasi | Catatan teknis / foto |
|---|---|---|---|---|
| `BB-21-01` | Mengunggah bukti transfer valid | Mengunggah berkas JPG/PNG/PDF sesuai ketentuan ukuran, klik Kirim | Bukti tersimpan dan status booking berubah menjadi menunggu verifikasi | Prasyarat: booking menunggu pembayaran. Upload fixture JPG/PNG valid. Foto `-a` form terisi, `-b` status menunggu verifikasi. |
| `BB-21-02` | Mengunggah berkas tidak valid | Mengunggah berkas dengan format/ukuran yang tidak sesuai | Muncul pesan error bahwa format/ukuran berkas tidak valid | Upload fixture tidak valid (mis. .txt atau melebihi batas ukuran). Foto pesan error format/ukuran. |
| `BB-21-03` (BARU) | Menampilkan hitung mundur batas waktu pembayaran | Membuka halaman booking yang masih menunggu pembayaran | Sistem menampilkan hitung mundur 1 jam beserta peringatan batas waktu di atasnya | Fitur baru: booking punya batas waktu 1 jam untuk transfer. Foto banner hitung mundur. |
| `BB-21-04` (BARU) | Booking kedaluwarsa otomatis dibatalkan | Booking tidak dibayar hingga melewati batas waktu 1 jam | Status booking otomatis berubah menjadi Dibatalkan | Fitur baru. DATA KHUSUS: batas waktu disimulasikan mundur lewat data uji (`payment_due_at`), bukan menunggu 1 jam sungguhan. Foto status Dibatalkan. |

### Tabel 5.22 — Pengujian Riwayat & Kelola Booking

**Peran:** Pelanggan

| ID | Skenario | Test Case (langkah) | Ekspektasi | Catatan teknis / foto |
|---|---|---|---|---|
| `BB-22-01` | Melihat riwayat booking | Membuka halaman Riwayat Booking | Sistem menampilkan daftar booking beserta statusnya | Foto daftar riwayat beserta status. |
| `BB-22-02` | Membatalkan booking | Klik Batalkan Booking pada booking yang masih dapat dibatalkan | Status booking berubah menjadi dibatalkan | Prasyarat: booking yang masih bisa dibatalkan. Cek dialog konfirmasi. Foto status dibatalkan. |
| `BB-22-03` | Membatalkan booking yang tidak dapat dibatalkan | Klik Batalkan Booking pada booking yang statusnya tidak lagi memungkinkan | Muncul pesan bahwa booking tidak bisa dibatalkan | Prasyarat: booking berstatus yang tidak bisa dibatalkan. Bila tombol disembunyikan/disabled, catat di laporan dan foto kondisi tsb. |

### Tabel 5.23 — Pengujian Tulis Ulasan

**Peran:** Pelanggan

| ID | Skenario | Test Case (langkah) | Ekspektasi | Catatan teknis / foto |
|---|---|---|---|---|
| `BB-23-01` | Menulis ulasan dengan data valid | Mengisi rating dan komentar, klik Kirim | Ulasan tersimpan dan muncul pesan terima kasih | Prasyarat: booking selesai milik pelanggan uji. Foto `-a` form, `-b` pesan terima kasih. |
| `BB-23-02` | Menulis ulasan tanpa rating | Mengosongkan rating, klik Kirim | Muncul pesan validasi bahwa rating wajib diisi | Kosongkan rating. Foto pesan validasi rating wajib. |

### Tabel 5.24 — Pengujian Edit Profil Saya

**Peran:** Pelanggan

| ID | Skenario | Test Case (langkah) | Ekspektasi | Catatan teknis / foto |
|---|---|---|---|---|
| `BB-24-01` | Mengubah data diri | Mengubah nama/email/telepon, klik Simpan Perubahan | Data profil berhasil diperbarui | Ubah data diri; foto notifikasi sukses. Kembalikan data semula setelah tes. |
| `BB-24-02` | Mengubah kata sandi | Mengisi kata sandi saat ini dan kata sandi baru, klik Ubah Kata Sandi | Kata sandi berhasil diperbarui | Gunakan akun uji khusus atau kembalikan kata sandi setelah tes. Foto notifikasi sukses. |
| `BB-24-03` | Konfirmasi kata sandi tidak cocok | Mengisi kata sandi baru dan konfirmasi yang berbeda | Muncul pesan error bahwa konfirmasi kata sandi tidak cocok | Konfirmasi berbeda dari sandi baru. Foto pesan error tidak cocok. |

### Tabel 5.25 — Pengujian Lihat Destinasi

**Peran:** Publik / Pelanggan

| ID | Skenario | Test Case (langkah) | Ekspektasi | Catatan teknis / foto |
|---|---|---|---|---|
| `BB-25-01` | Melihat daftar destinasi | Membuka halaman Destinasi | Sistem menampilkan daftar destinasi wisata | Boleh tanpa login. Foto daftar destinasi. |
| `BB-25-02` | Melihat detail destinasi | Mengeklik salah satu destinasi | Sistem menampilkan deskripsi dan paket wisata terkait | Buka detail satu destinasi. Foto deskripsi + paket terkait. |

### Tabel 5.26 — Pengujian Notifikasi (BARU)

**Peran:** Pelanggan

Fitur baru ditemukan saat penelusuran kode: backend notifikasi (perubahan
status booking, pengingat masa sewa) sudah ada sejak awal, tapi sebelumnya
tidak ada tampilan bell/dropdown sama sekali di navbar - ditambahkan sebagai
bagian dari pengembangan ini.

| ID | Skenario | Test Case (langkah) | Ekspektasi | Catatan teknis / foto |
|---|---|---|---|---|
| `BB-26-01` (BARU) | Melihat notifikasi | Membuka ikon lonceng notifikasi di navbar | Sistem menampilkan daftar notifikasi pelanggan beserta badge jumlah belum dibaca | Prasyarat: pelanggan punya minimal satu notifikasi (otomatis ada dari perubahan status booking). Foto dropdown notifikasi terbuka. |
| `BB-26-02` (BARU) | Menandai semua notifikasi dibaca | Klik "Tandai semua dibaca" pada dropdown notifikasi | Badge jumlah notifikasi belum dibaca hilang dari ikon lonceng | Foto ikon lonceng setelah ditandai dibaca (tanpa badge). |
