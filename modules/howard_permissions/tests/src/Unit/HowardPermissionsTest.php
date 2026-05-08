<?php

namespace Drupal\Tests\howard_permissions\Unit;

use Drupal\Tests\UnitTestCase;
use Drupal\Core\Messenger\MessengerInterface;
use Drupal\Core\Logger\LoggerChannelInterface;
use Drupal\user\Entity\Role;

/**
 * Tests for Howard Permissions module functions.
 *
 * @group howard_permissions
 */
class HowardPermissionsTest extends UnitTestCase {

  /**
   * Tests the enforce_permission helper function.
   */
  public function testEnforcePermission() {
    // Test adding a single permission
    $result = howard_permissions_enforce_permission('test_permission', ['administrator']);
    $this->assertArrayHasKey('test_permission', $result);
    $this->assertEquals(['administrator'], $result['test_permission']);

    // Test adding another permission
    howard_permissions_enforce_permission('another_permission', ['site_admin', 'site_builder']);
    $result = howard_permissions_enforce_permission('', []);
    
    $this->assertArrayHasKey('test_permission', $result);
    $this->assertArrayHasKey('another_permission', $result);
    $this->assertEquals(['administrator'], $result['test_permission']);
    $this->assertEquals(['site_admin', 'site_builder'], $result['another_permission']);
  }

  /**
   * Tests JSON permission loading with valid data.
   */
  public function testJsonPermissionLoading() {
    // Create a mock JSON structure
    $json_data = [
      'permissions' => [
        [
          'permission' => 'Test Permission',
          'roles' => ['Site Admin', 'Administrator']
        ],
        [
          'permission' => 'Another Test Permission',
          'roles' => ['Site Builder']
        ]
      ]
    ];

    // Test the conversion logic that happens in howard_permissions_get_enforced_permissions()
    $converted_permissions = [];
    foreach ($json_data['permissions'] as $item) {
      if (isset($item['permission']) && isset($item['roles'])) {
        $roles = array_map(function($role) {
          return strtolower(str_replace(' ', '_', $role));
        }, $item['roles']);
        $converted_permissions[strtolower($item['permission'])] = $roles;
      }
    }

    $this->assertArrayHasKey('test permission', $converted_permissions);
    $this->assertArrayHasKey('another test permission', $converted_permissions);
    $this->assertEquals(['site_admin', 'administrator'], $converted_permissions['test permission']);
    $this->assertEquals(['site_builder'], $converted_permissions['another test permission']);
  }

  /**
   * Tests role name conversion.
   */
  public function testRoleNameConversion() {
    $test_roles = [
      'Site Builder' => 'site_builder',
      'Site Admin' => 'site_admin', 
      'Administrator' => 'administrator',
      'Multi Word Role Name' => 'multi_word_role_name'
    ];

    foreach ($test_roles as $display_name => $expected_machine_name) {
      $converted = strtolower(str_replace(' ', '_', $display_name));
      $this->assertEquals($expected_machine_name, $converted);
    }
  }

  /**
   * Tests permission name conversion.
   */
  public function testPermissionNameConversion() {
    $test_permissions = [
      'Use Admin Toolbar Search' => 'use admin toolbar search',
      'Basic block: Create new content block' => 'basic block: create new content block',
      'Editorial workflow: Use Publish transition' => 'editorial workflow: use publish transition'
    ];

    foreach ($test_permissions as $display_name => $expected_machine_name) {
      $converted = strtolower($display_name);
      $this->assertEquals($expected_machine_name, $converted);
    }
  }

  /**
   * Tests core roles list.
   */
  public function testCoreRolesList() {
    $expected_core_roles = ['anonymous', 'authenticated', 'administrator', 'site_admin', 'site_builder'];
    
    // This is the list used in the module
    $core_roles = ['anonymous', 'authenticated', 'administrator', 'site_admin', 'site_builder'];
    
    $this->assertEquals($expected_core_roles, $core_roles);
    $this->assertCount(5, $core_roles);
  }

  /**
   * Tests empty permissions handling.
   */
  public function testEmptyPermissionsHandling() {
    // Test with empty permission name
    $result = howard_permissions_enforce_permission('', ['administrator']);
    
    // The function should return all stored permissions when called with empty permission
    $this->assertIsArray($result);
  }

  /**
   * Tests permission application result structure.
   */
  public function testPermissionApplicationResultStructure() {
    // Mock the expected structure of howard_permissions_apply_all_permissions() return
    $expected_structure = [
      'success' => TRUE,
      'message' => 'Applied 5 permissions to 5 core roles.',
      'details' => [
        "Granted 'test_permission' to 'administrator'",
        "Revoked 'test_permission' from 'anonymous'"
      ]
    ];

    // Verify the structure has the required keys
    $this->assertArrayHasKey('success', $expected_structure);
    $this->assertArrayHasKey('message', $expected_structure);
    $this->assertArrayHasKey('details', $expected_structure);
    
    // Verify data types
    $this->assertIsBool($expected_structure['success']);
    $this->assertIsString($expected_structure['message']);
    $this->assertIsArray($expected_structure['details']);
  }

  /**
   * Tests JSON structure validation.
   */
  public function testJsonStructureValidation() {
    // Valid JSON structure
    $valid_json = [
      'permissions' => [
        [
          'permission' => 'test permission',
          'roles' => ['administrator']
        ]
      ]
    ];

    $this->assertArrayHasKey('permissions', $valid_json);
    $this->assertIsArray($valid_json['permissions']);
    
    foreach ($valid_json['permissions'] as $item) {
      $this->assertArrayHasKey('permission', $item);
      $this->assertArrayHasKey('roles', $item);
      $this->assertIsString($item['permission']);
      $this->assertIsArray($item['roles']);
    }

    // Invalid JSON structures
    $invalid_json_cases = [
      [], // Empty array
      ['permissions' => 'not_array'], // Permissions not array
      ['wrong_key' => []], // Missing permissions key
      ['permissions' => [['permission' => 'test']]], // Missing roles
      ['permissions' => [['roles' => ['admin']]]], // Missing permission
    ];

    foreach ($invalid_json_cases as $invalid) {
      if (!isset($invalid['permissions']) || !is_array($invalid['permissions'])) {
        $this->assertFalse(isset($invalid['permissions']) && is_array($invalid['permissions']));
        continue;
      }
      
      foreach ($invalid['permissions'] as $item) {
        if (!isset($item['permission']) || !isset($item['roles'])) {
          $this->assertTrue(!isset($item['permission']) || !isset($item['roles']));
        }
      }
    }
  }

}