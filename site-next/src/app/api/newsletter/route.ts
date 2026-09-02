import { NextRequest, NextResponse } from "next/server";
import { getSiteSettings, recordSubmission, setSubmissionEmailStatus } from "@/lib/content";
import { isValidEmail } from "@/lib/mail";

/**
 * Newsletter signup. Stores every signup in submissions.json, and — when a
 * Mailchimp audience is configured (MAILCHIMP_API_KEY + MAILCHIMP_LIST_ID or
 * siteSettings.mailchimpListId) — adds the address to that audience with
 * `status: "pending"` (double opt-in, matching the WordPress setup).
 */
export async function POST(request: NextRequest) {
  let email = "";
  const ct = request.headers.get("content-type") ?? "";
  if (ct.includes("application/json")) {
    email = String((await request.json().catch(() => ({}))).email ?? "").trim();
  } else {
    const form = await request.formData().catch(() => null);
    email = String(form?.get("email") ?? form?.get("ne") ?? form?.get("EMAIL") ?? "").trim();
  }

  if (!isValidEmail(email)) {
    return NextResponse.json({ error: "Please enter a valid email address." }, { status: 400 });
  }

  const submission = recordSubmission({ kind: "newsletter", email, emailStatus: "skipped" });

  const apiKey = process.env.MAILCHIMP_API_KEY;
  const listId =
    process.env.MAILCHIMP_LIST_ID || getSiteSettings().mailchimpListId || "";

  if (apiKey && listId && apiKey.includes("-")) {
    const dc = apiKey.split("-")[1];
    try {
      const res = await fetch(
        `https://${dc}.api.mailchimp.com/3.0/lists/${listId}/members`,
        {
          method: "POST",
          headers: {
            "Content-Type": "application/json",
            Authorization: `Basic ${Buffer.from(`anystring:${apiKey}`).toString("base64")}`,
          },
          body: JSON.stringify({ email_address: email, status: "pending" }),
        },
      );
      const ok = res.ok || res.status === 400; // 400 = already a member; still fine
      setSubmissionEmailStatus(submission.id, ok ? "sent" : "failed");
    } catch {
      setSubmissionEmailStatus(submission.id, "failed");
    }
  }

  return NextResponse.json({
    status: "OK",
    message: "Thanks for subscribing — please check your inbox to confirm.",
  });
}
