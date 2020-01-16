# Howard Paragraphs Timeline

Paragraphs integration of a timeline for use in Howard projects:

This module contains markup only (no js or css), those should be provided in the client theme, loaded via the idfive Component Library:

 - [idfive Component Library](https://bitbucket.org/idfivellc/idfive-component-library)
 - [idfive Component Library D8 Theme](https://bitbucket.org/idfivellc/idfive-component-library-d8-theme)

## Configuration

The following fields are available on this KS widget:
- Introduction
    - Title: Sets the title for the overall Timeline.
    - Text: Adds short follow-up text for the Timeline's title.
- Timeline Event
    - Title: Sets an event's title.
    - Description: Adds short descriptive text to describe an event.
    - Date: Sets the event's date.

## Markup Overrides

- You may override paragraphs templates by copying them into the client theme.
- You may override hooks by copying into client .theme, and modifying hook name/etc.
