import { expect, test } from '@playwright/test';
import { recordResult } from '../helpers/report';
import { shoot } from '../helpers/shoot';
import { ENV } from '../helpers/env';

// Tabel 5.16 — Pengujian Halaman Login Pelanggan

test('BB-16-01 Login dengan data valid', async ({ page }) => {
  await page.goto('/login', { waitUntil: 'networkidle' });
  await page.locator('#email').fill(ENV.customerEmail());
  await page.locator('#password').fill(ENV.customerPassword());
  await page.locator('button[type="submit"]').click();
  // Catatan: dokumen test case menyebut "diarahkan ke Beranda", namun perilaku
  // nyata aplikasi (lihat AuthController@login & activity-flow.md modul 1.2)
  // mengarahkan pelanggan ke Dashboard Pelanggan (/dashboard).
  await page.waitForURL('**/dashboard', { timeout: 15_000 });
  await expect(page.getByRole('heading', { name: /Halo, /i })).toBeVisible();
  const file = await shoot(page, 'BB-16-01', { fullPage: true });

  recordResult({
    id: 'BB-16-01',
    status: 'lolos',
    files: [file],
    catatan:
      'Login pelanggan valid berhasil. Catatan: redirect sesungguhnya menuju Dashboard Pelanggan (/dashboard), bukan Beranda seperti disebut pada dokumen test case awal - deskripsi skenario sudah diperbarui agar sesuai perilaku nyata aplikasi.',
  });
});

test('BB-16-02 Login dengan kredensial salah', async ({ page }) => {
  await page.goto('/login', { waitUntil: 'networkidle' });
  await page.locator('#email').fill(ENV.customerEmail());
  await page.locator('#password').fill('password-yang-salah');
  await page.locator('button[type="submit"]').click();
  const error = page.getByText('Email atau kata sandi salah.');
  await expect(error).toBeVisible();
  const file = await shoot(page, 'BB-16-02');

  recordResult({
    id: 'BB-16-02',
    status: 'lolos',
    files: [file],
    catatan: 'Pesan error "Email atau kata sandi salah." tampil untuk kredensial yang salah.',
  });
});
