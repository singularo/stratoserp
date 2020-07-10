<?php

declare(strict_types=1);

namespace Drupal\Tests\se_stock\Functional;

use Drupal\Core\Entity\EntityStorageException;
use Drupal\se_item\Entity\Item;
use Drupal\Tests\se_item\Traits\ItemTestTrait;
use Drupal\Tests\se_testing\Functional\FunctionalTestBase;

/**
 * @coversDefault Drupal\se_stock
 * @group se_stock
 * @group stratoserp
 */
class StockItemCrudTest extends FunctionalTestBase {

  use ItemTestTrait;

  protected $staff;

  /**
   *
   */
  public function testAddStockItem(): void {

    $staff = $this->setupStaffUser();

    $this->drupalLogin($staff);

    $item = $this->addStockItem();

    $this->drupalLogout();
  }

  private function addStockItem(): Item {
    return $this->createItemContent([
      'name' => $this->faker->name,
    ]);
  }

}
