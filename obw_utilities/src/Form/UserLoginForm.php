<?php

/**
 * Created by PhpStorm.
 * User: leopham
 * Date: 8/28/18
 * Time: 2:02 PM
 */

namespace Drupal\obw_utilities\Form;

use Drupal;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;
use Drupal\form_alter_service\FormAlterBase;
use Drupal\node\Entity\Node;
use Drupal\obw_social\User\SessionHandler;
use Exception;

/**
 *  Alter form to add css class and submit action
 *
 * Class UserLoginForm
 *
 * @package Drupal\obw_utilities\Form
 */
class UserLoginForm extends FormAlterBase {


  /**
   * @param array $form
   * @param FormStateInterface $form_state
   */
  public function alterForm(array &$form, FormStateInterface $form_state) {

    $session_handler = Drupal::service('obw_social.session_handler');
    $query = Drupal::request()->query;
    $params = Drupal::service('redirect.destination')->getAsArray();
    $is_voting = $query->get('is_voting');
    $register_to_vote = $query->get('register_to_vote');
    $campaign = $query->get('c');
    $register_to_action = $query->get('register_to_action');
    $follow_normal_url = $query->get('follow-normal-url');
    $register_to_vr_series = $query->get('register_to_vr_series');
    $register_to_vr_event = $query->get('register_to_vr_event');
    $vr_series_url = Url::fromRoute('<current>')->toString();
    $is_come_back = $query->get('come_back');
    $source = $query->get('source');
    $hide_register = $query->get('hide_register');
    $persona_id = $query->get('persona_id');
    $wl_ebook_id = $query->get('wl_ebook_id');
    $login_from_fe = FALSE;
    $uid = FALSE;
    $sid = FALSE;
    $action = FALSE;

    if (preg_match('/user\/(\d+)\/settings/', $params['destination'], $matches)) {
      $donor_account = Drupal\user\Entity\User::load($matches[1]);
      if ($donor_account) {
        $form['name']['#default_value'] = $donor_account->getEmail();
        $form['#hide_register_block'] = TRUE;
      }
    }

    if ($wl_ebook_id) {
      $form['#hide_register_block'] = TRUE;
      $session_handler->set('url-previous', $params['destination'] . '?ebook-id=' . $wl_ebook_id);
    }

    if ($hide_register) {
      $form['#hide_register_block'] = TRUE;
    }

    if (!empty($query->all()['destination']) && (preg_match('/oauth\/authorize\?client_id=(.*)\&client_secret=(.*)\&redirect_uri=(.*)&response_type=(.*)&path=(.*)&title=(.*)&src=(.*)&topic_title=(.*)&topic_content=(.*)/', $query->all()['destination'], $matches)
        || preg_match('/oauth\/authorize\?client_id=(.*)\&client_secret=(.*)\&redirect_uri=(.*)&response_type=(.*)&path=(.*)&title=(.*)&src=(.*)/', $query->all()['destination'], $matches))) {
      $login_from_fe = TRUE;
      $client_id = $matches[1];
      $client_secret = $matches[2];
      $auth_fe_url = $matches[3];
      $path = rawurlencode($matches[5]);
      $title = $matches[6];
      $src = $matches[7];
      $topic_title = !empty($matches[8]) ? explode('&', $matches[8])[0] : FALSE;
      $topic_content = !empty($matches[9]) ? explode('&', $matches[9])[0] : FALSE;

      // redirect to previous enquiry form if reset pass
      if (preg_match('/(.*)\&uid=(.*)\&sid=(.*)&action=(.*)[\&|$]/', $src, $matche_enquiry)) {
        $src = $matche_enquiry[1];
        $uid = $matche_enquiry[2];
        $sid = $matche_enquiry[3];
        $action = !empty($matche_enquiry[4]) ? explode('&', $matche_enquiry[4])[0] : FALSE;
      }

      if (strpos($auth_fe_url, '/auth/tbt/callback') !== FALSE) {
        $fe_domain = str_replace('/auth/tbt/callback', '', $auth_fe_url);
        $explode_domain = explode('//', $fe_domain);
        $schema = $explode_domain[0];
        $domain = $explode_domain[1];
      }
      elseif (strpos($auth_fe_url, '/auth/tbt') !== FALSE) {
        $fe_domain = str_replace('/auth/tbt', '', $auth_fe_url);
        $explode_domain = explode('//', $fe_domain);
        $schema = $explode_domain[0];
        $domain = $explode_domain[1];
      }

      $fe_register_url = 'schema=' . $schema . '&domain=' . $domain . '&path=' . $path . '&title=' . rawurlencode($title) . '&client_id=' . $client_id . '&client_secret=' . $client_secret . '&src=' . $src . '&come_back=true';
      if ($topic_title && $topic_content) {
        $fe_register_url .= '&topic_title=' . rawurlencode($topic_title) . '&topic_content=' . rawurlencode($topic_content);
      }
      if ($uid && $sid && $action) {
        $fe_register_url .= '&uid=' . $uid . '&sid=' . $sid . '&action=' . $action;
      }
      $form['#src'] = $src;
    }
    $current_path = Drupal::service('path.current')->getPath();
    $path_alias = Drupal::service('path_alias.manager')
      ->getAliasByPath($current_path);

    $is_campaign_content_type = $this->isCampaignContentType($path_alias);

    $_SESSION['obw_utilities']['is_voting'] = $is_voting ? $is_voting : FALSE;
    /** @var SessionHandler $session_handler */

    $is_crt_campaign = $this->isCRTCampaign($params);

    $session_handler->set('is_voting', TRUE);
    $params_to_url_destination = "%3F";

    if (strstr($params['destination'], '?')) {
      $params_to_url_destination = '%26';
    }
    if (isset($is_voting) && $is_voting == 'true') {
      $params_to_url_destination .= 'is_voting=true';
    }
    if (isset($register_to_vote) && $register_to_vote == 'true') {
      if (strlen($params_to_url_destination) > 1) {
        $params_to_url_destination .= '&register_to_vote=true';
      }
      else {
        $params_to_url_destination .= 'register_to_vote=true';
      }
    }

    if (isset($register_to_vr_series) && $register_to_vr_series == 'true') {
      $form['#register_to_vr_series'] = TRUE;
      $form['#reset_to_vr_series'] = $params['destination'];
    }

    if (isset($register_to_vr_event) && $register_to_vr_event == 'true') {
      $date_event = $query->get('date');
      $time_event = $query->get('time');
      $params_to_url_destination .= 'register_to_vr_event%3Dtrue%26date%3D' . $date_event . '%26time%3D' . $time_event;
      $session_handler->set('reset_to_vr_event', $params['destination']);
    }
    else {
      if ($session_handler->get('reset_to_vr_event') !== NULL) {
        $session_handler->clear('reset_to_vr_event');
      }
    }

    if (isset($campaign) && $campaign == $session_handler->get('campaign_name')) {
      $params_to_url_destination .= 'followed_campaign=true';
      $form['#action_campaign'] = ['#type' => 'hidden', '#value' => TRUE];
    }

    if (isset($follow_normal_url) && $follow_normal_url == 'true') {
      $params_to_url_destination .= 'follow-normal-url=true';
      $form['#follow-normal-url'] = ['#type' => 'hidden', '#value' => TRUE];
      $session_handler->set('is_following_normal_url', TRUE);
    }

    if ($source) {
      $params_to_url_destination = '?';
      $params_to_url_destination .= 'source=' . $source;
    }

    if (!$is_campaign_content_type) {
      $session_handler->clear('campaign_id');
      $session_handler->clear('url_campaign');
      $session_handler->clear('source');
    }
    else {
      $session_handler->set('campaign_id', str_replace('/', '', $path_alias));
      $session_handler->set('source', $source);
      $params['destination'] = $path_alias;
      $session_handler->set('url_campaign', $path_alias);
    }

    if (isset($is_come_back) && $is_come_back == 'true') {
      $form['#come_back_param'] = '%26come_back=true';
      $form['#come_back_forgot_pw'] = $params['destination'];
      if ($source) {
        $form['#come_back_forgot_pw'] .= '&source=' . $source;
      }
      if ($wl_ebook_id) {
        $form['#come_back_forgot_pw'] .= '&wl_ebook_id=' . $wl_ebook_id;
      }
    }

    if ($params_to_url_destination == '%3F' || $params_to_url_destination == '%26') {
      $params_to_url_destination = '';
    }

    if ($session_handler->get('login_by_campaign_form_' . $params['destination'])) {
      $params['destination'] .= '#series';
    }

    $form['#obw_destination'] = [
      '#type' => 'hidden',
      '#value' => isset($params['destination']) ? urlencode($params['destination']) . $params_to_url_destination : NULL,
    ];
    if ($login_from_fe) {
      $form['#fe_register_url'] = $fe_register_url;
      $form['#come_back_forgot_pw'] = $schema . '//' . $domain . $path;
      if (!empty($topic_title) && !empty($topic_content)) {
        $form['#come_back_forgot_pw'] .= '&topic_title=' . rawurlencode($topic_title) . '&topic_content=' . rawurlencode($topic_content);
      }
      if ($uid && $sid && $action) {
        $form['#come_back_forgot_pw'] .= '&uid=' . $uid . '&sid=' . $sid . '&action=' . $action;
      }
    }

    if ($persona_id) {
      $session_handler->set('persona_create_submission_verified_account', $persona_id);
    }
    else {
      if ($session_handler->get('persona_create_submission_verified_account') !== NULL) {
        $session_handler->clear('persona_create_submission_verified_account');
      }
    }

    try {
      $session_handler = Drupal::service('obw_social.session_handler');
      $error = $session_handler->get('error_account_social_already');
      if (!is_null($error) || !empty($error)) {

        $form['social_register_error'] = [
          '#type' => 'markup',
          '#markup' => $error,
        ];
        $session_handler->clear('error_account_social_already');
        if (!$login_from_fe) {
          $form['#obw_destination']['#value'] = $session_handler->get('url_destination');
        }
        else {
          $form['#obw_destination']['#value'] = rawurldecode($schema . '//' . $domain . $path);
        }

        if ($session_handler->get('login_by_campaign_form_' . $form['#obw_destination']['#value'])) {
          $form['#obw_destination']['#value'] .= '#series';
        }
        $session_handler->clear('url_destination');
      }

      if (isset($params['destination']) && !empty($params['destination'])) {
        $param_des = str_replace('&come_back=true', '', $params['destination']);
        $path = Drupal::service('path_alias.manager')
          ->getPathByAlias($param_des);
        if (preg_match('/node\/(\d+)/', $path, $matches)) {
          $node = Node::load($matches[1]);
          if ($node->getType() == 'gsoty_story' && isset($is_voting) && $is_voting == 'true') {
            $form['#obw_title_login'] = 'Please log in to vote.';
          }

          if ($node->getType() == 'event' && ($is_come_back || strpos($params['destination'], '&come_back=true') !== FALSE)) {
            if (isset($node->field_event_override_title_login) && !empty($node->field_event_override_title_login->value)) {
              $form['#obw_title_login'] = $node->field_event_override_title_login->value;
            }
          }
        }
      }

      if ($is_campaign_content_type) {
        $path = Drupal::service('path_alias.manager')
          ->getPathByAlias($path_alias);
        if (preg_match('/node\/(\d+)/', $path, $matches)) {
          $campaign_node = Node::load($matches[1]);
          if (isset($campaign_node->field_override_title_on_login) && !empty($campaign_node->field_override_title_on_login->value)) {
            $form['#obw_title_login'] = $campaign_node->field_override_title_on_login->value;
          }
          if (isset($campaign_node->field_user_can_signup) && $campaign_node->field_user_can_signup->value == '1') {
            $form['#hide_register_block'] = FALSE;
          }
          else {
            $form['#hide_register_block'] = TRUE;
          }

          if (isset($campaign_node->field_campaign_split_login_2_col) && !empty($campaign_node->field_campaign_split_login_2_col->value) && $campaign_node->field_campaign_split_login_2_col->value == '1') {
            $form['#split_login_2_col'] = TRUE;
            $form['#attributes']['class'][] = 'split-2-columns';
          }

          if (isset($campaign_node->field_url_link_to_campaign_page) && !empty($campaign_node->field_url_link_to_campaign_page->value)) {
            $path = Drupal::service('path_alias.manager')
              ->getPathByAlias($campaign_node->field_url_link_to_campaign_page->value);
            if (preg_match('/node\/(\d+)/', $path, $matches)) {
              $event_node = Node::load($matches[1]);
              if ($event_node) {
                if (isset($event_node->field_event_limited_access) && !empty($event_node->field_event_limited_access->value) && $event_node->field_event_limited_access->value === "1") {
                  $is_full_slot = FALSE;
                  $query_request = $query->all();
                  if (!empty($query_request['source'])) {
                    if (isset($event_node->field_event_limit_from_other) && !empty($event_node->field_event_limit_from_other->getValue())) {
                      $etm = Drupal::entityTypeManager();
                      foreach ($event_node->field_event_limit_from_other->getValue() as $source_para_id) {
                        if (!empty($source_para_id['target_id'])) {
                          $source_para = $etm->getStorage('paragraph')
                            ->load($source_para_id['target_id']);
                          if (isset($source_para->field_para_source_param) && !empty($source_para->field_para_source_param->value)) {
                            if ($source_para->field_para_source_param->value === $query_request['source']) {
                              if (isset($source_para->field_para_max_user_event) && !empty($source_para->field_para_max_user_event->value)) {
                                $is_full_slot = (int) $source_para->field_para_max_user_event->value <= (int) $source_para->field_para_number_user_register->value;
                              }
                            }
                          }
                        }
                      }
                    }
                  }
                  else {
                    if (isset($event_node->field_event_max_user_access) && !empty($event_node->field_event_max_user_access->value)) {
                      $is_full_slot = (int) $event_node->field_event_max_user_access->value <= (int) $event_node->field_event_number_user_register->value;
                    }
                  }
                  if ($is_full_slot) {
                    if (isset($campaign_node->field_override_title_on_waitlist) && !empty($campaign_node->field_override_title_on_waitlist->value)) {
                      $form['#obw_title_login_waitlist'] = $campaign_node->field_override_title_on_waitlist->value;
                    }
                  }
                }
              }
            }
          }


        }
        if ($session_handler->get('campaign_social_error') !== NULL) {
          $form['#campaign_social_error'] = $campaign_node->field_error_msg_no_login_social_->value;
          $session_handler->clear('campaign_social_error');
        }
        $form['#register_to_campaign'] = TRUE;
      }

      if ($is_crt_campaign) {
        $form['#obw_header_login'] = 'Welcome back';
        $form['#obw_title_login'] = 'Login in order to participate';
        $form['#obw_or_text'] = 'Or login with email';
      }

      $error = $session_handler->get('error_account_already');

      if (!is_null($error) || !empty($error)) {
        $form['register_error'] = [
          '#type' => 'markup',
          '#markup' => '<div class="alert-box mb-15 text-center"><span>' . $error . '</span></div>',
        ];

        $session_handler->clear('error_account_already');
        $form['link_social_account'] = [
          '#type' => 'checkbox',
          '#title' => t('Link to my social account'),
          '#size' => 10,
          '#maxlength' => 255,
          '#default_value' => 0, // Default to unchecked
          '#required' => FALSE,
        ];
      }
    } catch (Exception $e) {
      Drupal::logger('Login')
        ->error('Need install module "session_based_temp_store"');
    }

    $form['#prefix'] = '<div id="user-action-login-wrapper">';
    $form['#suffix'] = '</div>';
    $form['name']['#title'] = t('The Email');
    $form['pass']['#title'] = t('The Password');
    $form['actions']['submit']['#value'] = t('Login');
    $form['name']['#placeholder'] = t('Email Address');
    $form['pass']['#placeholder'] = t('Password');

    /** Set default value for the email field in the Login form*/
    if ($session_handler->get('email_login') !== NULL) {
      $form['name']['#default_value'] = $session_handler->get('email_login');
    }

    if ($session_handler->get('data_register_event')) {
      if (!empty($session_handler->get('data_register_event')['email']) && $is_come_back) {
        $form['name']['#default_value'] = $session_handler->get('data_register_event')['email'];
        $form['name']['#attributes']['disabled'] = 'disabled';
      }
    }

    // TODO: check module form_placeholder
    unset($form['pass']['#description']);
    unset($form['name']['#description']);

    //Override theme
    $form['#theme'] = ['obw_user_login_form'];
    if ($session_handler->get('login_by_campaign_form_' . $vr_series_url) !== NULL) {
      $form["#theme"] = ["obw_user_login_campaign_form"];
      $form['#obw_campaign_title_login'] = $session_handler->get('login_by_campaign_form_params_' . $vr_series_url)['label'];
      $form['#obw_campaign_description_login'] = $session_handler->get('login_by_campaign_form_params_' . $vr_series_url)['des'];
    }

    $form['#validate'][] = [
      $this,
      'userLoginFormValidate',
    ];//'_user_login_validation_handler';
    if ($is_campaign_content_type) {
      $form['#validate'][] = [$this, 'userAccessToCampaignValidate'];
    }
    $form['#validate'][] = [$this, 'userSyncSocialAccount'];
    $form['actions']['submit']['#attributes'] = [
      'class' => [
        'use-ajax',
      ],
    ];

    $form['actions']['submit']['#ajax'] = [
      'callback' => [
        '\Drupal\obw_utilities\AccountMgmt',
        'submitOBWUserLoginFormAjax',
      ],
      'event' => 'click',
    ];


  }

