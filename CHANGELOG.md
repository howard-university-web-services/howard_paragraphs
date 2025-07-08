# Changelog

All notable changes to the Howard Paragraphs module will be documented in this file.

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
