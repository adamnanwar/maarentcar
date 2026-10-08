import { defineConfig } from '@playwright/test';
import dotenv from 'dotenv';
import path from 'node:path';

dotenv.config({ path: path.join(__dirname, '.env.test') });
dotenv.config({ path: path.join(__dirname, '.env.test.example') });

export default defineConfig({
  testDir: './tests',
  globalSetup: './helpers/global-setup.ts',
  fullyParallel: false,
  workers: 1,
  retries: 0,
  timeout: 45_000,
  expect: { timeout: 8_000 },
  reporter: [
    ['list'],
    ['html', { outputFolder: path.join(__dirname, 'playwright-report'), open: 'never' }],
  ],
  outputDir: path.join(__dirname, 'test-results'),
  use: {
    baseURL: process.env.BASE_URL ?? 'http://127.0.0.1:8000',
    viewport: { width: 1366, height: 768 },
    locale: 'id-ID',
    actionTimeout: 10_000,
    navigationTimeout: 20_000,
    trace: 'retain-on-failure',
    video: 'off',
    screenshot: 'off',
  },
  projects: [{ name: 'chromium', use: {} }],
});
