import fs from 'node:fs';
import path from 'node:path';

const STATE_FILE = path.join(__dirname, '..', '.state.json');

type State = Record<string, unknown>;

function readAll(): State {
  if (!fs.existsSync(STATE_FILE)) return {};
  try {
    return JSON.parse(fs.readFileSync(STATE_FILE, 'utf-8'));
  } catch {
    return {};
  }
}

function writeAll(state: State): void {
  fs.writeFileSync(STATE_FILE, JSON.stringify(state, null, 2), 'utf-8');
}

/** Simpan satu nilai yang perlu dipakai lintas berkas test (mis. kode booking). */
export function saveState(key: string, value: unknown): void {
  const state = readAll();
  state[key] = value;
  writeAll(state);
}

export function loadState<T = unknown>(key: string): T {
  const state = readAll();
  if (!(key in state)) {
    throw new Error(`State "${key}" belum pernah disimpan. Pastikan test sebelumnya sudah berjalan dan berhasil.`);
  }
  return state[key] as T;
}

export function resetState(): void {
  writeAll({});
}
