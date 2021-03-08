<?php

declare(strict_types=1);

namespace Drupal\se_invoice\Service;

use Drupal\Core\Config\ConfigFactory;
use Drupal\Core\Entity\EntityTypeManager;
use Drupal\se_business\Entity\Business;
use Drupal\se_invoice\Entity\Invoice;
use Drupal\se_payment\Traits\PaymentTrait;

/**
 * Service for various invoice related functions.
 */
class InvoiceService {

  use PaymentTrait;

  /**
   * The config factory.
   *
   * @var configFactory
   */
  protected $configFactory;

  /**
   * The entity type manager.
   *
   * @var entityTypeManager
   */
  protected $entityTypeManager;

  /**
   * SeContactService constructor.
   *
   * @param \Drupal\Core\Config\ConfigFactory $config_factory
   *   The config factory being used.
   * @param \Drupal\Core\Entity\EntityTypeManager $entity_type_manager
   *   The entity type manager being used.
   */
  public function __construct(ConfigFactory $config_factory, EntityTypeManager $entity_type_manager) {
    $this->configFactory = $config_factory;
    $this->entityTypeManager = $entity_type_manager;
  }

  /**
   * Retrieve the outstanding invoices for a business.
   *
   * @param \Drupal\se_business\Entity\Business $business
   *   The Business node.
   *
   * @return \Drupal\Core\Entity\EntityInterface[]
   *   The found entity.
   *
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   */
  public function getOutstandingInvoices(Business $business): array {
    $query = \Drupal::entityQuery('se_invoice');
    $query->condition('se_bu_ref', $business->id());
    $query->condition('se_status_ref', $this->getOpenTerm()->id());
    $entityIds = $query->execute();

    return \Drupal::entityTypeManager()
      ->getStorage('se_invoice')
      ->loadMultiple($entityIds);
  }

  /**
   * Check if an invoice should be marked as paid.
   *
   * @param \Drupal\se_invoice\Entity\Invoice $invoice
   *   The Invoice node.
   * @param string $payment
   *   The payment amount.
   */
  public function checkCloseInvoice(Invoice $invoice, $payment = NULL): Invoice {
    if ($payment === $invoice->se_in_total->value
      || $payment === $invoice->se_in_outstanding->value
      || $invoice->se_in_outstanding->value === 0) {
      $invoice->set('se_status_ref', $this->getClosedTerm());
    }
    else {
      $invoice->set('se_status_ref', $this->getOpenTerm());
    }

    $invoice->se_in_outstanding->value = $this->getInvoiceBalance($invoice);

    return $invoice;
  }

  /**
   * Retrieve the term user for open status.
   *
   * @return \Drupal\Core\Entity\EntityInterface|\Drupal\taxonomy\Entity\Term|null
   *   The term for open status.
   */
  public function getOpenTerm() {
    $term = \Drupal::configFactory()->get('se_invoice.settings')->get('open_term');
    return reset($term);
  }

  /**
   * Retrieve the term user for paid status.
   *
   * @return \Drupal\Core\Entity\EntityInterface|\Drupal\taxonomy\Entity\Term|null
   *   The term for paid status.
   */
  public function getClosedTerm() {
    $term = \Drupal::configFactory()->get('se_invoice.settings')->get('closed_term');
    return reset($term);
  }

}
