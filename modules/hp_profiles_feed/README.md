# Howard Paragraphs Profiles Feed

Paragraphs integration of a Profiles Feed (from profiles.howard.edu) for Howard projects. Essentially, this module pulls a feed of profiles from profiles.howard.edu, with optional filter paramaters available, in order to feature faculty and staff by department.

This module contains markup only (no js or css), those should be provided in the client theme, loaded via the idfive Component Library:

 - [idfive Component Library](https://bitbucket.org/idfivellc/idfive-component-library)
 - [idfive Component Library D8 Theme](https://bitbucket.org/idfivellc/idfive-component-library-d8-theme)

## Configuration
The following fields are available on this KS widget:
 - Title: Sets the title in the header bar. Defaults to empty.
 - Descriptive Text: Optional text that appears below the title.
 - Environment: Choose which environment of the profiles site to pull profiles from. Defaults to production.
 - Department: Filters profiles by the Department taxonomy (profiles_department) on profiles.howard.edu.
 - Sort: Choose the sort order for profiles (e.g., alphabetical, by level).
 - Administrative Taxonomy: Optionally filter profiles by administrative classification from the "Administrative" taxonomy on profiles.howard.edu.

## Notes on profiles feed filtering
If no optional filters are chosen, it will show a default feed, though we highly recommend choosing a department.

These filters are additive, meaning they will combine if you choose more than one. For example, if both "Department" and "Administrative Taxonomy" are chosen, the query would say "Give me all people tagged as department=x AND admin=y". 

More information and documentation on the profiles API can be found in the idfive_profile_api module on huenterprise.

## Markup Overrides
- You may override paragraphs templates by copying them into the client theme.
- You may override hooks by copying into client .theme, and modifying hook name/etc.
