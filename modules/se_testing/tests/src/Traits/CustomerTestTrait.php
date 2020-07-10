<?php

declare(strict_types=1);

namespace Drupal\Tests\se_testing\Traits;

use Drupal\node\Entity\Node;
use Faker\Factory;

/**
 * Provides functions for creating content during functional tests.
 */
trait CustomerTestTrait {

  /**
   * Storage for the faker data for customer.
   *
   * @var FakerFactory
   */
  protected $customer;

  /**
   * Setup basic faker fields for this test trait.
   */
  public function customerFakerSetup(): void {
    $this->faker = Factory::create();

    $original                      = error_reporting(0);
    $this->customer->name          = $this->faker->realText(50);
    $this->customer->phoneNumber   = $this->faker->phoneNumber;
    $this->customer->mobileNumber  = $this->faker->phoneNumber;
    $this->customer->streetAddress = $this->faker->streetAddress;
    $this->customer->suburb        = $this->faker->city;
    $this->customer->state         = $this->faker->stateAbbr;
    $this->customer->postcode      = $this->faker->postcode;
    $this->customer->url           = $this->faker->url;
    $this->customer->companyEmail  = $this->faker->companyEmail;
    error_reporting($original);
  }

  /**
   * Add a customer node.
   */
  public function addCustomer(): Node {
    $this->customerFakerSetup();

    /** @var \Drupal\node\Entity\Node $node */
    $node = $this->createNode([
      'type' => 'se_customer',
      'title' => $this->customer->name,
      'se_cu_phone' => $this->customer->phoneNumber,
      'se_cu_email' => $this->customer->companyEmail,
    ]);
    $this->assertNotEqual($node, FALSE);
    $this->drupalGet($node->toUrl());
    $this->assertSession()->statusCodeEquals(200);

    $content = $this->getTextContent();

    $this->assertStringNotContainsString('Please fill in this field', $content);

    // Check that what we entered is shown.
    $this->assertStringContainsString($this->customer->name, $content);
    $this->assertStringContainsString($this->customer->phoneNumber, $content);

    return $node;
  }

}
