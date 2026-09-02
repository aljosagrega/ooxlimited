import { redirect, notFound } from "next/navigation";
import { requireAuth } from "@/lib/session";
import { getPagemap } from "@/lib/fieldMap";
import { getPageEdits } from "@/lib/pageEdits";
import { listEditablePages } from "@/lib/pageList";
import AdminShell from "@/components/admin/AdminShell";
import PageEditor from "@/components/admin/PageEditor";

export default async function EditPage({ params }: { params: Promise<{ key: string }> }) {
  const user = await requireAuth();
  if (!user) redirect("/admin/login");

  const { key } = await params;
  const pagemap = getPagemap(key);
  if (!pagemap.length) notFound();

  const meta = listEditablePages().find((p) => p.key === key);
  const edits = getPageEdits(key);

  return (
    <AdminShell>
      <PageEditor
        routeKey={key}
        routePath={meta?.routePath ?? "/"}
        title={meta?.title ?? key}
        pagemap={pagemap}
        edits={edits}
      />
    </AdminShell>
  );
}
