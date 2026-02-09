# Changelog

All notable changes to the Howard Paragraphs module will be documented in this file.

## [11.1.6] - 2026-02-09

### Improved

- **HP Featured Article**: Cleaned up template structure by removing unnecessary nested `section-content` wrapper div and improved indentation formatting

## [11.1.5] - 2026-01-27

### Improved

- **Template Code Quality**: Standardized indentation and formatting across multiple paragraph templates
- **Button Styling**: Enhanced button markup with proper `btn__text` spans and arrow icons for consistency
- **HP Featured Article**: Removed unnecessary `block-header--slim` class
- **HP Graph**: Improved template formatting and button structure
- **HP Parallax**: Fixed indentation and updated button markup

## [11.1.4] - 2026-01-15

### Added

- **HP Profiles Feed By ID**: New paragraph module for displaying manually selected profiles from profiles.howard.edu by specific profile IDs
  - Includes configuration fields for title and selected profiles
  - Admin-only functionality for manual profile selection
  - Template markup provided without CSS/JS (to be styled in client theme)
  - Enhanced HowardProfilesService to support ID-based profile retrieval

## [11.1.3] - 2026-01-13

### Fixed

- **HP Timeline**: Fixed syntax error in timeline widget template

## [11.1.2] - 2025-12-11

### Schema.org Validation Fixes

- **Schema.org Validation**: Removed inappropriate `publisher` properties from `ItemList`, `Table`, `WebPageElement`, and `Thing` schema types
- **URL References**: Fixed HP Card URLs to use proper rendered URLs instead of entity references
- **Schema Type Accuracy**: Changed HP Card from `Thing` to `WebPageElement` for general cards

### New Schema.org Implementations

- **HP Countdown** (`paragraph--hp-countdown`): Added `Event` schema for countdown timers with event dates
- **HP Table** (`paragraph--ip-table`): Added `Table` schema for structured data presentation

### Schema.org Compliance Updates

- **HP Accordion**: Removed `publisher` from `ItemList` schema
- **HP Callout**: Removed `publisher` from `WebPageElement`, replaced with `about` property
- **HP Card**: Fixed URL rendering and changed `Thing` to `WebPageElement` for generic cards
- **HP Carousel with Caption**: Removed `publisher` from `ItemList` schema
- **HP Carousel with Modal**: Removed `publisher` from `ItemList` schema
- **HP Data Point**: Fixed invalid `Statistic` type to valid `QuantitativeValue` type
- **HP Program**: Removed invalid `alternativeName` property from `EducationalOccupationalProgram`
- **HP Table**: Removed `publisher` from `Table`, replaced with `about` property
- **HP Timeline**: Removed `publisher` from `ItemList` schema, removed invalid `position` property from `Event` objects
- **HP Video Slideshow**: Removed `publisher` from `ItemList` schema
- **HP YouTube Playlist**: Removed `publisher` from `ItemList` schema

### Release Statistics

- **Total Enhanced**: 28 modules now include Schema.org markup (up from 26)
- **Coverage**: 72% of all paragraph modules (28 out of 39)
- **Schema Types**: 15 different Schema.org types implemented
- **Validation**: All implementations now pass Schema.org validator requirements

## [11.1.1] - 2025-12-05

### Fixed
- **Schema.org Data Quality**: Improved JSON encoding and field access patterns across all templates
- **String Safety**: Enhanced string cleaning with proper quote escaping and newline removal  
- **Field Validation**: Added proper conditional rendering to prevent malformed JSON
- **Educational Context**: Standardized Howard University as `EducationalOrganization` across all modules

