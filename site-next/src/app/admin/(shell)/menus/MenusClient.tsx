"use client";

import { useMemo, useState } from "react";
import { toast } from "sonner";
import { Save, Plus, Trash2, ChevronUp, ChevronDown, CornerDownRight } from "lucide-react";
import type { Menus, MenuItem } from "@/lib/types";

type Which = "main" | "footer";

const inputStyle: React.CSSProperties = {
  padding: "7px 10px", borderRadius: 8, fontSize: 13, fontFamily: "inherit",
  border: "1px solid var(--at-border-input)", background: "var(--at-input)", color: "var(--at-text)", outline: "none",
};

let tmp = -1;
const blank = (): MenuItem => ({ id: tmp--, label: "", url: "/", parent: 0, children: [] });

export default function MenusClient({ initial }: { initial: Menus }) {
  const [menus, setMenus] = useState<Menus>(structuredClone(initial));
  const [saving, setSaving] = useState(false);
  const dirty = useMemo(() => JSON.stringify(menus) !== JSON.stringify(initial), [menus, initial]);

  function update(which: Which, items: MenuItem[]) {
    setMenus((m) => ({ ...m, [which]: items }));
  }

  function move(list: MenuItem[], i: number, dir: -1 | 1): MenuItem[] {
    const j = i + dir;
    if (j < 0 || j >= list.length) return list;
    const next = [...list];
    [next[i], next[j]] = [next[j], next[i]];
    return next;
  }

  async function save() {
    setSaving(true);
    try {
      const res = await fetch("/api/admin/menus/", {
        method: "PUT",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify(menus),
      });
      if (res.ok) {
        setMenus(structuredClone(await res.json()));
        toast.success("Menus saved — live now");
      } else toast.error((await res.json()).error ?? "Save failed");
    } catch {
      toast.error("Connection error");
    } finally {
      setSaving(false);
    }
  }

  function Row({
    item, onChange, onRemove, onUp, onDown, child,
  }: {
    item: MenuItem;
    onChange: (v: MenuItem) => void;
    onRemove: () => void;
    onUp: () => void;
    onDown: () => void;
    child?: boolean;
  }) {
    return (
      <div style={{ display: "flex", alignItems: "center", gap: 8, paddingLeft: child ? 26 : 0 }}>
        {child && <CornerDownRight size={13} style={{ color: "var(--at-faint)", flexShrink: 0 }} />}
        <input
          value={item.label}
          placeholder="Label"
          onChange={(e) => onChange({ ...item, label: e.target.value })}
          style={{ ...inputStyle, width: child ? 150 : 170, flexShrink: 0 }}
        />
        <input
          value={item.url}
          placeholder="/path/ or https://…"
          onChange={(e) => onChange({ ...item, url: e.target.value })}
          style={{ ...inputStyle, flex: 1, minWidth: 0 }}
        />
        <div style={{ display: "flex", gap: 2, flexShrink: 0 }}>
          <button type="button" onClick={onUp} className="btn btn-ghost btn-sm" aria-label="Move up"><ChevronUp size={13} /></button>
          <button type="button" onClick={onDown} className="btn btn-ghost btn-sm" aria-label="Move down"><ChevronDown size={13} /></button>
          <button type="button" onClick={onRemove} className="btn btn-ghost btn-sm btn-ghost-delete" aria-label="Remove"><Trash2 size={13} /></button>
        </div>
      </div>
    );
  }

  function MenuBlock({ which, title, hint }: { which: Which; title: string; hint: string }) {
    const list = menus[which];
    const setTop = (i: number, v: MenuItem) => update(which, list.map((it, k) => (k === i ? v : it)));
    return (
      <div className="at-card" style={{ display: "flex", flexDirection: "column", gap: 12 }}>
        <div>
          <h2 style={{ fontSize: 15, fontWeight: 600, color: "var(--at-text)", margin: 0 }}>{title}</h2>
          <p style={{ fontSize: 12, color: "var(--at-muted)", margin: "4px 0 0" }}>{hint}</p>
        </div>

        {list.map((item, i) => (
          <div key={item.id} style={{ display: "flex", flexDirection: "column", gap: 8 }}>
            <Row
              item={item}
              onChange={(v) => setTop(i, v)}
              onRemove={() => update(which, list.filter((_, k) => k !== i))}
              onUp={() => update(which, move(list, i, -1))}
              onDown={() => update(which, move(list, i, 1))}
            />
            {which === "main" && (
              <>
                {item.children.map((c, ci) => (
                  <Row
                    key={c.id}
                    child
                    item={c}
                    onChange={(v) => setTop(i, { ...item, children: item.children.map((x, k) => (k === ci ? v : x)) })}
                    onRemove={() => setTop(i, { ...item, children: item.children.filter((_, k) => k !== ci) })}
                    onUp={() => setTop(i, { ...item, children: move(item.children, ci, -1) })}
                    onDown={() => setTop(i, { ...item, children: move(item.children, ci, 1) })}
                  />
                ))}
                <button
                  type="button"
                  onClick={() => setTop(i, { ...item, children: [...item.children, blank()] })}
                  className="btn btn-ghost btn-sm"
                  style={{ alignSelf: "flex-start", marginLeft: 26, color: "var(--at-muted)" }}
                >
                  <Plus size={12} /> Sub-link
                </button>
              </>
            )}
          </div>
        ))}

        <button
          type="button"
          onClick={() => update(which, [...list, blank()])}
          className="btn btn-secondary btn-sm"
          style={{ alignSelf: "flex-start" }}
        >
          <Plus size={12} /> Add link
        </button>
      </div>
    );
  }

  return (
    <div className="admin-content-pad" style={{ maxWidth: 760, display: "flex", flexDirection: "column", gap: 18 }}>
      <div>
        <h1 style={{ fontSize: 22, fontWeight: 600, color: "var(--at-text)", margin: 0 }}>Menus</h1>
        <p style={{ fontSize: 13, color: "var(--at-muted)", marginTop: 4 }}>
          The header and footer navigation. Changes go live immediately.
        </p>
      </div>

      <MenuBlock which="main" title="Main navigation" hint="The header menu. Top-level links can have a dropdown of sub-links (used for Services)." />
      <MenuBlock which="footer" title="Footer navigation" hint="The link column in the site footer." />

      <div style={{ display: "flex", alignItems: "center", gap: 12, borderTop: "1px solid var(--at-border)", paddingTop: 16, position: "sticky", bottom: 0, background: "var(--at-bg)", paddingBottom: 12 }}>
        <button type="button" onClick={save} disabled={saving || !dirty} className="btn btn-primary">
          <Save size={13} /> {saving ? "Saving…" : "Save menus"}
        </button>
        {dirty && !saving && <span style={{ fontSize: 12, color: "var(--at-muted)" }}>Unsaved changes</span>}
      </div>
    </div>
  );
}
