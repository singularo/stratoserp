<?php

declare(strict_types=1);

namespace Drupal\Tests\se_item\Traits;

use Drupal\se_item\Entity\Item;
use Drupal\user\Entity\User;
use Faker\Factory;

/**
 * Provides functions for creating content during functional tests.
 */
trait ItemTestTrait {

  /**
   * Storage for the faker data for an item.
   *
   * @var \Faker\Factory
   */
  protected $item;

  /**
   * Setup basic faker fields for this test trait.
   */
  public function itemFakerSetup(): void {
    $this->faker = Factory::create();

    $original               = error_reporting(0);
    $this->item->name       = $this->faker->realText(20);
    $this->item->code       = $this->faker->word();
    $this->item->serial     = $this->faker->randomNumber(5);
    $this->item->cost_price = $this->faker->numberBetween(5, 10);
    $this->item->sell_price = $this->item->cost_price * 1.2;
    error_reporting($original);
  }

  /**
   * Add an item entity.
   *
   * @param $type
   *   The type of item entity to add.
   *
   * @return \Drupal\se_item\Entity\Item
   *   The Item Content.
   *
   * @throws \Drupal\Core\Entity\EntityMalformedException
   */
  public function addItem($type): Item {
    $item = $this->createItem([
      'type' => $type,
      'name' => $this->item->name,
      'se_it_code' => $this->item->code,
    ]);

    $this->assertNotEqual($item, FALSE);
    $this->drupalGet($item->toUrl());
    $this->assertSession()->statusCodeEquals(200);

    $content = $this->getTextContent();

    $this->assertStringNotContainsString('Please fill in this field', $content);

    // Check that what we entered is shown.
    $this->assertStringContainsString($this->item->name, $content);

    return $item;
  }

  /**
   * Create an item entity.
   *
   * @param array $settings
   *   Content settings to use.
   *
   * @return \Drupal\se_item\Entity\Item
   *   The created Item.
   *
   * @throws \Drupal\Core\Entity\EntityStorageException
   */
  public function createItem(array $settings = []) {
    $item = $this->createItemContent($settings);
    $item->save();

    $this->markEntityForCleanup($item);

    return $item;
  }

  /**
   * Create and item entity.
   *
   * @param array $settings
   *   Content settings to use.
   *
   * @return \Drupal\Core\Entity\EntityBase|\Drupal\Core\Entity\EntityInterface|\Drupal\se_item\Entity\Item
   *   The created item.
   */
  public function createItemContent(array $settings = []) {
    $settings += [
      'type' => 'se_stock',
    ];

    if (!array_key_exists('uid', $settings)) {
      $this->item->user = User::load(\Drupal::currentUser()->id());
      if ($this->item->user) {
        $settings['uid'] = $this->item->user->id();
      }
      elseif (method_exists($this, 'setUpCurrentUser')) {
        /** @var \Drupal\user\UserInterface $user */
        $this->item->user = $this->setUpCurrentUser();
        $settings['uid'] = $this->item->user->id();
      }
      else {
        $settings['uid'] = 0;
      }
    }

    return Item::create($settings);
  }

  /**
   * Retrieve an item by its title.
   *
   * @param string $name
   *   The title to use to retrieve the item by.
   * @param bool $resetCache
   *   Whether to reset the cache first.
   *
   * @return \Drupal\Core\Entity\EntityInterface
   *   The located Item.
   *
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   */
  public function getItemByTitle($name, $resetCache = FALSE): \Drupal\Core\Entity\EntityInterface {
    if ($resetCache) {
      \Drupal::entityTypeManager()->getStorage('se_item')->resetCache();
    }
    $name = (string) $name;
    $items = \Drupal::entityTypeManager()
      ->getStorage('se_item')
      ->loadByProperties(['name' => $name]);

    return reset($items);
  }

  /**
   * Add some stock items for use in voices.
   *
   * @return array
   *   And array of items.
   *
   * @throws \Drupal\Core\Entity\EntityMalformedException
   * @throws \Exception
   */
  public function createItems() {
    // Add some stock items.
    $count = random_int(5, 10);
    $items = [];
    for ($i = 0; $i < $count; $i++) {
      $this->itemFakerSetup();
      $items[$i] = [
        'item' => $this->addItem('se_stock'),
        'quantity' => random_int(5, 10),
      ];
    }

    return $items;
  }

}