### Enhanced - 12 Paragraph Modules
- **HP Articles** (`views-view-fields--hp-articles-ks-feed`): Enhanced article schema with safe JSON encoding
- **HP Card** (`paragraph--hp-card`): Improved schema relationships and string safety
- **HP Carousel with Caption** (`paragraph--hp-carousel-with-caption`): Better image metadata handling  
- **HP Carousel with Modal** (`paragraph--hp-carousel-with-modal`): Enhanced modal gallery schema
- **HP Data Point** (`paragraph--hp-data-point`): Improved statistic schema with URL validation
- **HP Giving Feed** (`paragraph--hp-giving-feed`): Enhanced article schema for fundraising content
- **HP Graph** (`paragraph--hp-graph`): Better dataset schema with proper field validation
- **HP Program** (`paragraph--hp-program`): Comprehensive educational program schema improvements
- **HP Promo Space** (`paragraph--hp-promo-space`): Enhanced creative work schema
- **HP Promo Space Multi Image** (`paragraph--hp-promo-space-multi-img`): Complex multi-image schema arrays  
- **HP Timeline** (`paragraph--hp-timeline`): Enhanced event-based timeline schema
- **HP YouTube Playlist** (`paragraph--hp-youtube-playlist`): Comprehensive video playlist schema

### Technical
- **JSON Safety**: All fields now use `|json_encode|raw` for proper escaping
- **String Cleaning**: Added `|replace({'"': '\\"', "\n": " ", "\r": " "})` for safe JSON output
- **Error Prevention**: Eliminated potential malformed JSON from field values
- **SEO Enhancement**: Better structured data quality for search engines

## [11.1.0] - 2025-12-05

### Enhanced - Schema.org Implementation

#### New Schema.org Implementations (6 modules)

- **HP Carousel with Caption**: Added `ItemList` with `ImageObject` items for enhanced image carousels
- **HP Carousel with Modal**: Added `ItemList` with `ImageObject` items for modal image collections  
- **HP Video Slideshow**: Added `ItemList` with `VideoObject` items for video content
- **HP Timeline**: Added `ItemList` with `Event` objects for chronological content
- **HP Accordion**: Added `ItemList` with `WebPageElement` objects for collapsible content
- **HP Cards**: Added dynamic schema (`Person`/`Organization`/`Thing`) based on card type
- **HP Callout**: Added `WebPageElement` with `Action` objects for call-to-action content

#### Enhanced Existing Schema.org (4 modules)

- **HP Media**: Enhanced with proper media entity access, content URLs, and safe JSON encoding
- **HP Testimonial**: Enhanced with safe JSON encoding and proper author affiliation
- **HP Featured Article**: Enhanced with conditional field rendering and complete publisher URLs
- **HP News Feed**: Enhanced all layout types with improved field validation and safety

### Technical Improvements

- **Safe JSON Encoding**: All text fields use `json_encode|raw` to prevent syntax errors
- **Conditional Rendering**: All schema fields check for existence before output
- **Educational Context**: All modules reference Howard University as `EducationalOrganization`
- **Media Entity Support**: Enhanced media field access patterns for rich content URLs
- **Cross-template Consistency**: Standardized schema patterns across all templates

### SEO Benefits

- **Rich Snippets**: Enabled image carousels, video thumbnails, event timelines, person profiles
- **Enhanced Discovery**: Better content categorization and relationship understanding
- **Technical SEO**: Structured data validation compliance, no JSON syntax errors

### Documentation

- Added comprehensive Schema.org implementation documentation (`docs/schema-org-implementation.md`)
- Documented field access patterns and best practices
- Added testing and validation guidelines
- Included maintenance guidelines for future development
- Updated index and development guides with Schema.org references

## [11.0.19] - 2025-11-25

### Fixed

- Fixed target attribute access in hp_button_link template for button-type links
- Changed from `content.field_ip_bl_link[key]['#options'].attributes.target` to `content.field_ip_bl_link[key]['#url'].getOption('attributes')['target']` for proper URL option handling

### Technical Details

- Updated paragraph--ip-button-link.html.twig to use correct Drupal URL API method for accessing link options
- Ensures consistent behavior when links are configured to open in new windows/tabs

## [11.0.18] - 2025-10-29

### Changed

- Removed unnecessary CSS class 'ks_article_img' from hp_articles template
- Cleaned up template markup for better consistency and maintainability

