import "server-only";
import {
  getSiteSettings, getPageByPath, getPost, getService, getTeamMember,
} from "./content";

/**
 * Structured data (schema.org / JSON-LD) for the public site.
 *
 * The frozen WordPress markup carried a Yoast `@graph` in its <head>, but the
 * renderer strips non-JS <script> tags (see lib/frozen.ts), so none of it
 * reaches the page. These builders reproduce the useful parts from the JSON
 * content store instead, so the graph stays correct as the admin edits data.
 *
 * Nothing here renders visibly — emitted as <script type="application/ld+json">
 * by the layout (Organization + WebSite, site-wide) and the catch-all route
 * (page-specific nodes).
 */

export const SITE_URL = process.env.SITE_URL || "https://ooxlimited.com";

const ORG_ID = `${SITE_URL}/#organization`;
const WEBSITE_ID = `${SITE_URL}/#website`;

/** Absolutise a site-relative path ("/wp-content/…" → "https://…/wp-content/…"). */
function abs(url: string | undefined | null): string | undefined {
  if (!url) return undefined;
  if (/^https?:\/\//i.test(url)) return url;
  return `${SITE_URL}${url.startsWith("/") ? "" : "/"}${url}`;
}

/** JSON.stringify with `<` escaped, per the Next.js JSON-LD guidance. */
export function renderJsonLd(data: unknown): string {
  return JSON.stringify(data).replace(/</g, "\\u003c");
}

export function organizationJsonLd() {
  const s = getSiteSettings();
  return {
    "@context": "https://schema.org",
    "@type": "Organization",
    "@id": ORG_ID,
    name: s.title,
    url: `${SITE_URL}/`,
    logo: {
      "@type": "ImageObject",
      url: abs("/wp-content/uploads/2026/03/ooxlogo.png"),
      width: 154,
      height: 154,
    },
    image: abs("/og-default.png"),
    description: s.tagline,
    email: s.contactEmail,
    sameAs: s.socialLinks.map((l) => l.href).filter(Boolean),
  };
}

export function websiteJsonLd() {
  const s = getSiteSettings();
  return {
    "@context": "https://schema.org",
    "@type": "WebSite",
    "@id": WEBSITE_ID,
    url: `${SITE_URL}/`,
    name: s.title,
    description: s.tagline,
    publisher: { "@id": ORG_ID },
    inLanguage: "en-US",
  };
}

interface Crumb {
  name: string;
  path: string;
}

function breadcrumbJsonLd(crumbs: Crumb[]) {
  return {
    "@context": "https://schema.org",
    "@type": "BreadcrumbList",
    itemListElement: crumbs.map((c, i) => ({
      "@type": "ListItem",
      position: i + 1,
      name: c.name,
      item: `${SITE_URL}${c.path}`,
    })),
  };
}

/**
 * Page-specific JSON-LD nodes for a route. Returns an array of graph objects
 * (each rendered as its own <script>). Empty for routes with nothing to add
 * beyond the site-wide Organization / WebSite.
 */
export function pageJsonLd(path: string): unknown[] {
  const url = `${SITE_URL}${path}`;
  const out: unknown[] = [];

  // Blog post: /<slug>/
  const postSlug = path.replace(/^\/|\/$/g, "");
  const post = postSlug && !postSlug.includes("/") ? getPost(postSlug) : null;
  if (post) {
    out.push({
      "@context": "https://schema.org",
      "@type": "BlogPosting",
      "@id": `${url}#article`,
      isPartOf: { "@id": WEBSITE_ID },
      mainEntityOfPage: url,
      headline: post.title,
      description: post.metaDescription || post.excerpt || undefined,
      datePublished: post.date || undefined,
      dateModified: post.modified || post.date || undefined,
      author: { "@type": "Organization", "@id": ORG_ID, name: getSiteSettings().title },
      publisher: { "@id": ORG_ID },
      image: post.featuredImage?.url ? abs(post.featuredImage.url) : abs("/og-default.png"),
      inLanguage: "en-US",
    });
    out.push(breadcrumbJsonLd([
      { name: "Home", path: "/" },
      { name: "Blog", path: "/blog/" },
      { name: post.title, path },
    ]));
    return out;
  }

  // Service: /service/<slug>/
  const svcMatch = path.match(/^\/service\/([^/]+)\/$/);
  if (svcMatch) {
    const svc = getService(svcMatch[1]);
    if (svc) {
      out.push({
        "@context": "https://schema.org",
        "@type": "Service",
        "@id": `${url}#service`,
        name: svc.title,
        description: svc.metaDescription || svc.excerpt || undefined,
        serviceType: svc.title,
        provider: { "@id": ORG_ID },
        areaServed: "Worldwide",
        url,
      });
      out.push(breadcrumbJsonLd([
        { name: "Home", path: "/" },
        { name: "Services", path: "/services/" },
        { name: svc.title, path },
      ]));
    }
    return out;
  }

  // Team member: /team/<slug>/
  const teamMatch = path.match(/^\/team\/([^/]+)\/$/);
  if (teamMatch) {
    const member = getTeamMember(teamMatch[1]);
    if (member) {
      out.push({
        "@context": "https://schema.org",
        "@type": "Person",
        "@id": `${url}#person`,
        name: member.name,
        jobTitle: member.position || undefined,
        worksFor: { "@id": ORG_ID },
        image: member.photo?.url ? abs(member.photo.url) : undefined,
        url,
      });
      out.push(breadcrumbJsonLd([
        { name: "Home", path: "/" },
        { name: "Our Team", path: "/game-development-team/" },
        { name: member.name, path },
      ]));
    }
    return out;
  }

  // Marketing pages: breadcrumb only (Home > Page), skip the front page.
  const page = getPageByPath(path);
  if (page && !page.isFront) {
    out.push(breadcrumbJsonLd([
      { name: "Home", path: "/" },
      { name: page.title, path },
    ]));
  }
  return out;
}
