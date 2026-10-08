import { expect, test } from '@playwright/test';
import { recordResult } from '../helpers/report';
import { shoot } from '../helpers/shoot';
import { confirmDialog, uniqueSuffix, VALID_IMAGE } from '../helpers/ui';
import { ADMIN_STATE } from '../helpers/global-setup';

test.use({ storageState: ADMIN_STATE });

// Tabel 5.7 — Pengujian Kelola Paket Wisata

const pkgName = `Paket Uji Blackbox ${uniqueSuffix()}`;
const pkgNameEdited = `${pkgName} Diubah`;

test('BB-07-01 Tambah paket wisata', async ({ page }) => {
  await page.goto('/admin/paket-wisata/create', { waitUntil: 'networkidle' });
  await page.locator('#name').fill(pkgName);
  await page.locator('#vehicle_id').selectOption({ label: 'Toyota Avanza' });
  await page.locator('#seat_capacity').fill('6');
  await page.locator('#duration_days').fill('1');
  await page.locator('#price').fill('975000');
  await page.locator('#description').fill('Paket wisata dibuat otomatis oleh suite blackbox Playwright.');
  await page.getByRole('checkbox', { name: 'Pantai Sekilak' }).check();
  await page.setInputFiles('input[type="file"]', VALID_IMAGE);
  const fileA = await shoot(page, 'BB-07-01', { suffix: 'a' });

  await page.locator('button[type="submit"]').click();
  await page.waitForURL('**/admin/paket-wisata', { timeout: 10_000 });
  await expect(page.getByText('Paket wisata berhasil ditambahkan.')).toBeVisible();
  await expect(page.getByRole('row', { name: new RegExp(pkgName) })).toBeVisible();

  await page.goto('/paket-wisata', { waitUntil: 'networkidle' });
  await expect(page.getByText(pkgName)).toBeVisible();
  const fileB = await shoot(page, 'BB-07-01', { suffix: 'b', fullPage: true });

  recordResult({
    id: 'BB-07-01',
    status: 'lolos',
    files: [fileA, fileB],
    catatan: 'Paket wisata baru tersimpan, tampil pada daftar admin dan katalog publik.',
  });
});

test('BB-07-02 Ubah paket wisata', async ({ page }) => {
  await page.goto('/admin/paket-wisata', { waitUntil: 'networkidle' });
  const row = page.getByRole('row', { name: new RegExp(pkgName) });
  await row.getByRole('button').first().click();
  await page.waitForURL('**/paket-wisata/*/edit', { timeout: 10_000 });

  await page.locator('#name').fill(pkgNameEdited);
  await page.locator('button[type="submit"]').click();
  await page.waitForURL('**/admin/paket-wisata', { timeout: 10_000 });
  await expect(page.getByText('Paket wisata berhasil diperbarui.')).toBeVisible();
  await expect(page.getByRole('row', { name: new RegExp(pkgNameEdited) })).toBeVisible();
  const file = await shoot(page, 'BB-07-02', { fullPage: true });

  recordResult({
    id: 'BB-07-02',
    status: 'lolos',
    files: [file],
    catatan: 'Perubahan nama paket wisata tersimpan dan tampil pada daftar.',
  });
});

test('BB-07-03 Hapus paket wisata', async ({ page }) => {
  await page.goto('/admin/paket-wisata', { waitUntil: 'networkidle' });
  const row = page.getByRole('row', { name: new RegExp(pkgNameEdited) });
  await row.getByRole('button').last().click();
  await confirmDialog(page, 'Hapus');
  await expect(page.getByText('Paket wisata berhasil dihapus.')).toBeVisible();
  await expect(page.getByRole('row', { name: new RegExp(pkgNameEdited) })).toHaveCount(0);
  const file = await shoot(page, 'BB-07-03', { fullPage: true });

  recordResult({
    id: 'BB-07-03',
    status: 'lolos',
    files: [file],
    catatan: 'Paket wisata uji berhasil dihapus dari daftar.',
  });
});
