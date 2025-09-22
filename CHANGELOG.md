# Changelog

All notable changes to the Howard Paragraphs module will be documented in this file.

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
