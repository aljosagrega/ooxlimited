"use client";

import { useState } from "react";

export function inputStyle(focused = false) {
  return {
    width: "100%",
    height: 44,
    padding: "10px 14px",
    borderRadius: 12,
    fontSize: 14,
    color: "var(--at-text)",
    background: "var(--at-input)",
    border: `1px solid ${focused ? "rgba(99,102,241,0.45)" : "var(--at-border-input)"}`,
    boxShadow: focused ? "0 0 0 3px rgba(99,102,241,0.07)" : "none",
    outline: "none",
    boxSizing: "border-box" as const,
    transition: "border-color 0.15s, box-shadow 0.15s",
    fontFamily: "inherit",
  };
}

export function FieldLabel({ label }: { label: string }) {
  return (
    <label style={{ display: "block", fontSize: 11, fontWeight: 500, color: "var(--at-muted)", marginBottom: 8, textTransform: "uppercase", letterSpacing: "0.08em" }}>
      {label}
    </label>
  );
}

export function Field({ label, children }: { label: string; children: React.ReactNode }) {
  return (
    <div>
      <FieldLabel label={label} />
      {children}
    </div>
  );
}

export function FocusInput({ value, onChange, placeholder, required, type = "text" }: {
  value: string;
  onChange: (v: string) => void;
  placeholder?: string;
  required?: boolean;
  type?: string;
}) {
  const [focused, setFocused] = useState(false);
  return (
    <input
      type={type}
      value={value}
      onChange={(e) => onChange(e.target.value)}
      placeholder={placeholder}
      required={required}
      style={inputStyle(focused)}
      onFocus={() => setFocused(true)}
      onBlur={() => setFocused(false)}
    />
  );
}

export function FocusTextarea({ value, onChange, placeholder, required, rows = 3 }: {
  value: string;
  onChange: (v: string) => void;
  placeholder?: string;
  required?: boolean;
  rows?: number;
}) {
  const [focused, setFocused] = useState(false);
  return (
    <textarea
      value={value}
      onChange={(e) => onChange(e.target.value)}
      placeholder={placeholder}
      required={required}
      rows={rows}
      style={{ ...inputStyle(focused), height: "auto", resize: "vertical" as const }}
      onFocus={() => setFocused(true)}
      onBlur={() => setFocused(false)}
    />
  );
}
