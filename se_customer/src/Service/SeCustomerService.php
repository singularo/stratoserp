<?php

namespace Drupal\se_customer\Service;

use Drupal\Core\Config\ConfigFactory;
use Drupal\Core\Entity\EntityTypeManager;
use Drupal\node\Entity\Node;
use Drupal\taxonomy\Entity\Term;

class SeCustomerService {

  /**
   * The config factory.
   *
   * @var $configFactory
   */
  protected $configFactory;

  /**
   * The entity type manager.
   *
   * @var $entityTypeManager
   */
  protected $entityTypeManager;

  /**
   * SeContactService constructor.
   *
   * @param \Drupal\Core\Config\ConfigFactory $config_factory
   * @param \Drupal\Core\Entity\EntityTypeManager $entity_type_manager
   */
  public function __construct(ConfigFactory $config_factory, EntityTypeManager $entity_type_manager) {
    $this->configFactory = $config_factory;
    $this->entityTypeManager = $entity_type_manager;
  }

  /**
   * Given any node with a customer, return the customer.
   *
   * @param \Drupal\node\Entity\Node $node
   *
   * @return array|bool|int
   */
  public function lookupCustomer(Node $node) {
    foreach ($node->referencedEntities() as $entity) {
      if ($entity->bundle() == 'se_customer') {
        return $entity;
      }
    }
    return FALSE;
  }
}