### Fixed

- Updated README.md version number to reflect current version
- Improved template consistency across article feed displays

## [11.0.17] - 2025-10-23

### Fixed

- Fixed version number in howard_paragraphs.info.yml

## [11.0.16] - 2025-10-23

### Fixed

- Fixed Twig error in hp_data_point module caused by missing `|render` filter before `|striptags` in Schema.org structured data markup
- Corrected field value processing in data point template to ensure proper JSON-LD output

### Technical Details

- Added `|render` filter before `|striptags` in paragraph--hp-data-point.html.twig template for Schema.org fields
- Ensures field values are properly rendered before string processing for structured data
- Prevents Twig filter chain errors when processing field content

## [11.0.15] - 2025-10-09

### Fixed

- Fixed Schema.org structured data field value access in Twig templates
- Corrected `hp_featured_article` module preprocess function to return raw text instead of render arrays for summary fields
- Fixed field value access in `hp_media` templates to use `paragraph.field_name.value` instead of `content.field_name` for Schema.org markup
- Fixed field value access in `hp_programs_feed` template for proper Schema.org structured data output
- Improved consistency of field value handling across all paragraph templates with Schema.org markup

### Technical Details

- Changed summary field output in `hp_featured_article_preprocess_paragraph__hp_featured_article()` from render arrays to plain text
- Updated Schema.org JSON-LD markup in media, programs feed, and other templates to access field values correctly
- Ensures proper Schema.org validation and prevents HTML markup in structured data

## [11.0.14] - 2025-09-23

### Added

- Comprehensive Schema.org structured data markup across all major paragraph templates
- Schema.org Person markup for profiles, testimonials, alumni feeds, and featured alumni
- Schema.org Article markup for featured articles, giving feed, and magazine feed
- Schema.org NewsArticle markup for news feeds and announcements feeds
- Schema.org Event markup for announcements and announcement feeds
- Schema.org Statistic markup for data point paragraphs
- Schema.org VideoObject/AudioObject/MediaObject markup for media paragraphs
- Schema.org EducationalOccupationalProgram markup for program paragraphs
- Schema.org WebApplication markup for programs feed (program finder tool)
- Schema.org Quotation markup for testimonial paragraphs
- Enhanced SEO capabilities through structured data for search engine optimization
- Rich snippet potential for better search result display

### Changed

- All major paragraph templates now include appropriate Schema.org JSON-LD structured data
- Improved semantic markup consistency across educational content types
- Enhanced content discoverability through structured data implementation

### Technical Details

- Added Schema.org markup to 14+ paragraph templates including profiles, news, articles, media, programs, testimonials, and data points
- Used appropriate schema types based on content: Person for individuals, Article/NewsArticle for content, Event for announcements, etc.
- Implemented conditional field inclusion to prevent empty schema properties
- Used Howard University as the consistent organization/publisher entity across all markup

## [11.0.13] - 2025-09-22

### Added

- Administrative taxonomy filtering field for HP Profiles Feed module
- New external data source plugin (ProfilesAdmin) to fetch administrative classifications from profiles.howard.edu
- Field storage and configuration for field_hp_pf_admin_taxonomy
- Support for filtering profiles by administrative taxonomy in HowardProfilesService
- Update hook hp_profiles_feed_update_8006() to install new field configuration

### Changed

- Enhanced getProfiles() method in HowardProfilesService to accept admin_taxonomy parameter
- Updated hp_profiles_feed preprocessing to handle administrative taxonomy filtering
- Improved profiles feed flexibility with additional filtering options

### Fixed

- Corrected module file header comment in hp_profiles_feed.module (was showing "Ip_button_link" instead of "HP Profiles Feed")
- Added proper parameter handling for administrative taxonomy filtering in profile queries

## [11.0.12] - 2025-08-28

### Fixed

- Fixed card link target attribute access in hp_cards template
- Updated paragraph--hp-card.html.twig to properly access target attribute from URL options
- Resolved issue where card link targets were not being applied correctly

