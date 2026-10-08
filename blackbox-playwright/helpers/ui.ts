import fs from 'node:fs';
import path from 'node:path';
import type { Page } from '@playwright/test';
import { expect } from '@playwright/test';
import { ENV } from './env';

export async function login(page: Page, email: string, password: string): Promise<void> {
  await page.goto('/login', { waitUntil: 'networkidle' });
  await page.locator('#email').fill(email);
  await page.locator('#password').fill(password);
  await page.locator('button[type="submit"]').click();
  await page.waitForURL((url) => !url.pathname.includes('/login'), { timeout: 15_000 }).catch(() => {});
  await page.waitForLoadState('networkidle').catch(() => {});
}

export async function logout(page: Page): Promise<void> {
  // Ada dua tombol "Keluar" di DOM (menu desktop & menu mobile), hanya satu yang
  // benar-benar terlihat sesuai lebar viewport saat ini.
  await page.locator('button:visible').filter({ hasText: 'Keluar' }).first().click();
  await page.waitForLoadState('networkidle').catch(() => {});
}

/** Tunggu dialog konfirmasi custom (ConfirmDialog.vue) terbuka lalu klik tombol konfirmasi/batal di dalamnya. */
export async function confirmDialog(page: Page, buttonName: string): Promise<void> {
  const dialog = page.getByRole('dialog');
  await expect(dialog).toBeVisible();
  await dialog.getByRole('button', { name: buttonName, exact: true }).click();
}

/** Tunggu toast flash (sukses/error) berisi teks tertentu, lalu kembalikan locator-nya. */
export function flashMessage(page: Page, textSubstring: string) {
  return page.getByText(textSubstring, { exact: false }).first();
}

export function uniqueEmail(prefix: string): string {
  return `${prefix}.${Date.now()}@example.com`;
}

export function uniqueSuffix(): string {
  return String(Date.now()).slice(-8);
}

/** Baca tautan reset kata sandi (/reset-password/...) paling baru dari storage/logs/laravel.log. */
export function readLatestResetLink(): string {
  const logPath = path.join(__dirname, '..', ENV.laravelLogPath);
  if (!fs.existsSync(logPath)) {
    throw new Error(`Berkas log tidak ditemukan: ${logPath}. Pastikan MAIL_MAILER=log di .env Laravel.`);
  }
  const content = fs.readFileSync(logPath, 'utf-8');
  const matches = [...content.matchAll(/https?:\/\/[^\s"'<>]*\/reset-password\/[^\s"'<>]+/g)];
  if (!matches.length) {
    throw new Error('Tidak menemukan tautan reset password di log. Jalankan skenario kirim link reset terlebih dahulu.');
  }
  return matches[matches.length - 1][0].replace(/&amp;/g, '&');
}

export const VALID_IMAGE = path.join(__dirname, '..', '..', 'public', 'logo.png');
export const INVALID_FILE = path.join(__dirname, '..', 'fixtures', 'invalid.txt');

export { ENV };
