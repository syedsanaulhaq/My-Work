<?php

/**
 * Created by PhpStorm.
 * User: leopham
 * Date: 8/28/18
 * Time: 2:02 PM
 */

namespace Drupal\obw_utilities\Form;

use Drupal;
use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Ajax\DataCommand;
use Drupal\Core\Ajax\InvokeCommand;
use Drupal\Core\Ajax\ReplaceCommand;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Render\Element\PasswordConfirm;
use Drupal\form_alter_service\FormAlterBase;
use Drupal\node\Entity\Node;
use Drupal\obw_contributor_profile\Controller\ActiveCampaignController;
use Drupal\user\Entity\User;
use Drupal\webform\Entity\WebformSubmission;

/**
 *  Alter form to add css class and submit action
 *
 * Class UserRegisterForm
 *
 * @package Drupal\obw_utilities\Form
 */
class UserRegisterForm extends FormAlterBase {

  /**
   * @param array $form
   * @param FormStateInterface $form_state
   */
  public function alterForm(array &$form, FormStateInterface $form_state) {

    $query = Drupal::request()->query;
    $redirect_to_external = FALSE;
    $params = Drupal::service('redirect.destination')->getAsArray();
    $register_to_vote = $query->get('register_to_vote');
    $register_to_action = $query->get('register_to_action');
    $register_follow_normal = $query->get('follow-normal-url');
    $register_to_vr_series = $query->get('register_to_vr_series');
    $register_to_vr_event = $query->get('register_to_vr_event');
    $register_to_campaign = $query->get('register_to_campaign');
    $is_come_back = $query->get('come_back');
    $source = $query->get('source');

    if (preg_match('/node\/(\d+)\/webform\/confirmation/', $params['destination'], $matches)) {
      //      NodeID=148 -> Node webform Support Us
      if ($matches[1] == '148') {
        $wf_token = explode('?token=', Drupal::request()->headers->get('referer'))[1];
        if ($wf_token) {
          /** @var \Drupal\webform\WebformSubmissionStorageInterface $webform_submission_storage */
          $webform_submission_storage = \Drupal::entityTypeManager()
            ->getStorage('webform_submission');
          if ($entities = $webform_submission_storage->loadByProperties(['token' => $wf_token])) {
            $webform_submission = reset($entities);
            $wf_data = $webform_submission->getData();
            $donor_email = $wf_data['email'];
          }
        }
      }
    }
    $is_campaign_content_type = $this->isCampaignContentType($params['destination']);

    if (!$is_come_back) {
      if (strstr($params['destination'], '&come_back=true')) {
        $is_come_back = 'true';
      }
    }

    $session_handler = Drupal::service('obw_social.session_handler');
    $is_crt_campaign = $this->isCRTCampaign($params);

    if (isset($register_to_vote) && $register_to_vote == 'true') {
      $params_to_register_link = '&register_to_vote=true&is_voting=true';
      $session_handler->set('is_voting', TRUE);
    }

    if (isset($register_to_vr_series) && $register_to_vr_series == 'true') {
      $session_handler->set('register_to_vr_series', TRUE);
      $form['#register_to_vr_series'] = TRUE;
    }
    else {
      if ($session_handler->get('register_to_vr_series') !== NULL) {
        $session_handler->clear('register_to_vr_series');
      }
    }

    if ($is_campaign_content_type) {
      if (isset($is_campaign_content_type->field_signup_join_our_community) && !empty($is_campaign_content_type->field_signup_join_our_community->value)) {
        $form['#override_right_column_signup'] = $is_campaign_content_type->field_signup_join_our_community->value;
      }

      if (isset($is_campaign_content_type->field_success_msg_register) && !empty($is_campaign_content_type->field_success_msg_register->value)) {
        $form['#success_msg_signup_override'] = $is_campaign_content_type->field_success_msg_register->value;
      }

      if (isset($is_campaign_content_type->field_campaign_signup_email) && !empty($is_campaign_content_type->field_campaign_signup_email->value)) {
        $session_handler->set('email_signup_event_override', [
          'email_signup_template' => $is_campaign_content_type->field_campaign_signup_email->value,
          'url_campaign' => $is_campaign_content_type->toUrl()->toString(),
        ]);
      }
    }

    if (isset($register_to_campaign) && $register_to_campaign == 'true') {
      $session_handler->set('register_to_campaign', TRUE);
      if ($source) {
        $session_handler->set('source', $source);
      }
      $form['#register_to_campaign'] = TRUE;
    }
    else {
      if ($session_handler->get('register_to_campaign') !== NULL) {
        $session_handler->clear('register_to_campaign');
        $session_handler->clear('source');
      }
    }

    if (isset($register_to_vr_event) && $register_to_vr_event == 'true') {
      $date_event = $query->get('date');
      $time_event = $query->get('time');
      $params_to_register_link = '?register_to_vr_event=true%26date%3D' . $date_event . '%26time%3D' . $time_event;
      $session_handler->set('register_to_vr_event', TRUE);
    }
    else {
      if ($session_handler->get('register_to_vr_event') !== NULL) {
        $session_handler->clear('register_to_vr_event');
      }
    }

    if ($session_handler->get('campaign_id') !== NULL) {
      $session_handler->clear('campaign_id');
    }

    if (isset($register_to_action) && $register_to_action == 'true') {
      //      $params_to_register_link = '&campaign=action-campaign&action=follow&register_to_action=true';
      $params_to_register_link = '?followed_campaign=true';
    }

    if (isset($register_follow_normal) && $register_follow_normal == 'true') {
      $params_to_register_link = '?follow-normal-url=true';
      $session_handler->set('is_following_normal_url', TRUE);
    }

    if ($source) {
      $params_to_register_link = '?source=' . $source;
    }

    if ($is_crt_campaign) {
      $form['#obw-campaign'] = 'crt-campaign';
    }


    $form['#admin_mode'] = Drupal::service('router.admin_context')
      ->isAdminRoute();

    $form['#prefix'] = '<div id="user-action-register-wrapper">';
    $form['#suffix'] = '</div>';
    $form['actions']['submit']['#value'] = t('Sign up');
    $form['account']['name']['#title'] = t('Email');
    $form['account']['mail']['#title'] = t('The Email');
    if (isset($donor_email)) {
      $form['account']['mail']['#default_value'] = $donor_email;
      $form['account']['mail']['#attributes']['readonly'] = 'readonly';
      $form['account']['mail']['#field_suffix'] = '<div class="tooltip-container">
                      <i class="fas fa-question-circle"></i>
                      <div class="tooltip-message">
                        <p>You made a donation using this email, yay! We\'ll reflect this in your account settings.</p>
                        <div class="triangle"></div>
                      </div>
                    </div> ';
      $session_handler->set('donor_register', TRUE);
      $form['#donor_register'] = [
        '#type' => 'hidden',
        '#value' => TRUE,
      ];
    }
    else {
      if ($session_handler->get('donor_register') !== NULL) {
        $session_handler->clear('donor_register');
      }
    }
    $form['field_account_last_name']['widget'][0]['value']['#title'] = t('The Last Name');
    $form['field_account_last_name']['widget'][0]['value']['#maxlength'] = 20;
    $form['field_account_first_name']['widget'][0]['value']['#title'] = t('The First Name');
    $form['field_account_first_name']['widget'][0]['value']['#maxlength'] = 20;
    $form['account']['name']['#required'] = FALSE;

    unset($form['account']['mail']['#description']);
    unset($form['account']['name']['#description']);
    unset($form['account']['pass']['#description']);

    $params = Drupal::service('redirect.destination')->getAsArray();
    $form['#obw_destination'] = [
      '#type' => 'hidden',
      '#value' => isset($params['destination']) ? urlencode($params['destination']) . (isset($params_to_register_link) ? $params_to_register_link : NULL) : NULL,
    ];


    if (isset($is_come_back) && $is_come_back == 'true') {
      //      $form['#come_back_param'] = '%26come_back=true';
      if (strpos($form['#obw_destination']['#value'], 'come_back%3Dtrue') === FALSE
        && strpos($form['#obw_destination']['#value'], 'come_back=true') === FALSE) {
        $form['#obw_destination']['#value'] = $form['#obw_destination']['#value'] . '&come_back=true';
      }

      if ($query->get('schema') && $query->get('domain') && $query->get('path') && $query->get('client_id') && $query->get('client_secret') && $query->get('title') && $query->get('src')) {
        $schema = $query->get('schema');
        $domain = $query->get('domain');
        $path = rawurlencode($query->get('path'));
        $client_id = $query->get('client_id');
        $client_secret = $query->get('client_secret');
        $title = $query->get('title');
        $src = $query->get('src');
        $topic_title = !empty($query->get('topic_title')) ? $query->get('topic_title') : FALSE;
        $topic_content = !empty($query->get('topic_content')) ? $query->get('topic_content') : FALSE;

        $uid = !empty($query->get('uid')) ? $query->get('uid') : FALSE;
        $sid = !empty($query->get('sid')) ? $query->get('sid') : FALSE;
        $action = !empty($query->get('action')) ? $query->get('action') : FALSE;

        $redirect_to_external = TRUE;
        $form['#src'] = $src;
        //        $session_handler->set('come_back', str_replace('&come_back=true', '', $params['destination']));
        $url_external = $schema . '//' . $domain . $path;
        if ($form['#src'] == 'tbt') {
          $form['#obw_destination']['#value'] = '/oauth/authorize%3Fclient_id%3D' . $client_id
            . '%26client_secret%3D' . $client_secret . '%26redirect_uri%3D' . $schema . '//' . $domain . '/auth/tbt%26response_type%3Dcode%26scope%3D%26';
        }
        elseif ($form['#src'] == 'forum') {
          $form['#obw_destination']['#value'] = '/oauth/authorize%3Fclient_id%3D' . $client_id
            . '%26client_secret%3D' . $client_secret . '%26redirect_uri%3D' . $schema . '//' . $domain . '/auth/tbt/callback%26response_type%3Dcode%26scope%3D%26';
        }

        $form['#obw_destination']['#value'] = $form['#obw_destination']['#value'] . 'path=' . $path . '%26title=' . rawurlencode($title) . '%26src=' . $src;

        $redirect_to_fe = $url_external;
        if ($topic_title && $topic_content) {
          $form['#obw_destination']['#value'] .= '%26topic_title=' . rawurlencode($topic_title) . '%26topic_content=' . rawurlencode($topic_content);
          $redirect_to_fe .= '&topic_title=' . rawurlencode($topic_title) . '&topic_content=' . rawurlencode($topic_content);
        }

        if ($uid && $sid && $action) {
          $form['#obw_destination']['#value'] .= '%26uid=' . $uid . '%26sid=' . $sid . '%26action=' . $action . '%26';
          $redirect_to_fe .= '&uid=' . $uid . '&sid=' . $sid . '&action=' . $action;
        }

        $session_handler->set('redirect_to_fe', $redirect_to_fe);
      }
      else {
        $session_handler->set('come_back', str_replace('&come_back=true', '', $params['destination']));
        if ($source) {
          $session_handler->set('come_back', $session_handler->get('come_back') . '&source=' . $source);
        }
        else {
          if (strpos($session_handler->get('come_back'), '?source') !== FALSE) {
            $session_handler->set('come_back', str_replace('?', '&', $session_handler->get('come_back')));
          }
        }
      }
    }
    else {
      if ($session_handler->get('come_back') !== NULL) {
        $session_handler->clear('come_back');
      }

      if ($session_handler->get('redirect_to_fe') !== NULL) {
        $session_handler->clear('redirect_to_fe');
      }
    }

    if ($redirect_to_external) {
      $title = $query->get('src') ? strtoupper($query->get('src')) . ' | ' : '';
      $title .= $query->get('title') ? $query->get('title') : '';
      $form['field_account_signup_source']['widget'][0]['value']['#value'] = $title;
      $form['field_account_signup_url']['widget'][0]['value']['#value'] = $url_external;
    }
    else {
      $tempstore = \Drupal::service('tempstore.private')
        ->get('tracking_signup_source');
      $signup_source = $tempstore->get('signup_source');
      $wrong_signup_source = $tempstore->get('wrong_signup_source');
      if ($wrong_signup_source !== NULL && $signup_source !== NULL) {
        $title_node = $signup_source['title'];
        $prev_url = $signup_source['url'];
        //        $tempstore->delete('signup_source');
      }
      else {
        $path = Drupal::service('path_alias.manager')
          ->getPathByAlias($params['destination']);
        $title_node = '';
        if (preg_match('/node\/(\d+)/', $path, $matches)) {
          $node = Node::load($matches[1]);
          $title_node = $node->get('title')->getValue()[0]['value'];

          if ($node->getType() == 'gsoty_story' && $register_to_vote == 'true') {
            $form['#obw_gsoty_register'] = 'Create an account to vote';
            $form['field_gsoty_story_id']['widget'][0]['value']['#value'] = isset($params['destination']) ? $params['destination'] : NULL;
          }

        }
        else {
          $obw_utilities_service = \Drupal::service('obw_utilities.service');
          if ($node = $obw_utilities_service->isContentTypePage('wl_download_storybook')) {
            $title_node = $node->get('title')->getValue()[0]['value'];
          }
          else {
            $url = Drupal::request()->getSchemeAndHttpHost() . $path;
            $title_node = $this->getTitlePreviousPage($url);
          }

        }
      }

      $form['field_account_signup_source']['widget'][0]['value']['#value'] = $title_node;
      $des_param = NULL;
      if (isset($params['destination'])) {
        if ($params['destination'] == '/node') {
          $des_param = '/homepage';
        }
        else {
          $des_param = $params['destination'];
        }
      }
      $form['field_account_signup_url']['widget'][0]['value']['#value'] = isset($prev_url) ? $prev_url : (isset($params['destination']) ? $des_param : NULL);
    }

    $form['account']['mail']['#attributes']['placeholder'] = t('Email');

    // TODO: check module form_placeholder
    $form['field_account_last_name']['widget'][0]['value']['#attributes']['placeholder'] = $form['field_account_last_name']['widget'][0]['#title'];
    $form['field_account_first_name']['widget'][0]['value']['#attributes']['placeholder'] = $form['field_account_first_name']['widget'][0]['#title'];
    $form['field_account_nationality']['widget'][0]['value']['#attributes']['placeholder'] = $form['field_account_nationality']['widget'][0]['#title'];
    $form['field_account_city_of_residence']['widget'][0]['value']['#attributes']['placeholder'] = $form['field_account_city_of_residence']['widget'][0]['#title'];
    $form['field_account_accept_tnc']['widget']['value']['#title'] = "Accept <a target='_blank' href='/terms'>Terms and Conditions</a> and <a target=\"_blank\" href=\"/privacy\">Privacy Policy</a>";

    if (isset($form['account']['pass'])) {
      $form['account']['pass']['#process'][] = [
        get_class(),
        '_user_register_form_process_pass',
      ];
    }

    $form['#theme'] = ['obw_user_register_form'];

    //    Alter register form for the WL Download E-Book
    $this->alterRegisterFormForWlDownloadEbook($form, $form_state);

    array_unshift($form['#validate'], [$this, 'userRegisterFormValidate']);

    $form['actions']['submit']['#attributes'] = [
      'class' => [
        'use-ajax',
      ],
    ];
    $form['actions']['submit']['#ajax'] = [
      'callback' => [get_class(), 'submitOBWUserRegisterFormAjax'],
      'event' => 'click',
    ];


  }


