/**
 * Pure SEO scoring — no React, safe on the server (dashboard stats, /admin/seo)
 * and the client (form checklist, list badges). One implementation, two callers.
 */

export type SeoStatus = "good" | "warn" | "bad" | "empty";
/**
 * `hint` explains the rule (shown next to the check in the editor); `advice` is
 * the imperative fix, shown in the /admin/seo Recommendations column. Every
 * non-`good` item carries one, so the score and the advice can never disagree —
 * they are derived from the same pass.
 */
export interface SeoItem { label: string; status: SeoStatus; hint: string; detail?: string; advice?: string }

export interface SeoScoreInput {
  effectiveTitle: string;
  effectiveDescription: string;
  slug: string;
  bodyWordCount: number;
  hasImage?: boolean;
  imageLabel?: string;
  noindex?: boolean;
  /**
   * Word-count expectations for this kind of content. An article is thin under
   * 600 words; a contact or team page is complete at 200 and must not be nagged
   * against a blog target it will never meet.
   */
  contentTarget?: { thin: number; aim: number };
}

export const CONTENT_TARGET_ARTICLE = { thin: 600, aim: 800 };
export const CONTENT_TARGET_PAGE = { thin: 100, aim: 300 };

export function stripHtmlText(html: string): string {
  return (html || "").replace(/<[^>]+>/g, " ").replace(/&[a-zA-Z#0-9]+;/g, " ").replace(/\s+/g, " ").trim();
}

export function wordCount(html: string): number {
  const t = stripHtmlText(html);
  return t ? t.split(" ").filter(Boolean).length : 0;
}

export function computeSeoItems({
  effectiveTitle, effectiveDescription, slug, bodyWordCount, hasImage, imageLabel = "Cover image",
  contentTarget = CONTENT_TARGET_ARTICLE,
}: SeoScoreInput): SeoItem[] {
  const tLen = effectiveTitle.trim().length;
  const dLen = effectiveDescription.trim().length;
  const slugOk = !slug || /^[a-z0-9]+(-[a-z0-9]+)*$/.test(slug);

  return [
    tLen === 0
      ? { label: "Title", status: "empty", hint: "Required — lead with the main keyword", advice: "Add a title" }
      : tLen < 30 ? { label: "Title", status: "warn", hint: "Short — aim for 50–60 chars", detail: `${tLen}`, advice: `Title is short (${tLen} chars) — aim for 50–60` }
      : tLen <= 60 ? { label: "Title", status: "good", hint: "Good length", detail: `${tLen} chars` }
      : { label: "Title", status: tLen <= 70 ? "warn" : "bad", hint: "Google truncates titles at ~60 chars", detail: `${tLen} chars`, advice: `Title is ${tLen} chars — trim to 60 or fewer` },
    dLen === 0
      ? { label: "Description", status: "empty", hint: "Shown in search results — include the keyword", advice: "Add a meta description" }
      : dLen < 120 ? { label: "Description", status: "warn", hint: "Aim for 120–160 chars", detail: `${dLen}`, advice: `Description is short (${dLen} chars) — aim for 120–160` }
      : dLen <= 160 ? { label: "Description", status: "good", hint: "Good length", detail: `${dLen} chars` }
      : { label: "Description", status: "bad", hint: "Google cuts at ~160 chars", detail: `${dLen} chars`, advice: `Description is ${dLen} chars — trim to 160 or fewer` },
    !slug ? { label: "URL slug", status: "warn", hint: "Will auto-generate from the title", advice: "Set a URL slug" }
      : !slugOk ? { label: "URL slug", status: "bad", hint: "Lowercase letters, numbers and hyphens only", advice: "Fix the slug — lowercase letters, numbers and hyphens only" }
      : { label: "URL slug", status: "good", hint: "Clean URL" },
    bodyWordCount === 0 ? { label: "Content length", status: "empty", hint: `Write the body — aim for ${contentTarget.aim}+ words`, advice: `Write the body — aim for ${contentTarget.aim}+ words` }
      : bodyWordCount < contentTarget.thin ? { label: "Content length", status: "warn", hint: `Thin — aim for ${contentTarget.aim}+ words`, detail: `${bodyWordCount} words`, advice: `Body is thin (${bodyWordCount} words) — aim for ${contentTarget.aim}+` }
      : { label: "Content length", status: "good", hint: "Good length", detail: `${bodyWordCount} words` },
    ...(hasImage === undefined ? [] : hasImage
      ? [{ label: imageLabel, status: "good" as SeoStatus, hint: "Set" }]
      : [{ label: imageLabel, status: "empty" as SeoStatus, hint: "Boosts click-through in search + social", advice: `Add a ${imageLabel.toLowerCase()}` }]),
  ];
}

/**
 * The fix list for whatever isn't `good`, in check order. The /admin/seo report
 * renders this verbatim — never re-derive advice from the score, or the two
 * drift apart (a page could score 2/5 and still be told "Looks good").
 */
export function seoAdvice(input: SeoScoreInput): string[] {
  return computeSeoItems(input)
    .filter((i) => i.status !== "good")
    .map((i) => i.advice)
    .filter((a): a is string => !!a);
}

export function computeSeoScore(input: SeoScoreInput): { good: number; total: number; issues: number } {
  const items = computeSeoItems(input);
  return {
    good: items.filter((i) => i.status === "good").length,
    total: items.length,
    issues: items.filter((i) => i.status === "bad" || i.status === "empty").length,
  };
}

export function seoScoreColor(good: number, total: number): string {
  const ratio = total === 0 ? 0 : good / total;
  return ratio >= 0.8 ? "#10b981" : ratio >= 0.4 ? "#f59e0b" : "#f87171";
}

export type SeoBucket = "good" | "ok" | "poor";
export function seoBucket(good: number, total: number): SeoBucket {
  const ratio = total === 0 ? 0 : good / total;
  return ratio >= 0.8 ? "good" : ratio >= 0.4 ? "ok" : "poor";
}
