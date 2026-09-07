/** Pure dotted-path read ("featuredImage.url"). Safe on client and server.
 *  Collection schemas address nested record fields this way (see
 *  adminSchema.seo.imageField), so every consumer must resolve them the same. */
export function dotGet(row: unknown, key: string): unknown {
  return key
    .split(".")
    .reduce<unknown>(
      (acc, k) => (acc && typeof acc === "object" ? (acc as Record<string, unknown>)[k] : undefined),
      row,
    );
}
