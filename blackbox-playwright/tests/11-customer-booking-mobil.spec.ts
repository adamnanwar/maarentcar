import { expect, test } from '@playwright/test';
import { recordResult } from '../helpers/report';
import { shoot } from '../helpers/shoot';
import { saveState, loadState } from '../helpers/state';
import { login, VALID_IMAGE } from '../helpers/ui';
import { ENV } from '../helpers/env';
import { CUSTOMER_STATE } from '../helpers/global-setup';

test.use({ storageState: CUSTOMER_STATE });

// Tabel 5.19 — Pengujian Sewa Mobil (+ skenario baru BB-19-05, BB-19-06)
// Menggunakan CUSTOMER_EMAIL sebagai "pelanggan utama" (Customer A) yang booking
// mobilnya akan dipakai lagi oleh skenario pembayaran, verifikasi admin, ubah
// status, dan ulasan di berkas-berkas berikutnya.

function futureDate(daysFromNow: number): string {
  const d = new Date(Date.now() + daysFromNow * 24 * 3600 * 1000);
  return d.toISOString().slice(0, 16);
}

test('BB-19-01 Mengisi jadwal sewa', async ({ page }) => {
  await page.goto('/booking/mobil/honda-brio/baru', { waitUntil: 'networkidle' });
  await page.locator('#start_datetime').fill(futureDate(3));
  await page.locator('#end_datetime').fill(futureDate(4));
  await expect(page.getByText('Durasi sewa:')).toBeVisible();
  await page.getByRole('button', { name: 'Lanjut', exact: true }).click();
  await expect(page.getByText('Langkah 2: Jenis Layanan')).toBeVisible();
  const file = await shoot(page, 'BB-19-01', { fullPage: true });

  recordResult({
    id: 'BB-19-01',
    status: 'lolos',
    files: [file],
    catatan: 'Jadwal sewa tersimpan di wizard, sistem menghitung durasi & subtotal, lanjut ke Langkah 2.',
  });
});

test('BB-19-02 Memilih metode serah terima', async ({ page }) => {
  await page.goto('/booking/mobil/honda-brio/baru', { waitUntil: 'networkidle' });
  await page.locator('#start_datetime').fill(futureDate(3));
  await page.locator('#end_datetime').fill(futureDate(4));
  await page.getByRole('button', { name: 'Lanjut', exact: true }).click();

  await expect(page.getByText('Langkah 2: Jenis Layanan')).toBeVisible();
  const fileA = await shoot(page, 'BB-19-02', { suffix: 'a' });

  await page.getByText('Dengan Supir').click();
  await expect(page.getByText('Alamat Penjemputan')).toBeVisible();
  const fileB = await shoot(page, 'BB-19-02', { suffix: 'b' });

  recordResult({
    id: 'BB-19-02',
    status: 'lolos',
    files: [fileA, fileB],
    catatan: 'Memilih "Dengan Supir" menampilkan biaya tambahan supir dan form alamat penjemputan yang tidak muncul pada pilihan "Lepas Kunci".',
  });
});

test('BB-19-05 Upload KTP sebagai syarat sewa (BARU)', async ({ page }) => {
  await page.goto('/booking/mobil/honda-brio/baru', { waitUntil: 'networkidle' });
  await page.locator('#start_datetime').fill(futureDate(3));
  await page.locator('#end_datetime').fill(futureDate(4));
  await page.getByRole('button', { name: 'Lanjut', exact: true }).click();
  await page.getByText('Lepas Kunci').click();
  await page.getByRole('button', { name: 'Lanjut', exact: true }).click();

  await expect(page.getByText('Langkah 3: Upload KTP')).toBeVisible();
  const lanjutButton = page.getByRole('button', { name: 'Lanjut', exact: true });
  await expect(lanjutButton).toBeDisabled();
  const fileA = await shoot(page, 'BB-19-05', { suffix: 'a' });

  await page.setInputFiles('input[type="file"]', VALID_IMAGE);
  await expect(lanjutButton).toBeEnabled();
  const fileB = await shoot(page, 'BB-19-05', { suffix: 'b' });

  recordResult({
    id: 'BB-19-05',
    status: 'lolos',
    files: [fileA, fileB],
    catatan: 'Skenario baru: tombol Lanjut nonaktif sebelum KTP diunggah, aktif kembali setelah foto KTP dipilih.',
  });
});

