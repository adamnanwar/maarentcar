import fs from 'node:fs';
import type { Page } from '@playwright/test';
import { BUKTI_DIR } from './report';

/**
 * Ambil screenshot dan simpan sebagai bukti/<id><suffix>.png, mis. shoot(page, 'BB-04-01', 'a').
 * Mengembalikan nama berkas (tanpa folder) untuk dicatat di manifest.
 */
export async function shoot(page: Page, id: string, opts: { suffix?: string; fullPage?: boolean } = {}): Promise<string> {
  fs.mkdirSync(BUKTI_DIR, { recursive: true });
  const filename = opts.suffix ? `${id}-${opts.suffix}.png` : `${id}.png`;
  await page.screenshot({ path: `${BUKTI_DIR}/${filename}`, fullPage: opts.fullPage ?? false });
  return filename;
}
