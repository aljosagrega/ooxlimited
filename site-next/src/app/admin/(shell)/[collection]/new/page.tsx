import { redirect, notFound } from "next/navigation";
import { requireAuth } from "@/lib/session";
import { getSchema } from "@/lib/adminSchema";
import { getRefOptions } from "@/lib/adminCollections";
import { getLanguages } from "@/lib/languages";import SchemaForm from "@/components/admin/SchemaForm";

export default async function NewRecordPage({ params }: { params: Promise<{ collection: string }> }) {
  const user = await requireAuth();
  if (!user) redirect("/admin/login");

  const { collection } = await params;
  const schema = getSchema(collection);
  if (!schema || schema.noCreate) notFound();

  return (
          <SchemaForm schema={schema} locales={getLanguages().map((l) => l.code)} refOptions={getRefOptions(schema)} />
  );
}
