# SEO audit → site-next action plan

Source: external SEO audit of the live WordPress site (`ooxlimited.com`), Sept 2026.
This maps every finding to its status in the `site-next` rebuild and lists what is
left to do. The rebuild's architecture (Next.js Metadata API for `<head>`,
statically enumerated routes, server-rendered frozen HTML) already neutralises
most of the audit. The remaining work is small and concentrated in three files:
`src/data/redirects.json`, `src/lib/chromePatch.ts` / `src/lib/frozenFixups.ts`,
and `src/data/posts.json`.

---

## Scorecard

| Audit finding | Live site | site-next today | Work left |
|---|---|---|---|
| 43 URLs blocked by robots.txt | Yoast + LiteSpeed `Disallow: /` residue | `robots.txt` = `Allow: /` except `/admin/`, `/api/` (both `public/robots.txt` and `middleware.ts`) | ✅ none — spot-check no real content sits under `/admin` or `/api` |
| 22 internal 4xx URLs | broken | partly carried over in frozen bodies (see **A**, **B**, **C**) | ⚠️ redirects + link scrub |
| 10 pages missing H1 | broken | only `/service/art-design-art-direction/` was affected — already patched in `frozenFixups.ts`; all other frozen bodies have exactly one `<h1>` | ✅ verify the other 9 audited URLs were archives/old pages that no longer exist |
| 10 pages missing meta description | broken | only the `hybrid-casual-gaming-with-oox-limited` post has an empty `metaDescription`; every page/service has one | ⚠️ write 1 description + review fallback pages (**D**) |
| 7 semantically-similar pages (archives) | category/author/date archives overlap | site-next generates **no** archive routes (`generateStaticParams` + `dynamicParams = false`) | ✅ resolved by architecture — just redirect old archive URLs (**B**) |
| 4 duplicate / misplaced `<title>` tags | template bug | Next Metadata API emits exactly one `<title>`, one `description`, one canonical per route — structurally impossible to duplicate | ✅ resolved by architecture |
| Head markup conflicts (multiple title/description on 4 article pages) | Yoast + theme double-emit | same as above — frozen `.head.html` files carry **no** `<title>`/`<meta name=description>`/canonical at all; all regenerated | ✅ resolved by architecture |
| 3 pages need JS rendering for content | client-only render | frozen bodies are full server-rendered HTML snapshots; text is in the initial response | ✅ verify the 3 audited URLs render body copy server-side |
| Duplicate article: `/game-prototyping-studio-deliverables/` vs `…-3/` | both live | **both** still in `posts.json`, both frozen, both in sitemap, both internally linked | ❌ **E** — resolve, plus 2 more pairs the audit missed |
| Broken internal links / 403 content paths | placeholder images, `wp-login` | `wp-login.php` login widget baked into 45 frozen pages, with `localhost:8080` in the `redirect_to` param | ❌ **A** |
| Archive indexation strategy | needed | not needed — no archives exist | ✅ resolved by architecture |

---

## Status (2026-09-03)

- ✅ **A** — `chromePatch.ts` removes `.account-wrap` from every rendered page. Build gate: no `wp-login` / `localhost:8080` string survives.
- ✅ **B** — 11 redirects added to `redirects.json`; 308s verified in a real `next start`. Additionally **scrubbed the dead hrefs from the frozen bodies** (`/author/ooxadmin/`→`/game-development-team/`, `/category/*`→`/blog/`, flat `/app-development/` etc.→`/service/*`, `/contact/`→`/contact-us/`) so crawlers see no redirect hop. The redirect table stays as the safety net for external inbound links.
- ✅ **D** — `hybrid-casual-gaming-with-oox-limited` got a hand-written `metaDescription` (only genuinely-empty case). All 7 pages + 10 services already had real descriptions; no tagline-fallback routes remain.
- ✅ **E** — all 3 near-duplicate pairs resolved: old post removed from `posts.json`, its 3 frozen files deleted, old slug added to the keeper's `oldSlugs` (→ 308 via middleware), stale `/elementor-8398` redirect repointed, duplicate cards stripped from `blog.html` / `blog/page/2`, and every in-body link repointed to the keeper. Verified: old URLs 308 to keepers, keepers 200, sitemap clean (67 routes, was 70).
- ✅ **F** — `ser-game-6.png` (broken on all 10 service pages) repointed to the `-300x300` variant via `frozenFixups.ts`. The 4 `PASTE-…` `<figure>` blocks on `/hybrid-casual-gaming-with-oox-limited/` **removed** (frozen body + `posts.json`) — the captions went with them; re-add real images + captions when assets land. The one 404ing Unsplash hotlink (`photo-1581291518655…`) removed from `game-prototyping-studio-deliverables-3` (frozen + JSON); the two other Unsplash URLs on the site resolve (200) and were left — but self-hosting them is still advisable.
- ✅ **G** — swept `posts.json`: all 111 `localhost:8080` links in `bodyHtml` → site-relative, and old flat/retired paths → their canonical targets. `bodyHtml` is now safe to render if any post is edited in `/admin`.
- ⏳ **C** — blocked: need the auditor's raw URL lists or GSC access.

