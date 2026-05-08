# Howard Permissions Module

> **This module is distributed as a submodule of [howard_paragraphs](https://github.com/howard-university-web-services/howard_paragraphs).** It is designed to be dropped into any Howard University Drupal site independently; it has no dependency on `howard_paragraphs` itself at runtime.

## Overview

The Howard Permissions module manages and enforces standard permissions for core Howard University roles across all multisite Drupal installations. This ensures consistency and security across the entire Howard University web ecosystem.

## Purpose

- **Standardization**: Ensures consistent permissions across all Howard University Drupal sites
- **Security**: Prevents accidental modification of core role permissions
- **Maintenance**: Centralizes permission management through JSON configuration files
- **Flexibility**: Allows custom site-specific roles to remain fully editable
- **Automation**: Provides programmatic and command-line tools for permission management

## Core Roles Managed

The module manages permissions for these standard Howard University roles:

- **anonymous**: Visitors who are not logged in
- **authenticated**: Any user who has logged in
- **administrator**: Users with full administrative access
- **site_admin**: Users with site-specific administration privileges  
- **site_builder**: Users who can manage content and site structure

## Features

### Permission Enforcement
- Automatically loads permissions from `permissions_roles.json`
- Disables manual editing of core role permissions on the permissions page
- Enforces correct permissions on form submission
- Displays clear indicators that permissions are managed automatically
- Provides programmatic API for permission application

### JSON Configuration
The module loads permissions from `permissions_roles.json` with this structure:

```json
{
  "permissions": [
    {
      "permission": "use admin toolbar search",
      "roles": ["Site Builder", "Site Admin", "Administrator"]
    },
    {
      "permission": "administer blocks", 
      "roles": ["Administrator"]
    }
  ]
}
```

**Important**: Permission names must exactly match Drupal's machine names, not display names.

### Error Handling
- Clear error messages if JSON file is missing or invalid
- Debug messages showing which permissions don't exist in Drupal
- Comprehensive logging for troubleshooting

## Installation

### 1. Place the module

Copy the module into your site's custom modules directory:

```
docroot/modules/custom/howard_permissions/
```

Ensure `permissions_roles.json` is present — the module cannot function without it.

### 2. Enable the module

**This is the only command you need.** The install hook automatically:
- Creates the `site_admin` and `site_builder` roles if they don't already exist
- Reads `permissions_roles.json` and applies all permissions to the core roles

```bash
drush en howard_permissions -y && drush cr
```

**With Lando (local development):**
```bash
lando drush en howard_permissions -y && lando drush cr
```

No separate permission-application step is required after a fresh enable.

### 3. Verify (optional)

To confirm the install hook ran cleanly:

```bash
# Confirm the module is enabled
drush pm-list | grep howard_permissions

# Check logs for permission application output
drush ws --count=20 | grep howard_permissions
```

### Re-applying permissions after updates

> **This is not needed after a fresh install.** Use this only if you update `permissions_roles.json` or need to re-sync permissions on a site where the module is already enabled.

```bash
# Using the included script (respects DRUSH_URI for multisite)
DRUSH_URI=https://mysite.example.com ./apply-permissions.sh apply

# Or directly via Drush
drush eval "howard_permissions_apply_all_permissions();"
drush cr
```

## Usage

### Automatic Application
Once enabled, the module automatically:
1. **Modifies the permissions page** (`/admin/people/permissions`) to disable core role checkboxes
2. **Displays informational messages** explaining that core roles are managed automatically
3. **Enforces permissions** whenever the permissions form is submitted
4. **Leaves custom roles editable** so site-specific permissions can still be managed

### Manual/Scripted Application
Apply permissions programmatically without visiting the permissions page:

**Option 1: Shell Script (Recommended)**
```bash
# Apply all permissions from JSON
./apply-permissions.sh apply

# Check permissions status  
./apply-permissions.sh status

# Validate JSON configuration
./apply-permissions.sh validate

# Get help
./apply-permissions.sh help
```

**Option 2: Direct Drush Eval**
```bash
# Apply all permissions from JSON
lando drush eval "howard_permissions_apply_all_permissions();"

# Get formatted output
lando drush eval "echo howard_permissions_apply_all_permissions()['message'];"

# Check for success
lando drush eval "print_r(howard_permissions_apply_all_permissions());"
```

### Integration with Deployment
Add to deployment scripts:

```bash
#!/bin/bash
# Deploy and apply permissions
drush cr
drush updatedb -y

# Option 1: Use the script
./docroot/modules/custom/howard_permissions/apply-permissions.sh apply

# Option 2: Direct eval
drush eval "echo howard_permissions_apply_all_permissions()['message'];"
```

## API Documentation

### Functions

#### `howard_permissions_get_enforced_permissions()`
Loads permissions from JSON file and returns array of permissions with assigned roles.

**Returns**: `array` - Associative array where keys are permission machine names and values are arrays of role machine names.

#### `howard_permissions_apply_all_permissions()`
Applies all permissions from JSON configuration to core roles.

**Returns**: `array` with keys:
- `success`: `boolean` - Whether operation succeeded
- `message`: `string` - Summary message
- `details`: `array` - Detailed list of changes made

**Example**:
```php
$result = howard_permissions_apply_all_permissions();
if ($result['success']) {
  \Drupal::logger('deployment')->info($result['message']);
}
```

#### `howard_permissions_enforce_permission($permission, $grant_roles)`
Helper function to register a permission and its allowed roles.

**Parameters**:
- `$permission`: `string` - Permission machine name
- `$grant_roles`: `array` - Array of role machine names

## File Structure

```
howard_permissions/
├── howard_permissions.info.yml          # Module definition
├── howard_permissions.module            # Main module code  
├── howard_permissions.install           # Install/update hooks
├── permissions_roles.json              # Permission configuration
├── apply-permissions.sh                # Permission application script
├── run-tests.sh                        # Test runner script
├── README.md                           # This documentation
├── API.md                              # Developer API reference
├── TESTING.md                          # Testing guide
├── phpunit.xml                         # PHPUnit configuration
└── tests/
    └── src/
        ├── Unit/
        │   └── HowardPermissionsTest.php    # Unit tests
        └── Functional/
            └── HowardPermissionsFunctionalTest.php # Functional tests
```

## Permission Name Mapping

The JSON file must use Drupal's exact permission machine names. Common mappings:

| JSON Display Name | Drupal Machine Name |
|-------------------|-------------------|
| "Basic block: Create new content block" | "create basic block content" |
| "Use Admin Toolbar search" | "use admin toolbar search" |
| "Editorial workflow: Use Publish transition" | "use editorial transition publish" |
| "Access HC Media Browser Audio pages" | "access hc_media_browser_audio entity browser pages" |

To find correct permission names:
```bash
drush eval "print_r(array_keys(\Drupal::service('user.permissions')->getPermissions()));" | grep -i "keyword"
```

## Testing

Run the module's test suite:

```bash
# Unit tests
./vendor/bin/phpunit modules/custom/howard_permissions/tests/src/Unit/

# Functional tests  
./vendor/bin/phpunit modules/custom/howard_permissions/tests/src/Functional/

# All tests
./vendor/bin/phpunit modules/custom/howard_permissions/tests/
```

## Maintenance

### Updating Permissions
1. Edit `permissions_roles.json` with new permission assignments
2. Apply changes: `drush eval "howard_permissions_apply_all_permissions();"`
3. Verify on permissions page: `/admin/people/permissions`

### Adding New Permissions
1. Find the exact machine name: `drush eval "print_r(array_keys(\Drupal::service('user.permissions')->getPermissions()));" | grep -i "new_permission"`
2. Add to JSON file with appropriate roles
3. Apply: `drush eval "howard_permissions_apply_all_permissions();"`

### Troubleshooting

**Issue**: Permissions not being enforced
```bash
# Check if module is enabled
drush pm-list | grep howard_permissions

# Check for JSON errors
drush eval "print_r(howard_permissions_get_enforced_permissions());"

# Check logs
drush ws --count=10
```

**Issue**: Permission not found errors
- Visit `/admin/people/permissions` to see debug messages
- Use `grep` to find correct permission machine names
- Update JSON file with exact machine names

**Issue**: Roles missing
```bash
# Check if core roles exist
drush eval "print_r(array_keys(user_roles()));"

# Recreate missing roles
drush updatedb
```

## Development

### Adding New Features
1. Add functionality to `howard_permissions.module`
2. Update API documentation in this README
3. Add unit tests in `tests/src/Unit/`
4. Add functional tests if needed
5. Update install hooks if database changes are needed

### Code Standards
- Follow Drupal coding standards
- Add comprehensive documentation for all functions
- Include error handling and logging
- Write tests for all new functionality

### Contributing
1. Test changes locally with `lando`
2. Run test suite: `./vendor/bin/phpunit modules/custom/howard_permissions/tests/`
3. Update documentation for any API changes
4. Ensure backward compatibility