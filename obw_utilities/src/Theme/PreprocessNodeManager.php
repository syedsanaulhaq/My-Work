<?php

namespace Drupal\obw_utilities\Theme;

use Drupal;
use Drupal\Core\Url;
use Drupal\image\Entity\ImageStyle;
use Drupal\media\Entity\Media;
use Drupal\node\Entity\Node;
use Drupal\obw_action_entity\Controller\ActionEntityController;
use Drupal\obw_contributor_profile\Controller\ActiveCampaignController;
use Drupal\obw_social\User\SessionHandler;
use Drupal\obw_vr_story\Theme\VRStoryPreprocessNodeManager;
use Drupal\paragraphs\Entity\Paragraph;
use Drupal\taxonomy\Entity\Term;
use Drupal\user\Entity\User;
use Drupal\webform\Entity\Webform;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;

/**
 * Created by PhpStorm.
 * User: leopham
 * Date: 10/12/18
 * Time: 2:43 PM
 */
class PreprocessNodeManager {

  public static function CallToAction(&$variables) {

    $cta_node = $variables['elements']['#node'];
    $view_mode = $variables['view_mode'];
    $nodes = Drupal::entityTypeManager()
      ->getStorage('node')
      ->loadByProperties([
        'type' => 'story',
        'field_story_call_to_actions' => ['target_id' => $cta_node->id()],
      ]);
    $story_node = ($nodes) ? reset($nodes) : FALSE;

    $story_node_feature_thumbnail = NULL;

    if ($story_node) {
      $variables['story_url'] = $story_node->toUrl()->toString();
      $variables['story_nid'] = $story_node->id();

      /** @var SessionHandler $session */
      $session = Drupal::service('obw_social.session_handler');
      $session->set('story_url', Url::fromRoute('<front>', [], ['absolute' => TRUE])
          ->toString() . ltrim($variables['story_url'], '/'));
      $session->set('story_nid', $variables['story_nid']);

      if (!$story_node->get('field_story_feature_media')->isEmpty()) {
        if ($story_node->get('field_story_feature_media')->entity) {
          $story_node_feature_thumbnail = $story_node->get('field_story_feature_media')->entity->get('thumbnail')[0]->entity;
          $thumbnail = $story_node_feature_thumbnail->uri->value;
          $variables['story_thumb'] = file_create_url($thumbnail);
        }
      }
    }


    if (!empty($variables['content']['field_cta_form_detail'][0]['#webform'])) {
      /** @var Webform $webform */
      $webform = $variables['content']['field_cta_form_detail'][0]['#webform'];

      if ($webform->get('category') == 'Typeform') {
        $default_data = $variables['content']['field_cta_form_detail'][0]['#default_data'];
        $variables['typeform_integrated'] = TRUE;
        $variables['typeform_url'] = TRUE;
        $user = \Drupal::currentUser();
        $login_required = !empty($default_data['login_required']) ? $default_data['login_required'] : '';
        $typeform_id = !empty($default_data['typeform_id']) ? $default_data['typeform_id'] : '';//'odgAAI'
        if ($login_required) {
          $variables['login_required'] = TRUE;
          if ($user->isAnonymous()) {
            $variables['#attached']['library'][] = 'obw_theme/come-back-page';
            $variables['is_anonymous'] = TRUE;
          }
          else {
            $variables['is_anonymous'] = FALSE;
            $account = User::load($user->id());
            $first_name = $account->field_account_first_name->value;
            $last_name = $account->field_account_last_name->value;

            $ac_tag = !empty($default_data['ac_tag']) ? $default_data['ac_tag'] : '';
            $ac_tag = str_replace('~', ':', $ac_tag);
            $ac_list = !empty($default_data['ac_list']) ? $default_data['ac_list'] : '';
            $variables['ac_tag'] = $ac_tag;
            $variables['ac_list'] = $ac_list;
            $variables['typeform_id'] = $typeform_id;
            $variables['full_name'] = $first_name . ' ' . $last_name;
            $variables['user_email'] = $user->getEmail();
            $variables['uid'] = $user->id();
            $variables['#attached']['library'][] = 'obw_theme/typeform';
          }
        }
        else {
          $variables['#attached']['library'][] = 'obw_theme/typeform';
          if (!$user->isAnonymous()) {
            $account = User::load($user->id());
            $first_name = $account->field_account_first_name->value;
            $last_name = $account->field_account_last_name->value;
            $variables['full_name'] = $first_name . ' ' . $last_name;
            $variables['user_email'] = $user->getEmail();
          }
          $variables['uid'] = $user->id();

          $ac_tag = !empty($default_data['ac_tag']) ? $default_data['ac_tag'] : '';
          $ac_tag = str_replace('~', ':', $ac_tag);
          $ac_list = !empty($default_data['ac_list']) ? $default_data['ac_list'] : '';
          $variables['ac_tag'] = $ac_tag;
          $variables['ac_list'] = $ac_list;
          $variables['typeform_id'] = $typeform_id;
        }
      }
      if ($webform->get('category') == 'Donate API - GlobalGiving') {
        $variables['donation_api'] = TRUE;
        $variables['donation_title'] = $cta_node->title->value;
        $variables['donation_description'] = $cta_node->get('field_cta_short_description')->value;
        if (isset($cta_node->field_cta_image) && !empty($cta_node->field_cta_image->entity)) {
          $variables['donation_thumbnail'] = self::getImageUrlWithStyle($cta_node->field_cta_image->entity, 'thumbnail_350x200');
        }
        elseif ($story_node_feature_thumbnail) {
          $variables['donation_thumbnail'] = self::getImageUrlWithStyle($story_node_feature_thumbnail, 'thumbnail_350x200');
        }
      }
      if ($webform->get('category') == 'Paypal integration') {
        $variables['paypal_integration'] = TRUE;
        $variables['donation_title'] = $cta_node->title->value;
        $variables['donation_description'] = $cta_node->get('field_cta_des_at_cta_page')->value;
        if (isset($cta_node->field_cta_image) && !empty($cta_node->field_cta_image->entity)) {
          $variables['donation_thumbnail'] = self::getImageUrlWithStyle($cta_node->field_cta_image->entity, 'thumbnail_350x200');
        }
        elseif ($story_node_feature_thumbnail) {
          $variables['donation_thumbnail'] = self::getImageUrlWithStyle($story_node_feature_thumbnail, 'thumbnail_350x200');
        }
      }
    }
    switch ($view_mode) {
      case 'resident':
      case 'organisations':
        /** @var  $request  Request */
        $request = Drupal::request();
        $submitted = $request->get('submitted', FALSE);
        $webform_id = $request->get('wf', '');
        if ($submitted && !empty($webform_id)) {
          /** @var $webform Webform */
          $webform = $variables['content']['field_cta_form_detail'][0]['#webform'];
          if ($webform && $webform_id == $webform->id()) {
            // Get webform elements.
            $confirm_title = $webform->getSetting('confirmation_title');
            $confirmation_message = $webform->getSetting('confirmation_message');

            $html = '<div class="thank-you-page gray-bg"><div class="container"><div class="webform-confirmation ">'
              . '<div class="webform-confirmation__message"><h3 style="display: block">' . $confirm_title . '</h3>'
              . $confirmation_message
              . '</div></div></div></div>';
            $variables['cta_thank_you'] = $html;
          }
        }

        if (!$cta_node->get('field_cta_image')->isEmpty()) {
          // One line uri without loading.
          if ($cta_node->get('field_cta_image')->entity) {
            $thumbnail = $cta_node->get('field_cta_image')->entity->uri->value;
            $variables['cta_thumbnail'] = self::buildImageStyle($thumbnail, 'thumbnail_180x180');
          }
        }
        break;
      case 'take_action_content':
        if (!$cta_node->get('field_cta_image')->isEmpty()) {
          // One line uri without loading.
          if ($cta_node->get('field_cta_image')->entity) {
            $thumbnail = $cta_node->get('field_cta_image')->entity->uri->value;
            $variables['background_thumbnail'] = self::buildImageStyle($thumbnail, 'medium_540x300');
            $attrs = $cta_node->get('field_cta_image')->getValue();
            $variables['background_thumbnail_alt'] = isset($attrs['alt']) ? $attrs['alt'] : '';
            $variables['background_thumbnail_title'] = isset($attrs['title']) ? $attrs['title'] : '';
          }
        }
        break;
      case 'cta_with_image_thumbnail':
        PreprocessNodeManager::renderNodeImageResponsiveThumbnail($cta_node, 'field_cta_image', $variables, 'responsive_910x400', ['obw-card__image']);
        PreprocessNodeManager::getCTADataAction($variables, $cta_node);

        if (isset($cta_node->field_migrant_workers_cta) && !empty($cta_node->field_migrant_workers_cta->referencedEntities())) {
          $linked_cta = $cta_node->field_migrant_workers_cta->referencedEntities()[0];
          $variables['migrant_workers_linked_cta'] = [
            'nid' => $linked_cta->id(),
            'title' => $linked_cta->field_cta_link->title,
            'url' => Url::fromUri($linked_cta->field_cta_link->uri)->toString(),
            'target' => !empty($linked_cta->field_cta_link->options['attributes']['target']) ? $linked_cta->field_cta_link->options['attributes']['target'] : '_self',
            'has_form_detail' => empty($linked_cta->field_cta_form_detail->getValue()) ? FALSE : TRUE,
          ];

          PreprocessNodeManager::getCTADataAction($variables, $linked_cta);
        }
        break;
    }

  }

