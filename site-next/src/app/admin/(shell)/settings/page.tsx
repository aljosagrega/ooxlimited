import { redirect } from "next/navigation";
import { requireAuth } from "@/lib/session";
import { getSiteSettings } from "@/lib/content";import SettingsClient from "./SettingsClient";

export default async function AdminSettingsPage() {
  const user = await requireAuth();
  if (!user) redirect("/admin/login");

  return (
          <SettingsClient username={user.username} initialSettings={getSiteSettings()} />
  );
}
