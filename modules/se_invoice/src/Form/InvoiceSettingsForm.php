<?php

declare(strict_types=1);

namespace Drupal\se_invoice\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Implementation of the Invoice settings form.
 *
 * @ingroup se_invoice
 */
class InvoiceSettingsForm extends FormBase {

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManager
   */
  protected $entityTypeManager;

  /**
   * Simple constructor.
   */
  public function __construct() {
    $this->entityTypeManager = \Drupal::entityTypeManager();
  }

  /**
   * Returns a unique string identifying the form.
   *
   * @return string
   *   The unique string identifying the form.
   */
  public function getFormId() {
    return 'invoice_settings';
  }

  /**
   * Form submission handler.
   *
   * @param array $form
   *   An associative array containing the structure of the form.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The current state of the form.
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {

    // Create an editable config.
    $config = \Drupal::configFactory()->getEditable('se_invoice.settings');

    // Retrieve the submitted values.
    $values = $form_state->getValues();

    // Update the value if its changed.
    if (isset($values['open_term'])
      && ($values['open_term'] !== $config->get('open_term'))) {
      if (!$term = $this->entityTypeManager->getStorage('taxonomy_term')->load($values['open_term'])) {
        $this->messenger()->addError('Invalid term for invoice status, unable to update.');
        return;
      }

      // Now set the last one as the default for the field.
      $field = $this->entityTypeManager->getStorage('field_config')->load('se_invoice.se_invoice.se_status_ref');
      $field->setDefaultValue(['target_uuid' => $term->uuid()]);
      $field->save();

      $config->set('open_term', $values['open_term']);
      $config->save();

      $this->messenger()->addMessage(t('Invoice open term updated to %open_term', ['%open_term' => $term->label()]));
    }

    // Update the value if its changed.
    if (isset($values['closed_term'])
      && ($values['closed_term'] !== $config->get('closed_term'))) {
      if (!$term = $this->entityTypeManager->getStorage('taxonomy_term')->load($values['closed_term'])) {
        $this->messenger()->addError('Invalid term for invoice status, unable to update.');
        return;
      }

      $config->set('closed_term', $values['closed_term']);
      $config->save();

      $this->messenger()->addMessage(t('Invoice closed term updated to %closed_term', ['%closed_term' => $term->label()]));
    }
  }

  /**
   * Defines the settings form for Invoice entities.
   *
   * @param array $form
   *   An associative array containing the structure of the form.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The current state of the form.
   *
   * @return array
   *   Form definition array.
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form['invoice_settings']['#markup'] = 'Default settings form for Invoice entities. Manage field settings here.';

    $config = $this->config('se_invoice.settings');
    $termOptions = [];

    // Retrieve the field and then the vocab.
    $field = $this->entityTypeManager->getStorage('field_config')->load('se_invoice.se_invoice.se_status_ref');
    $vocabulary = reset($field->getSettings()['handler_settings']['target_bundles']);

    $terms = $this->entityTypeManager->getStorage('taxonomy_term')->loadByProperties(['vid' => $vocabulary]);

    /**
     * @var \Drupal\taxonomy\Entity\Term $term
     */
    foreach ($terms as $tid => $term) {
      $termOptions[$tid] = $term->getName();
    }

    $form['open_term'] = [
      '#title' => $this->t('Select invoice open term.'),
      '#type' => 'select',
      '#options' => $termOptions,
      '#default_value' => $config->get('open_term'),
      '#description' => t("The vocabulary can be changed in the field configuration for 'Type'"),
    ];

    $form['closed_term'] = [
      '#title' => $this->t('Select invoice closed term.'),
      '#type' => 'select',
      '#options' => $termOptions,
      '#default_value' => $config->get('closed_term'),
      '#description' => t("The vocabulary can be changed in the field configuration for 'Type'"),
    ];

    $form['actions']['#type'] = 'actions';
    $form['actions']['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Save configuration'),
      '#button_type' => 'primary',
    ];

    return $form;
  }

}
