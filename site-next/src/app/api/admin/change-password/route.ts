import { NextRequest, NextResponse } from "next/server";
import { requireAuth } from "@/lib/session";
import { updateAdminCredentials } from "@/lib/adminAuth";

export async function POST(request: NextRequest) {
  const user = await requireAuth();
  if (!user) return NextResponse.json({ error: "Unauthorized" }, { status: 401 });

  const { currentPassword, newUsername, newPassword } = await request.json();

  if (!currentPassword) {
    return NextResponse.json({ error: "Current password is required" }, { status: 400 });
  }
  if (!newUsername?.trim() && !newPassword) {
    return NextResponse.json({ error: "Nothing to change" }, { status: 400 });
  }
  if (newPassword && newPassword.length < 8) {
    return NextResponse.json({ error: "New password must be at least 8 characters" }, { status: 400 });
  }

  const result = await updateAdminCredentials(currentPassword, {
    username: newUsername,
    password: newPassword,
  });
  if (!result.ok) return NextResponse.json({ error: result.error }, { status: 400 });

  return NextResponse.json({ ok: true });
}
