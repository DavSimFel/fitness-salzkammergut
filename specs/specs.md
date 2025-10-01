# WP Implementation Guide (MVP)

**Principles:** KISS • YAGNI • DRY • Gutenberg-first • Editors-over-developers • Tailwind for styling only (no JS unless needed)

---

## Table of Contents

1. [Sitemap & URL rules](#sitemap--url-rules)
2. [Custom Post Types (CPTs)](#custom-post-types-cpts)
3. [Taxonomies](#taxonomies)
4. [Content editability rules](#content-editability-rules)
5. [Minimal code scaffolding](#minimal-code-scaffolding)
6. [Next step: page-by-page build order](#next-step-page-by-page-build-order)

---

## Sitemap & URL rules

> Start from a **blank theme** (only `theme.json`). No overview pages you didn’t ask for.

**Site map (MVP — updated, no `/ziele/`, no `/tarif/{slug}`)**

| Section             | URL                                                                                                                                            | Type       | Source                | Template/Block                      | Notes                                                                   |
| ------------------- | ---------------------------------------------------------------------------------------------------------------------------------------------- | ---------- | --------------------- | ----------------------------------- | ----------------------------------------------------------------------- |
| Home                | `/`                                                                                                                                            | Page       | Page                  | `home.html` (block template)        | Full-bleed hero, review-row, Ziele mosaic, Kurs & Tarif teasers, News   |
| Studios (archive)   | `/studios/`                                                                                                                                    | Archive    | `studio` CPT          | `archive-studio.html`               | Menu label **„Standorte“** points here; no `/standorte/` page           |
| Studio detail       | `/studio/{slug}/`                                                                                                                              | Single     | `studio` CPT          | `single-studio.html`                | Hero + Ausstattung navigator + Team + prefiltered timetable             |
| Training (Services) | `/training/`                                                                                                                                   | Page       | Page                  | `page.html` + patterns              | Copy-first                                                              |
| Ziel pages (5×)     | `/ziel-abnehmen-wohlfuehlen/`, `/ziel-fitness-gesundheit/`, `/ziel-sport-performance/`, `/ziel-kraft-muskelaufbau/`, `/ziel-reha-praevention/` | Pages      | **Pages**             | `page-ziel.html`                    | Each page acts as its **own archive** filtered by its `ziel_topic` term |
| Kursprogramm        | `/kurse/`                                                                                                                                      | Archive    | `kurs` CPT            | `archive-kurs.html`                 | Timetable (week columns), sticky filters; modal details                 |
| Kurs detail         | `/kurs/{slug}/`                                                                                                                                | Single     | `kurs` CPT            | `single-kurs.html`                  | Only for selected formats (Yoga, Rückenfit, Kampfsport)                 |
| Tarife              | `/tarife/`                                                                                                                                     | Page       | Page (+ `tarif` data) | `page.html` + mosaic/table patterns | **No tariff singles**; CPT is data-only                                 |
| News (blog)         | `/news/`                                                                                                                                       | Posts page | `post`                | Posts index                         | Standard cards, focal point                                             |
| Post detail         | `/news/{slug}/`                                                                                                                                | Single     | `post`                | `single.html`                       | Article schema                                                          |
| Über uns            | `/ueber-uns/`                                                                                                                                  | Page       | Page                  | `page.html`                         | Team + mission                                                          |
| Kontakt             | `/kontakt/`                                                                                                                                    | Page       | Page                  | `page.html`                         | Form + maps                                                             |
| Firmenfitness       | `/firmenfitness/`                                                                                                                              | Page       | Page                  | `page.html`                         | As planned                                                              |
| Impressum           | `/impressum/`                                                                                                                                  | Page       | Page                  | `page.html`                         | Legal                                                                   |
| Datenschutz         | `/datenschutz/`                                                                                                                                | Page       | Page                  | `page.html`                         | Legal                                                                   |
| AGB                 | `/agb/`                                                                                                                                        | Page       | Page                  | `page.html`                         | Optional                                                                |
| Cookies             | `/cookies/`                                                                                                                                    | Page       | Page                  | `page.html`                         | Link from consent tool                                                  |

**Primary navigation**

- **Home** → `/`
- **Standorte** → `/studios/` (logos link to VIVO/Exciting Fit pages)
- **Training** → `/training/`
- **Ziele** → dropdown to the **5 Ziel pages** (no `/ziele/`)
- **Kurse** → `/kurse/`
- **Tarife** → `/tarife/`
- **Über uns** → `/ueber-uns/`

**Redirects**

- No `/standorte/` page → map legacy links to `/studios/` (via server/Redirection plugin)
- `www.excitingfit.at` → `/studio/exciting-fit/`; `vivo.fit` → `/studio/vivo/`
- No human HTML sitemap page; rely on WP XML sitemap

------------------- | ----------------- | -------------- | --------------------- | ----------------------------------- | --------------------------------------------------------------------------- |
| Home | `/` | Page | `home` | `home.html` (block template) | Full-bleed hero, review-row, Ziele mosaic, Kurs & Tarif teasers, News cards |
| Studios (archive) | `/studios/` | Archive | `studio` CPT | `archive-studio.html` | Grid of studios with rating badges |
| Studio detail | `/studio/{slug}/` | Single | `studio` CPT | `single-studio.html` | Hero + Ausstattung navigator + Team + prefiltered schedule embed |
| Training (Services) | `/training/` | Page | Page | `page.html` + patterns | Copy-first; CTA to Probetraining |
| Ziele (overview) | `/ziele/` | Archive | `ziel` CPT | `archive-ziel.html` | 5 main Ziel pages highlighted |
| Ziel detail | `/ziel/{slug}/` | Single | `ziel` CPT | `single-ziel.html` | Left copy / right testimonial; filtered posts list + pagination |
| Kursprogramm | `/kurse/` | Archive | `kurs` CPT | `archive-kurs.html` | Timetable (week columns), sticky filters; modal details |
| Kurs detail | `/kurs/{slug}/` | Single | `kurs` CPT | `single-kurs.html` | Only for selected formats (Yoga, Rückenfit, Kampfsport) |
| Tarife (landing) | `/tarife/` | Page | Page (+ `tarif` data) | `page.html` + mosaic/table patterns | Combines tariff mosaic + table for Kartenprodukte |
| Tarif detail | `/tarif/{slug}/` | Single | `tarif` CPT | `single-tarif.html` | Optional; SEO-visible |
| News (blog) | `/news/` | Posts page | `post` | `index.html` or `home` posts | Standard cards, focal point |
| Post detail | `/news/{slug}/` | Single | `post` | `single.html` | Article schema |
| Probetraining | `/probetraining/` | Page | Page (+ Connect API) | `page.html` + booking block | Phase 1: embed; Phase 2: custom flow |
| Über uns | `/ueber-uns/` | Page | Page | `page.html` | Team + mission |
| Standort-Übersicht | `/standorte/` | Redirect/alias | Page → `/studios/` | Pattern link | Extra menu item per feedback |
| Kontakt | `/kontakt/` | Page | Page | `page.html` | Form + maps |
| Firmenfitness | `/firmenfitness/` | Page | Page | `page.html` | Logos section on light background |
| Impressum | `/impressum/` | Page | Page | `page.html` | Legal |
| Datenschutz | `/datenschutz/` | Page | Page | `page.html` | Legal |
| AGB | `/agb/` | Page | Page | `page.html` | Optional if needed |
| Cookies | `/cookies/` | Page | Page | `page.html` | Link from consent tool |
| Sitemap | `/sitemap/` | Page | Page | `page.html` | Human-readable list of links |

**Primary navigation**

- **Home** → `/`
- **Studios (Standorte)** → `/studios/` (logos link to VIVO/Exciting Fit studio pages)
- **Training** → `/training/`
- **Ziele** → `/ziele/`
- **Kurse** → `/kurse/`
- **Tarife** → `/tarife/`
- **Über uns** → `/ueber-uns/`
- **Probetraining** → `/probetraining/`

Notes: Keep page slugs lowercase, use `ue/ae/oe`. Header/Footer as Synced Patterns.

---

## Custom Post Types (CPTs)

> Keep only what’s needed. We’re starting from a blank theme; code can be pasted in WP’s Theme Editor.

### Usage Matrix (where used / not used)

| CPT           | Purpose                                                       | Used on                                         | Not used for                 | Key taxonomies                                                                            |
| ------------- | ------------------------------------------------------------- | ----------------------------------------------- | ---------------------------- | ----------------------------------------------------------------------------------------- |
| `studio`      | Studio pages (VIVO, Exciting Fit)                             | `/studios/`, `/studio/{slug}/`, teasers on Home | Blog articles, generic pages | `standort`, `studio_brand`, `ausstattung`                                                 |
| `kurs`        | Formats that deserve content pages (most sessions modal-only) | `/kurse/`, `/kurs/{slug}/` (select few)         | News, static pages           | `ziel_topic`, `kurs_kategorie`, `level`, `wochentag`, `tageszeit`, `studio_brand`, `raum` |
| `tarif`       | Manage tariff tiles/table content                             | **Rendered only on `/tarife/`** (no singles)    | Blog, singles                | `tarif_typ`, (`ziel_topic` optional)                                                      |
| `team` (opt.) | Trainers/Staff                                                | Studio pages, About                             | Blog, Tarife                 | `studio_brand`, `rolle`                                                                   |
| `testimonial` | Curated social proof (photos, text)                           | Ziel pages (right column), Home/Studio cards    | Replacing Google review text | `ziel_topic`, `studio_brand`                                                              |

### CPT notes

- `tarif`: `has_archive:false`, `publicly_queryable:false`, `exclude_from_search:true` → **no front-end singles**.
- **No `ziel` CPT.** The 5 Ziel are **Pages** filtered by `ziel_topic`.

------------- | ------------------------------------------ | ------------------------------------------------------------ | ---------------------------- | ----------------------------------------------------------------------------------------- |
| `studio` | Studio pages (VIVO, Exciting Fit) | `/studios/`, `/studio/{slug}/`, teasers on Home | Blog articles, generic pages | `standort`, `studio_brand`, `ausstattung` |
| `kurs` | Courses & timetable items | `/kurse/`, `/kurs/{slug}/`, studio pages (prefiltered embed) | News, static pages | `ziel_topic`, `kurs_kategorie`, `level`, `wochentag`, `tageszeit`, `studio_brand`, `raum` |
| `tarif` | Memberships & cards | Data source for `/tarife/`, optional `/tarif/{slug}/` | Blog | `tarif_typ`, (`ziel_topic` optional) |
| `ziel` | Ziel landing pages | `/ziele/`, `/ziel/{slug}/` | News | `ziel_topic` (self-tag for hierarchy) |
| `team` (opt.) | Trainers/Staff | Studio pages, About | Blog, Tarife | `studio_brand`, `rolle` |
| `testimonial` | Editor-curated testimonials (photos, text) | Ziel pages (right column), Home/Studio cards | Replacing Google review text | `ziel_topic`, `studio_brand` |

### CPT registrations (update)

- Keep block templates per CPT; use Synced Patterns for cards/rows.
- All CPTs: `show_in_rest: true`, supports: `title`, `editor`, `thumbnail`, `excerpt`, `revisions` (except where noted).

_(Default **`post`** remains for News/Insights; will be heavily used for Ziel content and listed on 5 Ziel pages via taxonomy filters.)_

---

## Taxonomies

> Favor taxonomies over custom fields where filters are needed. All taxonomies visible in the editor.

**Global**

- `studio_brand` (non-hierarchisch): _VIVO, Exciting Fit_ — mark content studiozugehörig (assignable on **posts & pages**)
- `ziel_topic` (hierarchisch): _Abnehmen, Rücken, Mobility, Ausdauer, …_ — filtert News/Kurse; visible on **posts & pages**

**Locations & facilities**

- `standort` (hierarchisch): _Bad Goisern, Bad Ischl_ — on `studio`, `kurs`
- `raum` (hierarchisch): _Kursraum, Kampfsportraum, Wellness, Outdoor/Katrin, Untersee …_ — on `kurs`, `studio`
- `ausstattung` (hierarchisch): _Trainingsfläche, Wellnessbereich, Kursraum, …_ — on `studio`

**Courses**

- `kurs_kategorie` (hierarchisch): _Yoga, Rückenfit, Kampfsport, …_
- `level` (non-hierarchisch): _Anfänger, Mittel, Fortgeschrittene_ _(or MVP only „Fortgeschrittene“ badge)_
- `wochentag` (hierarchisch): _Mo–So_
- `tageszeit` (hierarchisch): _Morgen, Mittag, Abend_

**Tarife**

- `tarif_typ` (hierarchisch): _Basic, Flex, Young 16, Young 21, Baby, Kartenprodukte_

**Team** (optional)

- `rolle` (hierarchisch): _Trainer, Physiocoach, Empfang …_

**No free text — how we enforce:**

- Editors **assign** terms but **cannot create** new ones. Only Admin can add/edit/delete terms. Implement via taxonomy `capabilities` so `assign_terms: edit_posts`, `manage_terms: manage_categories` (Editors lack this by default). Hide “Add New” UI for non-admin via small admin CSS/JS if needed.

---

## Content editability rules

- Every page and archive uses **block templates** with **Template Parts** (Header, Footer) and **Synced Patterns** for repeatables (Cards, Review-Row, CTA, Tariftabelle, Studio-Shortcut).
- Use **block locking** for structure, leave text/media fully editable.
- Images: use **Featured Image** + **Focal Point**; videos as Cover block or native Video block (no autoplay on mobile; short hover-loops only on desktop).
- Reviews in hero: use **Testimonial** CPT (curated, photo-heavy) — avoids Google policy issues while keeping social proof consistent.

---

## Minimal code scaffolding

> You don’t need PHP locally. Paste code via **Appearance → Editor (Theme)**.

### Option A (simplest): single `functions.php`

Create `functions.php` and paste this — includes CPTs, taxonomies, Tailwind/editor styles, and Ziel PPC query vars.

```php
<?php
// 1) Editor styles (Tailwind later) — works with only theme.json too
add_action('after_setup_theme', function(){
  add_theme_support('editor-styles');
  if (file_exists(get_stylesheet_directory().'/build/tw.css')) {
    add_editor_style('build/tw.css');
  }
});

add_action('wp_enqueue_scripts', function(){
  $tw = get_stylesheet_directory().'/build/tw.css';
  if (file_exists($tw)) {
    wp_enqueue_style('tw', get_stylesheet_directory_uri().'/build/tw.css', [], filemtime($tw));
  }
});

// 2) CPTs (no ziel CPT)
add_action('init', function(){
  $supports = ['title','editor','thumbnail','excerpt','revisions'];

  register_post_type('studio', [
    'label'=>'Studios','public'=>true,'has_archive'=>true,
    'rewrite'=>['slug'=>'studios'],'show_in_rest'=>true,'supports'=>$supports,
  ]);

  register_post_type('kurs', [
    'label'=>'Kurse','public'=>true,'has_archive'=>true,
    'rewrite'=>['slug'=>'kurse'],'show_in_rest'=>true,'supports'=>$supports,
  ]);

  register_post_type('tarif', [
    'label'=>'Tarife','public'=>true,'has_archive'=>false,
    'publicly_queryable'=>false,'exclude_from_search'=>true,
    'show_in_rest'=>true,'supports'=>$supports,
  ]);

  register_post_type('team', [
    'label'=>'Team','public'=>true,'has_archive'=>false,
    'show_in_rest'=>true,'supports'=>['title','editor','thumbnail'],
  ]);

  register_post_type('testimonial', [
    'label'=>'Testimonials','public'=>true,'has_archive'=>false,
    'show_in_rest'=>true,'supports'=>['title','editor','thumbnail','excerpt','revisions'],
  ]);
});

// 3) Taxonomies (assign-only for editors)
add_action('init', function(){
  $cap = [
    'manage_terms'=>'manage_categories','edit_terms'=>'manage_categories','delete_terms'=>'manage_categories','assign_terms'=>'edit_posts'
  ];

  register_taxonomy('studio_brand', ['studio','kurs','tarif','post','page','testimonial','team'], [
    'label'=>'Studio','public'=>true,'show_in_rest'=>true,'hierarchical'=>false,'rewrite'=>['slug'=>'studio'],'capabilities'=>$cap,
  ]);

  register_taxonomy('ziel_topic', ['post','page','kurs','tarif','testimonial'], [
    'label'=>'Ziel','public'=>true,'show_in_rest'=>true,'hierarchical'=>true,'rewrite'=>['slug'=>'ziel-topic'],'capabilities'=>$cap,
  ]);

  register_taxonomy('standort', ['studio','kurs'], [
    'label'=>'Standort','public'=>true,'show_in_rest'=>true,'hierarchical'=>true,'rewrite'=>['slug'=>'standort'],'capabilities'=>$cap,
  ]);

  register_taxonomy('raum', ['kurs','studio'], [
    'label'=>'Raum','public'=>true,'show_in_rest'=>true,'hierarchical'=>true,'rewrite'=>['slug'=>'raum'],'capabilities'=>$cap,
  ]);

  register_taxonomy('ausstattung', ['studio'], [
    'label'=>'Ausstattung','public'=>true,'show_in_rest'=>true,'hierarchical'=>true,'rewrite'=>['slug'=>'ausstattung'],'capabilities'=>$cap,
  ]);

  register_taxonomy('kurs_kategorie', ['kurs'], [
    'label'=>'Kurs-Kategorie','public'=>true,'show_in_rest'=>true,'hierarchical'=>true,'rewrite'=>['slug'=>'kurs-kategorie'],'capabilities'=>$cap,
  ]);

  register_taxonomy('level', ['kurs'], [
    'label'=>'Level','public'=>true,'show_in_rest'=>true,'hierarchical'=>false,'rewrite'=>['slug'=>'level'],'capabilities'=>$cap,
  ]);

  register_taxonomy('wochentag', ['kurs'], [
    'label'=>'Wochentag','public'=>true,'show_in_rest'=>true,'hierarchical'=>true,'rewrite'=>['slug'=>'wochentag'],'capabilities'=>$cap,
  ]);

  register_taxonomy('tageszeit', ['kurs'], [
    'label'=>'Tageszeit','public'=>true,'show_in_rest'=>true,'hierarchical'=>true,'rewrite'=>['slug'=>'tageszeit'],'capabilities'=>$cap,
  ]);
});

// 4) PPC filters for Ziel pages (query vars)
add_filter('query_vars', function($vars){
  foreach(['ziel','studio','level','testimonials','pinned'] as $v) $vars[]=$v; return $vars;
});
function site_get_ziel_filters(){
  $q = (object) [
    'ziel'=>get_query_var('ziel'),'studio'=>get_query_var('studio'),'level'=>get_query_var('level'),
    'testimonials'=>get_query_var('testimonials'),'pinned'=>intval(get_query_var('pinned')) ?: 0,
  ];
  foreach(['ziel','studio','level'] as $k){ if($q->$k){ $q->$k = array_filter(array_map('sanitize_title', explode(',', $q->$k))); } }
  return $q;
}
```

> **Option B** (cleaner): split into `inc/` files later. For now, one file keeps it simple.

---

## Google Reviews — display strategy (policy-safe)

- **Per Studio rating badge**: Fetch _rating_ + _user_ratings_total_ server-side at request time (or short transient cache ~10–15 min) using Places API _Place Details_. Display with Google attribution. Avoid storing review text.
- **Hero review row**: Use **Testimonial** CPT (curated snippets with photos). This gives full editorial control and avoids API compliance pitfalls.
- **Schema.org**: Output `LocalBusiness` with `aggregateRating` using live values.

Implementation notes: Store Place IDs on each `studio` (or `standort` term). Provide a small WP REST endpoint `GET /wp-json/site/v1/studios/{id}/rating` used by a dynamic block.

---

## Ziele content model (5 Pages; no overview page)

- Create **5 Pages** with the slugs listed in the sitemap. Each page is assigned **one** `ziel_topic` term.
- **Template idea (`page-ziel.html`)**:

  1. Intro two-column (copy left, testimonial right — `testimonial` CPT, auto-rotate ~10s)
  2. Featured posts (2–6) — editor selects specific posts
  3. Query Loop filtered by the page’s `ziel_topic` with pagination (9/Seite)
  4. Cross-navigation „Weitere Ziele“ pattern

- **Editorial rule:** Each post must select **exactly one** `ziel_topic`.

### PPC query filters (pre-filter Ziel pages)

Params: `ziel` (term slug or list), `studio` (`studio_brand`), `level`, `testimonials` (1/0), `pinned` (2/4/6).
Examples:

- `/ziel-abnehmen-wohlfuehlen/?studio=excitingfit&testimonials=1`
- `/ziel-fitness-gesundheit/?level=fortgeschrittene&pinned=4`

---

## Kurs pages vs. Timetable ("not every Kurs has a page")

**Goal:** Some class formats get full pages; most show only as timetable cards/modals.

**Approach**

- Treat `kurs` CPT as **format** content (only create for important formats like _Yoga, Rückenfit, Kampfsport_).
- **Timetable data** comes from **Magicline Connect API** (live sessions). Each session card:

  - builds its content from API fields (name, start–end, studio, room, badges),
  - checks if a matching `kurs` CPT exists (by normalized title or mapped external ID); if yes → use CPT excerpt/image in the modal and link to the full page; otherwise modal-only.

- **SEO**: Modals render server-side content (indexable via dedicated route `/kursinfo/{sessionId}`) but no standalone thin pages. Only selected formats (`kurs` CPTs) have real singles.
- **Mapping**: Start with **name match**; later add an admin mapping UI for _classId ⇄ kurs_ (optional). Avoid custom fields by using a small taxonomy `kurs_mapping` with slugs equal to external IDs (admin-only to manage terms).

---

## TailwindCSS setup (theme + editor)

> You don’t have local PHP — that’s fine. Tailwind also doesn’t require PHP locally. If you **can’t** run Node yet, start **without Tailwind** (theme.json + core blocks). Add Tailwind later.

**Phase A (no Tailwind yet)**

- Rely on `theme.json` tokens for colors/typography.
- Use core blocks + block styles; keep layout simple.

**Phase B (add Tailwind when possible)**

- Add `build/tw.css` and the enqueue code already present in `functions.php`.
- Use `prefix:'tw-'` and `preflight:false`.
- Optional (temporary): Tailwind Play CDN for quick experiments only; remove before launch.

_(The full CLI config remains in this guide for later use.)_

---

## Carousel (no plugin, Tailwind + CSS Scroll Snap)

**HTML (block pattern idea)**

```html
<div class="tw-relative" data-carousel>
  <div
    class="tw-flex tw-gap-4 tw-overflow-x-auto tw-scroll-smooth tw-snap-x tw-snap-mandatory tw-pb-2"
    data-carousel-track
  >
    <!-- Each card is a core Group/Image block with these classes -->
    <div class="tw-snap-start tw-snap-always tw-shrink-0 tw-w-80">
      <!-- Card content here -->
    </div>
    <!-- repeat -->
  </div>
  <button
    class="tw-absolute tw-left-0 tw-top-1/2 -tw-translate-y-1/2 tw-p-2"
    data-carousel-prev
    aria-label="Zurück"
  >
    ‹
  </button>
  <button
    class="tw-absolute tw-right-0 tw-top-1/2 -tw-translate-y-1/2 tw-p-2"
    data-carousel-next
    aria-label="Weiter"
  >
    ›
  </button>
</div>
```

**assets/js/carousel.js** (≈20 lines)

```js
(function () {
  document.querySelectorAll("[data-carousel]").forEach((root) => {
    const track = root.querySelector("[data-carousel-track]");
    const prev = root.querySelector("[data-carousel-prev]");
    const next = root.querySelector("[data-carousel-next]");
    const step = () => track.clientWidth * 0.9;
    const scrollBy = (dx) => track.scrollBy({ left: dx, behavior: "smooth" });
    prev && prev.addEventListener("click", () => scrollBy(-step()));
    next && next.addEventListener("click", () => scrollBy(step()));
    // A11y: pause auto-scroll on hover if added later
    root.addEventListener("mouseenter", () => (root.dataset.paused = "1"));
    root.addEventListener("mouseleave", () => (root.dataset.paused = ""));
  });
})();
```

**A11y**

- `role="region"` + `aria-label` on root when used.
- Keyboard focusable nav buttons; no layout shift; pause on hover.

---

## Plugins — minimal set

- **Cookie consent (GDPR):** _Complianz_ **or** _CookieYes_. One only.
- **SEO (optional):** _The SEO Framework_ (lightweight) **or** use core + our JSON‑LD.
- **SVG uploads:** _Safe SVG_ (if you need vector logos/icons in Media Library).
- **Redirects (optional):** _Redirection_ to map `/standorte → /studios` if you don’t add a page alias.

> Everything else (Magicline, carousels, reviews, schema) we do in-theme.

---

## Pre‑flight checklist (before we start build)

- **Decisions**:

  - Ziel featured count per page: **2 / 4 / 6**
  - Timetable weekend visible: **always / on demand**
  - Level badges: **Fortgeschrittene only / 3‑stufe**
  - Negative‑radius accent width under course cards: **8–12 px**

- **IDs & Keys**:

  - Google Places **Place IDs** per Studio, API key (restricted)
  - Magicline tenant base URL + **Connect/Open API** keys

- **Mappings**:

  - Optional: list of Kurs formats that **get full pages**
  - (Later) map _classId ⇄ kurs_ for richer modals

- **Assets**: font files/licence, hero video (≤30s), logo variants, review photos
- **Performance**: image sizes (WebP), lazyload, video compression target, cache headers
- **Compliance**: cookie banner config, imprint/legal texts, privacy policy
- **Analytics**: events for Probetraining flow (view slot, start lead, submit)

## Magicline — opening hours & data

- Use **Magicline Open API** `GET /v1/studios/information` to fetch `publicOpeningHours`, address, phone, logo, etc. Store in a daily cron (server-side) and render via a **dynamic block** (Opening Hours) with studio selector.
- Keep API key in `.env` / `wp-config.php` and WP options; add a simple admin settings page for the key + tenant hostname.
- Fallback: manual hours fields on `studio` if API unavailable.

---

## Magicline — Probetraining (trial session) booking plan

**Phase 1 (fast):** Embed Magicline widget on `/probetraining/` and in Studio pages; style via Tailwind utility classes scoped to the container.

**Phase 2 (custom flow, Connect API):**

1. **Discovery**: List bookable offers via Connect API:

   - `GET /v1/trial-offers/bookable-trial-offers/classes`
   - `GET /v1/trial-offers/bookable-trial-offers/appointments/bookable`

2. **Slots**: Fetch slots per class/appointment:

   - `GET /v1/trial-offers/bookable-trial-offers/classes/{classId}/slots`
   - `GET /v1/trial-offers/bookable-trial-offers/appointments/bookable/{bookableAppointmentId}/slots`

3. **Lead**: Validate & create:

   - `POST /v1/trial-offers/lead/validate` → `POST /v1/trial-offers/lead/create`

4. **UX**: Modal with required fields (Name, Email, Telefon, Alter), confirmation screen, email + T‑1 reminder; optional `.ics` download.
5. **Timetable embed**: On Studio pages, embed prefiltered timetable from `kurs` CPT; the „Jetzt“-Marker and states are client-side only.

Security: Calls go through a **server-side proxy WP REST endpoint** to avoid exposing API keys. Add rate limiting and logging. Graceful fallbacks if API fails.

---

## Next step: page-by-page build order

1. **Blank → Editor-ready**: activate theme (with your `theme.json`), create Header/Footer **template parts** in Site Editor; create legal pages & 5 Ziel pages
2. Home (Hero, Review-Row, Ziele-Mosaik, Kurs-Teaser, Tarife-Teaser, News)
3. Studios (Archive + Single)
4. Kursprogramm (Archive grid + Modals)
5. Ziel pages (wire filters + featured)
6. Tarife (Mosaik + Tabelle; no singles)
7. Probetraining (optional) / timetable modals

> Keep everything block-based; introduce Tailwind only when you’re ready.

---

## Roadmap (checkable, by milestones)

> Start from a **completely blank** theme (only `theme.json`). No local PHP required.

### Milestone 0 — Boot & Editor-Ready (No code tools)

- [x] Activate blank theme with provided `theme.json`
- [x] Site Editor → create **Header** template part (menu, logos, Home link)
- [x] Site Editor → create **Footer** template part (Impressum, Datenschutz, AGB, Cookies)
- [x] Create Pages: Home, Training, 5× Ziel pages, Tarife, Über uns, Kontakt, Firmenfitness, Impressum, Datenschutz, AGB, Cookies
- [ ] Build Patterns: **Button**, **Card**, **Review Row**, **CTA** (core blocks only)

**DoD:** Editors can compose pages with header/footer + patterns; colors/fonts correct via `theme.json`.

### Milestone 1 — Minimal PHP drop‑in (via Theme Editor)

- [ ] Paste **single `functions.php`** from guide (CPTs, taxonomies, editor styles, PPC query vars)
- [ ] Verify taxonomies appear on **Posts & Pages**; editors can **assign** (not create) terms
- [ ] Create **`archive-studio.html`**, **`single-studio.html`**, **`archive-kurs.html`**, **`single-kurs.html`**, **`page-ziel.html`** block templates

**DoD:** Studios/Kurse structures exist; Ziel pages filter posts; no Tailwind yet.

### Milestone 2 — Home (top → bottom)

- [ ] Hero (Cover with overlay presets), Review Row (Testimonials CPT), Ziele mosaic links
- [ ] Kurs teaser (cards) and Tarife teaser (Basic + Young)
- [ ] News grid (equal heights, focal points)

**DoD:** Home is fully editable.

### Milestone 3 — Studios

- [ ] Archive grid with quick rating badge placeholder
- [ ] Single: Ausstattung navigator, Team overlay cards, prefiltered timetable embed

**DoD:** Editors can publish studios.

### Milestone 4 — Kursprogramm (MVP)

- [ ] Phase 1: embed/widget for timetable
- [ ] Phase 2: Connect API for sessions → modal details; sticky filters; „Jetzt“-marker

**DoD:** Schedule browsable; editors don’t manage times.

### Milestone 5 — Ziel pages (5×)

- [ ] Wire featured (2–6) + filtered loop (9/Seite) on each page
- [ ] Implement PPC query filters (ziel/studio/level/testimonials/pinned)

**DoD:** Editors publish posts and they appear correctly; PPC links pre-filter.

### Milestone 6 — Tarife

- [ ] `/tarife/` mosaic + cards table powered by `tarif` CPT (no singles)

**DoD:** Pricing editable without dev.

### Milestone 7 — Probetraining (optional)

- [ ] Option A: landing with Magicline widget; Option B: timetable modal booking

**DoD:** Booking path is clear and working.

### Milestone 8 — Perf, A11y, Schema

- [ ] Lazy images/WebP, ARIA on carousels/modals, `LocalBusiness` + `Article` schema

### Milestone 9 — Redirects & Domains

- [ ] Map legacy `/standorte/` → `/studios/`; set **domain → studio** 301s (excitingfit.at → studio/exciting-fit, vivo.fit → studio/vivo)

### Milestone 10 — Tailwind (optional)

- [ ] Add compiled `build/tw.css` and enable utilities in editor/front

---

## Handy prompts you can use with me

> Copy/paste and fill the {…} bits. I’ll return code + instructions.

### Blocks & Patterns

- **“Make a block pattern: {name}. Sections: {list}. Blocks: core-only. Use theme.json colors/classes; no Tailwind yet.”**
- **“Create a dynamic block ‘Opening Hours’ (placeholder now); later wire Magicline API.”**
- **“Build a Ziel page pattern (intro split + featured posts + filtered Query Loop) wired to `ziel_topic={slug}`.”**

### Templates & Queries

- **“Give me `page-ziel.html` markup with placeholders and the PHP snippet to inject a `tax_query` from `site_get_ziel_filters()`.”**
- **“Write `archive-studio.html` using a Query Loop and include a `rating-badge` placeholder block.”**

### JS & UI

- **“Provide minimal JS for scroll-snap carousel, 20 lines, with prev/next buttons and no layout shift.”**
- **“Implement modal accessibility utilities (focus trap + ESC close) in vanilla JS.”**

### Redirects & Legal

- **“Generate .htaccess rules to redirect `{domainA}` → `{target}` preserving path/query.”**
- **“Create a CSV for the Redirection plugin from this mapping list: {old → new}.”**

### Tailwind (later)

- **“Tailwind config + enqueue once Node is available; map to theme.json tokens.”**

---

# Annex A — Expanded, step‑by‑step roadmap & boilerplate (blank theme)

## A1) Theme files checklist (minimum to activate)

- [ ] `style.css` (header only)

```css
/*
Theme Name: Fitness Salzkammergut
Text Domain: fitness-skg
Version: 0.0.1
Requires at least: 6.6
Requires PHP: 8.1
*/
```

- [ ] `theme.json` (your provided file)
- [ ] _(Later)_ `functions.php` (from this guide)

**DoD:** Theme activates and Site Editor opens with your palette/fonts.

## A2) Site Editor boot sequence

1. Appearance → Editor → **Template Parts** → create **Header** (navigation block, logos, Home link).
2. Create **Footer** (legal links).
3. Templates → add **Home**, **Page**, **Single**, **Index** as needed.
4. Create Pages: Home, Training, 5× Ziel, Tarife, Über uns, Kontakt, Firmenfitness, Impressum, Datenschutz, AGB, Cookies.

## A3) Patterns to create (core-only, no code)

- **Buttons** (primary/secondary) using theme.json colors.
- **Card** (image top, text body, negative-radius style achieved via spacing/border presets).
- **Review Row** (3–5 testimonials; use Testimonial CPT later).
- **CTA** (copy + button).
- **Ziele Mosaic** (2/3 + 1/3 layout then 3-up grid).

## A4) Paste-one-file PHP (when ready)

- Open Theme Editor → theme files → add **`functions.php`** (use the single-file version from this guide).
- Refresh editor; confirm CPTs appear; confirm taxonomies attach to Posts & Pages and **can’t be created** by editors.

## A5) Page-by-page implementation (granular)

### Home

- [ ] Hero Cover with overlay preset (desktop side→center, mobile bottom→top)
- [ ] Review Row pattern (static for now)
- [ ] Dein Training intro + CTA
- [ ] Ziele mosaic (links to 5 Ziel pages)
- [ ] Kurs teaser cards
- [ ] Tarife teaser (Basic + Young prominent)
- [ ] News grid (equal heights, focal point)
- [ ] Partnerlogos section (light bg)

### Studios — Archive

- [ ] Query Loop of `studio` with card pattern
- [ ] Rating badge placeholder (Google Places later)

### Studios — Single

- [ ] Hero media (full-bleed)
- [ ] Ausstattung-Navigator (background swap via simple JS later)
- [ ] Team cards with overlay
- [ ] Prefiltered timetable embed

### Kursprogramm

- [ ] Phase 1: embed/widget (zero code)
- [ ] Phase 2: Connect API sessions → modals (later)
- [ ] Sticky filters (studio/ziel/level; week/day toggle)
- [ ] „Jetzt“-marker (JS later)

### Ziel pages (5×)

- [ ] Template `page-ziel.html`: intro split + testimonial column
- [ ] Featured posts (2–6 manually selected)
- [ ] Filtered Query Loop by `ziel_topic` + pagination (9/Seite)
- [ ] PPC query filters respected (`ziel, studio, level, testimonials, pinned`)

### Tarife

- [ ] Mosaic (Basic, Flex, Young…)
- [ ] Table for Kartenprodukte
- [ ] No singles for `tarif`

### Probetraining (optional)

- [ ] Option A: landing page with embedded widget
- [ ] Option B: timetable modals only

## A6) Redirects

- [ ] Install **Redirection** plugin
- [ ] Import CSV with mappings (old → new). Columns: `source_url,target_url,match_type,group`
- [ ] Add wildcard rule `/standorte/(.*)` → `/studios/$1`
- [ ] Domain-level 301s set at host/edge (if possible); if not, temporary mu-plugin fallback.

## A7) A11y, Perf, Schema (checklist)

- [ ] Carousels: buttons focusable, `aria-labels`, pause on hover/focus
- [ ] Modals: focus trap + ESC close
- [ ] Lazy images, WebP, `fetchpriority` on hero
- [ ] Schema: `LocalBusiness` on studios, `Article` on posts

## A8) Content & QA

- [ ] Seed 3–5 Testimonials per Ziel/Studio
- [ ] Publish first 6–9 Ziel posts (1 per Ziel to start)
- [ ] Run through mobile breakpoints and keyboard-only QA

---

# Annex B — Edits to `theme.json` you might want later

- **Link colors** (default + hover): set in `styles.elements.link.color.text` and add hover style via block styles or custom CSS var.
- **Overlay presets** for Cover block (desktop side→center, mobile bottom→top).
- **Border radius scale** (custom CSS vars) for cards/buttons.
- **Typography scale** presets (xs/sm/base/lg/xl/2xl) in `settings.typography.fontSizes` so editors pick consistent sizes.

**Prompt:** _“Propose a theme.json diff to add link colors (default/hover), a cover overlay preset, and a radius scale that matches our cards.”_
