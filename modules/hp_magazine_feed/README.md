# Howard Paragraphs Magazine Feed

Paragraphs integration of Magazine Feed (from magazine.howard.edu) for Howard projects. Essentially, this module pulls a feed of Howard Magazine articles from magazine.howard.edu, with optional filter paramaters available, in order to feature relevant articles.

This module contains markup only (no js or css), those should be provided in the client theme, loaded via the idfive Component Library:

 - [idfive Component Library](https://bitbucket.org/idfivellc/idfive-component-library)
 - [idfive Component Library D8 Theme](https://bitbucket.org/idfivellc/idfive-component-library-d8-theme)

## Configuration
The following fields are available on this KS widget:
 - Title: Sets the title in the header bar.
 - Link: Sets the "View all Link" in the header bar, defaults to production magazine (magazine.howard.edu).
 - Environment: Choose which environment of the magazine site to pull articles from. Defaults to production.
 - Article Category: Filters events by the Category taxonomy (category) in the calendar.
 - Article Admin Category: Filters events by the Administrative Category taxonomy (admin_categories) in the calendar.

## Notes on profiles feed filtering
If no optional filters are chosen, it will show a default feed, though we highly reccomend choosing a department.

These filters are additive, meaning they will combine if you choose more than one. For example, if both "Article Category" and "Article Admin Category" are chosen, the query would say "Give me all articles tagged as catergory=x AND admin_category=y". Usually, only one should be chosen in order to have events refresh at a better rate.

More information and documentation on the magazine API can be found in the idfive_magazine_api module on huenterprise.

## Markup Overrides
- You may override paragraphs templates by copying them into the client theme.
- You may override hooks by copying into client .theme, and modifying hook name/etc.
