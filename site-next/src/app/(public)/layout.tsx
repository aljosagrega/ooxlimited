import type { Metadata } from "next";
import "./globals.css";
import { getSiteSettings } from "@/lib/content";
import { organizationJsonLd, websiteJsonLd, renderJsonLd } from "@/lib/jsonLd";
import ExtraScripts from "@/components/ExtraScripts";
import CookieConsent from "@/components/CookieConsent";

const settings = getSiteSettings();

export const metadata: Metadata = {
  metadataBase: new URL(process.env.SITE_URL || "https://ooxlimited.com"),
  title: { default: settings.title, template: "%s" },
  description: settings.tagline,
  openGraph: {
    siteName: settings.title,
    locale: "en_US",
    type: "website",
    images: [{ url: "/og-default.png", width: 1200, height: 630 }],
  },
  twitter: { card: "summary_large_image" },
};

export default function PublicLayout({ children }: { children: React.ReactNode }) {
  const s = getSiteSettings();
  return (
    <html lang="en-US">
      {/* <FrozenBodyClass> sets the frozen page's class list on <body> via a
          synchronous inline script before first paint; that runs ahead of
          hydration, so the class is on <body> when React reconciles it. */}
      <body suppressHydrationWarning>
        <script
          type="application/ld+json"
          dangerouslySetInnerHTML={{ __html: renderJsonLd(organizationJsonLd()) }}
        />
        <script
          type="application/ld+json"
          dangerouslySetInnerHTML={{ __html: renderJsonLd(websiteJsonLd()) }}
        />
        {children}

        {/* Loads Google Analytics only after the visitor accepts cookies. */}
        <CookieConsent gaId={s.gaId || undefined} />

        {s.headScripts?.trim() && <ExtraScripts html={s.headScripts} />}
      </body>
    </html>
  );
}
