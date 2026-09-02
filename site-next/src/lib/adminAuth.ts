import "server-only";
import fs from "fs";
import path from "path";
import bcrypt from "bcryptjs";

interface AdminAuthFile {
  username: string;
  passwordHash: string;
  updatedAt: string;
}

const DATA_FILE = path.join(process.cwd(), "src/data/adminAuth.json");

function fsRead(): AdminAuthFile | null {
  try {
    return JSON.parse(fs.readFileSync(DATA_FILE, "utf-8")) as AdminAuthFile;
  } catch {
    return null;
  }
}

function fsWrite(data: AdminAuthFile): void {
  fs.mkdirSync(path.dirname(DATA_FILE), { recursive: true });
  fs.writeFileSync(DATA_FILE, JSON.stringify(data, null, 2), "utf-8");
}

// Lazily bootstraps src/data/adminAuth.json from ADMIN_USERNAME/ADMIN_PASSWORD
// the first time credentials are needed and no hashed file exists yet — so
// env vars remain the disaster-recovery path (delete adminAuth.json on the
// server to reset to whatever's in .env) while the file is the live source
// of truth once it exists, editable from /admin/settings without touching
// the server's .env again.
function getOrBootstrap(): AdminAuthFile | null {
  const existing = fsRead();
  if (existing) return existing;

  const envUsername = process.env.ADMIN_USERNAME;
  const envPassword = process.env.ADMIN_PASSWORD;
  if (!envUsername || !envPassword) return null;

  const bootstrapped: AdminAuthFile = {
    username: envUsername,
    passwordHash: bcrypt.hashSync(envPassword, 10),
    updatedAt: new Date().toISOString(),
  };
  fsWrite(bootstrapped);
  return bootstrapped;
}

export async function verifyAdminCredentials(username: string, password: string): Promise<boolean> {
  const auth = getOrBootstrap();
  if (!auth) return false;
  if (username !== auth.username) return false;
  return bcrypt.compare(password, auth.passwordHash);
}

export async function getAdminUsername(): Promise<string | null> {
  return getOrBootstrap()?.username ?? null;
}

export async function updateAdminCredentials(
  currentPassword: string,
  next: { username?: string; password?: string }
): Promise<{ ok: true } | { ok: false; error: string }> {
  const auth = getOrBootstrap();
  if (!auth) return { ok: false, error: "Server misconfiguration — no admin credentials set" };

  const valid = await bcrypt.compare(currentPassword, auth.passwordHash);
  if (!valid) return { ok: false, error: "Current password is incorrect" };

  const username = next.username?.trim() || auth.username;
  const passwordHash = next.password ? bcrypt.hashSync(next.password, 10) : auth.passwordHash;

  fsWrite({ username, passwordHash, updatedAt: new Date().toISOString() });
  return { ok: true };
}
