import { expect, test } from '@playwright/test';
import { recordResult } from '../helpers/report';
import { shoot } from '../helpers/shoot';
import { ENV } from '../helpers/env';

// Tabel 5.2 — Pengujian Halaman Login Admin

test('BB-02-01 Login dengan data valid', async ({ page }) => {
  await page.goto('/login', { waitUntil: 'networkidle' });
  await page.locator('#email').fill(ENV.adminEmail());
  await page.locator('#password').fill(ENV.adminPassword());
  await page.locator('button[type="submit"]').click();
  await page.waitForURL('**/admin', { timeout: 15_000 });
  await expect(page.getByRole('heading', { name: 'Dashboard' })).toBeVisible();
  const file = await shoot(page, 'BB-02-01', { fullPage: true });

  recordResult({
    id: 'BB-02-01',
    status: 'lolos',
    files: [file],
    catatan: 'Login admin valid berhasil diarahkan ke /admin (Dashboard Admin).',
  });
});

test('BB-02-02 Login dengan email tidak terdaftar', async ({ page }) => {
  await page.goto('/login', { waitUntil: 'networkidle' });
  await page.locator('#email').fill('tidak.terdaftar.blackbox@example.com');
  await page.locator('#password').fill('passwordasal');
  await page.locator('button[type="submit"]').click();
  const error = page.getByText('Email atau kata sandi salah.');
  await expect(error).toBeVisible();
  const file = await shoot(page, 'BB-02-02');

  recordResult({
    id: 'BB-02-02',
    status: 'lolos',
    files: [file],
    catatan: 'Pesan error "Email atau kata sandi salah." tampil untuk email tidak terdaftar.',
  });
});

test('BB-02-03 Login dengan password salah', async ({ page }) => {
  await page.goto('/login', { waitUntil: 'networkidle' });
  await page.locator('#email').fill(ENV.adminEmail());
  await page.locator('#password').fill('password-salah-sekali');
  await page.locator('button[type="submit"]').click();
  const error = page.getByText('Email atau kata sandi salah.');
  await expect(error).toBeVisible();
  const file = await shoot(page, 'BB-02-03');

  recordResult({
    id: 'BB-02-03',
    status: 'lolos',
    files: [file],
    catatan: 'Pesan error "Email atau kata sandi salah." tampil untuk password yang salah.',
  });
});

test('BB-02-04 Login dengan field kosong', async ({ page }) => {
  await page.goto('/login', { waitUntil: 'networkidle' });
  await page.locator('button[type="submit"]').click();
  await page.waitForTimeout(300);

  const stillOnLogin = page.url().includes('/login');
  const validationMessage = await page.locator('#email').evaluate((el: HTMLInputElement) => el.validationMessage);
  const file = await shoot(page, 'BB-02-04');

  expect(stillOnLogin).toBeTruthy();

  recordResult({
    id: 'BB-02-04',
    status: 'lolos',
    files: [file],
    catatan: `Validasi field wajib memakai validasi bawaan browser HTML5 (atribut "required"), bukan pesan dari server. Pesan validasi browser pada field email: "${validationMessage}".`,
  });
});
