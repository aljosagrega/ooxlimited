import fs from "fs";
import path from "path";
import type { Metadata } from "next";
import FrozenView from "@/components/FrozenView";
import FrozenBodyClass from "@/components/FrozenBodyClass";
import { getFrozenByKey } from "@/lib/frozen";
import { applyChromePatch } from "@/lib/chromePatch";
import { render404, NOT_FOUND_ASSET_SRC } from "@/lib/notFoundRender";

/**
 * 404 page. Next's default is an unstyled page with none of the site's chrome;
 * this renders the designed 404 inside a frozen shell so the visitor still gets
 * the real header, footer and navigation to escape with.
 *
 * SHELL_KEY is any frozen page — only its header, footer and stylesheet stack
 * are kept, the content region is replaced. contact-us is the smallest one.
 */
const SHELL_KEY = "contact-us";

export const metadata: Metadata = {
  title: "Page not found",
  robots: { index: false, follow: true },
};

export default function NotFound() {
  const frozen = getFrozenByKey(SHELL_KEY);
  const hasAsset = fs.existsSync(path.join(process.cwd(), "public", NOT_FOUND_ASSET_SRC));
  const body = frozen ? render404(applyChromePatch(frozen.bodyHtml), hasAsset) : null;

  // No shell on disk: fall back to the block on its own rather than 500.
  if (!frozen || !body) {
    return (
      <main style={{ padding: "120px 24px", textAlign: "center", fontFamily: "Poppins, sans-serif" }}>
        <h1>Oops! Page is not found</h1>
        <p>We&apos;re not being able to find the page you&apos;re looking for</p>
        <a href="/">go back</a>
      </main>
    );
  }

  return (
    <>
      <FrozenBodyClass className={frozen.bodyClass} lang={frozen.lang} />
      <FrozenView frozenKey={SHELL_KEY} patchedBody={body} />
    </>
  );
}
