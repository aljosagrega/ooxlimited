"use client";

import { useEffect } from "react";

/**
 * Rebinds the frozen WordPress forms to the Next API routes:
 *   - Contact Form 7  (form.wpcf7-form)        -> POST /api/contact
 *   - Newsletter / mc4wp signup                -> POST /api/newsletter
 * The original plugin JS is stripped at freeze time (see freeze.ts), so these
 * are the only submit handlers.
 */
export default function FrozenForms() {
  useEffect(() => {
    const root = document.getElementById("frozen-root");
    if (!root) return;
    const cleanups: (() => void)[] = [];

    // ---- Contact Form 7 ----------------------------------------------------
    root.querySelectorAll<HTMLFormElement>("form.wpcf7-form").forEach((form) => {
      const onSubmit = async (e: Event) => {
        e.preventDefault();
        e.stopImmediatePropagation();
        const out = ensureResponse(form);
        setState(form, out, "submitting", "Sending…");
        try {
          const res = await fetch("/api/contact/", { method: "POST", body: new FormData(form) });
          const data = await res.json().catch(() => ({}));
          if (res.ok) {
            setState(form, out, "sent", data.message || "Thanks — your message has been sent.");
            form.reset();
          } else {
            setState(form, out, "failed", data.error || "Something went wrong. Please try again.");
          }
        } catch {
          setState(form, out, "failed", "Network error. Please try again.");
        }
      };
      form.addEventListener("submit", onSubmit, true);
      cleanups.push(() => form.removeEventListener("submit", onSubmit, true));
    });

    // ---- Newsletter / mc4wp ---------------------------------------------------
    const newsletterForms = new Set<HTMLFormElement>();
    root
      .querySelectorAll<HTMLFormElement>(
        'form.tnp-form, form.mc4wp-form, form[action*="na=s"], .mc4wp-form form',
      )
      .forEach((f) => newsletterForms.add(f));
    root.querySelectorAll<HTMLInputElement>('input[name="EMAIL"], input[name="ne"]').forEach((inp) => {
      const f = inp.closest("form");
      if (f) newsletterForms.add(f as HTMLFormElement);
    });

    newsletterForms.forEach((form) => {
      const onSubmit = async (e: Event) => {
        e.preventDefault();
        e.stopImmediatePropagation();
        const out = ensureResponse(form);
        setState(form, out, "submitting", "Subscribing…");
        const fd = new FormData(form);
        const email =
          (fd.get("email") as string) ||
          (fd.get("EMAIL") as string) ||
          (fd.get("ne") as string) ||
          "";
        try {
          const res = await fetch("/api/newsletter/", {
            method: "POST",
            headers: { "Content-Type": "application/json" },
            body: JSON.stringify({ email }),
          });
          const data = await res.json().catch(() => ({}));
          setState(
            form,
            out,
            res.ok ? "sent" : "failed",
            (res.ok ? data.message : data.error) || "Please try again.",
          );
          if (res.ok) form.reset();
        } catch {
          setState(form, out, "failed", "Network error. Please try again.");
        }
      };
      form.addEventListener("submit", onSubmit, true);
      cleanups.push(() => form.removeEventListener("submit", onSubmit, true));
    });

    return () => cleanups.forEach((fn) => fn());
  }, []);

  return null;
}

function ensureResponse(form: HTMLFormElement): HTMLElement {
  let out = form.querySelector<HTMLElement>(".wpcf7-response-output, .oox-form-response");
  if (!out) {
    out = document.createElement("div");
    out.className = "oox-form-response";
    out.setAttribute("aria-live", "polite");
    out.style.marginTop = "12px";
    form.appendChild(out);
  }
  return out;
}

function setState(
  form: HTMLFormElement,
  out: HTMLElement,
  state: "submitting" | "sent" | "failed",
  msg: string,
) {
  form.dataset.status = state === "sent" ? "sent" : state === "failed" ? "invalid" : "submitting";
  out.textContent = msg;
  out.classList.toggle("wpcf7-mail-sent-ok", state === "sent");
  out.classList.toggle("wpcf7-validation-errors", state === "failed");
  const btn = form.querySelector<HTMLButtonElement>('button[type="submit"], input[type="submit"]');
  if (btn) btn.disabled = state === "submitting";
}
