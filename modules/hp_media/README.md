# Howard Paragraphs Views Embed

Paragraphs integration of media for use in Howard projects. It is a way to add a single media entity of any type.

This module contains markup only (no js or css), those should be provided in the client theme, loaded via the idfive Component Library:

 - [idfive Component Library](https://bitbucket.org/idfivellc/idfive-component-library)
 - [idfive Component Library D8 Theme](https://bitbucket.org/idfivellc/idfive-component-library-d8-theme)

## Configuration
The following fields are available on this KS widget:
 - Title: Optional, the title in the top bar.
 - Media: You may add Audio, File, Image, or Remote Video media types here.
 - Caption: You may add a caption that will appear under the media.

## Markup Overrides
- You may override paragraphs templates by copying them into the client theme.
- You may override hooks by copying into client .theme, and modifying hook name/etc.
