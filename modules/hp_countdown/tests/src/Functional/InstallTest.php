<?php

namespace Drupal\Tests\hp_countdown\Functional;

use Drupal\Tests\BrowserTestBase;

/**
 * Tests module installation.
 *
 * @group howard_paragraphs
 * @group hp_countdown
 */
class InstallTest extends BrowserTestBase {

  /**
   * {@inheritdoc}
   */
  protected static $modules = [
    'node',
  ];

  /**
   * {@inheritdoc}
   */
  protected $defaultTheme = 'stark';

  /**
   * Module handler to ensure installed modules.
   *
   * @var \Drupal\Core\Extension\ModuleHandlerInterface
   */
  public $moduleHandler;

  /**
   * Module installer.
   *
   * @var \Drupal\Core\Extension\ModuleInstallerInterface
   */
  public $moduleInstaller;

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();
    $this->moduleHandler = $this->container->get('module_handler');
    $this->moduleInstaller = $this->container->get('module_installer');

    // Set the front page to "/node".
    \Drupal::configFactory()
      ->getEditable('system.site')
      ->set('page.front', '/node')
      ->save(TRUE);
  }

  /**
   * Reloads services used by this test.
   */
  protected function reloadServices() {
    $this->rebuildContainer();
    $this->moduleHandler = $this->container->get('module_handler');
    $this->moduleInstaller = $this->container->get('module_installer');
  }

  /**
   * Tests that the module is installable.
   */
  public function testInstallation() {
    $account = $this->drupalCreateUser(['access content']);
    $this->drupalLogin($account);

    $this->assertFalse($this->moduleHandler->moduleExists('hp_countdown'));
    $this->assertFalse($this->moduleHandler->moduleExists('howard_paragraphs'));
    $this->assertTrue($this->moduleInstaller->install(['howard_paragraphs']));
    \Drupal::service('config.installer')->installDefaultConfig('module', 'howard_paragraphs');
    $this->assertTrue($this->moduleInstaller->install(['hp_countdown']));
    \Drupal::service('config.installer')->installDefaultConfig('module', 'hp_countdown');
    $this->reloadServices();
    $this->assertTrue($this->moduleHandler->moduleExists('hp_countdown'));

    // Load the front page.
    $this->drupalGet('<front>');

    // Confirm that the site didn't throw a server error or something else.
    $this->assertSession()->statusCodeEquals(200);

    // Confirm that the front page contains the standard text.
    $this->assertSession()->pageTextContains('Welcome!');
  }

}