  public static function Story(&$variables) {
    $is_front = Drupal::service('path.matcher')->isFrontPage();
    $node = $variables['elements']['#node'];
    $account = Drupal::currentUser();
    $user_id = $account->id();
    $variables['show_spotlight'] = FALSE;
    if (isset($node->field_story_spotlight) && $node->field_story_spotlight->getValue()) {
      $variables['show_spotlight'] = TRUE;
    }
    $field_tags_without_link = NULL;

    switch ($variables['view_mode']) {
      case 'featured_1st_home':
      case 'feature_home_series':
        $variables['show_spotlight'] = FALSE;
        if ($is_front && isset($node->field_story_spotlight) && $node->field_story_spotlight->getValue()) {
          $variables['show_spotlight'] = TRUE;
        }
        PreprocessNodeManager::loadThumbnailByImageStyleForStory($variables, 'medium_730x400', $node, 'field_story_feature_media');
        break;
      case 'feature_content':
        $hide_category = FALSE;
        if (isset($node->field_story_format) && !empty($node->field_story_format->getValue())) {
          //Hide category for story subject
          $hide_category = in_array('8', array_column($node->field_story_format->getValue(), 'value'));
        }

        if (!$hide_category) {
          if (isset($node->field_story_category) && !empty($node->field_story_category->getValue()[0]['target_id'])) {
            $variables['field_story_category_name'] = $node->field_story_category->first()->entity->getName();
            $variables['field_story_category_url'] = $node->field_story_category->first()->entity->toUrl()
              ->toString();
          }
        }
        else {
          $variables['open_new_tab'] = TRUE;
          PreprocessNodeManager::loadThumbnailByImageStyleForStory($variables, 'thumbnail_350x200', $node, 'field_story_thumbnail');
        }

        if ($node->published_at->getValue() && isset($node->published_at->getValue()[0]['value'])) {
          $variables['published_at'] = $node->published_at->getValue()[0]['value'];
        }

        if (isset($node->field_story_format) && !empty($node->field_story_format->getValue())) {
          $is_multimedia = array_search('5', array_column($node->field_story_format->getValue(), 'value'));
          if ($is_multimedia !== FALSE) {
            $variables['interactive_icon'] = TRUE;
          };
        }
        break;
      case 'related_stories_in_national_day':
        $hide_category = FALSE;
        if (isset($node->field_story_format) && !empty($node->field_story_format->getValue())) {
          //Hide category for story subject
          $hide_category = in_array('8', array_column($node->field_story_format->getValue(), 'value'));
        }

        if (!$hide_category) {
          if (isset($node->field_story_category) && !empty($node->field_story_category->getValue()[0]['target_id'])) {
            $variables['field_story_category_name'] = $node->field_story_category->first()->entity->getName();
            $variables['field_story_category_url'] = $node->field_story_category->first()->entity->toUrl()
              ->toString();
          }
        }

        PreprocessNodeManager::loadThumbnailByImageStyleForStory($variables, 'thumbnail_350x200', $node, 'field_story_thumbnail');

        if ($node->published_at->getValue() && isset($node->published_at->getValue()[0]['value'])) {
          $variables['published_at'] = $node->published_at->getValue()[0]['value'];
        }

        if (isset($node->field_story_format) && !empty($node->field_story_format->getValue())) {
          $is_multimedia = array_search('5', array_column($node->field_story_format->getValue(), 'value'));
          if ($is_multimedia !== FALSE) {
            $variables['interactive_icon'] = TRUE;
          };
        }
        break;
      case 'featured_home':
      case 'featured_home_series':
        $variables['show_spotlight'] = FALSE;
        if ($is_front && isset($node->field_story_spotlight) && $node->field_story_spotlight->getValue()) {
          $variables['show_spotlight'] = TRUE;
        }
        if (!$node->get('field_story_feature_media')->isEmpty()) {
          // One line uri without loading.
          if ($node->get('field_story_feature_media')->entity) {
            if (!empty(\Drupal::hasService('obw_utilities.responsive_image'))) {
              $response_service = Drupal::service('obw_utilities.responsive_image');
              $responsive_image = $response_service->responsiveImageStyle($node->get('field_story_feature_media')->entity->get('thumbnail'), 'responsive_medium_460x250');
              $variables['responsive_img'] = $responsive_image;
            }
            else {
              PreprocessNodeManager::loadThumbnailByImageStyleForStory($variables, 'medium_730x400', $node, 'field_story_feature_media');
            }
          }
        }
        break;
      case 'teaser':
      case 'featured_series':
      case 'my_actions_content':
      case 'featured_organisation':
        PreprocessNodeManager::loadThumbnailByImageStyleForStory($variables, 'thumbnail_350x200', $node, 'field_story_feature_media');
        break;
      case 'all_residents':
        PreprocessNodeManager::loadThumbnailByImageStyleForStory($variables, 'medium_420x280', $node, 'field_story_feature_media');
        break;
      case 'featured_top_series':
        PreprocessNodeManager::loadThumbnailByImageStyleForStory($variables, 'medium_540x300', $node, 'field_story_feature_media');
        break;
      case 'featured_top_small_series':
        PreprocessNodeManager::loadThumbnailByImageStyleForStory($variables, 'thumbnail_255x140', $node, 'field_story_feature_media');
        break;
      case 'full':
      case 'obw_10th_year':
      case 'amp':
        $etm = Drupal::entityTypeManager();
        $is_multimedia = FALSE;
        $is_substory_multimedia = FALSE;
        $is_mental_health = FALSE;
        $is_infographic = FALSE;
        $is_humankind_premium_story = FALSE;
        if (isset($node->get('field_story_format')->getValue()[0]['value'])) {
          $is_multimedia = array_search('5', array_column($node->get('field_story_format')
            ->getValue(), 'value'));
          $is_substory_multimedia = array_search('6', array_column($node->get('field_story_format')
            ->getValue(), 'value'));
          $is_mental_health = array_search('9', array_column($node->field_story_format->getValue(), 'value'));
          $is_infographic = array_search('10', array_column($node->field_story_format->getValue(), 'value'));
          $is_humankind_premium_story = array_search('12', array_column($node->field_story_format->getValue(), 'value'));
        }

        if (!$node->get('field_story_feature_media')->isEmpty()) {
          // One line uri without loading.
          if ($node->get('field_story_feature_media')->entity) {
            $node_media = $node->get('field_story_feature_media')->entity;

            $flag_media_video = FALSE;
            $is_video = isset($node->get('field_story_format')
                ->getValue()[0]['value']) ? $node->get('field_story_format')
                ->getValue()[0]['value'] == '1' : FALSE;

            if ($is_video && $node_media->hasField('field_media_video_embed_field')) {
              $flag_media_video = TRUE;
            }
            $variables['flag_media_video'] = $flag_media_video;

            if ($is_substory_multimedia !== FALSE) {
              $image_style = 'thumbnail_960x540';
              $variables['#attached']['library'][] = 'obw_utilities/come-back-prev-page';
            }
            else {
              $image_style = 'medium_730x400';
            }

            self::loadThumbnailByImageStyleForStory($variables, $image_style, $node, 'field_story_feature_media');
          }
        }

        if (isset($node->field_story_video_banner) && !empty($node->field_story_video_banner->entity)) {
          $variables['banner_video_url'] = file_create_url($node->field_story_video_banner->entity->uri->value);
        }

        if ($is_multimedia !== FALSE) {
          $variables['show_crt_contributors'] = TRUE;
        }

        if ($is_substory_multimedia !== FALSE) {
          $multimedia_node = $etm->getStorage('node')->loadByProperties(
            [
              'type' => 'story',
              'field_story_sub_stories' => $node->id(),
            ]
          );
          $multimedia_node = reset($multimedia_node);
          if ($multimedia_node) {
            $variables['multimedia_node_id'] = $multimedia_node->id();
          }

        }

        if ($is_mental_health) {
          if (isset($node->field_story_sub_stories) && !empty($node->field_story_sub_stories->getValue())) {
            $sub_story_list = PreprocessNodeManager::loadSubStories($node, 'field_story_sub_stories', 'field_story_slider_image');
            if (!empty($sub_story_list)) {
              $variables['sub_story_list'] = $sub_story_list;
            }
          }

          if (isset($node->field_story_cta_introduction) && !empty($node->field_story_cta_introduction->value)) {
            $variables['cta_introduction'] = $node->field_story_cta_introduction->value;
          }

          if (isset($node->field_story_width_not_full) && !empty($node->field_story_width_not_full->value) && $node->field_story_width_not_full->value == "1") {
            $variables['width_not_full'] = TRUE;
          }
        }

        if ($is_humankind_premium_story) {
          if (isset($node->field_story_comissioned_type) && !empty($node->field_story_comissioned_type->referencedEntities())) {
            $taxo_comissioned_type = $node->field_story_comissioned_type->referencedEntities()[0];
            if (isset($taxo_comissioned_type->description) && !empty($taxo_comissioned_type->description->value)) {
              $variables['story_comissioned_type'] = $taxo_comissioned_type->description->value;
            }
          }
        }
        PreprocessNodeManager::loadForumBlock($node, 'field_story_forum_block', $variables);
        if (isset($node->field_story_disable_action) && $node->field_story_disable_action->value === "1") {
          $variables['disable_action_function'] = TRUE;
        }
        else {
          if ($account->id() !== 0) {
            $moduleHandler = Drupal::service('module_handler');
            if ($moduleHandler->moduleExists('obw_action_entity')) {
              $params_action = self::getParamsAction($node);
              $action_entity_controller = new ActionEntityController();
              $status_like = $action_entity_controller->isUserActed($params_action, 'like');
              $variables['total_like'] = !empty($status_like['total']) ? $status_like['total'] : '0';
              $status_follow = $action_entity_controller->isUserActed($params_action, 'follow');
              $status_share = $action_entity_controller->getAmoutOfClick($params_action);
              $variables['total_share'] = !empty($status_share['total']) ? $status_share['total'] : '0';
              if (!empty($status_like['is_acted']) && $status_like['is_acted']) {
                $variables['user_liked'] = TRUE;
              }

              if (!empty($status_follow['is_acted']) && $status_follow['is_acted']) {
                $variables['user_followed'] = TRUE;
              }
            }
          }
          else {
            $variables['total_like'] = '';
          }
        }

        if (!empty($node->field_story_photo_gallery->entity)) {
          $para_gallery = $node->field_story_photo_gallery->entity;
          $list_images = [];
          if (!empty($para_gallery->field_photo_gallery_image_list)) {
            foreach ($para_gallery->field_photo_gallery_image_list as $item) {
              $image_url = self::buildImageStyle($item->entity->getFileUri(), 'thumbnail_960x540');
              $image_original = file_url_transform_relative(file_create_url($item->entity->getFileUri()));
              $img = [
                'original' => $image_original,
                'url' => $image_url,
                'alt' => $item->alt,
                'title' => $item->title,
              ];
              $list_images[] = $img;
            }
            $variables['photo_gallery'] = $list_images;
          }

          $variables['name_gallery'] = $para_gallery->field_photo_gallery_name->value;
        }

        if (isset($node->field_story_call_to_actions)) {
          $is_anonymous = $account->isAnonymous();
          $renderer = \Drupal::service('renderer');
          $cta_list_featured_content = [];
          $cta_index = 0;
          $survey_index = 0;
          $cta_list = $node->field_story_call_to_actions->referencedEntities();
          foreach ($cta_list as $key => $cta) {
            $cta_featured_content = [];
            $cta_url = '';
            $cta_option = '';
            $cta_category = $cta->field_cta_category->referencedEntities()[0]->name->value;

            if ($cta_category !== 'Share') {
              $cta_url = Url::fromUri($cta->field_cta_link->getValue()[0]['uri'])
                ->toString();
              $cta_option = !empty($cta->field_cta_link->getValue()[0]['options']['attributes']['target']) ? $cta->field_cta_link->getValue()[0]['options']['attributes']['target'] : '';
            }

            $cta_short_desc = $cta->field_cta_short_description->value;
            if ($cta_category == 'Survey') {
              $survey_index++;
              if ($survey_index === 1) {
                $survey_html = '';
              }
              $default_data_arr = [];
              if ($survey_index <= 2) {
                if (isset($cta->field_cta_form_detail) && !empty($cta->field_cta_form_detail->getValue())) {
                  $default_data_str = $cta->field_cta_form_detail->getValue()[0]['default_data'];
                  $lines = preg_split('/\r\n|\r|\n/', $default_data_str);
                  foreach ($lines as $line) {
                    if ($line) {
                      $data = explode(':', $line);
                      if (!empty($data)) {
                        $default_data_arr[trim($data[0])] = trim($data[1]);
                      }
                    }
                  }
                }
                if (!empty($default_data_arr)) {
                  if (!empty($default_data_arr['login_required']) && $default_data_arr['login_required'] === 'true') {
                    $survey_html = $survey_html . '<div class="col-md-12 item-block">'
                      . '<a class="' . ($is_anonymous ? 'cta-anonymous' : ' ') . '" data-index="survey-' . $survey_index . '" href="' . $cta_url . '" target="' . $cta_option . '" title="' . $cta_short_desc . '">'
                      . '<b>' . $cta_category . '</b>'
                      . '<span>' . $cta_short_desc . '</span>'
                      . '</a></div>';
                  }
                  else {
                    $survey_html = $survey_html . '<div class="col-md-12 item-block">'
                      . '<a href="' . $cta_url . '" target="' . $cta_option . '" title="' . $cta_short_desc . '">'
                      . '<b>' . $cta_category . '</b>'
                      . '<span>' . $cta_short_desc . '</span>'
                      . '</a></div>';
                  }
                }
                else {
                  $survey_html = $survey_html . '<div class="col-md-12 item-block">'
                    . '<a href="' . $cta_url . '" target="' . $cta_option . '" title="' . $cta_short_desc . '">'
                    . '<b>' . $cta_category . '</b>'
                    . '<span>' . $cta_short_desc . '</span>'
                    . '</a></div>';
                }
              }

            }
            else {
              $cta_index++;
              if ($cta_index === 1) {
                $cta_html = '';
              }
              if ($cta_index <= 2) {
                $cta_html = $cta_html . '<div class="col-md-12 item-block">'
                  . '<a href="' . $cta_url . '" target="' . $cta_option . '" title="' . $cta_short_desc . '">'
                  . '<b>' . $cta_category . '</b>'
                  . '<span>' . $cta_short_desc . '</span>'
                  . '</a></div>';
              }

            }

          }
          if (isset($cta_html)) {
            $cta_list_html = [
              '#markup' => $cta_html,
              '#cache' => [
                'contexts' => [
                  // The "current user" is used above, which depends on the request,
                  // so we tell Drupal to vary by the 'user' cache context.
                  'user',
                ],
              ],
            ];
            $renderer->addCacheableDependency($cta_list_html, \Drupal\user\Entity\User::load($user_id));

            $variables['cta_list'] = $cta_list_html;
            $variables['cta_count'] = $cta_index;
          }

          if (isset($survey_html)) {
            $survey_list_html = [
              '#markup' => $survey_html,
              '#cache' => [
                'contexts' => [
                  // The "current user" is used above, which depends on the request,
                  // so we tell Drupal to vary by the 'user' cache context.
                  'user',
                ],
              ],
            ];
            $renderer->addCacheableDependency($survey_list_html, \Drupal\user\Entity\User::load($user_id));
            $variables['survey_list'] = $survey_list_html;
            $variables['survey_count'] = $survey_index;
            $variables['#attached']['library'][] = 'obw_theme/login-access-form';
            //              $variables['#attached']['library'][] = 'obw_theme/come-back-page';
          }
        }

        if (isset($node->field_story_disable_share) && $node->field_story_disable_share->value === "1") {
          $variables['disable_share_function'] = TRUE;
        }
        if (!empty($variables['disable_action_function']) && empty($variables['cta_list']) && empty($variables['survey_list'])) {
          $variables['show_full_width_content'] = TRUE;
        }

        if (isset($node->field_story_embed_eko_player) && !empty($node->field_story_embed_eko_player->value) && $node->field_story_embed_eko_player->value == '1') {
          $variables['#attached']['library'][] = 'obw_theme/embed_eko_player';
        }
        break;
      case 'who_you_can_help_content':
      case 'persona_detail':
        PreprocessNodeManager::loadThumbnailByImageStyleForStory($variables, 'thumbnail_350x200', $node, 'field_story_thumbnail');
        break;
      case 'stories_from_country':
      case 'stories_from_public_profile':
      case 'search_content_result':
        if (isset($node->field_story_format) && !empty($node->field_story_format->getValue())) {
          $is_multimedia = array_search('5', array_column($node->field_story_format->getValue(), 'value'));
          if ($is_multimedia !== FALSE) {
            $variables['interactive_icon'] = TRUE;
          };
        }
        break;
      case 'take_action_content':
        PreprocessNodeManager::loadThumbnailByImageStyleForStory($variables, 'medium_540x300', $node, 'field_story_thumbnail');
        if ($variables['view_mode'] == 'take_action_content') {
          if (isset($node->field_story_call_to_actions) && !empty($node->field_story_call_to_actions->referencedEntities())) {
            $cta_nodes = $node->field_story_call_to_actions->referencedEntities();
            foreach ($cta_nodes as $cta_node) {
              if ($cta_node->field_cta_category->referencedEntities()[0]->name->value !== 'Survey') {
                $variables['cta_url'] = Url::fromUri($cta_node->field_cta_link->uri);
                break;
              }
            }
          }
        }
        if (isset($node->field_story_unlink_to_story_page) && !empty($node->field_story_unlink_to_story_page->value) && $node->field_story_unlink_to_story_page->value == '1') {
          $variables['unlink_to_story_page'] = TRUE;
        }

        if (isset($node->field_story_hide_act_now_button) && !empty($node->field_story_hide_act_now_button->value) && $node->field_story_hide_act_now_button->value == '1') {
          $variables['hide_act_now_button'] = TRUE;
        }

        break;
      case 'related_stories_with_share_button':
      case 'archive_comissioned_stories':
        $image_style = 'thumbnail_350x200';
        switch ($variables['view_mode']) {
          case 'archive_comissioned_stories':
            $image_style = 'thumbnail_500x260';
            break;
        }
        PreprocessNodeManager::loadThumbnailByImageStyleForStory($variables, $image_style, $node, 'field_story_thumbnail');

        $variables['share_buttons'] = VRStoryPreprocessNodeManager::GetShareBlock($node->getTitle(), $node, $node->toURL()
          ->toString(), '/form/email-a-friend');
        if (isset($node->field_story_comissioned_type) && !empty($node->field_story_comissioned_type->referencedEntities())) {
          $taxo_comissioned_type = $node->field_story_comissioned_type->referencedEntities()[0];
          if (isset($taxo_comissioned_type->description) && !empty($taxo_comissioned_type->description->value)) {
            $variables['story_comissioned_type'] = $taxo_comissioned_type->description->value;
          }
        }
        break;
      case 'comissioned_stories':
      case 'commision_stories_related':
        if (isset($node->field_story_comissioned_type) && !empty($node->field_story_comissioned_type->referencedEntities())) {
          $taxo_comissioned_type = $node->field_story_comissioned_type->referencedEntities()[0];
          if (isset($taxo_comissioned_type->description) && !empty($taxo_comissioned_type->description->value)) {
            $variables['story_comissioned_type'] = $taxo_comissioned_type->description->value;
          }
        }
        if (!$node->get('field_story_thumbnail')->isEmpty()) {
          // One line uri without loading.
          if ($node->get('field_story_thumbnail')->entity) {
            $thumbnail = $node->get('field_story_thumbnail')->entity->uri->value;
            $image_style = 'thumbnail_1400x300';
            $landing_node = \Drupal::routeMatch()->getParameter('node');
            if ($landing_node instanceof \Drupal\node\NodeInterface) {
              // You can get nid and anything else you need from the node object.
              $stories_least = 1;
              // You can get nid and anything else you need from the node object.
              if ($landing_node->getType() == 'story' && isset($landing_node->field_story_landing_node) && !empty($landing_node->field_story_landing_node->target_id)) {
                $landing_node = Node::load($landing_node->field_story_landing_node->target_id);
                $stories_least = 2;
              }
              if ($landing_node->getType() == 'landing_page') {
                if (isset($landing_node->field_landing_comission_stories) && !empty($landing_node->field_landing_comission_stories->getValue())) {
                  if (count($landing_node->field_landing_comission_stories->getValue()) > $stories_least) {
                    $image_style = 'thumbnail_916x400';
                  }
                }
              }

              $variable_img_style = [
                '#markup' => self::buildImageStyle($thumbnail, $image_style),
                '#cache' => [
                  'tags' => ['node:' . $landing_node->id()],
                ],
              ];
              $variables['background_thumbnail'] = $variable_img_style;
              $attrs = $node->get('field_story_thumbnail')->getValue();
              $variables['background_thumbnail_alt'] = isset($attrs['alt']) ? $attrs['alt'] : '';
              $variables['background_thumbnail_title'] = isset($attrs['title']) ? $attrs['title'] : '';
            }
          }
        }
        break;
      case 'all_commision_stories':
        if (isset($node->field_story_comissioned_type) && !empty($node->field_story_comissioned_type->referencedEntities())) {
          $taxo_comissioned_type = $node->field_story_comissioned_type->referencedEntities()[0];
          if (isset($taxo_comissioned_type->description) && !empty($taxo_comissioned_type->description->value)) {
            $variables['story_comissioned_type'] = $taxo_comissioned_type->description->value;
          }
        }
        if (!$node->get('field_story_thumbnail')->isEmpty()) {
          // One line uri without loading.
          if ($node->get('field_story_thumbnail')->entity) {
            $thumbnail = $node->get('field_story_thumbnail')->entity->uri->value;
            $image_style = 'thumbnail_1400x300';
            $landing_node = \Drupal::routeMatch()->getParameter('node');
            if ($landing_node instanceof \Drupal\node\NodeInterface) {
              if ($landing_node->getType() == 'list_page') {
                if (isset($landing_node->field_list_page_spotlight_list) && !empty($landing_node->field_list_page_spotlight_list->getValue())) {
                  if (count($landing_node->field_list_page_spotlight_list->getValue()) > 1) {
                    $image_style = 'thumbnail_916x400';
                  }
                }
              }

              $variable_img_style = [
                '#markup' => self::buildImageStyle($thumbnail, $image_style),
                '#cache' => [
                  'tags' => ['node:' . $landing_node->id()],
                ],
              ];
              $variables['background_thumbnail'] = $variable_img_style;
            }


            $attrs = $node->get('field_story_thumbnail')->getValue();
            $variables['background_thumbnail_alt'] = isset($attrs['alt']) ? $attrs['alt'] : '';
            $variables['background_thumbnail_title'] = isset($attrs['title']) ? $attrs['title'] : '';
          }
        }
        break;
      case 'migrant_workers_landing_sub_stories':
        PreprocessNodeManager::renderNodeImageResponsiveThumbnail($node, 'field_story_thumbnail', $variables, 'responsive_910x400', ['obw-card__image']);
        break;
      case 'mental_health_2020_resources':
        PreprocessNodeManager::loadThumbnailByImageStyleForStory($variables, 'small_345x230_3_2', $node, 'field_story_thumbnail', 'img_url');
        PreprocessNodeManager::checkStoryNoThumbnail($node, $variables);
        PreprocessNodeManager::getAnchorMh2020Resource($node, $variables);
        break;
      case 'spotlight_with_credit_name':
        PreprocessNodeManager::renderNodeImageResponsiveThumbnail($node, 'field_story_thumbnail', $variables, 'responsive_medium_600x400_3_2_', ['obw-card__image']);
        break;
      case 'wl_landing_2021_launched_story':
      case 'wl_landing_2020_launched_story':
      case 'wl_landing_2021_resources':
      case 'wl_landing_2022_resources':
      case 'wl_landing_2021_launching_story':
      case 'wl_resources_2021_first_resource':
      case 'wl_resources_2021_resources_remaining':
      case 'refugees_2021_stories':
      case 'refugees_2021_blogs':
        PreprocessNodeManager::getReadingTimeNode($node, $variables);
        break;
    }
    self::getStoryInfo($node, $variables);
    self::getTagsWithoutLink($node, $variables, $variables['view_mode']);
    if ($node->get('field_story_using_years_template')->value) {
      if (isset($_COOKIE['narrative_year_3_read_nodes'])) {
        $data_cookie = json_decode($_COOKIE['narrative_year_3_read_nodes']);
        array_push($data_cookie, $node->id());
        $data_cookie = array_unique($data_cookie);
        setcookie('narrative_year_3_read_nodes', json_encode($data_cookie), 2147483647, '/');
      }
      else {
        setcookie('narrative_year_3_read_nodes', json_encode([$node->id()]), 2147483647, '/');
      }
    }

  }

