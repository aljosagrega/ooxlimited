"use client";

import { useMemo, useRef, useState } from "react";
import { toast } from "sonner";
import { Save, KeyRound, Plus, Trash2, Settings2, Mail, Share2, ShieldCheck, BarChart3 } from "lucide-react";
import type { SiteSettings } from "@/lib/types";
import { Field, FocusInput, FocusTextarea } from "@/components/admin/fields/FormPrimitives";

type TabId = "general" | "contact" | "analytics" | "social" | "account";

const TABS: { id: TabId; label: string; icon: typeof Mail }[] = [
  { id: "general", label: "General", icon: Settings2 },
  { id: "contact", label: "Contact & newsletter", icon: Mail },
  { id: "analytics", label: "Analytics & scripts", icon: BarChart3 },
  { id: "social", label: "Social links", icon: Share2 },
  { id: "account", label: "Account", icon: ShieldCheck },
];

function TabBar({ active, onChange }: { active: TabId; onChange: (t: TabId) => void }) {
  const refs = useRef<(HTMLButtonElement | null)[]>([]);
  function onKeyDown(e: React.KeyboardEvent, i: number) {
    if (e.key !== "ArrowLeft" && e.key !== "ArrowRight") return;
    e.preventDefault();
    const next = e.key === "ArrowRight" ? (i + 1) % TABS.length : (i - 1 + TABS.length) % TABS.length;
    onChange(TABS[next].id);
    refs.current[next]?.focus();
  }
  return (
    <div className="at-tabs" role="tablist" aria-label="Settings sections" style={{ marginBottom: 24 }}>
      {TABS.map(({ id, label, icon: Icon }, i) => {
        const on = id === active;
        return (
          <button
            key={id}
            ref={(el) => { refs.current[i] = el; }}
            type="button"
            role="tab"
            id={`tab-${id}`}
            aria-selected={on}
            aria-controls={`panel-${id}`}
            tabIndex={on ? 0 : -1}
            onKeyDown={(e) => onKeyDown(e, i)}
            onClick={() => onChange(id)}
            className={`at-tab${on ? " active" : ""}`}
          >
            <Icon size={14} /> {label}
          </button>
        );
      })}
    </div>
  );
}

function SectionHeading({ title, hint }: { title: string; hint: React.ReactNode }) {
  return (
    <div style={{ marginBottom: 20 }}>
      <h2 style={{ fontSize: 15, fontWeight: 600, color: "var(--at-text)", margin: 0 }}>{title}</h2>
      <p style={{ fontSize: 12.5, lineHeight: 1.6, color: "var(--at-muted)", margin: "5px 0 0", maxWidth: 460 }}>{hint}</p>
    </div>
  );
}

function panelProps(id: TabId, active: TabId) {
  return { role: "tabpanel" as const, id: `panel-${id}`, "aria-labelledby": `tab-${id}`, hidden: id !== active };
}

