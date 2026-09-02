"use client";

import { useEffect } from "react";

/**
 * The frozen WordPress markup expects its classes on <body> (theme CSS targets
 * `body.home`, `body.elementor-page-22`, …). The Next root layout owns <body>,
 * so mirror the frozen page's body class / lang onto it at runtime.
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

  return null;
}
