import { expect, test } from '@playwright/test';
import { recordResult } from '../helpers/report';
import { shoot } from '../helpers/shoot';
import { ADMIN_STATE } from '../helpers/global-setup';

test.use({ storageState: ADMIN_STATE });

// Tabel 5.13 — Pengujian Pengaturan Sistem

test('BB-13-01 Mengubah rekening bank & kontak', async ({ page }) => {
  await page.goto('/admin/pengaturan', { waitUntil: 'networkidle' });
  const originalAccountNumber = await page.locator('#bank_account_number').inputValue();

  await page.locator('#bank_account_number').fill('9988776655');
  await page.getByRole('button', { name: 'Simpan Pengaturan', exact: true }).click();
  await expect(page.getByText('Pengaturan berhasil disimpan.')).toBeVisible();
  const file = await shoot(page, 'BB-13-01', { fullPage: true });

  // Kembalikan nilai semula.
  await page.locator('#bank_account_number').fill(originalAccountNumber);
  await page.getByRole('button', { name: 'Simpan Pengaturan', exact: true }).click();
  await expect(page.getByText('Pengaturan berhasil disimpan.')).toBeVisible();

  recordResult({
    id: 'BB-13-01',
    status: 'lolos',
    files: [file],
    catatan: 'Perubahan nomor rekening bank tersimpan (nilai semula dikembalikan setelah pengecekan).',
  });
});

test('BB-13-02 Mengubah konten homepage', async ({ page }) => {
  await page.goto('/admin/pengaturan', { waitUntil: 'networkidle' });
  const originalTitle = await page.locator('#homepage_hero_title').inputValue();
  const testTitle = 'Judul Hero Uji Blackbox';

  await page.locator('#homepage_hero_title').fill(testTitle);
  await page.getByRole('button', { name: 'Simpan Pengaturan', exact: true }).click();
  await expect(page.getByText('Pengaturan berhasil disimpan.')).toBeVisible();
  const fileA = await shoot(page, 'BB-13-02', { suffix: 'a', fullPage: true });

  await page.goto('/', { waitUntil: 'networkidle' });
  const titleAppeared = await page
    .getByText(testTitle)
    .isVisible()
    .catch(() => false);
  const fileB = await shoot(page, 'BB-13-02', { suffix: 'b', fullPage: true });

  // Kembalikan nilai semula.
  await page.goto('/admin/pengaturan', { waitUntil: 'networkidle' });
  await page.locator('#homepage_hero_title').fill(originalTitle);
  await page.getByRole('button', { name: 'Simpan Pengaturan', exact: true }).click();
  await expect(page.getByText('Pengaturan berhasil disimpan.')).toBeVisible();

  if (titleAppeared) {
    recordResult({
      id: 'BB-13-02',
      status: 'lolos',
      files: [fileA, fileB],
      catatan: 'Perubahan judul hero tampil di halaman Beranda publik (nilai semula dikembalikan setelah pengecekan).',
    });
  } else {
    recordResult({
      id: 'BB-13-02',
      status: 'gagal',
      files: [fileA, fileB],
      catatan:
        'GAGAL (temuan bug nyata, bukan masalah skrip uji): judul hero berhasil tersimpan di halaman Pengaturan (muncul notifikasi sukses), tetapi perubahan itu TIDAK tampil sama sekali di halaman Beranda publik. Penyebab: HomeController@index (app/Http/Controllers/HomeController.php) tidak pernah mengambil/mengirim setting "homepage_hero_title" atau "homepage_hero_subtitle" ke halaman Home, dan resources/js/pages/Home.vue menampilkan judul hero sebagai teks statis ("Jelajahi Batam, Tanpa Ribet") yang di-hardcode, bukan dari data pengaturan. Field pengaturan ini saat ini tidak berfungsi sama sekali bagi pengunjung. Tidak diperbaiki di sini sesuai aturan "jangan mengubah kode aplikasi tanpa persetujuan" - perlu keputusan pemilik produk apakah homepage perlu dihubungkan ke setting ini atau field ini sebaiknya dihapus dari halaman Pengaturan.',
    });
  }
});
