# Howard Paragraphs Countdown

Paragraphs integration of the ability to add a countdown widget to Howard projects. 

This module contains markup only (no js or css), those should be provided in the client theme, loaded via the idfive Component Library:

 - [idfive Component Library](https://bitbucket.org/idfivellc/idfive-component-library)
 - [idfive Component Library D8 Theme](https://bitbucket.org/idfivellc/idfive-component-library-d8-theme)

 ## Configuration
The following fields are available on this KS widget:
 - Image: Optional.
 - Date: That date and time at which the component is counting down to.
 - Title: Text above the countdown
 - Link: Optional, Can link to relevant internal or external page.

## Markup Overrides
- You may override paragraphs templates by copying them into the client theme.
- You may override hooks by copying into client .theme, and modifying hook name/etc.
