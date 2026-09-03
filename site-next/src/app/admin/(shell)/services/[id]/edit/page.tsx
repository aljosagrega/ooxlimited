import { redirect, notFound } from "next/navigation";
import { requireAuth } from "@/lib/session";
import { getSchema } from "@/lib/adminSchema";
import { getRow, getRefOptions } from "@/lib/adminCollections";
import { getService } from "@/lib/content";
import { getPagemap } from "@/lib/fieldMap";
import { getPageEdits } from "@/lib/pageEdits";
import { routeKey } from "@/lib/routeKey";
import ServiceEditor from "./ServiceEditor";

/**
 * Overrides the generic /admin/[collection]/[id]/edit for services: one screen
 * with the structured fields *and* the /service/<slug>/ page content, so
 * everything about a service is edited in one place.
 */
export default async function EditServicePage({ params }: { params: Promise<{ id: string }> }) {
  const user = await requireAuth();
  if (!user) redirect("/admin/login");

  const { id } = await params;
  const schema = getSchema("services")!;
  const record = getRow("services", Number(id));
  if (!record) notFound();

  const svc = getService(String(record.slug ?? ""));
  const routePath = svc ? `/service/${svc.slug}/` : "/";
  const key = routeKey(routePath);

  return (
    <ServiceEditor
      schema={schema}
      record={record}
      refOptions={getRefOptions(schema)}
      routeKey={key}
      routePath={routePath}
      pagemap={getPagemap(key)}
      pageEdits={getPageEdits(key)}
    />
  );
}
