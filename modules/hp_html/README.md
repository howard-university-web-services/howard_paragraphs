# Howard Paragraphs HTML

Paragraphs integration of HTML for use in Howard projects:

This module contains markup only (no js or css), those should be provided in the client theme, loaded via the idfive Component Library:

 - [idfive Component Library](https://bitbucket.org/idfivellc/idfive-component-library)
 - [idfive Component Library D8 Theme](https://bitbucket.org/idfivellc/idfive-component-library-d8-theme)

## Configuration
The following fields are available on this KS widget:
 - Body: Standard WYSIWYG field for adding general content.
 - Show Sidebar: Boolean, sets whether this widget should display a sidebar. 
 - Sidebar Title: If sidebar is displayed, a title can be added here.
 - Sidebar Body: If sidebar is displayed, content can be added to this WYSIWYG.

## Content tips
This KS widget can be used in a variety of ways, but works best for text heavy content. While images/etc can be added, other KS widgets exist that will display these in a better fashion.

## Markup Overrides
- You may override paragraphs templates by copying them into the client theme.
- You may override hooks by copying into client .theme, and modifying hook name/etc.
