import LegalPage, { legalMetadata } from "@/components/LegalPage";
import { TERMS } from "@/lib/legalContent";

// Linked from the App Store / Google Play listings as the terms of service URL.
export const metadata = legalMetadata(TERMS, "/terms-of-service/");

export default function TermsOfService() {
  return <LegalPage doc={TERMS} />;
}
