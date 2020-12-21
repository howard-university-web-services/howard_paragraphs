# Howard Paragraphs Alumni Featured

Paragraphs integration of a Profiles Feed (from profiles.howard.edu) for Howard projects. Essentially, this module pulls a specific alumni from profiles.howard.edu, by ID. It is for advanced users only, such as DSWS.

This module contains markup only (no js or css), those should be provided in the client theme, loaded via the idfive Component Library:

- [idfive Component Library](https://bitbucket.org/idfivellc/idfive-component-library)
- [idfive Component Library D8 Theme](https://bitbucket.org/idfivellc/idfive-component-library-d8-theme)

## Configuration

The following fields are available on this KS widget:

- Title: Sets the title in the header bar. Defaults to empty.
- Environment: Choose which environment of the profiles site to pull profiles from. Defaults to production.
- Chosen People: This field allows you to manually specify the ID of individual profiles to feature. These ID's are the node ID of the profile, in profiles.howard.edu.

## Notes

More information and documentation on the profiles API can be found in the idfive_profile_api module on huenterprise.

## Markup Overrides

- You may override paragraphs templates by copying them into the client theme.
- You may override hooks by copying into client .theme, and modifying hook name/etc.
