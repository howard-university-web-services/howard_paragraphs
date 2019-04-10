# Howard Paragraphs Twitter Feed

Paragraphs integration of a twitter Feed for Howard projects:

This module contains markup only (no js or css), those should be provided in the client theme, loaded via the idfive Component Library:

 - [idfive Component Library](https://bitbucket.org/idfivellc/idfive-component-library)
 - [idfive Component Library D8 Theme](https://bitbucket.org/idfivellc/idfive-component-library-d8-theme)

# Composer

This module needs the following library, [abraham/twitteroauth](https://github.com/abraham/twitteroauth) added to the projects composer file:

 - composer require abraham/twitteroauth

## Markup Overrides

- You may override paragraphs templates by copying them into the client theme.
- You may override hooks by copying into client .theme, and modifying hook name/etc.
