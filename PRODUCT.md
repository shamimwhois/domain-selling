# Product

<!-- impeccable:product-schema 1 -->

## Platform

web

## Stack

PHP single-file template (`index.php`) that renders all content by looping over the array returned by `app/data.php`; content is edited and saved through `admin.php`. Styling uses the Tailwind CDN plus a small custom `assets/css/style.css`; conversion behavior lives in `assets/js/main.js`. This stack and the data-driven architecture were confirmed by the user for this redesign (keep `app/data.php` + `admin.php`; keep Tailwind CDN).

## Users

Prospective buyers evaluating whether to purchase the premium domain **sajib.bd** — high-profile individuals, tech ventures, or people doing personal branding in the Bangladesh market. They arrive on a single page, decide the name is worth owning, and make contact. Secondary audience: the domain owner, who maintains the page content through `admin.php`.

## Product Purpose

A one-page sales surface for the single asset `sajib.bd`. Success = a qualified visitor sends an offer through WhatsApp (or calls). The page must establish the name's value, reassure about a safe transfer, and make offering frictionless.

## Positioning

Rare, short, memorable brand keyword in the `.bd` namespace, sold directly with safe, official transfer (BTCL procedures or global escrow) and a fast human response. It is a scarce-asset listing, not a SaaS or agency site.

## Operating Context

Static marketing page served from `index.php`; no auth, no backend beyond the flat data file. Conversion actions are external: `wa.me` deep link and `tel:` link to the owner's +966 number. All editable copy originates from `app/data.php` and is managed through `admin.php`.

## Capabilities and Constraints

- **Data-driven template (binding):** `index.php` renders via loops over `app/data.php`; it must not hard-code content that the admin can no longer edit. Repeater sections: `nav_links`, `hero_actions`, `contact_info`, `benefits`, `form.fields`, `footer.badges`.
- **Keep current copy (binding):** reuse the existing wording exactly; the redesign may add only neutral UI labels (navigation, tab names) — no invented price, FAQ, testimonials, or other claims.
- **Contact fields (binding):** `whatsapp_number` (966535453006), `phone_raw`/`phone_display` (+966 53 545 3006) drive the CTAs; `main.js` builds the WhatsApp offer message from the form field ids `name`, `email`, `amount`, `message`. Those ids must stay intact.
- **Requested layout patterns:** fully responsive; a modern tabbed navigation pattern; CSS Grid for the benefits and contact sections.
- Undecided: none blocking; visual world is decided in new-work, not here.

## Brand Commitments

- Name/identity: the domain **sajib** + extension **.bd**; existing logo mark `S.BD`. Badge "Rare Digital Asset".
- Voice: premium, confident, concise ("Elite Identity", "Market Growth", "Safe Transfer").
- `logo_html` is rendered as raw markup in the navbar (intentional in the data model); other fields are HTML-escaped.

## Evidence on Hand

Real content only, all in `app/data.php`: hero heading/description, three benefit cards (Elite Identity, Market Growth, Safe Transfer), two contact-info stats (Direct Contact number; "Response Time: Within 1 Hour"), the offer form, and footer badges ("Premium Asset", "Transfer Protected"). No testimonials, case studies, verified buyers, pricing history, or press exist — future work must not fabricate them.

## Product Principles

1. Make the scarcity and identity value of the name legible in the first viewport.
2. Every surface decision should reduce friction toward one action: send the offer via WhatsApp.
3. Signal trust and a safe transfer without inventing proof that does not exist.
4. Preserve the owner's ability to edit all copy through the data file and admin.
5. Craft and restraint over hype — a premium asset deserves a premium, unhurried presentation.

## Accessibility & Inclusion

Standard web accessibility: keyboard-operable tabs with correct ARIA roles/states, visible focus, sufficient text contrast on the dark palette, and reduced-motion support for background animation. No specific mandated standard was stated by the user.
