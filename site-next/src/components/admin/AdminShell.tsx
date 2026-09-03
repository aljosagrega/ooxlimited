import AdminSidebar from "./AdminSidebar";

export default function AdminShell({ children }: { children: React.ReactNode }) {
  return (
    <div style={{ display: "flex", minHeight: "100vh", background: "var(--at-bg)" }}>
      <AdminSidebar />
      {/* longhand padding (not the `padding` shorthand) so a component that
          temporarily tweaks main.style.paddingRight can't leave the shorthand
          decomposed with a 0 value — see SchemaForm's preview panel */}
      <main style={{ flex: 1, minWidth: 0, paddingTop: 40, paddingRight: 48, paddingBottom: 40, paddingLeft: 48, overflowX: "hidden", boxSizing: "border-box" }}>
        <div style={{ maxWidth: 1800, margin: "0 auto" }}>{children}</div>
      </main>
    </div>
  );
}
