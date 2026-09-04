"use client";

import Link from "next/link";
import { usePathname, useRouter } from "next/navigation";
import {
  LayoutDashboard, LogOut, Sun, Moon, Monitor, Settings, Search,
  FileText, Users, Layers, Inbox, PanelsTopLeft, ListTree, PenLine,
  ChevronLeft, ChevronRight, Menu, X,
} from "lucide-react";
import { useAdminTheme, type AdminTheme } from "./AdminThemeProvider";
import { useEffect, useState } from "react";
import { toast } from "sonner";

const NAV_SECTIONS = [
  { items: [{ href: "/admin/dashboard", label: "Dashboard", icon: LayoutDashboard, exact: true }] },
  {
    label: "Content",
    items: [
      { href: "/admin/posts", label: "Blog posts", icon: FileText },
      { href: "/admin/authors", label: "Authors", icon: PenLine },
      { href: "/admin/team", label: "Team", icon: Users },
      { href: "/admin/services", label: "Services", icon: Layers },
      { href: "/admin/pages", label: "Pages", icon: PanelsTopLeft },
      { href: "/admin/menus", label: "Menus", icon: ListTree },
    ],
  },
  {
    label: "Inbox",
    items: [{ href: "/admin/submissions", label: "Form submissions", icon: Inbox }],
  },
  {
    label: "Site",
    items: [
      { href: "/admin/seo", label: "SEO report", icon: Search },
      { href: "/admin/settings", label: "Settings", icon: Settings },
    ],
  },
];

