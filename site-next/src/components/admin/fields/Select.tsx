"use client";

import { useCallback, useEffect, useId, useMemo, useRef, useState } from "react";
import { Check, ChevronDown } from "lucide-react";

export type SelectOption = { value: string; label: string };

const REDUCE_MOTION =
  typeof window !== "undefined" &&
  window.matchMedia?.("(prefers-reduced-motion: reduce)").matches;

/**
 * Admin listbox — a styled replacement for native <select> that follows the
 * `--at-*` design tokens. Keeps focus on the trigger and drives the open list
 * with `aria-activedescendant` (WAI-ARIA listbox pattern): Arrow/Home/End move
 * the active option, Enter/Space commit, Escape/Tab/outside-click close.
 */
export default function Select({
  value,
  onChange,
  options,
  placeholder = "Select…",
  ariaLabel,
  size = "md",
  disabled = false,
  align = "left",
  minWidth,
  highlightWhenSet = false,
}: {
  value: string;
  onChange: (v: string) => void;
  options: SelectOption[];
  placeholder?: string;
  ariaLabel?: string;
  size?: "sm" | "md";
  disabled?: boolean;
  /** which edge of the trigger the panel lines up with */
  align?: "left" | "right";
  minWidth?: number;
  /** tint the trigger with the accent when a non-empty value is chosen (filters) */
  highlightWhenSet?: boolean;
}) {
  const [open, setOpen] = useState(false);
  const [active, setActive] = useState(0);
  const [focusRing, setFocusRing] = useState(false);
  const rootRef = useRef<HTMLDivElement>(null);
  const listRef = useRef<HTMLUListElement>(null);
  const baseId = useId();

  const selectedIndex = useMemo(
    () => options.findIndex((o) => o.value === value),
    [options, value],
  );
  const selected = selectedIndex >= 0 ? options[selectedIndex] : undefined;

  const dims =
    size === "sm"
      ? { h: 36, pad: "0 12px", font: 13, radius: 10, gap: 8 }
      : { h: 44, pad: "0 14px", font: 14, radius: 12, gap: 10 };

  const close = useCallback(() => setOpen(false), []);

  const openList = useCallback(() => {
    if (disabled) return;
    setActive(selectedIndex >= 0 ? selectedIndex : 0);
    setOpen(true);
  }, [disabled, selectedIndex]);

  const commit = useCallback(
    (i: number) => {
      const o = options[i];
      if (o) onChange(o.value);
      setOpen(false);
    },
    [options, onChange],
  );

  // Close on outside pointer / focus leaving the component
  useEffect(() => {
    if (!open) return;
    const onDown = (e: PointerEvent) => {
      if (!rootRef.current?.contains(e.target as Node)) setOpen(false);
    };
    document.addEventListener("pointerdown", onDown, true);
    return () => document.removeEventListener("pointerdown", onDown, true);
  }, [open]);

  // Keep the active option scrolled into view
  useEffect(() => {
    if (!open || !listRef.current) return;
    const el = listRef.current.children[active] as HTMLElement | undefined;
    el?.scrollIntoView({ block: "nearest" });
  }, [open, active]);

  function onKeyDown(e: React.KeyboardEvent) {
    if (disabled) return;
    if (!open) {
      if (["ArrowDown", "ArrowUp", "Enter", " "].includes(e.key)) {
        e.preventDefault();
        openList();
      }
      return;
    }
    switch (e.key) {
      case "ArrowDown":
        e.preventDefault();
        setActive((i) => Math.min(options.length - 1, i + 1));
        break;
      case "ArrowUp":
        e.preventDefault();
        setActive((i) => Math.max(0, i - 1));
        break;
      case "Home":
        e.preventDefault();
        setActive(0);
        break;
      case "End":
        e.preventDefault();
        setActive(options.length - 1);
        break;
      case "Enter":
      case " ":
        e.preventDefault();
        commit(active);
        break;
      case "Escape":
        e.preventDefault();
        setOpen(false);
        break;
      case "Tab":
        setOpen(false);
        break;
    }
  }

  const activeId = open ? `${baseId}-opt-${active}` : undefined;

  return (
    <div ref={rootRef} style={{ position: "relative", minWidth }}>
      <button
        type="button"
        role="combobox"
        aria-haspopup="listbox"
        aria-expanded={open}
        aria-controls={`${baseId}-list`}
        aria-activedescendant={activeId}
        aria-label={ariaLabel}
        disabled={disabled}
        onClick={() => (open ? close() : openList())}
        onKeyDown={onKeyDown}
        onFocus={() => setFocusRing(true)}
        onBlur={() => setFocusRing(false)}
        style={{
          display: "flex",
          alignItems: "center",
          gap: dims.gap,
          width: "100%",
          height: dims.h,
          padding: dims.pad,
          borderRadius: dims.radius,
          fontSize: dims.font,
          fontFamily: "inherit",
          textAlign: "left",
          cursor: disabled ? "not-allowed" : "pointer",
          opacity: disabled ? 0.55 : 1,
          color: selected ? "var(--at-text)" : "var(--at-placeholder)",
          background: highlightWhenSet && value ? "var(--at-accent-muted)" : "var(--at-input)",
          border: `1px solid ${open || focusRing ? "rgba(99,102,241,0.45)" : "var(--at-border-input)"}`,
          boxShadow: open || focusRing ? "0 0 0 3px rgba(99,102,241,0.10)" : "none",
          outline: "none",
          transition: "border-color 0.15s, box-shadow 0.15s, background 0.15s",
        }}
      >
        <span style={{ flex: 1, overflow: "hidden", textOverflow: "ellipsis", whiteSpace: "nowrap" }}>
          {selected ? selected.label : placeholder}
        </span>
        <ChevronDown
          size={size === "sm" ? 14 : 16}
          aria-hidden="true"
          style={{
            flexShrink: 0,
            color: "var(--at-muted)",
            transition: REDUCE_MOTION ? undefined : "transform 0.18s ease",
            transform: open ? "rotate(180deg)" : "rotate(0deg)",
          }}
        />
      </button>

      {open && (
        <ul
          ref={listRef}
          id={`${baseId}-list`}
          role="listbox"
          aria-label={ariaLabel}
          aria-activedescendant={activeId}
          tabIndex={-1}
          style={{
            position: "absolute",
            zIndex: 50,
            top: "calc(100% + 6px)",
            left: align === "left" ? 0 : undefined,
            right: align === "right" ? 0 : undefined,
            minWidth: "100%",
            maxHeight: 264,
            overflowY: "auto",
            margin: 0,
            padding: 5,
            listStyle: "none",
            borderRadius: dims.radius,
            background: "var(--at-card)",
            border: "1px solid var(--at-border-input)",
            boxShadow: "var(--at-shadow-card)",
            animation: REDUCE_MOTION ? undefined : "at-select-in 0.14s cubic-bezier(0.16,1,0.3,1)",
          }}
        >
          <style>{`@keyframes at-select-in{from{opacity:0;transform:translateY(-4px) scale(0.98)}to{opacity:1;transform:none}}`}</style>
          {options.map((o, i) => {
            const isSelected = o.value === value;
            const isActive = i === active;
            return (
              <li
                key={o.value}
                id={`${baseId}-opt-${i}`}
                role="option"
                aria-selected={isSelected}
                onMouseEnter={() => setActive(i)}
                onClick={() => commit(i)}
                style={{
                  display: "flex",
                  alignItems: "center",
                  gap: 8,
                  padding: size === "sm" ? "7px 9px" : "9px 10px",
                  borderRadius: dims.radius - 4,
                  fontSize: dims.font,
                  cursor: "pointer",
                  color: isSelected ? "var(--at-accent)" : "var(--at-text)",
                  fontWeight: isSelected ? 600 : 400,
                  background: isActive ? "var(--at-hover)" : "transparent",
                }}
              >
                <Check
                  size={14}
                  aria-hidden="true"
                  style={{ flexShrink: 0, opacity: isSelected ? 1 : 0, color: "var(--at-accent)" }}
                />
                <span style={{ flex: 1, overflow: "hidden", textOverflow: "ellipsis", whiteSpace: "nowrap" }}>
                  {o.label}
                </span>
              </li>
            );
          })}
        </ul>
      )}
    </div>
  );
}
