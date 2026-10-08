import { expect, test } from '@playwright/test';
import { recordResult } from '../helpers/report';
import { shoot } from '../helpers/shoot';
import { loadState } from '../helpers/state';
import { confirmDialog } from '../helpers/ui';
import { CUSTOMER_STATE } from '../helpers/global-setup';

test.use({ storageState: CUSTOMER_STATE });

// Tabel 5.22 — Pengujian Riwayat & Kelola Booking

test('BB-22-01 Melihat riwayat booking', async ({ page }) => {
  await page.goto('/booking', { waitUntil: 'networkidle' });
  await expect(page.getByText('Selesai', { exact: true })).toBeVisible();
  const file = await shoot(page, 'BB-22-01', { fullPage: true });

  recordResult({
    id: 'BB-22-01',
    status: 'lolos',
    files: [file],
    catatan: 'Riwayat booking menampilkan seluruh pesanan pelanggan beserta status masing-masing.',
  });
});

test('BB-22-02 Membatalkan booking', async ({ page }) => {
  const packageBooking = loadState<{ code: string; url: string }>('packageBooking');
  await page.goto(packageBooking.url, { waitUntil: 'networkidle' });
  await page.getByRole('button', { name: 'Batalkan Booking', exact: true }).click();
  await confirmDialog(page, 'Batalkan');
  await expect(page.getByText('Booking berhasil dibatalkan.')).toBeVisible();
  await expect(page.locator('span.rounded-full', { hasText: 'Dibatalkan' })).toBeVisible();
  const file = await shoot(page, 'BB-22-02', { fullPage: true });

  recordResult({
    id: 'BB-22-02',
    status: 'lolos',
    files: [file],
    catatan: `Booking paket wisata ${packageBooking.code} yang masih Menunggu Pembayaran berhasil dibatalkan pelanggan.`,
  });
});

test('BB-22-03 Membatalkan booking yang tidak dapat dibatalkan', async ({ page }) => {
  const carBooking = loadState<{ code: string; url: string }>('carBooking');
  await page.goto(carBooking.url, { waitUntil: 'networkidle' });
  await expect(page.locator('span.rounded-full', { hasText: 'Selesai' })).toBeVisible();
  await expect(page.getByRole('button', { name: 'Batalkan Booking', exact: true })).toHaveCount(0);
  const file = await shoot(page, 'BB-22-03', { fullPage: true });

  recordResult({
    id: 'BB-22-03',
    status: 'lolos',
    files: [file],
    catatan: `Booking ${carBooking.code} berstatus Selesai tidak lagi menampilkan tombol Batalkan Booking, sesuai aturan hanya booking Menunggu Pembayaran/Menunggu Verifikasi yang bisa dibatalkan.`,
  });
});
