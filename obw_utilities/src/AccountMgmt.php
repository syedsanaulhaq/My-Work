<?php

namespace Drupal\obw_utilities;

use Drupal;
use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Ajax\DataCommand;
use Drupal\Core\Ajax\RedirectCommand;
use Drupal\Core\Ajax\ReplaceCommand;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Messenger\MessengerInterface;
use Drupal\Core\Url;
use Drupal\user\Entity\User;
use Drupal\webform\Entity\WebformSubmission;

/**
 * Class AccountMgmt.
 */
class AccountMgmt {

  /**
   * Drupal\Core\Messenger\MessengerInterface definition.
   *
   * @var \Drupal\Core\Messenger\MessengerInterface
   */
  protected $messenger;

  /**
   * Constructs a new AccountMgmt object.
   */
  public function __construct(MessengerInterface $messenger) {
    $this->messenger = $messenger;
  }

  /**
   * AJAX callback handler that displays any errors or a success message.
   */
  public static function submitOBWUserLoginFormAjax(array $form, FormStateInterface $form_state) {
    $response = new AjaxResponse();
    $session_handler = \Drupal::service('obw_social.session_handler');
    $account = \Drupal::currentUser();

    // TODO: clone submission here for email login

    // If there are any form errors, re-display the form.
    if ($form_state->hasAnyErrors()) {
      $response->addCommand(new ReplaceCommand('#user-action-login-wrapper', $form));
    }
    else {
      self::createWebform($account, 'who_you_can_help', '/choose-your-action');

      $messenger = \Drupal::messenger();
      $messenger->deleteByType('error');
      $messenger->deleteByType('warning');
      $messenger->deleteByType('status');
      $request = \Drupal::request()->get('destination');

      $is_voting = \Drupal::request()->query->get('is_voting');
      $register_to_vote = \Drupal::request()->query->get('register_to_vote');
      $campaign = \Drupal::request()->query->get('c');
      $follow_normal_url = \Drupal::request()->query->get('follow-normal-url');
      $register_to_vr_event = \Drupal::request()->query->get('register_to_vr_event');
      $source = \Drupal::request()->query->get('source');

      if ($session_handler->get('url_campaign') !== NULL) {
        $url_campaign = $session_handler->get('url_campaign');
      }

      $register_to_vote_link = "?";
      if (strstr($request, '?')) {
        $register_to_vote_link = '&';
      }
      if (isset($is_voting) && $is_voting == 'true') {
        $register_to_vote_link .= 'is_voting=true';
      }
      if (isset($register_to_vote) && $register_to_vote == 'true') {
        if (strlen($register_to_vote_link) > 1) {
          $register_to_vote_link .= '&register_to_vote=true';
        }
        else {
          $register_to_vote_link .= 'register_to_vote=true';
        }
      }

      if ((isset($campaign) && $campaign == $session_handler->get('campaign_name')) || $session_handler->get('follow_story_campaign') == 'true') {
        $register_to_vote_link .= 'followed_campaign=true';
        $session_handler->clear('follow_story_campaign');
      }

      if ((isset($follow_normal_url) && $follow_normal_url == 'true')) {
        $register_to_vote_link .= 'follow-normal-url=true';
      }

      if (isset($url_campaign)) {
        $request = $url_campaign;
        $register_to_vote_link = '';
      }

      $vr_series_url = Url::fromRoute('<current>')->toString();
      if ($session_handler->get('login_by_campaign_form_' . $vr_series_url) !== NULL) {
        $request = $vr_series_url;
        $register_to_vote_link = '?reload=1&#series';
        $session_handler->clear('login_by_campaign_form_' . $vr_series_url);
      }
      if (isset($register_to_vr_event)) {
        $date_event = \Drupal::request()->query->get('date');
        $time_event = \Drupal::request()->query->get('time');
        if (strstr($register_to_vote_link, '#series')) {
          $register_to_vote_link = str_replace('#series', "", $register_to_vote_link);
          $register_to_vote_link .= 'date=' . $date_event . '&time=' . $time_event . '#series';
        }
        else {
          $register_to_vote_link .= 'date=' . $date_event . '&time=' . $time_event;
        }
      }

      if ($source) {
        $register_to_vote_link = '?source=' . $source;
      }

      $request = isset($request) ? $request . $register_to_vote_link : NULL;

      //      PERSONA: Check condition to send mail when user login from the Get toolkit webform
      if ($session_handler->get('persona_create_submission_verified_account') !== NULL) {
        $persona_id = $session_handler->get('persona_create_submission_verified_account');
        $current_user = Drupal::currentUser();

        if ($current_user->isAuthenticated() && !empty($current_user->getEmail())) {
          // Check condition to send mail to user when clicking to the Toolkit button
          $obw_query = \Drupal::service('obw_utilities.entity_query');
          $is_send_mail = $obw_query->checkToolkitWebformSendMail(\Drupal::currentUser()
            ->getEmail(), $persona_id);
          if (empty($is_send_mail)) {
            $obw_api_service = Drupal::service('obw_api.obw_api_service');
            $createSubmission = $obw_api_service->personaCreateSubmissionVerifiedAccount($persona_id, $current_user);
          }
        }
        $session_handler->clear('persona_create_submission_verified_account');
      }

      if (in_array('writer', \Drupal::currentUser()->getRoles())) {
        $request = '/admin/writer';
      }
      if ($request == NULL) {
        $request = '/';
        if ($session_handler->get('url-previous') !== NULL) {
          $request = $session_handler->get('url-previous');
          $session_handler->clear('url-previous');
        }

        if ($register_to_vote_link && $session_handler->get('submission_id') !== NULL) {
          $list_id_submission = $session_handler->get('list_id_submission');
          $nid = array_search($session_handler->get('submission_id'), $list_id_submission);
          $request .= 'node/' . $nid . $register_to_vote_link;
        }

        if ($session_handler->get('follow-normal') !== NULL) {
          $request = '';
          $request .= $session_handler->get('follow-normal');
          $session_handler->clear('follow-normal');
        }
      }

      if ($session_handler->get('url-previous') !== NULL) {
        $request = $session_handler->get('url-previous');
        $session_handler->clear('url-previous');
      }

      if (!empty(\Drupal::hasService('obw_vr_story.event_service'))) {
        $event_service = \Drupal::service('obw_vr_story.event_service');
        $event_service->createSubmissionByApi($request);
      }

      $user = User::load($account->id());
      if ($user->get('field_redirect_choose_actions')->value == 1) {
        $user->set('field_redirect_choose_actions', 0);
        $user->save();
        $session_handler->clear('causes');
        $session_handler->clear('your_actions');
        $session_handler->clear('countries');
        $session_handler->clear('nid');
        $response = new AjaxResponse();
        $command = new RedirectCommand('/user/' . $user->id() . '/you-can-help-stories');
        $response->addCommand($command);
        return $response;
      }


      $response->addCommand(
        new RedirectCommand($request)
      );
      $response->addCommand(
        new DataCommand(".user-selector", "user_id", $account->id())
      );
    }
    return $response;
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
      $form_value = $form_state->getValues();
      if (isset($form_value['name']) && $form_value['name']) {
        $email = $form_value['name'];
        $user_id = user_load_by_mail($email)->id();
        $user_data = User::load($user_id);
        $user_data->set('field_redirect_choose_actions', 1);
        $user_data->save();
      }

      $messenger = \Drupal::messenger();
      $messenger->deleteByType('error');
      $messenger->deleteByType('warning');
      $messenger->deleteByType('status');
      $div = '<div id="content">
    <div id="user-action" class="pt-30 pb-20 message-popup">
    <div class="container">
        <div class="title pb-15"><h3>' . t('Email sent!') . '</h3> </div>' .
        '<div class="message success text-center"><p>' . t('Instructions to reset your password have been emailed to you. Please check your email.') . '</p> </div>' .
        '</div></div></div>';
      $response->addCommand(new ReplaceCommand('#user-action-pass-wrapper', $div));
    }
    return $response;
  }

  public static function getUserFieldValueOrEmpty(\Drupal\user\Entity\User $user, $field, $default = '') {

    if ($field == 'field_account_city_of_residence' && $user->get($field)->value == '-1') {
      return $default;
    }
    if ($field == 'field_account_nationality' && $user->get($field)->value == '-1') {
      return $default;
    }
    if ($field == 'field_account_residence' && in_array($user->get($field)->value, [
        'Select State',
        'Select City',
      ])) {
      return $default;
    }

    if ($field == 'field_account_news_subscribed') {
      return boolval($user->get($field)->value);
    }

    if (in_array($field, [
      'field_account_actions',
      'field_account_countries',
      'field_account_cause',
    ])) {
      $referenced = $user->get($field)->referencedEntities();
      $output = [];
      /** @var \Drupal\taxonomy\Entity\Term $item */
      foreach ($referenced as $item) {
        $output[] = $item->getName();
      }
      return $output ? implode(', ', $output) : '';
    }

    return $user->get($field)->value ? $user->get($field)->value : $default;
  }

  /**
   * @throws \Drupal\Core\Entity\EntityStorageException
   */
  protected static function createWebform($account, $webform_id, $uri) {

    $session_handler = \Drupal::service('obw_social.session_handler');
    if ($session_handler->get('countries') &&
      $session_handler->get('causes') &&
      $session_handler->get('your_actions') &&
      $session_handler->get('nid')) {

      $ip_address = \Drupal::request()->getClientIp();
      $wf_data = [
        'webform_id' => $webform_id,
        'remote_addr' => $ip_address,
        'uri' => $uri,
        'uid' => $account->id(),
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

      $user = User::load($account->id());
      $user->set('field_account_actions', json_decode($_GET['your_actions']));
      $user->set('field_account_cause', json_decode($_GET['causes']));
      $user->set('field_account_countries', json_decode($_GET['countries']));
      $user->set('field_redirect_choose_actions', 1);
      $user->save();
    }
  }

}
