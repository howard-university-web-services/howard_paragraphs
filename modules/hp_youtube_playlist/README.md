# Howard Paragraphs Youtube Playlist

Paragraphs integration of a Youtube for Howard projects:

This module contains markup only (no js or css), those should be provided in the client theme, loaded via the idfive Component Library:

 - [idfive Component Library](https://bitbucket.org/idfivellc/idfive-component-library)
 - [idfive Component Library D8 Theme](https://bitbucket.org/idfivellc/idfive-component-library-d8-theme)

## Configuration
The following fields are available on this KS widget:

 - Title
 - Description: Optional.
 - Playlist ID: The ID of the playlist to embed.
 - Display choice: Default (links out to youtube) or Play in Page, which uses embeds.

This widget has a configuration page at admin/config/hp_youtube_playlist/settings which takes the following:

- YouTube Data API key

These credentials can be obtained from the google Developer portal. It is important that this API key be created and entered, or no playlist will appear.


## Markup Overrides

- You may override paragraphs templates by copying them into the client theme.
- You may override hooks by copying into client .theme, and modifying hook name/etc.
