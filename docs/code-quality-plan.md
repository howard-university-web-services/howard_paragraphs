# Code Quality Improvements for 11.0.5

This document outlines the code quality improvements needed for the next release (11.0.5) to fully comply with Drupal coding standards.

## PHPDoc Improvements

Many files need improved PHPDoc comments to meet Drupal coding standards. Most issues fall into these categories:

1. **Missing or incomplete method documentation**
   - Missing short descriptions
   - Missing or improperly formatted @return value descriptions

2. **Property documentation**
   - Missing member variable documentation
   - Improper property naming (e.g., using snake_case instead of camelCase)

3. **Line length issues**
   - Lines exceeding 80 characters

## Affected Files

### ExternalDataSource Plugins

All ExternalDataSource plugins have similar documentation issues:

- Missing short descriptions in doc comments
- Missing or improperly formatted @return descriptions
- Doc comment short descriptions not properly formatted

### Service Classes

Service classes need improved documentation:

- HowardNewsService
- HowardGivingService
- HowardProfilesService
- HowardYoutubeService

### Filter Plugins

- FilterUL needs improved documentation and use implode() instead of join()

### Controllers

- HowardExternalContentCacheClear has improperly formatted @deprecated tag

## Action Plan

1. Create a standardized PHPDoc template for ExternalDataSource plugins
2. Improve service class documentation with consistent property documentation
3. Fix line length issues by breaking long lines
4. Replace deprecated function aliases (join() -> implode())
5. Correct all @deprecated tags to use proper format
