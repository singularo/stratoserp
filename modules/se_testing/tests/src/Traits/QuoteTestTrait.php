<?php

declare(strict_types=1);

namespace Drupal\Tests\se_testing\Traits;

use Drupal\node\Entity\Node;
use Faker\Factory;

/**
 * Provides functions for creating content during functional tests.
 */
trait QuoteTestTrait {

  /**
   * Storage for the faker data for quote.
   *
   * @var FakerFactory
   */
  protected $quote;

  /**
   * Setup basic faker fields for this test trait.
   */
  public function quoteFakerSetup(): void {
    $this->faker = Factory::create();

    $original                   = error_reporting(0);
    $this->quote->name          = $this->faker->text;
    $this->quote->phoneNumber   = $this->faker->phoneNumber;
    $this->quote->mobileNumber  = $this->faker->phoneNumber;
    $this->quote->streetAddress = $this->faker->streetAddress;
    $this->quote->suburb        = $this->faker->city;
    $this->quote->state         = $this->faker->stateAbbr;
    $this->quote->postcode      = $this->faker->postcode;
    $this->quote->url           = $this->faker->url;
    $this->quote->companyEmail  = $this->faker->companyEmail;
    error_reporting($original);
  }

  /**
   * Add a quote node.
   *
   * @param \Drupal\node\Entity\Node $test_customer
   *   The customer to associate the node with.
   * @param array $items
   *   An array of items to use for invoice lines.
   *
   * @return \Drupal\node\Entity\Node
   *   The node to return.
   *
   * @throws \Drupal\Core\Entity\EntityMalformedException
   */
  public function addQuote(Node $test_customer, array $items = []): Node {
    $this->quoteFakerSetup();

    $lines = [];
    foreach ($items as $item) {
      $line = [
        'target_type' => 'se_item',
        'target_id' => $item['item']->id(),
        'quantity' => $item['quantity'],
      ];
      $lines[] = $line;
    }

    /** @var \Drupal\node\Entity\Node $node */
    $node = $this->createNode([
      'type' => 'se_quote',
      'title' => $this->quote->name,
      'se_bu_ref' => ['target_id' => $test_customer->id()],
      'se_qu_phone' => $this->quote->phoneNumber,
      'se_qu_email' => $this->quote->companyEmail,
      'se_qu_lines' => $lines,
    ]);

    $this->assertNotEqual($node, FALSE);
    $this->drupalGet($node->toUrl());
    $this->assertSession()->statusCodeEquals(200);

    $content = $this->getTextContent();

    $this->assertStringNotContainsString('Please fill in this field', $content);

    // Check that what we entered is shown.
    $this->assertStringContainsString($this->quote->name, $content);

    return $node;
  }

}
