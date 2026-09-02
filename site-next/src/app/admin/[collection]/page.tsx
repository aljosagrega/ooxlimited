import { redirect, notFound } from "next/navigation";
import { requireAuth } from "@/lib/session";
import { getSchema } from "@/lib/adminSchema";
import { listRowsForView } from "@/lib/adminCollections";
import AdminShell from "@/components/admin/AdminShell";
import CollectionList from "@/components/admin/CollectionList";

export default async function CollectionListPage({ params }: { params: Promise<{ collection: string }> }) {
  const user = await requireAuth();
  if (!user) redirect("/admin/login");

  const { collection } = await params;
  const schema = getSchema(collection);
  if (!schema) notFound();

  return (
    <AdminShell>
      <CollectionList schema={schema} rows={listRowsForView(schema)} />
    </AdminShell>
  );
}
