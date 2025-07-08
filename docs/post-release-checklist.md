# Post-Release Checklist

This document outlines tasks to complete after the 11.0.5 release.

## Code Quality Improvements

- Address PHPDoc and coding standards issues as outlined in [code-quality-plan.md](code-quality-plan.md)
- Run automated code formatting with PHPCBF where possible
- Fix manual coding standard issues
- Continue improving error handling and logging

## Security Verification

- Verify all API interactions are using secure connections
- Ensure all form submissions are properly validated
- Check for any remaining instances of insecure configuration

## Cache Clearing Improvements

- Monitor the batch processing performance in production
- Consider further optimizations for sites with very large numbers of paragraphs
- Add more detailed logging for cache clearing operations

## Documentation

- Review and update all README.md files in submodules for consistency
- Update installation instructions if needed
- Consider creating a troubleshooting guide for common issues
- Document the batch processing feature for external content cache clearing

## Testing

- Expand automated test coverage
- Create a comprehensive test plan for manual testing
- Test all features with Drupal 10 and 11

## Feature Planning

- Review user feedback for feature requests
- Prioritize features for 11.1.0
- Update roadmap documentation

## Maintenance

- Review dependencies and update if necessary
- Clean up any unused code
- Optimize database queries and caching

## Deployment

- Create documentation for upgrading from earlier versions
- Test upgrade paths from 10.x and 11.0.x
