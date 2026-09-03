import "server-only";
import {
  getSiteSettings, getPageByPath, getPost, getService, getTeamMember,
  getTeam, getServices, getAllPosts,
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
  const services = getServices();
  const teamSize = getTeam().length;
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
    slogan: s.tagline,
    email: s.contactEmail,
    ...(teamSize ? { numberOfEmployees: teamSize } : {}),
    // What the studio does — helps entity understanding in AI/answer engines.
    knowsAbout: [
      "Mobile game development",
      "Game prototyping",
      "App development",
      "Unity development",
      ...services.map((svc) => svc.title),
    ],
    contactPoint: {
      "@type": "ContactPoint",
      contactType: "customer support",
      email: s.contactEmail,
      url: `${SITE_URL}/contact-us/`,
      availableLanguage: ["English"],
    },
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
    const words = post.bodyHtml
      ? post.bodyHtml.replace(/<[^>]+>/g, " ").split(/\s+/).filter(Boolean).length
      : 0;
    const image = post.featuredImage?.url
      ? {
          "@type": "ImageObject",
          url: abs(post.featuredImage.url),
          ...(post.featuredImage.alt ? { caption: post.featuredImage.alt } : {}),
        }
      : abs("/og-default.png");
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
      image,
      ...(words ? { wordCount: words } : {}),
      ...(post.categories?.length
        ? { articleSection: post.categories.map((c) => c.name) }
        : {}),
      ...(post.tags?.length ? { keywords: post.tags.map((t) => t.name).join(", ") } : {}),
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
      // team.json still carries omero-theme demo data on some fields
      // (info@example.com, facebook.com/themelexus). Emit a field only when
      // it's real, so the graph never asserts a placeholder as fact.
      const realEmail =
        member.email && !/@example\.(com|org)$/i.test(member.email) ? member.email : undefined;
      const social = Object.values(member.socials || {}).filter(
        (u): u is string =>
          typeof u === "string" && /^https?:\/\//.test(u) && !/themelexus/i.test(u),
      );
      out.push({
        "@context": "https://schema.org",
        "@type": "Person",
        "@id": `${url}#person`,
        name: member.name,
        jobTitle: member.position || member.job || undefined,
        worksFor: { "@id": ORG_ID },
        image: member.photo?.url ? abs(member.photo.url) : undefined,
        ...(realEmail ? { email: realEmail } : {}),
        ...(member.skills?.length ? { knowsAbout: member.skills } : {}),
        ...(social.length ? { sameAs: social } : {}),
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

  // Blog listing: a Blog node linking its posts, plus a breadcrumb.
  const page = getPageByPath(path);
  if (page && (page.isBlog || page.path === "/blog/")) {
    const posts = getAllPosts()
      .filter((p) => !p.noindex)
      .sort((a, b) => (b.date || "").localeCompare(a.date || ""));
    out.push({
      "@context": "https://schema.org",
      "@type": "Blog",
      "@id": `${url}#blog`,
      name: page.title,
      url,
      isPartOf: { "@id": WEBSITE_ID },
      publisher: { "@id": ORG_ID },
      inLanguage: "en-US",
      blogPost: posts.slice(0, 20).map((p) => ({
        "@type": "BlogPosting",
        "@id": `${SITE_URL}/${p.slug}/#article`,
        headline: p.title,
        url: `${SITE_URL}/${p.slug}/`,
        datePublished: p.date || undefined,
      })),
    });
    out.push(breadcrumbJsonLd([
      { name: "Home", path: "/" },
      { name: page.title, path },
    ]));
    return out;
  }

  // Marketing pages: breadcrumb only (Home > Page), skip the front page.
  if (page && !page.isFront) {
    out.push(breadcrumbJsonLd([
      { name: "Home", path: "/" },
      { name: page.title, path },
    ]));
  }
  return out;
}
