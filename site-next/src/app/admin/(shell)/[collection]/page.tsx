import { redirect, notFound } from "next/navigation";
import { requireAuth } from "@/lib/session";
import { getSchema } from "@/lib/adminSchema";
import { listRowsForView } from "@/lib/adminCollections";import CollectionList from "@/components/admin/CollectionList";

export default async function CollectionListPage({ params }: { params: Promise<{ collection: string }> }) {
  const user = await requireAuth();
  if (!user) redirect("/admin/login");

  const { collection } = await params;
  const schema = getSchema(collection);
  if (!schema) notFound();

  return (
          <CollectionList schema={schema} rows={listRowsForView(schema)} />
  );
}
