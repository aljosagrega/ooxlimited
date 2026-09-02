import { NextRequest, NextResponse } from "next/server";
import { revalidatePath } from "next/cache";
import { requireAuth } from "@/lib/session";
import { getMenus, saveMenus } from "@/lib/content";
import type { Menus, MenuItem } from "@/lib/types";

export async function GET() {
  const user = await requireAuth();
  if (!user) return NextResponse.json({ error: "Unauthorized" }, { status: 401 });
  return NextResponse.json(getMenus());
}

let nextTmpId = -1;

function clean(items: unknown, depth = 0): MenuItem[] {
  if (!Array.isArray(items)) return [];
  return items
    .map((raw): MenuItem | null => {
      const r = raw as Partial<MenuItem>;
      const label = String(r.label ?? "").trim();
      const url = String(r.url ?? "").trim() || "/";
      if (!label) return null;
      return {
        id: typeof r.id === "number" && r.id > 0 ? r.id : nextTmpId--,
        label,
        url,
        parent: 0,
        children: depth === 0 ? clean(r.children, 1) : [],
      };
    })
    .filter((x): x is MenuItem => x !== null);
}

export async function PUT(request: NextRequest) {
  const user = await requireAuth();
  if (!user) return NextResponse.json({ error: "Unauthorized" }, { status: 401 });

  const body = (await request.json()) as Partial<Menus>;
  nextTmpId = -1;
  const menus: Menus = {
    main: clean(body.main),
    footer: clean(body.footer),
  };
  // re-stamp parent ids
  for (const top of menus.main) for (const c of top.children) c.parent = top.id;

  saveMenus(menus);
  revalidatePath("/", "layout");
  return NextResponse.json(menus);
}
