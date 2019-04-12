# Howard Paragraphs Articles

Paragraphs integration of the ability to add a feed of local Articles in Howard client projects. "Articles" being a content type added from howard_content_types. It relies on a view (hp_articles_ks_feed) that could be modified if needed.

This module contains markup only (no js or css), those should be provided in the client theme, loaded via the idfive Component Library:

 - [idfive Component Library](https://bitbucket.org/idfivellc/idfive-component-library)
 - [idfive Component Library D8 Theme](https://bitbucket.org/idfivellc/idfive-component-library-d8-theme)

## Configuration
The following fields are available on this KS widget:
 - Article Categories Filter: Optional, prefilters articles by HC Article Category (hc_article_category). If no category chosen, all articles will display.

## Configuration Overrides
You could override the base view (hp_articles_ks_feed) if needed.

## Markup Overrides
- You may override paragraphs templates by copying them into the client theme.
- You may override hooks by copying into client .theme, and modifying hook name/etc.