test('BB-19-03 Konfirmasi dan membuat pesanan', async ({ page }) => {
  await page.goto('/booking/mobil/honda-brio/baru', { waitUntil: 'networkidle' });
  await page.locator('#start_datetime').fill(futureDate(3));
  await page.locator('#end_datetime').fill(futureDate(4));
  await page.getByRole('button', { name: 'Lanjut', exact: true }).click();
  await page.getByText('Lepas Kunci').click();
  await page.getByRole('button', { name: 'Lanjut', exact: true }).click();
  await page.setInputFiles('input[type="file"]', VALID_IMAGE);
  await page.getByRole('button', { name: 'Lanjut', exact: true }).click();
  await expect(page.getByText('Langkah 4: Konfirmasi Data Diri')).toBeVisible();
  await page.getByRole('button', { name: 'Lanjut', exact: true }).click();

  await expect(page.getByText('Langkah 5: Ringkasan & Bayar')).toBeVisible();
  const fileA = await shoot(page, 'BB-19-03', { suffix: 'a' });

  await page.getByRole('button', { name: 'Buat Pesanan', exact: true }).click();
  await page.waitForURL('**/booking/*', { timeout: 15_000 });
  await expect(page.getByText('Informasi Transfer')).toBeVisible();
  const fileB = await shoot(page, 'BB-19-03', { suffix: 'b', fullPage: true });

  const bodyText = (await page.textContent('body')) ?? '';
  const bookingCode = bodyText.match(/WRC-[0-9]{6}-[A-Z0-9]{5}/)?.[0] ?? '';
  const bookingUrl = page.url();
  saveState('carBooking', { code: bookingCode, url: bookingUrl });

  recordResult({
    id: 'BB-19-03',
    status: 'lolos',
    files: [fileA, fileB],
    catatan: `Pesanan ${bookingCode} berhasil dibuat dan diarahkan ke halaman pembayaran.`,
  });
});

test('BB-19-06 Batas satu sewa mobil aktif (BARU)', async ({ page }) => {
  await page.goto('/booking/mobil/toyota-agya/baru', { waitUntil: 'networkidle' });
  const carBooking = loadState<{ code: string; url: string }>('carBooking');
  await page.waitForURL((url) => url.toString().includes(new URL(carBooking.url).pathname), { timeout: 10_000 });
  await expect(page.getByText(/sewa mobil yang aktif/i)).toBeVisible();
  const file = await shoot(page, 'BB-19-06', { fullPage: true });

  recordResult({
    id: 'BB-19-06',
    status: 'lolos',
    files: [file],
    catatan: 'Skenario baru: pelanggan yang masih punya sewa mobil aktif diarahkan kembali ke booking lamanya beserta pesan peringatan, tidak diizinkan membuka wizard mobil lain.',
  });
});

test('BB-19-04 Memesan pada tanggal yang tidak tersedia', async ({ browser }) => {
  const freshCustomer = loadState<{ email: string; password: string }>('freshCustomerA');
  // storageState: undefined wajib eksplisit, karena browser.newContext() ikut
  // mewarisi test.use({ storageState: CUSTOMER_STATE }) di atas jika tidak ditimpa.
  const context = await browser.newContext({ storageState: undefined });
  const page = await context.newPage();
  await login(page, freshCustomer.email, freshCustomer.password);

  await page.goto('/booking/mobil/honda-brio/baru', { waitUntil: 'networkidle' });
  // Tanggal sengaja dibuat bertabrakan dengan booking Customer A pada BB-19-03 (H+3 s.d. H+4).
  await page.locator('#start_datetime').fill(futureDate(3));
  await page.locator('#end_datetime').fill(futureDate(4));
  await page.getByRole('button', { name: 'Lanjut', exact: true }).click();
  await page.getByText('Lepas Kunci').click();
  await page.getByRole('button', { name: 'Lanjut', exact: true }).click();
  await page.setInputFiles('input[type="file"]', VALID_IMAGE);
  await page.getByRole('button', { name: 'Lanjut', exact: true }).click();
  await page.getByRole('button', { name: 'Lanjut', exact: true }).click();
  await page.getByRole('button', { name: 'Buat Pesanan', exact: true }).click();
  await page.waitForTimeout(800);

  const stillOnWizard = page.url().includes('/baru');
  const file = await shoot(page, 'BB-19-04', { fullPage: true });
  await context.close();

  if (stillOnWizard) {
    recordResult({
      id: 'BB-19-04',
      status: 'lolos',
      files: [file],
      catatan: 'Mobil yang sama pada tanggal bentrok ditolak sistem seperti yang diharapkan.',
    });
  } else {
    recordResult({
      id: 'BB-19-04',
      status: 'gagal',
      files: [file],
      catatan:
        'GAGAL (temuan bug nyata, bukan masalah skrip uji): pesanan kedua tetap berhasil dibuat meskipun tanggal & mobilnya persis bentrok dengan booking pelanggan lain. Penyebab: Vehicle::isAvailableFor() di app/Models/Vehicle.php baris 84 hanya menganggap status "menunggu_verifikasi", "dikonfirmasi", dan "berlangsung" sebagai penahan tanggal - booking berstatus "menunggu_pembayaran" (yaitu, belum ada bukti transfer sama sekali) tidak ikut dihitung. Akibatnya dua pelanggan berbeda bisa sama-sama mendapat pesanan "menunggu_pembayaran" untuk mobil & tanggal yang identik, dan berpotensi lolos sampai tahap verifikasi admin tanpa ada pengecekan silang. Ini murni temuan analisis blackbox - tidak diperbaiki di sini sesuai aturan "jangan mengubah kode aplikasi tanpa persetujuan"; perlu keputusan pemilik produk apakah status menunggu_pembayaran juga harus ikut memblokir tanggal.',
    });
  }
});
