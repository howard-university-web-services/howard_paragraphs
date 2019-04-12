# Howard Paragraphs Events Feed

Paragraphs integration of an Events Feed (from calendar.howard.edu) for Howard projects. Essentially, this module pulls a feed of upcoming events from calendar.howard.edu, with optional filter paramaters available, in order to highlight relevant events.

This module contains markup only (no js or css), those should be provided in the client theme, loaded via the idfive Component Library:

 - [idfive Component Library](https://bitbucket.org/idfivellc/idfive-component-library)
 - [idfive Component Library D8 Theme](https://bitbucket.org/idfivellc/idfive-component-library-d8-theme)

## Configuration
The following fields are available on this KS widget:
 - Title: Sets the title in the header bar. Defaults to "Events".
 - Link: Sets the "View all Link" in the header bar, defaults to production calendar (calendar.howard.edu). 
 - Environment: Choose which environment of the calendar to pull events from. Defaults to production.
 - Event Category: Filters events by the Category taxonomy (calendar_taxonomy_3) in the calendar.
 - Event Admin Category: Filters events by the Admin Category taxonomy (calendar_taxonomy_5) in the calendar.
 - Event Schools and Colleges: Filters events by the Schools and Colleges taxonomy (calendar_taxonomy_1) in the calendar.

## Notes on event feed filtering
The feed is set to show events happening today or after. While there may be some lag time/caching, generally speaking it does not show past events. If no optional filters are chosen, it will show a default feed.

These filters are additive, meaning they will combine if you choose more than one. For example, if both "News Category" and "Schools and Colleges" are chosen, the query would say "Give me all events tagged as catergory=x AND schools_and_colleges=y". Usually, only one should be chosen in order to have events refresh at a better rate.

More information and documentation on the events API can be found in the unical module on huenterprise.

## Markup Overrides
- You may override paragraphs templates by copying them into the client theme.
- You may override hooks by copying into client .theme, and modifying hook name/etc.
