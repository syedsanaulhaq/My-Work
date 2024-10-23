<?php

/**
 * Created by PhpStorm.
 * User: leopham
 * Date: 8/28/18
 * Time: 2:02 PM
 */

namespace Drupal\obw_utilities\Form;

use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Ajax\InvokeCommand;
use Drupal\Core\Ajax\ReplaceCommand;
use Drupal\Core\DependencyInjection\DependencySerializationTrait;
use Drupal\Core\Entity\EntityForm;
use Drupal\Core\Entity\EntityFormInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;
use Drupal\form_alter_service\FormAlterBase;
use Drupal\Core\Render\Markup;
/**
 *  Alter form to add css class and submit action
 *
 * Class NodeStoryForm
 *
 * @package Drupal\obw_utilities\Form
 */
class NodeStoryForm extends FormAlterBase {

  use DependencySerializationTrait;

  /**
   * @param array $form
   * @param FormStateInterface $form_state
   */
  public function alterForm(array &$form, FormStateInterface $form_state) {
    $current_user = \Drupal::currentUser();
    $role_name = $current_user->getRoles();
    $frmObj = $form_state->getFormObject();
    $story_uuid = '';
    if ($frmObj instanceof EntityForm) {
      $story_uuid = $frmObj->getEntity()->uuid();
    }

    $form['field_image_story_blur']['#states'] = [
      'visible' => [
        [
          ':input[name="field_story_format[16]"]' => ['checked' => TRUE],
        ],
        'or',
        [
          ':input[name="field_story_format[13]"]' => ['checked' => TRUE],
          ':input[name="field_story_mh2020_type"]' => ['value' => 2],
        ],
        'or',
        [
          ':input[name="field_story_format[22]"]' => ['checked' => TRUE],
          ':input[name="field_story_mh2020_type"]' => ['value' => 2],
        ],
        'or',
        [
          ':input[name="field_story_article_type"]' => ['value' => 'blog'],
        ],
      ],

    ];

    $form['field_contributors']['#states'] = [
      'invisible' => [
        ':input[name="field_story_format[16]"]' => ['checked' => TRUE],
      ],
    ];

    $form['field_story_contributor_suffix']['#states'] = [
      'invisible' => [
        ':input[name="field_story_format[16]"]' => ['checked' => TRUE],
      ],
    ];

    $form['field_story_subject_profile']['#states'] = [
      'invisible' => [
        ':input[name="field_story_format[16]"]' => ['checked' => TRUE],
      ],
    ];

    $form['field_story_feature_media']['#states'] = [
      'visible' => [
        ':input[name="field_story_format[1]"]' => ['checked' => TRUE],
      ],
    ];

    $form['field_story_video_credits']['#states'] = [
      'visible' => [
        ':input[name="field_story_format[1]"]' => ['checked' => TRUE],
      ],
    ];

    $form['field_story_video_duration_time']['#states'] = [
      'visible' => [
        [
          ':input[name="field_story_format[1]"]' => ['checked' => TRUE],
          ':input[name="field_story_format[12]"]' => ['checked' => TRUE],
        ],
        'or',
        [
          ':input[name="field_story_format[1]"]' => ['checked' => TRUE],
          ':input[name="field_story_format[13]"]' => ['checked' => TRUE],
        ],
      ],
    ];

    $form['field_embed_quick_poll_survey']['#states'] = [
      'visible' => [
        ':input[name="field_story_format[13]"]' => ['checked' => TRUE],
      ],
    ];

    $form['field_quick_summary']['#states'] = [
      'visible' => [
        ':input[name="field_story_format[13]"]' => ['checked' => TRUE],
      ],
    ];

    $form['field_story_sub_stories']['#states'] = [
      'visible' => [
        [':input[name="field_story_format[5]"]' => ['checked' => TRUE]],
        'or',
        [':input[name="field_story_format[7]"]' => ['checked' => TRUE]],
        'or',
        [':input[name="field_story_format[9]"]' => ['checked' => TRUE]],
        'or',
        [':input[name="field_story_format[24]"]' => ['checked' => TRUE]],
      ],
    ];

    $form['field_story_full_page']['#states'] = [
      'visible' => [
        ':input[name="field_story_format[5]"]' => ['checked' => TRUE],
      ],
    ];

    $form['field_story_gif_thumbnail']['#states'] = [
      'visible' => [
        ':input[name="field_story_format[6]"]' => ['checked' => TRUE],
      ],
    ];

    $form['obw_story_uuid'] = [
      '#type' => 'hidden',
      '#value' => $story_uuid,
    ];

    //    BEGIN MH2020
    $form['field_story_mh2020_type']['#states'] = [
      'visible' => [
        [
          ':input[name="field_story_format[13]"]' => ['checked' => TRUE],
        ],
        'or',
        [
          ':input[name="field_story_format[15]"]' => ['checked' => TRUE],
        ],
        'or',
        [
          ':input[name="field_story_format[22]"]' => ['checked' => TRUE],
        ]
      ],
    ];

    //    BEGIN WL-2022
    $form['field_story_thumbnail_position']['#states'] = [
      'visible' => [
        [
          ':input[name="field_story_format[18]"]' => ['checked' => TRUE],
        ]
      ],
    ];
    $form['field_story_article_type']['#states'] = [
      'visible' => [
        [
          ':input[name="field_story_format[18]"]' => ['checked' => TRUE],
        ],
        'or',
        [
          ':input[name="field_story_format[14]"]' => ['checked' => TRUE],
        ],
        'or',
        [
          ':input[name="field_story_format[19]"]' => ['checked' => TRUE],
        ],
        'or',
        [
          ':input[name="field_story_format[20]"]' => ['checked' => TRUE],
        ],
        'or',
        [
          ':input[name="field_story_format[25]"]' => ['checked' => TRUE],
        ],
      ],
    ];

    $form['field_mental_health_label']['#states'] = [
      'visible' => [
        [
          ':input[name="field_story_format[13]"]' => ['checked' => TRUE],
          ':input[name="field_story_mh2020_type"]' => ['value' => 1],
        ],
        'or',
        [
          ':input[name="field_story_format[15]"]' => ['checked' => TRUE],
          ':input[name="field_story_mh2020_type"]' => ['value' => 1],
        ],
      ],
    ];

    $form['field_wildlife_label']['#states'] = [
      'visible' => [
        [
          ':input[name="field_story_format[19]"]' => ['checked' => TRUE],
        ],
        [
          ':input[name="field_story_format[18]"]' => ['checked' => TRUE],
        ],
        'or',
        [
          ':input[name="field_story_format[14]"]' => ['checked' => TRUE],
        ],
        'or',
        [
          ':input[name="field_story_format[11]"]' => ['checked' => TRUE],
        ],
        //5522
        'or',
        [
          ':input[name="field_story_format[25]"]' => ['checked' => TRUE],
        ],
      ],
    ];

    $form['field_story_mh2020_resource_tags']['#states'] = [
      'visible' => [
        [
          ':input[name="field_story_format[13]"]' => ['checked' => TRUE],
          ':input[name="field_story_mh2020_type"]' => ['value' => 3],
        ],
        //5522
        'or',
        [
          ':input[name="field_story_format[25]"]' => ['checked' => TRUE],
        ],
      ],
    ];

    $form['field_resource_article']['#states'] = [
      'visible' => [
        [':input[name="field_story_format[11]"]' => ['checked' => TRUE]],
        'or',
        [':input[name="field_story_format[14]"]' => ['checked' => TRUE]],
      ],
    ];

    $form['field_mh2020_resource_topic']['#states'] = [
      'visible' => [
        [
          ':input[name="field_story_format[13]"]' => ['checked' => TRUE],
          ':input[name="field_story_mh2020_type"]' => ['value' => 2],
        ],
        'or',
        [
          ':input[name="field_story_format[13]"]' => ['checked' => TRUE],
          ':input[name="field_story_mh2020_type"]' => ['value' => 3],
        ],
      ],
    ];
    //    END MH2020

    $form['field_story_using_years_template']['#states'] = [
      'visible' => [
        [
          ':input[name="field_story_format[13]"]' => ['checked' => TRUE],
        ],
      ],
    ];
    //video 2.0	field_story_series obw 5412
    $form['field_story_series']['#states'] = [
      'visible' => [
        [
          ':input[name="field_story_format[21]"]' => ['checked' => TRUE],
        ],
      ],
    ];
    //obw 5386 5412
    $form['field_story_format']['series_selection'] = [

      '#markup' => Markup::create('
        <p id="series-help-text">Please choose the series/campaign if required</p>
        <br>
        <select id="series" name="series" class="custom">
          <option id="series_none" value="0" selected>-None-</option>
          <option id="series_15" value="15"> Refugee 2021</option>
          <option id="series_13" value="13"> Mental Health 2020</option>
          <option id="series_17" value="17"> Giving for Good 2021</option>
          <option id="series_23" value="23"> Giving for Good 2022</option>
          <option id="series_18" value="18"> Wildlife 2022</option>
          <option id="series_19" value="19"> Environment 2022</option>
          <option id="series_12" value="12"> Humankind</option>
          <option id="series_9" value="9"> Carousel</option>
          <option id="series_22" value="22"> Mental Health 2022</option>
          <option id="series_25" value="25"> Women Empower Series 2023</option>
        </select>
        
      '),

    ];    
    $form['#attached']['library'][] = 'obw_admin_theme/story';
    //obw 5386 5412
    //5475
    $form['field_downloadable_file_gfg']['#states'] = [
      'visible' => [
        [
          ':input[name="field_story_format[23]"]' => ['checked' => TRUE],
        ],
      ],
    ];
    //end 5475    
    //field_obw_menu_2_0
    $form['field_story_narrative']['#states'] = [
      'visible' => [
        [
          ':input[name="field_story_using_years_template"]' => ['value' => 3],
        ],
      ],
    ];
    //5503
    $form['field_post_event_page_carousel']['#states'] = [
      'visible' => [
        ':input[name="field_story_format[24]"]' => ['checked' => TRUE],
      ],
    ];
    $form['field_post_event_page_flip_card']['#states'] = [
      'visible' => [
        ':input[name="field_story_format[24]"]' => ['checked' => TRUE],
      ],
    ];
    $form['field_post_event_page_feedback']['#states'] = [
      'visible' => [
        ':input[name="field_story_format[24]"]' => ['checked' => TRUE],
      ],
    ];    
    $form['field_post_event_page_config']['#states'] = [
      'visible' => [
        ':input[name="field_story_format[24]"]' => ['checked' => TRUE],
      ],
    ];     
    $form['field_post_event_page_popup']['#states'] = [
      'visible' => [
        ':input[name="field_story_format[24]"]' => ['checked' => TRUE],
      ],
    ];          
    $form['field_post_event_page_kumo_space']['#states'] = [
      'visible' => [
        ':input[name="field_story_format[24]"]' => ['checked' => TRUE],
      ],
    ];          
    $form['field_post_event_page_sign_up']['#states'] = [
      'visible' => [
        ':input[name="field_story_format[24]"]' => ['checked' => TRUE],
      ],
    ];
    $form['field_post_event_feedback_form']['#states'] = [
      'visible' => [
        ':input[name="field_story_format[24]"]' => ['checked' => TRUE],
      ],
    ];    
    // ];    
    //end 5503

    // ticket 5365 - remove Non - Account holder out of the story type
    
    // $form['field_user_bios']['#states'] = [
    //   'visible' => [
    //     ':input[name="field_story_format[16]"]' => ['checked' => TRUE],
    //   ],
    // ];
    // $form['field_user_display_picture']['#states'] = [
    //   'visible' => [
    //     ':input[name="field_story_format[16]"]' => ['checked' => TRUE],
    //   ],
    // ];
    // $form['field_user_name']['#states'] = [
    //   'visible' => [
    //     ':input[name="field_story_format[16]"]' => ['checked' => TRUE],
    //   ],
    // ];
    $form['field_user_type']['#states'] = [
      'visible' => [
        ':input[name="field_story_format[16]"]' => ['checked' => TRUE],
      ],
    ];

    $form['obw_cta_callback'] = [
      '#type' => 'button',
      '#value' => 'CTA Search',
      '#title' => 'CTA search',
      '#attributes' => ['class' => ['hidden-field']],
      '#description' => 'Search for current CTA',
      '#prefix' => '<div id="cta-call-back-data" class="you-can-help hidden-field"></div>',
      '#limit_validation_errors' => [],
      '#weight' => 1000,
      '#ajax' => [
        'callback' => [$this, 'submitOBWCTACallbackAjax'],
        'event' => 'click',
      ],
    ];
    $form['field_story_feature_media']['widget']['form']['actions']['ief_reference_save']['#attributes']['class'][] = 'hidden-field';

    if (in_array('writer', $role_name)) {
      $form['body_class_group']['#attributes']['class'][] = 'hidden-field';
      $form['specific_node_class']['#attributes']['class'][] = 'hidden-field';
      $form['field_contributors']['#attributes']['class'][] = 'hidden-field';
      if (!$frmObj->getEntity()
          ->id() && isset($form['field_contributors']['widget'][0])) {
        unset($form['field_contributors']['widget'][0]);
      }

      $form['field_story_activities']['#attributes']['class'][] = 'hidden-field';
      $form['field_story_subject_profile']['#attributes']['class'][] = 'hidden-field';

      $form['#attributes']['class'][] = 'role-writer';
    }
    else {
      array_unshift($form['#validate'], [$this, 'validateRequireField']);
      //$form['field_story_related_topics']['widget']['#required'] = TRUE;
      //$form['field_story_subject_profile']['widget']['#required'] = TRUE;
    }

    array_unshift($form['#validate'], [$this, 'validateVideoEmbeded']);

    if (in_array('content_admin', $role_name) || in_array('administrator', $role_name)) {
      $form['#attributes']['class'][] = 'role-content-admin';
    }

    $form['field_story_share_counter']['#attributes']['class'][] = 'hidden-field';

    $form['actions']['discard'] = [
      '#type' => 'button',
      '#weight' => 30,
      '#value' => t('Discard '),
      '#attributes' => ['class' => ['btn-discard']],
      '#limit_validation_errors' => [],
    ];
    $form['actions']['sync_image_drive'] = [
      '#type' => 'submit',
      '#weight' => 30,
      '#value' => t('Sync image drive '),
      '#submit' => [[get_class($this), 'callGoogleService']],
      '#limit_validation_errors' => [],
    ];
    $form['#attached']['library'][] = 'obw_utilities/story-form';

    //MH2021
    $form['field_mh2021_sub_tag_1']['#states'] = [
      'visible' => [
        ':input[name="field_story_format[13]"]' => ['checked' => TRUE],
      ],
    ];
    $form['field_mh2021_sub_tag_2']['#states'] = [
      'visible' => [
        ':input[name="field_story_format[13]"]' => ['checked' => TRUE],
      ],
    ];
    $form['field_mh2021_tag_2']['#states'] = [
      'visible' => [
        ':input[name="field_story_format[13]"]' => ['checked' => TRUE],
      ],
    ];
  }

  /**
   * validate required field for story form
   *
   * @param $form
   * @param FormStateInterface $form_state
   */
  public function validateVideoEmbeded(&$form, \Drupal\Core\Form\FormStateInterface $form_state) {
    $formats = $form_state->getValue('field_story_format');
    $need_video = FALSE;
    if (!empty($formats)) {
      $formats = array_column($formats, 'value');
      //TODO: find name of video type = "1"
      $need_video = in_array("1", $formats);
    }

    if ($need_video) {
      $field_name = 'field_story_feature_media';
      $value = $form_state->getValue($field_name);
      $title = $form[$field_name]['widget']['#title'];
      if (isset($value) && empty($value['target_id'])) {
        $form_state->setErrorByName($field_name, t('@name is field is required.', ['@name' => !empty($title) ? $title : '']));
      }
    }
  }

  /**
   * validate required field for story form
   *
   * @param $form
   * @param FormStateInterface $form_state
   */
  public function validateRequireField(&$form, \Drupal\Core\Form\FormStateInterface $form_state) {
    //    $field_names_require = ['field_story_related_topics'];
    $field_names_require = [];
    foreach ($field_names_require as $name) {
      $value = $form_state->getValue($name);
      $title = $form[$name]['widget']['#title'];
      if (isset($value) && empty($value['target_id'])) {
        $form_state->setErrorByName($name, t('@name is field is required.', ['@name' => !empty($title) ? $title : '']));
      }
    }
  }

  /**
   * AJAX callback handler that displays any errors or a success message.
   */
  public static function submitOBWCTACallbackAjax(array $form, FormStateInterface $form_state) {
    foreach ($form_state->get('inline_entity_form') as &$widget_state) {
      if ('field_story_call_to_actions' == $widget_state['instance']->getName()) {
        foreach ($widget_state['entities'] as $delta => $entity_item) {
          if (!empty($entity_item['entity'])) {
            $entity_type = $entity_item['entity']->getType();
            if (!empty($entity_type)) {
              $uuid = $entity_item['entity']->uuid();
              $desc = $entity_item['entity']->field_cta_short_description->getValue()[0]['value'];
              $title = $entity_item['entity']->title->getValue()[0]['value'];
              $title_link = $entity_item['entity']->field_cta_link->getValue()[0]['title'];
              $url = Url::fromUri($entity_item['entity']->field_cta_link->getValue()[0]['uri']);
              $inline_entities[$delta]['uuid'] = $uuid;
              $inline_entities[$delta]['desc'] = $desc;
              $inline_entities[$delta]['title'] = $title;
              $inline_entities[$delta]['url'] = $url->toString();
              $inline_entities[$delta]['title_link'] = $title_link;
            }
          }
        }
      }
    }

    $div = '<div id="cta-call-back-data" class="you-can-help hidden-field">';
    if (empty($inline_entities)) {
      $div .= '<div class="head-block"><h3> This story has no CTA. Please add CTA for the story first!</h3></div>';
    }
    else {
      $div .= '<div class="head-block"><h3> The story has ' . count($inline_entities) . ' CTAs.</h3></div>';
      foreach ($inline_entities as $delta => $item) {
        $div .=
          '<div class="cta-item">' .
          '<input type="checkbox" id="' . $item['uuid'] . '" name="' . $item['uuid'] . '" value="' . $item['uuid'] . '">' .
          '<label for="' . $item['uuid'] . '">' .
          '<drupal-entity data-embed-button="cta_for_story" data-entity-embed-display="view_mode:node.feature_content" data-entity-type="node" data-entity-uuid="' . $item['uuid'] . '">' .
          '</drupal-entity>' .
          '<div class="col-md-12 item-block">' .
          '<a href="' . $item['url'] . '"> ' .
          '<b>' . $item['title_link'] . ' </b>' .
          '<span>' . $item['desc'] . ' </span>' .
          '</a> </div>' .
          '</label>' .
          '</div>';
      }
    }
    $div .= '</div>';
    $response = new AjaxResponse();
    $response->addCommand(new ReplaceCommand('#cta-call-back-data', $div));
    $response->addCommand(
      new InvokeCommand('html', 'trigger',
        [
          'add_cta_data',
          ['cta-call-back-data', 'data'],
        ]
      )
    );
    return $response;
  }

  public static function callGoogleService(array &$form, FormStateInterface $form_state) {

    /** @var \Drupal\obw_import_data\googleApiHandler $google_api_handler */
    $google_api_handler = \Drupal::service('obw_import_data.google_api_handler');
    if ($form_state->getFormObject() instanceof EntityFormInterface) {
      $nid = $form_state->getformObject()->getEntity()->id();
      $google_api_handler->getAccessToken($nid);
    }
  }


}
