/**
 * Pure SEO scoring — no React, safe on the server (dashboard stats, /admin/seo)
 * and the client (form checklist, list badges). One implementation, two callers.
 */

export type SeoStatus = "good" | "warn" | "bad" | "empty";
export interface SeoItem { label: string; status: SeoStatus; hint: string; detail?: string }

export interface SeoScoreInput {
  effectiveTitle: string;
  effectiveDescription: string;
  slug: string;
  bodyWordCount: number;
  hasImage?: boolean;
  imageLabel?: string;
  noindex?: boolean;
}

export function stripHtmlText(html: string): string {
  return (html || "").replace(/<[^>]+>/g, " ").replace(/&[a-zA-Z#0-9]+;/g, " ").replace(/\s+/g, " ").trim();
}

export function wordCount(html: string): number {
  const t = stripHtmlText(html);
  return t ? t.split(" ").filter(Boolean).length : 0;
}

export function computeSeoItems({
  effectiveTitle, effectiveDescription, slug, bodyWordCount, hasImage, imageLabel = "Cover image",
}: SeoScoreInput): SeoItem[] {
  const tLen = effectiveTitle.trim().length;
  const dLen = effectiveDescription.trim().length;
  const slugOk = !slug || /^[a-z0-9]+(-[a-z0-9]+)*$/.test(slug);

  return [
    tLen === 0
      ? { label: "Title", status: "empty", hint: "Required — lead with the main keyword" }
      : tLen < 30 ? { label: "Title", status: "warn", hint: "Short — aim for 50–60 chars", detail: `${tLen}` }
      : tLen <= 60 ? { label: "Title", status: "good", hint: "Good length", detail: `${tLen} chars` }
      : { label: "Title", status: tLen <= 70 ? "warn" : "bad", hint: "Google truncates titles at ~60 chars", detail: `${tLen} chars` },
    dLen === 0
      ? { label: "Description", status: "empty", hint: "Shown in search results — include the keyword" }
      : dLen < 120 ? { label: "Description", status: "warn", hint: "Aim for 120–160 chars", detail: `${dLen}` }
      : dLen <= 160 ? { label: "Description", status: "good", hint: "Good length", detail: `${dLen} chars` }
      : { label: "Description", status: "bad", hint: "Google cuts at ~160 chars", detail: `${dLen} chars` },
    !slug ? { label: "URL slug", status: "warn", hint: "Will auto-generate from the title" }
      : !slugOk ? { label: "URL slug", status: "bad", hint: "Lowercase letters, numbers and hyphens only" }
      : { label: "URL slug", status: "good", hint: "Clean URL" },
    bodyWordCount === 0 ? { label: "Content length", status: "empty", hint: "Write the body — aim for 800+ words" }
      : bodyWordCount < 600 ? { label: "Content length", status: "warn", hint: "Thin — aim for 800+ words", detail: `${bodyWordCount} words` }
      : { label: "Content length", status: "good", hint: "Good length", detail: `${bodyWordCount} words` },
    ...(hasImage === undefined ? [] : hasImage
      ? [{ label: imageLabel, status: "good" as SeoStatus, hint: "Set" }]
      : [{ label: imageLabel, status: "empty" as SeoStatus, hint: "Boosts click-through in search + social" }]),
  ];
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
