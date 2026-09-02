import { getIronSession } from "iron-session";
import { cookies } from "next/headers";

export interface SessionData {
  user?: {
    username: string;
    isLoggedIn: boolean;
  };
}

export const sessionOptions = {
  password: process.env.SESSION_SECRET as string,
  cookieName: "oox-admin-session",
  cookieOptions: {
    secure: process.env.NODE_ENV === "production",
    httpOnly: true,
    sameSite: "lax" as const,
    maxAge: 28800, // 8 hours
  },
};

export async function getSession() {
  return getIronSession<SessionData>(await cookies(), sessionOptions);
}

export async function requireAuth() {
  const session = await getSession();
  if (!session.user?.isLoggedIn) return null;
  return session.user;
}
