import { expect, test } from '@playwright/test';
import { recordResult } from '../helpers/report';
import { shoot } from '../helpers/shoot';
import { loadState } from '../helpers/state';
import { INVALID_FILE, VALID_IMAGE, uniqueEmail } from '../helpers/ui';
import { runTinker } from '../helpers/artisan';
import { CUSTOMER_STATE } from '../helpers/global-setup';

// Tabel 5.21 — Pengujian Upload Bukti Pembayaran (+ skenario baru BB-21-03, BB-21-04)

test.use({ storageState: CUSTOMER_STATE });

test('BB-21-02 Mengunggah berkas tidak valid', async ({ page }) => {
  const carBooking = loadState<{ code: string; url: string }>('carBooking');
  await page.goto(carBooking.url, { waitUntil: 'networkidle' });

  await page.setInputFiles('input[type="file"]', INVALID_FILE);
  await page.getByRole('button', { name: 'Unggah Bukti Transfer', exact: true }).click();
  await page.waitForTimeout(500);

  await expect(page.getByText('Menunggu Pembayaran', { exact: true })).toBeVisible();
  const file = await shoot(page, 'BB-21-02', { fullPage: true });

  recordResult({
    id: 'BB-21-02',
    status: 'lolos',
    files: [file],
    catatan: 'Berkas .txt ditolak validasi format, status booking tetap Menunggu Pembayaran.',
  });
});

test('BB-21-03 Menampilkan hitung mundur batas waktu pembayaran (BARU)', async ({ page }) => {
  const carBooking = loadState<{ code: string; url: string }>('carBooking');
  await page.goto(carBooking.url, { waitUntil: 'networkidle' });
  await expect(page.getByText('Segera selesaikan pembayaran dalam waktu 1 jam')).toBeVisible();
  await expect(page.locator('text=/\\d{2}:\\d{2}/')).toBeVisible();
  const file = await shoot(page, 'BB-21-03', { fullPage: true });

  recordResult({
    id: 'BB-21-03',
    status: 'lolos',
    files: [file],
    catatan: 'Skenario baru: banner hitung mundur 1 jam beserta peringatan batas waktu tampil pada booking yang masih menunggu pembayaran.',
  });
});

test('BB-21-01 Mengunggah bukti transfer valid', async ({ page }) => {
  const carBooking = loadState<{ code: string; url: string }>('carBooking');
  await page.goto(carBooking.url, { waitUntil: 'networkidle' });

  await page.locator('input[placeholder="Nama pengirim (opsional)"]').fill('Budi Pelanggan');
  await page.setInputFiles('input[type="file"]', VALID_IMAGE);
  const fileA = await shoot(page, 'BB-21-01', { suffix: 'a' });

  await page.getByRole('button', { name: 'Unggah Bukti Transfer', exact: true }).click();
  await page.waitForTimeout(800);
  await expect(page.locator('span.rounded-full', { hasText: 'Menunggu Verifikasi' })).toBeVisible();
  const fileB = await shoot(page, 'BB-21-01', { suffix: 'b', fullPage: true });

  recordResult({
    id: 'BB-21-01',
    status: 'lolos',
    files: [fileA, fileB],
    catatan: 'Bukti transfer valid tersimpan, status booking berubah menjadi Menunggu Verifikasi.',
  });
});

test('BB-21-04 Booking kedaluwarsa otomatis dibatalkan (BARU)', async ({ browser }) => {
  // Pelanggan baru khusus skenario ini (bukan freshCustomerA), supaya tidak
  // terganjal aturan "1 booking mobil aktif" oleh booking lain milik freshCustomerA.
  // storageState: undefined wajib eksplisit, karena browser.newContext() ikut
  // mewarisi test.use({ storageState: CUSTOMER_STATE }) di atas jika tidak ditimpa.
  const context = await browser.newContext({ storageState: undefined });
  const page = await context.newPage();
  const email = uniqueEmail('blackbox.expiry');
  await page.goto('/register', { waitUntil: 'networkidle' });
  await page.locator('#name').fill('Pelanggan Uji Kedaluwarsa');
  await page.locator('#email').fill(email);
  await page.locator('#phone').fill('081234500099');
  await page.locator('#password').fill('password123');
  await page.locator('#password_confirmation').fill('password123');
  await page.locator('button[type="submit"]').click();
  await page.waitForURL('**/dashboard', { timeout: 15_000 });

  await page.goto('/booking/mobil/toyota-fortuner/baru', { waitUntil: 'networkidle' });
  const start = new Date(Date.now() + 10 * 24 * 3600 * 1000).toISOString().slice(0, 16);
  const end = new Date(Date.now() + 11 * 24 * 3600 * 1000).toISOString().slice(0, 16);
  await page.locator('#start_datetime').fill(start);
  await page.locator('#end_datetime').fill(end);
  await page.getByRole('button', { name: 'Lanjut', exact: true }).click();
  await page.getByText('Lepas Kunci').click();
  await page.getByRole('button', { name: 'Lanjut', exact: true }).click();
  await page.setInputFiles('input[type="file"]', VALID_IMAGE);
  await page.getByRole('button', { name: 'Lanjut', exact: true }).click();
  await page.getByRole('button', { name: 'Lanjut', exact: true }).click();
  await page.getByRole('button', { name: 'Buat Pesanan', exact: true }).click();
  await page.waitForURL('**/booking/*', { timeout: 15_000 });

  const bodyText = (await page.textContent('body')) ?? '';
  const bookingCode = bodyText.match(/WRC-[0-9]{6}-[A-Z0-9]{5}/)?.[0] ?? '';

  // Data khusus: memundurkan batas waktu pembayaran booking ini ke masa lalu,
  // menggantikan penantian 1 jam sungguhan (lihat helpers/artisan.ts).
  runTinker(
    `$b = App\\Models\\Booking::where('booking_code','${bookingCode}')->first(); $b->payment_due_at = now()->subMinute(); $b->save(); echo 'ok';`,
  );

  await page.reload({ waitUntil: 'networkidle' });
  await expect(page.getByText('Dibatalkan', { exact: true })).toBeVisible();
  const file = await shoot(page, 'BB-21-04', { fullPage: true });
  await context.close();

  recordResult({
    id: 'BB-21-04',
    status: 'lolos',
    files: [file],
    catatan: `Booking ${bookingCode} otomatis berubah status menjadi Dibatalkan setelah batas waktu 1 jam pembayaran terlewati (disimulasikan lewat data uji, bukan menunggu 1 jam sungguhan).`,
  });
});