  public static function getParamsAction($node) {
    $params_request = [];
    global $base_url;
    $current_user = Drupal::currentUser();
    if ($node instanceof Node) {
      $params_request = [
        'obj_url' => $node->toUrl()->toString(),
        'obj_type' => 'node',
        'obj_subtype' => $node->getType(),
        'obj_id' => $node->id(),
      ];
    }
    $params_request['site_domain'] = $base_url;
    $params_request['user_id'] = $current_user->id();

    return $params_request;
  }

  public static function Collection(&$variables) {

    $language = Drupal::languageManager()->getCurrentLanguage();
    $node = $variables['elements']['#node'];
    switch ($variables['view_mode']) {
      case 'teaser':
      case 'feature_content':
        PreprocessNodeManager::loadThumbnailByImageStyleForStory($variables, 'medium_800x400', $node, 'field_story_feature_media');
        break;
      case 'featured_home':
        $stories = $node->get('field_story_collection')->getValue();
        $count_story = count($stories);
        $first_story = NULL;
        $view_more = NULL;
        // And then you can view/build them all together:
        $render_controller = Drupal::entityTypeManager()
          ->getViewBuilder('node');
        $renderer = Drupal::service('renderer');

        if ($count_story > 0) {
          $first_story = Node::load($stories[0]['target_id']);
          if ($first_story) {
            $variables['first_story'] = $render_controller->view($first_story, 'featured_home_series', $language->getId());

          }
        }
        if ($count_story > 4) {
          $target = "";
          if (isset($node->field_series_override_url_btn_hp)
            && !empty($node->field_series_override_url_btn_hp->getValue())
            && !empty($node->field_series_override_url_btn_hp->getValue()[0]['uri'])) {
            $override_url_btn_field = $node->field_series_override_url_btn_hp->getValue()[0];
            $override_url_btn = $override_url_btn_field['uri'];
            $target = (!empty($override_url_btn_field['options']['attributes']['target']) && $override_url_btn_field['options']['attributes']['target'] == "_blank") ? "_blank" : "";
          }
          $view_more = '<div class="viewmore text-center"> ' .
            '<a href="' . (isset($override_url_btn) ? Url::fromUri($override_url_btn)
              ->toString() : '/node/' . $node->id()) . '" target="' . (!empty($target) ? $target : '') . '" class="btn btn-primary">' . t('View stories from this series') .
            '<i class="ion ion-ios7-arrow-right"></i></a></div>';
          $variables['view_more'] = $view_more;
        }
        if ($count_story > 1) {
          $next_three_story_ids = array_slice(array_column($stories, 'target_id'), 1, 3);
          $render = '';
          foreach ($next_three_story_ids as $nid) {
            $nid = Node::load($nid);
            if ($nid) {
              $builder = $render_controller->view($nid, 'featured_home_series', $language->getId());
            }

            $render .= '<div class="col-md-4 views-row">' . $renderer->render($builder) . '</div>' . "\n";
          }
          $variables['next_three_stories'] = '<div class="row">' . $render . '</div>';
        }

        break;
      case 'full':
        $stories = $node->get('field_story_collection')->getValue();
        $count_story = count($stories);
        $first_story = NULL;
        // And then you can view/build them all together:
        $render_controller = Drupal::entityTypeManager()
          ->getViewBuilder('node');

        if ($count_story > 0) {
          $first_story = Node::load($stories[0]['target_id']);
          if ($first_story) {
            $variables['first_story'] = $render_controller->view(
              $first_story, 'featured_1st_home', $language->getId());
          }
        }
        break;
      default:

    }
  }

