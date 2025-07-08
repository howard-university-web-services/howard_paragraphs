# HTTP Client Security Fix Implementation

## Issue
Multiple HTTP clients have SSL verification disabled with `['verify' => FALSE]`, which makes these connections vulnerable to man-in-the-middle attacks.

## Fix Approach
For each file that uses HTTP clients with SSL verification disabled:

1. Replace the HTTP client options to:
   ```php
   [
     'verify' => TRUE,
     'timeout' => 30,
     'connect_timeout' => 10,
     'headers' => [
       'Accept' => 'application/json',
       'User-Agent' => 'Howard Paragraphs Module/1.0',
     ],
   ]
   ```

2. For Service classes:
   - These already use Drupal's dependency injection with `@http_client` service
   - Replace the `verify => FALSE` with our secure options
   - Ensure proper exception handling with `GuzzleHttp\Exception\RequestException`

3. For ExternalDataSource plugins:
   - These create their own Client instance: `$client = new Client();`
   - Replace the `verify => FALSE` with our secure options
   - Ensure proper exception handling with `GuzzleHttp\Exception\RequestException` (not `GuzzleException`)
   - Update the import statement if needed

4. Additional security improvements:
   - Add validation for URL parameters
   - Add proper error handling and logging
   - Add JSON validation after decoding
   - Add improved error messages for users

## Files to Fix

### Service Classes
- `/Users/danrogers/Sites/howard_paragraphs/src/Services/HowardNewsService.php`
- `/Users/danrogers/Sites/howard_paragraphs/src/Services/HowardProfilesService.php`
- `/Users/danrogers/Sites/howard_paragraphs/src/Services/HowardGivingService.php`
- `/Users/danrogers/Sites/howard_paragraphs/modules/hp_youtube_playlist/src/Services/HowardYoutubeService.php`

### ExternalDataSource Plugins
- `/Users/danrogers/Sites/howard_paragraphs/modules/hp_news_feed/src/Plugin/ExternalDataSource/NewsCategories.php`
- `/Users/danrogers/Sites/howard_paragraphs/modules/hp_news_feed/src/Plugin/ExternalDataSource/NewsUnits.php`
- `/Users/danrogers/Sites/howard_paragraphs/modules/hp_news_feed/src/Plugin/ExternalDataSource/NewsHowardForward.php`
- `/Users/danrogers/Sites/howard_paragraphs/modules/hp_news_feed/src/Plugin/ExternalDataSource/NewsSchoolsColleges.php`
- `/Users/danrogers/Sites/howard_paragraphs/modules/hp_news_feed/src/Plugin/ExternalDataSource/NewsInitiatives.php`
- `/Users/danrogers/Sites/howard_paragraphs/modules/hp_announcements_feed/src/Plugin/ExternalDataSource/NewsAnnouncementCategory.php`
- `/Users/danrogers/Sites/howard_paragraphs/modules/hp_announcements_feed/src/Plugin/ExternalDataSource/NewsAnnouncementUnit.php`
- `/Users/danrogers/Sites/howard_paragraphs/modules/hp_profiles_feed/src/Plugin/ExternalDataSource/ProfilesDepartments.php`
- `/Users/danrogers/Sites/howard_paragraphs/modules/hp_giving_feed/src/Plugin/ExternalDataSource/GivingCategories.php`
- `/Users/danrogers/Sites/howard_paragraphs/modules/hp_magazine_feed/src/Plugin/ExternalDataSource/MagazineCategories.php`
- `/Users/danrogers/Sites/howard_paragraphs/modules/hp_alumni_feed/src/Plugin/ExternalDataSource/AlumniAdminCategories.php`
