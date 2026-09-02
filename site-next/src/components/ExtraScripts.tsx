"use client";

import { useEffect } from "react";

/** Injects admin-configured raw markup (GTM, Meta Pixel, custom <script>) just
 *  after the page is interactive. `<script>` in dangerouslySetInnerHTML never
 *  runs, so parse it out and append real elements. */
export default function ExtraScripts({ html }: { html: string }) {
  useEffect(() => {
    if (!html.trim()) return;
    const tpl = document.createElement("div");
    tpl.innerHTML = html;
    const added: Element[] = [];
    tpl.childNodes.forEach((node) => {
      if (node.nodeName === "SCRIPT") {
        const src = node as HTMLScriptElement;
        const el = document.createElement("script");
        for (const attr of src.attributes) el.setAttribute(attr.name, attr.value);
        if (!src.src) el.textContent = src.textContent;
        document.body.appendChild(el);
        added.push(el);
      } else if (node.nodeType === 1) {
        document.body.appendChild(node.cloneNode(true));
        added.push(node as Element);
      }
    });
    return () => added.forEach((el) => el.remove());
  }, [html]);
  return null;
}
