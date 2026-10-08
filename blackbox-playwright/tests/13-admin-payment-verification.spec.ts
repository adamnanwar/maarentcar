import { expect, test } from '@playwright/test';
import { recordResult } from '../helpers/report';
import { shoot } from '../helpers/shoot';
import { loadState } from '../helpers/state';
import { confirmDialog, login, VALID_IMAGE } from '../helpers/ui';
import { ENV } from '../helpers/env';
import { ADMIN_STATE } from '../helpers/global-setup';

// Tabel 5.8 — Pengujian Verifikasi Pembayaran (lintas peran: admin & pelanggan)

test('BB-08-01 Menyetujui bukti transfer valid', async ({ browser }) => {
  const carBooking = loadState<{ code: string; url: string }>('carBooking');

  const adminContext = await browser.newContext({ storageState: ADMIN_STATE });
  const adminPage = await adminContext.newPage();
  await adminPage.goto('/admin/pembayaran', { waitUntil: 'networkidle' });
  const card = adminPage.locator('div.rounded-xl').filter({ hasText: carBooking.code });
  await card.getByRole('button', { name: 'Verifikasi', exact: true }).click();
  await confirmDialog(adminPage, 'Verifikasi');
  await expect(adminPage.getByText('Pembayaran berhasil diverifikasi, booking dikonfirmasi.')).toBeVisible();
  const fileA = await shoot(adminPage, 'BB-08-01', { suffix: 'a', fullPage: true });
  await adminContext.close();

  const customerContext = await browser.newContext({ storageState: undefined });
  const customerPage = await customerContext.newPage();
  await login(customerPage, ENV.customerEmail(), ENV.customerPassword());
  await customerPage.goto(carBooking.url, { waitUntil: 'networkidle' });
  await expect(customerPage.getByText('Dikonfirmasi', { exact: true })).toBeVisible();
  const fileB = await shoot(customerPage, 'BB-08-01', { suffix: 'b', fullPage: true });
  await customerContext.close();

  recordResult({
    id: 'BB-08-01',
    status: 'lolos',
    files: [fileA, fileB],
    catatan: `Admin memverifikasi booking ${carBooking.code}, status berubah menjadi Dikonfirmasi dan terlihat juga di sisi pelanggan.`,
  });
});

test('BB-08-02 Menolak bukti transfer', async ({ browser }) => {
  const freshCustomer = loadState<{ email: string; password: string }>('freshCustomerA');

  const customerContext = await browser.newContext({ storageState: undefined });
  const customerPage = await customerContext.newPage();
  await login(customerPage, freshCustomer.email, freshCustomer.password);
  await customerPage.goto('/booking', { waitUntil: 'networkidle' });
  await customerPage.getByText('Honda Brio').first().click();
  await customerPage.waitForURL('**/booking/*');
  const bodyText = (await customerPage.textContent('body')) ?? '';
  const bookingCode = bodyText.match(/WRC-[0-9]{6}-[A-Z0-9]{5}/)?.[0] ?? '';

  await customerPage.setInputFiles('input[type="file"]', VALID_IMAGE);
  await customerPage.getByRole('button', { name: 'Unggah Bukti Transfer', exact: true }).click();
  await customerPage.waitForTimeout(600);
  await customerContext.close();

  const adminContext = await browser.newContext({ storageState: ADMIN_STATE });
  const adminPage = await adminContext.newPage();
  await adminPage.goto('/admin/pembayaran', { waitUntil: 'networkidle' });
  const card = adminPage.locator('div.rounded-xl').filter({ hasText: bookingCode });
  await card.getByRole('button', { name: 'Tolak', exact: true }).click();
  await adminPage.getByPlaceholder('Alasan penolakan (wajib diisi, mis. nominal tidak sesuai)').fill('Nominal transfer tidak sesuai dengan total tagihan.');
  const fileA = await shoot(adminPage, 'BB-08-02', { suffix: 'a' });

  await adminPage.getByRole('button', { name: 'Kirim Penolakan', exact: true }).click();
  await expect(adminPage.getByText('Pembayaran ditolak, pelanggan diminta mengunggah ulang.')).toBeVisible();
  const fileB = await shoot(adminPage, 'BB-08-02', { suffix: 'b', fullPage: true });
  await adminContext.close();

  recordResult({
    id: 'BB-08-02',
    status: 'lolos',
    files: [fileA, fileB],
    catatan: `Admin menolak bukti transfer booking ${bookingCode} beserta alasan, status booking kembali ke Menunggu Pembayaran.`,
  });
});
