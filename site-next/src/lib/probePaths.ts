/**
 * Server-side script, archive, backup and config extensions — the shapes vulnerability
 * scanners walk through. This site serves none of them: no page path, post slug or
 * redirect source carries a file extension at all (every route ends in "/"), and the
 * assets Next does serve — /sitemap.xml, /icon.png, /og-default.png, /_css/** — are
 * none of these.
 *
 * Deliberately a deny-list rather than an allow-list of real assets: a missed entry
 * here leaks a little cache, while a missed entry in an allow-list would 404 a live
 * asset the next time public/ grows.
 *
 * Used by the middleware to answer probes before they reach the `[[...slug]]`
 * catch-all, which runs with `revalidate` and `dynamicParams: true` and so writes a
 * permanent on-disk ISR entry for every unique path it 404s.
 */
export const PROBE_EXT =
  /\.(php\d?|phtml|phar|asp|aspx|jsp|jspx|cgi|pl|py|rb|sh|bash|bak|old|orig|save|swp|sql|db|sqlite|zip|rar|7z|tar|t?gz|tgz|env|log|ya?ml|tf|tfstate|ini|conf|cfg|htm|htaccess|htpasswd)$/i;

/** True when a request path is a scanner probe rather than anything this site serves. */
export const isProbePath = (pathname: string): boolean => PROBE_EXT.test(pathname);
