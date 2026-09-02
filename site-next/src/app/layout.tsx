/**
 * Passthrough root layout. The public site ((public)/layout.tsx) and the admin
 * (admin/layout.tsx) each own their <html>/<body> — they are visually unrelated
 * and share nothing. This keeps them from being nested under one shell.
 */
export default function RootLayout({ children }: { children: React.ReactNode }) {
  return children;
}