function SiteSettingsForm({ initial, activeTab }: { initial: SiteSettings; activeTab: TabId }) {
  const [s, setS] = useState<SiteSettings>(initial);
  const [saving, setSaving] = useState(false);
  const dirty = useMemo(() => JSON.stringify(s) !== JSON.stringify(initial), [s, initial]);

  function set<K extends keyof SiteSettings>(key: K, value: SiteSettings[K]) {
    setS((prev) => ({ ...prev, [key]: value }));
  }
  function updateLink(i: number, field: "label" | "href" | "icon", v: string) {
    const next = [...s.socialLinks];
    next[i] = { ...next[i], [field]: v };
    set("socialLinks", next);
  }

  async function handleSave(e: React.FormEvent) {
    e.preventDefault();
    setSaving(true);
    try {
      const res = await fetch("/api/admin/settings", {
        method: "PUT",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({
          title: s.title,
          tagline: s.tagline,
          contactEmail: s.contactEmail,
          contactRecipients: s.contactRecipients,
          mailFromName: s.mailFromName,
          mailchimpListId: s.mailchimpListId,
          gaId: s.gaId,
          headScripts: s.headScripts,
          socialLinks: s.socialLinks,
        }),
      });
      if (res.ok) toast.success("Site settings saved");
      else toast.error((await res.json()).error ?? "Failed to save");
    } catch {
      toast.error("Connection error");
    } finally {
      setSaving(false);
    }
  }

  const linkInput: React.CSSProperties = {
    padding: "8px 12px", borderRadius: 9, fontSize: 13, border: "1px solid var(--at-border-input)",
    background: "var(--at-input)", color: "var(--at-text)", outline: "none", fontFamily: "inherit",
  };

  return (
    <form onSubmit={handleSave}>
      <div className="at-card" style={{ maxWidth: 620 }}>
        <div {...panelProps("general", activeTab)} style={{ display: "flex", flexDirection: "column", gap: 18 }}>
          <SectionHeading title="General" hint="Site name and tagline, used in metadata and structured data." />
          <Field label="Site title"><FocusInput value={s.title} onChange={(v) => set("title", v)} /></Field>
          <Field label="Tagline"><FocusInput value={s.tagline} onChange={(v) => set("tagline", v)} /></Field>
        </div>

        <div {...panelProps("contact", activeTab)} style={{ display: "flex", flexDirection: "column", gap: 18 }}>
          <SectionHeading title="Contact & newsletter" hint="Where contact submissions are emailed, and the Mailchimp audience newsletter signups are added to." />
          <div style={{ display: "grid", gridTemplateColumns: "1fr 1fr", gap: 16 }}>
            <Field label="Primary contact email"><FocusInput value={s.contactEmail} onChange={(v) => set("contactEmail", v)} type="email" /></Field>
            <Field label="Mail from name"><FocusInput value={s.mailFromName} onChange={(v) => set("mailFromName", v)} /></Field>
          </div>
          <Field label="Contact form recipients (comma-separated)">
            <FocusInput
              value={s.contactRecipients.join(", ")}
              onChange={(v) => set("contactRecipients", v.split(",").map((x) => x.trim()).filter(Boolean))}
            />
          </Field>
          <Field label="Mailchimp list / audience ID">
            <FocusInput value={s.mailchimpListId} onChange={(v) => set("mailchimpListId", v)} placeholder="e.g. a1b2c3d4e5" />
          </Field>
        </div>

        <div {...panelProps("analytics", activeTab)} style={{ display: "flex", flexDirection: "column", gap: 18 }}>
          <SectionHeading
            title="Analytics & scripts"
            hint="GA4 loads on every page (deferred). Additional scripts are injected just before </body> — use for GTM, Meta Pixel, etc."
          />
          <div style={{ maxWidth: 260 }}>
            <Field label="Google Analytics 4 ID">
              <FocusInput value={s.gaId} onChange={(v) => set("gaId", v)} placeholder="G-XXXXXXXXXX" />
            </Field>
          </div>
          <Field label="Extra scripts (advanced)">
            <FocusTextarea
              value={s.headScripts}
              onChange={(v) => set("headScripts", v)}
              rows={6}
              placeholder="<script>…</script>"
            />
          </Field>
        </div>

        <div {...panelProps("social", activeTab)} style={{ display: "flex", flexDirection: "column", gap: 18 }}>
          <SectionHeading title="Social links" hint="The footer social icons, in this order. Icon key matches the FontAwesome brand (fa-facebook-f, fa-instagram, …)." />
          <div style={{ display: "flex", flexDirection: "column", gap: 8 }}>
            {s.socialLinks.map((link, i) => (
              <div key={i} style={{ display: "flex", alignItems: "center", gap: 8 }}>
                <input value={link.label} onChange={(e) => updateLink(i, "label", e.target.value)} placeholder="Label"
                  style={{ ...linkInput, width: 100, flexShrink: 0 }} />
                <input value={link.icon ?? ""} onChange={(e) => updateLink(i, "icon", e.target.value)} placeholder="fa-instagram"
                  style={{ ...linkInput, width: 120, flexShrink: 0 }} />
                <input value={link.href} onChange={(e) => updateLink(i, "href", e.target.value)} placeholder="https://…"
                  style={{ ...linkInput, flex: 1 }} />
                <button type="button" aria-label={`Remove ${link.label || "link"}`} onClick={() => set("socialLinks", s.socialLinks.filter((_, idx) => idx !== i))} className="btn btn-ghost btn-sm btn-ghost-delete"><Trash2 size={13} /></button>
              </div>
            ))}
            <button type="button" onClick={() => set("socialLinks", [...s.socialLinks, { label: "", href: "" }])} className="btn btn-secondary btn-sm" style={{ alignSelf: "flex-start", marginTop: 2 }}>
              <Plus size={12} /> Add link
            </button>
          </div>
        </div>

        <div style={{ display: "flex", alignItems: "center", gap: 12, borderTop: "1px solid var(--at-border)", paddingTop: 18, marginTop: 22 }}>
          <button type="submit" disabled={saving || !dirty} className="btn btn-primary">
            <Save size={13} /> {saving ? "Saving…" : "Save changes"}
          </button>
          {dirty && !saving && (
            <span style={{ fontSize: 12, color: "var(--at-muted)", display: "inline-flex", alignItems: "center", gap: 6 }}>
              <span style={{ width: 6, height: 6, borderRadius: "50%", background: "var(--at-accent)" }} />
              Unsaved changes
            </span>
          )}
        </div>
      </div>
    </form>
  );
}

