# Howard Paragraphs Deadline Feed

Paragraphs integration of an Deadlines Feed (from calendar.howard.edu) for Howard projects. Essentially, this module pulls a feed of upcoming deadlines from calendar.howard.edu, with optional filter parameters available, in order to highlight relevant deadlines.

This module contains markup only (no js or css), those should be provided in the client theme, loaded via the idfive Component Library:

- [idfive Component Library](https://bitbucket.org/idfivellc/idfive-component-library)
- [idfive Component Library D8 Theme](https://bitbucket.org/idfivellc/idfive-component-library-d8-theme)

## Configuration

The following fields are available on this KS widget:

- Title: Sets the title in the header bar. Defaults to "Events".
- Number of deadlines to display: 1- 50. Sets the count on the API request to calendar.howard.edu.
- Hide time in deadline: Hides the time in deadline, and displays deadline only.
- Hide Schools / Colleges column: Hides the schools / colleges column in the table from display.
- Hide Department / Admin column: Hides the department / admin column in the table from display.
- Environment: Choose which environment of the calendar to pull deadlines from. Defaults to production.
- Deadline Admin / Department: Filters deadlines by the Admin Category taxonomy (calendar_taxonomy_5) in the calendar.
- Deadline Schools / Colleges: Filters deadlines by the Schools and Colleges taxonomy (calendar_taxonomy_1) in the calendar.

## Notes on deadline feed filtering

The feed is set to show deadlines happening before today. While there may be some lag time/caching, generally speaking it does not show past deadlines. We set this specifically as "show me all deadlines before tomorrow", so that current day deadlines would be shown. If no optional filters are chosen, it will show a default feed.

These filters are additive, meaning they will combine if you choose more than one. For example, if both "Admin Department" and "Schools and Colleges" are chosen, the query would say "Give me all deadlines tagged as admin=x AND schools_and_colleges=y". Usually, only one should be chosen in order to have events refresh at a better rate.

More information and documentation on the deadlines/events API can be found in the unical module on huenterprise.

## Markup Overrides

- You may override paragraphs templates by copying them into the client theme.
- You may override hooks by copying into client .theme, and modifying hook name/etc.
