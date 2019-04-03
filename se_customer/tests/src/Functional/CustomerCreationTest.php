<?php

namespace Drupal\Tests\se_customer\Functional;

use Drupal\node\Entity\Node;
use Drupal\Tests\se_testing\Functional\NodeTestBase;

/**
 * @coversDefault Drupal\se_customer
 * @group se_customer
 * @group stratoserp
 *
 */
class CustomerCreationTest extends NodeTestBase {

  /**
   * The modules to load to run the test.
   *
   * @var array
   */
  protected static $modules = [
    'comment',
    'field',
    'field_group',
    'field_layout',
    'file',
    'geolocation',
    'layout_discovery',
    'link',
    'menu_ui',
    'node',
    'options',
    'path',
    'se_core',
    'se_customer',
    'serial',
    'taxonomy',
    'telephone',
    'text',
    'user',
    'views',
  ];

  private $pages = [
    '/node/add/se_customer',
  ];

  public function testCustomerPermissions() {
    foreach ($this->pages as $page) {
      $this->drupalGet($page);
      $this->assertSession()->statusCodeEquals(403);
    }

    foreach ($this->pages as $page) {
      $this->drupalLogin($this->customerAccount);
      $this->drupalGet($page);
      $this->assertSession()->statusCodeEquals(403);
      $this->drupalLogout();
    }

    foreach ($this->pages as $page) {
      $this->drupalLogin($this->staffAccount);
      $this->drupalGet($page);
      $this->assertSession()->statusCodeEquals(200);
      $this->drupalLogout();
    }
  }

  public function testCustomerAdd($delete = TRUE) {
    $logout = $this->myLogin($this->staffAccount);

    /** @var Node $node */
    $node = $this->testCustomerForm();

    // If we're going to delete, we need to re-load the node. sqlite hack
    if ($delete) {
      $node->delete();
    }

    if ($logout) {
      $this->drupalLogout();
    }
  }

}