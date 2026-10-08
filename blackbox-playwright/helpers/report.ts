import fs from 'node:fs';
import path from 'node:path';

export const BUKTI_DIR = path.join(__dirname, '..', '..', 'bukti');

export type Status = 'lolos' | 'gagal' | 'perlu-foto-manual';

export interface ResultEntry {
  id: string;
  status: Status;
  files: string[];
  catatan: string;
}

const MANIFEST_FILE = path.join(BUKTI_DIR, 'manifest.json');
const HASIL_FILE = path.join(BUKTI_DIR, 'hasil.md');

function readManifest(): ResultEntry[] {
  if (!fs.existsSync(MANIFEST_FILE)) return [];
  try {
    return JSON.parse(fs.readFileSync(MANIFEST_FILE, 'utf-8'));
  } catch {
    return [];
  }
}

function sortById(entries: ResultEntry[]): ResultEntry[] {
  return [...entries].sort((a, b) => a.id.localeCompare(b.id, 'en', { numeric: true }));
}

function writeHasilMd(entries: ResultEntry[]): void {
  const sorted = sortById(entries);
  const lolos = sorted.filter((e) => e.status === 'lolos').length;
  const gagal = sorted.filter((e) => e.status === 'gagal').length;
  const manual = sorted.filter((e) => e.status === 'perlu-foto-manual').length;

  const lines: string[] = [];
  lines.push('# Hasil Pengujian Blackbox — We Rent Car');
  lines.push('');
  lines.push(`Dihasilkan otomatis oleh suite Playwright pada ${new Date().toISOString()}.`);
  lines.push('');
  lines.push(`Total skenario: **${sorted.length}** — Lolos: **${lolos}** · Gagal: **${gagal}** · Perlu foto manual: **${manual}**`);
  lines.push('');
  lines.push('| ID | Status | Berkas | Catatan |');
  lines.push('|---|---|---|---|');
  for (const entry of sorted) {
    const statusLabel = entry.status === 'lolos' ? 'Lolos' : entry.status === 'gagal' ? 'GAGAL' : 'Perlu foto manual';
    const files = entry.files.map((f) => `\`${f}\``).join(', ') || '-';
    const catatan = (entry.catatan || '-').replace(/\|/g, '\\|').replace(/\n/g, ' ');
    lines.push(`| ${entry.id} | ${statusLabel} | ${files} | ${catatan} |`);
  }
  lines.push('');
  fs.writeFileSync(HASIL_FILE, lines.join('\n'), 'utf-8');
}

/** Catat hasil satu skenario BB-xx-xx. Aman dipanggil berkali-kali (idempotent per id). */
export function recordResult(entry: ResultEntry): void {
  fs.mkdirSync(BUKTI_DIR, { recursive: true });
  const entries = readManifest().filter((e) => e.id !== entry.id);
  entries.push(entry);
  const sorted = sortById(entries);
  fs.writeFileSync(MANIFEST_FILE, JSON.stringify(sorted, null, 2), 'utf-8');
  writeHasilMd(sorted);
}
