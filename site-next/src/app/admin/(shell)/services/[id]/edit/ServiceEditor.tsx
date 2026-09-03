"use client";

import { useState } from "react";
import Link from "next/link";
import { ExternalLink, Layers, FileText } from "lucide-react";
import SchemaForm from "@/components/admin/SchemaForm";
import PageEditor from "@/components/admin/PageEditor";
import type { CollectionSchema } from "@/lib/adminSchema";
import type { PagemapEntry } from "@/lib/fieldMap";

/**
 * One screen for everything about a service:
 *   Details      — name, slug, order, excerpt, thumbnail, search appearance (services.json)
 *   Page content — the text & images on /service/<slug>/, field by field (pageEdits.json)
 */
export default function ServiceEditor({
  schema,
  record,
  refOptions,
  routeKey,
  routePath,
  pagemap,
  pageEdits,
}: {
  schema: CollectionSchema;
  record: Record<string, unknown>;
  refOptions: Record<string, { value: number; label: string }[]>;
  routeKey: string;
  routePath: string;
  pagemap: PagemapEntry[];
  pageEdits: Record<string, string>;
}) {
  const [tab, setTab] = useState<"details" | "content">("details");
  const title = String(record.title ?? "Service");

  return (
    <div className="admin-content-pad" style={{ maxWidth: 860, display: "flex", flexDirection: "column", gap: 18 }}>
      <div style={{ display: "flex", alignItems: "center", justifyContent: "space-between", flexWrap: "wrap", gap: 12 }}>
        <h1 style={{ fontSize: 20, fontWeight: 600, color: "var(--at-text)", margin: 0 }}>
          {title}
          <span style={{ color: "var(--at-faint)", fontWeight: 400, fontSize: 13 }}> — {routePath}</span>
        </h1>
        <a href={routePath} target="_blank" rel="noreferrer" className="btn btn-secondary btn-sm">
          <ExternalLink size={13} /> View page
        </a>
      </div>

      <div className="at-tabs">
        <button type="button" className={`at-tab${tab === "details" ? " active" : ""}`} onClick={() => setTab("details")}>
          <Layers size={13} /> Details
        </button>
        <button type="button" className={`at-tab${tab === "content" ? " active" : ""}`} onClick={() => setTab("content")}>
          <FileText size={13} /> Page content
          <span style={{ marginLeft: 4, fontSize: 11, color: "var(--at-faint)" }}>{pagemap.length}</span>
        </button>
      </div>

      <div hidden={tab !== "details"}>
        <SchemaForm schema={schema} record={record} locales={["en"]} refOptions={refOptions} embedded />
      </div>
      <div hidden={tab !== "content"}>
        <p style={{ fontSize: 12.5, color: "var(--at-muted)", margin: "0 0 14px" }}>
          The text and images on this service page, field by field. Layout is fixed. The
          &ldquo;Recent posts&rdquo; strip and the site header/footer aren&rsquo;t here — they come from
          other sections.
        </p>
        <PageEditor
          routeKey={routeKey}
          routePath={routePath}
          title={title}
          pagemap={pagemap}
          edits={pageEdits}
          hideSeo
          hideHeader
        />
      </div>

      <div style={{ fontSize: 12, color: "var(--at-faint)" }}>
        <Link href="/admin/services" style={{ color: "var(--at-muted)" }}>&larr; All services</Link>
      </div>
    </div>
  );
}
