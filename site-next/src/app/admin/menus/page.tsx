import { redirect } from "next/navigation";
import { requireAuth } from "@/lib/session";
import { getMenus } from "@/lib/content";
import AdminShell from "@/components/admin/AdminShell";
import MenusClient from "./MenusClient";

export default async function AdminMenusPage() {
  const user = await requireAuth();
  if (!user) redirect("/admin/login");
  return (
    <AdminShell>
      <MenusClient initial={getMenus()} />
    </AdminShell>
  );
}
