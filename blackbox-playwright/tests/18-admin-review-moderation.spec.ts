import { expect, test } from '@playwright/test';
import { recordResult } from '../helpers/report';
import { shoot } from '../helpers/shoot';
import { ADMIN_STATE } from '../helpers/global-setup';

test.use({ storageState: ADMIN_STATE });

// Tabel 5.10 — Pengujian Moderasi Ulasan

const REVIEW_SNIPPET = 'Mobil bersih dan nyaman';

test('BB-10-01 Melihat daftar ulasan', async ({ page }) => {
  await page.goto('/admin/ulasan', { waitUntil: 'networkidle' });
  const row = page.getByRole('row', { name: new RegExp(REVIEW_SNIPPET) });
  await expect(row).toBeVisible();
  const file = await shoot(page, 'BB-10-01', { fullPage: true });

  recordResult({
    id: 'BB-10-01',
    status: 'lolos',
    files: [file],
    catatan: 'Daftar ulasan pelanggan tampil lengkap beserta rating bintang.',
  });
});

test('BB-10-02 Memoderasi ulasan', async ({ page }) => {
  await page.goto('/admin/ulasan', { waitUntil: 'networkidle' });
  const row = page.getByRole('row', { name: new RegExp(REVIEW_SNIPPET) });
  await row.getByRole('button', { name: 'Sembunyikan', exact: true }).click();
  await page.waitForTimeout(500);
  await expect(row.getByText('Disembunyikan')).toBeVisible();
  const fileA = await shoot(page, 'BB-10-02', { suffix: 'a', fullPage: true });

  // Kembalikan ke semula (tampil) supaya tidak mengganggu data untuk pengujian lain.
  await row.getByRole('button', { name: 'Tampilkan', exact: true }).click();
  await page.waitForTimeout(500);
  await expect(row.getByText('Tampil', { exact: true })).toBeVisible();
  const fileB = await shoot(page, 'BB-10-02', { suffix: 'b', fullPage: true });

  recordResult({
    id: 'BB-10-02',
    status: 'lolos',
    files: [fileA, fileB],
    catatan: 'Admin berhasil menyembunyikan lalu menampilkan kembali ulasan dari katalog publik.',
  });
});
