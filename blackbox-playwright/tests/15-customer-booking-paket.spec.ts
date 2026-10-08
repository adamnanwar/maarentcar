import { expect, test } from '@playwright/test';
import { recordResult } from '../helpers/report';
import { shoot } from '../helpers/shoot';
import { saveState } from '../helpers/state';
import { CUSTOMER_STATE } from '../helpers/global-setup';

test.use({ storageState: CUSTOMER_STATE });

// Tabel 5.20 — Pengujian Sewa Paket Wisata (+ skenario baru BB-20-03)

function futureDateOnly(daysFromNow: number): string {
  return new Date(Date.now() + daysFromNow * 24 * 3600 * 1000).toISOString().slice(0, 10);
}

test('BB-20-01 Mengisi jadwal dan alamat penjemputan', async ({ page }) => {
  await page.goto('/booking/paket-wisata/batam-highlight-1-hari/baru', { waitUntil: 'networkidle' });
  await page.locator('#start_datetime').fill(futureDateOnly(7));
  await page.locator('#passenger_count').fill('4');
  await page.getByRole('button', { name: 'Lanjut', exact: true }).click();

  await expect(page.getByText('Langkah 2: Alamat Penjemputan')).toBeVisible();
  await page.locator('#full_address').fill('Jl. Uji Blackbox No. 10, Batam Centre');
  await page.getByRole('button', { name: 'Lanjut', exact: true }).click();
  await expect(page.getByText('Langkah 3: Catatan Tambahan')).toBeVisible();
  await page.getByRole('button', { name: 'Lanjut', exact: true }).click();

  await expect(page.getByText('Langkah 4: Ringkasan & Bayar')).toBeVisible();
  const file = await shoot(page, 'BB-20-01', { fullPage: true });

  recordResult({
    id: 'BB-20-01',
    status: 'lolos',
    files: [file],
    catatan: 'Jadwal keberangkatan dan alamat penjemputan tersimpan, sistem menampilkan ringkasan harga tetap paket.',
  });
});

test('BB-20-02 Konfirmasi dan membuat pesanan', async ({ page }) => {
  await page.goto('/booking/paket-wisata/batam-highlight-1-hari/baru', { waitUntil: 'networkidle' });
  await page.locator('#start_datetime').fill(futureDateOnly(7));
  await page.locator('#passenger_count').fill('4');
  await page.getByRole('button', { name: 'Lanjut', exact: true }).click();
  await page.locator('#full_address').fill('Jl. Uji Blackbox No. 10, Batam Centre');
  await page.getByRole('button', { name: 'Lanjut', exact: true }).click();
  await page.getByRole('button', { name: 'Lanjut', exact: true }).click();

  await expect(page.getByText('Langkah 4: Ringkasan & Bayar')).toBeVisible();
  const fileA = await shoot(page, 'BB-20-02', { suffix: 'a' });

  await page.getByRole('button', { name: 'Buat Pesanan', exact: true }).click();
  await page.waitForURL('**/booking/*', { timeout: 15_000 });
  await expect(page.getByText('Informasi Transfer')).toBeVisible();
  const fileB = await shoot(page, 'BB-20-02', { suffix: 'b', fullPage: true });

  const bodyText = (await page.textContent('body')) ?? '';
  const bookingCode = bodyText.match(/WRC-[0-9]{6}-[A-Z0-9]{5}/)?.[0] ?? '';
  saveState('packageBooking', { code: bookingCode, url: page.url() });

  recordResult({
    id: 'BB-20-02',
    status: 'lolos',
    files: [fileA, fileB],
    catatan: `Pesanan paket wisata ${bookingCode} berhasil dibuat dan diarahkan ke halaman pembayaran.`,
  });
});

test('BB-20-03 Peringatan sudah punya pesanan paket wisata aktif (BARU)', async ({ page }) => {
  await page.goto('/booking/paket-wisata/batam-kuliner-pantai-1-hari/baru', { waitUntil: 'networkidle' });
  const dialog = page.getByRole('dialog');
  await expect(dialog).toBeVisible();
  await expect(dialog.getByText(/sudah melakukan pemesanan paket wisata/i)).toBeVisible();
  const file = await shoot(page, 'BB-20-03');

  await dialog.getByRole('button', { name: 'Batal', exact: true }).click();
  await page.waitForURL('**/paket-wisata/batam-kuliner-pantai-1-hari', { timeout: 10_000 });

  recordResult({
    id: 'BB-20-03',
    status: 'lolos',
    files: [file],
    catatan: 'Skenario baru: pelanggan yang sudah memiliki pesanan paket wisata aktif mendapat dialog konfirmasi sebelum melanjutkan memesan paket lain. Memilih Batal membatalkan niat pesan dan kembali ke halaman detail paket.',
  });
});
