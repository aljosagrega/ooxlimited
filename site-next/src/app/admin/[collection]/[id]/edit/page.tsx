import { redirect, notFound } from "next/navigation";
import { requireAuth } from "@/lib/session";
import { getSchema } from "@/lib/adminSchema";
import { getRow, getRefOptions } from "@/lib/adminCollections";
import { getLanguages } from "@/lib/languages";
import { getPagemap } from "@/lib/fieldMap";
import { routeKey } from "@/lib/frozen";
import AdminShell from "@/components/admin/AdminShell";
import SchemaForm from "@/components/admin/SchemaForm";
import PageEditor from "@/components/admin/PageEditor";

export default async function EditRecordPage({ params }: { params: Promise<{ collection: string; id: string }> }) {
  const user = await requireAuth();
  if (!user) redirect("/admin/login");

  const { collection, id } = await params;
  const schema = getSchema(collection);
  if (!schema) notFound();

  const record = getRow(collection, Number(id));
  if (!record) notFound();

  // Pages are frozen marketing pages — edit their content through the pagemap,
  // not raw schema fields.
  if (collection === "pages") {
    const pagemap = getPagemap(routeKey(String(record.path ?? "")));
    return (
      <AdminShell>
        <PageEditor
          record={record as never}
          pagemap={pagemap}
        />
      </AdminShell>
    );
  }

  return (
    <AdminShell>
      <SchemaForm schema={schema} record={record} locales={getLanguages().map((l) => l.code)} refOptions={getRefOptions(schema)} />
    </AdminShell>
  );
}
