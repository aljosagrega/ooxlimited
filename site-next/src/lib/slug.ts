// URL slug generator matching the PHP site's Cocur\Slugify output (default
// ruleset + Cyrillic transliteration, which the live site has active — e.g.
// "Обновить" -> "obnovit", "трейдеров" -> "treyderov"). Lowercase, ASCII,
// hyphen-separated, repeats collapsed, ends trimmed.
const CHAR_MAP: Record<string, string> = {
  // Latin-1 / European
  à: "a", á: "a", â: "a", ã: "a", ä: "a", å: "a", ā: "a", ą: "a", ă: "a",
  è: "e", é: "e", ê: "e", ë: "e", ē: "e", ę: "e", ě: "e",
  ì: "i", í: "i", î: "i", ï: "i", ī: "i", į: "i",
  ò: "o", ó: "o", ô: "o", õ: "o", ö: "o", ō: "o", ø: "o", ő: "o",
  ù: "u", ú: "u", û: "u", ü: "u", ū: "u", ů: "u", ű: "u",
  ý: "y", ÿ: "y",
  ñ: "n", ń: "n", ň: "n",
  ç: "c", ć: "c", č: "c", ĉ: "c",
  ß: "ss", æ: "ae", œ: "oe",
  š: "s", ś: "s", ş: "s",
  ž: "z", ź: "z", ż: "z",
  đ: "d", ď: "d", ð: "d",
  ł: "l", ĺ: "l", ľ: "l",
  ř: "r", ŕ: "r",
  ť: "t", ţ: "t",
  ğ: "g",
  // Cyrillic (Cocur\Slugify default ruleset)
  а: "a", б: "b", в: "v", г: "g", д: "d", е: "e", ё: "e", ж: "zh", з: "z",
  и: "i", й: "y", к: "k", л: "l", м: "m", н: "n", о: "o", п: "p", р: "r",
  с: "s", т: "t", у: "u", ф: "f", х: "h", ц: "c", ч: "ch", ш: "sh", щ: "shch",
  ъ: "", ы: "y", ь: "", э: "e", ю: "yu", я: "ya",
  // Ukrainian extras (harmless for ru)
  ґ: "g", є: "e", і: "i", ї: "yi",
};

const MAP_RE = new RegExp(`[${Object.keys(CHAR_MAP).join("")}]`, "g");

export function slugify(input: string): string {
  return (input ?? "")
    .toLowerCase()
    .replace(MAP_RE, (c) => CHAR_MAP[c] ?? c)
    .normalize("NFKD")
    .replace(/[̀-ͯ]/g, "")
    .replace(/[^a-z0-9]+/g, "-")
    .replace(/^-+|-+$/g, "")
    .replace(/-{2,}/g, "-");
}
