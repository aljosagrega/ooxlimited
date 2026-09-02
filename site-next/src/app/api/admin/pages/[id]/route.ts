import { NextRequest, NextResponse } from "next/server";
import { revalidatePath } from "next/cache";
import { requireAuth } from "@/lib/session";
import { getAllPages, savePages } from "@/lib/content";
import { getPagemap } from "@/lib/fieldMap";
import { routeKey } from "@/lib/frozen";
import { sanitizeBodyHtml } from "@/lib/sanitize";
import type { Page } from "@/lib/types";

export async function PUT(request: NextRequest, { params }: { params: Promise<{ id: string }> }) {
  const user = await requireAuth();
  if (!user) return NextResponse.json({ error: "Unauthorized" }, { status: 401 });

  const { id } = await params;
  const body = (await request.json()) as {
    edits?: Record<string, string>;
    metaTitle?: string;
    metaDescription?: string;
    noindex?: boolean;
  };

  const pages = getAllPages();
  const idx = pages.findIndex((p) => p.id === Number(id));
  if (idx === -1) return NextResponse.json({ error: "Page not found" }, { status: 404 });

  const page = pages[idx];
  const pagemap = getPagemap(routeKey(page.path));
  const htmlIds = new Set(pagemap.filter((e) => e.kind === "html").map((e) => e.id));

  // Keep only non-empty edits; sanitise the rich-text ones.
  const edits: Record<string, string> = {};
  for (const [k, v] of Object.entries(body.edits ?? {})) {
    if (v == null || String(v).trim() === "") continue;
    edits[k] = htmlIds.has(k) ? sanitizeBodyHtml(String(v)) : String(v);
  }

  const next: Page = {
    ...page,
    edits: Object.keys(edits).length ? edits : undefined,
    metaTitle: body.metaTitle?.trim() || undefined,
    metaDescription: body.metaDescription?.trim() || undefined,
    noindex: body.noindex || undefined,
  };
  pages[idx] = next;
  savePages(pages);

  revalidatePath("/", "layout");
  revalidatePath(page.path);
  return NextResponse.json({ ok: true, edits: next.edits ?? {} });
}
