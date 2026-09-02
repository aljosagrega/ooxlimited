import AdminSidebar from "./AdminSidebar";

export default function AdminShell({ children }: { children: React.ReactNode }) {
  return (
    <div style={{ display: "flex", minHeight: "100vh", background: "var(--at-bg)" }}>
      <AdminSidebar />
      <main style={{ flex: 1, minWidth: 0, padding: "40px 48px", overflowX: "hidden", boxSizing: "border-box" }}>
        <div style={{ maxWidth: 1800, margin: "0 auto" }}>{children}</div>
      </main>
    </div>
  );
}
