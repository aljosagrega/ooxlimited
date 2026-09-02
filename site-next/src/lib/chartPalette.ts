/**
 * Chart colours — a plain module (no "use client") so server components
 * (the dashboard) and client chart components can both import the same
 * constants without crossing a client boundary at runtime.
 * Validated against the dataviz skill: status trio always ships with a label;
 * the categorical trio is validated slots 1–3 for the dark surface.
 */
export const STATUS = { good: "#10b981", warn: "#f59e0b", poor: "#f87171" } as const;
export const CAT3 = ["#3987e5", "#d95926", "#199e70"] as const;