  public static function PartnerUs(&$variables) {
    $node = $variables['elements']['#node'];
    /**
     * @var Paragraph $paragraphs ;
     * @var \Drupal\taxonomy\Entity\Term $term ;
     */
    $paragraphs = $node->get('field_partner_us_type_of_partner')
      ->referencedEntities()[0];
    $partner_types = $paragraphs->get('field_para_partner_types');
    foreach ($partner_types as $partner) {
      $term = Term::load($partner->getValue()['target_id']);
      if ($term) {
        $count_node = getNodesByTaxonomyTermIds($term->id());
        $variables['partner_types'][] = [
          'id' => $term->id(),
          'name' => $term->getName(),
          'count' => $count_node,
        ];
      }
    }

  }

  public static function Experience(&$variables) {
    $node = $variables['elements']['#node'];
    /**
     * @var Paragraph $paragraphs ;
     * @var \Drupal\taxonomy\Entity\Term $term ;
     */
    if (!$node->get('field_image')->isEmpty()) {
      // One line uri without loading.
      if ($node->get('field_image')->entity) {
        $thumbnail = $node->get('field_image')->entity->uri->value;
        $variables['background'] = self::buildImageStyle($thumbnail, 'medium_540x300');
      }
    }

  }

  public static function CreateStory(&$variables) {
    $node = $variables['elements']['#node'];

    /**
     * @var Paragraph $paragraphs ;
     * @var User $user ;
     */
    if (!$node->get('field_impact_storytellers')->isEmpty()) {
      // One line uri without loading.
      $users = $node->get('field_impact_storytellers')->referencedEntities();
      $em = Drupal::entityTypeManager();
      $profile_type = 'public_profile';
      foreach ($users as $user) {
        $profile = $em->getStorage('profile')->loadByUser($user, $profile_type);
        $bio = "";
        if ($profile) {
          $bio_entity = $profile->get('field_public_profile_bio')->getValue();
          if ($bio_entity) {
            $bio = $bio_entity[0]['value'];
          }
        }

        $variables['impact_users'][] =
          [
            'uid' => $user->id(),
            'link' => $profile ? $profile->toUrl()->toString() : '',
            'email' => $user->getEmail(),
            'bio' => $bio,
          ];
      }
    }

  }

