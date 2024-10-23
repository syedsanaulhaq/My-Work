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
 *  Alter password form to add css class and submit action
 *
 * Class UserPasswordFormAlter
 *
 * @package Drupal\obw_utilities\Form
 */
class UserPasswordForm extends FormAlterBase {


  /**
   * @param array $form
   * @param FormStateInterface $form_state
   */
  public function alterForm(array &$form, FormStateInterface $form_state) {
    $session_handler = \Drupal::service('obw_social.session_handler');

    $form['#prefix'] = '<div id="user-action-pass-wrapper">';
    $form['#suffix'] = '</div>';

    $session = \Drupal::service('obw_social.session_handler');

    $message = $session->get('onetime_login_link_error');
    $session->clear('onetime_login_link_error');
    if (!empty($message)) {
      $form['error'] = $message;
    }

    $action_campaign = \Drupal::request()->query->get('action_campaign');
    $follow_normal_url = \Drupal::request()->query->get('follow-normal-url');
    $vr_series = \Drupal::request()->query->get('vr_series');
    $come_back = \Drupal::request()->query->get('come_back');
    $fe_url = \Drupal::request()->query->get('fe_url');
    $source = \Drupal::request()->query->get('source');
    $wl_ebook_id = \Drupal::request()->query->get('wl_ebook_id');
    $session_handler = \Drupal::service('obw_social.session_handler');

    if ($session_handler->get('action_campaign') !== NULL) {
      $session_handler->clear('action_campaign');
    }
    if (isset($action_campaign) && $action_campaign == 'true') {
      $session_handler->set('action_campaign', TRUE);
    }

    if ($session_handler->get('action_campaign_normal_url') !== NULL) {
      $session_handler->clear('action_campaign_normal_url');
    }

    if (isset($follow_normal_url) && $follow_normal_url == 'true') {
      $session_handler->set('action_campaign_normal_url', TRUE);
    }

    if (isset($vr_series)) {
      $session_handler->set('reset_to_vr_series', $vr_series);
      $form['#vr_series_page'] = TRUE;
    }
    else {
      if ($session_handler->get('reset_to_vr_series') !== NULL) {
        $session_handler->clear('reset_to_vr_series');
      }
    }

    if (!$come_back && $session_handler->get('come_back_url') !== NULL) {
      $come_back = $session_handler->get('come_back_url');
    }

    if (isset($come_back)) {
      $session_handler->set('come_back', $come_back);
      if ($source) {
        $session_handler->set('come_back', $session_handler->get('come_back') . '&source=' . $source);
      }

      if (isset($wl_ebook_id)) {
        $session_handler->set('come_back', $session_handler->get('come_back') . '&wl_ebook_id=' . $wl_ebook_id);
      }
    }
    else {
      if ($session_handler->get('come_back') !== NULL) {
        $session_handler->clear('come_back');
      }
    }

    $request_queries = \Drupal::request()->query;
    if (isset($fe_url)) {
      $topic_title = !empty($request_queries->get('topic_title')) ? $request_queries->get('topic_title') : FALSE;
      $topic_content = !empty($request_queries->get('topic_content')) ? $request_queries->get('topic_content') : FALSE;
      $redirect_to_fe = $fe_url;
      if ($topic_title && $topic_content) {
        $redirect_to_fe .= '&topic_title=' . rawurlencode($topic_title) . '&topic_content=' . rawurlencode($topic_content);
      }

      $uid = !empty($request_queries->get('uid')) ? $request_queries->get('uid') : FALSE;
      $sid = !empty($request_queries->get('sid')) ? $request_queries->get('sid') : FALSE;
      $action = !empty($request_queries->get('action')) ? $request_queries->get('action') : FALSE;

      if ($uid && $sid && $action) {
        $redirect_to_fe .= '&uid=' . $uid . '&sid=' . $sid . '&action=' . $action;
      }

      $session_handler->set('redirect_to_fe', $redirect_to_fe);
    }
    else {
      if ($session_handler->get('redirect_to_fe') !== NULL) {
        $session_handler->clear('redirect_to_fe');
      }
    }

    $form['#theme'] = ['obw_user_pass_form'];
    $form['name']['#title'] = t('Email');
    $form['name']['#placeholder'] = t('Email address');
    if ($session_handler->get('campaign_id') !== NULL) {
      $form['#is_campaign'] = TRUE;
      $form['#validate'][] = [$this, 'userAccessToCampaignValidate'];
    }
    $form['actions']['submit']['#value'] = t('Reset my password');
    $form['actions']['submit']['#attributes'] = [
      'class' => [
        'use-ajax',
      ],
    ];
    $form['actions']['submit']['#ajax'] = [
      'callback' => [
        '\Drupal\obw_utilities\AccountMgmt',
        'submitOBWUserResetPassFormAjax',
      ],
      'event' => 'click',
    ];
  }

  public static function userAccessToCampaignValidate($form, \Drupal\Core\Form\FormStateInterface &$form_state) {
    $form_errors = $form_state->getErrors();
    $name = $form_state->getValue('name');

    $session_handler = \Drupal::service('obw_social.session_handler');

    $campaign_name = $session_handler->get('campaign_id');

    $path = \Drupal::service('path_alias.manager')
      ->getPathByAlias('/' . $campaign_name);
    if (preg_match('/node\/(\d+)/', $path, $matches)) {
      $campaign_node = \Drupal\node\Entity\Node::load($matches[1]);
      if ($campaign_node->getType() == 'campaign') {
        $err_msg = $campaign_node->field_error_message_no_login->value;
        $user = user_load_by_mail($name);
        if (!$user) {
          $users = \Drupal::entityTypeManager()->getStorage('user')
            ->loadByProperties(['name' => $name]);
          $user = reset($users);
        }

        $form_state->clearErrors();
        if ($user && $campaign_node) {
          $uid = $user->id();

          if ($campaign_node->hasField('field_list_users_of_campaign') && !$campaign_node->field_list_users_of_campaign->isEmpty()) {

            $is_campaign_user = array_search($uid, array_column($campaign_node->field_list_users_of_campaign->getValue(), 'target_id'));
            if ($is_campaign_user === FALSE) {
              $error_message_text = t('<div class="ui basic red pointing prompt label transition visible"><div class="campaign">' . $err_msg . '</div></a></div>');
              $form_state->setErrorByName('name', $error_message_text);
            }
          }
        }
        else {
          $error_message_text = t('<div class="ui basic red pointing prompt label transition visible"><div class="campaign">' . $err_msg . '</div></a></div>');
          $form_state->setErrorByName('name', $error_message_text);
        }
      }
    }
  }

}
