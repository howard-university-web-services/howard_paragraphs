# Howard Paragraphs Deadlines Feed

Paragraphs integration of the Dates & Deadlines Feed for Howard projects.

This module contains markup only (no js or css), those should be provided in the client theme, loaded via the idfive Component Library:

 - [idfive Component Library](https://bitbucket.org/idfivellc/idfive-component-library)
 - [idfive Component Library D8 Theme](https://bitbucket.org/idfivellc/idfive-component-library-d8-theme)

## Fields

| Field | Purpose |
|---|---|
| `field_hp_df_title` | Widget heading |
| `field_hp_df_link` | Optional "view all" link, rendered alongside the title (same `block-header`/`fancy-link` markup as `hp_news_feed`/`hp_magazine_feed`) |
| `field_hp_df_environment` | Which Deadlines environment to pull from (Production/Staging/Development). Only affects the feed itself - the Category/Audience/School prefilter option lists always come from production sources. |
| `field_hp_df_count` | Number of upcoming items to show (3/5/10/20) |
| `field_hp_df_category` | Optional prefilter, single-select, sourced from `deadlines.howard.edu`'s Category taxonomy |
| `field_hp_df_audience` | Optional prefilter, multi-select (checkboxes), sourced from `deadlines.howard.edu`'s Audience taxonomy |
| `field_hp_df_school` | Optional prefilter, multi-select (checkboxes), sourced from **`thedig.howard.edu`'s** "Schools and Colleges" taxonomy - confirmed via live field config (`ws: news_schools_colleges`) that `field_hc_deadline_school` on the Deadlines site is itself populated from that source, not a local taxonomy |

Each filter is grouped into its own collapsed `details` accordion on the edit form (matches the pattern used by `hp_graph`/`hp_parallax`/`hp_timeline`), rather than one shared fieldset.

**Academic Term is intentionally not a filter.** Since this widget only ever shows upcoming items, filtering by term would empty the list out once a new term starts. It's still shown as informational text under each item's title (fulfilling the "subtitle" slot in the design spec), not offered as a prefilter option.

## Data source & the proxy pattern

This widget prefilters against content on `deadlines.howard.edu` (`node/hc_deadline`, JSON:API). Unlike other `hp_*_feed` widgets, **the frontend does not call the Deadlines site directly** - it calls a same-origin Drupal route (`/hp-deadlines-feed/proxy`, see `DeadlinesProxyController`) which forwards the request server-side. This is required because, as of this writing:

- `deadlines.howard.edu` has no production site yet - only `stg` is real
- `stg.deadlines.howard.edu` requires `huweb`/`huweb` HTTP basic auth and has a bad/expired SSL certificate

Neither of those can be worked around from a browser XHR (no way to send basic auth reliably cross-origin, no way to bypass a bad cert), but Drupal's server-side Guzzle request can (`'auth'`, `'verify' => FALSE`). **TODO once `deadlines.howard.edu` prod exists with a valid cert:** remove the stg-only auth/verify overrides in `DeadlinesProxyController` and the two `ExternalDataSource` plugins that query it directly (`DeadlinesCategory`, `DeadlinesAudience` - both currently point at `stg.deadlines.howard.edu`). `DeadlinesSchool` is unaffected - it queries `thedig.howard.edu` (production, no auth/cert issues) instead.

The proxy response is cached server-side for 5 minutes (keyed by the full outgoing request) and sent with `Cache-Control: no-store` so it's never picked up by an edge/Varnish cache, which otherwise wouldn't know this is per-paragraph dynamic content.

## Known gaps vs. the design spec

- **No "Time" field.** The wireframe's "Opens at [time]" badge style depends on a field that doesn't exist on `hc_deadline` - only Start Date and End Date (no time component). Can't be built until the Deadlines site adds it.
- **Only one link per item.** The wireframe shows what could be two links; `field_hc_deadline_link` only carries one value.
- **School/College filter uses `IN`-style plain-field filtering**, not a taxonomy relationship filter, since `field_hc_deadline_school` stores literal `"id=<tid>"` strings rather than an entity reference on the Deadlines site itself.

## Markup Overrides

- You may override paragraphs templates by copying them into the client theme.
- You may override hooks by copying into client .theme, and modifying hook name/etc.
