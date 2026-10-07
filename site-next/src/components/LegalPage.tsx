import type { Metadata } from "next";
import FrozenView from "@/components/FrozenView";
import FrozenBodyClass from "@/components/FrozenBodyClass";
import { getFrozenByKey } from "@/lib/frozen";
import { applyChromePatch } from "@/lib/chromePatch";
import { getSiteSettings } from "@/lib/content";
import { renderLegal, LEGAL_SHELL_KEY } from "@/lib/legalRender";
import type { LegalDoc } from "@/lib/legalContent";

const SITE_URL = process.env.SITE_URL || "https://ooxlimited.com";

/** Metadata for a legal page served at `path` (with trailing slash). */
export function legalMetadata(doc: LegalDoc, path: string): Metadata {
  const title = `${doc.title} – ${getSiteSettings().title}`;
  const url = `${SITE_URL}${path}`;
  return {
    title,
    description: doc.metaDescription,
    alternates: { canonical: url },
    openGraph: { title, description: doc.metaDescription, url, type: "website" },
  };
}

/** A legal document rendered inside the site's real header and footer. */
export default function LegalPage({ doc }: { doc: LegalDoc }) {
  const frozen = getFrozenByKey(LEGAL_SHELL_KEY);
  const body = frozen ? renderLegal(applyChromePatch(frozen.bodyHtml), doc) : null;

  // No shell on disk: still serve the text — these URLs are linked from the
  // app stores and must never be blank.
  if (!frozen || !body) {
    return (
      <main className="oox-legal" style={{ maxWidth: 820, margin: "0 auto", padding: "64px 24px", fontFamily: "sans-serif" }}>
        <h1>{doc.title}</h1>
        <p>Last updated: {doc.updated}</p>
        <div dangerouslySetInnerHTML={{ __html: doc.html }} />
      </main>
    );
  }

  return (
    <>
      <FrozenBodyClass className={frozen.bodyClass} lang={frozen.lang} />
      <FrozenView frozenKey={LEGAL_SHELL_KEY} patchedBody={body} />
    </>
  );
}
