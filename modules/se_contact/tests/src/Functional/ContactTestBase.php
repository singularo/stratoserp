<?php

declare(strict_types=1);

namespace Drupal\Tests\se_contact\Functional;

use Drupal\Tests\se_business\Traits\BusinessTestTrait;
use Drupal\Tests\se_contact\Traits\ContactTestTrait;
use Drupal\Tests\se_testing\Functional\FunctionalTestBase;

/**
 * Perform basic contact tests.
 */
class ContactTestBase extends FunctionalTestBase {

  use ContactTestTrait;
  use BusinessTestTrait;

  /**
   * Faker factory for contact.
   *
   * @var \Faker\Factory
   */
  protected $contact;

  /**
   * Setup the basic contact tests.
   */
  protected function setUp(): void {
    parent::setUp();
    $this->contactFakerSetup();
  }

}
