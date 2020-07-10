<?php

declare(strict_types=1);

namespace Drupal\Tests\se_testing\Traits;

use Drupal\node\Entity\Node;
use Faker\Factory;

/**
 * Provides functions for creating content during functional tests.
 */
trait ContactTestTrait {

  /**
   * Storage for the faker data for contact.
   *
   * @var FakerFactory
   */
  protected $contact;

  /**
   * Setup basic faker fields for this test trait.
   */
  public function contactFakerSetup(): void {
    $this->faker = Factory::create();

    $original                     = error_reporting(0);
    $this->contact->name          = $this->faker->realText(50);
    $this->contact->phoneNumber   = $this->faker->phoneNumber;
    $this->contact->mobileNumber  = $this->faker->phoneNumber;
    $this->contact->streetAddress = $this->faker->streetAddress;
    $this->contact->suburb        = $this->faker->city;
    $this->contact->state         = $this->faker->stateAbbr;
    $this->contact->postcode      = $this->faker->postcode;
    $this->contact->url           = $this->faker->url;
    $this->contact->email         = $this->faker->email;
    $this->contact->companyEmail  = $this->faker->companyEmail;
    error_reporting($original);
  }

  /**
   * Add a contact node.
   *
   * @param \Drupal\node\Entity\Node $test_customer
   *   The customer to add the contact to.
   *
   * @return \Drupal\node\Entity\Node
   *   The node to return.
   *
   * @throws \Drupal\Core\Entity\EntityMalformedException
   */
  public function addContact(Node $test_customer): Node {
    $this->contactFakerSetup();

    /** @var \Drupal\node\Entity\Node $node */
    $node = $this->createNode([
      'type' => 'se_contact',
      'title' => $this->contact->name,
      'se_co_phone' => $this->contact->phoneNumber,
      'se_bu_ref' => ['target_id' => $test_customer->id()],
    ]);
    $this->assertNotEqual($node, FALSE);
    $this->drupalGet($node->toUrl());

    $this->assertSession()->statusCodeEquals(200);

    $content = $this->getTextContent();
    $this->assertStringNotContainsString('Please fill in this field', $content);

    // Check that what we entered is shown.
    $this->assertStringContainsString($this->contact->name, $content);
    $this->assertStringContainsString($this->contact->phoneNumber, $content);
    $this->assertStringContainsString($this->customer->name, $content);

    return $node;
  }

}
