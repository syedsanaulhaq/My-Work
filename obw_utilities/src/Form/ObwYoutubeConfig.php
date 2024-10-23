<?php

namespace Drupal\obw_utilities\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Class ObwYoutubeConfig.
 *
 * @package \Drupal\obw_utilities\Form
 *
 *
 */
class ObwYoutubeConfig extends ConfigFormBase {

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return [
      'obw_utilities.youtube.config',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'obw_youtube_config_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $config = $this->config('obw_utilities.youtube.config');

    if (\Drupal::currentUser()->hasPermission('edit obw youtube config')) {
      $form['markup'] = [
        '#type' => 'markup',
        '#markup' => '<h1>OBW Youtube Configuration Form</h1><span>Refer to this <a href="https://developers.google.com/youtube/v3/docs/videos/list" target="_blank">link</a></span>',
      ];

      $form['api_key'] = [
        '#type' => 'textfield',
        '#title' => t('Youtube API key'),
        '#required' => TRUE,
        '#default_value' => $config->get('OBW_YOUTUBE_API_KEY'),
      ];

      $form['api_link'] = [
        '#type' => 'url',
        '#title' => t('Youtube API Link'),
        '#required' => TRUE,
        '#default_value' => $config->get('OBW_YOUTUBE_API_LINK'),
      ];

      $form['submit'] = [
        '#type' => 'submit',
        '#value' => t('Save'),
      ];
    }
    else {
      $form['FORBIDDEN'] = [
        '#plain_text' => t('Staffs only!'),
      ];
    }

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $this->config('obw_utilities.youtube.config')
      ->set('OBW_YOUTUBE_API_KEY', $form_state->getValue('api_key'))
      ->set('OBW_YOUTUBE_API_LINK', $form_state->getValue('api_link'))
      ->save();

    parent::submitForm($form, $form_state);
  }

}