  public static function Partner(&$variables) {
    if ($variables['elements']['#view_mode'] == 'full') {
      $url = Url::fromUri('internal:/partner-us')->toString();
      $Response = new RedirectResponse($url);
      $Response->send();
    }
  }


  public static function getImageUrlWithStyle($image_entity, $image_style) {
    $style = Drupal::entityTypeManager()
      ->getStorage('image_style')
      ->load($image_style);

    return $style->buildUrl($image_entity->getFileUri());
  }

  public static function Spotlight(&$variables) {
    $node = $variables['elements']['#node'];
    if ($variables['elements']['#view_mode'] == 'full') {
      //Prevent redirect in these case
      if (Drupal::service('path.current')->getPath() == '/batch'
        || Drupal::service('path.current')
          ->getPath() == '/admin/config/system/cron/jobs/search_api_cron/run') {
        return;
      }
      $variables['#cache']['max-age'] = 0;
      \Drupal::service('page_cache_kill_switch')->trigger();
      if (isset($node->field_partner_url) && !empty($node->field_partner_url->uri)) {
        $response = new RedirectResponse(Url::fromUri($node->field_partner_url->uri)
          ->toString(), 301);
        $response->send();
        die();
      }
    }
    else {
      $language = Drupal::languageManager()->getCurrentLanguage();
      //Spotlight format
      $spotlight_formats = [
        '1' => 'default',
        '2' => 'humankind_static',
        '3' => 'humankind_commission',
        '4' => 'covid-19',
      ];
      $spotlight_format_key = !empty($node->field_spotlight_format->value) ? $node->field_spotlight_format->value : '1';
      $spotlight_format = $spotlight_formats[$spotlight_format_key];
      $image_style = 'thumbnail_350x200';

      if (!$node->get('field_story_spotlight')->isEmpty()) {
        // One line uri without loading.
        if ($node->get('field_story_spotlight')->entity) {
          $paragraph = $node->get('field_story_spotlight')->entity;
          if (isset($paragraph->field_para_banner_background) && $paragraph->field_para_banner_background->first()) {
            if ($paragraph->field_para_banner_background->first()->getValue()) {
              $image_field = $paragraph->field_para_banner_background->first()
                ->getValue();
              $media = Media::load($image_field['target_id']);
              if ($media && $media->get('thumbnail')) {
                $thumbnail = $media->get('thumbnail')[0]->entity->uri->value;
              }
            }
          }
          if (isset($paragraph->field_para_banner_title) && !empty($paragraph->field_para_banner_title->value)) {
            $variables['spotlight_title'] = $paragraph->field_para_banner_title->value;
          }

          if (isset($paragraph->field_para_banner_intro) && !empty($paragraph->field_para_banner_intro->value)) {
            $variables['spotlight_desc'] = $paragraph->field_para_banner_intro->value;
          }
        }
      }
      PreprocessNodeManager::getTagsWithoutLink($node, $variables, $variables['view_mode']);
      switch ($variables['view_mode']) {
        case 'teaser':
          if (isset($thumbnail)) {
            $image_style = 'medium_800x400';
            PreprocessNodeManager::loadThumbnailByImageStyleForSpotlight($variables, $thumbnail, $image_style);
          }
          break;
        case 'related_stories_with_share_button':
        case 'archive_comissioned_stories':
          PreprocessNodeManager::loadBtnShareForSpotlight($variables, $node);
          if ($variables['view_mode'] == 'archive_comissioned_stories') {
            $image_style = 'thumbnail_500x260';
          }
          else {
            PreprocessNodeManager::loadHumankindSpotlight($variables, $spotlight_format, $node);
          }
          if (isset($thumbnail)) {
            PreprocessNodeManager::loadThumbnailByImageStyleForSpotlight($variables, $thumbnail, $image_style);
          }
          break;
        case 'comissioned_stories':
        case 'commision_stories_related':
          if (isset($thumbnail)) {
            $image_style = 'thumbnail_1400x300';
            $landing_node = \Drupal::routeMatch()->getParameter('node');
            if ($landing_node instanceof \Drupal\node\NodeInterface) {
              $stories_least = 1;
              // You can get nid and anything else you need from the node object.
              if ($landing_node->getType() == 'story' && isset($landing_node->field_story_landing_node) && !empty($landing_node->field_story_landing_node->target_id)) {
                $landing_node = Node::load($landing_node->field_story_landing_node->target_id);
                $stories_least = 2;
              }
              if ($landing_node instanceof \Drupal\node\NodeInterface && $landing_node->getType() == 'landing_page') {
                if (isset($landing_node->field_landing_comission_stories) && !empty($landing_node->field_landing_comission_stories->getValue())) {
                  if (count($landing_node->field_landing_comission_stories->getValue()) > $stories_least) {
                    $image_style = 'thumbnail_916x400';
                  }
                }
              }
              $variable_img_style = [
                '#markup' => PreprocessNodeManager::buildImageStyle($thumbnail, $image_style),
                '#cache' => [
                  'tags' => ['node:' . $landing_node->id()],
                ],
              ];
              $variables['para_banner_background'] = $variable_img_style;
            }
          }
          if ($variables['view_mode'] == 'comissioned_stories') {
            PreprocessNodeManager::loadBtnShareForSpotlight($variables, $node);
            PreprocessNodeManager::loadHumankindSpotlight($variables, $spotlight_format, $node);
          }
          break;
        case 'all_commision_stories':
          if (isset($thumbnail)) {
            $image_style = 'thumbnail_1400x300';
            $landing_node = \Drupal::routeMatch()->getParameter('node');
            if ($landing_node instanceof \Drupal\node\NodeInterface) {
              if ($landing_node->getType() == 'list_page') {
                if (isset($landing_node->field_list_page_spotlight_list) && !empty($landing_node->field_list_page_spotlight_list->getValue())) {
                  if (count($landing_node->field_list_page_spotlight_list->getValue()) > 1) {
                    $image_style = 'thumbnail_916x400';
                  }
                }
              }
              $variable_img_style = [
                '#markup' => PreprocessNodeManager::buildImageStyle($thumbnail, $image_style),
                '#cache' => [
                  'tags' => ['node:' . $landing_node->id()],
                ],
              ];

              $variables['para_banner_background'] = $variable_img_style;
            }
          }
          break;
        case 'persona_detail':
          /** Content type is Spotlight */
          if (isset($thumbnail)) {
            $image_style = 'thumbnail_500x260';
            $landing_node = \Drupal::routeMatch()->getParameter('node');
            if ($landing_node instanceof \Drupal\node\NodeInterface) {
              // You can get nid and anything else you need from the node object.
              if ($landing_node->getType() == 'story' && isset($landing_node->field_story_landing_node) && !empty($landing_node->field_story_landing_node->target_id)) {
                $landing_node = Node::load($landing_node->field_story_landing_node->target_id);
              }
              if ($landing_node->getType() == 'list_page') {
                if (isset($landing_node->field_list_page_spotlight_list) && !empty($landing_node->field_list_page_spotlight_list->getValue())) {
                  if (count($landing_node->field_list_page_spotlight_list->getValue()) > 1) {
                    $image_style = 'thumbnail_916x400';
                  }
                }
              }
            }
            PreprocessNodeManager::loadThumbnailByImageStyleForSpotlight($variables, $thumbnail, $image_style);
          }
          break;
        case 'search_content_result':
          if (isset($thumbnail)) {
            $image_style = 'thumbnail_424x236';
            PreprocessNodeManager::loadThumbnailByImageStyleForSpotlight($variables, $thumbnail, $image_style);
          }
          break;
        case 'migrant_workers_landing_sub_stories':
          if (isset($media)) {
            PreprocessNodeManager::renderSpotlightImageResponsiveThumbnail($media, $variables, 'responsive_910x400', ['obw-card__image']);
          }
          break;
        case 'migrant_workers_orgs':
          if (isset($media)) {
            PreprocessNodeManager::renderSpotlightImageResponsiveThumbnail($media, $variables, 'responsive_350x200', ['obw-card__image']);
          }
          break;
        case 'spotlight_with_credit_name':
          if (isset($media)) {
            PreprocessNodeManager::renderSpotlightImageResponsiveThumbnail($media, $variables, 'responsive_medium_600x400_3_2_', ['obw-card__image']);
          }
          break;
        default:
          if (isset($thumbnail)) {
            PreprocessNodeManager::loadThumbnailByImageStyleForSpotlight($variables, $thumbnail, $image_style);
          }
      }

      if ($node->get('field_partner_url')[0]) {
        $variables['spotlight_url'] = Url::fromUri($node->get('field_partner_url')[0]->getValue()['uri'])
          ->toString();
        if ($node->get('field_partner_url')[0]->getValue()['options']) {
          $target = isset($node->get('field_partner_url')[0]->getValue()['options']['attributes']['target']) ? $node->get('field_partner_url')[0]->getValue()['options']['attributes']['target'] : '_self';
          $variables['spotlight_url_options'] = $target;
        }
      }
    }
  }

  public static function landingPagePreprocess(&$variables) {
    $node = $variables['elements']['#node'];
    $account = Drupal::currentUser();
    $user_id = $account->id();
    switch ($variables['view_mode']) {
      case 'full':
        if (isset($node->field_story_sub_stories) && !empty($node->field_story_sub_stories->getValue())) {
          $sub_story_list = PreprocessNodeManager::loadSubStories($node, 'field_story_sub_stories', 'field_story_thumbnail');
          if (!empty($sub_story_list)) {
            $first_story = $sub_story_list[0];
            unset($sub_story_list[0]);
            shuffle($sub_story_list);
            $variables['first_story'] = $first_story;
            $variables['sub_story_list'] = $sub_story_list;
          }
        }

        PreprocessNodeManager::loadForumBlock($node, 'field_landing_forum_block', $variables);
        if (isset($node->field_landing_cta_introduction) && !empty($node->field_landing_cta_introduction->value)) {
          $variables['cta_introduction'] = $node->field_landing_cta_introduction->value;
        }
        break;
      default:
    }
  }

