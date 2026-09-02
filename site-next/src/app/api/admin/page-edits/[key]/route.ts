import { NextRequest, NextResponse } from "next/server";
import { revalidatePath } from "next/cache";
import { requireAuth } from "@/lib/session";
import { getPagemap } from "@/lib/fieldMap";
import { savePageEdits } from "@/lib/pageEdits";
import { sanitizeBodyHtml } from "@/lib/sanitize";

export async function PUT(request: NextRequest, { params }: { params: Promise<{ key: string }> }) {
  const user = await requireAuth();
  if (!user) return NextResponse.json({ error: "Unauthorized" }, { status: 401 });

  const { key } = await params;
  const body = (await request.json()) as {
    edits?: Record<string, string>;
    seo?: { metaTitle?: string; metaDescription?: string; noindex?: boolean };
    routePath?: string;
  };

  const pagemap = getPagemap(key);
  if (!pagemap.length) return NextResponse.json({ error: "Unknown page" }, { status: 404 });
  const htmlIds = new Set(pagemap.filter((e) => e.kind === "html").map((e) => e.id));

  const clean: Record<string, string> = {};
  for (const [id, v] of Object.entries(body.edits ?? {})) {
    if (v == null || String(v).trim() === "") continue;
    clean[id] = htmlIds.has(id) ? sanitizeBodyHtml(String(v)) : String(v);
  }
  // SEO overrides ride in the same map under reserved keys.
  const seo = body.seo ?? {};
  if (seo.metaTitle?.trim()) clean.__seoTitle = seo.metaTitle.trim();
  if (seo.metaDescription?.trim()) clean.__seoDesc = seo.metaDescription.trim();
  if (seo.noindex) clean.__seoNoindex = "1";

  savePageEdits(key, clean);
  revalidatePath("/", "layout");
  if (body.routePath) revalidatePath(body.routePath);
  return NextResponse.json({ ok: true });
}
