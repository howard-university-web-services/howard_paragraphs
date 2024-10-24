# Howard Paragraphs

Paragraphs integration of the commonly used howard "Kitchen Sink" components.

These components are designed as individual sub-modules that you may enable as needed. These are designed to work with the idfive Component Library frontend.

This module, and sub-modules, contain markup only (no js or css), those should be provided in the client theme, loaded via the idfive Component Library:

- [idfive Component Library](https://bitbucket.org/idfivellc/idfive-component-library)
- [idfive Component Library D8 Theme](https://bitbucket.org/idfivellc/idfive-component-library-d8-theme)

## Installation and Updates

### Install Via Composer

`composer install howard/howard_paragraphs`

### Update Via Composer

`composer update howard/howard_paragraphs`

## Submodules

The following submodules are available:

- Accordion: Paragraphs integration of the silc accordion library for use in Howard projects
- Alumni Feed: Provides a paragraphs bundle for embedding feeds of Featured Students and Alumni from profiles.howard.edu as a Kitchen Sink Widget
- Announcements: Paragraphs integration of the ability to add local Announcements in Howard projects
- Announcements Feed: Paragraphs integration of the ability to add Announcements from the dig, in Howard projects
- Articles: Paragraphs integration of the ability to add a feed of Articles in Howard client projects
- Button/Link: Paragraphs integration of buttons/Links for use in Howard projects
- Callout: Paragraphs integration of callout with image for use in Howard projects
- Cards: Paragraphs integration of cards for use in Howard projects
- Carousel with Caption: Paragraphs integration of a Carousel with Caption for use in Howard projects
- Carousel with Modal: Paragraphs integration of a Carousel with Modal for use in Howard projects
- Countdown: Paragraphs integration of a Countdown for use in Howard projects
- Data Point: Paragraphs integration of a Data Point for use in Howard projects
- Deadline Feed: Paragraphs integration of Deadline Feed (from calendar.howard.edu) for Howard projects [DEPRECATED]
- Divider: Paragraphs integration of a simple content divider for Howard projects
- Embed: Paragraphs integration of the ability to add iframes/js/etc for Howard projects
- Events Feed: Paragraphs integration of Events Feed (from calendar.howard.edu) for Howard projects [DEPRECATED]
- Facebook Feed: Paragraphs integration of a Facebook Feed for Howard projects [DEPRECATED]
- Featured People from thedig.howard.edu: Provides a paragraphs bundle for embedding curated Featured People from thedig.howard.edu as a Kitchen Sink Widget
- Featured Article: Paragraphs integration of the ability to add Featured Articles in Howard projects
- Giving Feed: Paragraphs integration of Giving Feed (from giving.howard.edu) for Howard projects
- Graphs: Paragraphs integration of the ability to add custom graphs in Howard projects
- HTML: Paragraphs integration of HTML for use in Howard projects
- Instagram Feed: Paragraphs integration of a Instagram Feed for Howard projects
- Magazine Feed: Paragraphs integration of Magazine Feed (from magazine.howard.edu) for Howard projects
- Media: Paragraphs integration of media for use in Howard projects
- News Feed: Paragraphs integration of News Feed (from thedig.howard.edu) for Howard projects
- Parallax: Paragraphs integration of parallax scrolling for use in Howard projects
- Photoshelter Feed: Paragraphs integration of Photoshelter Feed into a carousel with modal front end [DEPRECATED]
- Photoshelter Feed Grid: Paragraphs integration of Photoshelter Feed into a silc grid. [DEPRECATED]
- Profiles Feed: Paragraphs integration of Profiles Feed (from profiles.howard.edu) for Howard projects
- Program: Paragraphs integration of embedding a Program (from programs.howard.edu) for Howard projects
- Programs Feed Paragraphs integration of embedding a Programs Feed (from programs.howard.edu) for Howard projects
- Promo Space: Paragraphs integration of a Promotional Space for use in Howard projects
- Promo Space with Multiple Images: Paragraphs integration of a Promotional Space for use in Howard projects
- Table: Paragraphs integration of tablefield module for use in Howard projects, and set up to use tablesaw js
- Testimonial: Paragraphs integration of testimonials for use in Howard projects
- Timeline: Paragraphs integration of a timeline for use in Howard projects
- Twitter Feed: Paragraphs integration of a twitter Feed for Howard projects
- Video Slide Show: Provides a paragraphs bundle for rendering a video slide show as a Kitchen Sink widget in Howard projects.
- Views Embed: Paragraphs integration of viewfield module for use in Howard projects
- Webform Embed: Paragraphs integration of webform module for use in Howard projects
- YouTube Playlist: Paragraphs integration of a YouTube playlist for use in Howard projects
- WYSIWYG Text Filter UL: Provides a filter to add ICL markup to UL's embedded in a WYSIWYG.

## CRON and External Content

This module also provides a hook_cron(), to load external paragraph feeds, and invalidate caches when cron is run. This is so things like news and events feeds will update in a timely fashion, without resorting to clearing caches for all sites. This functionality can alo be run directly from the UI, if needed, by visiting [Clear all Howard University external content feeds](/admin/config/clear_howard_external_content).

Ideally, this gets run via an acquia scheduled job, every three hours or similar: `bash /var/www/html/${AH_SITE_NAME}/scripts/hal_sites.sh core-cron`.

## Configuration Overrides

This module is designed so that config can be overridden locally. Essentially, the config provides a "starter" when installing the module, that can be modified per site. If config is "added to" after initial install, a manual config resync will likely need to be done. Something like:

- `drush cim -y --partial --source=modules/contrib/howard_paragraphs/SUBMODULE/config/install/`

Keep in mind that extensive testing should be done before attempting the above.

## Markup Overrides

- You may override paragraphs templates by copying them into the client theme.
- You may override hooks by copying into client .theme, and modifying hook name/etc.
