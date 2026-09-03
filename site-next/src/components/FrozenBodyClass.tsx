"use client";

import { useEffect } from "react";

/**
 * The frozen WordPress markup expects its classes on <body> (theme CSS targets
 * `body.home`, `body.elementor-page-22`, …). The Next root layout owns <body>,
 * so mirror the frozen page's body class / lang onto it.
 *
 * The class has to be on <body> *before first paint*, not just after hydration:
 * the omero theme's `#page .site-content` carries a 140px top/bottom margin that
 * `body.elementor-page` (present on every frozen page) resets to 0. Applying the
 * class only in the effect below let the page paint once with the 140px margin
 * and then jump when hydration removed it. So we also emit a tiny synchronous
 * inline script that sets the class during HTML parse; the effect stays for
 * client-side re-renders (e.g. the admin preview panel) and unmount cleanup.
 */
export default function FrozenBodyClass({
  className,
  lang,
}: {
  className: string;
  lang?: string;
}) {
  useEffect(() => {
    const prevClass = document.body.className;
    const prevLang = document.documentElement.lang;
    document.body.className = className;
    if (lang) document.documentElement.lang = lang;
    return () => {
      document.body.className = prevClass;
      document.documentElement.lang = prevLang;
    };
  }, [className, lang]);

  const code =
    `document.body.className=${JSON.stringify(className)};` +
    (lang ? `document.documentElement.lang=${JSON.stringify(lang)};` : "");

  return <script dangerouslySetInnerHTML={{ __html: code }} />;
}
