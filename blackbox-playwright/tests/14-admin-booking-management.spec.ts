import { expect, test } from '@playwright/test';
import { recordResult } from '../helpers/report';
import { shoot } from '../helpers/shoot';
import { loadState } from '../helpers/state';
import { ADMIN_STATE } from '../helpers/global-setup';

test.use({ storageState: ADMIN_STATE });

// Tabel 5.9 — Pengujian Kelola Booking (+ skenario baru BB-09-03)

test('BB-09-01 Mengubah status booking', async ({ page }) => {
  const carBooking = loadState<{ code: string; url: string }>('carBooking');
  const bookingPath = new URL(carBooking.url).pathname;
  const adminDetailUrl = bookingPath.replace('/booking/', '/admin/booking/');

  await page.goto(adminDetailUrl, { waitUntil: 'networkidle' });
  await page.locator('select').selectOption({ label: 'Sedang Berlangsung' });
  await page.locator('button[type="submit"]').click();
  await expect(page.getByText('Status booking berhasil diperbarui.')).toBeVisible();
  const file = await shoot(page, 'BB-09-01', { fullPage: true });

  // Lanjutkan ke "Selesai" (tanpa difoto tersendiri) supaya booking ini bisa
  // dipakai untuk skenario menulis ulasan (Tabel 5.23) di berkas berikutnya.
  await page.locator('select').selectOption({ label: 'Selesai' });
  await page.locator('button[type="submit"]').click();
  await expect(page.getByText('Status booking berhasil diperbarui.')).toBeVisible();

  recordResult({
    id: 'BB-09-01',
    status: 'lolos',
    files: [file],
    catatan: `Status booking ${carBooking.code} berhasil diubah dari Dikonfirmasi menjadi Sedang Berlangsung sesuai alur yang diizinkan.`,
  });
});

test('BB-09-03 Melihat KTP jaminan pelanggan (BARU)', async ({ page }) => {
  const carBooking = loadState<{ code: string; url: string }>('carBooking');
  const bookingPath = new URL(carBooking.url).pathname;
  const adminDetailUrl = bookingPath.replace('/booking/', '/admin/booking/');

  await page.goto(adminDetailUrl, { waitUntil: 'networkidle' });
  await expect(page.getByText('KTP Jaminan')).toBeVisible();
  await expect(page.getByRole('link', { name: 'Lihat KTP' })).toBeVisible();
  const file = await shoot(page, 'BB-09-03', { fullPage: true });

  recordResult({
    id: 'BB-09-03',
    status: 'lolos',
    files: [file],
    catatan: 'Skenario baru: admin dapat melihat foto KTP yang diunggah pelanggan sebagai jaminan langsung dari halaman detail booking.',
  });
});

test('BB-09-02 Mencari/memfilter booking', async ({ page }) => {
  const carBooking = loadState<{ code: string; url: string }>('carBooking');

  await page.goto('/admin/booking', { waitUntil: 'networkidle' });
  await page.getByPlaceholder('Cari kode booking / nama pelanggan...').fill(carBooking.code);
  await page.waitForTimeout(600);
  await expect(page.getByText(carBooking.code)).toBeVisible();
  const fileA = await shoot(page, 'BB-09-02', { suffix: 'a', fullPage: true });

  await page.getByPlaceholder('Cari kode booking / nama pelanggan...').fill('');
  await page.locator('select').first().selectOption({ label: 'Selesai' });
  await page.waitForTimeout(600);
  await expect(page.getByText(carBooking.code)).toBeVisible();
  const fileB = await shoot(page, 'BB-09-02', { suffix: 'b', fullPage: true });

  recordResult({
    id: 'BB-09-02',
    status: 'lolos',
    files: [fileA, fileB],
    catatan: 'Pencarian berdasarkan kode booking dan filter status keduanya menampilkan hasil yang sesuai.',
  });
});
