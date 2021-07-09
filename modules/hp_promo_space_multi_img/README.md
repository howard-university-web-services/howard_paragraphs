# Howard Paragraphs Promotional Space with Multiple Images

Paragraphs integration of a Promotional Space with Multiple Images for use in Howard projects:

This module contains no style releated markup, structual only (no js or css), those should be provided in the client theme, loaded via the idfive Component Library:

 - [idfive Component Library](https://bitbucket.org/idfivellc/idfive-component-library)
 - [idfive Component Library D8 Theme](https://bitbucket.org/idfivellc/idfive-component-library-d8-theme)

## Markup Overrides

- You may override paragraphs templates by copying them into the client theme.
- You may override hooks by copying into client .theme, and modifying hook name/etc.

## Module Questionnaire

**Use Case Questions**

What specific use case does this module fulfill?
- The objective of this widget is to allow multiple images and descriptions to be viewed simultaneously. With a department such as FA, they may want to share multiple images when promoting a gallery or performance.

Are there existing modules that also fit this use case?
- Yes, the hp_promo_space module fit the use case from a content perspective, however, this module extends that functionality to include more of an emphasis on the image (large percentage of the space) and allow more than one image.

If so, what are the reasons someone would choose this module over others?
- To be able to incorporate more individual images without using a slideshow.

In broad strokes, what does this module do?
- Provided a means to display multiple images together with individual sub content and a section for primary content and buttons.

How do you envision sites in the future will utilize this?  (Note: This is also stellar content to put into the readme file.)
- Site will use this module to show gallery style promotional content.

Within the context of D8 templates, where should this be available? (homepage, KS page, etc.)
- Homepage
- KS page

What types of test content should we populate, both to test interaction and to provide as a default to the starter site?
- Test content should include HTTP content in the body of the promotional space, and up to six images with sub HTML content.

**Integration Questions**

Add this to default content types as a KS widget.
- Only if desired to be used on other sites outside of FA.
   - KZ: Yes.

Update all sites in the ecosystem to be able to use this.
- No

Document its use on sitebuilding.howard.edu
- No
   - KZ: Yes.

Add example content on sitebuilding.howard.edu
- No
   - KZ: Yes. (We can wait to see its use in Fine Arts site, first.

CSS is contained in the module rather than the theme. This is technically fine, but it means variables/etc that match the rest of the theme cannot be used and potentially will be missed when future changes happen.
- This is only structural and animations, there are no conflicts with theme colors.
- The primary content section will still be themed by the Howard theme CSS.

Has this been accounted for in the 3 different theme variations?
- Yes

**Performance and Accessibility**

Has any thought been given to deeper accessibility for these.
- The same formatting will be used from the hp_promo_space module. However there could be an issue depending the side of the text provided for sub content per image
   - KZ: Let's please test for accessibility, Antonio.
   - AM: Decided to remove the overlay text to ensure there are no usage/accessibility issues.

Performance-wise, are there any concerns with the resource load times?
- The images are going to be stored locally on the website, we many want to enforce a image size limitation to ensure there are no loading issues.
   - KZ: Agreed.
