# Howard Paragraphs News Feed

Paragraphs integration of a News Feed (from newsroom.howard.edu) for Howard projects. Essentially, this module pulls a feed of articles from newsroom.howard.edu, with optional filter parameters available, in order to highlight relevant articles.

This module contains markup only (no js or css), those should be provided in the client theme, loaded via the idfive Component Library:

- [idfive Component Library](https://bitbucket.org/idfivellc/idfive-component-library)
- [idfive Component Library D8 Theme](https://bitbucket.org/idfivellc/idfive-component-library-d8-theme)

## Configuration

The following fields are available on this KS widget:

- Title: Sets the title in the header bar. Defaults to "News".
- Link: Sets the "View all Link" in the header bar, defaults to production newsroom (newsroom.howard.edu).
- Feed Type: Choose which style of feed you wish to display.
- Environment: Choose which environment of the newsroom to pull news from. Defaults to production.
- Category: Filters articles by the Category taxonomy (tags) in the dig.
- Events, Initiatives, Campaigns: Filters articles by the Events, Initiatives, Campaigns taxonomy (university_events_initiatives_ca) in the dig.
- Campus Units and Programs: Filters articles by the Campus Units and Programs taxonomy (campus_units_programs) in the dig.
- Schools and Colleges: Filters articles by the Schools and Colleges taxonomy (schools_and_colleges) in the dig.
- Howard Forward: Filters articles by the Howard Forward taxonomy (howard_forward) in the dig.

## Notes on news feed filtering

If no optional filters are chosen, it will show a default feed.

These filters are additive, meaning they will combine if you choose more than one. For example, if both "Category" and "Schools and Colleges" are chosen, the query would say "Give me all articles tagged as category=x AND schools_and_colleges=y". Usually, only one should be chosen in order to have news refresh at a better rate.

## Markup Overrides

- You may override paragraphs templates by copying them into the client theme.
- You may override hooks by copying into client .theme, and modifying hook name/etc.
