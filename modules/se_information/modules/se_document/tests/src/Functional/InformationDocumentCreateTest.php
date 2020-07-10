<?php

declare(strict_types=1);

namespace Drupal\Tests\se_document\Functional;

use weitzman\DrupalTestTraits\ExistingSiteBase;

/**
 * @coversDefault Drupal\se_document
 * @group se_document
 * @group stratoserp
 */
class InformationDocumentCreateTest extends ExistingSiteBase {

  protected $customer;
  protected $staff;

  private $pages = [
    '/information/add/se_document',
  ];

  /**
   *
   */
  public function testInformationDocumentPermissions() {
    $this->customer = $this->createUser([], NULL, FALSE);
    $this->customer->addRole('customer');
    $this->customer->save();

    $this->staff = $this->createUser([], NULL, FALSE);
    $this->staff->addRole('staff');
    $this->staff->save();

    foreach ($this->pages as $page) {
      $this->drupalGet($page);
      $this->assertSession()->statusCodeEquals(403);
    }

    foreach ($this->pages as $page) {
      $this->drupalLogin($this->customer);
      $this->drupalGet($page);
      $this->assertSession()->statusCodeEquals(403);
      $this->drupalLogout();
    }

    foreach ($this->pages as $page) {
      $this->drupalLogin($this->staff);
      $this->drupalGet($page);
      $this->assertSession()->statusCodeEquals(200);
      $this->drupalLogout();
    }
  }

}
