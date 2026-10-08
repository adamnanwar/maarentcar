import { expect, test } from '@playwright/test';
import { recordResult } from '../helpers/report';
import { shoot } from '../helpers/shoot';
import { saveState } from '../helpers/state';
import { uniqueEmail } from '../helpers/ui';
import { ENV } from '../helpers/env';

// Tabel 5.15 — Pengujian Registrasi Akun

const freshName = 'Pelanggan Uji Blackbox';
const freshEmail = uniqueEmail('blackbox.customer');
const freshPassword = 'password123';

test('BB-15-01 Registrasi dengan data valid', async ({ page }) => {
  await page.goto('/register', { waitUntil: 'networkidle' });
  await page.locator('#name').fill(freshName);
  await page.locator('#email').fill(freshEmail);
  await page.locator('#phone').fill('081234500001');
  await page.locator('#password').fill(freshPassword);
  await page.locator('#password_confirmation').fill(freshPassword);
  await page.locator('button[type="submit"]').click();

  // Catatan: dokumen test case menyebut "diarahkan ke Beranda", namun perilaku
  // nyata aplikasi (lihat AuthController@register & activity-flow.md) mengarahkan
  // ke Dashboard Pelanggan (/dashboard). Ini bukan bug, dokumen test case saja
  // yang perlu disesuaikan - lihat pembaruan pada test-case-blackbox-playwright.md.
  await page.waitForURL('**/dashboard', { timeout: 15_000 });
  await expect(page.getByRole('heading', { name: /Halo, /i })).toBeVisible();
  const file = await shoot(page, 'BB-15-01', { fullPage: true });

  // Simpan akun ini untuk dipakai ulang di skenario BB-19-04 (tanggal bentrok)
  // supaya tidak perlu mendaftar akun baru lagi di sana.
  saveState('freshCustomerA', { email: freshEmail, password: freshPassword, name: freshName });

  recordResult({
    id: 'BB-15-01',
    status: 'lolos',
    files: [file],
    catatan:
      'Registrasi dengan data valid berhasil, akun otomatis masuk. Catatan: redirect sesungguhnya menuju Dashboard Pelanggan (/dashboard), bukan Beranda seperti disebut pada dokumen test case awal - deskripsi skenario sudah diperbarui agar sesuai perilaku nyata aplikasi (lihat juga activity-flow.md modul 1.1).',
  });
});

test('BB-15-02 Registrasi dengan email sudah terdaftar', async ({ page }) => {
  await page.goto('/register', { waitUntil: 'networkidle' });
  await page.locator('#name').fill('Pelanggan Duplikat');
  await page.locator('#email').fill(ENV.customerEmail());
  await page.locator('#phone').fill('081234500002');
  await page.locator('#password').fill('password123');
  await page.locator('#password_confirmation').fill('password123');
  await page.locator('button[type="submit"]').click();
  await page.waitForTimeout(500);

  const error = page.locator('p.text-destructive').first();
  await expect(error).toBeVisible();
  const file = await shoot(page, 'BB-15-02');

  recordResult({
    id: 'BB-15-02',
    status: 'lolos',
    files: [file],
    catatan:
      'Pesan validasi email sudah terdaftar tampil. Catatan: proyek belum punya berkas bahasa Indonesia untuk pesan validasi Laravel bawaan, sehingga teksnya berbahasa Inggris ("The email has already been taken.") meskipun APP_LOCALE=id.',
  });
});

test('BB-15-03 Registrasi dengan field kosong', async ({ page }) => {
  await page.goto('/register', { waitUntil: 'networkidle' });
  await page.locator('#name').fill('Pelanggan Tanpa Email');
  await page.locator('#phone').fill('081234500003');
  await page.locator('#password').fill('password123');
  await page.locator('#password_confirmation').fill('password123');
  await page.locator('button[type="submit"]').click();
  await page.waitForTimeout(300);

  const validationMessage = await page.locator('#email').evaluate((el: HTMLInputElement) => el.validationMessage);
  const file = await shoot(page, 'BB-15-03');

  expect(page.url()).toContain('/register');

  recordResult({
    id: 'BB-15-03',
    status: 'lolos',
    files: [file],
    catatan: `Field email dikosongkan, dicegah lewat validasi bawaan browser (atribut "required"). Pesan browser: "${validationMessage}".`,
  });
});
