# Blackbox Playwright — We Rent Car

Suite Playwright yang menjalankan seluruh 71 skenario pengujian Blackbox dari
`test-case-blackbox-playwright.md` (Tabel 5.2–5.26) secara otomatis dan
menghasilkan foto bukti untuk bagian "Bukti Hasil Pengujian" di skripsi.

## Persiapan

1. Pastikan aplikasi We Rent Car berjalan di komputer ini:
   ```
   php artisan serve
   npm run dev
   ```
2. Masuk ke folder ini, pasang dependency, dan unduh browser Chromium:
   ```
   cd blackbox-playwright
   npm install
   npx playwright install chromium
   ```
3. Salin `.env.test.example` menjadi `.env.test` dan sesuaikan bila perlu
   (secara default sudah cocok dengan akun bawaan seeder).
4. **Jangan** jalankan suite ini melawan database produksi — gunakan database
   pengembangan/uji. Suite ini membuat dan menghapus data sungguhan (kategori,
   mobil, destinasi, paket wisata, akun, booking) selama berjalan; sebagian
   besar dibersihkan otomatis oleh test itu sendiri, tapi beberapa akun/booking
   uji dengan email mengandung kata "blackbox" dan nama mengandung "Uji
   Blackbox" bisa tersisa bila sebuah test gagal di tengah jalan.

## Menjalankan

```
npx playwright test
```

Suite berjalan satu worker, berurutan sesuai nomor awal nama berkas (01–24)
karena banyak skenario saling bergantung data (mis. booking yang dibuat di
berkas 11 dipakai lagi di berkas 12, 13, 14, 16, 17). Total waktu jalan penuh
sekitar 5 menit.

Untuk menjalankan sebagian saja (berguna saat debugging):
```
npx playwright test tests/04-admin-kategori-mobil.spec.ts
```

Lihat laporan HTML interaktif setelah selesai:
```
npx playwright show-report
```

## Keluaran

- `../bukti/<ID>.png` (dan `-a`, `-b`, dst bila satu skenario butuh beberapa foto).
- `../bukti/manifest.json` — daftar `{ id, status, files, catatan }` per skenario.
- `../bukti/hasil.md` — tabel ringkas yang sama dalam bentuk Markdown.

Status di `manifest.json`/`hasil.md` adalah **sumber kebenaran**, bukan status
hijau/merah Playwright itu sendiri — beberapa skenario (lihat di bawah)
sengaja tidak melempar error Playwright agar proses tetap bisa mengambil
screenshot kondisi nyata sebelum mencatat hasilnya sebagai "gagal".

## Struktur proyek

```
blackbox-playwright/
  tests/                 - satu berkas per tabel di dokumen test case,
                            diberi awalan angka sesuai URUTAN EKSEKUSI
                            (bukan urutan nomor tabel), karena skenario
                            lintas tabel saling bergantung data.
  helpers/
    env.ts                - baca variabel environment
    ui.ts                 - login/logout, dialog konfirmasi, fixture file
    state.ts               - penyimpanan kecil lintas-berkas (mis. kode
                             booking yang dibuat di satu berkas, dipakai
                             lagi di berkas lain) via .state.json
    report.ts              - penulisan manifest.json & hasil.md
    shoot.ts                - helper screenshot dengan penamaan baku
    artisan.ts              - menjalankan "php artisan tinker" untuk
                              menyiapkan DATA KHUSUS (mis. memundurkan
                              batas waktu pembayaran), tidak pernah untuk
                              mengubah logika aplikasi
    global-setup.ts         - login awal admin & pelanggan, simpan sebagai
                              storageState supaya tidak perlu login ulang
                              di tiap berkas
  fixtures/
    invalid.txt             - berkas tidak valid untuk uji validasi upload
  .auth/                     - storageState hasil login (dibuat otomatis,
                               boleh diabaikan/dihapus)
  .state.json                - data lintas-berkas (dibuat otomatis)
```

## Keputusan desain penting

- **Dialog konfirmasi** di seluruh aplikasi memakai komponen Vue custom
  (`ConfirmDialog.vue` + composable `useConfirm`), bukan `window.confirm()`
  bawaan browser — jadi semua bisa difoto langsung tanpa perlu `page.once
  ('dialog', ...)`.
