"use client";

import { useState } from "react";
import { useRouter } from "next/navigation";

export default function AdminLoginPage() {
  const router = useRouter();
  const [username, setUsername] = useState("");
  const [password, setPassword] = useState("");
  const [error, setError] = useState("");
  const [loading, setLoading] = useState(false);
  const [focused, setFocused] = useState<string | null>(null);

  async function handleSubmit(e: React.FormEvent) {
    e.preventDefault();
    setError("");
    setLoading(true);
    try {
      const res = await fetch("/api/admin/login", {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({ username, password }),
      });
      if (res.ok) {
        router.push("/admin/dashboard");
        router.refresh();
      } else {
        setError((await res.json()).error ?? "Invalid credentials");
      }
    } catch {
      setError("Connection error. Please try again.");
    } finally {
      setLoading(false);
    }
  }

  const inputStyle = (name: string) => ({
    width: "100%", padding: "10px 14px", borderRadius: 12, fontSize: 14, color: "var(--at-text)",
    background: "var(--at-input-alt)",
    border: `1px solid ${focused === name ? "rgba(99,102,241,0.5)" : "var(--at-border-input-alt)"}`,
    boxShadow: focused === name ? "0 0 0 3px rgba(99,102,241,0.08)" : "none",
    outline: "none", boxSizing: "border-box" as const, transition: "all 0.15s", fontFamily: "inherit",
  });

  return (
    <div style={{ minHeight: "100vh", display: "flex", alignItems: "center", justifyContent: "center", padding: 16, background: "var(--at-bg)" }}>
      <div style={{ width: "100%", maxWidth: 360 }}>
        <div style={{ display: "flex", justifyContent: "center", marginBottom: 32 }}>
          <div style={{ display: "flex", alignItems: "center", gap: 10 }}>
            <div style={{
              width: 32, height: 32, borderRadius: 8, display: "flex", alignItems: "center", justifyContent: "center",
              background: "rgba(122,46,142,0.15)", border: "1px solid rgba(122,46,142,0.35)",
            }}>
              <span style={{ color: "#b06fc4", fontWeight: 700, fontSize: 14 }}>W</span>
            </div>
            <span style={{ color: "var(--at-text)", fontWeight: 600, letterSpacing: "-0.02em" }}>Wikiwallet Admin</span>
          </div>
        </div>

        <div style={{ borderRadius: 16, padding: 32, background: "var(--at-card)", border: "1px solid var(--at-border)" }}>
          <h1 style={{ fontSize: 20, fontWeight: 600, color: "var(--at-text)", margin: "0 0 4px" }}>Sign in</h1>
          <p style={{ fontSize: 14, color: "var(--at-muted)", margin: "0 0 24px" }}>Admin access only</p>

          <form onSubmit={handleSubmit} style={{ display: "flex", flexDirection: "column", gap: 16 }}>
            <div>
              <label style={{ display: "block", fontSize: 11, fontWeight: 500, color: "var(--at-muted)", marginBottom: 6, textTransform: "uppercase", letterSpacing: "0.08em" }}>Username</label>
              <input type="text" value={username} onChange={(e) => setUsername(e.target.value)} required autoComplete="username"
                style={inputStyle("username")} onFocus={() => setFocused("username")} onBlur={() => setFocused(null)} />
            </div>
            <div>
              <label style={{ display: "block", fontSize: 11, fontWeight: 500, color: "var(--at-muted)", marginBottom: 6, textTransform: "uppercase", letterSpacing: "0.08em" }}>Password</label>
              <input type="password" value={password} onChange={(e) => setPassword(e.target.value)} required autoComplete="current-password"
                placeholder="••••••••" style={inputStyle("password")} onFocus={() => setFocused("password")} onBlur={() => setFocused(null)} />
            </div>

            {error && (
              <div style={{ borderRadius: 10, padding: "10px 14px", fontSize: 14, background: "rgba(239,68,68,0.08)", border: "1px solid rgba(239,68,68,0.2)", color: "#f87171" }}>
                {error}
              </div>
            )}

            <button type="submit" disabled={loading} style={{
              width: "100%", padding: 10, borderRadius: 12, fontSize: 14, fontWeight: 600, border: "none",
              cursor: loading ? "not-allowed" : "pointer", opacity: loading ? 0.5 : 1,
              background: "#7A2E8E", color: "white", transition: "all 0.15s", fontFamily: "inherit", marginTop: 4,
            }}>
              {loading ? "Signing in…" : "Sign in"}
            </button>
          </form>
        </div>
        <p style={{ textAlign: "center", fontSize: 12, color: "var(--at-faint)", marginTop: 24 }}>Session expires after 8 hours</p>
      </div>
    </div>
  );
}
