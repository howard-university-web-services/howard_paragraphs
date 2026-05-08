# Installation & Configuration

This guide covers the complete installation and configuration process for Howard Paragraphs.

## System Requirements

### Drupal Requirements
- **Drupal Core**: 10.x or 11.x
- **PHP**: 8.1 or higher (8.2+ recommended for Drupal 11)
- **Database**: MySQL 5.7+, PostgreSQL 10+, or SQLite 3.26+

### Required Modules
The following modules are automatically installed as dependencies:

- `entity_reference_revisions`
- `field`
- `field_group`
- `file`
- `filter`
- `image`
- `link`
- `options`
- `paragraphs`
- `system`
- `text`
- `user`
- `focal_point`

### Additional Dependencies
Some submodules require additional modules:

- `external_data_source`: For external content feeds
- `tablefield`: For table components  
- `viewsreference`: For views embed components
- `webform`: For webform embed components

**Note for Drupal 11**: Ensure all contrib dependencies support Drupal 11 before upgrading. Check module compatibility at drupal.org.

## Installation Methods

### Method 1: Composer (Recommended)

1. **Add the package to your project**:
   ```bash
   composer require howard/howard_paragraphs
   ```

2. **Enable the base module**:
   ```bash
   drush en howard_paragraphs
   ```

3. **Enable desired submodules**:
   ```bash
   drush en hp_cards hp_news_feed hp_button_link hp_callout
   ```

4. **(Recommended) Enable the permissions submodule** to enforce standard Howard role permissions:
   ```bash
   drush en howard_permissions -y && drush cr
   ```
   The install hook automatically creates the `site_admin` and `site_builder` roles if absent and applies all permissions from `permissions_roles.json`. No further steps are required.

### Method 2: Manual Installation

