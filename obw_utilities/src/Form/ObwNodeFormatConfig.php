<?php

namespace Drupal\obw_utilities\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Class ObwNodeFormatConfig.
 *
 * @package \Drupal\obw_utilities\Form
 *
 *
 */
class ObwNodeFormatConfig extends ConfigFormBase {

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return [
      'obw_utilities.node_format.config',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'obw_node_format_config_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $config = $this->config('obw_utilities.node_format.config');

    $form['markup'] = [
      '#type' => 'markup',
      '#markup' => '<h1>OBW Node Format Configuration Form</h1>',
    ];

    $form['node_story'] = [
      '#type' => 'textarea',
      '#title' => t('List format in the STORY content type'),
      '#required' => TRUE,
      '#default_value' => $config->get('OBW_NODE_STORY_FORMAT'),
      '#description' => 'The possible values this field can contain. Enter one value per line, in the format key|label.',
      '#attributes' => [
        'rows' => '10',
      ],
    ];

    $form['node_landing'] = [
      '#type' => 'textarea',
      '#title' => t('List format in the LANDING content type'),
      '#required' => TRUE,
      '#default_value' => $config->get('OBW_NODE_LANDING_FORMAT'),
      '#description' => 'The possible values this field can contain. Enter one value per line, in the format key|label.',
      '#attributes' => [
        'rows' => '10',
      ],
    ];

    $form['node_pdf'] = [
      '#type' => 'textarea',
      '#title' => t('List format in the PDF content type'),
      '#required' => TRUE,
      '#default_value' => $config->get('OBW_NODE_PDF_FORMAT'),
      '#description' => 'The possible values this field can contain. Enter one value per line, in the format key|label.',
      '#attributes' => [
        'rows' => '4',
      ],
    ];

    $form['node_event'] = [
      '#type' => 'textarea',
      '#title' => t('List format in the EVENT content type'),
      '#required' => TRUE,
      '#default_value' => $config->get('OBW_NODE_EVENT_FORMAT'),
      '#description' => 'The possible values this field can contain. Enter one value per line, in the format key|label.',
      '#attributes' => [
        'rows' => '3',
      ],
    ];

    $form['node_page'] = [
      '#type' => 'textarea',
      '#title' => t('List format in the PAGE content type'),
      '#required' => TRUE,
      '#default_value' => $config->get('OBW_NODE_PAGE_FORMAT'),
      '#description' => 'The possible values this field can contain. Enter one value per line, in the format key|label.',
      '#attributes' => [
        'rows' => '3',
      ],
    ];

    $form['node_cta'] = [
      '#type' => 'textarea',
      '#title' => t('List format in the CALL TO ACTION content type'),
      '#required' => TRUE,
      '#default_value' => $config->get('OBW_NODE_CTA_FORMAT'),
      '#description' => 'The possible values this field can contain. Enter one value per line, in the format key|label.',
      '#attributes' => [
        'rows' => '3',
      ],
    ];

    $form['submit'] = [
      '#type' => 'submit',
      '#value' => t('Save'),
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $this->config('obw_utilities.node_format.config')
      ->set('OBW_NODE_STORY_FORMAT', $form_state->getValue('node_story'))
      ->set('OBW_NODE_LANDING_FORMAT', $form_state->getValue('node_landing'))
      ->set('OBW_NODE_PDF_FORMAT', $form_state->getValue('node_pdf'))
      ->set('OBW_NODE_EVENT_FORMAT', $form_state->getValue('node_event'))
      ->set('OBW_NODE_PAGE_FORMAT', $form_state->getValue('node_page'))
      ->set('OBW_NODE_CTA_FORMAT', $form_state->getValue('node_cta'))
      ->save();

    parent::submitForm($form, $form_state);
  }

}
