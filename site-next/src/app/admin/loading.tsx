import AdminPageSkeleton from "@/components/admin/AdminPageSkeleton";

/** Fallback for admin routes without their own loading.tsx. Renders the neutral
 *  "generic" variant — routes with a distinct layout (dashboard, per-collection,
 *  pages, menus, translations, settings, sync, seo) override this with a
 *  shape-matched skeleton so the wrong page's frame never flashes on nav. */
export default function AdminLoading() {
  return <AdminPageSkeleton variant="generic" />;
}