1. **Download the module**:
   - Download from [GitHub releases](https://github.com/howard-university-web-services/howard_paragraphs/releases)
   - Extract to `modules/contrib/howard_paragraphs`

2. **Install dependencies**:
   ```bash
   composer install
   ```

3. **Enable modules**:
   ```bash
   drush en howard_paragraphs
   ```

## Configuration Setup

### Step 1: Configure Paragraph Fields

Add paragraph fields to your content types:

1. **Navigate to Content Types**:
   - Go to `Structure > Content Types`
   - Select your content type (e.g., "Page")
   - Click "Manage fields"

2. **Add Paragraph Field**:
   - Click "Add field"
   - Choose "Entity reference revisions"
   - Label: "Content Sections" (or similar)
   - Machine name: `field_content_sections`

3. **Configure Field Settings**:
   - **Type of item to reference**: Paragraphs
   - **Allowed number of values**: Unlimited
   - **Reference method**: Default

4. **Configure Field Instance**:
   - **Available Paragraph Types**: Select desired components
   - **Default paragraph type**: Leave empty or set default
   - **Allow users to add above the active paragraph**: Check if desired

### Step 2: Configure Display Settings

Configure how paragraphs appear:

1. **Manage Display**:
   - Go to "Manage display" for your content type
   - Find your paragraph field

2. **Set Display Format**:
   - **Format**: Rendered entity
   - **View mode**: Default
   - **Link to entity**: No

3. **Configure View Modes** (optional):
   - Create custom view modes for different display contexts
   - Configure each view mode separately

### Step 3: Configure Form Display

Optimize the editorial experience:

1. **Manage Form Display**:
   - Go to "Manage form display"
   - Find your paragraph field

2. **Configure Widget**:
   - **Widget**: Paragraphs Classic (or Experimental)
   - **Edit mode**: Open
   - **Autocollapse**: All

3. **Set Field Order**:
   - Drag field to desired position
   - Usually works best near the top or bottom

## External Content Configuration

### Environment Settings

Configure external content sources:

1. **Visit Configuration Page**:
   - Go to `/admin/config/clear_howard_external_content`
   - Review available external content sources

2. **Set API Endpoints**:
   - Most feeds default to production endpoints
   - Override in settings.php for non-production environments:
   ```php
   $config['howard_paragraphs.settings']['news_api_endpoint'] = 'https://dev.thedig.howard.edu/api';
   ```

### Cron Configuration

Set up automated cache clearing:

1. **Enable Cron**:
   - Ensure Drupal cron is configured
   - Recommended: Run every 3 hours

2. **Acquia Scheduled Jobs** (if using Acquia):
   ```bash
   bash /var/www/html/${AH_SITE_NAME}/scripts/hal_sites.sh core-cron
   ```

3. **Manual Cache Clearing**:
   - Visit `/admin/config/clear_howard_external_content`
   - Click "Clear Cache" button

## Performance Optimization

### Caching Configuration

1. **Enable Render Caching**:
   ```php
   $config['system.performance']['cache']['page']['max_age'] = 3600;
   ```

2. **Configure External Content Cache**:
   - External feeds cached for 3 hours by default
   - Override in settings.php:
   ```php
   $config['howard_paragraphs.settings']['cache_ttl'] = 10800; // 3 hours
   ```

### Database Optimization

1. **Enable Database Caching**:
   ```php
   $config['system.performance']['cache']['bins']['data'] = 'cache.backend.database';
   ```

2. **Configure Cache Bins**:
   - External content uses dedicated cache bins
   - Can be configured to use Redis or Memcache

## Security Configuration

### API Security

1. **SSL Verification**:
   - Always enabled for external API calls
   - Cannot be disabled in production

2. **Rate Limiting**:
   - Built-in rate limiting for external content
   - Prevents abuse of external services

### User Permissions

Configure appropriate permissions:

1. **Administrative Permissions**:
   - `administer site configuration`: For cache clearing
   - `administer paragraphs types`: For component configuration

2. **Content Creation Permissions**:
   - `create [content_type] content`: For creating content
   - `edit [content_type] content`: For editing content

## Troubleshooting Common Issues

### Installation Issues

**Problem**: Module won't install due to missing dependencies
**Solution**: 
```bash
composer install
drush en paragraphs field_group
```

**Problem**: Configuration import fails
**Solution**:
```bash
drush cim -y --partial --source=modules/contrib/howard_paragraphs/config/install/
```

### External Content Issues

**Problem**: External feeds not loading
**Solution**:
1. Check network connectivity
2. Verify API endpoints are accessible
3. Clear external content cache
4. Check error logs

**Problem**: Stale content displaying
**Solution**:
1. Clear caches: `drush cr`
2. Clear external content: Visit `/admin/config/clear_howard_external_content`
3. Check cron is running

### Performance Issues

**Problem**: Slow page loads
**Solution**:
1. Enable page caching
2. Use CDN for static assets
3. Optimize database queries
4. Consider using Redis/Memcache

## Environment-Specific Configuration

### Development Environment

```php
// settings.local.php
$config['howard_paragraphs.settings']['news_api_endpoint'] = 'https://dev.thedig.howard.edu/api';
$config['howard_paragraphs.settings']['cache_ttl'] = 300; // 5 minutes
$config['system.performance']['cache']['page']['max_age'] = 0; // Disable page cache
```

### Staging Environment

```php
// settings.staging.php
$config['howard_paragraphs.settings']['news_api_endpoint'] = 'https://staging.thedig.howard.edu/api';
$config['howard_paragraphs.settings']['cache_ttl'] = 1800; // 30 minutes
```

### Production Environment

```php
// settings.php
$config['howard_paragraphs.settings']['cache_ttl'] = 10800; // 3 hours
$config['system.performance']['cache']['page']['max_age'] = 3600; // 1 hour
```

## Maintenance Tasks

### Regular Maintenance

1. **Weekly**:
   - Review error logs
   - Check external content functionality
   - Monitor site performance

2. **Monthly**:
   - Update module and dependencies
   - Review configuration changes
   - Test external content feeds

3. **Quarterly**:
   - Review paragraph usage analytics
   - Update documentation
   - Plan new component development

### Backup Strategy

1. **Configuration Backup**:
   ```bash
   drush config:export
   ```

2. **Database Backup**:
   ```bash
   drush sql:dump > backup.sql
   ```

3. **File Backup**:
   - Backup entire modules directory
   - Include any custom templates

Your Howard Paragraphs installation is now complete and configured! Next, explore the available paragraph components by reviewing the submodules documentation in the main module or check out the [Development Guide](development-guide.md) to learn about creating custom components.
