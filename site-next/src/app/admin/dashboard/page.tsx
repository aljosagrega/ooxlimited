import { redirect } from "next/navigation";
import Link from "next/link";
import { requireAuth } from "@/lib/session";
import { collectionCounts } from "@/lib/adminCollections";
import { getDashboardStats } from "@/lib/adminStats";
import { listEditablePages } from "@/lib/pageList";
import { SCHEMAS } from "@/lib/adminSchema";
import AdminShell from "@/components/admin/AdminShell";
import {
  ChartCard, BarChart, SegmentedBar, HBars, StatList, CountCard, SectionLabel,
} from "@/components/admin/charts";
import { STATUS } from "@/lib/chartPalette";
import { routeKey } from "@/lib/routeKey";

function seoEditHref(kind: string, id: number, url: string): string {
  if (kind === "page") return `/admin/pages/${routeKey(url)}/edit`;
  return `/admin/${kind === "post" ? "posts" : "services"}/${id}/edit`;
}

export default async function AdminDashboardPage() {
  const user = await requireAuth();
  if (!user) redirect("/admin/login");

  const counts = collectionCounts();
  const stats = getDashboardStats();
  const seoTotal = stats.seoBuckets.good + stats.seoBuckets.ok + stats.seoBuckets.poor;
  const editablePages = listEditablePages();
  const pagesEdited = editablePages.filter((p) => p.edited > 0).length;

  return (
    <AdminShell>
      <div className="admin-content-pad" style={{ display: "flex", flexDirection: "column", gap: 28 }}>
        <div>
          <h1 style={{ fontSize: 24, fontWeight: 600, color: "var(--at-text)", margin: 0, letterSpacing: "-0.02em" }}>
            Welcome back, {user.username}
          </h1>
          <p style={{ fontSize: 14, color: "var(--at-muted)", marginTop: 6 }}>
            Everything editable on ooxlimited.com, at a glance.
          </p>
        </div>

        <div style={{ display: "grid", gridTemplateColumns: "repeat(auto-fill, minmax(190px, 1fr))", gap: 14 }}>
          <CountCard
            href="/admin/pages"
            label="Pages"
            total={editablePages.length}
            sub={pagesEdited ? `${pagesEdited} customised` : undefined}
          />
          {Object.values(SCHEMAS).map((s) => {
            const c = counts[s.slug] ?? { total: 0, published: 0 };
            return (
              <CountCard
                key={s.slug}
                href={`/admin/${s.slug}`}
                label={s.label}
                total={c.total}
                sub={
                  s.slug === "submissions" && stats.recentSubmissions
                    ? `${stats.recentSubmissions} in 30 days`
                    : s.publishedField
                      ? `${c.published} ${s.statusLabels ? s.statusLabels[0].toLowerCase() : "live"}`
                      : undefined
                }
              />
            );
          })}
        </div>

        <div className="dash-masonry">
          <ChartCard title="Blog posts published" subtitle="Last 12 months">
            <BarChart data={stats.postsByMonth} valueLabel="posts" />
          </ChartCard>

          <ChartCard
            title="SEO health"
            subtitle={`${seoTotal} pages, posts & services scored`}
            right={<Link href="/admin/seo" className="btn btn-secondary btn-sm">Open report</Link>}
          >
            <SegmentedBar segments={[
              { label: "Good", value: stats.seoBuckets.good, color: STATUS.good },
              { label: "Needs work", value: stats.seoBuckets.ok, color: STATUS.warn },
              { label: "Poor", value: stats.seoBuckets.poor, color: STATUS.poor },
            ]} />
            {stats.worstSeo.length > 0 && (
              <div style={{ marginTop: 16 }}>
                <SectionLabel>Weakest</SectionLabel>
                <StatList rows={stats.worstSeo.map((w) => ({
                  key: `${w.kind}-${w.id}`,
                  title: w.title,
                  href: seoEditHref(w.kind, w.id, w.url),
                  value: w.score,
                  tone: "danger" as const,
                }))} />
              </div>
            )}
          </ChartCard>

          {stats.postsByCategory.length > 0 && (
            <ChartCard title="Posts by category" subtitle={`Top ${stats.postsByCategory.length} by post count`}>
              <HBars data={stats.postsByCategory} />
            </ChartCard>
          )}

          <ChartCard title="Form submissions" subtitle={`${stats.recentSubmissions} in the last 30 days`}>
            <HBars data={stats.submissions} />
            <div style={{ marginTop: 12 }}>
              <Link href="/admin/submissions" className="btn btn-secondary btn-sm">Open inbox</Link>
            </div>
          </ChartCard>
        </div>

        <div>
          <SectionLabel>Quick actions</SectionLabel>
          <div style={{ display: "flex", gap: 10, flexWrap: "wrap", marginTop: 8 }}>
            <Link href="/admin/posts/new" className="btn btn-secondary">New blog post</Link>
            <Link href="/admin/team/new" className="btn btn-secondary">New team member</Link>
            <Link href="/admin/seo" className="btn btn-secondary">SEO report</Link>
            <Link href="/admin/settings" className="btn btn-secondary">Settings</Link>
          </div>
        </div>
      </div>
    </AdminShell>
  );
}
