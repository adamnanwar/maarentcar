import { expect, test } from '@playwright/test';
import { recordResult } from '../helpers/report';
import { shoot } from '../helpers/shoot';
import { confirmDialog, uniqueEmail } from '../helpers/ui';
import { ADMIN_STATE } from '../helpers/global-setup';

test.use({ storageState: ADMIN_STATE });

// Tabel 5.12 — Pengujian Kelola Staff & Permission

const staffEmail = uniqueEmail('blackbox.staff');

test('BB-12-01 Tambah staff', async ({ page }) => {
  await page.goto('/admin/staff/create', { waitUntil: 'networkidle' });
  await page.locator('#name').fill('Staff Uji Blackbox');
  await page.locator('#email').fill(staffEmail);
  await page.locator('#phone').fill('081234500088');
  await page.locator('#password').fill('password123');
  await page.locator('button[type="submit"]').click();
  await page.waitForURL('**/admin/staff', { timeout: 10_000 });
  await expect(page.getByText('Akun staff berhasil ditambahkan.')).toBeVisible();
  await expect(page.getByRole('row', { name: new RegExp(staffEmail) })).toBeVisible();
  const file = await shoot(page, 'BB-12-01', { fullPage: true });

  recordResult({
    id: 'BB-12-01',
    status: 'lolos',
    files: [file],
    catatan: `Akun staff baru (${staffEmail}) tersimpan dan tampil pada daftar staff.`,
  });
});

test('BB-12-03 Mengatur permission staff', async ({ page }) => {
  await page.goto('/admin/staff', { waitUntil: 'networkidle' });
  const checkbox = page.getByRole('checkbox', { name: 'Hapus data mobil' });
  const wasChecked = await checkbox.isChecked();
  if (wasChecked) await checkbox.uncheck();
  else await checkbox.check();
  const fileA = await shoot(page, 'BB-12-03', { suffix: 'a' });

  await page.getByRole('button', { name: 'Simpan Permission', exact: true }).click();
  await expect(page.getByText('Permission staff berhasil diperbarui.')).toBeVisible();
  const fileB = await shoot(page, 'BB-12-03', { suffix: 'b' });

  // Kembalikan ke kondisi semula.
  if (wasChecked) await checkbox.check();
  else await checkbox.uncheck();
  await page.getByRole('button', { name: 'Simpan Permission', exact: true }).click();
  await expect(page.getByText('Permission staff berhasil diperbarui.')).toBeVisible();

  recordResult({
    id: 'BB-12-03',
    status: 'lolos',
    files: [fileA, fileB],
    catatan: 'Perubahan centang permission "Hapus data mobil" untuk role staff berhasil disimpan (lalu dikembalikan ke kondisi semula).',
  });
});

test('BB-12-02 Nonaktifkan/hapus staff', async ({ page }) => {
  await page.goto('/admin/staff', { waitUntil: 'networkidle' });
  const row = page.getByRole('row', { name: new RegExp(staffEmail) });
  await row.getByRole('button', { name: 'Nonaktifkan', exact: true }).click();
  await page.waitForTimeout(500);
  await expect(row.getByRole('button', { name: 'Aktifkan', exact: true })).toBeVisible();
  const fileA = await shoot(page, 'BB-12-02', { suffix: 'a', fullPage: true });

  await row.getByRole('button', { name: 'Hapus', exact: true }).click();
  await confirmDialog(page, 'Hapus');
  await expect(page.getByText('Akun staff berhasil dihapus.')).toBeVisible();
  await expect(page.getByRole('row', { name: new RegExp(staffEmail) })).toHaveCount(0);
  const fileB = await shoot(page, 'BB-12-02', { suffix: 'b', fullPage: true });

  recordResult({
    id: 'BB-12-02',
    status: 'lolos',
    files: [fileA, fileB],
    catatan: 'Akun staff berhasil dinonaktifkan lalu dihapus dari daftar.',
  });
});
