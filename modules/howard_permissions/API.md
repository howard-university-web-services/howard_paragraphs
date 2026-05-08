# Howard Permissions API Documentation

## Overview

The Howard Permissions module provides a simple API for managing and enforcing permissions across Howard University's multisite Drupal installation.

## Core Functions

### `howard_permissions_get_enforced_permissions()`

Loads and returns all enforced permissions from the JSON configuration file.

**Return Value:**
```php
array - Associative array where:
  - Keys: Permission machine names (string)
  - Values: Arrays of role machine names (array of strings)
```

**Example:**
```php
$permissions = howard_permissions_get_enforced_permissions();
// Result:
// [
//   'use admin toolbar search' => ['site_admin', 'site_builder', 'administrator'],
//   'administer blocks' => ['administrator']
// ]
```

### `howard_permissions_apply_all_permissions()`

Applies all permissions from JSON configuration to Drupal's role system.

**Return Value:**
```php
array - Result information:
  'success' => boolean,      // Whether operation succeeded
  'message' => string,       // Summary message  
  'details' => array        // Detailed list of changes
```

**Example:**
```php
$result = howard_permissions_apply_all_permissions();

if ($result['success']) {
  \Drupal::messenger()->addMessage($result['message']);
  foreach ($result['details'] as $detail) {
    \Drupal::logger('howard_permissions')->info($detail);
  }
} else {
  drupal_set_message($result['message'], 'error');
}
```

### `howard_permissions_enforce_permission($permission, $grant_roles = [])`

Helper function to register a permission and its assigned roles.

**Parameters:**
- `$permission` (string): Permission machine name
- `$grant_roles` (array): Array of role machine names

**Return Value:**
```php
array - Static array of all registered permissions
```

**Example:**
```php
// Add a permission
howard_permissions_enforce_permission('edit articles', ['site_builder', 'site_admin']);

// Get all registered permissions
$all_permissions = howard_permissions_enforce_permission('', []);
```

## Hooks

### `howard_permissions_form_alter()`

**Implements:** `hook_form_alter()`

Modifies the user permissions form to:
- Disable checkboxes for core Howard University roles
- Add visual indicators that permissions are managed
- Include form submission handler

**Form ID:** `user_admin_permissions`

### `howard_permissions_permissions_form_submit()`

**Form Submit Handler**

Enforces Howard University permissions after form submission, regardless of form input.

## Installation Hooks

### `howard_permissions_install()`

**Implements:** `hook_install()`

Runs when module is first installed:
- Creates required Howard University roles
- Applies initial permissions from JSON file

### `howard_permissions_ensure_required_roles()`

**Helper Function**

Creates missing Howard University core roles:
- `site_admin`: Site Admin
- `site_builder`: Site Builder

## Usage Patterns

### Basic Permission Check
```php
// Check if a role has a specific permission after enforcement
$permissions = howard_permissions_get_enforced_permissions();
$has_permission = in_array('site_builder', $permissions['use admin toolbar search'] ?? []);
```

### Programmatic Permission Application
```php
// Apply permissions in a custom module or deployment script
$result = howard_permissions_apply_all_permissions();
if (!$result['success']) {
  throw new Exception('Failed to apply Howard University permissions: ' . $result['message']);
}
```

### Adding New Permissions
```php
// In a custom module, add site-specific permissions
howard_permissions_enforce_permission('access custom feature', ['site_builder', 'site_admin']);

// Apply all permissions including the new one
howard_permissions_apply_all_permissions();
```

## Error Handling

The module provides comprehensive error handling:

### JSON File Missing
- **Detection**: File existence check in `howard_permissions_get_enforced_permissions()`
- **Response**: Error message to user, log entry, empty permissions array
- **Recovery**: Module continues to function, form alterations still work

### JSON Parse Error
- **Detection**: `json_decode()` validation
- **Response**: Error message to user, log entry
- **Recovery**: Module continues with empty permissions

### Missing Roles
- **Detection**: `Role::load()` returns NULL
- **Response**: Warning in apply function result, log entry
- **Recovery**: Continue processing other roles

### Invalid Permissions
- **Detection**: Debug messages on permissions page
- **Response**: Warning message listing invalid permissions
- **Recovery**: Invalid permissions are ignored, valid ones still enforced

## Integration Examples

### Deployment Script
```bash
#!/bin/bash
# Apply Howard University permissions after deployment

echo "Applying Howard University permissions..."
RESULT=$(drush eval "echo howard_permissions_apply_all_permissions()['message'];")
echo "Result: $RESULT"

# Check for errors
if echo "$RESULT" | grep -q "ERROR"; then
  echo "Permission application failed!"
  exit 1
fi

echo "Permissions applied successfully"
```

### Custom Module Integration
```php
/**
 * Implements hook_install().
 */
function mymodule_install() {
  // Add custom permissions to Howard system
  howard_permissions_enforce_permission('use mymodule feature', ['site_builder']);
  
  // Apply all permissions including new ones
  $result = howard_permissions_apply_all_permissions();
  if (!$result['success']) {
    \Drupal::logger('mymodule')->error('Failed to integrate with Howard Permissions: @message', ['@message' => $result['message']]);
  }
}
```

### Monitoring Integration
```php
/**
 * Check Howard University permission consistency.
 */
function check_howard_permissions_consistency() {
  $result = howard_permissions_apply_all_permissions();
  
  // Count changes - if many changes were needed, permissions may have drifted
  $change_count = count($result['details']);
  
  if ($change_count > 10) {
    \Drupal::logger('monitoring')->warning('Howard permissions required @count changes - possible configuration drift', ['@count' => $change_count]);
  }
  
  return $result;
}
```

## Security Considerations

### Core Role Protection
- Only affects roles: `anonymous`, `authenticated`, `administrator`, `site_admin`, `site_builder`
- Custom site-specific roles remain fully editable
- No elevation of privileges possible through JSON manipulation

### File Access
- JSON file should be readable by web server but not web-accessible
- Place in module directory (not web root)
- Consider file permissions in production

### Permission Validation
- Only existing Drupal permissions can be enforced
- Invalid permissions are ignored with warnings
- No SQL injection possible (uses Drupal APIs)

## Performance Notes

### Caching
- Permission checks use Drupal's built-in role/permission caching
- JSON file is read on each `howard_permissions_get_enforced_permissions()` call
- Consider caching for high-traffic sites

### Optimization
- JSON loading could be cached in a custom cache bin
- Form alterations only run on permissions page
- Permission application is typically only needed during deployment