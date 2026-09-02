import { NextRequest, NextResponse } from "next/server";
import { revalidatePath } from "next/cache";
import { requireAuth } from "@/lib/session";
import { isCollection, listRows, createRow } from "@/lib/adminCollections";

export async function GET(_req: NextRequest, { params }: { params: Promise<{ collection: string }> }) {
  const user = await requireAuth();
  if (!user) return NextResponse.json({ error: "Unauthorized" }, { status: 401 });

  const { collection } = await params;
  if (!isCollection(collection)) return NextResponse.json({ error: "Unknown collection" }, { status: 404 });
  return NextResponse.json(listRows(collection));
}

export async function POST(req: NextRequest, { params }: { params: Promise<{ collection: string }> }) {
  const user = await requireAuth();
  if (!user) return NextResponse.json({ error: "Unauthorized" }, { status: 401 });

  const { collection } = await params;
  if (!isCollection(collection)) return NextResponse.json({ error: "Unknown collection" }, { status: 404 });

  const body = (await req.json()) as Record<string, unknown>;
  const row = createRow(collection, body);
  revalidatePath("/", "layout");
  return NextResponse.json(row);
}
