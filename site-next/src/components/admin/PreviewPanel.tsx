"use client";

import { useEffect, useMemo, useState } from "react";
import { X, RefreshCw } from "lucide-react";

export interface PreviewData {
  title: string;
  description: string;
  image: string;
  bodyHtml: string;
  author?: string;
}

/* The exact stylesheet stack the frozen blog-post pages load for the article
   body, so the preview matches the live page. Root-absolute → served from
   public/wp-content inside the iframe. omero-child/style.css @imports the
   Poppins / Plus Jakarta webfonts. */
const ARTICLE_CSS = [
  "/wp-content/uploads/elementor/css/custom-frontend.min.css",
  "/wp-content/uploads/elementor/css/post-8.css",
  "/wp-content/themes/omero/style.css",
  "/wp-content/themes/omero/assets/css/base/gutenberg-blocks.css",
  "/wp-content/themes/omero/assets/css/base/elementor.css",
  "/wp-content/themes/omero-child/style.css",
];

/* Trim just the surrounding page furniture — keep every article-body rule. */
const FRAME_CSS = `
  html,body{margin:0;background:#fff;}
  body{padding:44px clamp(20px,6vw,64px);}
  header,footer,.site-header,.site-footer,.elementor-location-header,
  .elementor-location-footer,.omero-single-hero,#wpadminbar{display:none!important;}
  .oox-blog-article{max-width:760px;margin:0 auto!important;float:none!important;width:auto!important;}
  .oox-blog-article img{max-width:100%;height:auto;}
`;

export default function PreviewPanel({
  data,
  label,
  onClose,
  closing,
}: {
  data: PreviewData;
  url: string;
  label: string;
  onClose: () => void;
  closing: boolean;
}) {
  const [nonce, setNonce] = useState(0);

  useEffect(() => {
    const onKey = (e: KeyboardEvent) => e.key === "Escape" && onClose();
    window.addEventListener("keydown", onKey);
    return () => window.removeEventListener("keydown", onKey);
  }, [onClose]);

  const srcDoc = useMemo(() => {
    const links = ARTICLE_CSS.map((h) => `<link rel="stylesheet" href="${h}">`).join("");
    // The WP post body already carries its own <h1>, byline and inline <style>
    // (that's how the live article is styled) — render it verbatim, don't
    // synthesise a heading.
    const body = data.bodyHtml?.trim() || "<p style='color:#9aa'>No content yet.</p>";
    return `<!doctype html><html lang="en"><head><meta charset="utf-8">${links}<style>${FRAME_CSS}</style></head>
<body class="oox-blog single single-post"><article class="oox-blog-article">${body}</article></body></html>`;
  }, [data.bodyHtml]);

  return (
    <>
      <style>{`
        @keyframes oox-pp-in { from { transform: translateX(100%); } to { transform: translateX(0); } }
        @keyframes oox-pp-out { from { transform: translateX(0); } to { transform: translateX(100%); } }
      `}</style>
      <aside
        style={{
          position: "fixed", top: 0, right: 0, bottom: 0, zIndex: 300,
          width: "min(46vw, 620px)",
          background: "var(--at-card)", borderLeft: "1px solid var(--at-border)",
          display: "flex", flexDirection: "column",
          animation: `${closing ? "oox-pp-out" : "oox-pp-in"} 0.28s cubic-bezier(0.4,0,0.2,1) both`,
          boxShadow: "-16px 0 40px -28px rgba(0,0,0,0.22)",
        }}
      >
        <div style={{ display: "flex", alignItems: "center", gap: 10, padding: "12px 14px", borderBottom: "1px solid var(--at-border)", flexShrink: 0 }}>
          <span style={{ width: 7, height: 7, borderRadius: "50%", background: "#10b981", boxShadow: "0 0 7px rgba(16,185,129,0.6)" }} />
          <div style={{ fontSize: 13, fontWeight: 600, color: "var(--at-text)", textTransform: "capitalize" }}>{label} preview</div>
          <div style={{ marginLeft: "auto", display: "flex", gap: 6 }}>
            <button onClick={() => setNonce((n) => n + 1)} className="btn btn-secondary btn-sm" aria-label="Reload"><RefreshCw size={13} /></button>
            <button onClick={onClose} className="btn btn-ghost btn-sm" aria-label="Close preview"><X size={14} /></button>
          </div>
        </div>
        <iframe
          key={nonce}
          srcDoc={srcDoc}
          title="Article preview"
          style={{ flex: 1, width: "100%", border: 0, background: "#fff" }}
        />
        <div style={{ padding: "8px 14px", borderTop: "1px solid var(--at-border)", fontSize: 11, color: "var(--at-faint)", flexShrink: 0 }}>
          Article body, styled as on the site — live as you type. Header, footer and hero are omitted.
        </div>
      </aside>
    </>
  );
}

function escapeHtml(s: string): string {
  return s.replace(/[&<>"']/g, (c) => ({ "&": "&amp;", "<": "&lt;", ">": "&gt;", '"': "&quot;", "'": "&#39;" }[c]!));
}
