"use client";

import { Plus, Trash2, ChevronUp, ChevronDown } from "lucide-react";

export type QaEntry = { question: string; answer: string };

export default function QaListField({ value, onChange }: {
  value: QaEntry[];
  onChange: (v: QaEntry[]) => void;
}) {
  function update(i: number, patch: Partial<QaEntry>) {
    const next = [...value];
    next[i] = { ...next[i], ...patch };
    onChange(next);
  }
  function remove(i: number) {
    onChange(value.filter((_, idx) => idx !== i));
  }
  function move(i: number, dir: -1 | 1) {
    const j = i + dir;
    if (j < 0 || j >= value.length) return;
    const next = [...value];
    [next[i], next[j]] = [next[j], next[i]];
    onChange(next);
  }

  const ctrlBtn = {
    display: "flex", alignItems: "center", justifyContent: "center",
    width: 28, height: 28, flexShrink: 0, borderRadius: 7,
    border: "1px solid var(--at-border-input)", background: "var(--at-card)",
    color: "var(--at-muted)", cursor: "pointer",
  } as const;

  const inputStyle = {
    width: "100%", padding: "0 12px", borderRadius: 8, fontSize: 13,
    border: "1px solid var(--at-border-input)", background: "var(--at-card)",
    color: "var(--at-text)", outline: "none", fontFamily: "inherit",
  } as const;

  return (
    <div style={{
      border: "1px solid var(--at-border-input)", borderRadius: 12,
      background: "var(--at-input)", padding: 8, display: "flex", flexDirection: "column", gap: 8,
    }}>
      {value.length === 0 && (
        <p style={{ margin: "4px 6px", fontSize: 12, color: "var(--at-faint)" }}>No questions yet.</p>
      )}

      {value.map((qa, i) => (
        <div key={i} style={{
          display: "flex", gap: 8, padding: 10, borderRadius: 10,
          border: "1px solid var(--at-border-input)", background: "var(--at-card)",
        }}>
          <span style={{
            width: 22, flexShrink: 0, textAlign: "center", fontSize: 11, fontWeight: 600,
            color: "var(--at-faint)", paddingTop: 8,
          }}>
            {i + 1}
          </span>
          <div style={{ flex: 1, minWidth: 0, display: "flex", flexDirection: "column", gap: 6 }}>
            <input
              value={qa.question}
              onChange={(e) => update(i, { question: e.target.value })}
              placeholder="Question"
              style={{ ...inputStyle, height: 34, fontWeight: 600 }}
            />
            <textarea
              value={qa.answer}
              onChange={(e) => update(i, { answer: e.target.value })}
              placeholder="Answer"
              rows={3}
              style={{ ...inputStyle, padding: "8px 12px", resize: "vertical", lineHeight: 1.5 }}
            />
          </div>
          <div style={{ display: "flex", flexDirection: "column", gap: 4, flexShrink: 0 }}>
            <button type="button" onClick={() => move(i, -1)} disabled={i === 0}
              style={{ ...ctrlBtn, opacity: i === 0 ? 0.35 : 1, cursor: i === 0 ? "default" : "pointer" }}
              aria-label="Move up" title="Move up">
              <ChevronUp size={14} />
            </button>
            <button type="button" onClick={() => move(i, 1)} disabled={i === value.length - 1}
              style={{ ...ctrlBtn, opacity: i === value.length - 1 ? 0.35 : 1, cursor: i === value.length - 1 ? "default" : "pointer" }}
              aria-label="Move down" title="Move down">
              <ChevronDown size={14} />
            </button>
            <button type="button" onClick={() => remove(i)}
              style={{ ...ctrlBtn, color: "#f87171", borderColor: "rgba(239,68,68,0.3)" }}
              aria-label="Remove" title="Remove">
              <Trash2 size={13} />
            </button>
          </div>
        </div>
      ))}

      <button
        type="button"
        onClick={() => onChange([...value, { question: "", answer: "" }])}
        style={{
          display: "flex", alignItems: "center", justifyContent: "center", gap: 6,
          height: 32, borderRadius: 8, border: "1px dashed var(--at-border-input)",
          background: "transparent", color: "var(--at-muted)", cursor: "pointer",
          fontSize: 12, fontWeight: 500, fontFamily: "inherit",
        }}
      >
        <Plus size={13} /> Add question
      </button>
    </div>
  );
}
