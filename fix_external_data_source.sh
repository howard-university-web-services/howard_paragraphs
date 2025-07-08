#!/bin/bash

# Fix for ExternalDataSource plugins
declare -a files=(
    "/Users/danrogers/Sites/howard_paragraphs/modules/hp_news_feed/src/Plugin/ExternalDataSource/NewsCategories.php"
    "/Users/danrogers/Sites/howard_paragraphs/modules/hp_news_feed/src/Plugin/ExternalDataSource/NewsUnits.php"
    "/Users/danrogers/Sites/howard_paragraphs/modules/hp_news_feed/src/Plugin/ExternalDataSource/NewsHowardForward.php"
    "/Users/danrogers/Sites/howard_paragraphs/modules/hp_news_feed/src/Plugin/ExternalDataSource/NewsSchoolsColleges.php"
    "/Users/danrogers/Sites/howard_paragraphs/modules/hp_news_feed/src/Plugin/ExternalDataSource/NewsInitiatives.php"
    "/Users/danrogers/Sites/howard_paragraphs/modules/hp_announcements_feed/src/Plugin/ExternalDataSource/NewsAnnouncementCategory.php"
    "/Users/danrogers/Sites/howard_paragraphs/modules/hp_announcements_feed/src/Plugin/ExternalDataSource/NewsAnnouncementUnit.php"
    "/Users/danrogers/Sites/howard_paragraphs/modules/hp_profiles_feed/src/Plugin/ExternalDataSource/ProfilesDepartments.php"
    "/Users/danrogers/Sites/howard_paragraphs/modules/hp_giving_feed/src/Plugin/ExternalDataSource/GivingCategories.php"
    "/Users/danrogers/Sites/howard_paragraphs/modules/hp_magazine_feed/src/Plugin/ExternalDataSource/MagazineCategories.php"
    "/Users/danrogers/Sites/howard_paragraphs/modules/hp_alumni_feed/src/Plugin/ExternalDataSource/AlumniAdminCategories.php"
)

for file in "${files[@]}"; do
    echo "Processing $file"
    
    # 1. Fix use statement
    sed -i '' 's/use GuzzleHttp\\Exception as GuzzleException;/use GuzzleHttp\\Exception\\RequestException;/' "$file"
    
    # 2. Fix catch statement
    sed -i '' 's/catch (GuzzleException $e) {/catch (RequestException $e) {/' "$file"
    
    # 3. Fix HTTP client options
    # For NewsCategories.php specifically (already fixed)
    if [[ "$file" == *"NewsCategories.php" ]]; then
        continue
    fi
    
    # For all other files
    sed -i '' 's/$response = $client->get.*verify.*FALSE.*);/$response = $client->get('"'"'https:\/\/'"'"' . substr(strstr($response_url, "\/\/"), 2), [\n          '"'"'verify'"'"' => TRUE,\n          '"'"'timeout'"'"' => 30,\n          '"'"'connect_timeout'"'"' => 10,\n          '"'"'headers'"'"' => [\n            '"'"'Accept'"'"' => '"'"'application\/json'"'"',\n            '"'"'User-Agent'"'"' => '"'"'Howard Paragraphs Module\/1.0'"'"',\n          ],\n        ]);/' "$file"
done

echo "All ExternalDataSource plugins updated"