  /**
   * validate form register
   *
   * @param $form
   * @param FormStateInterface $form_state
   */
  public static function userRegisterFormValidate($form, \Drupal\Core\Form\FormStateInterface &$form_state) {
    $register_mail = $form_state->getValue('mail');
    $query = Drupal::request()->query;
    /**Change mail content if the user is registering to follow*/
    $register_to_action = $query->get('register_to_action');
    if ($register_to_action == NULL) {
      $register_to_action = $query->get('followed_campaign');
    }

    $register_to_action_normal_url = $query->get('follow-normal-url');
    $session_handler = Drupal::service('obw_social.session_handler');
    if (isset($register_to_action) && $register_to_action == 'true') {
      $session_handler->set('action_campaign', TRUE);
    }
    else {
      $destination = $query->get('destination');
      if (strpos($destination, 'followed_campaign=true') !== FALSE) {
        $session_handler->set('action_campaign', TRUE);
      }
    }

    if (isset($register_to_action_normal_url) && $register_to_action_normal_url == 'true') {
      $session_handler->set('action_campaign_normal_url', TRUE);
    }
    else {
      $destination = $query->get('destination');
      if (strpos($destination, 'follow-normal-url=true') !== FALSE) {
        $session_handler->set('action_campaign_normal_url', TRUE);
      }
    }

    if (filter_var($register_mail, FILTER_VALIDATE_EMAIL)) {
      $user = user_load_by_mail($register_mail);
      if ($user) {
        $form_state->setValue('name', $user->getAccountName());
        $form_state->setErrorByName('mail', t('An account has already been registered with the email address @mail If you believe this to be an error, please contact us at storyteller@ourbetterworld.org.', ['@mail' => $register_mail]));
      }
      else {
        $name = strstr($register_mail, '@', TRUE);
        //TODO: check rule to create user login name, currently get the first part of email address, with random num 100-999
        $new_name = $name;
        $user = user_load_by_name($new_name);
        while ($user) {
          $rand_num = mt_rand(100, 999);
          $new_name = $name . '_' . $rand_num;
          $user = user_load_by_name($new_name);
        }
        $form_state->setValue('name', $new_name);

        //Regex - match a string without leading and trailing spaces
        $first_name = $form_state->getValue('field_account_first_name');
        $last_name = $form_state->getValue('field_account_last_name');
        $regex = '/^(?! )[A-Za-z0-9 ]*(?<! )$/';

        if (!empty($first_name[0]) && !empty($first_name[0]['value'])) {
          if (!preg_match($regex, $first_name[0]['value'])) {
            $first_name[0]['value'] = trim($first_name[0]['value']);
            $form_state->setValue('field_account_first_name', $first_name);
          }
        }

        if (!empty($last_name[0]) && !empty($last_name[0]['value'])) {
          if (!preg_match($regex, $last_name[0]['value'])) {
            $last_name[0]['value'] = trim($last_name[0]['value']);
            $form_state->setValue('field_account_last_name', $last_name);
          }
        }

        if ($form_state->getValue('field_account_news_subscribed')['value']) {
          $form_state->setValue('field_account_subcribe_options', 'weekly');
        }
        if (!empty($form_state->getValue('obw_subscribe_radios'))
          && $form_state->getValue('obw_subscribe_radios') !== 'no') {
          $form_state->setValue('field_account_news_subscribed', [TRUE]);
          $form_state->setValue('field_account_subcribe_options', $form_state->getValue('obw_subscribe_radios'));
        }
      }
    }
    else {
      $form_state->setErrorByName('mail', t('@mail is error format.', ['@mail' => $register_mail]));
    }

    if ($form_state->hasAnyErrors()) {
      $errors = $form_state->getErrors();
      $form_state->clearErrors();

      foreach ($errors as $key => $value) {
        if ($key == 'field_account_accept_tnc][value') {
          $form_state->setErrorByName($key, t('<div class="ui basic red pointing prompt label transition visible">Please agree to the Terms and Conditions.</div>'));
        }
        else {
          $form_state->setErrorByName($key, t('<div class="ui basic red pointing prompt label transition visible">' . $value . '</div>'));
        }
      }
    }
  }

