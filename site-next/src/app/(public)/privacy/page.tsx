import LegalPage, { legalMetadata } from "@/components/LegalPage";
import { PRIVACY } from "@/lib/legalContent";

// Linked from the App Store / Google Play listings as the privacy policy URL.
export const metadata = legalMetadata(PRIVACY, "/privacy/");

export default function Privacy() {
  return <LegalPage doc={PRIVACY} />;
}
