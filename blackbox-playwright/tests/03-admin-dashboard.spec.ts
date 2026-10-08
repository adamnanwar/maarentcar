import { expect, test } from '@playwright/test';
import { recordResult } from '../helpers/report';
import { shoot } from '../helpers/shoot';
import { ADMIN_STATE } from '../helpers/global-setup';

test.use({ storageState: ADMIN_STATE });

// Tabel 5.3 — Pengujian Dashboard Admin

test('BB-03-01 Menampilkan ringkasan data', async ({ page }) => {
  await page.goto('/admin', { waitUntil: 'networkidle' });
  const main = page.getByRole('main');
  await expect(main.getByText('Total Mobil')).toBeVisible();
  await expect(main.getByText('Mobil Tersedia')).toBeVisible();
  await expect(main.getByText('Destinasi', { exact: true })).toBeVisible();
  await expect(main.getByText('Paket Wisata', { exact: true })).toBeVisible();
  await expect(main.getByText('Menunggu Verifikasi')).toBeVisible();
  await expect(main.getByText('Sedang Berlangsung')).toBeVisible();
  const file = await shoot(page, 'BB-03-01', { fullPage: true });

  recordResult({
    id: 'BB-03-01',
    status: 'lolos',
    files: [file],
    catatan: 'Dashboard admin menampilkan seluruh kartu ringkasan (mobil, destinasi, paket, status booking).',
  });
});
