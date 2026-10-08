import { expect, test } from '@playwright/test';
import { recordResult } from '../helpers/report';
import { shoot } from '../helpers/shoot';
import { uniqueSuffix } from '../helpers/ui';
import { confirmDialog } from '../helpers/ui';
import { ADMIN_STATE } from '../helpers/global-setup';

test.use({ storageState: ADMIN_STATE });

// Tabel 5.4 — Pengujian Kelola Kategori Mobil
// Urutan eksekusi sengaja disusun ulang (tambah -> ubah -> validasi -> hapus)
// supaya kategori uji tersedia untuk diubah sebelum akhirnya dihapus;
// ID skenario pada judul test tetap mengikuti dokumen asli.

const categoryName = `Kategori Uji Blackbox ${uniqueSuffix()}`;
const categoryNameEdited = `${categoryName} Diubah`;

test('BB-04-01 Tambah kategori', async ({ page }) => {
  await page.goto('/admin/kategori-mobil/create', { waitUntil: 'networkidle' });
  await page.locator('#name').fill(categoryName);
  await page.locator('#description').fill('Kategori dibuat otomatis oleh suite blackbox Playwright.');
  const fileA = await shoot(page, 'BB-04-01', { suffix: 'a' });

  await page.locator('button[type="submit"]').click();
  await page.waitForURL('**/admin/kategori-mobil', { timeout: 10_000 });
  await expect(page.getByText('Kategori mobil berhasil ditambahkan.')).toBeVisible();
  await expect(page.getByRole('row', { name: new RegExp(categoryName) })).toBeVisible();
  const fileB = await shoot(page, 'BB-04-01', { suffix: 'b', fullPage: true });

  recordResult({
    id: 'BB-04-01',
    status: 'lolos',
    files: [fileA, fileB],
    catatan: 'Kategori baru tersimpan dan tampil pada daftar kategori mobil.',
  });
});

test('BB-04-02 Ubah kategori', async ({ page }) => {
  await page.goto('/admin/kategori-mobil', { waitUntil: 'networkidle' });
  const row = page.getByRole('row', { name: new RegExp(categoryName) });
  await row.getByRole('button').first().click();
  await page.waitForURL('**/kategori-mobil/*/edit', { timeout: 10_000 });

  await page.locator('#name').fill(categoryNameEdited);
  await page.locator('button[type="submit"]').click();
  await page.waitForURL('**/admin/kategori-mobil', { timeout: 10_000 });
  await expect(page.getByText('Kategori mobil berhasil diperbarui.')).toBeVisible();
  await expect(page.getByRole('row', { name: new RegExp(categoryNameEdited) })).toBeVisible();
  const file = await shoot(page, 'BB-04-02', { fullPage: true });

  recordResult({
    id: 'BB-04-02',
    status: 'lolos',
    files: [file],
    catatan: 'Perubahan nama kategori tersimpan dan tampil pada daftar.',
  });
});

test('BB-04-04 Tambah kategori tanpa nama', async ({ page }) => {
  await page.goto('/admin/kategori-mobil/create', { waitUntil: 'networkidle' });
  await page.locator('button[type="submit"]').click();
  await page.waitForTimeout(300);

  const validationMessage = await page.locator('#name').evaluate((el: HTMLInputElement) => el.validationMessage);
  const file = await shoot(page, 'BB-04-04');

  expect(page.url()).toContain('/create');

  recordResult({
    id: 'BB-04-04',
    status: 'lolos',
    files: [file],
    catatan: `Nama kategori wajib diisi dicegah lewat validasi bawaan browser (atribut "required"). Pesan browser: "${validationMessage}".`,
  });
});

test('BB-04-03 Hapus kategori', async ({ page }) => {
  await page.goto('/admin/kategori-mobil', { waitUntil: 'networkidle' });
  const row = page.getByRole('row', { name: new RegExp(categoryNameEdited) });
  await row.getByRole('button').last().click();
  await confirmDialog(page, 'Hapus');
  await expect(page.getByText('Kategori mobil berhasil dihapus.')).toBeVisible();
  await expect(page.getByRole('row', { name: new RegExp(categoryNameEdited) })).toHaveCount(0);
  const file = await shoot(page, 'BB-04-03', { fullPage: true });

  recordResult({
    id: 'BB-04-03',
    status: 'lolos',
    files: [file],
    catatan: 'Kategori uji berhasil dihapus dan tidak lagi tampil pada daftar. Dialog konfirmasi berupa modal custom (ConfirmDialog.vue), bukan window.confirm() bawaan browser, sehingga dapat difoto langsung.',
  });
});
