# Hasil Pengujian Blackbox — We Rent Car

Dihasilkan otomatis oleh suite Playwright pada 2026-10-10T11:17:10.204Z.

Total skenario: **71** — Lolos: **68** · Gagal: **3** · Perlu foto manual: **0**

| ID | Status | Berkas | Catatan |
|---|---|---|---|
| BB-02-01 | Lolos | `BB-02-01.png` | Login admin valid berhasil diarahkan ke /admin (Dashboard Admin). |
| BB-02-02 | Lolos | `BB-02-02.png` | Pesan error "Email atau kata sandi salah." tampil untuk email tidak terdaftar. |
| BB-02-03 | Lolos | `BB-02-03.png` | Pesan error "Email atau kata sandi salah." tampil untuk password yang salah. |
| BB-02-04 | Lolos | `BB-02-04.png` | Validasi field wajib memakai validasi bawaan browser HTML5 (atribut "required"), bukan pesan dari server. Pesan validasi browser pada field email: "Please fill out this field.". |
| BB-03-01 | Lolos | `BB-03-01.png` | Dashboard admin menampilkan seluruh kartu ringkasan (mobil, destinasi, paket, status booking). |
| BB-04-01 | Lolos | `BB-04-01-a.png`, `BB-04-01-b.png` | Kategori baru tersimpan dan tampil pada daftar kategori mobil. |
| BB-04-02 | Lolos | `BB-04-02.png` | Perubahan nama kategori tersimpan dan tampil pada daftar. |
| BB-04-03 | Lolos | `BB-04-03.png` | Kategori uji berhasil dihapus dan tidak lagi tampil pada daftar. Dialog konfirmasi berupa modal custom (ConfirmDialog.vue), bukan window.confirm() bawaan browser, sehingga dapat difoto langsung. |
| BB-04-04 | Lolos | `BB-04-04.png` | Nama kategori wajib diisi dicegah lewat validasi bawaan browser (atribut "required"). Pesan browser: "Please fill out this field.". |
| BB-05-01 | Lolos | `BB-05-01-a.png`, `BB-05-01-b.png` | Mobil baru tersimpan, tampil pada daftar admin maupun katalog publik. |
| BB-05-02 | Lolos | `BB-05-02.png` | Perubahan harga sewa mobil berhasil tersimpan. |
| BB-05-03 | Lolos | `BB-05-03.png` | Mobil uji berhasil dihapus dari daftar. |
| BB-05-04 | Lolos | `BB-05-04-a.png`, `BB-05-04-b.png` | Mobil yang dinonaktifkan tidak lagi tampil pada katalog publik (diverifikasi tanpa sesi login). |
| BB-06-01 | Lolos | `BB-06-01-a.png`, `BB-06-01-b.png` | Destinasi baru tersimpan dan tampil pada daftar. |
| BB-06-02 | Lolos | `BB-06-02.png` | Perubahan nama destinasi tersimpan dan tampil pada daftar. |
| BB-06-03 | Lolos | `BB-06-03.png` | Destinasi uji berhasil dihapus dari daftar. |
| BB-07-01 | Lolos | `BB-07-01-a.png`, `BB-07-01-b.png` | Paket wisata baru tersimpan, tampil pada daftar admin dan katalog publik. |
| BB-07-02 | Lolos | `BB-07-02.png` | Perubahan nama paket wisata tersimpan dan tampil pada daftar. |
| BB-07-03 | Lolos | `BB-07-03.png` | Paket wisata uji berhasil dihapus dari daftar. |
| BB-08-01 | Lolos | `BB-08-01-a.png`, `BB-08-01-b.png` | Admin memverifikasi booking WRC-261007-ZHBPU, status berubah menjadi Dikonfirmasi dan terlihat juga di sisi pelanggan. |
| BB-08-02 | Lolos | `BB-08-02-a.png`, `BB-08-02-b.png` | Admin menolak bukti transfer booking WRC-261007-CP5VH beserta alasan, status booking kembali ke Menunggu Pembayaran. |
| BB-09-01 | Lolos | `BB-09-01.png` | Status booking WRC-261007-ZHBPU berhasil diubah dari Dikonfirmasi menjadi Sedang Berlangsung sesuai alur yang diizinkan. |
| BB-09-02 | Lolos | `BB-09-02-a.png`, `BB-09-02-b.png` | Pencarian berdasarkan kode booking dan filter status keduanya menampilkan hasil yang sesuai. |
| BB-09-03 | Lolos | `BB-09-03.png` | Skenario baru: admin dapat melihat foto KTP yang diunggah pelanggan sebagai jaminan langsung dari halaman detail booking. |
| BB-10-01 | Lolos | `BB-10-01.png` | Daftar ulasan pelanggan tampil lengkap beserta rating bintang. |
| BB-10-02 | Lolos | `BB-10-02-a.png`, `BB-10-02-b.png` | Admin berhasil menyembunyikan lalu menampilkan kembali ulasan dari katalog publik. |
| BB-11-01 | GAGAL | `BB-11-01-a.png`, `BB-11-01-b.png` | GAGAL (temuan bug nyata, bukan masalah skrip uji): status akun berhasil berubah menjadi Nonaktif di panel admin, namun pelanggan tersebut TETAP BISA login normal setelahnya. Penyebab: AuthController@login (app/Http/Controllers/AuthController.php) hanya memanggil Auth::attempt($credentials) berdasarkan email+password, tidak ada pengecekan kolom status user sama sekali di mana pun pada alur login. Akibatnya field status pelanggan/staff saat ini murni kosmetik dan tidak benar-benar memblokir akses. Tidak diperbaiki di sini sesuai aturan "jangan mengubah kode aplikasi tanpa persetujuan" - perlu keputusan pemilik produk untuk menambahkan pengecekan status saat login. |
| BB-11-02 | Lolos | `BB-11-02.png` | Akun pelanggan sekali pakai berhasil dihapus dari daftar pengguna. |
| BB-12-01 | Lolos | `BB-12-01.png` | Akun staff baru (blackbox.staff.1791376514080@example.com) tersimpan dan tampil pada daftar staff. |
| BB-12-02 | Lolos | `BB-12-02-a.png`, `BB-12-02-b.png` | Akun staff berhasil dinonaktifkan lalu dihapus dari daftar. |
| BB-12-03 | Lolos | `BB-12-03-a.png`, `BB-12-03-b.png` | Perubahan centang permission "Hapus data mobil" untuk role staff berhasil disimpan (lalu dikembalikan ke kondisi semula). |
| BB-13-01 | Lolos | `BB-13-01.png` | Perubahan nomor rekening bank tersimpan (nilai semula dikembalikan setelah pengecekan). |
| BB-13-02 | GAGAL | `BB-13-02-a.png`, `BB-13-02-b.png` | GAGAL (temuan bug nyata, bukan masalah skrip uji): judul hero berhasil tersimpan di halaman Pengaturan (muncul notifikasi sukses), tetapi perubahan itu TIDAK tampil sama sekali di halaman Beranda publik. Penyebab: HomeController@index (app/Http/Controllers/HomeController.php) tidak pernah mengambil/mengirim setting "homepage_hero_title" atau "homepage_hero_subtitle" ke halaman Home, dan resources/js/pages/Home.vue menampilkan judul hero sebagai teks statis ("Jelajahi Batam, Tanpa Ribet") yang di-hardcode, bukan dari data pengaturan. Field pengaturan ini saat ini tidak berfungsi sama sekali bagi pengunjung. Tidak diperbaiki di sini sesuai aturan "jangan mengubah kode aplikasi tanpa persetujuan" - perlu keputusan pemilik produk apakah homepage perlu dihubungkan ke setting ini atau field ini sebaiknya dihapus dari halaman Pengaturan. |
| BB-14-01 | Lolos | `BB-14-01.png` | Laporan menampilkan data sesuai rentang tanggal 2026-09-10 s.d. 2026-10-10. |
| BB-14-02 | Lolos | `BB-14-02.png` | Berkas CSV berhasil terunduh: laporan-pendapatan.csv. |
| BB-15-01 | Lolos | `BB-15-01.png` | Registrasi dengan data valid berhasil, akun otomatis masuk. Catatan: redirect sesungguhnya menuju Dashboard Pelanggan (/dashboard), bukan Beranda seperti disebut pada dokumen test case awal - deskripsi skenario sudah diperbarui agar sesuai perilaku nyata aplikasi (lihat juga activity-flow.md modul 1.1). |
| BB-15-02 | Lolos | `BB-15-02.png` | Pesan validasi email sudah terdaftar tampil. Catatan: proyek belum punya berkas bahasa Indonesia untuk pesan validasi Laravel bawaan, sehingga teksnya berbahasa Inggris ("The email has already been taken.") meskipun APP_LOCALE=id. |
| BB-15-03 | Lolos | `BB-15-03.png` | Field email dikosongkan, dicegah lewat validasi bawaan browser (atribut "required"). Pesan browser: "Please fill out this field.". |
| BB-16-01 | Lolos | `BB-16-01.png` | Login pelanggan valid berhasil. Catatan: redirect sesungguhnya menuju Dashboard Pelanggan (/dashboard), bukan Beranda seperti disebut pada dokumen test case awal - deskripsi skenario sudah diperbarui agar sesuai perilaku nyata aplikasi. |
| BB-16-02 | Lolos | `BB-16-02.png` | Pesan error "Email atau kata sandi salah." tampil untuk kredensial yang salah. |
| BB-17-01 | Lolos | `BB-17-01.png` | Notifikasi pengiriman link reset tampil. MAIL_MAILER=log sehingga isi email tercatat di storage/logs/laravel.log, bukan terkirim sungguhan. |
| BB-17-02 | Lolos | `BB-17-02.png` | Kata sandi berhasil direset lewat tautan email dan diarahkan ke halaman Login. Kata sandi akun uji dikembalikan ke nilai semula setelah pengecekan agar tidak mengganggu skenario lain. |
| BB-17-03 | Lolos | `BB-17-03.png` | Pesan error "Email tidak ditemukan." tampil untuk email yang tidak terdaftar. |
| BB-18-01 | Lolos | `BB-18-01-a.png`, `BB-18-01-b.png` | Katalog mobil dan paket wisata tampil tanpa login, masing-masing menampilkan item aktif. |
| BB-18-02 | Lolos | `BB-18-02.png` | Filter kategori "MPV" pada katalog mobil menampilkan hasil yang sesuai (mis. Toyota Avanza). |
| BB-18-03 | Lolos | `BB-18-03.png` | Detail mobil menampilkan spesifikasi lengkap beserta bagian Ulasan Pelanggan. |
| BB-19-01 | Lolos | `BB-19-01.png` | Jadwal sewa tersimpan di wizard, sistem menghitung durasi & subtotal, lanjut ke Langkah 2. |
| BB-19-02 | Lolos | `BB-19-02-a.png`, `BB-19-02-b.png` | Memilih "Dengan Supir" menampilkan biaya tambahan supir dan form alamat penjemputan yang tidak muncul pada pilihan "Lepas Kunci". |
| BB-19-03 | Lolos | `BB-19-03-a.png`, `BB-19-03-b.png` | Pesanan WRC-261007-ZHBPU berhasil dibuat dan diarahkan ke halaman pembayaran. |
| BB-19-04 | GAGAL | `BB-19-04.png` | GAGAL (temuan bug nyata, bukan masalah skrip uji): pesanan kedua tetap berhasil dibuat meskipun tanggal & mobilnya persis bentrok dengan booking pelanggan lain. Penyebab: Vehicle::isAvailableFor() di app/Models/Vehicle.php baris 84 hanya menganggap status "menunggu_verifikasi", "dikonfirmasi", dan "berlangsung" sebagai penahan tanggal - booking berstatus "menunggu_pembayaran" (yaitu, belum ada bukti transfer sama sekali) tidak ikut dihitung. Akibatnya dua pelanggan berbeda bisa sama-sama mendapat pesanan "menunggu_pembayaran" untuk mobil & tanggal yang identik, dan berpotensi lolos sampai tahap verifikasi admin tanpa ada pengecekan silang. Ini murni temuan analisis blackbox - tidak diperbaiki di sini sesuai aturan "jangan mengubah kode aplikasi tanpa persetujuan"; perlu keputusan pemilik produk apakah status menunggu_pembayaran juga harus ikut memblokir tanggal. |
| BB-19-05 | Lolos | `BB-19-05-a.png`, `BB-19-05-b.png` | Skenario baru: tombol Lanjut nonaktif sebelum KTP diunggah, aktif kembali setelah foto KTP dipilih. |
| BB-19-06 | Lolos | `BB-19-06.png` | Skenario baru: pelanggan yang masih punya sewa mobil aktif diarahkan kembali ke booking lamanya beserta pesan peringatan, tidak diizinkan membuka wizard mobil lain. |
| BB-20-01 | Lolos | `BB-20-01.png` | Jadwal keberangkatan dan alamat penjemputan tersimpan, sistem menampilkan ringkasan harga tetap paket. |
| BB-20-02 | Lolos | `BB-20-02-a.png`, `BB-20-02-b.png` | Pesanan paket wisata WRC-261007-CRULX berhasil dibuat dan diarahkan ke halaman pembayaran. |
| BB-20-03 | Lolos | `BB-20-03.png` | Skenario baru: pelanggan yang sudah memiliki pesanan paket wisata aktif mendapat dialog konfirmasi sebelum melanjutkan memesan paket lain. Memilih Batal membatalkan niat pesan dan kembali ke halaman detail paket. |
| BB-21-01 | Lolos | `BB-21-01-a.png`, `BB-21-01-b.png` | Bukti transfer valid tersimpan, status booking berubah menjadi Menunggu Verifikasi. |
| BB-21-02 | Lolos | `BB-21-02.png` | Berkas .txt ditolak validasi format, status booking tetap Menunggu Pembayaran. |
| BB-21-03 | Lolos | `BB-21-03.png` | Skenario baru: banner hitung mundur 1 jam beserta peringatan batas waktu tampil pada booking yang masih menunggu pembayaran. |
| BB-21-04 | Lolos | `BB-21-04.png` | Booking WRC-261007-67IJN otomatis berubah status menjadi Dibatalkan setelah batas waktu 1 jam pembayaran terlewati (disimulasikan lewat data uji, bukan menunggu 1 jam sungguhan). |
| BB-22-01 | Lolos | `BB-22-01.png` | Riwayat booking menampilkan seluruh pesanan pelanggan beserta status masing-masing. |
| BB-22-02 | Lolos | `BB-22-02.png` | Booking paket wisata WRC-261007-CRULX yang masih Menunggu Pembayaran berhasil dibatalkan pelanggan. |
| BB-22-03 | Lolos | `BB-22-03.png` | Booking WRC-261007-ZHBPU berstatus Selesai tidak lagi menampilkan tombol Batalkan Booking, sesuai aturan hanya booking Menunggu Pembayaran/Menunggu Verifikasi yang bisa dibatalkan. |
| BB-23-01 | Lolos | `BB-23-01-a.png`, `BB-23-01-b.png` | Ulasan untuk booking WRC-261007-ZHBPU berhasil dikirim beserta pesan terima kasih. |
| BB-23-02 | Lolos | `BB-23-02.png` | Pesan validasi rating wajib diisi tampil. Catatan teknis: komponen bintang di UI tidak memungkinkan rating kosong lewat klik biasa (minimal 1), sehingga pengiriman rating kosong disimulasikan lewat penyadapan request (page.route) ke nilai asli form, bukan dengan mengubah kode aplikasi. |
| BB-24-01 | Lolos | `BB-24-01.png` | Perubahan nomor telepon berhasil tersimpan (nilai semula dikembalikan setelah pengecekan). |
| BB-24-02 | Lolos | `BB-24-02.png` | Kata sandi berhasil diubah (dikembalikan ke kata sandi semula setelah pengecekan agar tidak mengganggu skenario lain). |
| BB-24-03 | Lolos | `BB-24-03.png` | Pesan error konfirmasi kata sandi tidak cocok tampil, kata sandi tidak jadi diubah. |
| BB-25-01 | Lolos | `BB-25-01.png` | Daftar destinasi wisata tampil tanpa login. |
| BB-25-02 | Lolos | `BB-25-02.png` | Detail destinasi menampilkan deskripsi beserta paket wisata terkait. |
| BB-26-01 | Lolos | `BB-26-01.png` | Skenario baru: ikon lonceng notifikasi menampilkan badge jumlah belum dibaca dan dropdown berisi riwayat notifikasi (perubahan status booking, pengingat masa sewa, dsb). |
| BB-26-02 | Lolos | `BB-26-02.png` | Skenario baru: setelah menekan "Tandai semua dibaca", badge jumlah notifikasi belum dibaca hilang dari ikon lonceng. |