  private static function loadSubStories($node, $field_name, $field_image) {
    $etm = Drupal::entityTypeManager();
    $sub_story_list = [];
    foreach ($node->$field_name->referencedEntities() as $sub_node) {
      $sub_story = [];
      $sub_story['title'] = $sub_node->getTitle();
      $sub_story['published_date'] = !empty($sub_node->published_at->value) ? date('d M Y', $sub_node->published_at->value) : '';
      switch ($sub_node->getType()) {
        case 'story':
          $sub_story['url'] = $sub_node->toURL()->toString();
          if (isset($sub_node->$field_image) && !empty($sub_node->$field_image->getValue())) {
            $story_thumbnail = $sub_node->$field_image->getValue()[0];
            $file = $etm->getStorage('file')
              ->load($story_thumbnail['target_id']);
            $file_uri = $file->uri->value;
            $sub_story['img_original'] = self::buildImageStyle($file_uri, 'banner_gstory_1140x517');
            $sub_story['img_thumbnail_255x140'] = self::buildImageStyle($file_uri, 'thumbnail_255x140');
            $sub_story['img_thumbnail_730x400'] = self::buildImageStyle($file_uri, 'medium_730x400');

            $sub_story['img_alt'] = !empty($story_thumbnail['alt']) ? $story_thumbnail['alt'] : '';
            $sub_story['img_title'] = !empty($story_thumbnail['title']) ? $story_thumbnail['title'] : '';
          }
          if (isset($sub_node->field_story_short_description) && !empty($sub_node->field_story_short_description->value)) {
            $sub_story['short_desc'] = $sub_node->field_story_short_description->value;
          }
          break;
        case 'spotlight':
          if (isset($sub_node->field_partner_url) && !empty($sub_node->field_partner_url->getValue())) {
            $partner_url = $sub_node->field_partner_url->getValue()[0];
            $sub_story['url'] = $partner_url['uri'];
          }
          if (isset($sub_node->field_story_spotlight) && !empty($sub_node->field_story_spotlight->getValue())) {
            $para_spotlight = $sub_node->field_story_spotlight->referencedEntities()[0];
            if (isset($para_spotlight->field_para_banner_title) && !empty($para_spotlight->field_para_banner_title->value)) {
              $sub_story['title'] = $para_spotlight->field_para_banner_title->value;
            }

            if (isset($para_spotlight->field_para_banner_intro) && !empty($para_spotlight->field_para_banner_intro->value)) {
              $sub_story['short_desc'] = $para_spotlight->field_para_banner_intro->value;
            }

            if (isset($para_spotlight->field_para_banner_background) && !empty($para_spotlight->field_para_banner_background->entity)) {
              $file_uri = $para_spotlight->field_para_banner_background->entity->get('thumbnail')[0]->entity->uri->value;
              $image_original = file_url_transform_relative(file_create_url($file_uri));
              $sub_story['img_original'] = $image_original;
              $sub_story['img_thumbnail_255x140'] = self::buildImageStyle($file_uri, 'thumbnail_255x140');
              $sub_story['img_thumbnail_730x400'] = self::buildImageStyle($file_uri, 'medium_730x400');;
              $sub_story['img_alt'] = !empty($story_thumbnail['alt']) ? $story_thumbnail['alt'] : '';
              $sub_story['img_title'] = !empty($story_thumbnail['title']) ? $story_thumbnail['title'] : '';
            }
          }
          break;
        default:
      }
      $sub_story_list[] = $sub_story;
    }
    return $sub_story_list;
  }

  private static function loadForumBlock($node, $field_name, &$variables) {
    if (isset($node->$field_name) && !empty($node->$field_name->getValue())) {
      $variables['#attached']['library'][] = 'obw_theme/forum-style';
      //      $variables['#attached']['library'][] = 'obw_theme/mental-health-campaign';
      $para_forum_block = $node->$field_name->referencedEntities()[0];
      if (isset($para_forum_block->field_para_forum_description) && !empty($para_forum_block->field_para_forum_description->value)) {
        $variables['forum_desc_html'] = $para_forum_block->field_para_forum_description->value;
      }

      if (isset($para_forum_block->field_para_forum_sync_to_forum) && !empty($para_forum_block->field_para_forum_sync_to_forum->value) && $para_forum_block->field_para_forum_sync_to_forum->value === '1') {
        if (isset($para_forum_block->field_para_forum_api_get_topic) && !empty($para_forum_block->field_para_forum_api_get_topic->value)) {
          $api_get_topic = $para_forum_block->field_para_forum_api_get_topic->value;
          $action_service = Drupal::service('action_entity.action_service');
          $api_topic_response = $action_service->cUrlGet($api_get_topic, [], []);
          $list_topics = [];
          if (!empty($api_topic_response) && !empty($api_topic_response['payload']) && !empty($api_topic_response['payload']['showTopics'])) {
            $domain_forum = 'http://stg.ourbetterworld.org/forum';
            if (isset($para_forum_block->field_para_forum_domain) && !empty($para_forum_block->field_para_forum_domain->value)) {
              $domain_forum = $para_forum_block->field_para_forum_domain->value;
            }
            foreach ($api_topic_response['payload']['showTopics'] as $topic_response) {
              $topic = [
                'tid' => $topic_response['tid'],
                'title' => $topic_response['titleRaw'],
                'votes' => $topic_response['votes'],
                'replies' => $topic_response['postcount'] > 0 ? $topic_response['postcount'] - 1 : 0,
                'url' => $domain_forum . '/topic/' . $topic_response['tid'],
              ];
              $list_topics[] = $topic;
            }
          }
          if (!empty($list_topics)) {
            $variables['list_topics'] = $list_topics;
          }
          else {
            if (isset($para_forum_block->field_para_forum_msg_no_topic) && !empty($para_forum_block->field_para_forum_msg_no_topic->value)) {
              $variables['msg_no_topic'] = $para_forum_block->field_para_forum_msg_no_topic->value;
            }
          }
        }
      }

      if (isset($para_forum_block->field_para_forum_create_topic) && !empty($para_forum_block->field_para_forum_create_topic->value)) {
        $variables['create_topic_html'] = $para_forum_block->field_para_forum_create_topic->value;
      }
    }
    else {
      $variables['dont_show_forum'] = TRUE;
    }
  }

  public static function renderNextStory($node, &$variable) {
    if (isset($node->field_story_next_story) && !empty($node->field_story_next_story->referencedEntities())) {
      $next_story = $node->field_story_next_story->referencedEntities()[0];
      if (!$next_story->get('field_story_thumbnail')->isEmpty()) {
        // One line uri without loading.
        if ($next_story->get('field_story_thumbnail')->entity) {
          $thumbnail = $next_story->get('field_story_thumbnail')->entity->uri->value;
          $variables['background_thumbnail'] = self::buildImageStyle($thumbnail, 'large_1440x617');
          $attrs = $next_story->get('field_story_thumbnail')->getValue();
          $img_alt = isset($attrs['alt']) ? $attrs['alt'] : '';
          $img_title = isset($attrs['title']) ? $attrs['title'] : '';
        }

        if (!empty(\Drupal::hasService('obw_utilities.responsive_background_image'))) {
          $css_selector = '.wl-story__next-story__background-image';
          $response_service = Drupal::service('obw_utilities.responsive_background_image');
          $responsive_image = $response_service->generateMediaQueries($css_selector, $next_story, 'field_story_thumbnail', 'responsive_image_1140x489_21_9');
          if ($responsive_image) {
            $variable['#attached']['html_head'][] = $responsive_image;
          }
        }

      }

      $next_story_params = [
        'title' => $next_story->getTitle(),
        'url' => $next_story->toURL()->toString(),
        'img' => isset($image1440x617) ? $image1440x617 : '',
        'img_alt' => isset($img_alt) ? $img_alt : '',
        'img_title' => isset($img_title) ? $img_title : '',
        'published' => $next_story->status->value == '1' ? TRUE : FALSE,
        'btn_launch' => (isset($next_story->field_story_btn_launch_date) && !empty($next_story->field_story_btn_launch_date->value)) ? $next_story->field_story_btn_launch_date->value : 'Coming soon',
      ];

      $variable['next_story_param'] = $next_story_params;

      if (!$next_story->get('field_story_slider_image')->isEmpty()) {
        // One line uri without loading.
        if ($next_story->get('field_story_slider_image')->entity) {
          $thumbnail = $next_story->get('field_story_slider_image')->entity->uri->value;
          $image1440x617 = self::buildImageStyle($thumbnail, '759_x_686');
          if (!empty($next_story->get('field_story_slider_image')
            ->getValue())) {
            $attrs = $next_story->get('field_story_slider_image')
              ->getValue()[0];
            $img_alt = isset($attrs['alt']) ? $attrs['alt'] : '';
            $img_title = isset($attrs['title']) ? $attrs['title'] : '';
          }
        }

        $next_story_image_mobile = [
          'img' => isset($image1440x617) ? $image1440x617 : '',
          'img_alt' => isset($img_alt) ? $img_alt : '',
          'img_title' => isset($img_title) ? $img_title : '',
        ];
        $variable['next_story_image_mobile'] = $next_story_image_mobile;
      }

    }
  }

  public static function renderNodeImageResponsiveThumbnail($node, $field_name, &$variables, $responsive_image_style_id, $class_attr = []) {
    if (!$node->get($field_name)->isEmpty()) {
      // One line uri without loading.
      if ($node->get($field_name)->entity) {
        $response_service = Drupal::service('obw_utilities.responsive_image');
        $responsive_image = $response_service->responsiveImageStyle($node->get($field_name), $responsive_image_style_id, $class_attr);
        $variables['background_thumbnail'] = $responsive_image;
      }
    }
  }

  public static function renderSpotlightImageResponsiveThumbnail($media, &$variables, $responsive_image_style_id, $class_attr = []) {
    $response_service = Drupal::service('obw_utilities.responsive_image');
    $responsive_image = $response_service->responsiveImageStyle($media->get('thumbnail'), $responsive_image_style_id, $class_attr);
    $variables['para_banner_background'] = $responsive_image;
  }

