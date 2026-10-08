import { expect, test } from '@playwright/test';
import { recordResult } from '../helpers/report';
import { shoot } from '../helpers/shoot';
import { loadState } from '../helpers/state';
import { CUSTOMER_STATE } from '../helpers/global-setup';

test.use({ storageState: CUSTOMER_STATE });

// Tabel 5.23 — Pengujian Tulis Ulasan

test('BB-23-02 Menulis ulasan tanpa rating', async ({ page }) => {
  const carBooking = loadState<{ code: string; url: string }>('carBooking');
  const bookingId = new URL(carBooking.url).pathname.split('/').pop();

  // Komponen bintang di UI selalu punya rating minimal 1 (tidak bisa dikosongkan
  // lewat klik biasa - lihat RatingStars.vue, rating default 5 dan tiap bintang
  // minimal bernilai 1). Untuk tetap menguji validasi server "rating wajib diisi"
  // secara blackbox, request POST disadap dan field rating diganti kosong,
  // bukan dengan mengubah kode aplikasi.
  await page.route(`**/booking/${bookingId}/ulasan`, async (route) => {
    const request = route.request();
    const contentType = request.headers()['content-type'] ?? '';
    if (contentType.includes('json')) {
      const postData = request.postDataJSON() as Record<string, unknown>;
      postData.rating = '';
      await route.continue({ postData: JSON.stringify(postData) });
    } else {
      const raw = request.postData() ?? '';
      const modified = raw.replace(/rating=\d+/, 'rating=');
      await route.continue({ postData: modified });
    }
  });

  await page.goto(`/booking/${bookingId}/ulasan/tulis`, { waitUntil: 'networkidle' });
  await page.getByRole('button', { name: 'Kirim Ulasan', exact: true }).click();
  await page.waitForTimeout(500);

  await expect(page.locator('p.text-destructive').first()).toBeVisible();
  const file = await shoot(page, 'BB-23-02');

  recordResult({
    id: 'BB-23-02',
    status: 'lolos',
    files: [file],
    catatan:
      'Pesan validasi rating wajib diisi tampil. Catatan teknis: komponen bintang di UI tidak memungkinkan rating kosong lewat klik biasa (minimal 1), sehingga pengiriman rating kosong disimulasikan lewat penyadapan request (page.route) ke nilai asli form, bukan dengan mengubah kode aplikasi.',
  });
});

test('BB-23-01 Menulis ulasan dengan data valid', async ({ page }) => {
  const carBooking = loadState<{ code: string; url: string }>('carBooking');
  const bookingId = new URL(carBooking.url).pathname.split('/').pop();

  await page.goto(`/booking/${bookingId}/ulasan/tulis`, { waitUntil: 'networkidle' });
  await page.locator('div.flex.items-center.gap-1 button').nth(3).click();
  await page.getByPlaceholder('Bagaimana pengalaman Anda?').fill('Mobil bersih dan nyaman, proses sewa sangat mudah. Terima kasih We Rent Car!');
  const fileA = await shoot(page, 'BB-23-01', { suffix: 'a' });

  await page.getByRole('button', { name: 'Kirim Ulasan', exact: true }).click();
  await page.waitForURL(`**/booking/${bookingId}`, { timeout: 10_000 });
  await expect(page.getByText('Terima kasih atas ulasan Anda.')).toBeVisible();
  const fileB = await shoot(page, 'BB-23-01', { suffix: 'b', fullPage: true });

  recordResult({
    id: 'BB-23-01',
    status: 'lolos',
    files: [fileA, fileB],
    catatan: `Ulasan untuk booking ${carBooking.code} berhasil dikirim beserta pesan terima kasih.`,
  });
});
