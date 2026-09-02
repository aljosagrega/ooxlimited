"use client";

export default function ColorField({ value, onChange }: {
  value: string;
  onChange: (v: string) => void;
}) {
  return (
    <div style={{ display: "flex", alignItems: "center", gap: 8 }}>
      <input
        type="color"
        value={/^#[0-9a-f]{6}$/i.test(value) ? value : "#000000"}
        onChange={(e) => onChange(e.target.value)}
        style={{ width: 36, height: 36, padding: 0, border: "1px solid var(--at-border-input)", borderRadius: 8, cursor: "pointer", background: "none" }}
      />
      <input
        value={value}
        onChange={(e) => onChange(e.target.value)}
        placeholder="#7A2E8E or a token"
        style={{
          flex: 1, padding: "7px 12px", borderRadius: 9, fontSize: 13,
          border: "1px solid var(--at-border-input)", background: "var(--at-input)",
          color: "var(--at-text)", outline: "none", fontFamily: "inherit",
        }}
      />
    </div>
  );
}
