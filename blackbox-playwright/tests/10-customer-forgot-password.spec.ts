import { expect, test } from '@playwright/test';
import { recordResult } from '../helpers/report';
import { shoot } from '../helpers/shoot';
import { readLatestResetLink } from '../helpers/ui';
import { ENV } from '../helpers/env';

// Tabel 5.17 — Pengujian Lupa Kata Sandi & Reset
// BB-17-02 mengubah kata sandi akun CUSTOMER_EMAIL, lalu segera mengembalikannya
// ke kata sandi semula di akhir test supaya tidak mengganggu skenario lain yang
// memakai akun yang sama.

test('BB-17-01 Mengirim link reset', async ({ page }) => {
  await page.goto('/lupa-password', { waitUntil: 'networkidle' });
  await page.locator('#email').fill(ENV.customerEmail());
  await page.locator('button[type="submit"]').click();
  await expect(page.getByText('Link reset kata sandi telah dikirim ke email Anda.')).toBeVisible();
  const file = await shoot(page, 'BB-17-01');

  recordResult({
    id: 'BB-17-01',
    status: 'lolos',
    files: [file],
    catatan: 'Notifikasi pengiriman link reset tampil. MAIL_MAILER=log sehingga isi email tercatat di storage/logs/laravel.log, bukan terkirim sungguhan.',
  });
});

test('BB-17-02 Reset kata sandi baru', async ({ page }) => {
  const resetLink = readLatestResetLink();
  const newPassword = 'passwordBaru123';

  await page.goto(resetLink, { waitUntil: 'networkidle' });
  await page.locator('#password').fill(newPassword);
  await page.locator('#password_confirmation').fill(newPassword);
  await page.locator('button[type="submit"]').click();
  await page.waitForURL('**/login', { timeout: 15_000 });
  await expect(page.getByText('Kata sandi berhasil diubah, silakan masuk kembali.')).toBeVisible();
  const file = await shoot(page, 'BB-17-02');

  // Kembalikan kata sandi akun uji ke nilai semula supaya skenario lain tidak terganggu.
  await page.locator('#email').fill(ENV.customerEmail());
  await page.locator('#password').fill(newPassword);
  await page.locator('button[type="submit"]').click();
  await page.waitForURL((url) => !url.pathname.includes('/login'), { timeout: 15_000 });
  await page.goto('/profil', { waitUntil: 'networkidle' });
  await page.locator('#current_password').fill(newPassword);
  await page.locator('#password').fill(ENV.customerPassword());
  await page.locator('#password_confirmation').fill(ENV.customerPassword());
  await page.getByRole('button', { name: 'Ubah Kata Sandi', exact: true }).click();
  await expect(page.getByText('Kata sandi berhasil diubah.')).toBeVisible();

  recordResult({
    id: 'BB-17-02',
    status: 'lolos',
    files: [file],
    catatan: 'Kata sandi berhasil direset lewat tautan email dan diarahkan ke halaman Login. Kata sandi akun uji dikembalikan ke nilai semula setelah pengecekan agar tidak mengganggu skenario lain.',
  });
});

test('BB-17-03 Reset dengan email tidak terdaftar', async ({ page }) => {
  await page.goto('/lupa-password', { waitUntil: 'networkidle' });
  await page.locator('#email').fill('tidak.pernah.daftar.blackbox@example.com');
  await page.locator('button[type="submit"]').click();
  await expect(page.getByText('Email tidak ditemukan.')).toBeVisible();
  const file = await shoot(page, 'BB-17-03');

  recordResult({
    id: 'BB-17-03',
    status: 'lolos',
    files: [file],
    catatan: 'Pesan error "Email tidak ditemukan." tampil untuk email yang tidak terdaftar.',
  });
});