**Verification:** production build green (67 routes), `tsc` + `eslint` clean, recursive
link crawl of 544 links = **zero broken internal links**. Remaining external dead
links (content-team fixes): `pocketgamer.biz/metrics/app-store-stats/mobile-games/`,
`sga.rs/en/`, `busanit.or.kr/eng/`, `overseas.mofa.go.kr/rs-en/`.

## Work items

### A. Strip the WordPress login widget from frozen chrome — *Critical* — ✅ DONE

**What:** The omero theme's account widget is baked into **45 of ~50 frozen pages**. It renders:
- a full `username` / `password` `<form>` that posts to `/wp-login.php` (404 in site-next)
- `href="/wp-login.php?action=register"` and `href="/wp-login.php?action=lostpassword&redirect_to=http%3A%2F%2Flocalhost%3A8080%2F…"` links

**Why it matters:**
1. **`localhost:8080` is leaked into production HTML** on 45 pages (the `redirect_to` param). Privacy + obviously-broken link.
2. ~2 broken internal links per page → a large slice of the audit's "22 internal 4xx".
3. A dead login form is thin boilerplate repeated site-wide — feeds "semantically similar pages".

**Fix:** add a pass in `chromePatch.ts` (or a `path: null` rule set in `frozenFixups.ts`) that removes the account-widget block. `middleware.ts` already 308s `/wp-login.php` → `/admin/login/`, so any link that survives is at least not a hard 404 — but the form and the `localhost` string must go regardless.

**Falsifiable check:** `grep -rl 'wp-login.php\|localhost' src/data/frozen` at build time should return nothing after the render pass (add it as a test over rendered output, not raw files).

---

### B. Redirect dead legacy URLs — *High* — ✅ DONE

Add to `src/data/redirects.json` (all 308, they resolve through `middleware.ts`):

| From | To | Note |
|---|---|---|
| `/author/ooxadmin/` | `/game-development-team/` | linked from 30 frozen pages (post byline) |
| `/category/game-development/` | `/blog/` | on blog list pages |
| `/category/game-tips/` | `/blog/` | |
| `/category/mobile-gaming/` | `/blog/` | |
| `/app-development/` | `/service/app-development/` | old flat service URL, still linked |
| `/mobile-game-development/` | `/service/mobile-game-development/` | linked 4× |
| `/rapid-prototyping/` | `/service/rapid-prototyping/` | linked 4× |
| `/full-cycle-development/` | `/service/full-cycle-development/` | linked 2× |
| `/co-development-vs-outsourcing-game-development/` | `/co-development-vs-outsourcing/` | old post slug |
| `/hybrid-casual-gaming/` | `/hybrid-casual-gaming-with-oox-limited/` | old post slug |
| `/contact/` | `/contact-us/` | |

Better still, also fix the byline/category hrefs in `frozenFixups.ts` so the crawler
never sees the redirect hop — but the redirect table is the safety net and should
land first.

**Also:** `/about-us`, `/contact-us`, `/services` are linked **without** a trailing
slash in some frozen bodies. `middleware.ts` normalises these to a 308, so they
work, but it is an extra hop on internal links. Low priority — fix opportunistically
in `frozenFixups.ts`.

**Falsifiable check:** crawl the built site (e.g. `npx linkinator http://localhost:3000 --recurse`) → zero 404s, zero redirect chains longer than 1.

---

### C. Confirm the "43 blocked / 22 4xx" set is fully covered — *High* — ⏳ NEEDS INPUT

