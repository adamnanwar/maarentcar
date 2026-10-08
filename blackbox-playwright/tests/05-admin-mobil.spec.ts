import { expect, test } from '@playwright/test';
import { recordResult } from '../helpers/report';
import { shoot } from '../helpers/shoot';
import { confirmDialog, uniqueSuffix, VALID_IMAGE } from '../helpers/ui';
import { ADMIN_STATE } from '../helpers/global-setup';

test.use({ storageState: ADMIN_STATE });

// Tabel 5.5 — Pengujian Kelola Mobil
// Urutan eksekusi: tambah -> ubah -> nonaktifkan (cek katalog publik) -> hapus.

const plate = `BP ${uniqueSuffix()} BX`;
const vehicleName = `Mobil Uji Blackbox ${uniqueSuffix()}`;

test('BB-05-01 Tambah mobil', async ({ page }) => {
  await page.goto('/admin/mobil/create', { waitUntil: 'networkidle' });
  await page.locator('#category_id').selectOption({ label: 'City Car' });
  await page.locator('#name').fill(vehicleName);
  await page.locator('#brand').fill('Blackbox');
  await page.locator('#model').fill('Uji');
  await page.locator('#year').fill('2024');
  await page.locator('#plate_number').fill(plate);
  await page.locator('#transmission').selectOption('manual');
  await page.locator('#fuel_type').selectOption('bensin');
  await page.locator('#seat_capacity').fill('5');
  await page.locator('#status').selectOption('tersedia');
  await page.locator('#price_per_day').fill('250000');
  await page.locator('#description').fill('Mobil dibuat otomatis oleh suite blackbox Playwright.');
  await page.setInputFiles('input[type="file"]', VALID_IMAGE);
  const fileA = await shoot(page, 'BB-05-01', { suffix: 'a', fullPage: true });

  await page.locator('button[type="submit"]').click();
  await page.waitForURL('**/admin/mobil', { timeout: 10_000 });
  await expect(page.getByText('Mobil berhasil ditambahkan.')).toBeVisible();
  await expect(page.getByRole('row', { name: new RegExp(vehicleName) })).toBeVisible();

  await page.goto('/mobil', { waitUntil: 'networkidle' });
  await expect(page.getByText(vehicleName)).toBeVisible();
  const fileB = await shoot(page, 'BB-05-01', { suffix: 'b', fullPage: true });

  recordResult({
    id: 'BB-05-01',
    status: 'lolos',
    files: [fileA, fileB],
    catatan: 'Mobil baru tersimpan, tampil pada daftar admin maupun katalog publik.',
  });
});

test('BB-05-02 Ubah mobil', async ({ page }) => {
  await page.goto('/admin/mobil', { waitUntil: 'networkidle' });
  const row = page.getByRole('row', { name: new RegExp(vehicleName) });
  await row.getByRole('button').first().click();
  await page.waitForURL('**/mobil/*/edit', { timeout: 10_000 });

  await page.locator('#price_per_day').fill('275000');
  await page.locator('button[type="submit"]').click();
  await page.waitForURL('**/admin/mobil', { timeout: 10_000 });
  await expect(page.getByText('Mobil berhasil diperbarui.')).toBeVisible();
  const file = await shoot(page, 'BB-05-02', { fullPage: true });

  recordResult({
    id: 'BB-05-02',
    status: 'lolos',
    files: [file],
    catatan: 'Perubahan harga sewa mobil berhasil tersimpan.',
  });
});

test('BB-05-04 Menonaktifkan status mobil', async ({ page, browser }) => {
  await page.goto('/admin/mobil', { waitUntil: 'networkidle' });
  const row = page.getByRole('row', { name: new RegExp(vehicleName) });
  await row.getByRole('button').first().click();
  await page.waitForURL('**/mobil/*/edit', { timeout: 10_000 });

  await page.locator('#status').selectOption('nonaktif');
  await page.getByLabel('Tampilkan mobil ini di katalog publik').uncheck();
  const fileA = await shoot(page, 'BB-05-04', { suffix: 'a', fullPage: true });

  await page.locator('button[type="submit"]').click();
  await page.waitForURL('**/admin/mobil', { timeout: 10_000 });
  await expect(page.getByText('Mobil berhasil diperbarui.')).toBeVisible();

  // storageState: undefined wajib eksplisit, karena browser.newContext() ikut
  // mewarisi test.use({ storageState: ADMIN_STATE }) di atas jika tidak ditimpa.
  const guestContext = await browser.newContext({ storageState: undefined });
  const guestPage = await guestContext.newPage();
  await guestPage.goto('/mobil', { waitUntil: 'networkidle' });
  await expect(guestPage.getByText(vehicleName)).toHaveCount(0);
  const fileB = await shoot(guestPage, 'BB-05-04', { suffix: 'b', fullPage: true });
  await guestContext.close();

  recordResult({
    id: 'BB-05-04',
    status: 'lolos',
    files: [fileA, fileB],
    catatan: 'Mobil yang dinonaktifkan tidak lagi tampil pada katalog publik (diverifikasi tanpa sesi login).',
  });
});

test('BB-05-03 Hapus mobil', async ({ page }) => {
  await page.goto('/admin/mobil', { waitUntil: 'networkidle' });
  const row = page.getByRole('row', { name: new RegExp(vehicleName) });
  await row.getByRole('button').last().click();
  await confirmDialog(page, 'Hapus');
  await expect(page.getByText('Mobil berhasil dihapus.')).toBeVisible();
  await expect(page.getByRole('row', { name: new RegExp(vehicleName) })).toHaveCount(0);
  const file = await shoot(page, 'BB-05-03', { fullPage: true });

  recordResult({
    id: 'BB-05-03',
    status: 'lolos',
    files: [file],
    catatan: 'Mobil uji berhasil dihapus dari daftar.',
  });
});
