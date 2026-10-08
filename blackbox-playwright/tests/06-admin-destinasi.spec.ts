import { expect, test } from '@playwright/test';
import { recordResult } from '../helpers/report';
import { shoot } from '../helpers/shoot';
import { confirmDialog, uniqueSuffix, VALID_IMAGE } from '../helpers/ui';
import { ADMIN_STATE } from '../helpers/global-setup';

test.use({ storageState: ADMIN_STATE });

// Tabel 5.6 — Pengujian Kelola Destinasi

const destName = `Destinasi Uji Blackbox ${uniqueSuffix()}`;
const destNameEdited = `${destName} Diubah`;

test('BB-06-01 Tambah destinasi', async ({ page }) => {
  await page.goto('/admin/destinasi/create', { waitUntil: 'networkidle' });
  await page.locator('#name').fill(destName);
  await page.locator('#category').fill('pantai');
  await page.locator('#addon_price').fill('35000');
  await page.locator('#address').fill('Jl. Uji Blackbox No. 1, Batam');
  await page.locator('#description').fill('Destinasi dibuat otomatis oleh suite blackbox Playwright.');
  await page.setInputFiles('input[type="file"]', VALID_IMAGE);
  const fileA = await shoot(page, 'BB-06-01', { suffix: 'a' });

  await page.locator('button[type="submit"]').click();
  await page.waitForURL('**/admin/destinasi', { timeout: 10_000 });
  await expect(page.getByText('Destinasi berhasil ditambahkan.')).toBeVisible();
  await expect(page.getByRole('row', { name: new RegExp(destName) })).toBeVisible();
  const fileB = await shoot(page, 'BB-06-01', { suffix: 'b', fullPage: true });

  recordResult({
    id: 'BB-06-01',
    status: 'lolos',
    files: [fileA, fileB],
    catatan: 'Destinasi baru tersimpan dan tampil pada daftar.',
  });
});

test('BB-06-02 Ubah destinasi', async ({ page }) => {
  await page.goto('/admin/destinasi', { waitUntil: 'networkidle' });
  const row = page.getByRole('row', { name: new RegExp(destName) });
  await row.getByRole('button').first().click();
  await page.waitForURL('**/destinasi/*/edit', { timeout: 10_000 });

  await page.locator('#name').fill(destNameEdited);
  await page.locator('button[type="submit"]').click();
  await page.waitForURL('**/admin/destinasi', { timeout: 10_000 });
  await expect(page.getByText('Destinasi berhasil diperbarui.')).toBeVisible();
  await expect(page.getByRole('row', { name: new RegExp(destNameEdited) })).toBeVisible();
  const file = await shoot(page, 'BB-06-02', { fullPage: true });

  recordResult({
    id: 'BB-06-02',
    status: 'lolos',
    files: [file],
    catatan: 'Perubahan nama destinasi tersimpan dan tampil pada daftar.',
  });
});

test('BB-06-03 Hapus destinasi', async ({ page }) => {
  await page.goto('/admin/destinasi', { waitUntil: 'networkidle' });
  const row = page.getByRole('row', { name: new RegExp(destNameEdited) });
  await row.getByRole('button').last().click();
  await confirmDialog(page, 'Hapus');
  await expect(page.getByText('Destinasi berhasil dihapus.')).toBeVisible();
  await expect(page.getByRole('row', { name: new RegExp(destNameEdited) })).toHaveCount(0);
  const file = await shoot(page, 'BB-06-03', { fullPage: true });

  recordResult({
    id: 'BB-06-03',
    status: 'lolos',
    files: [file],
    catatan: 'Destinasi uji berhasil dihapus dari daftar.',
  });
});