  public static function userSyncSocialAccount($form, \Drupal\Core\Form\FormStateInterface &$form_state) {
    $input = $form_state->getUserInput();

    // Return if field is not existed or form has errors
    if ($form_state->hasAnyErrors() || empty($input['link_social_account'])) {
      return;
    }

    if (!empty($input['link_social_account']) && $input['link_social_account'] == 1) {
      // todo: save social by service
      $social_account_handler = Drupal::service('obw_social.social_account_handler');
      $social_account_handler->addSocialAccountRecord();
    }
  }

  /**
   *
   * @param $form
   * @param FormStateInterface $form_state
   */
  public static function userLoginFormValidate($form, \Drupal\Core\Form\FormStateInterface &$form_state) {
    // Temporarily store all form errors.
    $form_errors = $form_state->getErrors();
    // Clear the form errors.
    $form_state->clearErrors();

    // Now loop through and re-apply the remaining form error messages.

    foreach ($form_errors as $name => $error_message) {
      switch ($name) {
        case 'name':
          if (empty($form_state->getValue('name')) || empty($form_state->getValue('pass'))) {
            if (empty($form_state->getValue('name'))) {
              $error_message_text = t('<div class="ui basic red pointing prompt label transition visible">Please input your username or email address.</div>');
              $form_state->setErrorByName('name', $error_message_text);
            }

            if (empty($form_state->getValue('pass'))) {
              $error_message_text = t('<div class="ui basic red pointing prompt label transition visible">Please input your password.</div>');
              $form_state->setErrorByName('pass', $error_message_text);
            }
          }
          elseif (!$form_state->isValueEmpty('name') && user_is_blocked($form_state->getValue('name'))) {
            $error_message_text = t('<div class="ui basic red pointing prompt label transition visible">You are not able to login due to an issue with your account. Please <a href="/contact-us">contact us</a>  so we can help you resolve this issue.</div>');
            // Blocked in user administration.
            $form_state->setErrorByName('name', $error_message_text);
          }
          else {
            if (user_load_by_name($form_state->getValue('name'))) {
              $error_message_text = t('<div class="ui basic red pointing prompt label transition visible">Incorrect password.</div>');
              $form_state->setErrorByName('pass', $error_message_text);
            }
            else {
              $session_handler = Drupal::service('obw_social.session_handler');
              if ($session_handler->get('campaign_id') !== NULL) {
                $path = Drupal::service('path_alias.manager')
                  ->getPathByAlias('/' . $session_handler->get('campaign_id'));
                if (preg_match('/node\/(\d+)/', $path, $matches)) {
                  $campaign_node = Node::load($matches[1]);
                  if ($campaign_node->getType() == 'campaign') {
                    if (isset($campaign_node->field_error_message_no_login) && !empty($campaign_node->field_error_message_no_login->value)) {
                      $err_msg = $campaign_node->field_error_message_no_login->value;
                      $error_message_text = t('<div class="ui basic red pointing prompt label transition visible"><div class="campaign">' . $err_msg . '</div></div>');
                    }
                    else {
                      $error_message_text = t('<div class="ui basic red pointing prompt label transition visible">Incorrect username or email address.</div>');
                    }
                  }
                }
              }
              else {
                $error_message_text = t('<div class="ui basic red pointing prompt label transition visible">Incorrect username or email address.</div>');
              }
              $form_state->setErrorByName('name', $error_message_text);
            }
          }
          break;
        case 'pass':
          if (empty($form_state->getValue('pass'))) {
            $error_message_text = t('<div class="ui basic red pointing prompt label transition visible">Please input your password.</div>');
            $form_state->setErrorByName('pass', $error_message_text);
          }
          else {
            $error_message_text = t('<div class="ui basic red pointing prompt label transition visible">Incorrect password.</div>');
            $form_state->setErrorByName('pass', $error_message_text);
          }
          break;
        default:
          $error_message_text = $error_message ? $error_message->render() : '';
          $form_state->setErrorByName($name,  $error_message_text);
          break;
      }
    }
  }

