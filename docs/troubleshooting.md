# Troubleshooting Guide

This guide helps you diagnose and resolve common issues with Howard Paragraphs.

## Common Issues

### Installation & Configuration Issues

#### Module won't install
**Symptoms**: Error messages during installation, missing dependencies

**Solutions**:
1. Check PHP version compatibility (8.1+ required)
2. Install missing dependencies:
   ```bash
   composer install
   drush en paragraphs field_group entity_reference_revisions
   ```
3. Clear caches and retry:
   ```bash
   drush cache:rebuild
   drush en howard_paragraphs
   ```

#### Configuration import fails
**Symptoms**: Errors when enabling submodules, missing field configurations

**Solutions**:
1. Use partial configuration import:
   ```bash
   drush cim -y --partial --source=modules/contrib/howard_paragraphs/hp_cards/config/install/
   ```
2. Check for configuration conflicts:
   ```bash
   drush config:status
   ```
3. Clear caches before import:
   ```bash
   drush cache:rebuild
   drush cim
   ```

### Display Issues

#### Components not rendering
**Symptoms**: Blank spaces where components should appear, no output

**Debugging steps**:
1. Check if component is enabled:
   ```bash
   drush pml | grep hp_
   ```
2. Verify paragraph type is allowed in field settings
3. Check view mode configuration:
   - Go to Structure > Paragraph types
   - Select your component
   - Check "Manage display"
4. Clear caches:
   ```bash
   drush cache:rebuild
   ```

#### Styling issues
**Symptoms**: Components display but look broken, missing CSS

**Solutions**:
1. Verify idfive Component Library is loaded
2. Check browser developer tools for CSS errors
3. Ensure theme has proper library attachments:
   ```yaml
   # your_theme.libraries.yml
   global-styling:
     css:
       theme:
         css/style.css: {}
     dependencies:
       - idfive_component_library/global
   ```

### External Content Issues

#### External feeds not loading
**Symptoms**: Empty feed components, "No content available" messages

**Debugging steps**:
1. Check network connectivity:
   ```bash
   curl -I https://thedig.howard.edu/api/health
   ```
2. Verify API endpoints in settings:
   ```php
   // Check in settings.php
   $config['howard_paragraphs.settings']['news_api_endpoint']
   ```
3. Clear external content cache:
   - Visit `/admin/config/clear_howard_external_content`
   - Click "Clear Cache"
4. Check error logs:
   ```bash
   drush watchdog:show --type=howard_paragraphs
   ```

#### Stale external content
**Symptoms**: Old content displaying, not updating

**Solutions**:
1. Check cron is running:
   ```bash
   drush core:status
   ```
2. Run cron manually:
   ```bash
   drush core:cron
   ```
3. Adjust cache TTL in settings:
   ```php
   $config['howard_paragraphs.settings']['cache_ttl'] = 1800; // 30 minutes
   ```

### Performance Issues

#### Slow page loads
**Symptoms**: Long loading times, timeouts

**Optimization steps**:
1. Enable page caching:
   ```php
   $config['system.performance']['cache']['page']['max_age'] = 3600;
   ```
2. Optimize images:
   - Configure image styles
   - Enable lazy loading
3. Monitor external API calls:
   ```bash
   drush watchdog:show --type=howard_paragraphs --severity=info
   ```
4. Consider CDN for static assets

#### Memory issues
**Symptoms**: PHP memory errors, server timeouts

**Solutions**:
1. Increase PHP memory limit:
   ```php
   ini_set('memory_limit', '256M');
   ```
2. Optimize database queries
3. Reduce number of items in feeds
4. Use pagination for large datasets

## Debugging Tools

### Drupal Development Modules

Enable helpful development modules:
```bash
drush en devel webprofiler stage_file_proxy
```

### Browser Developer Tools

Use browser tools to diagnose frontend issues:
1. **Console**: Check for JavaScript errors
2. **Network**: Monitor API requests and loading times
3. **Elements**: Inspect HTML structure and CSS
4. **Performance**: Profile page loading

### Command Line Tools

#### Drush Commands
```bash
# Clear all caches
drush cache:rebuild

# Check module status
drush pml | grep howard

# View recent log entries
drush watchdog:show --count=50

# Check configuration status
drush config:status

# Export configuration
drush config:export

# Check database updates
drush updatedb:status
```

#### Useful Debugging Commands
```bash
# Check PHP configuration
php -m | grep -E "(curl|json|gd|xml)"

# Test API connectivity
curl -v https://thedig.howard.edu/api/articles

# Check file permissions
ls -la web/sites/default/files/

# Monitor real-time logs
tail -f /var/log/apache2/error.log
```

