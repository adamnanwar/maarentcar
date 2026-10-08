import { chromium, type FullConfig } from '@playwright/test';
import fs from 'node:fs';
import path from 'node:path';
import { resetState } from './state';
import { ENV } from './env';

const AUTH_DIR = path.join(__dirname, '..', '.auth');
export const ADMIN_STATE = path.join(AUTH_DIR, 'admin.json');
export const CUSTOMER_STATE = path.join(AUTH_DIR, 'customer.json');

async function saveLoggedInState(baseURL: string, email: string, password: string, outFile: string) {
  const browser = await chromium.launch();
  const context = await browser.newContext({ baseURL });
  const page = await context.newPage();
  await page.goto('/login', { waitUntil: 'networkidle' });
  await page.locator('#email').fill(email);
  await page.locator('#password').fill(password);
  await page.locator('button[type="submit"]').click();
  await page.waitForURL((url) => !url.pathname.includes('/login'), { timeout: 15_000 });
  await context.storageState({ path: outFile });
  await browser.close();
}

export default async function globalSetup(config: FullConfig): Promise<void> {
  const baseURL = config.projects[0]?.use?.baseURL ?? ENV.baseURL;

  try {
    const res = await fetch(baseURL);
    if (!res.ok && res.status !== 404) throw new Error(`Status ${res.status}`);
  } catch (err) {
    throw new Error(
      `Tidak bisa menghubungi ${baseURL}. Pastikan "php artisan serve" dan "npm run dev" berjalan sebelum menjalankan suite ini.\n${err}`,
    );
  }

  resetState();

  fs.mkdirSync(AUTH_DIR, { recursive: true });
  await saveLoggedInState(baseURL, ENV.adminEmail(), ENV.adminPassword(), ADMIN_STATE);
  await saveLoggedInState(baseURL, ENV.customerEmail(), ENV.customerPassword(), CUSTOMER_STATE);
}
