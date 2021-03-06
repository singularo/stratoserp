<?php

namespace Drupal\se_ticket\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Implementation of the Ticket settings form.
 *
 * @ingroup se_ticket
 */
class TicketSettingsForm extends FormBase {

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
    return 'ticket_settings';
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
    $config = \Drupal::configFactory()->getEditable('se_ticket.settings');

    // Retrieve the submitted values.
    $values = $form_state->getValues();

    // Update the value if its changed.
    if (isset($values['se_ticket_priority'])
      && ($values['se_ticket_priority'] !== $config->get('se_ticket_priority'))) {
      if (!$term = $this->entityTypeManager->getStorage('taxonomy_term')->load($values['se_ticket_priority'])) {
        $this->messenger()->addError('Invalid term for ticket priority, unable to update.');
        return;
      }

      // Now set the last one as the default for the field.
      $field = $this->entityTypeManager->getStorage('field_config')->load('se_ticket.se_ticket.se_ti_priority_ref');
      $field->setDefaultValue(['target_uuid' => $term->uuid()]);
      $field->save();

      $config->set('se_ticket_priority', $values['se_ticket_priority']);
      $config->save();

      $this->messenger()->addMessage(t('Ticket priority term updated to %priority_term', ['%priority_term' => $term->label()]));
    }

    // Update the value if its changed.
    if (isset($values['se_ticket_type'])
      && ($values['se_ticket_type'] !== $config->get('se_ticket_type'))) {
      if (!$term = $this->entityTypeManager->getStorage('taxonomy_term')->load($values['se_ticket_type'])) {
        $this->messenger()->addError('Invalid term for ticket type, unable to update.');
        return;
      }

      // Now set the last one as the default for the field.
      $field = $this->entityTypeManager->getStorage('field_config')->load('se_ticket.se_ticket.se_ti_type_ref');
      $field->setDefaultValue(['target_uuid' => $term->uuid()]);
      $field->save();

      $config->set('se_ticket_type', $values['se_ticket_type']);
      $config->save();

      $this->messenger()->addMessage(t('Ticket type term updated to %type_term', ['%type_term' => $term->label()]));
    }

    // Update the value if its changed.
    if (isset($values['se_ticket_status'])
      && ($values['se_ticket_status'] !== $config->get('se_ticket_status'))) {
      if (!$term = $this->entityTypeManager->getStorage('taxonomy_term')->load($values['se_ticket_status'])) {
        $this->messenger()->addError('Invalid term for ticket status, unable to update.');
        return;
      }

      // Now set the last one as the default for the field.
      $field = $this->entityTypeManager->getStorage('field_config')->load('se_ticket.se_ticket.se_ti_status_ref');
      $field->setDefaultValue(['target_uuid' => $term->uuid()]);
      $field->save();

      $config->set('se_ticket_status', $values['se_ticket_status']);
      $config->save();

      $this->messenger()->addMessage(t('Ticket type term updated to %status_term', ['%status_term' => $term->label()]));
    }
  }

  /**
   * Defines the settings form for Ticket entities.
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
    $form['ticket_settings']['#markup'] = 'Settings form for Ticket entities. Manage field settings here.';

    $config = $this->config('se_ticket.settings');
    $priorityOptions = $typeOptions = $statusOptions = [];

    // Retrieve the field and then the vocab.
    $field = $this->entityTypeManager->getStorage('field_config')->load('se_ticket.se_ticket.se_ti_priority_ref');
    $vocabulary = reset($field->getSettings()['handler_settings']['target_bundles']);
    $terms = $this->entityTypeManager->getStorage('taxonomy_term')->loadByProperties(['vid' => $vocabulary]);
    /**
     * @var \Drupal\taxonomy\Entity\Term $term
     */
    foreach ($terms as $tid => $term) {
      $priorityOptions[$tid] = $term->getName();
    }

    $form['se_ticket_priority'] = [
      '#title' => $this->t('Select default ticket priority.'),
      '#type' => 'select',
      '#options' => $priorityOptions,
      '#default_value' => $config->get('se_ticket_priority'),
      '#description' => t("The vocabulary can be changed in the field configuration for 'Type'"),
    ];

    // Retrieve the field and then the vocab.
    $field = $this->entityTypeManager->getStorage('field_config')->load('se_ticket.se_ticket.se_ti_type_ref');
    $vocabulary = reset($field->getSettings()['handler_settings']['target_bundles']);
    $terms = $this->entityTypeManager->getStorage('taxonomy_term')->loadByProperties(['vid' => $vocabulary]);
    /**
     * @var \Drupal\taxonomy\Entity\Term $term
     */
    foreach ($terms as $tid => $term) {
      $typeOptions[$tid] = $term->getName();
    }

    $form['se_ticket_type'] = [
      '#title' => $this->t('Select default ticket type.'),
      '#type' => 'select',
      '#options' => $typeOptions,
      '#default_value' => $config->get('se_ticket_type'),
      '#description' => t("The vocabulary can be changed in the field configuration for 'Type'"),
    ];

    // Retrieve the field and then the vocab.
    $field = $this->entityTypeManager->getStorage('field_config')->load('se_ticket.se_ticket.se_ti_status_ref');
    $vocabulary = reset($field->getSettings()['handler_settings']['target_bundles']);
    $terms = $this->entityTypeManager->getStorage('taxonomy_term')->loadByProperties(['vid' => $vocabulary]);
    /**
     * @var \Drupal\taxonomy\Entity\Term $term
     */
    foreach ($terms as $tid => $term) {
      $statusOptions[$tid] = $term->getName();
    }

    $form['se_ticket_status'] = [
      '#title' => $this->t('Select default ticket status.'),
      '#type' => 'select',
      '#options' => $statusOptions,
      '#default_value' => $config->get('se_ticket_status'),
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
