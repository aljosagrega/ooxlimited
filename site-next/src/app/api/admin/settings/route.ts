import { NextRequest, NextResponse } from "next/server";
import { revalidatePath } from "next/cache";
import { requireAuth } from "@/lib/session";
import { getSiteSettings, saveSiteSettings } from "@/lib/content";
import type { SiteSettings } from "@/lib/types";

export async function GET() {
  const user = await requireAuth();
  if (!user) return NextResponse.json({ error: "Unauthorized" }, { status: 401 });
  return NextResponse.json(getSiteSettings());
}

const ALLOWED: (keyof SiteSettings)[] = [
  "title", "tagline", "contactEmail", "contactRecipients", "mailFromName",
  "mailchimpListId", "gaId", "headScripts", "socialLinks",
];

export async function PUT(request: NextRequest) {
  const user = await requireAuth();
  if (!user) return NextResponse.json({ error: "Unauthorized" }, { status: 401 });

  const body = (await request.json()) as Record<string, unknown>;
  const patch: Record<string, unknown> = {};
  for (const key of ALLOWED) {
    if (key in body) patch[key] = body[key];
  }

  const merged = saveSiteSettings(patch as Partial<SiteSettings>);
  revalidatePath("/", "layout");
  return NextResponse.json(merged);
}