- **Toast/flash sukses-error** dirender sebagai `<div>` biasa (bukan komponen
  reusable) dengan auto-dismiss 5 detik di kedua layout (Admin & Publik) -
  screenshot diambil segera setelah teks flash terlihat.
- **browser.newContext() ikut mewarisi `test.use({ storageState })`** milik
  berkas yang memanggilnya. Setiap kali sebuah test butuh context "tamu" yang
  benar-benar belum login (mis. untuk memeriksa katalog publik atau login
  sebagai akun lain), context tersebut HARUS dibuat dengan
  `browser.newContext({ storageState: undefined })` secara eksplisit - ini
  gotcha Playwright yang sempat menyebabkan beberapa skenario salah login
  sebagai akun lain selama pengembangan suite ini.
- Beberapa skenario memakai **`page.route()`** untuk menyadap/mengubah isi
  request (bukan mengubah kode aplikasi) ketika kondisi yang diminta dokumen
  test case (mis. "rating kosong") tidak bisa dicapai lewat interaksi UI biasa
  karena komponennya sendiri tidak pernah mengizinkan nilai kosong.

## Skenario yang gagal (temuan bug nyata aplikasi)

Skenario di `hasil.md` berstatus **GAGAL** bukan karena skrip ujinya salah,
melainkan karena aplikasi memang berperilaku tidak sesuai ekspektasi dokumen
test case. Rinciannya ada di kolom "Catatan" masing-masing baris di
`hasil.md`/`manifest.json`; ringkasannya:

| ID | Temuan | Status |
|---|---|---|
| `BB-11-01` | Menonaktifkan akun pelanggan/staff di panel admin tidak benar-benar memblokir login - `AuthController@login` tidak pernah memeriksa kolom `status`. | Belum diperbaiki |
| `BB-13-02` | Mengubah "Judul Hero" di halaman Pengaturan tidak berpengaruh ke Beranda - `HomeController` tidak mengirim setting tsb dan `Home.vue` memakai teks statis. | Belum diperbaiki |
| `BB-14-02` | Tombol "Ekspor CSV" di halaman Laporan selalu menghasilkan HTTP 500 - `AdminReportController::export()` salah mendeklarasikan tipe kembalian method (`Illuminate\Http\Response` padahal mengembalikan `StreamedResponse`). | **Diperbaiki (2026-10-10)** |
| `BB-19-04` | Dua pelanggan berbeda bisa mendapat pesanan mobil pada mobil & tanggal yang identik selama keduanya belum mengunggah bukti transfer - `Vehicle::isAvailableFor()` tidak menghitung status `menunggu_pembayaran` sebagai penahan tanggal. | Belum diperbaiki |

Sesuai aturan pada dokumen test case ("jangan mengubah kode aplikasi tanpa
persetujuan"), yang belum diperbaiki sengaja tidak disentuh sebagai bagian
dari pembuatan suite ini; `BB-14-02` diperbaiki atas permintaan eksplisit
pemilik produk setelahnya.

## Skenario baru di luar 63 skenario awal

Selama penelusuran kode untuk menulis suite ini, ditemukan beberapa fitur
yang sudah ada di aplikasi namun belum punya skenario pengujian di dokumen
awal. Delapan skenario baru ditambahkan (ditandai "(BARU)" di judul test dan
di `test-case-blackbox-playwright.md`):

- `BB-19-05`, `BB-19-06` — langkah Upload KTP (jaminan sewa) dan batas 1
  sewa mobil aktif per pelanggan pada wizard Sewa Mobil.
- `BB-20-03` — dialog konfirmasi saat pelanggan sudah punya pesanan paket
  wisata aktif.
- `BB-21-03`, `BB-21-04` — hitung mundur batas waktu pembayaran 1 jam dan
  pembatalan otomatis saat kedaluwarsa.
- `BB-09-03` — admin melihat foto KTP jaminan pada detail booking.
- `BB-26-01`, `BB-26-02` — fitur bell notifikasi (sebelumnya backend-nya ada
  tapi tidak ada tampilannya sama sekali).
