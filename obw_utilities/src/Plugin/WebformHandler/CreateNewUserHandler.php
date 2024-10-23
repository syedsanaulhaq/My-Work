<?php

namespace Drupal\obw_utilities\Plugin\WebformHandler;

use Drupal;
use Drupal\node\Entity\Node;
use Drupal\obw_reminder\Entity\ReminderEntity;
use Drupal\webform\Plugin\WebformHandlerBase;
use Drupal\webform\WebformSubmissionInterface;

/**
 * Form submission handler.
 *
 * Handlers after the submit.
 *
 * @WebformHandler(
 *   id = "create_new_account",
 *   label = @Translation("Create new user"),
 *   category = @Translation("OBW Custom"),
 *   description = @Translation("Create new user after the submission is
 *   created"), cardinality =
 *       \Drupal\webform\Plugin\WebformHandlerInterface::CARDINALITY_SINGLE,
 *   results =
 *    \Drupal\webform\Plugin\WebformHandlerInterface::RESULTS_PROCESSED,
 * )
 */
class CreateNewUserHandler extends WebformHandlerBase {

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    return [];
  }

  /**
   * {@inheritdoc}
   */
  public function getSummary() {
    // Broken/missing webform handlers do not need a summary.
    return [];
  }

  public function postSave(WebformSubmissionInterface $webform_submission, $update = TRUE) {
    $wf = $webform_submission->getWebform();
    $ws_data = $webform_submission->getData();

    $user = user_load_by_mail($ws_data['email']);
    if (!$user) {
      $name = strstr($ws_data['email'], '@', TRUE);
      //TODO: check rule to create user login name, currently get the first part of email address, with random num 100-999
      $new_name = $name;
      $user = user_load_by_name($new_name);
      while ($user) {
        $rand_num = mt_rand(100, 999);
        $new_name = $name . '_' . $rand_num;
        $user = user_load_by_name($new_name);
      }

      if ($wf->id() == 'support_us') {
        $first_name = $ws_data['payment_first_name'];
        $last_name = $ws_data['payment_last_name'];
      }
      else {
        $first_name = $ws_data['first_name'];
        $last_name = $ws_data['last_name'];
      }

      $account_fields = [
        'field_account_first_name' => $first_name,
        'field_account_last_name' => $last_name,
        'name' => $new_name,
        'mail' => $ws_data['email'],
        'field_account_accept_tnc' => 1,
        'field_account_salutation' => !empty($ws_data['salutation']) ? $ws_data['salutation'] : '',
        'field_account_contact_number' => !empty($ws_data['phone']) ? $ws_data['phone'] : '',
        'field_account_nationality' => !empty($ws_data['nationality']) ? $ws_data['nationality'] : '',
        'field_account_residence' => !empty($ws_data['country']) ? $ws_data['country'] : '',
        'field_account_city_of_residence' => !empty($ws_data['city']) ? $ws_data['city'] : '',
        'field_account_street_address' => !empty($ws_data['street_address']) ? $ws_data['street_address'] : '',
        'field_account_street_address_2' => !empty($ws_data['street_address_2']) ? $ws_data['street_address_2'] : '',
        'field_account_postal_code' => !empty($ws_data['postal_code']) ? $ws_data['postal_code'] : '',
      ];

      if (!empty($ws_data['sign_me_up_to_receive_more_inspiring_stories_like_this_from_our_'])) {
        $account_fields['field_account_news_subscribed'] = 1;
        $account_fields['field_account_subcribe_options'] = $ws_data['sign_me_up_to_receive_more_inspiring_stories_like_this_from_our_'];
      }

      if (!empty($ws_data['would_you_like_to_receive_stories_of_good_like_this_one_from_our']) && $ws_data['would_you_like_to_receive_stories_of_good_like_this_one_from_our'] != 'No') {
        $account_fields['field_account_news_subscribed'] = 1;
        $account_fields['field_account_subcribe_options'] = $ws_data['would_you_like_to_receive_stories_of_good_like_this_one_from_our'];
      }

      switch ($wf->id()) {
        case 'typeform_integrated':
          $account_fields['field_account_signup_source'] = !empty($ws_data['typeform_title']) ? 'Typeform | ' . $ws_data['typeform_title'] : 'Register from Typeform';
          $account_fields['field_account_signup_url'] = !empty($ws_data['typeform_id']) ? 'https://ourbetterworld1.typeform.com/to/' . $ws_data['typeform_id'] : '';
          break;
        default:
          $account_fields['field_account_signup_url'] = !empty($webform_submission->uri->value) ? $webform_submission->uri->value : '';
          $path = Drupal::service('path_alias.manager')
            ->getPathByAlias($webform_submission->uri->value);
          if (preg_match('/node\/(\d+)/', $path, $matches)) {
            $node = Node::load($matches[1]);
            $account_fields['field_account_signup_source'] = $node->getTitle();
          }
      }

      $account = Drupal\user\Entity\User::create($account_fields);
      $account->activate();
      $account->save();
      Drupal::logger('create_account_webformhandler')
        ->info('User has created successful.');
      $session_handler = \Drupal::service('obw_social.session_handler');
      $session_handler->set('create_acc_to_register_event', [
        'webform_id' => $wf->id(),
        'sid' => $webform_submission->id(),
        'uri' => $webform_submission->uri->value,
        'category' => $wf->get('category'),
      ]);


      if (!empty($ws_data['reminder_register_account']) && $ws_data['reminder_register_account'] == '1') {
        $reminder_data = [
          'name' => 'Reminder ' . $account->mail->value,
          'user_id' => $account->id(),
          'status' => '1',
          'field_reminder_email' => $account->mail->value,
          'field_reminder_type' => 1,
          'field_reminder_time' => !empty($ws_data['reminder_duration']) ? time() + $ws_data['reminder_duration'] : time() + 86400,
          'field_reminder_desnation_url' => $webform_submission->uri->value,
          'field_reminder_submission_id' => $webform_submission->id(),
          'field_reminder_duration' => !empty($ws_data['reminder_duration']) ? $ws_data['reminder_duration'] : 86400,
          'field_reminder_total' => !empty($ws_data['reminder_total']) ? $ws_data['reminder_total'] : 2,
          'field_reminder_count' => 1,
          'field_reminder_status' => 0,
          'field_reminder_webform_id' => $wf->id(),
        ];

        $reminder_entity = ReminderEntity::create($reminder_data);
        $reminder_entity->save();
        $session_handler->set('reminder_record_id', $reminder_entity->id());
      }

      if ($wf->id() !== 'email_me_a_copy_of_my_persona') {
        _user_mail_notify('register_no_approval_required', $account);
      }
    }
  }

}
