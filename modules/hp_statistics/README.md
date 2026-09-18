# Howard Paragraphs Statistics

Paragraphs integration of a Statistics widget for Howard projects: a heading/intro/link block plus up to 3 repeating statistic items (`hp_statistics_item`).

Kept as a fully separate paragraph type from `hp_data_point` (not a reuse/reference of it) - the visual styling needs diverge enough (large stat + small superscript suffix, source line) that sharing fields/markup wasn't worth the coupling.

This module contains markup only (no js or css), those should be provided in the client theme, loaded via the idfive Component Library:

 - [idfive Component Library](https://bitbucket.org/idfivellc/idfive-component-library)
 - [idfive Component Library D8 Theme](https://bitbucket.org/idfivellc/idfive-component-library-d8-theme)

## Fields

**`hp_statistics` (container)**

| Field | Purpose |
|---|---|
| `field_hp_stat_eyebrow` | Small heading label above the intro (e.g. "H2 HEADING" in the design) |
| `field_hp_stat_title` | Large intro heading |
| `field_hp_stat_text` | Optional descriptive text |
| `field_hp_stat_link` | Optional link |
| `field_hp_stat_items` | Up to 3 `hp_statistics_item` paragraphs |

**`hp_statistics_item` (child, max 3 per container)**

| Field | Purpose |
|---|---|
| `field_hp_si_stat` | The statistic number (e.g. "85", "1,200") |
| `field_hp_si_suffix` | Optional suffix rendered smaller/raised next to the number (e.g. "%", "th") |
| `field_hp_si_body` | Statistic description |
| `field_hp_si_source` | Optional source text |

## Markup Overrides

- You may override paragraphs templates by copying them into the client theme.
- You may override hooks by copying into client .theme, and modifying hook name/etc.
