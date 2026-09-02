import "server-only";
import { Resend } from "resend";

export function getResend() {
  return new Resend(process.env.RESEND_API_KEY);
}

export function fromAddress(): string {
  return process.env.RESEND_FROM_EMAIL ?? "OOX Limited <onboarding@resend.dev>";
}

export function isValidEmail(v: string): boolean {
  return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(v);
}
