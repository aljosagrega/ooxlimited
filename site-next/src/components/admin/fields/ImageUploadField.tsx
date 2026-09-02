"use client";

import { useRef, useState } from "react";
import { X } from "lucide-react";

export default function ImageUploadField({ value, onChange, folder, fallbackSrc }: {
  value: string;
  onChange: (v: string) => void;
  folder?: string;
  /** shown in the preview box (dimmed) when no value is set — e.g. a YouTube auto-thumbnail */
  fallbackSrc?: string;
}) {
  const [uploading, setUploading] = useState(false);
  const [uploadError, setUploadError] = useState("");
  const [brokenSrc, setBrokenSrc] = useState("");
  const [pasteOpen, setPasteOpen] = useState(false);
  const [pasteVal, setPasteVal] = useState("");
  const fileRef = useRef<HTMLInputElement>(null);
  const abortRef = useRef<AbortController | null>(null);

  async function handleFile(e: React.ChangeEvent<HTMLInputElement>) {
    const file = e.target.files?.[0];
    if (!file) return;
    setUploadError("");
    setUploading(true);
    const controller = new AbortController();
    abortRef.current = controller;
    try {
      const fd = new FormData();
      fd.append("file", file);
      const res = await fetch("/api/admin/upload", { method: "POST", body: fd, signal: controller.signal });
      const data = await res.json();
      if (res.ok) { onChange(data.url); setPasteOpen(false); }
      else setUploadError(
        res.status === 401
          ? "Session expired — refresh the page and log in again"
          : (data.error ?? "Upload failed")
      );
    } catch (err) {
      if ((err as Error).name !== "AbortError") setUploadError("Connection error");
    } finally {
      setUploading(false);
      abortRef.current = null;
      if (fileRef.current) fileRef.current.value = "";
    }
  }

  function cancelUpload() {
    abortRef.current?.abort();
    setUploading(false);
    setUploadError("");
    abortRef.current = null;
    if (fileRef.current) fileRef.current.value = "";
  }

  // ooxlimited image values are already root-absolute (/wp-content/uploads/… or
  // /media/uploads/…) or full URLs — use them as-is.
  const previewSrc = value;
  void folder;

  return (
    <div style={{ display: "flex", alignItems: "center", gap: 14 }}>
      <div style={{
        width: 80, height: 56, borderRadius: 10, flexShrink: 0,
        background: "var(--at-input)", border: "1px solid var(--at-border)",
        overflow: "hidden", display: "flex", alignItems: "center", justifyContent: "center",
        position: "relative",
      }} title={value || undefined}>
        {value && brokenSrc !== previewSrc ? (
          /* eslint-disable-next-line @next/next/no-img-element */
          <img src={previewSrc} alt="" onError={() => setBrokenSrc(previewSrc)}
            style={{ width: "100%", height: "100%", objectFit: "cover" }} />
        ) : !value && fallbackSrc && brokenSrc !== fallbackSrc ? (
          /* eslint-disable-next-line @next/next/no-img-element */
          <img src={fallbackSrc} alt="" onError={() => setBrokenSrc(fallbackSrc)}
            style={{ width: "100%", height: "100%", objectFit: "cover", opacity: 0.85 }} />
        ) : (
          <span style={{ fontSize: value ? 9 : 22, color: "var(--at-faint)", textAlign: "center", lineHeight: 1.2, padding: 3, wordBreak: "break-all" }}>
            {value ? "no preview" : "🖼"}
          </span>
        )}
      </div>

      <div style={{ flex: 1, display: "flex", flexDirection: "column", gap: 6 }}>
        <div style={{ display: "flex", gap: 6, flexWrap: "wrap", alignItems: "center" }}>
          <button type="button" onClick={() => fileRef.current?.click()} disabled={uploading} className="btn btn-secondary btn-sm">
            {uploading ? "Uploading…" : value ? "Change" : "Upload"}
          </button>
          {uploading && (
            <button type="button" onClick={cancelUpload} className="btn btn-danger btn-sm" title="Cancel upload">
              <X size={12} />
            </button>
          )}
          {value && (
            <button type="button" onClick={() => { onChange(""); setPasteOpen(false); }} className="btn btn-danger btn-sm">
              Remove
            </button>
          )}
          <button type="button" onClick={() => { setPasteOpen((o) => !o); setPasteVal(value); }} className="btn btn-ghost btn-sm">
            {pasteOpen ? "hide URL" : "paste URL"}
          </button>
        </div>

        {pasteOpen && (
          <div style={{ display: "flex", gap: 6 }}>
            <input
              value={pasteVal}
              onChange={(e) => setPasteVal(e.target.value)}
              placeholder="https://… or a bare filename"
              style={{
                flex: 1, padding: "7px 12px", borderRadius: 9, fontSize: 13,
                border: "1px solid var(--at-border-input)", background: "var(--at-input)",
                color: "var(--at-text)", outline: "none", fontFamily: "inherit",
              }}
            />
            <button type="button" onClick={() => { onChange(pasteVal); setPasteOpen(false); }} className="btn btn-primary btn-sm">
              Set
            </button>
          </div>
        )}

        {uploadError && <p style={{ margin: 0, fontSize: 12, color: "#f87171" }}>{uploadError}</p>}
      </div>

      <input ref={fileRef} type="file" accept="image/*" style={{ display: "none" }} onChange={handleFile} />
    </div>
  );
}
