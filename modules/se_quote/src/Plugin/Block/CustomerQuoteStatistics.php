<?php

declare(strict_types=1);

namespace Drupal\se_quote\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\se_report\ReportUtilityTrait;

/**
 * Provides a "Customer quote statistics" block.
 *
 * @Block(
 *   id = "customer_quote_statistics",
 *   admin_label = @Translation("Customer quote statistics"),
 * )
 */
class CustomerQuoteStatistics extends BlockBase {

  use ReportUtilityTrait;

  /**
   * Customer quote statistics block builder.
   */
  public function build() {
    $content = FALSE;
    $datasets = [];

    /** @var \Drupal\Core\Entity\EntityInterface $node */
    if (!$node = $this->getCurrentControllerEntity()) {
      return [];
    }

    if ($node->bundle() !== 'se_customer') {
      return [];
    }

    for ($i = 5; $i >= 0; $i--) {
      $year = date('Y') - $i;
      $month_data = [];
      $fg_colors = [];
      [$fg_color] = $this->generateColorsDarkening(100, NULL, 50);

      foreach ($this->reportingMonths($year) as $month => $timestamps) {
        $query = \Drupal::entityQuery('node');
        $query->condition('type', 'se_quote');
        $query->condition('se_bu_ref', $node->id());
        $query->condition('created', $timestamps['start'], '>=');
        $query->condition('created', $timestamps['end'], '<');
        $entity_ids = $query->execute();
        $quotes = \Drupal::entityTypeManager()
          ->getStorage('node')
          ->loadMultiple($entity_ids);
        $total = 0;
        if (count($quotes)) {
          $content = TRUE;
        }
        /** @var \Drupal\node\Entity\Node $quote */
        foreach ($quotes as $quote) {
          if (is_object($quote) && $quote->hasField('se_qu_total')) {
            $total += $quote->se_qu_total->value;
          }
        }
        $month_data[] = \Drupal::service('se_accounting.currency_format')->formatRaw((int) ($total ?? 0));
        $fg_colors[] = $fg_color;
      }

      $datasets[] = [
        'label' => $year,
        'data' => $month_data,
        'backgroundColor' => $fg_colors,
        'borderColor' => $fg_colors,
        'hoverBackgroundColor' => $fg_colors,
        'fill' => FALSE,
        'hover' => [
          'mode' => 'dataset',
        ],
        'pointRadius' => 5,
        'pointHoverRadius' => 10,
      ];
    }

    if (!$content) {
      return [];
    }

    $build['quote_statistics_customer'] = [
      '#data' => [
        'labels' => array_keys($this->reportingMonths()),
        'datasets' => $datasets,
      ],
      '#graph_type' => 'line',
      '#options' => [
        'tooltips' => [
          'mode' => 'point',
        ],
        'hover' => [
          'mode' => 'dataset',
        ],
      ],
      '#id' => 'quote_statistics_customer',
      '#type' => 'chartjs_api',
      '#cache' => [
        'max-age' => 0,
      ],
    ];

    return $build;
  }

}
