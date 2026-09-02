/** Pure route-path → pagemap/edits key. Safe on client and server.
 *  "/" → "home", "/a/b/" → "a__b". Mirrors frozen.ts routeKey. */
export function routeKey(routePath: string): string {
  if (routePath === "/" || routePath === "") return "home";
  return routePath.replace(/^\/|\/$/g, "").replace(/\//g, "__") || "home";
}
