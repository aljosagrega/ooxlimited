import { redirect, notFound } from "next/navigation";
import { requireAuth } from "@/lib/session";
import { getSchema } from "@/lib/adminSchema";
import { getRow, getRefOptions } from "@/lib/adminCollections";
import { getLanguages } from "@/lib/languages";
import AdminShell from "@/components/admin/AdminShell";
import SchemaForm from "@/components/admin/SchemaForm";

export default async function EditRecordPage({ params }: { params: Promise<{ collection: string; id: string }> }) {
  const user = await requireAuth();
  if (!user) redirect("/admin/login");

  const { collection, id } = await params;
  const schema = getSchema(collection);
  if (!schema) notFound();

  const record = getRow(collection, Number(id));
  if (!record) notFound();

  return (
    <AdminShell>
      <SchemaForm schema={schema} record={record} locales={getLanguages().map((l) => l.code)} refOptions={getRefOptions(schema)} />
    </AdminShell>
  );
}