export default function AdminSidebar() {
  const pathname = usePathname();
  const router = useRouter();
  const { theme, setTheme } = useAdminTheme();
  const [collapsed, setCollapsed] = useState(false);
  const [mobileOpen, setMobileOpen] = useState(false);
  const [badges, setBadges] = useState<{ submissions?: number }>({});

  useEffect(() => {
    if (localStorage.getItem("oox-sidebar-collapsed") === "1") setCollapsed(true);
  }, []);
  useEffect(() => { setMobileOpen(false); }, [pathname]);

  // Refresh nav badge counts on every navigation — cheap, and picks up
  // submissions you just marked handled.
  useEffect(() => {
    let alive = true;
    fetch("/api/admin/nav-badges")
      .then((r) => (r.ok ? r.json() : null))
      .then((d) => { if (alive && d) setBadges(d); })
      .catch(() => {});
    return () => { alive = false; };
  }, [pathname]);

  const badgeFor = (href: string): number | undefined =>
    href === "/admin/submissions" ? badges.submissions || undefined : undefined;

  function toggleCollapse() {
    setCollapsed((c) => {
      const next = !c;
      localStorage.setItem("oox-sidebar-collapsed", next ? "1" : "0");
      return next;
    });
  }

  async function handleLogout() {
    await fetch("/api/admin/logout", { method: "POST" });
    toast.success("Signed out");
    router.push("/admin/login");
    router.refresh();
  }

  function isActive(href: string, exact = false) {
    if (exact) return pathname === href;
    return pathname === href || pathname.startsWith(href + "/");
  }

  function NavLink({ href, label, icon: Icon, exact, forceExpanded }: {
    href: string; label: string; icon: React.ElementType; exact?: boolean; forceExpanded?: boolean;
  }) {
    const active = isActive(href, exact);
    const c = forceExpanded ? false : collapsed;
    const badge = badgeFor(href);
    return (
      <Link
        href={href}
        title={c ? (badge ? `${label} (${badge})` : label) : undefined}
        style={{
          display: "flex", alignItems: "center", gap: c ? 0 : 10,
          padding: c ? "8px" : "7px 12px", justifyContent: c ? "center" : "flex-start",
          borderRadius: 10, textDecoration: "none", fontSize: 13, fontWeight: active ? 500 : 400,
          transition: "all 0.2s", marginBottom: 2, position: "relative",
          background: active
            ? "linear-gradient(135deg, rgba(99,102,241,0.22) 0%, rgba(139,92,246,0.10) 60%, rgba(99,102,241,0.04) 100%)"
            : "transparent",
          border: active ? "1px solid rgba(99,102,241,0.25)" : "1px solid transparent",
          color: active ? "#6366f1" : "var(--at-muted)",
        }}
      >
        <Icon size={14} style={{ flexShrink: 0 }} />
        {!c && <span style={{ overflow: "hidden", whiteSpace: "nowrap" }}>{label}</span>}
        {badge != null && (c ? (
          <span style={{
            position: "absolute", top: 4, right: 4, width: 7, height: 7, borderRadius: "50%",
            background: "#6366f1", boxShadow: "0 0 0 2px var(--at-sidebar)",
          }} />
        ) : (
          <span style={{
            marginLeft: "auto", flexShrink: 0, minWidth: 18, height: 18, padding: "0 5px", borderRadius: 9,
            background: active ? "rgba(99,102,241,0.25)" : "rgba(99,102,241,0.18)", color: "#818cf8",
            fontSize: 11, fontWeight: 600, display: "flex", alignItems: "center", justifyContent: "center",
          }}>
            {badge}
          </span>
        ))}
      </Link>
    );
  }

  const themeOptions: { value: AdminTheme; icon: React.ElementType; title: string }[] = [
    { value: "light", icon: Sun, title: "Light" },
    { value: "dark", icon: Moon, title: "Dark" },
    { value: "auto", icon: Monitor, title: "Auto" },
  ];

  function ThemeToggle({ compact }: { compact?: boolean }) {
    if (compact) {
      const active = themeOptions.find((o) => o.value === theme)!;
      return (
        <button
          title={`Theme: ${active.title}`}
          onClick={() => {
            const idx = themeOptions.findIndex((o) => o.value === theme);
            setTheme(themeOptions[(idx + 1) % themeOptions.length].value);
          }}
          style={{
            display: "flex", alignItems: "center", justifyContent: "center", width: "100%", padding: "7px 0",
            border: "none", background: "rgba(99,102,241,0.1)", color: "#818cf8", borderRadius: 8, cursor: "pointer",
          }}
        >
          <active.icon size={13} />
        </button>
      );
    }
    return (
      <div>
        <div style={{ fontSize: 10, fontWeight: 500, textTransform: "uppercase", letterSpacing: "0.1em", color: "var(--at-faint)", marginBottom: 8, paddingLeft: 2 }}>
          Theme
        </div>
        <div style={{ display: "flex", borderRadius: 10, overflow: "hidden", border: "1px solid var(--at-border-input)", background: "var(--at-input)" }}>
          {themeOptions.map(({ value, icon: Icon, title }, idx) => {
            const on = theme === value;
            return (
              <button
                key={value} title={title} onClick={() => setTheme(value)}
                style={{
                  flex: 1, display: "flex", alignItems: "center", justifyContent: "center", padding: "7px 0", border: "none",
                  borderRight: idx < themeOptions.length - 1 ? "1px solid var(--at-border)" : "none",
                  cursor: "pointer", background: on ? "rgba(99,102,241,0.15)" : "transparent",
                  color: on ? "#818cf8" : "var(--at-muted)", borderRadius: 0,
                }}
              >
                <Icon size={13} />
              </button>
            );
          })}
        </div>
      </div>
    );
  }

  function SignOutButton({ centered }: { centered?: boolean }) {
    return (
      <button
        onClick={handleLogout}
        title={centered ? "Sign out" : undefined}
        style={{
          display: "flex", alignItems: "center", gap: centered ? 0 : 10,
          justifyContent: centered ? "center" : "flex-start", padding: centered ? "8px" : "8px 12px",
          borderRadius: 10, fontSize: 13, width: "100%", border: "none", background: "transparent",
          color: "var(--at-muted)", cursor: "pointer",
        }}
      >
        <LogOut size={14} style={{ flexShrink: 0 }} />
        {!centered && <span>Sign out</span>}
      </button>
    );
  }

  const Brand = () => (
    <div style={{ display: "flex", alignItems: "center", gap: 10, overflow: "hidden", flexShrink: 0 }}>
      <div style={{
        width: 30, height: 30, borderRadius: 9, flexShrink: 0,
        background: "linear-gradient(135deg, #7A2E8E 0%, #b06fc4 100%)",
        display: "flex", alignItems: "center", justifyContent: "center",
      }}>
        <span style={{ color: "#fff", fontWeight: 700, fontSize: 13 }}>W</span>
      </div>
      {!collapsed && (
        <div style={{ overflow: "hidden" }}>
          <div style={{ fontSize: 13, fontWeight: 600, lineHeight: 1, color: "var(--at-text)", whiteSpace: "nowrap" }}>OOX Limited</div>
          <div style={{ fontSize: 10, marginTop: 3, letterSpacing: "0.08em", textTransform: "uppercase", color: "var(--at-faint)" }}>Admin</div>
        </div>
      )}
    </div>
  );

  const w = collapsed ? 60 : 220;

  return (
    <>
      <aside className="admin-sidebar" style={{
        width: w, minWidth: w, flexShrink: 0, display: "flex", flexDirection: "column", height: "100vh",
        position: "sticky", top: 0, background: "var(--at-sidebar)", borderRight: "1px solid var(--at-border)",
        transition: "width 0.22s ease, min-width 0.22s ease", overflow: "hidden",
      }}>
        <div style={{
          padding: collapsed ? "14px 0 10px" : "18px 16px", borderBottom: "1px solid var(--at-border)",
          display: "flex", flexDirection: collapsed ? "column" : "row", alignItems: "center",
          justifyContent: collapsed ? "center" : "space-between", gap: collapsed ? 8 : 10,
        }}>
          <Brand />
          <button
            onClick={toggleCollapse}
            title={collapsed ? "Expand" : "Collapse"}
            style={{
              display: "flex", alignItems: "center", justifyContent: "center", width: 24, height: 24, borderRadius: 7,
              border: "1px solid var(--at-border)", background: "var(--at-input)", color: "var(--at-faint)",
              cursor: "pointer", flexShrink: 0,
            }}
          >
            {collapsed ? <ChevronRight size={12} /> : <ChevronLeft size={12} />}
          </button>
        </div>

        <nav style={{ flex: 1, padding: collapsed ? "12px 6px" : "12px 10px", overflowY: "auto", overflowX: "hidden" }}>
          {NAV_SECTIONS.map((section, si) => (
            <div key={si}>
              {section.label && !collapsed && (
                <div style={{ fontSize: 10, fontWeight: 500, textTransform: "uppercase", letterSpacing: "0.1em", color: "var(--at-faint)", padding: "14px 12px 4px" }}>
                  {section.label}
                </div>
              )}
              {section.label && collapsed && <div style={{ height: 10 }} />}
              {section.items.map((item) => <NavLink key={item.href} {...item} />)}
            </div>
          ))}
        </nav>

        <div style={{ padding: collapsed ? "8px 6px" : "10px 12px", borderTop: "1px solid var(--at-border)" }}>
          <ThemeToggle compact={collapsed} />
        </div>
        <div style={{ padding: collapsed ? "8px 6px 16px" : "8px 12px 16px", borderTop: "1px solid var(--at-border)" }}>
          <SignOutButton centered={collapsed} />
        </div>
      </aside>

      <div className="admin-mobile-bar" style={{
        position: "fixed", top: 0, left: 0, right: 0, zIndex: 200, height: 52,
        background: "var(--at-sidebar)", borderBottom: "1px solid var(--at-border)",
        padding: "0 14px", alignItems: "center", gap: 10,
      }}>
        <div style={{ display: "flex", alignItems: "center", gap: 8, flex: 1 }}>
          <div style={{
            width: 26, height: 26, borderRadius: 8, flexShrink: 0,
            background: "linear-gradient(135deg, #7A2E8E 0%, #b06fc4 100%)",
            display: "flex", alignItems: "center", justifyContent: "center",
          }}>
            <span style={{ color: "#fff", fontWeight: 700, fontSize: 11 }}>W</span>
          </div>
          <span style={{ fontSize: 13, fontWeight: 600, color: "var(--at-text)" }}>OOX Limited</span>
          <span style={{ fontSize: 10, color: "var(--at-faint)", textTransform: "uppercase", letterSpacing: "0.08em" }}>Admin</span>
        </div>
        <button
          onClick={() => setMobileOpen(true)}
          style={{
            display: "flex", alignItems: "center", justifyContent: "center", width: 36, height: 36, borderRadius: 10,
            border: "1px solid var(--at-border)", background: "var(--at-input)", color: "var(--at-muted)", cursor: "pointer", flexShrink: 0,
          }}
        >
          <Menu size={16} />
        </button>
      </div>

      {mobileOpen && (
        <>
          <style>{`
            @keyframes adminBackdropIn { from { opacity: 0; } to { opacity: 1; } }
            @keyframes adminDrawerIn { from { transform: translateX(100%); } to { transform: translateX(0); } }
          `}</style>
          <div
            onClick={() => setMobileOpen(false)}
            style={{ position: "fixed", inset: 0, zIndex: 300, background: "rgba(0,0,0,0.45)", animation: "adminBackdropIn 0.25s ease both" }}
          />
          <div style={{
            position: "fixed", right: 0, top: 0, bottom: 0, zIndex: 301, width: 272,
            background: "var(--at-sidebar)", borderLeft: "1px solid var(--at-border)",
            display: "flex", flexDirection: "column", overflowY: "auto",
            animation: "adminDrawerIn 0.3s cubic-bezier(0.4, 0, 0.2, 1) both",
          }}>
            <div style={{
              padding: "14px 16px", borderBottom: "1px solid var(--at-border)",
              display: "flex", alignItems: "center", justifyContent: "space-between", flexShrink: 0,
            }}>
              <button
                onClick={() => setMobileOpen(false)}
                style={{
                  display: "flex", alignItems: "center", justifyContent: "center", width: 28, height: 28, borderRadius: 8,
                  border: "1px solid var(--at-border)", background: "var(--at-input)", color: "var(--at-faint)", cursor: "pointer",
                }}
              >
                <X size={13} />
              </button>
              <span style={{ fontSize: 13, fontWeight: 600, color: "var(--at-text)" }}>OOX Admin</span>
            </div>
            <nav style={{ flex: 1, padding: "12px 10px", overflowY: "auto" }}>
              {NAV_SECTIONS.map((section, si) => (
                <div key={si}>
                  {section.label && (
                    <div style={{ fontSize: 10, fontWeight: 500, textTransform: "uppercase", letterSpacing: "0.1em", color: "var(--at-faint)", padding: "14px 12px 4px" }}>
                      {section.label}
                    </div>
                  )}
                  {section.items.map((item) => <NavLink key={item.href} {...item} forceExpanded />)}
                </div>
              ))}
            </nav>
            <div style={{ padding: "10px 12px", borderTop: "1px solid var(--at-border)" }}><ThemeToggle /></div>
            <div style={{ padding: "8px 12px 16px", borderTop: "1px solid var(--at-border)" }}><SignOutButton /></div>
          </div>
        </>
      )}
    </>
  );
}