  public static function getTagsWithoutLink($node, &$variables, $view_mode) {
    $field_tags_without_link = NULL;

    switch ($view_mode) {
      case 'mental_health_2020_sub_stories':
      case 'refugees_2021_stories':
      case 'mental_health_2021_sub_stories':
        $field_tags_without_link = 'field_mental_health_label';
        break;
      case 'mental_health_2020_resources':
        $field_tags_without_link = 'field_story_mh2020_resource_tags';
        break;
      case 'migrant_workers_landing_sub_stories':
        $field_tags_without_link = 'field_migrant_workers_tags';
        break;
      case 'wl_landing_2020_launched_story':
      case 'wl_landing_2021_launched_story':
      case 'wl_landing_2021_launching_story':
      case 'wl_landing_2021_resources':
      case 'wl_landing_2022_resources':
      case 'wl_resources_2021_first_resource':
      case 'wl_resources_2021_resources_remaining':
        $field_tags_without_link = 'field_wildlife_label';
        break;
      default:
        return;
    }

    if (isset($node->$field_tags_without_link) && !empty($node->$field_tags_without_link->getValue())) {
      $variables['tags_without_link'] = [];
      foreach ($node->$field_tags_without_link->referencedEntities() as $taxo) {
        $variables['tags_without_link'][] = $taxo->name->value;
      }
    }
  }

  public static function getReadingTimeNode($node, &$variables, $return = NULL) {
    $moduleHandler = \Drupal::service('module_handler');
    if (isset($node->field_story_override_read_time) && !empty($node->field_story_override_read_time->value)) {
      $variables['reading_time'] = ' ' . $node->field_story_override_read_time->value;
    }
    elseif ($moduleHandler->moduleExists('node_read_time')) {
      $words_per_minute = \Drupal::config('node_read_time.settings')
        ->get('reading_time')['words_per_minute'] ?: 225;
      $reading_time_service = \Drupal::service('reading_time');
      $reading_time = $reading_time_service
        ->setWordsPerMinute($words_per_minute)
        ->collectWords($node)
        ->calculateReadingTime()
        ->getReadingTime();

      // Clear the words variable.
      $reading_time_service->setWords(0);
      $variables['reading_time'] = ' ' . str_replace(' minute', '-minute', str_replace('minutes', 'minute', $reading_time)) . ' read';
    }
    if ($return) {
      return $variables['reading_time'];
    }
  }

  public static function getCTADataAction(&$variables, $cta_node) {
    global $base_url;
    $data_cta = [
      'site_domain' => $base_url,
      'obj_url' => $cta_node->toUrl()->toString(),
      'user_id' => \Drupal::currentUser()
        ->id() == 0 ? 'anonymous' : \Drupal::currentUser()->id(),
      'obj_title' => $cta_node->getTitle(),
      'obj_type' => 'node',
      'obj_subtype' => $cta_node->getType(),
      'obj_id' => $cta_node->id(),
      'action_type' => 'click',
      'action_status' => 'add',
    ];
    $variables['#attached']['drupalSettings']['data_cta'][$cta_node->id()] = $data_cta;
  }

  public static function checkStoryNoThumbnail($node, &$variables) {
    if (isset($node->field_story_thumbnail) && !empty($node->field_story_thumbnail->getValue())) {
      return FALSE;
    }
    if (isset($node->field_story_spotlight) && !empty($node->field_story_spotlight->entity)) {
      if (isset($node->field_story_spotlight->entity->field_para_banner_background)
        && !empty($node->field_story_spotlight->entity->field_para_banner_background->getValue())) {
        return FALSE;
      }
    }

    $variables['story_no_image'] = TRUE;

  }

  public static function getAnchorMh2020Resource($node, &$variables) {
    $current_path = \Drupal::service('path.current')->getPath();
    $url = \Drupal::service('path.validator')->getUrlIfValid($current_path);
    if ($url && $url->isRouted()) {
      if ($url->getRouteName() == 'view.story.page_mental_health_2020_help_page') {
        if (\Drupal::request()->query->get('topic_id')) {
          $topic_id = \Drupal::request()->query->get('topic_id');
          if (isset($node->field_story_anchor) && !empty($node->field_story_anchor->getValue())) {
            $etm = \Drupal::entityTypeManager();
            foreach (array_column($node->field_story_anchor->getValue(), 'target_id') as $para_id) {
              $para = $etm->getStorage('paragraph')->load($para_id);
              if ($para) {
                if (isset($para->field_mh2020_anchor_topic) && !empty($para->field_mh2020_anchor_topic->target_id)
                  && $para->field_mh2020_anchor_topic->target_id == $topic_id) {
                  if (isset($para->field_mh2020_anchor_name) && !empty($para->field_mh2020_anchor_name->value)) {
                    $variables['anchor_name'] = $para->field_mh2020_anchor_name->value;
                    return;
                  }
                }
              }
            }
          }
        }

      }
    }
  }

  public static function renderCalendarBlock($node, &$variables) {
    if ($node && $node->getType() == 'event') {
      if (isset($node->field_event_add_to_calendar) && !empty($node->field_event_add_to_calendar->getValue())
        && !empty($node->field_event_add_to_calendar->getValue()[0])) {
        if (!empty($node->field_event_add_to_calendar->getValue()[0]['plugin_id'])
          && $node->field_event_add_to_calendar->getValue()[0]['plugin_id'] == 'obw_add_to_event_block') {
          $settings = $node->field_event_add_to_calendar->getValue()[0]['settings'];
          $id_event = $settings['id_event'];

          $event_params = new \stdClass();
          $today = date("Ymd\THis");
          $event_params->subject = isset($settings['subject']) ? $settings['subject'] : 'no-subject';
          $event_params->date_start = isset($settings['date_start']) ? $settings['date_start'] : $today;
          $event_params->date_end = isset($settings['date_end']) ? $settings['date_end'] : $today;
          $event_params->details = isset($settings['details']) ? $settings['details'] : '';
          $event_params->locations = isset($settings['locations']) ? $settings['locations'] : '';
          $event_params->id_event = isset($settings['id_event']) ? $settings['id_event'] : 'no-event';

          $html_add_calendar_block = '<div title="" id="' . $id_event . '" class="addeventatc" style="visibility: visible; z-index: 100004;">Add to Calendar
            <span class="addeventatc_icon atc_node notranslate"></span>
            <span id="' . $id_event . '-drop" class="addeventatc_dropdown" aria-hidden="false" aria-labelledby="' . $id_event . '">
            <a class="ateappleical" id="' . $id_event . '-appleical" role="menuitem">Apple</a>
            <a class="ategoogle" id="' . $id_event . '-google" target="_blank" role="menuitem">Google</a>
            <a class="ateoutlookcom" id="' . $id_event . '-outlookcom" target="_blank" role="menuitem">Outlook.com</a>
            <a class="ateyahoo" id="' . $id_event . '-yahoo" target="_blank" role="menuitem">Yahoo</a></span></div>';

          //5466
          if(isset($_SESSION['event_attend_value']) && !empty($_SESSION['event_attend_value'])){
            $text = $_SESSION['event_attend_value'];
          }else{
            $text = "sorry cant get the value";
          }

          if($text == "in-person"){
            $id_event = $settings['id_event_in_person'];
            $event_params->subject = isset($settings['subject_in_person']) ? $settings['subject_in_person'] : 'no-subject';
            $event_params->date_start = isset($settings['date_start_in_person']) ? $settings['date_start_in_person'] : $today;
            $event_params->date_end = isset($settings['date_end_in_person']) ? $settings['date_end_in_person'] : $today;
            $event_params->details = isset($settings['details_in_person']) ? $settings['details_in_person'] : '';
            $event_params->locations = isset($settings['locations_in_person']) ? $settings['locations_in_person'] : '';
            $event_params->id_event = isset($settings['id_event_in_person']) ? $settings['id_event_in_person'] : 'no-event';

            $html_add_calendar_block = '
            <div title="" id="' . $id_event . '" class="addeventatc" style="visibility: visible; z-index: 100004;">Add to Calendar
              <span class="addeventatc_icon atc_node notranslate"></span>
              <span id="' . $id_event . '-drop" class="addeventatc_dropdown" aria-hidden="false" aria-labelledby="' . $id_event . '">
              <a class="ateappleical" id="' . $id_event . '-appleical" role="menuitem">Apple</a>
              <a class="ategoogle" id="' . $id_event . '-google" target="_blank" role="menuitem">Google</a>
              <a class="ateoutlookcom" id="' . $id_event . '-outlookcom" target="_blank" role="menuitem">Outlook.com</a>
              <a class="ateyahoo" id="' . $id_event . '-yahoo" target="_blank" role="menuitem">Yahoo</a></span></div>';
          }
          //5466
          $json_event_params = json_encode($event_params);
          $variables['add_calendar_block'] = $html_add_calendar_block;
          $variables['#attached']['library'][] = 'obw_utilities/obw-add-to-event';
          $variables['#attached']['drupalSettings']['event_params'] = [$event_params->id_event => $json_event_params];
        }
      }
    }
  }

  public static function loadHumankindSpotlight(&$variables, $spotlight_format, $node) {
    switch ($spotlight_format) {
      case 'humankind_static':
        $variables['spotlight_static'] = TRUE;
        if (isset($node->field_spotlight_html) && !empty($node->field_spotlight_html->value)) {
          $variables['spotlight_html'] = $node->field_spotlight_html->value;
        }
        break;
      default:
        if (isset($node->field_spotlight_comissioned_type) && !empty($node->field_spotlight_comissioned_type->referencedEntities())) {
          $taxo_comissioned_type = $node->field_spotlight_comissioned_type->referencedEntities()[0];
          if (isset($taxo_comissioned_type->description) && !empty($taxo_comissioned_type->description->value)) {
            $variables['spotlight_comissioned_type'] = $taxo_comissioned_type->description->value;
          }
        }
        if (isset($node->field_humankind_tag) && !empty($node->field_humankind_tag->value)) {
          $variables['humankind_tag'] = $node->field_humankind_tag->value;
        }
        if (isset($paragraph->field_para_photo_credit) && !empty($paragraph->field_para_photo_credit->value)) {
          $variables['spotlight_photo_credit'] = $paragraph->field_para_photo_credit->value;
        }
        break;
    }
  }