  public static function userAccessToCampaignValidate($form, \Drupal\Core\Form\FormStateInterface &$form_state) {
    $name = $form_state->getValue('name');

    $current_path = Drupal::service('path.current')->getPath();
    $url_campaign = Drupal::service('path_alias.manager')
      ->getAliasByPath($current_path);
    $path = Drupal::service('path_alias.manager')
      ->getPathByAlias($url_campaign);
    if (preg_match('/node\/(\d+)/', $path, $matches)) {
      $campaign_node = Node::load($matches[1]);
      if ($campaign_node->getType() == 'campaign') {
        $err_msg = $campaign_node->field_error_message_no_login->value;
        $user = user_load_by_mail($name);
        if (!$user) {
          $users = Drupal::entityTypeManager()->getStorage('user')
            ->loadByProperties(['name' => $name]);
          $user = reset($users);
        }
        if ($user && $campaign_node) {
          $uid = $user->id();
          if ($campaign_node->hasField('field_list_users_of_campaign') && !$campaign_node->field_list_users_of_campaign->isEmpty()) {
            $is_campaign_user = array_search($uid, array_column($campaign_node->field_list_users_of_campaign->getValue(), 'target_id'));
            if ($is_campaign_user === FALSE) {
              $error_message_text = t('<div class="ui basic red pointing prompt label transition visible"><div class="campaign">' . $err_msg . '</div></div>');
              $form_state->setErrorByName('name', $error_message_text);
            }
          }
        }
        else {
          $error_message_text = t('<div class="ui basic red pointing prompt label transition visible"><div class="campaign">' . $err_msg . '</div></div>');
          $form_state->setErrorByName('name', $error_message_text);
          $error_message_text = t('<div class="ui basic red pointing prompt label transition visible">Incorrect password.</div>');
          $form_state->setErrorByName('pass', $error_message_text);
        }
      }
    }
  }

  public function isCampaignContentType($path_alias) {
    if (strstr($path_alias, '?source=')) {
      $path_alias = str_replace(strstr($path_alias, '?source='), '', $path_alias);;
    }

    $path = Drupal::service('path_alias.manager')->getPathByAlias($path_alias);
    if (preg_match('/node\/(\d+)/', $path, $matches)) {
      $node = Node::load($matches[1]);
      if ($node->getType() == 'campaign') {
        return $node->field_url_link_to_campaign_page->value;
      }
    }
    return FALSE;
  }

  public function isCRTCampaign($param) {
    if (!empty($param['destination'])) {
      $path = Drupal::service('path_alias.manager')
        ->getPathByAlias($param['destination']);
      if (preg_match('/node\/(\d+)/', $path, $matches)) {
        $node = Node::load($matches[1]);
        if ($node->getType() == 'story') {
          if (!empty($node->field_story_format->getValue())) {
            $key = array_search('5', array_column($node->field_story_format->getValue(), 'value'));
            if ($key === FALSE) {
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
      }
    }
    return FALSE;
  }

}
