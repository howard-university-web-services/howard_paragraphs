# Howard Permissions - Testing & Development Guide

## Overview

The Howard Permissions module includes comprehensive testing and documentation to ensure reliability and maintainability.

## Test Suite

### Running Tests

The module includes a convenient test runner script:

```bash
# Run all tests
./run-tests.sh

# Run specific test types
./run-tests.sh unit
./run-tests.sh functional  
./run-tests.sh validate

# Get help
./run-tests.sh help
```

### Test Types

#### Unit Tests (`tests/src/Unit/HowardPermissionsTest.php`)
- Tests core functionality in isolation
- Validates permission parsing and conversion logic
- Tests data structure validation
- No database or Drupal bootstrap required

**Test Coverage:**
- Permission name conversion (display → machine names)
- Role name conversion (display → machine names)  
- JSON structure validation
- Helper function behavior
- Error handling scenarios

#### Functional Tests (`tests/src/Functional/HowardPermissionsFunctionalTest.php`)
- Tests module behavior in full Drupal environment
- Validates form alterations and user interface
- Tests permission enforcement end-to-end
- Requires full Drupal bootstrap

**Test Coverage:**
- Permissions page modifications
- Core role creation and management
- Permission application functionality
- Form submission handling
- Custom role protection

#### Validation Tests
- PHP syntax validation for all module files
- JSON configuration file validation
- Module structure verification

### Test Output Example

```
Howard Permissions Module - Test Runner
=======================================
Validating PHP syntax...
✓ howard_permissions.module
✓ howard_permissions.install
✓ src/Commands/HowardPermissionsCommands.php
✓ tests/src/Unit/HowardPermissionsTest.php
✓ tests/src/Functional/HowardPermissionsFunctionalTest.php

Validating JSON configuration...
✓ JSON is valid
✓ Found 38 permissions in JSON

Running All Tests...
✓ HowardPermissionsTest::testEnforcePermission
✓ HowardPermissionsTest::testJsonPermissionLoading
✓ HowardPermissionsTest::testRoleNameConversion
✓ HowardPermissionsFunctionalTest::testPermissionsPageModifications
✓ HowardPermissionsFunctionalTest::testCoreRolesExist

Test run complete!
```

## Documentation Structure

### 1. README.md
- **Purpose**: Primary user documentation
- **Audience**: Site administrators, developers implementing the module
- **Content**: Installation, usage, troubleshooting, examples

### 2. API.md  
- **Purpose**: Developer API reference
- **Audience**: Developers extending or integrating with the module
- **Content**: Function signatures, parameters, return values, usage patterns

### 3. TESTING.md (this file)
- **Purpose**: Testing and development guide
- **Audience**: Developers maintaining or contributing to the module
- **Content**: Test procedures, development workflow, code standards

## Development Workflow

### 1. Making Changes
```bash
# 1. Edit module files
vim howard_permissions.module

# 2. Validate syntax  
./run-tests.sh validate

# 3. Run tests
./run-tests.sh unit

# 4. Test in browser
lando drush cr
# Visit /admin/people/permissions

# 5. Run full test suite
./run-tests.sh all
```

### 2. Adding New Features

When adding new functionality:

1. **Write tests first** (TDD approach)
   ```php
   // Add test to tests/src/Unit/HowardPermissionsTest.php
   public function testNewFeature() {
     // Test logic here
   }
   ```

2. **Implement feature**
   ```php
   // Add function to howard_permissions.module
   function howard_permissions_new_feature() {
     // Implementation here
   }
   ```

3. **Update documentation**
   - Add function to API.md
   - Update examples in README.md
   - Document any new configuration options

4. **Validate all tests pass**
   ```bash
   ./run-tests.sh all
   ```

### 3. Updating Permissions

When updating the permissions list:

1. **Edit JSON file**
   ```bash
   vim permissions_roles.json
   ```

2. **Validate JSON**
   ```bash
   ./run-tests.sh validate
   ```

3. **Apply changes**
   ```bash
   lando drush eval "howard_permissions_apply_all_permissions();"
   ```

4. **Test on permissions page**
   Visit `/admin/people/permissions` to verify changes

## Code Quality Standards

### PHP Standards
- Follow Drupal coding standards
- Use type hints where appropriate  
- Include comprehensive docblocks
- Handle errors gracefully with logging

### Testing Standards
- Aim for high test coverage (>80%)
- Test both success and failure scenarios
- Use descriptive test method names
- Include assertions for all expected outcomes

### Documentation Standards
- Keep README user-focused
- Keep API docs developer-focused
- Include code examples for complex features
- Update docs with every API change

## Continuous Integration

### Pre-commit Checks
Before committing changes:
```bash
# Run full validation
./run-tests.sh validate

# Run all tests
./run-tests.sh all

# Manual browser test
lando drush cr
# Test permissions page manually
```

### Deployment Checklist
Before deploying to production:

1. **All tests pass**: `./run-tests.sh all`
2. **JSON validates**: `./run-tests.sh validate`  
3. **Permissions apply correctly**: Test `howard_permissions_apply_all_permissions()`
4. **Documentation updated**: README and API docs current
5. **Backup existing permissions**: Export current roles before deployment

## Performance Testing

### Load Testing Permissions Application
```bash
# Time the permission application
time lando drush eval "howard_permissions_apply_all_permissions();"

# Monitor for performance with large permission sets
lando drush eval "
  \$start = microtime(true);
  howard_permissions_apply_all_permissions();
  echo 'Execution time: ' . (microtime(true) - \$start) . ' seconds';
"
```

### Memory Usage Testing
```bash
# Check memory usage during permission application
lando drush eval "
  \$before = memory_get_usage();
  howard_permissions_apply_all_permissions();
  \$after = memory_get_usage();
  echo 'Memory used: ' . ((\$after - \$before) / 1024 / 1024) . ' MB';
"
```

## Troubleshooting Tests

### Test Failures
If tests fail:

1. **Check PHP syntax**: `./run-tests.sh validate`
2. **Review error output**: Look for specific assertion failures
3. **Check test environment**: Ensure Drupal bootstrap is working
4. **Verify test data**: Ensure JSON file exists and is valid

### Common Issues

**Issue**: PHPUnit not found
```bash
# Solution: Check vendor/bin/phpunit exists
ls -la ../../../vendor/bin/phpunit
```

**Issue**: Bootstrap errors  
```bash
# Solution: Run from project root
cd /path/to/drupal/root
./docroot/modules/custom/howard_permissions/run-tests.sh
```

**Issue**: JSON validation fails
```bash
# Solution: Check JSON syntax
cat permissions_roles.json | python -m json.tool
```

## Contributing

### Submitting Changes
1. Run full test suite: `./run-tests.sh all`
2. Update relevant documentation
3. Add tests for new features
4. Follow Drupal coding standards
5. Include clear commit messages

### Reporting Issues
Include with bug reports:
- Output of `./run-tests.sh validate`
- Relevant error logs from `drush ws`
- Steps to reproduce the issue
- Expected vs actual behavior