  public static function loadBtnShareForSpotlight(&$variables, $node) {
    $variables['spotlight_title_btn'] = $node->get('field_partner_url')[0]->getValue()['title'];
    $variables['share_buttons'] = VRStoryPreprocessNodeManager::GetShareBlock($node->getTitle(), $node, $node->toURL()
      ->toString(), '/form/email-a-friend');
  }

  public static function loadThumbnailByImageStyleForStory(&$variables, $image_style, $node, $field_image_name, $variable_name = 'background_thumbnail') {
    $variables['background_thumbnail_alt'] = '';
    $variables['background_thumbnail_title'] = '';
    switch ($field_image_name) {
      case 'field_story_feature_media':
        if (!$node->get($field_image_name)->isEmpty()) {
          // One line uri without loading.
          if ($node->get($field_image_name)->entity) {
            $thumbnail = $node->get($field_image_name)->entity->get('thumbnail')[0]->entity->uri->value;
            $attrs = !empty($node->get($field_image_name)->entity->get('thumbnail')[0]->getValue()) ? $node->get($field_image_name)->entity->get('thumbnail')[0]->getValue() : [];
          }
        }
        break;
      default:
        if (!$node->get($field_image_name)->isEmpty()) {
          // One line uri without loading.
          if ($node->get($field_image_name)->entity) {
            $thumbnail = $node->get($field_image_name)->entity->uri->value;
            $attrs = $node->get($field_image_name)->getValue();
          }
        }
    }
    if (isset($thumbnail)) {
      $variables[$variable_name] = PreprocessNodeManager::buildImageStyle($thumbnail, $image_style);
      if (!empty($attrs)) {
        $variables['background_thumbnail_alt'] = !empty($attrs['alt']) ? $attrs['alt'] : '';
        $variables['background_thumbnail_title'] = !empty($attrs['title']) ? $attrs['title'] : '';
      }
    }
  }

  public static function getThumbnailUrlByImageStyleForStory($image_style, $node, $field_image_name) {
    $thumbnail_url = NULL;
    switch ($field_image_name) {
      case 'field_story_feature_media':
        if (!$node->get($field_image_name)->isEmpty()) {
          // One line uri without loading.
          if ($node->get($field_image_name)->entity) {
            $thumbnail = $node->get($field_image_name)->entity->get('thumbnail')[0]->entity->uri->value;
            $attrs = !empty($node->get($field_image_name)->entity->get('thumbnail')[0]->getValue()) ? $node->get($field_image_name)->entity->get('thumbnail')[0]->getValue() : [];
          }
        }
        break;
      default:
        if (!$node->get($field_image_name)->isEmpty()) {
          // One line uri without loading.
          if ($node->get($field_image_name)->entity) {
            $thumbnail = $node->get($field_image_name)->entity->uri->value;
            $attrs = $node->get($field_image_name)->getValue();
          }
        }
    }
    if (isset($thumbnail)) {
      $thumbnail_url = PreprocessNodeManager::buildImageStyle($thumbnail, $image_style);
    }
    return $thumbnail_url;
  }

  public static function getThumbnailUrlByImageStyleForSpotlight($paragraph, $image_style) {
    $thumbnail_url = FALSE;
    if (isset($paragraph->field_para_banner_background) && $paragraph->field_para_banner_background->first()) {
      if ($paragraph->field_para_banner_background->first()->getValue()) {
        $image_field = $paragraph->field_para_banner_background->first()
          ->getValue();
        $media = Media::load($image_field['target_id']);
        if ($media && $media->get('thumbnail')) {
          $thumbnail = $media->get('thumbnail')[0]->entity->uri->value;
          $thumbnail_url = PreprocessNodeManager::buildImageStyle($thumbnail, $image_style);
        }
      }
    }

    return $thumbnail_url;
  }

  public static function loadThumbnailByImageStyleForSpotlight(&$variables, $thumbnail, $image_style) {
    $variables['para_banner_background'] = PreprocessNodeManager::buildImageStyle($thumbnail, $image_style);
  }

  public static function buildImageStyle($thumbnail, $image_style) {
    $image_style = ImageStyle::load($image_style);
    return file_url_transform_relative($image_style->buildUrl($thumbnail));
  }

  public static function checkUserSubscribeObw() {
    if (\Drupal::currentUser()->isAuthenticated()) {
      $ac_controller = new ActiveCampaignController();
      $result_contact_view = $ac_controller->getACContactDetailByEmail(\Drupal::currentUser()
        ->getEmail());
      if ($result_contact_view && !empty($result_contact_view['lists'])) {
        $config = \Drupal::config('obw_contributor_profile.config');
        $weekly_subscribe = $config->get('AC_LIST_ID_WEEKLY_FOR_OBW');
        $monthly_subscribe = $config->get('AC_LIST_ID_MONTHLY_FOR_OBW');
        $lists_subscribed_ac = self::getListsSubscribedOnActiveCampaign($result_contact_view['lists']);

        if (!empty($weekly_subscribe) && in_array($weekly_subscribe, $lists_subscribed_ac)) {
          return 'Weekly';
        }
        if (!empty($monthly_subscribe) && in_array($monthly_subscribe, $lists_subscribed_ac)) {
          return 'Monthly';
        }

      }
    }
    return FALSE;
  }

  private static function getListsSubscribedOnActiveCampaign($lists = []) {
    $lists_subscribed = [];
    foreach ($lists as $list_id => $list_value) {
      if ($list_value['status'] == '1') {
        $lists_subscribed[] = $list_id;
      }
    }
    return $lists_subscribed;
  }


  public static function getYoutubeId($node, $field_name) {
    $yt_id = FALSE;
    if (isset($node->$field_name) && !empty($node->$field_name->entity)
      && isset($node->$field_name->entity->field_media_video_embed_field)
      && !empty($node->$field_name->entity->field_media_video_embed_field->value)) {
      preg_match('%(?:youtube(?:-nocookie)?\.com/(?:[^/]+/.+/|(?:v|e(?:mbed)?)/|.*[?&]v=)|youtu\.be/)([^"&?/ ]{11})%i', $node->$field_name->entity->field_media_video_embed_field->value, $match);
      $yt_id = $match[1];
    }
    return $yt_id;
  }

  public static function getLengthMarkupField($node, $field_name) {
    $len = 0;
    if (isset($node->$field_name) && !empty($node->$field_name->value)) {
      $striptags = strip_tags($node->$field_name->value);
      $replace_striptags = preg_replace('/\s+/', ' ', $striptags);
      $len = mb_strlen($replace_striptags);
    }
    return $len;
  }

  public static function getNodeFormat($type) {
    $node_format_config = \Drupal::config('obw_utilities.node_format.config');
    $field_name = '';
    $format = [];
    switch ($type) {
      case 'story':
        $field_name = 'OBW_NODE_STORY_FORMAT';
        break;
      case 'landing':
        $field_name = 'OBW_NODE_LANDING_FORMAT';
        break;
      case 'pdf':
        $field_name = 'OBW_NODE_PDF_FORMAT';
        break;
      case 'event':
        $field_name = 'OBW_NODE_EVENT_FORMAT';
        break;
      case 'page':
        $field_name = 'OBW_NODE_PAGE_FORMAT';
        break;
      case 'cta':
        $field_name = 'OBW_NODE_CTA_FORMAT';
        break;
    }
    if (!empty($preg_split = preg_split("/\r\n/", $node_format_config->get($field_name)))) {
      $format = self::splitByVerticalBar($preg_split);
    }
    return $format;
  }

  private static function splitByVerticalBar($items) {
    $result = [];
    foreach ($items as $item) {
      if (!empty($explode = explode('|', $item)) && count($explode) == 2) {
        $result[$explode[0]] = $explode[1];
      }
    }
    return $result;
  }

  public static function getStoryInfo($node, &$variables, $image_style = 'medium_730x400') {
    $story_title = $node->getTitle();
    $story_thumbnail_url = self::getThumbnailUrlByImageStyleForStory($image_style, $node, 'field_story_feature_media');
    $story_desc = !empty($node->field_story_short_description->value) ? $node->field_story_short_description->value : '';
    if ($node->hasField('field_story_spotlight') && !empty($node->field_story_spotlight->referencedEntities())) {
      $story_spotlight_para = $node->field_story_spotlight->referencedEntities()[0];
      if (isset($story_spotlight_para->field_para_banner_title) && !empty($story_spotlight_para->field_para_banner_title->value)) {
        $story_title = $story_spotlight_para->field_para_banner_title->value;
      }
      if (self::getThumbnailUrlByImageStyleForSpotlight($story_spotlight_para, $image_style)) {
        $story_thumbnail_url = self::getThumbnailUrlByImageStyleForSpotlight($story_spotlight_para, 'medium_730x400');
      }

      if (isset($story_spotlight_para->field_para_banner_intro) && !empty($story_spotlight_para->field_para_banner_intro->value)) {
        $story_desc = $story_spotlight_para->field_para_banner_intro->value;
      }
    }

    if ($node->hasField('field_story_notify_launch') && !empty($node->field_story_notify_launch->target_id)) {
      $variables['story_webform'] = [
        '#lazy_builder' => ['\Drupal\obw_utilities\WebformLazyBuilder::buildWebform', [$node->field_story_notify_launch->target_id]],
        '#create_placeholder' => TRUE,
      ];
    }
    else {
      $variables['story_webform'] = NULL;
    }

    $variables['story_title'] = $story_title;
    $variables['story_thumbnail'] = $story_thumbnail_url;
    $variables['story_desc'] = $story_desc;
    PreprocessNodeManager::getReadingTimeNode($node, $variables, FALSE);
  }

}
