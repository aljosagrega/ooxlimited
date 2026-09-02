import type { Metadata } from "next";
import Script from "next/script";
import "./globals.css";
import { getSiteSettings } from "@/lib/content";
import ExtraScripts from "@/components/ExtraScripts";

const settings = getSiteSettings();

export const metadata: Metadata = {
  metadataBase: new URL(process.env.SITE_URL || "https://ooxlimited.com"),
  title: { default: settings.title, template: "%s" },
  description: settings.tagline,
};

export default function PublicLayout({ children }: { children: React.ReactNode }) {
  const s = getSiteSettings();
  return (
    <html lang="en-US">
      <body>
        {children}

        {s.gaId && (
          <Script id="ga4" strategy="afterInteractive">{`
            setTimeout(function(){
              window.dataLayer=window.dataLayer||[];function gtag(){dataLayer.push(arguments);}
              gtag('js',new Date());gtag('config','${s.gaId}');
              var el=document.createElement('script');
              el.src='https://www.googletagmanager.com/gtag/js?id=${s.gaId}';el.async=true;
              document.head.appendChild(el);
            },2000);
          `}</Script>
        )}

        {s.headScripts?.trim() && <ExtraScripts html={s.headScripts} />}
      </body>
    </html>
  );
}
