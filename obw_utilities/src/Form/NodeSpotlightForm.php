<?php


namespace Drupal\obw_utilities\Form;

use Drupal\Core\Form\FormStateInterface;
use Drupal\form_alter_service\FormAlterBase;

/**
 *  Alter form to add css class and submit action
 *
 * Class NodeSpotlightForm
 *
 * @package Drupal\obw_utilities\Form
 */
class NodeSpotlightForm extends FormAlterBase {

  /**
   * @param array $form
   * @param FormStateInterface $form_state
   */
  public function alterForm(array &$form, FormStateInterface $form_state) {
    /**
     * field_spotlight_format
     * '1' => 'default',
     * '2' => 'humankind_static',
     * '3' => 'humankind_commission',
     * '4' => 'covid-19'
     */
    $form['field_spotlight_html']['#states'] = [
      'visible' => [
        ':input[name="field_spotlight_format"]' => ['value' => '2'],
      ],
    ];

    $form['field_story_spotlight']['#states'] = [
      'invisible' => [
        ':input[name="field_spotlight_format"]' => ['value' => '2'],
      ],
    ];

    $form['field_spotlight_campaign']['#states'] = [
      'invisible' => [
        ':input[name="field_spotlight_format"]' => ['value' => '2'],
      ],
    ];

    $form['field_story_category']['#states'] = [
      'visible' => [
        ':input[name="field_spotlight_static[value]"]' => ['checked' => FALSE],
      ],
    ];

    $form['field_humankind_tag']['#states'] = [
      'invisible' => [
        ':input[name="field_spotlight_format"]' => ['value' => '2'],
      ],
    ];

    $form['field_spotlight_comissioned_type']['#states'] = [
      'visible' => [
        ':input[name="field_spotlight_format"]' => ['value' => '3'],
      ],
    ];

    $form['field_humankind_tag']['#states'] = [
      'visible' => [
        ':input[name="field_spotlight_format"]' => ['value' => '3'],
      ],
    ];

    $form['field_spotlight_tag_label']['#states'] = [
      'visible' => [
        ':input[name="field_spotlight_format"]' => ['value' => '4'],
      ],
    ];
  }


}