  public static function _user_register_form_process_pass(&$element, FormStateInterface $form_state, &$complete_form) {

    $element = PasswordConfirm::processPasswordConfirm($element, $form_state, $complete_form);
    $element['pass1']['#placeholder'] = t('Password');
    $element['pass2']['#placeholder'] = t('Confirm password');
    $element['#element_validate'][] = [
      '\Drupal\obw_contributor_profile\Form\ChangePasswordForm',
      'validatePasswordStrength',
    ];


    return $element;
  }


  /**
   * AJAX callback handler that displays any errors or a success message.
   */
  public static function submitOBWUserResetPassFormAjax(array $form, FormStateInterface $form_state) {
    $response = new AjaxResponse();

    // If there are any form errors, re-display the form.
    if ($form_state->hasAnyErrors()) {
      $response->addCommand(new ReplaceCommand('#user-action-pass-wrapper', $form));
    }
    else {
      $messenger = Drupal::messenger();
      $messenger->deleteByType('error');
      $messenger->deleteByType('warning');
      $messenger->deleteByType('status');
      $div = '<div id="content">
    <div id="user-action" class="pt-30 pb-20">
    <div class="container">
        <div class="title pb-15"><h3>' . t('Password Reset') . '</h3> </div>' .
        '<div class="message success text-center"><p>' . t('Your temporary password has been sent.') . '</p> </div>' .
        '</div></div></div>';
      $response->addCommand(new ReplaceCommand('#user-action-pass-wrapper', $div));
    }
    return $response;
  }


