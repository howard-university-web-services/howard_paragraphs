# Howard Paragraphs News Feed

Paragraphs integration of a News Feed (from newsroom.howard.edu) for Howard projects. Essentially, this module pulls a feed of articles from newsroom.howard.edu, with optional filter paramaters available, in order to highlight relevant articles.

This module contains markup only (no js or css), those should be provided in the client theme, loaded via the idfive Component Library:

 - [idfive Component Library](https://bitbucket.org/idfivellc/idfive-component-library)
 - [idfive Component Library D8 Theme](https://bitbucket.org/idfivellc/idfive-component-library-d8-theme)

## Configuration
The following fields are available on this KS widget:
 - Title: Sets the title in the header bar. Defaults to "News".
 - Link: Sets the "View all Link" in the header bar, defaults to production newsroom (newsroom.howard.edu). 
 - Feed Type: Choose which style of feed you wish to display.
 - Environment: Choose which environment of the newsroom to pull news from. Defaults to production.
 - News Category: Filters articles by the Category taxonomy (calendar_taxonomy_3) in the newsroom.
 - News Admin Category: Filters articles by the Admin Category taxonomy (calendar_taxonomy_5) in the newsroom.
 - News Schools and Colleges: Filters articles by the Schools and Colleges taxonomy (calendar_taxonomy_1) in the newsroom.

## Notes on news feed filtering
If no optional filters are chosen, it will show a default feed.

These filters are additive, meaning they will combine if you choose more than one. For example, if both "News Category" and "Schools and Colleges" are chosen, the query would say "Give me all articles tagged as catergory=x AND schools_and_colleges=y". Usually, only one should be chosen in order to have news refresh at a better rate.

More information and documentation on the newsroom API can be found in the idfive_newsroom_api module on huenterprise.

## Markup Overrides
- You may override paragraphs templates by copying them into the client theme.
- You may override hooks by copying into client .theme, and modifying hook name/etc.
