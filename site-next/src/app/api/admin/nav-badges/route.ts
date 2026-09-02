import { NextResponse } from "next/server";
import { requireAuth } from "@/lib/session";
import { listRows } from "@/lib/adminCollections";

/** Small counts the admin sidebar shows as badges. Cheap enough to poll on
 *  every navigation. Extend the shape as more sections need a badge. */
export async function GET() {
  const user = await requireAuth();
  if (!user) return NextResponse.json({ error: "Unauthorized" }, { status: 401 });

  const submissions = listRows("submissions").filter((r) => !r.handled).length;

  return NextResponse.json({ submissions });
}
