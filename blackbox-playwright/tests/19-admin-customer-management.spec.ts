import { expect, test } from '@playwright/test';
import { recordResult } from '../helpers/report';
import { shoot } from '../helpers/shoot';
import { saveState, loadState } from '../helpers/state';
import { confirmDialog, login, uniqueEmail } from '../helpers/ui';
import { ADMIN_STATE } from '../helpers/global-setup';

test.use({ storageState: ADMIN_STATE });

// Tabel 5.11 — Pengujian Kelola Akun Pelanggan

test('BB-11-01 Menonaktifkan akun pelanggan', async ({ browser }) => {
  // Akun pelanggan sekali pakai khusus skenario ini.
  const email = uniqueEmail('blackbox.disposable');
  const password = 'password123';

  // storageState: undefined wajib eksplisit di setiap context manual di sini,
  // karena browser.newContext() ikut mewarisi test.use({storageState: ADMIN_STATE}).
  const regContext = await browser.newContext({ storageState: undefined });
  const regPage = await regContext.newPage();
  await regPage.goto('/register', { waitUntil: 'networkidle' });
  await regPage.locator('#name').fill('Pelanggan Sekali Pakai');
  await regPage.locator('#email').fill(email);
  await regPage.locator('#phone').fill('081234500077');
  await regPage.locator('#password').fill(password);
  await regPage.locator('#password_confirmation').fill(password);
  await regPage.locator('button[type="submit"]').click();
  await regPage.waitForURL('**/dashboard', { timeout: 15_000 });
  await regContext.close();
  saveState('disposableCustomer', { email, password });

  const adminContext = await browser.newContext({ storageState: ADMIN_STATE });
  const adminPage = await adminContext.newPage();
  await adminPage.goto('/admin/pengguna', { waitUntil: 'networkidle' });
  await adminPage.getByPlaceholder(/cari/i).fill(email).catch(() => {});
  await adminPage.waitForTimeout(500);
  const row = adminPage.getByRole('row', { name: new RegExp(email) });
  await row.getByRole('button', { name: 'Nonaktifkan', exact: true }).click();
  await adminPage.waitForTimeout(500);
  await expect(row.getByRole('button', { name: 'Aktifkan', exact: true })).toBeVisible();
  const fileA = await shoot(adminPage, 'BB-11-01', { suffix: 'a', fullPage: true });
  await adminContext.close();

  const loginContext = await browser.newContext({ storageState: undefined });
  const loginPage = await loginContext.newPage();
  await login(loginPage, email, password);
  const loginBlocked = loginPage.url().includes('/login');
  const fileB = await shoot(loginPage, 'BB-11-01', { suffix: 'b', fullPage: true });
  await loginContext.close();

  if (loginBlocked) {
    recordResult({
      id: 'BB-11-01',
      status: 'lolos',
      files: [fileA, fileB],
      catatan: 'Akun pelanggan berhasil dinonaktifkan dan percobaan login ditolak.',
    });
  } else {
    recordResult({
      id: 'BB-11-01',
      status: 'gagal',
      files: [fileA, fileB],
      catatan:
        'GAGAL (temuan bug nyata, bukan masalah skrip uji): status akun berhasil berubah menjadi Nonaktif di panel admin, namun pelanggan tersebut TETAP BISA login normal setelahnya. Penyebab: AuthController@login (app/Http/Controllers/AuthController.php) hanya memanggil Auth::attempt($credentials) berdasarkan email+password, tidak ada pengecekan kolom status user sama sekali di mana pun pada alur login. Akibatnya field status pelanggan/staff saat ini murni kosmetik dan tidak benar-benar memblokir akses. Tidak diperbaiki di sini sesuai aturan "jangan mengubah kode aplikasi tanpa persetujuan" - perlu keputusan pemilik produk untuk menambahkan pengecekan status saat login.',
    });
  }
});

test('BB-11-02 Menghapus akun pelanggan', async ({ page }) => {
  const disposable = loadState<{ email: string; password: string }>('disposableCustomer');
  await page.goto('/admin/pengguna', { waitUntil: 'networkidle' });
  await page.getByPlaceholder(/cari/i).fill(disposable.email).catch(() => {});
  await page.waitForTimeout(500);
  const row = page.getByRole('row', { name: new RegExp(disposable.email) });
  await row.getByRole('button', { name: 'Hapus', exact: true }).click();
  await confirmDialog(page, 'Hapus');
  await page.waitForTimeout(500);
  await expect(page.getByRole('row', { name: new RegExp(disposable.email) })).toHaveCount(0);
  const file = await shoot(page, 'BB-11-02', { fullPage: true });

  recordResult({
    id: 'BB-11-02',
    status: 'lolos',
    files: [file],
    catatan: 'Akun pelanggan sekali pakai berhasil dihapus dari daftar pengguna.',
  });
});
