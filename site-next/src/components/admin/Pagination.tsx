"use client";

import { useMemo, useState } from "react";

/**
 * Client-side pagination for admin tables. Give it the full filtered list; it
 * returns the current page slice plus a `<Pagination>` control to render below
 * the table.
 *
 *   const { pageItems, control } = usePagination(filtered, 25);
 *   {pageItems.map(...)}
 *   {control}
 *
 * The page auto-resets to 1 whenever `total` changes (i.e. a filter/search
 * narrowed the list), so callers don't have to wire that up themselves.
 */
export function usePagination<T>(items: T[], pageSize = 25) {
  const [page, setPage] = useState(1);
  const total = items.length;
  const pageCount = Math.max(1, Math.ceil(total / pageSize));

  // Reset to the first page when the filtered set changes size (search/filter),
  // and clamp if the current page fell off the end — both adjusted during render
  // per the React "storing info from previous renders" pattern, so no effect.
  const [prevTotal, setPrevTotal] = useState(total);
  if (prevTotal !== total) {
    setPrevTotal(total);
    setPage(1);
  }
  const safePage = Math.min(page, pageCount);

  const pageItems = useMemo(
    () => items.slice((safePage - 1) * pageSize, safePage * pageSize),
    [items, safePage, pageSize],
  );

  const control =
    total > pageSize ? (
      <Pagination
        page={safePage}
        pageCount={pageCount}
        total={total}
        rangeStart={(safePage - 1) * pageSize + 1}
        rangeEnd={Math.min(safePage * pageSize, total)}
        onChange={setPage}
      />
    ) : null;

  return { page: safePage, setPage, pageItems, pageCount, control };
}

function pageWindow(page: number, pageCount: number): (number | "…")[] {
  if (pageCount <= 7) return Array.from({ length: pageCount }, (_, i) => i + 1);
  const out: (number | "…")[] = [1];
  const from = Math.max(2, page - 1);
  const to = Math.min(pageCount - 1, page + 1);
  if (from > 2) out.push("…");
  for (let i = from; i <= to; i++) out.push(i);
  if (to < pageCount - 1) out.push("…");
  out.push(pageCount);
  return out;
}

export function Pagination({
  page, pageCount, total, rangeStart, rangeEnd, onChange,
}: {
  page: number;
  pageCount: number;
  total: number;
  rangeStart: number;
  rangeEnd: number;
  onChange: (page: number) => void;
}) {
  return (
    <div style={{
      display: "flex", alignItems: "center", justifyContent: "space-between",
      flexWrap: "wrap", gap: 12, padding: "14px 4px 4px",
    }}>
      <span style={{ fontSize: 12, color: "var(--at-muted)" }}>
        {rangeStart.toLocaleString()}–{rangeEnd.toLocaleString()} of {total.toLocaleString()}
      </span>
      <div style={{ display: "flex", alignItems: "center", gap: 4 }}>
        <button
          type="button"
          className="btn btn-secondary btn-sm"
          disabled={page <= 1}
          onClick={() => onChange(page - 1)}
        >
          Prev
        </button>
        {pageWindow(page, pageCount).map((p, i) =>
          p === "…" ? (
            <span key={`gap-${i}`} style={{ padding: "0 4px", fontSize: 12, color: "var(--at-faint)" }}>…</span>
          ) : (
            <button
              key={p}
              type="button"
              onClick={() => onChange(p)}
              className={`btn-pill${p === page ? " active" : ""}`}
              style={{ minWidth: 32 }}
            >
              {p}
            </button>
          ),
        )}
        <button
          type="button"
          className="btn btn-secondary btn-sm"
          disabled={page >= pageCount}
          onClick={() => onChange(page + 1)}
        >
          Next
        </button>
      </div>
    </div>
  );
}