## [11.0.11] - 2025-08-20

### Added

- External source field filtering for article feeds in HowardNewsService
- Automatic exclusion of articles marked with field_article_external_source boolean field
- JSON:API filtering integration to exclude external content from article feeds

### Changed

- Enhanced getArticles() method with external source filtering capability
- Improved service methods for better content filtering and data integrity
- Updated documentation and maintenance for release preparation

### Fixed

- Resolved article feed filtering to properly exclude external source content
- Improved API query construction for better content control

## [11.0.10] - 2025-07-17

### Fixed

- Fixed promo space template to conditionally render heading only when title field has content
- Improved template output by preventing empty heading tags in promo space paragraphs

### Changed

- Enhanced hp_promo_space template with conditional title rendering
- Better content structure for promo space components

## [11.0.9] - 2025-07-17

### Fixed

- Fixed carousel with caption template to conditionally render heading only when title field has content
- Improved template output by preventing empty heading tags in carousel slides

### Changed

- Enhanced hp_carousel_with_caption_slide template with conditional title rendering
- Better content structure for carousel components

## [11.0.8] - 2025-07-16

### Changed

- Updated drupal/tablefield dependency requirement from ^2.3 to ^3.0
- Improved compatibility with latest tablefield module version
- Enhanced module dependency management for better stability

### Added

- Support for tablefield module 3.x with improved features and Drupal 11 compatibility

## [11.0.7] - 2025-07-08

### Changed

- Updated composer.json and info file to properly reflect this is a custom module served by Packagist
- Clarified that this module is not distributed through Drupal.org
- Updated documentation to emphasize custom module distribution

### Added

- Enhanced documentation about custom module distribution via Packagist
- Better support documentation in composer.json

## [11.0.6] - 2025-07-08

### Changed

- Updated external_data_source dependency from ^2.0 to ^3.2 for Drupal 11 compatibility
- Improved dependency management for better security and performance

### Added

- Support for external_data_source 3.2.x with enhanced features and Drupal 11 compatibility

## [11.0.5] - 2025-07-08

### Fixed

- Fixed database connection serialization issue in cache clearing controller
- Improved error handling in batch processing
- Enhanced user interface for the cache clearing page
- Fixed property name capitalization in IdfiveParagraphsTablefieldFormatter
- Updated template variables for external content cache clearing

### Known Issues

- Some PHPDoc comments need improvements to fully comply with Drupal coding standards (planned for 11.0.6)

## [11.0.4] - 2025-07-08

### Security

- Fixed all instances of disabled SSL verification in HTTP clients
- Enhanced Form API security for sensitive API credentials
- Improved error handling to prevent information disclosure
- Added proper configuration schema validation for module settings

### Added

- Comprehensive security documentation and best practices
- Improved error logging and debugging capabilities
- Support for environment variables for sensitive configuration
- New performance optimization documentation and techniques

### Changed

- Updated service classes to use modern Drupal dependency injection patterns
- Refactored external data source plugins for better security
- Improved HTTP client configuration with proper timeouts and headers
- Enhanced cache clearing with batch processing for large sites

### Fixed

- Resolved potential man-in-the-middle vulnerabilities in API connections
- Fixed password field types for sensitive configuration
- Improved input validation and sanitization
- Optimized database queries for better performance
- Addressed some code style issues with automated PHPCS fixes

### Known Issues

- Some PHPDoc comments need improvements to fully comply with Drupal coding standards (planned for 11.0.5)

## [2.0.0] - 2025-02-15

### Added

- Drupal 10 compatibility
- New paragraph types for enhanced content display
- Improved caching system for external content

### Changed

- Updated module structure to support Drupal 10
- Improved templating system

### Removed

- Legacy code for Drupal 8 compatibility

## [1.0.0] - 2024-09-01

### Added

- Initial release with basic paragraph types
- External content integration
- Support for Howard University branding
