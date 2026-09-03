import AdminShell from "@/components/admin/AdminShell";

/**
 * Wraps every admin screen except /admin/login in the sidebar shell. The shell
 * lives here (not in the page or the loading state) so the sidebar mounts once
 * and never re-runs its localStorage read / nav-badge fetch on navigation —
 * page content and loading.tsx swap inside it with nothing in the nav shifting.
 */
export default function ShellLayout({ children }: { children: React.ReactNode }) {
  return <AdminShell>{children}</AdminShell>;
}