  /**
   * AJAX callback handler that displays any errors or a success message.
   *
   * @throws \Drupal\Core\Entity\EntityStorageException
   */
  public static function submitOBWUserRegisterFormAjax(array $form, FormStateInterface $form_state) {
    $response = new AjaxResponse();
    $params = Drupal::service('redirect.destination')->getAsArray();
    $register_to_vote = Drupal::request()->query->get('register_to_vote');
    $session_handler = Drupal::service('obw_social.session_handler');
    $session_handler->clear('submission_id');
    $account = Drupal::currentUser();


    // If there are any form errors, re-display the form.
    if ($form_state->hasAnyErrors()) {

      $response->addCommand(new ReplaceCommand('#user-action-register-wrapper', $form));
      $form_errors = $form_state->getErrors();
      foreach ($form_errors as $name => $error_message) {
        switch ($name) {
          case 'captcha_response':
            $response->addCommand(new InvokeCommand('.recaptcha-processed', 'addClass', ['error']));
            break;
        }
      }
    }
    else {
      $messenger = Drupal::messenger();
      $messenger->deleteByType('error');
      $messenger->deleteByType('warning');
      $messenger->deleteByType('status');

      if (!empty($form['#donor_register'])) {
        $ac_controller = new ActiveCampaignController();
        $ac_sync_tags = $ac_controller->addTagToACContact($form_state->getValues()['mail'], ['[HOLD] Account created - Donor']);
        if ($ac_sync_tags['result_code'] == 1) {
          \Drupal::logger('sync_ac_support_us')
            ->info('Synced [HOLD] Account created - Donor tag to AC');
        }
        else {
          \Drupal::logger('sync_ac_support_us')
            ->info('Sync [HOLD] Account created - Donor tag to AC fail');
        }
      }

      $path = Drupal::service('path_alias.manager')
        ->getPathByAlias($params['destination']);
      if (!empty($form['#success_msg_signup_override'])) {
        $div = $form['#success_msg_signup_override'];
      }
      else {
        $div = '<div id="content"> <div id="user-action" class="pt-30 pb-20 message-popup">'
          . '<div class="container"> <div class="title pb-15"><h3>' . t('Account created!') . '</h3> </div>'
          . '<div class="message success text-center"><p>' . t('Your account has been created.')
          . '<br/>' . t('Please check your email to set your password.') . '</p> </div>' .
          '</div></div></div>';
      }

      if (preg_match('/node\/(\d+)/', $path, $matches)) {
        $node = Node::load($matches[1]);

        if ($node && $node->getType() == 'gsoty_story' && isset($register_to_vote) && $register_to_vote == 'true') {
          $div = '<div id="content"> <div id="user-action" class="pt-30 pb-20 message-popup">'
            . '<div class="container"> <div class="title pb-15"><h3>' . t('Check your email!') . '</h3> </div>'
            . '<div class="message success text-center"><p>' . t('Click on the link in the email sent to you to confirm your vote.')
            . '</p></div>' .
            '</div></div></div>';
        }
      }

      $success_message = [
        '#markup' => $div,
      ];

      self::createWebform($form_state, 'who_you_can_help', '/choose-your-action');
      $response->addCommand(new ReplaceCommand('#user-action-register-wrapper', $success_message));
      $response->addCommand(new InvokeCommand('html', 'trigger', [
        'set_width',
        [],
      ]));
      $response->addCommand(new DataCommand(".user-selector", "user_id", $account->id()));

    }

    return $response;
  }

