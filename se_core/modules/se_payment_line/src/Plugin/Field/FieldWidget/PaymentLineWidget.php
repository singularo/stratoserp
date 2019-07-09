<?php

namespace Drupal\se_payment_line\Plugin\Field\FieldWidget;

use Drupal\Core\Annotation\Translation;
use Drupal\Core\Field\Annotation\FieldWidget;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Field\Plugin\Field\FieldWidget\EntityReferenceAutocompleteWidget;
use Drupal\Core\Form\FormStateInterface;

/**
 * Plugin implementation of the 'se_payment_line_widget' widget.
 *
 * @FieldWidget(
 *   id = "se_payment_line_widget",
 *   label = @Translation("Payment line widget"),
 *   field_types = {
 *     "se_payment_line"
 *   }
 * )
 */
class PaymentLineWidget extends EntityReferenceAutocompleteWidget {

  /**
   * {@inheritdoc}
   */
  public function formElement(FieldItemListInterface $items, $delta, array $element, array &$form, FormStateInterface $form_state) {
    $build = parent::formElement($items, $delta, $element, $form, $form_state);

//    $settings = $this->getFieldSettings();
//    $available = DynamicEntityReferenceItem::getTargetTypes($settings);
//    $target_type = $items->get($delta)->target_type ?: reset($available);
//    $entity = $items->get($delta)->entity;

    // Changes to the target type field.
    $build['target_type']['#options']['comment'] = $this->t('Timekeeping');
    unset($build['target_type']['#title']);
    $build['target_type']['#weight'] = 2;

    // Adjust the weight of the target id field.
    $build['target_id']['#weight'] = 6;
    $build['target_id']['#attributes']['placeholder'] = t('Select invoice');

    // Add a new price field.
    $build['amount'] = [
      '#type' => 'textfield',
      '#default_value' => $items[$delta]->amount,
      '#size' => 10,
      '#maxlength' => 20,
      '#weight' => 10,
      '#required' => TRUE,
      '#attributes' => [
        'placeholder' => t('Amount')
      ]
    ];

    // When the service/item was completed/delivered/done
    $build['date'] = [
      '#type' => 'datetime',
      '#date_time_element' => 'none',
      '#default_value' => $items[$delta]->date,
      '#weight' => 10,
      '#description' => t('Payment date')
    ];

    $config = \Drupal::service('config.factory')->get('se_payment.settings');
    $vocabulary = $config->get('vocabulary');
    if (isset($vocabulary)) {
      $term_options = [];
      $term_storage = \Drupal::entityTypeManager()->getStorage('taxonomy_term');
      $terms = $term_storage->loadByProperties(['vid' => $vocabulary]);

      /** @var \Drupal\taxonomy\Entity\Term $term */
      foreach ($terms as $tid => $term) {
        $term_options[$tid] = $term->getName();
      }

      $build['payment_type'] = [
        '#type' => 'select',
        '#options' => $term_options,
        '#default_value' => $items[$delta]->payment_type,
        '#weight' => 12,
      ];
    }

    return $build;
  }

}