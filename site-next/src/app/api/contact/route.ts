import { NextRequest, NextResponse } from "next/server";
import { getSiteSettings, recordSubmission, setSubmissionEmailStatus } from "@/lib/content";
import { getResend, fromAddress, isValidEmail } from "@/lib/mail";

function clientIp(req: NextRequest): string {
  return (
    req.headers.get("cf-connecting-ip") ??
    req.headers.get("x-forwarded-for")?.split(",")[0].trim() ??
    ""
  );
}

/** Accepts either JSON or the CF7 field names (your-name / your-email / your-message). */
export async function POST(request: NextRequest) {
  let body: Record<string, unknown> = {};
  const ct = request.headers.get("content-type") ?? "";
  if (ct.includes("application/json")) {
    body = await request.json().catch(() => ({}));
  } else {
    const form = await request.formData().catch(() => null);
    if (form) for (const [k, v] of form.entries()) body[k] = typeof v === "string" ? v : "";
  }

  const email = String(body.email ?? body["your-email"] ?? "").trim();
  const message = String(body.message ?? body["your-message"] ?? "").trim();
  const name = String(body.name ?? body["your-name"] ?? "").trim();
  const optIn = Boolean(body.optIn ?? body["checkbox-895"] ?? body["checkbox-895[]"]);

  if (!email || !message) {
    return NextResponse.json({ error: "Please fill in your email and message." }, { status: 400 });
  }
  if (!isValidEmail(email)) {
    return NextResponse.json({ error: "Please enter a valid email address." }, { status: 400 });
  }

  const submission = recordSubmission({
    kind: "contact",
    email,
    name: name || undefined,
    message,
    optIn,
    ip: clientIp(request) || undefined,
    emailStatus: "skipped",
  });

  if (process.env.RESEND_API_KEY) {
    const settings = getSiteSettings();
    const to = settings.contactRecipients?.length ? settings.contactRecipients : [settings.contactEmail];
    const { error } = await getResend().emails.send({
      from: fromAddress(),
      to,
      replyTo: email,
      subject: "ooxlimited.com — contact form",
      text: `From: ${name ? `${name} <${email}>` : email}\nOpt-in: ${optIn ? "yes" : "no"}\n\n${message}`,
      html:
        `<p><strong>From:</strong> ${escapeHtml(name ? `${name} <${email}>` : email)}</p>` +
        `<p><strong>Newsletter opt-in:</strong> ${optIn ? "yes" : "no"}</p>` +
        `<p>${escapeHtml(message).replace(/\n/g, "<br>")}</p>`,
    });
    setSubmissionEmailStatus(submission.id, error ? "failed" : "sent");
  }

  return NextResponse.json({ status: "OK", message: "Thanks — your message has been sent." });
}

function escapeHtml(s: string): string {
  return s.replace(/[&<>"']/g, (c) => ({ "&": "&amp;", "<": "&lt;", ">": "&gt;", '"': "&quot;", "'": "&#39;" }[c]!));
}
