"use client";

import { useEditor, EditorContent } from "@tiptap/react";
import { posToDOMRect } from "@tiptap/core";
import StarterKit from "@tiptap/starter-kit";
import Link from "@tiptap/extension-link";
import Underline from "@tiptap/extension-underline";
import Placeholder from "@tiptap/extension-placeholder";
import Image from "@tiptap/extension-image";
import { TableKit } from "@tiptap/extension-table";
import { useEffect, useCallback, useRef, useState } from "react";
import {
  Bold, Italic, Underline as UnderlineIcon,
  Heading1, Heading2, Heading3,
  List, ListOrdered, Link as LinkIcon, Unlink, ImageIcon,
  Code, Code2, X, MessageSquareQuote, SquareMousePointer,
  Table as TableIcon, Trash2,
  ArrowLeftToLine, ArrowRightToLine, ArrowUpToLine, ArrowDownToLine,
} from "lucide-react";

interface Props {
  value: string;
  onChange: (html: string) => void;
}

const CTA_VARIANTS = ["blue", "green", "dark", "purple", "plum", "outline"] as const;

/** Mirror RawHtml `fix()` / PreviewPanel `normalizeBody()`: migrated bodies carry
 *  in-body paths that lost their leading slash (`src="uploads/…"`,
 *  `template/original/gfx/…`, `href="en/…"`). Root them before they reach the
 *  editor DOM, where — unlike the frontend and the preview iframe — there is no
 *  <base href> to save them, so `uploads/foo.jpg` 404s against the admin URL.
 *  Save already normalises via sanitize.ts; this fixes the pre-save display. */
