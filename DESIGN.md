# Design

<!-- impeccable:design-schema 1 -->

## Visual world — "The Auction Lot"

A single-lot fine-art sale catalogue, cast as a web page for the scarce vanity
asset `sajib.bd`. The whole surface is printed on warm rag paper, set in an
engraved Didone display with old-style catalogue figures, divided by hairline
rules rather than shadows or cards, and carries exactly one saturated colour —
oxblood — reserved for the single action (sending an offer). Provenance and safe
transfer are conveyed by the object itself (a guilloché certificate of title, a
rotated rarity stamp, a lot record), never by invented testimonials or pricing.

The world carries the page's parts and never absorbs them: navigation reads as
links (a tablist), the lot record reads as a heading with real actions, sections
keep a steady vertical rhythm, and the offer form is plainly a form.

## Palette

| Token | Value | Use |
|---|---|---|
| `--paper` | `#f4efe3` | warm rag ground |
| `--paper-deck` | `#ece3d1` | certificate field / deckle |
| `--paper-glow` | `#faf6ee` | raised plate (offer card) |
| `--ink` | `#17130d` | primary text |
| `--ink-soft` | `#4c4536` | secondary text |
| `--ink-faint` | `#6f6757` | small-caps marginalia (AA ≥4.5:1 on paper) |
| `--rule` / `--rule-strong` | ink @ 14% / 30% | hairlines, dividers |
| `--oxblood` / `--oxblood-deep` | `#7a1f24` / `#5f1519` | the one reserved action colour |
| `--gilt` | `#a8863d` | a whisper of metal (name rule, lot numerals) — never a second accent |

The only non-catalogue colour is the WhatsApp brand green `#1faa4f`, permitted on
the mobile floating action because it is a recognition cue for the platform.

## Typography

- **Display:** Playfair Display 500–700 — the name, lot titles, section heads,
  estimate values. `text-wrap: balance`; display capped at `clamp(... 7rem)`;
  tracking floor `-0.02em`.
- **Text / UI:** EB Garamond 400–600 — body, form, nav, labels. Old-style figures
  via `font-feature-settings: "onum"`; the estimate/contact values switch to
  tabular lining figures (`"lnum","tnum"`) so numbers align like a price column.
- Functional labels are EB Garamond small caps with `.24em` tracking. There is no
  decorative kicker/eyebrow above a heading; the lot meta-bar is a record header,
  not an eyebrow.

## Structure & components

- **Masthead:** `grid-template-columns: 1fr auto 1fr` — monogram left, a centred
  pill **ARIA tablist**, phone CTA right. Sticky, hairline under, light blur.
- **Lot record (hero):** left column = meta-bar → `H1` name → gilt rule →
  italic description → **estimate strip** (CSS Grid, from `contact_info`) →
  actions; right column = framed **certificate** with a 26-ellipse SVG guilloché
  and the rotated rarity stamp (from `brand.badge`).
- **Tabs:** panels are driven by `nav_links`; `#benefits` → particulars,
  `#offer` → enquire. Keyboard operable (arrows/Home/End), `aria-selected`/
  `tabindex` roving, panels toggled with `hidden`.
- **Benefits:** CSS Grid `auto-fit minmax(260px,1fr)`, 1px hairline gutters,
  catalogue index numerals, circular line-icon badges.
- **Enquire:** CSS Grid `.82fr/1.18fr` aside + offer card; form fields keep ids
  `name/email/amount/message`; two-up first row, full-width amount + textarea.
- **Colophon** (footer) and a mobile-only floating offer stack.

## Depth, motion, states

Elevation is a hairline border plus a single soft plate shadow (`--shadow-plate`)
with a top inset highlight — declared once, no border+halo ghost cards. Motion:
one authored entrance (panel cross-fade + rise, `cubic-bezier(.16,1,.3,1)`), a
"live" status dot pulse, and hover lifts on plates/buttons. `prefers-reduced-motion`
freezes all animation and reveals content immediately. Focus rings are themed
oxblood; selection, caret, and scrollbar are themed from the palette.

## Responsive

Three breakpoints: `≤900px` (stack lot + certificate, certificate lifts above
text, masthead wraps tabs to their own row, floating CTAs appear), `≤560px`
(single-column form, icon-only call CTA at 44px, hide seal + section index,
stretch hero buttons). Verified at 1440 and 390 with no horizontal overflow and
≥44px touch targets.

## Data binding (unchanged)

`index.php` renders entirely by looping `app/data.php`; the redesign adds no
hard-coded copy the admin cannot edit, keeps `logo_html` raw and escapes the rest,
preserves the WhatsApp/tel wiring and the form field ids `main.js` depends on.
`app/data.php` + `admin.php` are unchanged.
