import AdminPageSkeleton from "@/components/admin/AdminPageSkeleton";

/** Fallback for admin routes without their own loading.tsx. The dashboard,
 *  per-collection, translations, settings, sync and seo routes override this. */
export default function AdminLoading() {
  return <AdminPageSkeleton />;
}
