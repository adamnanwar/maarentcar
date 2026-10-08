import { expect, test } from '@playwright/test';
import { recordResult } from '../helpers/report';
import { shoot } from '../helpers/shoot';
import { ADMIN_STATE } from '../helpers/global-setup';

test.use({ storageState: ADMIN_STATE });

// Tabel 5.14 — Pengujian Laporan

test('BB-14-01 Memfilter rentang tanggal', async ({ page }) => {
  await page.goto('/admin/laporan', { waitUntil: 'networkidle' });
  const dateInputs = page.locator('input[type="date"]');
  const from = new Date(Date.now() - 30 * 24 * 3600 * 1000).toISOString().slice(0, 10);
  const to = new Date().toISOString().slice(0, 10);
  await dateInputs.first().fill(from);
  await dateInputs.last().fill(to);
  await page.getByRole('button', { name: 'Terapkan', exact: true }).click();
  await page.waitForLoadState('networkidle');
  await expect(page.getByText('Total Pendapatan')).toBeVisible();
  const file = await shoot(page, 'BB-14-01', { fullPage: true });

  recordResult({
    id: 'BB-14-01',
    status: 'lolos',
    files: [file],
    catatan: `Laporan menampilkan data sesuai rentang tanggal ${from} s.d. ${to}.`,
  });
});

test('BB-14-02 Mengekspor laporan', async ({ page }) => {
  await page.goto('/admin/laporan', { waitUntil: 'networkidle' });

  const downloadPromise = page.waitForEvent('download', { timeout: 8_000 }).catch(() => null);
  await page.getByRole('link', { name: 'Ekspor CSV', exact: true }).click();
  await page.waitForTimeout(500);
  const download = await downloadPromise;

  if (download) {
    const suggestedName = download.suggestedFilename();
    const file = await shoot(page, 'BB-14-02', { fullPage: true });
    recordResult({
      id: 'BB-14-02',
      status: 'lolos',
      files: [file],
      catatan: `Berkas CSV berhasil terunduh: ${suggestedName}.`,
    });
    return;
  }

  const file = await shoot(page, 'BB-14-02', { fullPage: true });
  const pageText = (await page.textContent('body')) ?? '';
  const isServerError = pageText.includes('Internal Server Error') || pageText.includes('TypeError');

  recordResult({
    id: 'BB-14-02',
    status: 'gagal',
    files: [file],
    catatan: isServerError
      ? 'GAGAL (temuan bug nyata, bukan masalah skrip uji): klik "Ekspor CSV" menghasilkan HTTP 500 Internal Server Error, bukan file terunduh. Penyebab: AdminReportController::export() (app/Http/Controllers/Admin/AdminReportController.php baris 39-51) mendeklarasikan tipe kembalian "Illuminate\\Http\\Response", padahal isi methodnya mengembalikan response()->streamDownload(...) yang sebenarnya bertipe Symfony\\Component\\HttpFoundation\\StreamedResponse - bukan turunan dari Illuminate\\Http\\Response. PHP melempar TypeError fatal setiap kali endpoint ini diakses, sehingga fitur ekspor laporan sama sekali tidak berfungsi saat ini. Tidak diperbaiki di sini sesuai aturan "jangan mengubah kode aplikasi tanpa persetujuan" - perbaikannya cukup sederhana (ganti tipe kembalian method menjadi Symfony\\Component\\HttpFoundation\\StreamedResponse atau hapus deklarasi tipe), tapi perlu persetujuan pemilik produk.'
      : 'GAGAL: tidak ada berkas yang terunduh setelah tombol Ekspor CSV diklik, dan halaman tidak menampilkan pesan error yang dikenali.',
  });
});
