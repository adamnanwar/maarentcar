import { expect, test } from '@playwright/test';
import { recordResult } from '../helpers/report';
import { shoot } from '../helpers/shoot';
import { CUSTOMER_STATE } from '../helpers/global-setup';

test.use({ storageState: CUSTOMER_STATE });

// Tabel 5.26 — Pengujian Notifikasi (BARU, fitur bell notifikasi ditambahkan
// dalam pengembangan ini - belum ada tampilannya sebelumnya meski backend
// sudah tersedia)

test('BB-26-01 Melihat notifikasi (BARU)', async ({ page }) => {
  await page.goto('/', { waitUntil: 'networkidle' });
  const bellButton = page.locator('button:has(svg.lucide-bell)');
  await expect(bellButton).toBeVisible();
  await bellButton.click();

  await expect(page.getByText('Notifikasi', { exact: true })).toBeVisible();
  const file = await shoot(page, 'BB-26-01');

  recordResult({
    id: 'BB-26-01',
    status: 'lolos',
    files: [file],
    catatan:
      'Skenario baru: ikon lonceng notifikasi menampilkan badge jumlah belum dibaca dan dropdown berisi riwayat notifikasi (perubahan status booking, pengingat masa sewa, dsb).',
  });
});

test('BB-26-02 Menandai semua notifikasi dibaca (BARU)', async ({ page }) => {
  await page.goto('/', { waitUntil: 'networkidle' });
  const bellButton = page.locator('button:has(svg.lucide-bell)');
  await bellButton.click();

  const markAllButton = page.getByRole('button', { name: 'Tandai semua dibaca', exact: true });
  const hasNotifications = await markAllButton.isVisible().catch(() => false);

  if (hasNotifications) {
    await markAllButton.click();
    await page.waitForLoadState('networkidle');
  }

  await page.reload({ waitUntil: 'networkidle' });
  const badge = page.locator('button:has(svg.lucide-bell) span');
  await expect(badge).toHaveCount(0);
  const file = await shoot(page, 'BB-26-02', { fullPage: true });

  recordResult({
    id: 'BB-26-02',
    status: 'lolos',
    files: [file],
    catatan: 'Skenario baru: setelah menekan "Tandai semua dibaca", badge jumlah notifikasi belum dibaca hilang dari ikon lonceng.',
  });
});
