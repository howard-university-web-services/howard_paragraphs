# Howard Paragraphs

Paragraphs integration of the commonly used howard "Kitchen Sink" components.

These components are designed as individual submodules that you may enable as needed. These are designed to work with the idfive Component Library frontend.

This module, and submodules, contain markup only (no js or css), those should be provided in the client theme, loaded via the idfive Component Library:

 - [idfive Component Library](https://bitbucket.org/idfivellc/idfive-component-library)
 - [idfive Component Library D8 Theme](https://bitbucket.org/idfivellc/idfive-component-library-d8-theme)

## Instalation and Updates

**Install Via Composer:**
```
composer install howard/howard_paragraphs
```

**Update Via Composer:**
```
composer update howard/howard_paragraphs
```

## Submodules
The following submodules are available:
 - Accordion: Paragraphs integration of the silc accordion library for use in Howard projects
 - Alumni Feed: Paragraphs integration of the ability to add Announcements in Howard projects
 - Announcements: Paragraphs integration of the ability to add Announcements in Howard projects
 - Articles: Paragraphs integration of the ability to add a feed of Articles in Howard client projects
 - Button/Link: Paragraphs integration of buttons/Links for use in Howard projects
 - Callout: Paragraphs integration of callout with image for use in Howard projects
 - Cards: Paragraphs integration of cards for use in Howard projects
 - Carousel with Caption: Paragraphs integration of a Carousel with Caption for use in Howard projects
 - Carousel with Modal: Paragraphs integration of a Carousel with Modal for use in Howard projects
 - Embed: Paragraphs integration of the ability to add iframes/js/etc Howard projects
 - Events Feed: Paragraphs integration of Events Feed (from calendar.howard.edu) for Howard projects
 - Facebook Feed: Paragraphs integration of a Facebook Feed for Howard projects
 - Featured Article: Paragraphs integration of the ability to add Featured Articles in Howard projects
 - HTML: Paragraphs integration of HTML for use in Howard projects
 - Instagram Feed: Paragraphs integration of a Instagram Feed for Howard projects
 - Magazine Feed: Paragraphs integration of Magazine Feed (from magazine.howard.edu) for Howard projects
 - Media: Paragraphs integration of media for use in Howard projects
 - News Feed: Paragraphs integration of News Feed (from newsroom.howard.edu) for Howard projects
 - Parallax: Paragraphs integration of parallax scrolling for use in Howard projects
 - Profiles Feed: Paragraphs integration of Profiles Feed (from profiles.howard.edu) for Howard projects
 - Program: Paragraphs integration of embedding a Program (from programs.howard.edu) for Howard projects
 - Programs Feed (EXPERIMENTAL/UNFINISHED/DO NOT ENABLE)
 - Promo Space: Paragraphs integration of a Promotional Space for use in Howard projects
 - Table: Paragraphs integration of tablefield module for use in Howard projects, and set up to use tablesaw js
 - Testimonial: Paragraphs integration of testimonials for use in Howard projects
 - Twitter Feed: Paragraphs integration of a twitter Feed for Howard projects
 - Views Embed: Paragraphs integration of viewfield module for use in Howard projects
 - Webform Embed: Paragraphs integration of webform module for use in Howard projects
 - WYSIWYG Text Filter UL: Provides a filter to add ICL markup to UL's embedded in a WYSIWYG.


## Configuration Overrides
This module is designed so that config can be overridden locally. Essentially, the config provides a "starter" when installing the module, that can be modified per site. If config is "added to" after initial install, a manual config resync will likely need to be done. Something like:

 - drush cim -y --partial --source=modules/contrib/howard_paragraphs/SUBMODULE/config/install/

Keep in mind thast extensive testing should be done before attempting the above.

## Markup Overrides

- You may override paragraphs templates by copying them into the client theme.
- You may override hooks by copying into client .theme, and modifying hook name/etc.