  public function getTitlePreviousPage($url) {
    // create curl resource
    if (strpos($url, '/user/register') !== FALSE) {
      $title_node = 'Create new account';
    }
    else {
      $ch = curl_init();
      // set url
      curl_setopt($ch, CURLOPT_URL, $url);
      //return the transfer as a string
      curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: text/xml']);
      curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
      curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
      curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
      // $output contains the output string
      $output = curl_exec($ch);
      $pattern = '/[<]title[>]([^<]*)[<][\/]title/i';
      preg_match($pattern, $output, $matches);
      curl_close($ch);
      if (strpos($matches[1], 'Redirecting to http') !== FALSE) {
        $url = str_replace('Redirecting to http', '', $matches[1]);
        $matches[1] = $this->getTitlePreviousPage("http" . $url);
      }

      $title_node = trim(str_replace("| Our Better World", "", $matches[1]));
    }

    return $title_node;
  }

  public function isCRTCampaign($param) {
    if (!empty($param['destination'])) {
      $path = Drupal::service('path_alias.manager')
        ->getPathByAlias($param['destination']);
      if (preg_match('/node\/(\d+)/', $path, $matches)) {
        $node = Node::load($matches[1]);
        if ($node->getType() == 'story') {
          if (!empty($node->field_story_format->getValue())) {
            $node_multimedia = array_search('5', array_column($node->field_story_format->getValue(), 'value'));
            $node_substory = array_search('6', array_column($node->field_story_format->getValue(), 'value'));
            if ($node_multimedia === FALSE && $node_substory === FALSE) {
              return FALSE;
            }
            else {
              return TRUE;
            }
          }
        }

        if ($node->getType() == 'organisations') {
          return TRUE;
        }

        if ($node->getType() == 'page' && isset($node->field_basic_header)
          && !empty($node->field_basic_header->value) && $node->field_basic_header->value == 'crt-header') {
          return TRUE;
        }
      }
    }
    return FALSE;
  }

