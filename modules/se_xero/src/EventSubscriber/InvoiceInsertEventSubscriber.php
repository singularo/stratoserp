<?php

declare(strict_types=1);

namespace Drupal\se_xero\EventSubscriber;

use Drupal\core_event_dispatcher\Event\Entity\EntityInsertEvent;
use Drupal\core_event_dispatcher\Event\Entity\EntityUpdateEvent;
use Drupal\hook_event_dispatcher\HookEventDispatcherInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Class InvoiceInsertEventSubscriber.
 *
 * When an invoice is saved, sync it through to xero.
 *
 * @package Drupal\se_xero\EventSubscriber
 */
class InvoiceInsertEventSubscriber implements EventSubscriberInterface {

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents(): array {
    return [
      HookEventDispatcherInterface::ENTITY_INSERT => 'invoiceInsert',
      HookEventDispatcherInterface::ENTITY_UPDATE => 'invoiceUpdate',
    ];
  }

  /**
   * Add a new invoice to Xero.
   *
   * @param \Drupal\core_event_dispatcher\Event\Entity\EntityInsertEvent $event
   *   The event we are working with.
   */
  public function invoiceInsert(EntityInsertEvent $event): void {
    /** @var \Drupal\node\Entity\Node $entity */
    $entity = $event->getEntity();
    if (isset($entity->skipInvoiceXeroEvents)) {
      return;
    }

    // TODO Check for the xero uuid instead?
    if ($entity->bundle() === 'se_customer' && !$entity->get('se_xero_uuid')) {
      \Drupal::service('se_xero.invoice_service')->sync($entity);
    }
  }

  /**
   * Update an existing invoice in Xero.
   *
   * @param \Drupal\core_event_dispatcher\Event\Entity\EntityUpdateEvent $event
   *   The event we are working with.
   */
  public function invoiceUpdate(EntityUpdateEvent $event): void {
    /** @var \Drupal\node\Entity\Node $entity */
    $entity = $event->getEntity();
    if (isset($entity->skipInvoiceXeroEvents)) {
      return;
    }

    if ($entity->bundle() === 'se_customer' && !$entity->get('se_xero_uuid')) {
      \Drupal::service('se_xero.invoice_service')->sync($entity);
    }

  }

}
