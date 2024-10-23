<?php

namespace Drupal\obw_utilities\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Configure Our Better World Utilities settings for this site.
 */
class ContrastModeForm extends ConfigFormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'obw_utilities_contrast_mode';
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return ['obw_utilities.settings'];
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form['contrast_header'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Enable contrast to header'),
      '#default_value' => $this->config('obw_utilities.settings')->get('contrast_header'),
    ];

    $form['contrast_subscribe'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Enable contrast to subscribe form'),
      '#default_value' => $this->config('obw_utilities.settings')->get('contrast_subscribe'),
    ];

    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    parent::validateForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $this->config('obw_utilities.settings')
      ->set('contrast_header', $form_state->getValue('contrast_header'))
      ->set('contrast_subscribe', $form_state->getValue('contrast_subscribe'))
      ->save();
    parent::submitForm($form, $form_state);
  }

}