function PasswordForm({ username, activeTab }: { username: string; activeTab: TabId }) {
  const [currentPassword, setCurrentPassword] = useState("");
  const [newUsername, setNewUsername] = useState(username);
  const [newPassword, setNewPassword] = useState("");
  const [confirmPassword, setConfirmPassword] = useState("");
  const [saving, setSaving] = useState(false);
  const [error, setError] = useState("");

  async function handleSave(e: React.FormEvent) {
    e.preventDefault();
    setError("");
    if (newPassword && newPassword !== confirmPassword) {
      setError("New passwords don't match");
      return;
    }
    setSaving(true);
    try {
      const res = await fetch("/api/admin/change-password", {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({
          currentPassword,
          newUsername: newUsername !== username ? newUsername : undefined,
          newPassword: newPassword || undefined,
        }),
      });
      if (res.ok) {
        toast.success("Credentials updated");
        setCurrentPassword(""); setNewPassword(""); setConfirmPassword("");
      } else {
        const data = await res.json();
        setError(data.error ?? "Failed to update credentials");
        toast.error(data.error ?? "Failed to update credentials");
      }
    } catch {
      setError("Connection error");
    } finally {
      setSaving(false);
    }
  }

  return (
    <form onSubmit={handleSave} {...panelProps("account", activeTab)}>
      <div className="at-card" style={{ maxWidth: 420, display: "flex", flexDirection: "column", gap: 18 }}>
        <SectionHeading
          title="Admin login"
          hint={<>Updates the stored credentials in <code>src/data/adminAuth.json</code>. Delete that file on the server to reset back to the <code>ADMIN_USERNAME</code> / <code>ADMIN_PASSWORD</code> env vars.</>}
        />
        <Field label="Current password"><FocusInput value={currentPassword} onChange={setCurrentPassword} type="password" required /></Field>
        <Field label="Username"><FocusInput value={newUsername} onChange={setNewUsername} /></Field>
        <Field label="New password (leave blank to keep current)"><FocusInput value={newPassword} onChange={setNewPassword} type="password" /></Field>
        {newPassword && <Field label="Confirm new password"><FocusInput value={confirmPassword} onChange={setConfirmPassword} type="password" /></Field>}
        {error && (
          <div style={{ borderRadius: 10, padding: "10px 14px", fontSize: 13, background: "rgba(239,68,68,0.08)", border: "1px solid rgba(239,68,68,0.2)", color: "#f87171" }}>
            {error}
          </div>
        )}
        <div style={{ borderTop: "1px solid var(--at-border)", paddingTop: 18, marginTop: 4 }}>
          <button type="submit" disabled={saving || !currentPassword} className="btn btn-primary">
            <KeyRound size={13} /> {saving ? "Updating…" : "Update credentials"}
          </button>
        </div>
      </div>
    </form>
  );
}

export default function SettingsClient({ username, initialSettings }: { username: string; initialSettings: SiteSettings }) {
  const [tab, setTab] = useState<TabId>("general");
  return (
    <div className="admin-content-pad" style={{ maxWidth: 720 }}>
      <div style={{ marginBottom: 22 }}>
        <h1 style={{ fontSize: 22, fontWeight: 600, color: "var(--at-text)", margin: 0 }}>Settings</h1>
        <p style={{ fontSize: 13, color: "var(--at-muted)", marginTop: 4 }}>
          Signed in as <strong style={{ color: "var(--at-text)" }}>{username}</strong>
        </p>
      </div>
      <TabBar active={tab} onChange={setTab} />
      <div hidden={tab === "account"}>
        <SiteSettingsForm initial={initialSettings} activeTab={tab} />
      </div>
      <PasswordForm username={username} activeTab={tab} />
    </div>
  );
}
