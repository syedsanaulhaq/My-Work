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
class NodeLandingForm extends FormAlterBase {

  /**
   * @param array $form
   * @param FormStateInterface $form_state
   */
  public function alterForm(array &$form, FormStateInterface $form_state) {
    /**
     * field_landing_format:
     * '1' => 'resource_landing_page',
     * '2' => 'resource_wild_life',
     * '3' => 'animals_landing',
     * '4' => 'covid_19',
     * '5' => 'humankind',
     * '6' => 'obw_birth8',
     * '7' => 'migrant_workers',
     * '8' => 'mental_health_2020'
     */
    $form['field_landing_whatnext_thumbnail']['#states'] = [
      'visible' => [
        ':input[name="field_landing_format"]' => ['value' => '3'],
      ],
    ];

    $form['field_using_year_1_2_template']['#states'] = [
      'visible' => [
        ':input[name="field_landing_format"]' => ['value' => '8'],
      ],
    ];

    $form['field_landing_ngo']['#states'] = [
      'visible' => [
        [
          ':input[name="field_landing_format"]' => ['value' => '12'],
        ],
      ],
    ];

    $form['field_landing_galleries']['#states'] = [
      'visible' => [
        [
          ':input[name="field_landing_format"]' => ['value' => '2'],
        ],
        'or',
        [
          ':input[name="field_landing_format"]' => ['value' => '10'],
        ],
      ],
    ];

    $form['field_landing_photo_essay']['#states'] = [
      'visible' => [
        [
          ':input[name="field_landing_format"]' => ['value' => '2'],
        ],
        'or',
        [
          ':input[name="field_landing_format"]' => ['value' => '10'],
        ],
        'or',
        [
          ':input[name="field_landing_format"]' => ['value' => '16'],
        ],
      ],
    ];

    $form['field_landing_comission_stories']['#states'] = [
      'visible' => [
        ':input[name="field_landing_format"]' => ['value' => '5'],
      ],
    ];


    $form['field_landing_narrative_ref']['#states'] = [
      'visible' => [
        ':input[name="field_landing_format"]' => ['value' => '13'],
      ],
    ];


    $form['field_landing_resource_stories']['#states'] = [
      'visible' => [
        [
          ':input[name="field_landing_format"]' => ['value' => '8'],
        ],
        'or',
        [
          ':input[name="field_landing_format"]' => ['value' => '12'],
        ],
        'or',
        [
          ':input[name="field_landing_format"]' => ['value' => '13'],
        ],
      ],
    ];

    $form['field_landing_persona_list']['#states'] = [
      'visible' => [
        ':input[name="field_landing_format"]' => ['value' => '6'],
      ],
    ];

    $form['field_landing_list_stories']['#states'] = [
      'visible' => [
        [':input[name="field_landing_format"]' => ['value' => '2']],
        'or',
        [':input[name="field_landing_format"]' => ['value' => '3']],
        'or',
        [':input[name="field_landing_format"]' => ['value' => '9']],
        'or',
        [':input[name="field_landing_format"]' => ['value' => '16']],
      ],
    ];
  }


}
