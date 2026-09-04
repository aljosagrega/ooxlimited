export interface ImageRef {
  url: string;
  alt: string;
}

export interface SeoFields {
  metaTitle?: string;
  metaTitleRaw?: string;
  metaDescription?: string;
  canonicalUrl?: string;
  noindex?: boolean;
  ogTitle?: string;
  ogDesc?: string;
  ogImage?: string;
}

export interface TermRef {
  id: number;
  name: string;
  slug: string;
}

export interface Post extends SeoFields {
  id: number;
  slug: string;
  oldSlugs: string[];
  title: string;
  excerpt: string;
  bodyHtml: string;
  date: string;
  modified: string;
  /** legacy author name; the byline is resolved from `authorId` when set */
  author: string;
  /** references an Author record (see authors.json) */
  authorId?: number;
  /** false = draft (hidden from the public site). Absent is treated as live. */
  published?: boolean;
  featuredImage: ImageRef | null;
  categories: TermRef[];
  tags: TermRef[];
}

export interface Author {
  id: number;
  name: string;
  slug: string;
  role: string;
  bio: string;
  email: string;
  photo: ImageRef | null;
}

export interface TeamMember {
  id: number;
  slug: string;
  ord: number;
  name: string;
  position: string;
  job: string;
  experience: string;
  responsibility: string;
  email: string;
  bio: string;
  photo: ImageRef | null;
  socials: Record<string, string>;
  skills: string[];
  programs: string[];
  qa: { question: string; answer: string }[];
}

export interface Service extends SeoFields {
  id: number;
  slug: string;
  ord: number;
  title: string;
  excerpt: string;
  thumbnail: ImageRef | null;
}

export interface Page extends SeoFields {
  id: number;
  slug: string;
  path: string;
  title: string;
  isFront: boolean;
  isBlog: boolean;
  /** admin content edits, keyed by pagemap id (see src/data/pagemaps/) */
  edits?: Record<string, string>;
}

export interface MenuItem {
  id: number;
  label: string;
  url: string;
  parent: number;
  children: MenuItem[];
}

export interface Menus {
  main: MenuItem[];
  footer: MenuItem[];
}

export interface SiteSettings {
  title: string;
  tagline: string;
  contactEmail: string;
  contactRecipients: string[];
  mailFromName: string;
  mailchimpListId: string;
  /** GA4 measurement ID, e.g. "G-XXXXXXX". Blank = no analytics injected. */
  gaId: string;
  /** Extra <script> to inject before </body> — GTM, Meta Pixel, etc. Advanced. */
  headScripts: string;
  socialLinks: { label: string; href: string; icon?: string }[];
}

export interface Redirect {
  from: string;
  to: string;
  type: number;
}
