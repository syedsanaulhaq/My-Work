<?php

/**
 * Created by PhpStorm.
 * User: leopham
 * Date: 8/28/18
 * Time: 2:02 PM
 */

namespace Drupal\obw_utilities\Form;

use Drupal\Core\Form\FormStateInterface;
use Drupal\form_alter_service\FormAlterBase;

/**
 *
 *
 * Class UserLoginForm
 *
 * @package Drupal\obw_utilities\Form
 */
class ViewsExposedForm extends FormAlterBase {


  /**
   * @param array $form
   * @param FormStateInterface $form_state
   */
  public function alterForm(array &$form, FormStateInterface $form_state) {
    switch ($form['#id']) {
      case 'views-exposed-form-search-view-search-page' :
        $this->alterSearchForm($form, $form_state);
        break;
    }
  }

  public function alterSearchForm(array &$form, FormStateInterface $formState) {
    $form['#theme'] = ['views_exposed_form_elastic_search'];
    $form['keyword']['#placeholder'] = t('Enter search terms');
    $form['actions']['submit']['#value'] = t('Search');
  }

}
