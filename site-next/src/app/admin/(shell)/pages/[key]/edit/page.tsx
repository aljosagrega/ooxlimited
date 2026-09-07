import { redirect, notFound } from "next/navigation";
import { requireAuth } from "@/lib/session";
import { getPagemap } from "@/lib/fieldMap";
import { trimmedPagemapIds } from "@/lib/pageTrims";
import { getPageEdits } from "@/lib/pageEdits";
import { listEditablePages } from "@/lib/pageList";import PageEditor from "@/components/admin/PageEditor";

export default async function EditPage({ params }: { params: Promise<{ key: string }> }) {
  const user = await requireAuth();
  if (!user) redirect("/admin/login");

  const { key } = await params;
  // Only marketing + service pages are edited here; blog articles and team
  // profiles live in their own sections.
  const meta = listEditablePages().find((p) => p.key === key);
  if (!meta) notFound();

  // Fields inside a block removed by pageTrims are dropped: the block is not on
  // the page any more, so offering them would be editing text nobody can see.
  const trimmed = trimmedPagemapIds(key, meta.routePath);
  const pagemap = getPagemap(key).filter((e) => !trimmed.has(e.id));
  if (!pagemap.length) notFound();
  const edits = getPageEdits(key);

  return (
          <PageEditor
        routeKey={key}
        routePath={meta.routePath}
        title={meta.title}
        pagemap={pagemap}
        edits={edits}
      />
  );
}
