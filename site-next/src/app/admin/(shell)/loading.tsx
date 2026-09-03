import AdminPageSkeleton from "@/components/admin/AdminPageSkeleton";

/** Fallback for admin routes without their own loading.tsx. Renders the neutral
 *  "generic" variant. Routes with a distinct layout each ship a loading.tsx that
 *  picks the matching variant (dashboard, list, pages, form, recordTabs,
 *  pageEditor, menus, settings, seo) so the wrong page's frame never flashes. */
export default function AdminLoading() {
  return <AdminPageSkeleton variant="generic" />;
}