The audit's prose does not enumerate the 43 + 22 URLs. To close this out, provide **one** of:
- the raw crawl export the auditor used (Screaming Frog / Sitebliss / Ahrefs CSV of the 4xx + blocked rows), or
- Search Console access (Pages report → "Not indexed" breakdown), or
- the appendix/table from the source doc if one exists.

Then, before calling this done:
1. Export the audit's blocked-URL and 4xx-URL lists.
2. Run each through `middleware.ts`'s redirect table + `hasFrozen()`.
3. Anything that still 404s and *should* exist → add a frozen page or a redirect.
4. Anything that 404s and *shouldn't* exist (old archives, tag pages, `?replytocom`, feeds) → confirm it 404s or 410s cleanly and is not in the sitemap. `/feed/`, `/*/feed/`, `/wp-json/`, `?author=N` are the usual suspects — add a catch-all 410 in middleware if the audit flags them.

---

### D. Meta description review — *Medium* — ✅ DONE

- ✅ Wrote `metaDescription` for `hybrid-casual-gaming-with-oox-limited` (the only post with an empty one).
- Verified: all 7 pages + 10 services carry hand-written descriptions already. No indexable route resolves to the `settings.tagline` fallback, so there is no duplicate-description exposure to fix.

**Falsifiable check:** no two indexable routes share an identical `<meta name="description">` (script over the built HTML).

---

### E. Resolve duplicate / near-duplicate articles — *High* — ⏳ NEEDS DECISION

The audit caught one pair. There are **three**:

| Keep (canonical) | Retire | Evidence |
|---|---|---|
| `game-prototyping-studio-deliverables-3` (Jul 3, 2015 words) | `game-prototyping-studio-deliverables` (Jun 29, 2032 words) | audit: "same title and H1"; 4 days apart |
| `rapid-prototyping-game-development-2` (Aug 26, 2121 words) | `rapid-prototyping-game-development` (Jun 11, 1605 words) | same topic, newer + longer rewrite |
| `mobile-game-vertical-slice-prove-before-production-2` (Aug 28, 2610 words) | `mobile-game-vertical-slice-prove-before-production` (Jul 29, 2261 words) | same topic, newer + longer rewrite |

**Done** (owner approved "delete old, 301 to new"). For each retired post:
1. Removed from `posts.json`; deleted `src/data/frozen/<slug>.{html,head.html,meta.json}`.
2. Old slug appended to the keeper's `oldSlugs` → `middleware.ts` 308.
3. `/elementor-8398` redirect repointed from the retired slug to the keeper.
4. Duplicate cards removed from `blog.html` + `blog/page/2`; every in-body link
   to a retired slug repointed to the keeper across all frozen files.

Result: 67 static routes (was 70), sitemap carries only the keepers, retired URLs 308.

**Falsifiable check:** sitemap contains no two URLs with >70% body-text overlap; GSC "Duplicate without user-selected canonical" count trends to zero after recrawl.

**Residual:** other article pages may show a related-post card whose thumbnail/label
is from the retired version (link is correct). Cosmetic; clears on the next re-freeze.

---

### F. Broken images — *High* — ⏳ PARTLY DONE / NEEDS ASSETS

Found via a full recursive link crawl of the built site:

| Broken ref | Where | Status |
|---|---|---|
| `/wp-content/uploads/2026/01/ser-game-6.png` | all 10 `/service/*` pages (decorative, `alt=""`) | ✅ repointed to `ser-game-6-300x300.png` in `frozenFixups.ts` (only variant on disk). Confirm with the client whether a full-size asset should be uploaded instead. |
| `PASTE-HERO-IMAGE-URL-HERE`, `-IDLE-HOOLIGANS-`, `-SNARE-LAIR-`, `-TAXI-GARAGE-` | `/hybrid-casual-gaming-with-oox-limited/` — 4 `<img>` tags | ❌ post published from an unfinished draft. **Needs 4 real images** (1 hero + 3 game screenshots). Then re-freeze or add to `frozenFixups.ts`. Interim: strip the 4 `<figure>` blocks so nothing broken renders. |
| `https://images.unsplash.com/photo-1581291518655-…` | frozen bodies of `game-prototyping-studio-deliverables-3`, `indie-game-funding-2026`, `mobile-game-development-team-roles-workflow-handoffs`, `mobile-game-vertical-slice-prove-before-production-2` + `posts.json` | ❌ hotlinked stock image that 404s. Replace with a self-hosted asset. |
| Dead outbound links: `pocketgamer.biz/metrics/app-store-stats/mobile-games/` (404), `sga.rs/en/` (403), `busanit.or.kr/eng/` (404) | article body copy | content team — repoint or remove. |

