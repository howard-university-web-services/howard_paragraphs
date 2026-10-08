# Howard Paragraphs: Events Feed

Provides a complete, server-rendered Localist events feed from **events.howard.edu** as an `HP Events Feed` Paragraph. Uses the [Localist API](https://developer.localist.com/doc/api) and [Concept3D's API guide](https://help.concept3d.com/hc/en-us/articles/11940613344915-Localist-API). Supports Drupal 10/11 and the existing idfive component markup. A scoped CSS library provides Howard’s image-card and text-card layouts. No frontend JavaScript or external-data-source module is required.

## Enable and add to a page

1. Enable the submodule: `drush en hp_events_feed -y`, then `drush cr`.
2. In **Structure → Content types → your content type → Manage fields**, edit the Paragraphs / Kitchen Sink entity-reference-revisions field and allow the **HP Events Feed** paragraph type. Sites using `howard_content_types` may need their Kitchen Sink bundle allowlist updated in that project; this repository does not own those fields.
3. Add an HP Events Feed paragraph. Set a heading, optional calendar link, layout, and number of events (1–100).
4. Open the filter sections to configure the feed. Save the page.

For sites that already have this module enabled, run `drush updb -y` followed by `drush cr` to add the Text cards option and hide the legacy pagination control. Updates preserve existing layout selections.

Defaults: Events heading, View all events link, six events, pagination disabled, next 30 days, ascending event date, and the first matching occurrence of each event.

## Available filters

| Editor control | Localist parameter | Behavior |
| --- | --- | --- |
| Start date | `start` | Inclusive; blank rolls forward to today in America/New_York. |
| End date | `end` | Exclusive. Clear Number of days when using it; range must be 1–370 days. |
| Number of days | `days` | 1–370 days from start. Blank with no end means 30 days. |
| Created after/before | `created_after`, `created_before` | Filter on event creation dates. |
| Include categories | `type[]` | Any of the calendar's filter items, grouped under their actual labels. |
| Exclude categories | `exclude_type[]` | Exclude selected filter items. |
| Keywords or tags | `keyword[]` | One per line; not a free-text description search. |
| Place | `venue_id` | One place. |
| Group or department | `group_id` | One group/department; departments use the group ID parameter. |
| Community / campus | `campus_id` | One community, where configured on the calendar. |
| Attendance format | `experience` | In person, virtual, hybrid, or any. |
| Featured / sponsored only | `featured`, `sponsored` | Checked limits results; unchecked is unrestricted. |
| Recurring events | `recurring` | Recurring only, non-recurring only, or any. |
| Show each event once | `distinct` | First matching occurrence or repeated occurrences. |
| Sort / direction | `sort`, `direction` | Date, name, popularity, creation, or updated date; default/ascending/descending. |

The module retrieves `/api/2/events/filters` and `/api/2/events/labels` dynamically. Howard currently exposes Audience, Event Types, and Subject; new contexts appear without code changes. Parent categories are included in child labels. Places, groups, departments, organizations and communities are fully paginated when loading editor options. Removed or temporarily unavailable saved IDs remain selectable, so an API outage does not erase existing filters.

Matching is fixed to `match=all` and the display context to `for=widget`. These are automatic API settings rather than editor controls. Previously saved match/context settings and geographic filters are ignored; old distance sorting falls back to event date.

The feed always requests `publish_status=published` and omits private/unpublished event records. Private publication statuses, user-specific attendance, and response-only expansion flags (`all_custom_fields`, `include_attendance`) are not exposed as public-feed filters. Localist does not document arbitrary custom-field, price, or event-status filters on the event-list endpoint, so the module does not invent those parameters.

## API access

Howard's public read endpoints were verified without a token during development. Concept3D's help guide says API access requires a paid license; confirm your calendar's API entitlement with Concept3D if requests return 401/403. If a token is required, supply it **server-side** in the Drupal site's `settings.php` using your environment/secret configuration:

```php
$config['hp_events_feed.settings']['access_token'] = getenv('HOWARD_LOCALIST_ACCESS_TOKEN') ?: '';
```

The token is sent in the Authorization Bearer header, never in paragraph fields, browser URLs, markup, or logs. Keep it out of exported config. API URL is fixed to `https://events.howard.edu/api/2/`; HTTPS verification remains enabled and redirects are disabled.

## Rendering and cache behavior

- **Image cards (4 columns):** Navy background, equal-height white cards, landscape images, uppercase dates, start times and serif titles, matching the existing Localist widget.
- **Text cards (3 columns):** White background, bordered cards with a subtle shadow, no images, bold sans-serif titles and start–end time ranges. Missing end times and all-day events are handled explicitly.
- **List with images:** The image-card content in one column.
- Both card layouts collapse to two columns on tablets. Below 640px, all layouts use one column of text-only cards on a light background, with borders, shadows, bold sans-serif titles and time ranges. Mobile cards fit their content instead of using the desktop minimum height. Dates use the full month/day/year format. Times use America/New_York; all-day dates retain their calendar date. Event links open in a new tab.
- Feeds always show the first API results page, limited by Number of events. Pagination links are disabled, and legacy page query arguments and saved pager selections are ignored. The old pager field remains stored for compatibility but is hidden from paragraph forms.
- API feed pages cache for 5 minutes. Render metadata uses the remaining API cache lifetime, so an old cached API response cannot extend stale markup for another full 5 minutes. Rolling queries include today's date. Editor catalogues cache for an hour; failures retry after one minute.
- A response subscriber bounds `Expires` and public HTTP cache lifetimes on pages containing feeds, because Drupal's Internal Page Cache otherwise ignores render max-age. Existing private/no-cache/no-store responses remain restricted.
- Successful zero results show “No upcoming events”. Transport/HTTP/JSON/configuration failures show “Events are temporarily unavailable.” Failures are cached for one minute to avoid repeated requests.
- Cron invalidates the shared events cache tag. The base module's **Clear all Howard University external content feeds** action already includes `hp_events_feed`; API cache entries also carry paragraph tags, so this action refreshes feed data and markup.
- Remote descriptions render as escaped plain text. URLs must be HTTP(S). No external HTML or stream embed code is trusted.

## Fields and theming

| Field | Purpose |
| --- | --- |
| `field_hp_ef_title` | Heading (required) |
| `field_hp_ef_link` | Optional link to full calendar |
| `field_hp_ef_layout` | Image cards, text cards, or image list |
| `field_hp_ef_count` | Number of events |
| `field_hp_ef_pager` | Hidden legacy field; ignored when rendering |
| `field_hp_ef_filters` | JSON-backed filter settings, edited by `hp_events_filters` widget |

The filter field uses core `string_long` storage and a purpose-built widget; editors use labelled controls rather than writing JSON. Settings are revisionable with the paragraph. Keep the supplied widget selected in Manage form display. Imported/programmatically written filter JSON is defensively validated before fetching.

Override `paragraph--hp-events-feed.html.twig` for the section/header or `hp-events-feed-events.html.twig` for event rows in your client theme. Render `content.hp_events_feed` so the feed's cache metadata bubbles. Styling comes from existing `news-and-events`, `block-header` and `silc-grid` classes, with `hp-events-feed__*` hooks for customization. The bundled CSS controls responsive columns, landscape crops, card spacing, colors and layout-specific typography. Additional event metadata (location, summary, status, cost and calendar/registration URLs) remains available to template overrides.

## Verification

Run `php modules/hp_events_feed/tests/run.php /absolute/path/to/a/drupal/project/vendor/autoload.php` for the focused regression checks. They use the supplied Drupal/Guzzle/Twig libraries without connecting to a database or modifying that project. Live API smoke checks and a real Drupal install/page render are separate steps; the latter requires a configured Drupal site and database.
