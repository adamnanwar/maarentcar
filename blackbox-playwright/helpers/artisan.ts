import { execFileSync } from 'node:child_process';
import fs from 'node:fs';
import os from 'node:os';
import path from 'node:path';

const PROJECT_ROOT = path.join(__dirname, '..', '..');

/**
 * Jalankan satu baris kode PHP lewat "php artisan tinker" di proyek Laravel.
 * Dipakai HANYA untuk menyiapkan data uji (mis. memundurkan batas waktu
 * pembayaran agar tidak perlu menunggu 1 jam sungguhan) - tidak pernah untuk
 * mengubah logika aplikasi. Lihat bagian "Data dan keadaan" pada dokumen
 * test-case-blackbox-playwright.md.
 *
 * Kode PHP ditulis ke berkas sementara lalu di-require, bukan dikirim langsung
 * sebagai argumen command line - pada Windows, backslash pada argumen (mis.
 * "App\Models\Booking") bisa hilang saat diteruskan ke proses "php" lewat
 * shim .bat, sehingga FQCN jadi rusak. Path ke berkas sementara memakai garis
 * miring (/) supaya tidak mengandung backslash sama sekali.
 */
export function runTinker(phpCode: string): string {
  const tmpFile = path.join(os.tmpdir(), `blackbox-tinker-${Date.now()}-${Math.random().toString(36).slice(2)}.php`);
  fs.writeFileSync(tmpFile, `<?php\n${phpCode}\n`, 'utf-8');
  const forwardSlashPath = tmpFile.replace(/\\/g, '/');

  try {
    return execFileSync('php', ['artisan', 'tinker', '--execute', `require '${forwardSlashPath}';`], {
      cwd: PROJECT_ROOT,
      encoding: 'utf-8',
    });
  } finally {
    fs.rmSync(tmpFile, { force: true });
  }
}
