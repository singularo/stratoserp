<?php

declare(strict_types=1);

namespace Drupal\se_invoice\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Datetime\DateFormatterInterface;
use Drupal\Core\Entity\EntityRepositoryInterface;
use Drupal\Core\Render\RendererInterface;
use Drupal\node\Entity\Node;
use Drupal\node\NodeTypeInterface;
use Drupal\stratoserp\ErpCore;
use Drupal\se_item\Entity\Item;
use Drupal\taxonomy\Entity\Term;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Returns responses for Node routes.
 */
class NodeController extends ControllerBase {

  /**
   * The date formatter service.
   *
   * @var \Drupal\Core\Datetime\DateFormatterInterface
   */
  protected $dateFormatter;

  /**
   * The renderer service.
   *
   * @var \Drupal\Core\Render\RendererInterface
   */
  protected $renderer;

  /**
   * The entity repository service.
   *
   * @var \Drupal\Core\Entity\EntityRepositoryInterface
   */
  protected $entityRepository;

  /**
   * Constructs a NodeController object.
   *
   * @param \Drupal\Core\Datetime\DateFormatterInterface $date_formatter
   *   The date formatter service.
   * @param \Drupal\Core\Render\RendererInterface $renderer
   *   The renderer service.
   * @param \Drupal\Core\Entity\EntityRepositoryInterface $entity_repository
   *   The entity repository.
   */
  public function __construct(DateFormatterInterface $date_formatter, RendererInterface $renderer, EntityRepositoryInterface $entity_repository = NULL) {
    $this->dateFormatter = $date_formatter;
    $this->renderer = $renderer;
    if (!$entity_repository) {
      $entity_repository = \Drupal::service('entity.repository');
    }
    $this->entityRepository = $entity_repository;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('date.formatter'),
      $container->get('renderer'),
      $container->get('entity.repository')
    );
  }

  /**
   * Provides the node submission form for creation from a quote.
   *
   * @param \Drupal\node\NodeTypeInterface $node_type
   *   The node type entity for the node.
   * @param \Drupal\node\Entity\Node $source
   *   Source node to copy data from.
   *
   * @return array
   *   A node submission form.
   *
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   */
  public function add(NodeTypeInterface $node_type, Node $source) {
    /** @var \Drupal\node\Entity\Node $node */
    $node = $this->entityTypeManager()->getStorage('node')->create([
      'type' => $node_type->id(),
    ]);

    $total = 0;
    $source_field_type = 'se_' . ErpCore::ITEM_LINE_NODE_BUNDLE_MAP[$source->bundle()];
    $bundle_field_type = 'se_' . ErpCore::ITEM_LINE_NODE_BUNDLE_MAP[$node->bundle()];

    // TODO: Make this a service?
    foreach ($source->{$source_field_type . '_lines'} as $index => $item) {
      $node->{$bundle_field_type . '_lines'}->appendItem($item->getValue());
    }

    $node->se_bu_ref->target_id = $source->se_bu_ref->target_id;
    $node->se_co_ref->target_id = $source->se_co_ref->target_id;
    $node->{$bundle_field_type . '_quote_ref'}->target_id = $source->id();
    $node->{$bundle_field_type . '_total'} = $total;

    return $this->entityFormBuilder()->getForm($node);
  }

  /**
   * Provides the node submission form for creation from timekeeping entries.
   *
   * @param \Drupal\node\NodeTypeInterface $node_type
   *   The node type entity for the node.
   * @param \Drupal\node\Entity\Node $source
   *   The source node.
   *
   * @return array
   *   A node submission form.
   *
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   */
  public function timekeeping(NodeTypeInterface $node_type, Node $source) {
    /** @var \Drupal\node\Entity\Node $node */
    $node = $this->entityTypeManager()->getStorage('node')->create([
      'type' => $node_type->id(),
    ]);

    $default_status = \Drupal::config('se_invoice.settings')->get('invoice_status_term');
    $open = Term::load($default_status);

    // Retrieve a list of non billed timekeeping entries for this customer.
    $query = \Drupal::entityQuery('comment');

    if ($source->bundle() !== 'se_customer') {
      if (!$customer_id = $source->se_bu_ref->target_id) {
        return $this->entityFormBuilder()->getForm($node);
      }
    }

    $query->condition('comment_type', 'se_timekeeping');
    $query->condition('se_bu_ref', $source->id());
    $query->condition('se_tk_billed', TRUE, '<>');
    $query->condition('se_tk_billable', TRUE);
    $query->condition('se_tk_amount', 0, '>');
    $entity_ids = $query->execute();

    $total = 0;
    $lines = [];
    $bundle_field_type = 'se_' . ErpCore::ITEM_LINE_NODE_BUNDLE_MAP[$node->bundle()];

    // Loop through the timekeeping entries and setup invoice lines.
    foreach ($entity_ids as $entity_id) {
      /** @var \Drupal\comment\Entity\Comment $comment */
      if ($comment = $this->entityTypeManager()->getStorage('comment')->load($entity_id)) {
        /** @var \Drupal\se_item\Entity\Item $item */
        if ($item = Item::load($comment->se_tk_item->target_id)) {
          $price = $item->se_it_sell_price->value;
          $line = [
            'target_type' => 'comment',
            'target_id' => $comment->id(),
            'quantity' => round($comment->se_tk_amount->value / 60, 2),
            'notes' => $comment->se_tk_comment->value,
            'format' => $comment->se_tk_comment->format,
            'price' => $price,
          ];
          $lines[] = $line;
          $total += $line['quantity'] * $line['price'];
        }
        else {
          \Drupal::logger('se_timekeeping')->error('No matching item for entry @cid', ['@cid' => $comment->id()]);
        }
      }
    }

    $node->{$bundle_field_type . '_lines'} = $lines;
    $node->{$bundle_field_type . '_total'} = $total;

    if ($open) {
      $node->se_status_ref->target_id = $open->id();
    }

    return $this->entityFormBuilder()->getForm($node);
  }

}
