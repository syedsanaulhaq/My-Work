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
 *  Alter form to add validate
 *
 * Class NodeStoryForm
 *
 * @package Drupal\obw_utilities\Form
 */
class NodeCollectionForm extends FormAlterBase {

  /**
   * @param array $form
   * @param FormStateInterface $form_state
   */
  public function alterForm(array &$form, FormStateInterface $form_state) {
    array_unshift($form['#validate'], [$this, 'validateRequireField']);

  }

  /**
   * validate required field for serries form
   *
   * @param $form
   * @param FormStateInterface $form_state
   */
  public function validateRequireField(&$form, \Drupal\Core\Form\FormStateInterface $form_state) {
    $field_format = $form_state->getValue('field_series_format');
    $field_story = $form_state->getValue('field_story_collection');
    $count_selected_story = 0;
    if (!empty($field_story['target_id'])) {
      $count_selected_story = count(explode(',', $field_story['target_id']));
    }
    if (4 > $count_selected_story && (isset($field_format) && $field_format[0]['value'] !== 'vr')) {
      $form_state->setErrorByName('field_story_collection', t('Please select at least 4 stories.'));
    }
  }


}
