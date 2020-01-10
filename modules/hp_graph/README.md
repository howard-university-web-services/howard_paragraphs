# Howard Paragraphs Graph

Provides a paragraphs bundle for adding Bar Graphs as a Kitchen Sink Widget.
This module contains markup only (no js or css), those should be provided in the client theme, loaded via the idfive Component Library:

- [idfive Component Library](https://bitbucket.org/idfivellc/idfive-component-library)
- [idfive Component Library D8 Theme](https://bitbucket.org/idfivellc/idfive-component-library-d8-theme)

## Configuration

The following fields are available on this KS widget:

- Title: Sets the graph's title.
- Description: Adds small descriptive text below the graph's title.
- HP Graph Field: Paragraph bundle contains a singular percentage and label.
    - Percent: Add a percentage to a single bar for the graph.
    - Label: Adds a label to an individual graph's bar.
- Link URL: Add a URL to the graph module's hyperlink.
- Link Text: Adds a descriptive text to the graph module's hyperlink.

## Markup Overrides

- You may override paragraphs templates by copying them into the client theme.
- You may override hooks by copying into client. theme, and modifying hook name/etc.
