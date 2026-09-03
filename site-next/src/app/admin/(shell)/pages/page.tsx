import { redirect } from "next/navigation";
import Link from "next/link";
import { requireAuth } from "@/lib/session";
import { listEditablePages, type EditablePage } from "@/lib/pageList";import { ExternalLink } from "lucide-react";

const GROUP_ORDER: EditablePage["group"][] = ["Marketing", "Service pages"];

export default async function AdminPagesList() {
  const user = await requireAuth();
  if (!user) redirect("/admin/login");

  const pages = listEditablePages();
  const groups = GROUP_ORDER.map((g) => [g, pages.filter((p) => p.group === g)] as const).filter(([, l]) => l.length);

  return (
          <div className="admin-content-pad" style={{ maxWidth: 900, display: "flex", flexDirection: "column", gap: 24 }}>
        <div>
          <h1 style={{ fontSize: 22, fontWeight: 600, color: "var(--at-text)", margin: 0 }}>Pages</h1>
          <p style={{ fontSize: 13, color: "var(--at-muted)", marginTop: 4 }}>
            Every public page. Edit its text and images field by field — the layout stays fixed.
          </p>
        </div>

        {groups.map(([group, list]) => (
          <div key={group}>
            <div style={{ fontSize: 11, fontWeight: 600, textTransform: "uppercase", letterSpacing: "0.08em", color: "var(--at-faint)", marginBottom: 8 }}>
              {group} <span style={{ color: "var(--at-faint)", fontWeight: 400 }}>· {list.length}</span>
            </div>
            <div style={{ border: "1px solid var(--at-border)", borderRadius: 14, overflow: "hidden" }}>
              {list.map((p, i) => (
                <div
                  key={p.key}
                  className="news-tbl-row"
                  style={{
                    display: "grid", gridTemplateColumns: "1fr auto auto", gap: 14, alignItems: "center",
                    padding: "12px 16px",
                    borderBottom: i < list.length - 1 ? "1px solid var(--at-border-row)" : "none",
                    background: i % 2 === 0 ? "var(--at-row-even)" : "transparent",
                  }}
                >
                  <div style={{ minWidth: 0 }}>
                    <Link href={`/admin/pages/${p.key}/edit`} style={{ fontSize: 13.5, fontWeight: 500, color: "var(--at-text)", textDecoration: "none" }}>
                      {p.title}
                    </Link>
                    <div style={{ fontSize: 11, color: "var(--at-faint)", fontFamily: "var(--at-font-body)" }}>{p.routePath}</div>
                  </div>
                  <div style={{ fontSize: 11.5, color: p.edited ? "#818cf8" : "var(--at-faint)", whiteSpace: "nowrap" }}>
                    {p.edited ? `${p.edited} edited` : `${p.fields} fields`}
                  </div>
                  <div style={{ display: "flex", gap: 6 }}>
                    <a href={p.routePath} target="_blank" rel="noreferrer" className="btn btn-ghost btn-sm" aria-label="View page"><ExternalLink size={13} /></a>
                    <Link href={`/admin/pages/${p.key}/edit`} className="btn btn-secondary btn-sm">Edit</Link>
                  </div>
                </div>
              ))}
            </div>
          </div>
        ))}
      </div>
  );
}
