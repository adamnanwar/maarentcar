import { expect, test } from '@playwright/test';
import { recordResult } from '../helpers/report';
import { shoot } from '../helpers/shoot';

// Tabel 5.18 — Lihat Katalog Mobil & Paket Wisata (Publik / Pelanggan)
// Tabel 5.25 — Lihat Destinasi (Publik / Pelanggan)
// Semua skenario di sini tidak butuh login.

test('BB-18-01 Melihat katalog', async ({ page }) => {
  await page.goto('/mobil', { waitUntil: 'networkidle' });
  const mobilHeading = page.getByRole('heading', { name: 'Mobil' }).first();
  await expect(page.locator('a[href^="/mobil/"]').first()).toBeVisible();
  const fileA = await shoot(page, 'BB-18-01', { suffix: 'a', fullPage: true });

  await page.goto('/paket-wisata', { waitUntil: 'networkidle' });
  await expect(page.locator('a[href^="/paket-wisata/"]').first()).toBeVisible();
  const fileB = await shoot(page, 'BB-18-01', { suffix: 'b', fullPage: true });

  recordResult({
    id: 'BB-18-01',
    status: 'lolos',
    files: [fileA, fileB],
    catatan: 'Katalog mobil dan paket wisata tampil tanpa login, masing-masing menampilkan item aktif.',
  });
  void mobilHeading;
});

test('BB-18-02 Memfilter katalog', async ({ page }) => {
  await page.goto('/mobil', { waitUntil: 'networkidle' });
  await page.getByRole('button', { name: 'MPV', exact: true }).click();
  await page.waitForTimeout(600);
  await expect(page.getByText('Toyota Avanza')).toBeVisible();
  const file = await shoot(page, 'BB-18-02', { fullPage: true });

  recordResult({
    id: 'BB-18-02',
    status: 'lolos',
    files: [file],
    catatan: 'Filter kategori "MPV" pada katalog mobil menampilkan hasil yang sesuai (mis. Toyota Avanza).',
  });
});

test('BB-18-03 Melihat detail item', async ({ page }) => {
  await page.goto('/mobil/honda-brio', { waitUntil: 'networkidle' });
  await expect(page.getByRole('heading', { name: 'Honda Brio' })).toBeVisible();
  await expect(page.getByRole('heading', { name: 'Ulasan Pelanggan' })).toBeVisible();
  const file = await shoot(page, 'BB-18-03', { fullPage: true });

  recordResult({
    id: 'BB-18-03',
    status: 'lolos',
    files: [file],
    catatan: 'Detail mobil menampilkan spesifikasi lengkap beserta bagian Ulasan Pelanggan.',
  });
});

test('BB-25-01 Melihat daftar destinasi', async ({ page }) => {
  await page.goto('/destinasi', { waitUntil: 'networkidle' });
  await expect(page.getByText('Pantai Sekilak')).toBeVisible();
  const file = await shoot(page, 'BB-25-01', { fullPage: true });

  recordResult({
    id: 'BB-25-01',
    status: 'lolos',
    files: [file],
    catatan: 'Daftar destinasi wisata tampil tanpa login.',
  });
});

test('BB-25-02 Melihat detail destinasi', async ({ page }) => {
  await page.goto('/destinasi/pantai-sekilak', { waitUntil: 'networkidle' });
  await expect(page.getByRole('heading', { name: 'Pantai Sekilak' })).toBeVisible();
  await expect(page.getByRole('heading', { name: 'Paket Wisata Terkait' })).toBeVisible();
  const file = await shoot(page, 'BB-25-02', { fullPage: true });

  recordResult({
    id: 'BB-25-02',
    status: 'lolos',
    files: [file],
    catatan: 'Detail destinasi menampilkan deskripsi beserta paket wisata terkait.',
  });
});