function normalizeAssetPaths(html: string): string {
  return html
    .replace(/(\s(?:src|href))=(["'])uploads\//gi, "$1=$2/uploads/")
    .replace(/(\s(?:src|href))=(["'])template\/original\/gfx\//gi, "$1=$2/gfx/")
    .replace(/(\shref)=(["'])((?:en|es|ru|pt)\/)/gi, "$1=$2/$3");
}

function BubbleBtn({ onClick, active, title, children }: {
  onClick: () => void; active?: boolean; title: string; children: React.ReactNode;
}) {
  return (
    <button
      type="button"
      onMouseDown={(e) => e.preventDefault()}
      onClick={onClick}
      title={title}
      style={{
        padding: "5px 7px", border: "none", borderRadius: 6,
        background: active ? "rgba(99,102,241,0.35)" : "transparent",
        color: active ? "#a5b4fc" : "rgba(255,255,255,0.72)",
        cursor: "pointer", display: "flex", alignItems: "center", justifyContent: "center",
        transition: "background 0.1s, color 0.1s", flexShrink: 0,
      }}
    >
      {children}
    </button>
  );
}

export default function RichTextEditor({ value, onChange }: Props) {
  const fileRef = useRef<HTMLInputElement>(null);
  const [uploading, setUploading] = useState(false);
  const [bubbleVisible, setBubbleVisible] = useState(false);
  const [bubblePos, setBubblePos] = useState<{ x: number; y: number }>({ x: 0, y: 0 });
  const [showSource, setShowSource] = useState(false);
  const [sourceHtml, setSourceHtml] = useState("");
  const [ctaForm, setCtaForm] = useState<{
    label: string; href: string; variant: typeof CTA_VARIANTS[number];
  } | null>(null);

  const editor = useEditor({
    extensions: [
      StarterKit.configure({ heading: { levels: [1, 2, 3] } }),
      Underline,
      Link.configure({ openOnClick: false, HTMLAttributes: { rel: "noopener noreferrer", target: "_blank" } }),
      Image.configure({ inline: false, allowBase64: false }),
      TableKit.configure({ table: { resizable: true } }),
      Placeholder.configure({ placeholder: "Write your content here…" }),
    ],
    content: normalizeAssetPaths(value || ""),
    onUpdate: ({ editor }) => onChange(editor.getHTML()),
    editorProps: { attributes: { style: "outline:none; min-height:320px;" } },
    immediatelyRender: false,
  });

  useEffect(() => {
    if (!editor) return;
    const next = normalizeAssetPaths(value || "");
    if (next !== editor.getHTML()) {
      editor.commands.setContent(next, { emitUpdate: false });
    }
  }, [value, editor]);

  useEffect(() => {
    if (!editor) return;
    const updateBubble = () => {
      const { state, view } = editor;
      const { from, to } = state.selection;
      if (from === to || !editor.isFocused) { setBubbleVisible(false); return; }
      try {
        const rect = posToDOMRect(view, from, to);
        setBubblePos({ x: rect.left + rect.width / 2, y: rect.top });
        setBubbleVisible(true);
      } catch { setBubbleVisible(false); }
    };
    const hideBubble = () => setBubbleVisible(false);
    editor.on("selectionUpdate", updateBubble);
    editor.on("transaction", updateBubble);
    editor.on("blur", hideBubble);
    window.addEventListener("scroll", updateBubble, true);
    return () => {
      editor.off("selectionUpdate", updateBubble);
      editor.off("transaction", updateBubble);
      editor.off("blur", hideBubble);
      window.removeEventListener("scroll", updateBubble, true);
    };
  }, [editor]);

  const setLink = useCallback(() => {
    if (!editor) return;
    const prev = editor.getAttributes("link").href ?? "";
    const url = window.prompt("Enter URL:", prev);
    if (url === null) return;
    if (url === "") editor.chain().focus().unsetLink().run();
    else editor.chain().focus().setLink({ href: url }).run();
  }, [editor]);

  const handleImageFile = useCallback(async (e: React.ChangeEvent<HTMLInputElement>) => {
    if (!editor) return;
    const file = e.target.files?.[0];
    if (!file) return;
    if (fileRef.current) fileRef.current.value = "";
    setUploading(true);
    try {
      const fd = new FormData();
      fd.append("file", file);
      const res = await fetch("/api/admin/upload", { method: "POST", body: fd });
      const data = await res.json();
      if (res.ok) editor.chain().focus().setImage({ src: data.url }).run();
      else alert(data.error ?? "Upload failed");
    } catch { alert("Connection error during upload"); }
    finally { setUploading(false); }
  }, [editor]);

  const toggleSource = useCallback(() => {
    if (!editor) return;
    if (!showSource) setSourceHtml(editor.getHTML());
    else editor.commands.setContent(sourceHtml, { emitUpdate: true });
    setShowSource((s) => !s);
    setBubbleVisible(false);
  }, [editor, showSource, sourceHtml]);

  const insertCallout = useCallback(() => {
    if (!editor) return;
    const { state } = editor;
    const selectedText = state.doc.textBetween(state.selection.from, state.selection.to, " ");
    const text = window.prompt("Callout text:", selectedText || "Quick takeaway: …");
    if (!text) return;
    editor.chain().focus().insertContent(`<div class="callout">${text.replace(/</g, "&lt;")}</div><p></p>`).run();
  }, [editor]);

  const insertTable = useCallback(() => {
    if (!editor) return;
    editor.chain().focus().insertTable({ rows: 3, cols: 3, withHeaderRow: true }).run();
  }, [editor]);

  const insertCta = useCallback(() => {
    if (!editor || !ctaForm) return;
    const { label, href, variant } = ctaForm;
    if (!label.trim() || !href.trim()) return;
    const esc = (s: string) => s.replace(/</g, "&lt;").replace(/"/g, "&quot;");
    editor.chain().focus().insertContent(
      `<div class="cta-block"><a class="cta-button cta-button--${variant}" href="${esc(href)}" target="_blank" rel="noopener noreferrer sponsored">${esc(label)}</a></div><p></p>`
    ).run();
    setCtaForm(null);
  }, [editor, ctaForm]);

  if (!editor) return null;

  const BUBBLE_W = 420;
  const BUBBLE_H = 40;
  const GAP = 8;
  const clampedX = typeof window !== "undefined"
    ? Math.max(8, Math.min(bubblePos.x - BUBBLE_W / 2, window.innerWidth - BUBBLE_W - 8))
    : bubblePos.x - BUBBLE_W / 2;
  const aboveY = bubblePos.y - BUBBLE_H - GAP;
  const clampedY = aboveY < 8 ? bubblePos.y + 22 + GAP : aboveY;

  const divider = <div style={{ width: 1, height: 14, margin: "0 3px", background: "rgba(255,255,255,0.12)", flexShrink: 0 }} />;
  const barBtn = {
    display: "flex", alignItems: "center", gap: 5, padding: "4px 10px", borderRadius: 7,
    border: "1px solid var(--at-border-input)", background: "var(--at-input)", color: "var(--at-muted)",
    cursor: "pointer", fontSize: 12, fontFamily: "inherit", transition: "all 0.15s",
  } as const;

  return (
    <>
      {bubbleVisible && !showSource && (
        <div
          onMouseDown={(e) => e.preventDefault()}
          style={{
            position: "fixed", left: clampedX, top: clampedY, zIndex: 9999,
            display: "flex", alignItems: "center", gap: 1, padding: "4px 6px", borderRadius: 10,
            background: "rgba(10,10,20,0.96)", border: "1px solid rgba(99,102,241,0.28)",
            boxShadow: "0 8px 28px rgba(0,0,0,0.55)", backdropFilter: "blur(16px)", WebkitBackdropFilter: "blur(16px)",
          }}
        >
          <BubbleBtn onClick={() => editor.chain().focus().toggleBold().run()} active={editor.isActive("bold")} title="Bold"><Bold size={12} /></BubbleBtn>
          <BubbleBtn onClick={() => editor.chain().focus().toggleItalic().run()} active={editor.isActive("italic")} title="Italic"><Italic size={12} /></BubbleBtn>
          <BubbleBtn onClick={() => editor.chain().focus().toggleUnderline().run()} active={editor.isActive("underline")} title="Underline"><UnderlineIcon size={12} /></BubbleBtn>
          {divider}
          <BubbleBtn onClick={() => editor.chain().focus().toggleHeading({ level: 1 }).run()} active={editor.isActive("heading", { level: 1 })} title="H1"><Heading1 size={12} /></BubbleBtn>
          <BubbleBtn onClick={() => editor.chain().focus().toggleHeading({ level: 2 }).run()} active={editor.isActive("heading", { level: 2 })} title="H2"><Heading2 size={12} /></BubbleBtn>
          <BubbleBtn onClick={() => editor.chain().focus().toggleHeading({ level: 3 }).run()} active={editor.isActive("heading", { level: 3 })} title="H3"><Heading3 size={12} /></BubbleBtn>
          {divider}
          <BubbleBtn onClick={() => editor.chain().focus().toggleBulletList().run()} active={editor.isActive("bulletList")} title="Bullet list"><List size={12} /></BubbleBtn>
          <BubbleBtn onClick={() => editor.chain().focus().toggleOrderedList().run()} active={editor.isActive("orderedList")} title="Ordered list"><ListOrdered size={12} /></BubbleBtn>
          {divider}
          <BubbleBtn onClick={() => editor.chain().focus().toggleCode().run()} active={editor.isActive("code")} title="Inline code"><Code size={12} /></BubbleBtn>
          <BubbleBtn onClick={() => editor.chain().focus().toggleCodeBlock().run()} active={editor.isActive("codeBlock")} title="Code block"><Code2 size={12} /></BubbleBtn>
          {divider}
          <BubbleBtn onClick={setLink} active={editor.isActive("link")} title="Add link"><LinkIcon size={12} /></BubbleBtn>
          {editor.isActive("link") && (
            <BubbleBtn onClick={() => editor.chain().focus().unsetLink().run()} title="Remove link"><Unlink size={12} /></BubbleBtn>
          )}
        </div>
      )}

      {ctaForm && (
        <div
          onClick={() => setCtaForm(null)}
          style={{ position: "fixed", inset: 0, zIndex: 9999, background: "rgba(0,0,0,0.45)", display: "flex", alignItems: "center", justifyContent: "center", padding: 20 }}
        >
          <div
            onClick={(e) => e.stopPropagation()}
            style={{ background: "var(--at-card)", border: "1px solid var(--at-border)", borderRadius: 16, padding: 22, width: "100%", maxWidth: 380, boxShadow: "0 24px 64px rgba(0,0,0,0.4)" }}
          >
            <div style={{ fontSize: 15, fontWeight: 600, color: "var(--at-text)", marginBottom: 16 }}>Insert CTA button</div>
            <div style={{ display: "flex", flexDirection: "column", gap: 12 }}>
              <div>
                <label style={{ display: "block", fontSize: 11, fontWeight: 500, color: "var(--at-muted)", marginBottom: 5, textTransform: "uppercase", letterSpacing: "0.06em" }}>Label</label>
                <input
                  autoFocus value={ctaForm.label}
                  onChange={(e) => setCtaForm({ ...ctaForm, label: e.target.value })}
                  placeholder="Upgrade your Skrill account"
                  style={{ width: "100%", padding: "8px 12px", borderRadius: 9, fontSize: 13, border: "1px solid var(--at-border-input)", background: "var(--at-input)", color: "var(--at-text)", outline: "none", fontFamily: "inherit", boxSizing: "border-box" }}
                />
              </div>
              <div>
                <label style={{ display: "block", fontSize: 11, fontWeight: 500, color: "var(--at-muted)", marginBottom: 5, textTransform: "uppercase", letterSpacing: "0.06em" }}>URL</label>
                <input
                  value={ctaForm.href}
                  onChange={(e) => setCtaForm({ ...ctaForm, href: e.target.value })}
                  placeholder="https:// or /en/upgrade/"
                  style={{ width: "100%", padding: "8px 12px", borderRadius: 9, fontSize: 13, border: "1px solid var(--at-border-input)", background: "var(--at-input)", color: "var(--at-text)", outline: "none", fontFamily: "inherit", boxSizing: "border-box" }}
                />
              </div>
              <div>
                <label style={{ display: "block", fontSize: 11, fontWeight: 500, color: "var(--at-muted)", marginBottom: 5, textTransform: "uppercase", letterSpacing: "0.06em" }}>Style</label>
                <div style={{ display: "flex", gap: 6, flexWrap: "wrap" }}>
                  {CTA_VARIANTS.map((v) => (
                    <button key={v} type="button" onClick={() => setCtaForm({ ...ctaForm, variant: v })}
                      className={`btn-pill ${ctaForm.variant === v ? "active" : ""}`} style={{ textTransform: "capitalize" }}>
                      {v}
                    </button>
                  ))}
                </div>
              </div>
            </div>
            <div style={{ display: "flex", gap: 8, justifyContent: "flex-end", marginTop: 20 }}>
              <button type="button" onClick={() => setCtaForm(null)} className="btn btn-secondary">Cancel</button>
              <button type="button" onClick={insertCta} className="btn btn-primary" disabled={!ctaForm.label.trim() || !ctaForm.href.trim()}>Insert</button>
            </div>
          </div>
        </div>
      )}

      <div style={{ borderRadius: 12, border: "1px solid var(--at-border-input-alt)" }}>
        <div style={{
          display: "flex", alignItems: "center", justifyContent: "space-between",
          padding: "6px 10px 6px 14px", background: "var(--at-card)",
          borderBottom: "1px solid var(--at-border)", borderRadius: "12px 12px 0 0",
        }}>
          <span style={{ fontSize: 11, color: "var(--at-faint)", letterSpacing: "0.04em", userSelect: "none" }}>
            {showSource ? "Editing raw HTML" : "Select text to format"}
          </span>
          <div style={{ display: "flex", alignItems: "center", gap: 6 }}>
            {!showSource && (
              <>
                <button type="button" onClick={() => fileRef.current?.click()} disabled={uploading} style={barBtn}>
                  <ImageIcon size={11} />{uploading ? "Uploading…" : "Image"}
                </button>
                <button type="button" onClick={insertCallout} style={barBtn}>
                  <MessageSquareQuote size={11} />Callout
                </button>
                <button type="button" onClick={() => setCtaForm({ label: "", href: "", variant: "blue" })} style={barBtn}>
                  <SquareMousePointer size={11} />CTA button
                </button>
                <button type="button" onClick={insertTable} style={barBtn}>
                  <TableIcon size={11} />Table
                </button>
              </>
            )}
            <button
              type="button" onClick={toggleSource}
              style={{
                ...barBtn,
                border: `1px solid ${showSource ? "rgba(99,102,241,0.45)" : "var(--at-border-input)"}`,
                background: showSource ? "rgba(99,102,241,0.12)" : "var(--at-input)",
                color: showSource ? "#a5b4fc" : "var(--at-muted)",
              }}
            >
              {showSource ? <X size={11} /> : <Code2 size={11} />}
              {showSource ? "Visual" : "Source"}
            </button>
          </div>
          <input ref={fileRef} type="file" accept="image/*" style={{ display: "none" }} onChange={handleImageFile} />
        </div>

        {!showSource && editor.isActive("table") && (
          <div style={{
            display: "flex", alignItems: "center", flexWrap: "wrap", gap: 4,
            padding: "6px 12px", background: "var(--at-input)",
            borderBottom: "1px solid var(--at-border)",
          }}>
            <span style={{ fontSize: 11, color: "var(--at-faint)", marginRight: 4 }}>Table</span>
            <button type="button" style={barBtn} onClick={() => editor.chain().focus().addColumnBefore().run()} title="Add column left"><ArrowLeftToLine size={11} />Col</button>
            <button type="button" style={barBtn} onClick={() => editor.chain().focus().addColumnAfter().run()} title="Add column right">Col<ArrowRightToLine size={11} /></button>
            <button type="button" style={barBtn} onClick={() => editor.chain().focus().addRowBefore().run()} title="Add row above"><ArrowUpToLine size={11} />Row</button>
            <button type="button" style={barBtn} onClick={() => editor.chain().focus().addRowAfter().run()} title="Add row below"><ArrowDownToLine size={11} />Row</button>
            {divider}
            <button type="button" style={barBtn} onClick={() => editor.chain().focus().deleteColumn().run()} title="Delete column">− Col</button>
            <button type="button" style={barBtn} onClick={() => editor.chain().focus().deleteRow().run()} title="Delete row">− Row</button>
            {divider}
            <button type="button" style={barBtn} onClick={() => editor.chain().focus().toggleHeaderRow().run()} title="Toggle header row">Header</button>
            <button type="button" style={barBtn} onClick={() => editor.chain().focus().mergeOrSplit().run()} title="Merge / split cells">Merge</button>
            {divider}
            <button type="button" style={{ ...barBtn, color: "#f87171", borderColor: "rgba(239,68,68,0.3)" }} onClick={() => editor.chain().focus().deleteTable().run()} title="Delete table"><Trash2 size={11} />Table</button>
          </div>
        )}

        <div style={{ padding: 16, background: "var(--at-row-even)", borderRadius: "0 0 12px 12px" }}>
          <style>{`
            .ProseMirror p.is-editor-empty:first-child::before { content: attr(data-placeholder); float: left; color: var(--at-placeholder); pointer-events: none; height: 0; }
            .ProseMirror h1 { font-size:1.75rem; font-weight:700; margin:1rem 0 0.5rem; color:var(--at-text); }
            .ProseMirror h2 { font-size:1.35rem; font-weight:600; margin:0.875rem 0 0.4rem; color:var(--at-text); }
            .ProseMirror h3 { font-size:1.1rem; font-weight:600; margin:0.75rem 0 0.35rem; color:var(--at-text); }
            .ProseMirror p { margin:0.5rem 0; line-height:1.7; color:var(--at-prose-p); font-size:0.9rem; }
            .ProseMirror ul, .ProseMirror ol { margin:0.5rem 0; padding-left:1.25rem; color:var(--at-prose-p); font-size:0.9rem; }
            .ProseMirror li { margin:0.2rem 0; }
            .ProseMirror strong { color:var(--at-prose-strong); }
            .ProseMirror a { color:#6366f1; text-decoration:underline; }
            .ProseMirror blockquote { border-left:3px solid rgba(99,102,241,0.4); padding-left:1rem; margin:1rem 0; color:var(--at-muted); }
            .ProseMirror code { background:var(--at-prose-code-bg); border-radius:4px; padding:0.1em 0.3em; font-size:0.85em; color:var(--at-prose-code); }
            .ProseMirror pre { background:var(--at-prose-pre-bg); border:1px solid var(--at-prose-pre-border); border-radius:8px; padding:1rem; margin:0.75rem 0; }
            .ProseMirror pre code { background:none; padding:0; }
            .ProseMirror img { max-width:100%; height:auto; border-radius:8px; margin:0.75rem 0; display:block; }
            .ProseMirror img.ProseMirror-selectednode { outline:2px solid #6366f1; border-radius:8px; }
            .ProseMirror div.callout { margin:0.75rem 0; border:1px solid rgba(99,102,241,0.25); background:rgba(99,102,241,0.08); border-radius:14px; padding:12px 14px; font-weight:600; font-size:0.85rem; color:var(--at-text); }
            .ProseMirror div.cta-block { margin:0.75rem 0; }
            .ProseMirror a.cta-button { display:inline-flex; padding:8px 16px; border-radius:999px; background:#6366f1; color:#fff; font-weight:700; font-size:0.8rem; text-decoration:none; }
            .ProseMirror table { border-collapse:collapse; width:100%; margin:0.9rem 0; font-size:0.85rem; table-layout:fixed; overflow:hidden; }
            .ProseMirror table td, .ProseMirror table th { border:1px solid var(--at-border-input); padding:7px 10px; vertical-align:top; text-align:left; position:relative; min-width:3em; color:var(--at-prose-p); }
            .ProseMirror table th { background:var(--at-input-alt); font-weight:600; color:var(--at-prose-strong); }
            .ProseMirror table .selectedCell:after { content:""; position:absolute; inset:0; background:rgba(99,102,241,0.18); pointer-events:none; }
            .ProseMirror table .column-resize-handle { position:absolute; right:-2px; top:0; bottom:-2px; width:4px; background:#6366f1; pointer-events:none; }
            .ProseMirror .tableWrapper { overflow-x:auto; }
            .ProseMirror.resize-cursor { cursor:col-resize; }
          `}</style>
          {showSource ? (
            <textarea
              value={sourceHtml}
              onChange={(e) => { setSourceHtml(e.target.value); onChange(e.target.value); }}
              spellCheck={false}
              style={{
                width: "100%", minHeight: 320, padding: "10px 12px", borderRadius: 8,
                border: "1px solid var(--at-border-input)", background: "var(--at-input)",
                color: "var(--at-prose-p)", fontSize: 12, fontFamily: "monospace",
                lineHeight: 1.6, resize: "vertical", outline: "none", boxSizing: "border-box",
              }}
            />
          ) : (
            <EditorContent editor={editor} />
          )}
        </div>
      </div>
    </>
  );
}
