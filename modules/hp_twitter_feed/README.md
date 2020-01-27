# Howard Paragraphs Twitter Feed

Paragraphs integration of a twitter Feed for Howard projects:

This module contains markup only (no js or css), those should be provided in the client theme, loaded via the idfive Component Library:

 - [idfive Component Library](https://bitbucket.org/idfivellc/idfive-component-library)
 - [idfive Component Library D8 Theme](https://bitbucket.org/idfivellc/idfive-component-library-d8-theme)

# Composer
This module needs the following library, [abraham/twitteroauth](https://github.com/abraham/twitteroauth) added to the projects root composer file:
 - composer require abraham/twitteroauth

## Configuration
The following fields are available on this KS widget:
 - Count: The number of tweets wanted.
 - Username: the desired twitter user to pull tweets from. Defaults to "howardu".

This widget has a configuration page at admin/config/hp_twitter_feed/settings which takes credentials for the Twitter app accessing the feed. The fields required are:
- Consumer key
- Consumer Secret
- Access Token
- Access Token Secret

These credentials can be obtained from the Twitter Account Developer portal.


## Markup Overrides
- You may override paragraphs templates by copying them into the client theme.
- You may override hooks by copying into client .theme, and modifying hook name/etc.
