# Howard Paragraphs Facebook Feed

Paragraphs integration of a Facebook Feed for Howard projects:

This module contains markup only (no js or css), those should be provided in the client theme, loaded via the idfive Component Library:

 - [idfive Component Library](https://bitbucket.org/idfivellc/idfive-component-library)
 - [idfive Component Library D8 Theme](https://bitbucket.org/idfivellc/idfive-component-library-d8-theme)

# Tokens/Etc
See https://stackoverflow.com/questions/17197970/facebook-permanent-page-access-token for information on generating permanent access tokens/etc.

# Composer

This module needs the following library, [facebook/graph-sdk](https://github.com/facebook/php-graph-sdk) added to the projects composer file:

 - composer require facebook/graph-sdk

## Markup Overrides

- You may override paragraphs templates by copying them into the client theme.
- You may override hooks by copying into client .theme, and modifying hook name/etc.
