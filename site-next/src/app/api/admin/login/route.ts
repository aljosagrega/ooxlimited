import { NextRequest, NextResponse } from "next/server";
import { getSession } from "@/lib/session";
import { verifyAdminCredentials } from "@/lib/adminAuth";

export async function POST(req: NextRequest) {
  try {
    const { username, password } = await req.json();
    if (!username || !password) {
      return NextResponse.json({ error: "Username and password are required" }, { status: 400 });
    }
    if (!(await verifyAdminCredentials(username, password))) {
      return NextResponse.json({ error: "Invalid credentials" }, { status: 401 });
    }
    const session = await getSession();
    session.user = { username, isLoggedIn: true };
    await session.save();
    return NextResponse.json({ ok: true });
  } catch {
    return NextResponse.json({ error: "Internal server error" }, { status: 500 });
  }
}
