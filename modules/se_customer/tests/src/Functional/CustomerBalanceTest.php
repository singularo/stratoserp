<?php

declare(strict_types=1);

namespace Drupal\Tests\se_customer\Functional;

use Drupal\Tests\se_testing\Functional\FunctionalTestBase;

/**
 * @coversDefault Drupal\se_customer
 * @group se_customer
 * @group stratoserp
 */
class CustomerBalanceTest extends FunctionalTestBase {

  /**
   *
   */
  public function testCustomerBalance(): void {

    $staff = $this->setupStaffUser();
    $this->drupalLogin($staff);
    $customer = $this->addCustomer();

    \Drupal::service('se_customer.service')->setBalance($customer, 25);
    $this->assertEqual(\Drupal::service('se_customer.service')->getBalance($customer), 25);

    \Drupal::service('se_customer.service')->adjustBalance($customer, -50);
    $this->assertEqual(\Drupal::service('se_customer.service')->getBalance($customer), -25);

    $this->drupalLogout();
  }

}