**Note on `/wp-content/uploads/`:** uploads are host state and only a working
subset is synced locally, so a local 404 is not proof of a production 404 — but
`ser-game-6.png` (no size suffix) and the Unsplash URL are almost certainly the
audit's "placeholder-image URLs", since the audit ran against the live site.

### G. `posts.json` bodyHtml carries 127 `localhost:8080` links — *Medium* — latent

Every post's `bodyHtml` field in `posts.json` has hardcoded
`http://localhost:8080/...` internal links (127 total across 18 posts). **Not
currently rendered** — `singleContent.ts` only swaps `bodyHtml` into the page when
`post.bodyDirty` is true, and nothing sets that flag today. But the first time
anyone edits a post in `/admin`, that content ships with dev-origin links.

**Fix:** one-time sweep of `posts.json` — replace `http://localhost:8080/` with `/`
(and fix the old flat service paths while there: `/mobile-game-development/` →
`/service/mobile-game-development/`, etc., matching the redirect table in **B**).
Also strip the 4 `PASTE-…` refs in the `hybrid-casual` bodyHtml.

---

## Leading indicators to watch (no re-audit needed)

- **GSC → Pages → "Not indexed"**: `Crawled – currently not indexed`, `Duplicate…`, `Blocked by robots.txt`, `Soft 404` buckets should all fall after deploy + recrawl.
- **GSC → Sitemaps**: submitted vs indexed gap closes.
- **Bing Webmaster / Screaming Frog monthly**: internal 4xx count = 0, redirect chains = 0.
- **`grep` gate in CI**: `wp-login`, `localhost`, `/author/`, `/category/` absent from rendered output.

---

## Round 2 — score follow-ups (commit `36635885`)

Code-only improvements after the client took the content items:

- **Compression on** — `compress: true` in `next.config.ts` (was `false` with no
  reason given, behind an nginx proxy that doesn't gzip proxied responses without
  `gzip_proxied`). Frozen pages are 100–400 KB uncompressed. `DEPLOYMENT.md` §5
  now documents the nginx-side option too.
- **Headers** — added `Strict-Transport-Security` (1y, includeSubDomains),
  `X-DNS-Prefetch-Control: on`, `Permissions-Policy` (camera/mic/geo/topics off).
- **JSON-LD enrichment** (`jsonLd.ts`):
  - Organization: `slogan`, `numberOfEmployees`, `knowsAbout`, `contactPoint`.
  - BlogPosting: `wordCount`, `articleSection`, `keywords`, `image` as ImageObject.
  - Person: `knowsAbout` from `skills`; **placeholder demo data filtered** —
    `@example.com` emails and `facebook.com/themelexus` socials are dropped, not
    asserted.
  - New `Blog` node on `/blog/` listing up to 20 posts.

**Estimated score:** 56 → **82**, ~89 once the open content items + a page-speed
CSS/JS-trim pass land.

### Data-quality issues surfaced (client / content)

- `team.json`: all 13 bios are one paragraph with the name swapped; every
  `email` is `info@example.com`; every `socials` block is the omero theme's demo
  account (`themelexus`). Schema now hides these, but the team pages still render
  thin/near-duplicate — individualise or `noindex`.
- `team.json` has a member with slug `debela` — looks like a test row; confirm and remove.

## Not in the audit but worth doing during the rebuild

- JSON-LD is already broad in `jsonLd.ts` (`Organization`, `WebSite`, `BlogPosting`, `Service`, `Person`, `BreadcrumbList`) and `/og-default.png` exists — no action, just validate with Google's Rich Results Test after deploy.
- `/team/*` pages are thin (one bio each) and in the sitemap at priority 0.5 — decide index vs `noindex,follow`. They add little search value and dilute crawl budget, but do carry E-E-A-T author signal. Lean: keep indexed only if each bio is >150 words of unique copy.