  public function isCampaignContentType($path_alias) {
    if (strstr($path_alias, '?source=')) {
      $path_alias = str_replace(strstr($path_alias, '?source='), '', $path_alias);;
    }
    $path = Drupal::service('path_alias.manager')->getPathByAlias($path_alias);
    if (preg_match('/node\/(\d+)/', $path, $matches)) {
      $node = Node::load($matches[1]);
      if ($node->getType() == 'campaign') {
        return $node;
      }
    }
    return FALSE;
  }

  private function alterRegisterFormForWlDownloadEbook(&$form, $form_state) {
    $obw_utilities_service = \Drupal::service('obw_utilities.service');
    if ($obw_utilities_service->isContentTypePage('wl_download_storybook')) {
      $form['#theme'] = ['wl_download_e_booklet_register_form'];
      $form['actions']['submit']['#value'] = t('Sign Up and Download');
      $form['field_wl_download_id'] = [
        '#type' => 'hidden',
        '#value' => !empty($form_state->getUserInput()['field_wl_download_id']) ? $form_state->getUserInput()['field_wl_download_id'] : '',
        '#attributes' => [
          'id' => 'field_wl_download_id',
        ],
      ];
      $form['obw_subscribe_radios'] = [
        '#type' => 'radios',
        '#title' => t('Would you like to receive more stories from Our Better World?'),
        '#options' => [
          'weekly' => t('Weekly'),
          'monthly' => t('Monthly'),
          'no' => t('No / I’m already a subscriber'),
        ],
        '#default_value' => 'weekly',
      ];

    }
  }

