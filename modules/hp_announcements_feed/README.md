# Howard Paragraphs Announcements Feed

Paragraphs integration of a Announcements Feed (from thedig.howard.edu) for Howard projects. Essentially, this module pulls a feed of announcements from thedig.howard.edu, with optional filter parameters available, in order to highlight relevant announcements.

This module contains markup only (no js or css), those should be provided in the client theme, loaded via the idfive Component Library:

- [idfive Component Library](https://bitbucket.org/idfivellc/idfive-component-library)
- [idfive Component Library D8 Theme](https://bitbucket.org/idfivellc/idfive-component-library-d8-theme)

## Configuration

The following fields are available on this KS widget:

- Title: Sets the title in the header bar. Defaults to "News".
- Link: Sets the "View all Link" in the header bar, defaults to production newsroom (newsroom.howard.edu).
- Feed Type: Choose which style of feed you wish to display.
- Range: The max number of announcements to display. Between 3 and 30.
- Environment: Choose which environment of the newsroom to pull news from. Defaults to production.
- Announcement Category: Filters announcements by the Announcement Category taxonomy (announcement_category) in the dig.
- Announcement Sponsoring Unit: Filters announcements by the Announcement Sponsoring Unit taxonomy (announcement_sponsoring_unit) in the dig.

## Notes on feed filtering

If no optional filters are chosen, it will show a default feed.

These filters are additive, meaning they will combine if you choose more than one. For example, if both "News Category" and "Schools and Colleges" are chosen, the query would say "Give me all announcements tagged as catergory=x AND schools_and_colleges=y". Usually, only one should be chosen in order to have news refresh at a better rate.

## Markup Overrides

- You may override paragraphs templates by copying them into the client theme.
- You may override hooks by copying into client .theme, and modifying hook name/etc.
