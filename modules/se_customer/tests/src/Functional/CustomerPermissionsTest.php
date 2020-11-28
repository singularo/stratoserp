<?php

declare(strict_types=1);

namespace Drupal\Tests\se_customer\Functional;

use Drupal\Tests\se_testing\Functional\FunctionalTestBase;

/**
 * Test basic permissions.
 *
 * @coversDefault Drupal\se_customer
 * @group se_customer
 * @group stratoserp
 */
class CustomerPermissionsTest extends FunctionalTestBase {

  /**
   * Faker factory for customer.
   *
   * @var \Faker\Factory
   */
  protected $customer;

  /**
   * Faker factory for staff.
   *
   * @var \Faker\Factory
   */
  protected $staff;

  /**
   * List of pages to test permissions on.
   *
   * @var string[]
   */
  private $pages = [
    '/node/add/se_customer',
    '/se/customer-list',
  ];

  /**
   * Run the basic permissions check on the pages.
   */
  public function testCustomerPermissions(): void {
    $this->basicPermissionCheck($this->pages);
  }

}