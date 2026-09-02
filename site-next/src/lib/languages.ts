import "server-only";

/** ooxlimited is a single-locale site. This stub keeps the ported admin's
 *  per-locale form scaffolding happy without a real i18n layer. */
export interface Language {
  code: string;
  label: string;
  englishLabel: string;
}

export function getLanguages(): Language[] {
  return [{ code: "en", label: "English", englishLabel: "English" }];
}

export function getLocaleCodes(): string[] {
  return ["en"];
}
