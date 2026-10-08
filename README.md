# Beleon Tours — WordPress theme (v2)

An Elementor-first, performance-focused theme for **Beleon Tours**: minimal and premium (serif display type, lapis/ink and brass palette, arches, rings, film grain, scroll reveals), fully editable in Elementor, and built around the existing `tours` and `destinations` post types.

| Lighthouse (staging, mobile, no caching plugin) | Perf | A11y | Best pr. | SEO |
|---|---|---|---|---|
| Home | 97–98 | 100 | 100 | 100 |
| Single tour | 98 | 100 | 100 | 100 |
| Tours archive | 98 | 100 | 100 | 100 |
| Destination | 99 | 100 | 100 | 100 |

Desktop home: 99/100/100/100. Scores vary a few points between runs with server load.

## What's inside

- **19 Elementor widgets** (category "Beleon"): Hero (with tour search), Heading, Tours (grid/carousel, upcoming departures, filters), Destinations (arches/mosaic/grid/carousel), Features, Stats (count-up), Testimonials, Call to action, Marquee, Image & story, Contact details, and for tour templates: Page hero, Post content, Tour facts, Tour booking card, Tour itinerary, Included/Not included, Highlights, Gallery (with lightbox).
- **Controls on every Elementor element**: *Beleon motion* (fade, mask reveal, curtain, zoom, stagger, hover lift/zoom/glow) and on containers *Beleon surface & shapes* (night/lapis/ivory/sand/gold/glass backgrounds, orbit rings, Silk Road arch, aurora, dot grid, horizon line, film grain, arch corners).
- **Site Settings drive everything**: Elementor global colours and fonts restyle the whole theme, PHP templates included.
- **Editable layout without Elementor Pro**: *Beleon → Layout* lets you assign an Elementor template to the header, footer, single tour, single destination and both archives. Elementor Pro Theme Builder templates take priority if Pro is installed. With nothing assigned, fast built-in PHP templates render.
- **Tours & destinations kept as they are**: the theme never replaces an existing registration (JetEngine on the live site). It only registers same-slug fallbacks when nothing else does.
- **Field map** (*Beleon → Tour fields*): point each fact (price, duration, departures, itinerary, gallery, linked destinations…) at your existing JetEngine/ACF meta keys; the screen suggests the keys already used by your tours. Without a mapping the theme's own fields (`bl_*`) and meta boxes are used.
- **Greek-first**: all theme texts are translated (`languages/el.*`); Greek is used on the front end even if wp-admin is in English (Customizer → Beleon → Brand & contact).
- **SEO**: TouristTrip JSON-LD on tours, breadcrumbs with schema, meta description + Open Graph (skipped automatically when Yoast/Rank Math/SEOPress/AIOSEO is active).

## Performance features

- Self-hosted variable fonts (Noto Serif Display + Manrope, Greek + Latin subsets), preloaded, `font-display: swap`, metric-matched fallbacks (CLS ≈ 0).
- One inline stylesheet (~12 KB gzipped), one deferred vanilla script (~6 KB). No jQuery, icon fonts or Google Fonts from the theme.
- **Lean Elementor**: Elementor's front-end JavaScript (and jQuery) is skipped on pages that only use static widgets; any page with entrance animations, sliders, tabs, background video etc. keeps it automatically. `add_filter( 'beleon_lean_elementor', '__return_false' );` turns it off.
- WebP sub-sizes for uploads, responsive `srcset` sizes (16:9 and 4:5), `fetchpriority="high"` on hero images, lazy loading elsewhere.
- Block-editor CSS, emoji script, heartbeat and other head clutter removed where unused.

For the best real-world scores on the live site also enable page caching (Plesk/NGINX cache or a caching plugin), Brotli/GZIP and PHP 8.2+.

## Install / update

1. `tools/build.sh` → `dist/beleon-tours.zip` (minifies CSS/JS, compiles translations).
2. Appearance → Themes → Add New → Upload → replace the current *Beleon Tours* theme.
3. Elementor → Settings: make sure *Tours* and *Destinations* are ticked under post types (done automatically on activation).
4. Beleon → Tour fields: map the JetEngine meta keys (see below).
5. Customize → Beleon: phones, email, offices, socials, header button, booking page.

### Moving to the live site (JetEngine)

The live tours/destinations come from JetEngine and have their own Elementor single templates. After switching themes:

- Existing Elementor content keeps working. JetEngine listings and dynamic tags are untouched.
- Open **Beleon → Tour fields**, set "Edit fields with" to *My plugin (JetEngine / ACF)* and type each JetEngine field's meta key (the dropdown lists the keys your tours already use). The linked-destinations field should point at the JetEngine relation/posts field.
- If you prefer the theme's tour design over the old JetEngine single template, unassign that template in JetEngine/Elementor; otherwise keep it and add Beleon widgets to it.
- Always test on staging first and take a full backup (All-in-One WP Migration is installed).

## Editing tips

- In any heading field, wrap words in `*asterisks*` for the italic gold accent: `Ταξίδια που *δεν* βρίσκεις αλλού`.
- Departure dates: one per line, `2027-05-29 | Τελευταίες θέσεις` (the note shows as a badge).
- Itinerary: one day per block (first line = title, next lines = description), blank line between days.
- The hero search and the *Tours* widget's filter bar use `?dest=<destination ID>&month=YYYY-MM`.

## Developer notes

- `inc/fields.php`: every tour value is read through `beleon_field()`/`beleon_text()`/`beleon_departures()`…; filters `beleon_meta_key`, `beleon_field_value`, `beleon_tour_destination_ids`.
- `inc/render.php`: markup shared by widgets and PHP templates.
- `inc/elementor/`: integration (fonts, kit defaults, controls) and widgets.
- Strings: edit `tools/el.py`, then `python3 tools/build-i18n.py` (fails if a string is untranslated).
- Font licences: SIL Open Font License (`assets/fonts/OFL-*.txt`).
