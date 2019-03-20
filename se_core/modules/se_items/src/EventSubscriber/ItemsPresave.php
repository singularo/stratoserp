<?php

namespace Drupal\se_items\EventSubscriber;

use Drupal\hook_event_dispatcher\Event\Entity\EntityPresaveEvent;
use Drupal\hook_event_dispatcher\HookEventDispatcherInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class ItemsPresave implements EventSubscriberInterface {

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents() {
    return [
      HookEventDispatcherInterface::ENTITY_PRE_SAVE => 'itemsPreSave',
    ];
  }

  /**
   * When one of the node types that has items in it is saved,
   * calculate the total of the items for saving.
   *
   * @param EntityPresaveEvent $event
   *
   */
  public function itemsPreSave(EntityPresaveEvent $event) {
    $node = $event->getEntity();
    $total = 0;

    $bundles = [
      'se_bill'           => 'bi',
      'se_goods_receipt'  => 'gr',
      'se_invoice'        => 'in',
      'se_quote'          => 'qu',
      'se_purchase_order' => 'po',
    ];

    if (!array_key_exists($node->bundle(), $bundles)) {
      return;
    }

    /** @var \Drupal\node\Entity\Node $node */
    $items = $node->{'field_' . $bundles[$node->bundle()] . '_items'}->referencedEntities();

    foreach ($items as $ref_entity) {
      $total += $ref_entity->field_it_quantity->value * $ref_entity->field_it_price->value;
    }

    /** @var \Drupal\node\Entity\Node $entity */
    $node->{'field_' . $bundles[$node->bundle()] . '_total'}->value = $total;
  }

}