  /**
   * @throws \Drupal\Core\Entity\EntityStorageException
   */
  protected static function createWebform($form_state, $webform_id, $uri) {
    $session_handler = \Drupal::service('obw_social.session_handler');
    if ($session_handler->get('countries') &&
      $session_handler->get('causes') &&
      $session_handler->get('your_actions') &&
      $session_handler->get('nid')) {

      $form_value = $form_state->getValues();
      $ip_address = \Drupal::request()->getClientIp();
      $wf_data = [
        'webform_id' => $webform_id,
        'remote_addr' => $ip_address,
        'uri' => $uri,
        'uid' => $form_value['uid'],
        'completed' => time(),
        'entity_type' => 'node',
        'entity_id' => $_GET['nid'],
        'data' => [
          'your_actions' => json_decode($_GET['your_actions']),
          'causes' => json_decode($_GET['causes']),
          'countries' => json_decode($_GET['countries']),
        ],
      ];
      WebformSubmission::create($wf_data)->save();

      $user = User::load($form_value['uid']);
      $user->set('field_account_actions', json_decode($_GET['your_actions']));
      $user->set('field_account_cause', json_decode($_GET['causes']));
      $user->set('field_account_countries', json_decode($_GET['countries']));
      $user->set('field_redirect_choose_actions', 1);
      $user->save();
    }
  }

}
