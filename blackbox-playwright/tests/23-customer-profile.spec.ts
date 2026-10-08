import { expect, test } from '@playwright/test';
import { recordResult } from '../helpers/report';
import { shoot } from '../helpers/shoot';
import { ENV } from '../helpers/env';
import { CUSTOMER_STATE } from '../helpers/global-setup';

test.use({ storageState: CUSTOMER_STATE });

// Tabel 5.24 — Pengujian Edit Profil Saya

test('BB-24-01 Mengubah data diri', async ({ page }) => {
  await page.goto('/profil', { waitUntil: 'networkidle' });
  const originalPhone = await page.locator('#phone').inputValue();

  await page.locator('#phone').fill('081299998888');
  await page.getByRole('button', { name: 'Simpan Perubahan', exact: true }).click();
  await expect(page.getByText('Profil berhasil diperbarui.')).toBeVisible();
  const file = await shoot(page, 'BB-24-01', { fullPage: true });

  // Kembalikan nilai semula.
  await page.locator('#phone').fill(originalPhone);
  await page.getByRole('button', { name: 'Simpan Perubahan', exact: true }).click();
  await expect(page.getByText('Profil berhasil diperbarui.')).toBeVisible();

  recordResult({
    id: 'BB-24-01',
    status: 'lolos',
    files: [file],
    catatan: 'Perubahan nomor telepon berhasil tersimpan (nilai semula dikembalikan setelah pengecekan).',
  });
});

test('BB-24-03 Konfirmasi kata sandi tidak cocok', async ({ page }) => {
  await page.goto('/profil', { waitUntil: 'networkidle' });
  await page.locator('#current_password').fill(ENV.customerPassword());
  await page.locator('#password').fill('passwordBaruSatu123');
  await page.locator('#password_confirmation').fill('passwordBaruBerbeda123');
  await page.getByRole('button', { name: 'Ubah Kata Sandi', exact: true }).click();
  await page.waitForTimeout(500);

  await expect(page.locator('p.text-destructive').first()).toBeVisible();
  const file = await shoot(page, 'BB-24-03');

  recordResult({
    id: 'BB-24-03',
    status: 'lolos',
    files: [file],
    catatan: 'Pesan error konfirmasi kata sandi tidak cocok tampil, kata sandi tidak jadi diubah.',
  });
});

test('BB-24-02 Mengubah kata sandi', async ({ page }) => {
  const newPassword = 'passwordBaruValid123';

  await page.goto('/profil', { waitUntil: 'networkidle' });
  await page.locator('#current_password').fill(ENV.customerPassword());
  await page.locator('#password').fill(newPassword);
  await page.locator('#password_confirmation').fill(newPassword);
  await page.getByRole('button', { name: 'Ubah Kata Sandi', exact: true }).click();
  await expect(page.getByText('Kata sandi berhasil diubah.')).toBeVisible();
  const file = await shoot(page, 'BB-24-02', { fullPage: true });

  // Kembalikan ke kata sandi semula supaya tidak mengganggu skenario lain.
  await page.locator('#current_password').fill(newPassword);
  await page.locator('#password').fill(ENV.customerPassword());
  await page.locator('#password_confirmation').fill(ENV.customerPassword());
  await page.getByRole('button', { name: 'Ubah Kata Sandi', exact: true }).click();
  await expect(page.getByText('Kata sandi berhasil diubah.')).toBeVisible();

  recordResult({
    id: 'BB-24-02',
    status: 'lolos',
    files: [file],
    catatan: 'Kata sandi berhasil diubah (dikembalikan ke kata sandi semula setelah pengecekan agar tidak mengganggu skenario lain).',
  });
});
