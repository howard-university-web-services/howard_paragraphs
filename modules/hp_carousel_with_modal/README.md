# Howard Paragraphs Carousel with Modal

Paragraphs integration of a Carousel with Modal for use in Howard projects. It provides an image carousel, that opens a modal with the image inside it when clicked.

This module contains markup only (no js or css), those should be provided in the client theme, loaded via the idfive Component Library:

 - [idfive Component Library](https://bitbucket.org/idfivellc/idfive-component-library)
 - [idfive Component Library D8 Theme](https://bitbucket.org/idfivellc/idfive-component-library-d8-theme)

## Configuration
The following fields are available on this KS widget:
 - Title: The title in the top bar.
 - Slides: you may add an unlimited amount of slides. Each slide contains the following:
    - Image: Media type image, add the desired image.

## Markup Overrides
- You may override paragraphs templates by copying them into the client theme.
- You may override hooks by copying into client .theme, and modifying hook name/etc.
