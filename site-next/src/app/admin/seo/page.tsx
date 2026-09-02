import { redirect } from "next/navigation";
import { requireAuth } from "@/lib/session";
import { getSeoOverview } from "@/lib/adminStats";
import AdminShell from "@/components/admin/AdminShell";
import SeoReportClient from "./SeoReportClient";

export default async function AdminSeoPage() {
  const user = await requireAuth();
  if (!user) redirect("/admin/login");
  return (
    <AdminShell>
      <SeoReportClient rows={getSeoOverview()} />
    </AdminShell>
  );
}
