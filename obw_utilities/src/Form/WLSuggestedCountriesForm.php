<?php

namespace Drupal\obw_utilities\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Class WLSuggestedCountriesForm.
 *
 * @package \Drupal\obw_utilities\Form
 *
 *
 */
class WLSuggestedCountriesForm extends ConfigFormBase {

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return [
      'obw_utilities.wl_suggested_countries.config',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'wl_suggested_countries_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $config = $this->config('obw_utilities.wl_suggested_countries.config');

    if (\Drupal::currentUser()
      ->hasPermission('edit wl suggested countries config')) {
      $form['markup'] = [
        '#type' => 'markup',
        '#markup' => '<h1>Configure the suggested countries for Country filter in Wildlife Map.</h1>',
      ];
      if(!empty($config->get('WL_SUGGESTED_COUNTRIES'))) {
        $etm = \Drupal::entityTypeManager();
        $countries_list = $etm->getStorage('node')
          ->loadMultiple(array_column($config->get('WL_SUGGESTED_COUNTRIES'), 'target_id'));
      }
      $form['countries_list'] = [
        '#type' => 'entity_autocomplete',
        '#target_type' => 'node',
        '#tags' => TRUE,
        '#selection_settings' => [
          'target_bundles' => ['region'],
        ],
        '#multiple' => TRUE,
        '#default_value' => !empty($countries_list) ? $countries_list : [],
        '#description' => t('This input is autocomplete. Each country is separated by comma.'),
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
    $this->config('obw_utilities.wl_suggested_countries.config')
      ->set('WL_SUGGESTED_COUNTRIES', $form_state->getValue('countries_list'))
      ->save();

    parent::submitForm($form, $form_state);
  }

}