### Custom Debug Code

Add temporary debugging code:

```php
<?php
// In module file or preprocess function
if (\Drupal::currentUser()->hasPermission('access devel information')) {
  // Debug variables
  dpm($variables);
  
  // Log debug information
  \Drupal::logger('debug')->debug('Debug info: @data', [
    '@data' => print_r($data, TRUE),
  ]);
  
  // Check service availability
  $service = \Drupal::service('howard.news');
  dpm($service);
}
```

## Error Messages & Solutions

### Common Error Messages

#### "Class not found" errors
```
Error: Class 'Drupal\howard_paragraphs\Services\HowardNewsService' not found
```
**Solution**: Clear caches and rebuild autoloader:
```bash
drush cache:rebuild
composer dump-autoload
```

#### "Configuration entity dependency" errors
```
Configuration entity dependency error for field.field.paragraph.hp_cards.field_title
```
**Solution**: Install dependencies first:
```bash
drush en field entity_reference_revisions
drush cim
```

#### "Call to undefined method" errors
```
Call to undefined method on external content service
```
**Solution**: Check service definitions and clear caches:
```bash
drush cache:rebuild
drush config:export
```

### HTTP Error Codes

#### 500 Internal Server Error
**Causes**: PHP errors, memory issues, configuration problems

**Debugging**:
1. Check error logs
2. Enable error display temporarily:
   ```php
   error_reporting(E_ALL);
   ini_set('display_errors', TRUE);
   ```
3. Check .htaccess file

#### 403 Forbidden
**Causes**: Permission issues, missing access controls

**Solutions**:
1. Check user permissions
2. Verify file/directory permissions
3. Review access control configuration

#### 404 Not Found
**Causes**: Missing routes, incorrect URLs

**Solutions**:
1. Clear routing cache:
   ```bash
   drush cache:clear router
   ```
2. Check routing configuration
3. Verify URL patterns

## Environment-Specific Issues

### Development Environment

#### External APIs not accessible
**Solution**: Configure local API endpoints:
```php
// settings.local.php
$config['howard_paragraphs.settings']['news_api_endpoint'] = 'https://dev.thedig.howard.edu/api';
```

#### File permission issues
**Solution**: Set proper permissions:
```bash
chmod -R 755 web/sites/default/files/
chown -R www-data:www-data web/sites/default/files/
```

### Staging Environment

#### Configuration sync issues
**Solution**: Use environment-specific configuration:
```php
// settings.staging.php
$config['howard_paragraphs.settings']['cache_ttl'] = 1800;
```

#### SSL certificate issues
**Solution**: Configure proper SSL handling:
```php
$config['http_client_config']['verify'] = '/path/to/cacert.pem';
```

### Production Environment

#### Performance monitoring
**Tools**:
- New Relic or similar APM
- Database query monitoring
- Cache hit rate monitoring

#### Error handling
**Configuration**:
```php
// Disable error display
error_reporting(0);
ini_set('display_errors', FALSE);

// Log errors appropriately
ini_set('log_errors', TRUE);
ini_set('error_log', '/var/log/php_errors.log');
```

## Getting Additional Help

### When to Seek Help

Seek additional help when:
1. Following this guide doesn't resolve the issue
2. You encounter errors not covered here
3. The issue affects production systems
4. You need assistance with custom development

### How to Report Issues

When reporting issues:

1. **Provide Environment Details**:
   - Drupal version
   - PHP version
   - Howard Paragraphs version
   - Server configuration

2. **Include Error Information**:
   - Complete error messages
   - Log entries
   - Steps to reproduce

3. **Add Debugging Information**:
   - Browser console output
   - Network requests
   - Configuration export

### Resources

- **GitHub Issues**: https://github.com/howard-university-web-services/howard_paragraphs/issues
- **Drupal.org Documentation**: https://www.drupal.org/docs
- **Drupal Community**: https://www.drupal.org/slack

### Emergency Procedures

For critical production issues:

1. **Immediate Steps**:
   - Take site offline if necessary
   - Backup current state
   - Check error logs

2. **Rollback Procedure**:
   ```bash
   # Restore from backup
   drush sql:drop
   drush sql:cli < backup.sql
   
   # Restore files
   rsync -av backup/files/ web/sites/default/files/
   
   # Clear caches
   drush cache:rebuild
   ```

3. **Communication**:
   - Notify stakeholders
   - Document the issue
   - Plan resolution timeline

Remember: when in doubt, make a backup before attempting any fixes, and test solutions in a non-production environment first.
