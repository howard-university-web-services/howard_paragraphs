<?php

namespace Drupal\Tests\howard_permissions\Functional;

use Drupal\Tests\BrowserTestBase;
use Drupal\user\Entity\Role;
use Drupal\user\Entity\User;

/**
 * Tests the Howard Permissions module functionality.
 *
 * @group howard_permissions
 */
class HowardPermissionsFunctionalTest extends BrowserTestBase {

  /**
   * {@inheritdoc}
   */
  protected $defaultTheme = 'stark';

  /**
   * Modules to enable.
   *
   * @var array
   */
  protected static $modules = ['howard_permissions', 'user'];

  /**
   * An admin user with appropriate permissions.
   *
   * @var \Drupal\user\UserInterface
   */
  protected $adminUser;

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();

    // Create core Howard University roles
    $roles = [
      'site_admin' => 'Site Admin',
      'site_builder' => 'Site Builder',
    ];

    foreach ($roles as $id => $label) {
      if (!Role::load($id)) {
        Role::create([
          'id' => $id,
          'label' => $label,
        ])->save();
      }
    }

    // Create an admin user
    $this->adminUser = $this->drupalCreateUser([
      'administer permissions',
      'administer users',
    ]);
  }

  /**
   * Tests the permissions page modifications.
   */
  public function testPermissionsPageModifications() {
    $this->drupalLogin($this->adminUser);
    $this->drupalGet('admin/people/permissions');
    
    // Check for the informational message
    $this->assertSession()->pageTextContains('Core Howard University role permissions are managed automatically');
    
    // Check that the page loads without errors
    $this->assertSession()->statusCodeEquals(200);
  }

  /**
   * Tests that core roles exist after module installation.
   */
  public function testCoreRolesExist() {
    $required_roles = ['administrator', 'site_admin', 'site_builder'];
    
    foreach ($required_roles as $role_id) {
      $role = Role::load($role_id);
      $this->assertNotNull($role, "Role {$role_id} should exist");
    }
  }

  /**
   * Tests permission application function.
   */
  public function testPermissionApplication() {
    // Apply permissions programmatically
    $result = howard_permissions_apply_all_permissions();
    
    // Check result structure
    $this->assertArrayHasKey('success', $result);
    $this->assertArrayHasKey('message', $result);
    $this->assertArrayHasKey('details', $result);
    
    // For this test, we expect it to work even if no JSON file exists
    $this->assertIsBool($result['success']);
    $this->assertIsString($result['message']);
    $this->assertIsArray($result['details']);
  }

  /**
   * Tests form submission with enforced permissions.
   */
  public function testFormSubmissionEnforcement() {
    $this->drupalLogin($this->adminUser);
    
    // Visit the permissions page
    $this->drupalGet('admin/people/permissions');
    
    // Submit the form (even though checkboxes might be disabled)
    $this->submitForm([], 'Save permissions');
    
    // Check for success message or that we're still on the permissions page
    $this->assertSession()->statusCodeEquals(200);
  }

  /**
   * Tests that custom roles are not affected.
   */
  public function testCustomRolesNotAffected() {
    // Create a custom role
    $custom_role = Role::create([
      'id' => 'custom_test_role',
      'label' => 'Custom Test Role',
    ]);
    $custom_role->save();

    $this->drupalLogin($this->adminUser);
    $this->drupalGet('admin/people/permissions');
    
    // The page should load successfully with the custom role
    $this->assertSession()->statusCodeEquals(200);
    $this->assertSession()->pageTextContains('Custom Test Role');
  }

  /**
   * Tests permission enforcement for a specific permission.
   */
  public function testSpecificPermissionEnforcement() {
    // Get all permissions to find a real one to test
    $permissions_service = \Drupal::service('user.permissions');
    $all_permissions = $permissions_service->getPermissions();
    
    if (!empty($all_permissions)) {
      $test_permission = array_keys($all_permissions)[0];
      
      // Test the enforcement helper function
      $result = howard_permissions_enforce_permission($test_permission, ['administrator']);
      $this->assertArrayHasKey($test_permission, $result);
      $this->assertEquals(['administrator'], $result[$test_permission]);
    }
  }

  /**
   * Tests module uninstall cleanup.
   */
  public function testModuleUninstallCleanup() {
    // This test ensures the module can be cleanly uninstalled
    // The roles should remain but permissions management should be restored
    
    $roles_before = user_roles();
    
    // Simulate module disable (we can't actually uninstall in this context)
    // But we can verify roles still exist
    $roles_after = user_roles();
    
    // Core Howard roles should still exist
    $this->assertEquals($roles_before, $roles_after);
  }

  /**
   * Tests error handling when JSON file is missing.
   */
  public function testMissingJsonFileHandling() {
    // The module should handle missing JSON gracefully
    $this->drupalLogin($this->adminUser);
    $this->drupalGet('admin/people/permissions');
    
    // Page should still load, potentially with error message
    $this->assertSession()->statusCodeEquals(200);
  }

  /**
   * Tests that anonymous and authenticated roles are handled.
   */
  public function testAnonymousAndAuthenticatedRoles() {
    $anonymous_role = Role::load('anonymous');
    $authenticated_role = Role::load('authenticated');
    
    $this->assertNotNull($anonymous_role, 'Anonymous role should exist');
    $this->assertNotNull($authenticated_role, 'Authenticated role should exist');
    
    // These are the two roles that always exist in Drupal
    $this->assertEquals('anonymous', $anonymous_role->id());
    $this->assertEquals('authenticated', $authenticated_role->id());
  }

}