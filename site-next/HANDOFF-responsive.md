# Handoff — responsive breakage in the 768–1200px band

Working notes for whoever picks this up next (including a fresh agent session).
Everything below was measured on the local dev server, not guessed.

## Read this first

The public site is **frozen WordPress/Elementor HTML** (see `PLAN.md`). You cannot
fix layout by editing `src/data/frozen/**` or `public/_css/**` — `npm run freeze`
overwrites both. Every deliberate change to the frozen design goes in one place:

    src/lib/frozenOverrides.ts

It is rendered by `FrozenView` as the **last `<style>` on the page**, so it wins
ties without `!important` and survives a re-freeze. Each block in it is commented
with what it fixes and why. Read it before adding anything.

Two sibling mechanisms, don't confuse them:
- `src/lib/frozenFixups.ts` — markup patches that must stay **visually neutral**.
- `src/lib/pageTrims.ts` — whole blocks removed at render, at the client's request.

## The client's complaint

> "when scaled to these weird sizes, the design really breaks"

Reported again during this session as: **"from 768 to 1200px site totally off"**.
This is iPad-portrait territory and the desktop/tablet gap above it.

## Why this band breaks at all

The theme switches **visibility** and **spacing** at *different* widths:

| Breakpoint | Where it comes from |
|---|---|
| 767px | most of the mobile spacing rules (`@media (max-width:767px)`) |
| 880px | where mobile-only widgets stop being displayed |
| 1024px | Elementor's tablet container width |
| 1200px / 1366px | theme laptop breakpoints |

Media queries present in the theme CSS: 767, 880, 1024, 1200, 1366. Anything that
assumes one number while a sibling rule assumes another leaves a band where an
element is on screen without the CSS that positions it. That is the root pattern
behind everything below.

## Fixed (commit `1a92f198`, plus `frozenOverrides.ts` blocks)

### 1. Reviews carousel collapse — the severe one
The slide is a flex row:
- image column: `flex: 1 0 auto`, fixed `width: 486px` — **cannot shrink**
- gap: `127px`
- quote column: `flex: 0 1 auto` — absorbs the entire shortfall

Once the row is narrower than `486 + 127`, the quote column gets whatever is
left. Measured quote-column width:

| viewport | quote column | slide height |
|---|---|---|
| 834px | **0px** | 8,589px |
| 1024px | 171px | 1,109px |
| 1200px | 347px | 549px |
| 1280px | 427px | 486px |

A zero-width paragraph wraps one character per line, which is why the whole home
page measured **16,236px at 834px** vs 9,148px at 1024px.

Fix: let the row wrap and give the text a `min-width: 260px` floor.
**Home page at 834px is now 8,617px.** Above 1200px the row fits, so wrap never
triggers and the desktop design is untouched (verified: 1280px still `nowrap`,
gap still 127px).

### 2. Mobile-only widgets lost their inset (768–880px)
Widgets marked `elementor-hidden-desktop … elementor-hidden-tablet` keep
displaying up to 880px, but their padding lives in a `max-width:767px` query. In
between they render with **no inset at all**. On every service page the intro
paragraph sat flush at `left: 0` while the heading above it was properly inset.

Measured (`/service/rapid-prototyping/`, intro paragraph left edge):
`20px @390, 20px @600, **0px @768/834/880**, 120px @900+`.
After the fix: `110px @768, 143px @834, 166px @880`, phones and desktop untouched.

### 3. Centred lists with detached markers (≤1200px)
`list-style-position: outside` on a centred full-width block put bullets at
`x=63` with their text starting near `x=380`. Now `inside` in that band.

## Still open — this is where to start

1. **"about us" button reported cut off.** The user reported this visually; I
   could not reproduce it by measurement. It measures `360..433` (72×40) at
   1100px, fully inside the viewport, and is `0×0` (a hidden variant) at 834 and
   1024. **Needs the screenshot** — likely clipped vertically by the section
   boundary rather than horizontally, which my width-based checks would miss.
2. **Blank band under the hero at ~1100px.** `img.wp-image-7064` is 392×487 at
   y363–850, loaded (`natural 700×870`, `complete: true`), `opacity: 1`, and
   topmost at its own centre point — yet it renders as a faint ghost, leaving
   ~350px that reads as empty. Suspect a scroll-linked GSAP opacity/transform
   that never completes at this width. Not yet explained.
3. **Long line lengths in the stacked band.** Below ~1280 these sections go
   full-width and centred: the "who we are" copy runs ~1070px wide (roughly 150
   characters). Not broken, but poor. Capping the measure needs a design call —
   at ≥1280 the same column is 588px.
4. **`dotlottie-player` overlapping text on service pages.** The character is a
   Lottie player, *not* an `<img>` — every image-based check misses it. Its box
   (`369..819, y169–749` at 834) runs over the intro paragraph (`y647–710`).
   Worth checking whether that is intended overlap or a break.

## How to reproduce / measure

Dev server on `:3000`, then the scripts in `scratchpad/diag/` (gitignored, throwaway):

    node scratchpad/diag/tablet2.mjs      # sweep 768/810/834/1024/1112/1194, filtered
    node scratchpad/diag/heights2.mjs     # per-section height diff between two widths
    node scratchpad/diag/revrow.mjs       # the reviews row + column widths
    node scratchpad/diag/inset.mjs        # service intro left edge across widths
    node scratchpad/diag/look.mjs / home  # full-page screenshots at 834/1024/1100/1200

**Two traps that cost time here — don't repeat them:**

- **Let entrance animations settle before measuring.** Scrolling to the bottom
  and back leaves elements mid-flight. A "+55px past the right edge" accordion
  and several text overlaps were pure animation artefacts: after settling, the
  accordion is `transform: none` and sits at `15..819`, fully inside. Scroll in
  ~400px steps, then wait ~2s at the top.
- **`<img>` is not the only thing that paints.** The service-page character is a
  `dotlottie-player`; carousels and marquees are `swiper-slide` /
  `elementor-scrolling-item` and are *legitimately* wider than the viewport.
  Filter those out or every sweep drowns in false positives.

Also note `--content-width: 1440px` appears on many containers. It looks like a
smoking gun and **is not** — `max-width: 100%` clamps it. Verified at every width.

## Client-facing state

The report artifact answering the client's full feedback document:
https://claude.ai/code/artifact/1f93f908-c30c-4f2e-ac21-2602cd2f0a95

Still open there: moving background images, the history-timeline grey space
(two options offered, needs their pick), the services scroll animation (client
owes an example), the stray line (not reproducible — needs a wider screenshot),
and the full-width/scaling question, which is **parked deliberately** — the site
stays at the theme's 1410px container until that is scoped separately.
