"use client";

import Link from "next/link";
import { useCallback, useEffect, useState, useSyncExternalStore } from "react";

/**
 * Cookie consent banner, and the only thing that loads Google Analytics.
 *
 * EU ePrivacy rules (enforced in Ireland by the Data Protection Commission)
 * require opt-in consent before analytics cookies are set, with rejecting as
 * easy as accepting. So GA4 is never loaded until the visitor clicks "Accept",
 * and the choice is kept for 12 months, after which we ask again. The footer's
 * "Cookie settings" link (a [data-oox-cookie-settings] button added by
 * chromePatch.ts) reopens the banner; withdrawing deletes the _ga cookies.
 *
 * The cookie table on /privacy/#cookies describes this — keep them in step.
 */

const STORAGE_KEY = "oox_cookie_consent";
const MAX_AGE_MS = 365 * 24 * 60 * 60 * 1000;

type Choice = "granted" | "denied";

function readChoice(): Choice | null {
  try {
    const raw = localStorage.getItem(STORAGE_KEY);
    if (!raw) return null;
    const { v, t } = JSON.parse(raw) as { v: Choice; t: number };
    if ((v !== "granted" && v !== "denied") || Date.now() - t > MAX_AGE_MS) return null;
    return v;
  } catch {
    return null;
  }
}

const CHANGE_EVENT = "oox-cookie-consent";

function saveChoice(v: Choice) {
  try {
    localStorage.setItem(STORAGE_KEY, JSON.stringify({ v, t: Date.now() }));
  } catch {
    /* storage blocked: the banner simply asks again next visit */
  }
  window.dispatchEvent(new Event(CHANGE_EVENT));
}

function subscribe(onChange: () => void) {
  window.addEventListener(CHANGE_EVENT, onChange);
  window.addEventListener("storage", onChange);
  return () => {
    window.removeEventListener(CHANGE_EVENT, onChange);
    window.removeEventListener("storage", onChange);
  };
}

type GtagWindow = Window & { dataLayer?: unknown[]; gtag?: (...args: unknown[]) => void };

function loadGa(gaId: string) {
  const w = window as GtagWindow;
  // Undo a revokeGa() earlier in this page view.
  (window as unknown as Record<string, unknown>)[`ga-disable-${gaId}`] = false;
  if (w.gtag) {
    w.gtag("consent", "update", { analytics_storage: "granted" });
    return;
  }
  w.dataLayer = w.dataLayer || [];
  w.gtag = function gtag() {
    // gtag.js reads the `arguments` object, not an array.
    // eslint-disable-next-line prefer-rest-params
    w.dataLayer!.push(arguments);
  };
  w.gtag("consent", "default", {
    analytics_storage: "granted",
    ad_storage: "denied",
    ad_user_data: "denied",
    ad_personalization: "denied",
  });
  w.gtag("js", new Date());
  w.gtag("config", gaId);
  const el = document.createElement("script");
  el.src = `https://www.googletagmanager.com/gtag/js?id=${encodeURIComponent(gaId)}`;
  el.async = true;
  document.head.appendChild(el);
}

function revokeGa(gaId: string) {
  const w = window as GtagWindow;
  // gtag's documented per-property kill switch.
  (window as unknown as Record<string, unknown>)[`ga-disable-${gaId}`] = true;
  w.gtag?.("consent", "update", { analytics_storage: "denied" });
  // Remove the _ga cookies on this host and its parent domain.
  const host = location.hostname;
  const domains = ["", host, `.${host}`, `.${host.split(".").slice(-2).join(".")}`];
  for (const c of document.cookie.split(";")) {
    const name = c.split("=")[0].trim();
    if (name !== "_ga" && !name.startsWith("_ga_") && name !== "_gid") continue;
    for (const d of domains) {
      document.cookie = `${name}=; Max-Age=0; path=/${d ? `; domain=${d}` : ""}`;
    }
  }
}

export default function CookieConsent({ gaId }: { gaId?: string }) {
  // "ssr" on the server and during hydration, so the banner never renders
  // into the static HTML — it appears once the stored choice can be read.
  const choice = useSyncExternalStore<Choice | null | "ssr">(subscribe, readChoice, () => "ssr");
  const [reopened, setReopened] = useState(false);
  const open = reopened || choice === null;

  useEffect(() => {
    if (choice === "granted" && gaId) loadGa(gaId);
  }, [choice, gaId]);

  // Footer "Cookie settings" link — delegated, since the footer is frozen HTML.
  useEffect(() => {
    const onClick = (e: MouseEvent) => {
      const t = e.target as Element | null;
      if (t?.closest?.("[data-oox-cookie-settings]")) {
        e.preventDefault();
        setReopened(true);
      }
    };
    document.addEventListener("click", onClick);
    return () => document.removeEventListener("click", onClick);
  }, []);

  const decide = useCallback(
    (v: Choice) => {
      const previous = readChoice();
      saveChoice(v);
      setReopened(false);
      if (gaId && v === "denied" && previous === "granted") revokeGa(gaId);
    },
    [gaId],
  );

  if (!open) return null;

  return (
    <div className="oox-cc" role="dialog" aria-modal="false" aria-labelledby="oox-cc-title" aria-describedby="oox-cc-text">
      <style>{CSS}</style>
      <p id="oox-cc-title" className="oox-cc__title">We value your privacy</p>
      <p id="oox-cc-text" className="oox-cc__text">
        We use essential storage to run this site and, with your consent, Google Analytics cookies to
        understand how it is used. You can change your choice at any time via &ldquo;Cookie settings&rdquo; in
        the footer. <Link href="/privacy/#cookies">Learn more</Link>
      </p>
      <div className="oox-cc__actions">
        <button type="button" className="oox-cc__btn" onClick={() => decide("denied")}>
          Reject
        </button>
        <button type="button" className="oox-cc__btn" onClick={() => decide("granted")}>
          Accept
        </button>
      </div>
    </div>
  );
}

// Accept and Reject are styled identically on purpose: the DPC treats a
// visually favoured "Accept" as nudging, which invalidates the consent.
const CSS = `
.oox-cc {
  position: fixed;
  z-index: 100000;
  left: 16px;
  right: 16px;
  bottom: 16px;
  max-width: 560px;
  margin: 0 auto;
  padding: 20px 22px;
  background: #15171F;
  color: #EEF3FA;
  border-radius: 14px;
  box-shadow: 0 12px 40px rgba(0, 0, 0, 0.35);
  font-family: "Plus Jakarta Sans", system-ui, sans-serif;
  font-size: 14px;
  line-height: 1.6;
}
.oox-cc__title {
  margin: 0 0 6px;
  font-family: "Poppins", system-ui, sans-serif;
  font-weight: 700;
  font-size: 16px;
  color: #FFFFFF;
}
.oox-cc__text { margin: 0 0 16px; color: rgba(238, 243, 250, 0.85); }
.oox-cc__text a { color: #B7A6FF; text-decoration: underline; text-underline-offset: 2px; }
.oox-cc__actions { display: flex; gap: 12px; }
.oox-cc__btn {
  flex: 1;
  padding: 11px 16px;
  border: 0;
  border-radius: 10px;
  background: #7B5CFF;
  color: #FFFFFF;
  font-family: "Poppins", system-ui, sans-serif;
  font-weight: 600;
  font-size: 15px;
  cursor: pointer;
  transition: background-color 0.2s;
}
.oox-cc__btn:hover { background: #6A4BEE; }
.oox-cc__btn:focus-visible { outline: 2px solid #FFFFFF; outline-offset: 2px; }
`;
