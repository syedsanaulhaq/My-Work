<?php

namespace Drupal\obw_utilities\Plugin\WebformHandler;

use Drupal;
use Drupal\Core\Form\FormStateInterface;
use Drupal\node\Entity\Node;
use Drupal\obw_contributor_profile\Controller\ActiveCampaignController;
use Drupal\webform\Entity\WebformSubmission;
use Drupal\webform\Plugin\WebformHandlerBase;
use Drupal\webform\WebformSubmissionInterface;
use Drupal\Core\Url;
use Drupal\Component\Utility\Random;
use Drupal\obw_utilities\EntityQuery;

/**
 * Form submission handler.
 *
 * Handlers after the submit.
 *
 * @WebformHandler(
 *   id = "obw_utilities",
 *   label = @Translation("Webform submission handler"),
 *   category = @Translation("OBW Custom"),
 *   description = @Translation("Hanlder after submit"),
 *   cardinality =
 *       \Drupal\webform\Plugin\WebformHandlerInterface::CARDINALITY_SINGLE,
 *   results =
 *    \Drupal\webform\Plugin\WebformHandlerInterface::RESULTS_PROCESSED,
 * )
 */
class FormSubmissionHandler extends WebformHandlerBase {

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    return [];
  }

  /**
   * {@inheritdoc}
   */
  public function preSave(WebformSubmissionInterface $webform_submission) {
    $wf = $webform_submission->getWebform();
    $element_decoded = $wf->getElementsDecoded();
    $data = $webform_submission->getData();
    switch ($wf->id()) {
      case 'support_us_feedback_form':
        $feedback = $webform_submission->getElementData('feedback');
        if (isset($_SESSION['webform_submission_id'])) {
          $webform_submission = WebformSubmission::load($_SESSION['webform_submission_id']);
          if (!empty($webform_submission)) {
            $webform_submission->setElementData('why_did_you_donate', $feedback);
            $webform_submission->save();
          }
        }
        break;
      case 'support_us':
        if (!empty($data['monthly_basis']) && $data['monthly_basis'] == '1') {
          //        Change value of ac_tag to monthly_donor
          if (!empty($element_decoded['#attributes']) && !empty($element_decoded['#attributes']['monthly-donor'])) {
            $data['ac_tag'] = str_replace('~', ':', $element_decoded['#attributes']['monthly-donor']);
            $webform_submission->setData($data);
          }
        }
        else {
          $ac_controller = new ActiveCampaignController();
          $result_contact_view = $ac_controller->getACContactDetailByEmail($data['email']);
          if (!empty($result_contact_view['tags'])) {
            if (in_array(str_replace('~', ':', $element_decoded['#attributes']['one-time-donor']), $result_contact_view['tags']) !== FALSE) {
              if (!empty($element_decoded['#attributes']) && !empty($element_decoded['#attributes']['multiple-time-donor'])) {
                $data['ac_tag'] = str_replace('~', ':', $element_decoded['#attributes']['multiple-time-donor']);
                $webform_submission->setData($data);
              }
            }
          }
        }
        if (!empty($data['last_4digits'])) {
          //Removing user's credit card last 4 digits
          $data['last_4digits'] = '';
          $webform_submission->setData($data);
        }

        if (!empty($data['tax_rebate']) && $data['tax_rebate'] == 'yes' && !empty($data['nric_fin_number'])) {
          $total_amount = Drupal\stripe_popup\StripeManager::getTotalDonationByMail($data['email'], 0, TRUE);
          if (intval($total_amount) >= 50) {
            $nric_number = $data['nric_fin_number'];
          }
          //remove nric number in supportus form
          $data['nric_fin_number'] = '';
          $webform_submission->setData($data);
        }
        $_SESSION['support_us_tax_relief'] = [
          'nric_number' => $nric_number ?? '',
          'tax_relief' => !empty($data['tax_rebate']) ? $data['tax_rebate'] : '',
        ];
        break;
      case 'obw_birthday_2021':
        $themeName = \Drupal::service('theme.manager')
          ->getActiveTheme()
          ->getName();
        $service = Drupal::service('obw_utilities.service');
        $service_webform = Drupal::service('obw_utilities.entity_query');
        if ($themeName === 'obw_theme') {
          $service->randomShapeStypeForPostBirthday2021($data);
        }
        elseif ($themeName === 'obw_admin_theme') {
          $firstName = $service->cleanUrl($data['first_name']);
          $lastName = $service->cleanUrl($data['last_name']);
          $pickOne = $data['pick_one_of_our_values'];
          $status = $data['status'];
          if (empty($data['obw_values_story']) && $status === 'approved') {
            $dataResponse = $service_webform->getBirthday2021SubmissionRelated($data['first_name'], $data['last_name'], $pickOne);
            $count = $dataResponse > 0 ? $dataResponse + 1 : '';
            $data['obw_values_story'] = \Drupal::request()
                ->getSchemeAndHttpHost() . '/about-us/our-values?author=' . strtolower($pickOne) . $count . '-by-' . $firstName . '-' . $lastName;
          }
        }
        if (!$data['image_upload'] && $data['image_reupload']) {
          $data['image_upload'] = $data['image_reupload'];
          $data['image_reupload'] = NULL;
        }
        $webform_submission->setData($data);
        break;
      case 'obwaction_download_2021_creature':
        if (isset($_COOKIE['download_creature_first_time_visit'])
          && is_numeric($_COOKIE['download_creature_first_time_visit'])) {
          $nid = $_COOKIE['download_creature_first_time_visit'];
          if (Node::load($nid) instanceof Drupal\node\NodeInterface && Node::load($nid)
              ->bundle() == 'galleries' &&
            Node::load($nid)->field_gallery_anchor_id->value) {
            $data['animal_name'] = ucfirst(Node::load($nid)->field_gallery_anchor_id->value);
          }
        }
        $webform_submission->setData($data);
        break;
    }
  }

  public function confirmForm(array &$form, FormStateInterface $form_state, WebformSubmissionInterface $webform_submission) {
    $random = new Random();
    $submission_data = $webform_submission->getData();
    $wf = $webform_submission->getWebform();
    $wf_attrs = $form['#attributes'];
    //Update donation amount and donation count
    switch ($form['#webform_id']) {
      case 'support_us':
        $uid = $webform_submission->uid->target_id;
        // handle feedback form
        if (isset($_SESSION)) {
          unset($_SESSION['webform_submission_id']);
        }
        $_SESSION['webform_submission_id'] = $webform_submission->id();
        // handle tax relief form
        if (isset($submission_data['email']) && !empty($submission_data['email'])) {
          $key = $webform_submission->id() . $random->word(3) . rand(100, 999);
          $email = $webform_submission->getElementData('email');
          // check email
          $entityQuery = EntityQuery::getSubmissionIDByMailInTaxReliefForm($email);
          if (!empty($entityQuery)) {
            $load_submission = WebformSubmission::load($entityQuery['sid']);
            if (empty($_SESSION['support_us_tax_relief']['nric_number'])
              && $submission_data['monthly_basis']
              && $submission_data['is_total_donation_less_than_50']) {
              $key_load_submission = $load_submission->getElementData('key');
              $load_submission->setElementData('key_ac', \Drupal::request()
                  ->getSchemeAndHttpHost() . $wf_attrs['link_to_tax_form'] . '?key=' . $key);
              if (!empty($key_load_submission)) {
                $key = $key . ',' . $key_load_submission;
              }
              $load_submission->setElementData('key', $key);
            }
            else {
              $load_submission->setElementData('key_ac', ' ');
              $load_submission->setElementData('key', '');
            }

            $load_submission->setElementData('country', $submission_data['country']);
            $load_submission->setElementData('is_a_monthly_donation', $submission_data['monthly_basis']);
            $load_submission->setElementData('is_total_donation_less_than_50', $submission_data['is_total_donation_less_than_50'] ?? 0);
            $load_submission->setElementData('nric_fin_number', (!empty($_SESSION['support_us_tax_relief']['nric_number']) ? $_SESSION['support_us_tax_relief']['nric_number'] : ''));
            $load_submission->setElementData('tax_relief', (!empty($_SESSION['support_us_tax_relief']['tax_relief']) ? $_SESSION['support_us_tax_relief']['tax_relief'] : ''));
            unset($_SESSION['support_us_tax_relief']);
            $load_submission->save();
          }
          else {
            if (empty($_SESSION['support_us_tax_relief']['nric_number'])
              && $submission_data['monthly_basis']
              && $submission_data['is_total_donation_less_than_50']) {
              $create_submission = WebformSubmission::create([
                'webform_id' => 'support_us_tax_form',
                'data' => [
                  'email' => $email,
                  'key' => $key,
                  'key_ac' => \Drupal::request()
                      ->getSchemeAndHttpHost() . $wf_attrs['link_to_tax_form'] . '?key=' . $key,
                  'country' => $submission_data['country'],
                  'is_a_monthly_donation' => $submission_data['monthly_basis'],
                  'is_total_donation_less_than_50' => $submission_data['is_total_donation_less_than_50'] ?? 0,
                  'nric_fin_number' => !empty($_SESSION['support_us_tax_relief']['nric_number']) ? $_SESSION['support_us_tax_relief']['nric_number'] : '',
                  'tax_relief' => !empty($_SESSION['support_us_tax_relief']['tax_relief']) ? $_SESSION['support_us_tax_relief']['tax_relief'] : '',
                ],
              ]);
            }
            else {
              $create_submission = WebformSubmission::create([
                'webform_id' => 'support_us_tax_form',
                'data' => [
                  'email' => $email,
                  'country' => $submission_data['country'],
                  'is_a_monthly_donation' => $submission_data['monthly_basis'],
                  'is_total_donation_less_than_50' => $submission_data['is_total_donation_less_than_50'] ?? 0,
                  'nric_fin_number' => !empty($_SESSION['support_us_tax_relief']['nric_number']) ? $_SESSION['support_us_tax_relief']['nric_number'] : '',
                  'tax_relief' => !empty($_SESSION['support_us_tax_relief']['tax_relief']) ? $_SESSION['support_us_tax_relief']['tax_relief'] : '',
                ],
              ]);
            }
            unset($_SESSION['support_us_tax_relief']);
            $create_submission->save();
          }

        }
        if ($uid !== 0 && $uid !== '0') {
          $action_service = Drupal::service('action_entity.action_service');
          $action_service->updateDonationForUser($uid, $submission_data['payment_amount']);
        }
        $query = \Drupal::service('obw_utilities.entity_query');
        $sids = $query->getSubmissionMonthlyIDByEmailInSupportUs($submission_data['payment_email']);
        if (!empty($sids)) {
          $sids_array = [];
          foreach ($sids as $sid) {
            if ($sid->sid !== $webform_submission->id()) {
              $sids_array[] = $sid->sid;
            }
          }
          $submissions = WebformSubmission::loadMultiple($sids_array);
          foreach ($submissions as $submission) {
            $ws_data = $submission->getData();
            $ws_data['payment_status'] = 'Cancelled';
            $submission->setData($ws_data);
            $submission->save();
          }
        }
        break;
      case 'vr_event_form':
        $session_handler = \Drupal::service('obw_social.session_handler');
        //This webform only send from Singapore: add +65 to the phone number
        if (!empty($submission_data['field_account_contact_number'])) {
          $submission_data['field_account_contact_number'] = '+65' . $submission_data['field_account_contact_number'];
        }
        $webform_submission->setData($submission_data);

        $is_set_up_acc = $submission_data['set_up_an_account'] == 1 ? TRUE : FALSE;
        $register_mail = $submission_data['email'];
        $current_url = Url::fromRoute('<current>')->toString();
        $session_handler->set('vr_event_sms_reminder', !empty($submission_data['field_account_contact_number']) ? TRUE : FALSE);

        if ($is_set_up_acc) {
          $user = user_load_by_mail($register_mail);
          if ($user) {
            $session_handler->set('vr_event_email_exist', TRUE);
            //          $form['email_exist'] = [
            //            'type'=>'hidden',
            //            'value' => true
            //          ];
          }
          else {
            $session_handler->set('vr_event_email_exist', FALSE);
            //          $form['email_exist'] = [
            //            'type'=>'hidden',
            //            'value' => false
            //          ];

            $name = strstr($register_mail, '@', TRUE);
            //TODO: check rule to create user login name, currently get the first part of email address, with random num 100-999
            $new_name = $name;
            $user = user_load_by_name($new_name);
            while ($user) {
              $rand_num = mt_rand(100, 999);
              $new_name = $name . '_' . $rand_num;
              $user = user_load_by_name($new_name);
            }

            $account_fields = [
              'field_account_first_name' => $submission_data['first_name'],
              'field_account_last_name' => $submission_data['last_name'],
              'name' => $new_name,
              'mail' => $register_mail,
              'field_account_signup_url' => Url::fromRoute('<current>')
                ->toString(),
            ];
            $account = entity_create('user', $account_fields);
            $account->activate();
            $account->save();
            _user_mail_notify('register_no_approval_required', $account);
          }
        }
        break;
    }

    if (!empty($wf->get('category'))) {
      switch ($wf->get('category')) {
        case 'Donate API - GlobalGiving':
          //Update donation amount and donation count when submitting via Donate API - GlobalGiving
          $uid = $webform_submission->uid->target_id;
          if ($uid) {
            $action_service = Drupal::service('action_entity.action_service');
            $action_service->updateDonationForUser($uid, $submission_data['amount']);
          }
          break;
        case 'Redirect to Paypal gateway':
          $payment_method_field_name = !empty($wf_attrs['payment_method_field_name']) ? $wf_attrs['payment_method_field_name'] : 'preferred_payment_method';
          $paypal_amount_field_name = !empty($wf_attrs['paypal_amount_field_name']) ? $wf_attrs['paypal_amount_field_name'] : 'paypal_donation_amount';
          if (!empty($submission_data[$payment_method_field_name]) && $submission_data[$payment_method_field_name] == 'paypal'
            && !empty($wf_attrs['paypal_link'])
            && !empty($submission_data[$paypal_amount_field_name])) {
            $paypal_link = $wf_attrs['paypal_link'] . bcdiv($submission_data[$paypal_amount_field_name], 1, 2);
            if (!empty($wf_attrs['currency'])) {
              $paypal_link .= $wf_attrs['currency'];
            }
            $form_state->setResponse(new Drupal\Core\Routing\TrustedRedirectResponse($paypal_link));
          }
          break;
      }
    }
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state, WebformSubmissionInterface $webform_submission) {
    switch ($form['#webform_id']) {
      case 'obw_birthday_2021':
        if ($form_state->getValue('share_your_story') !== "") {
          $story = $form_state->getValue('share_your_story');
          if (count(explode(' ', $story)) > 100) {
            $form_state->setErrorByName('words_length', 'Please keep your story within 100 words.');
          }
        }
        break;
      case 'obwaction_download_2021_creature':
        if (empty($form_state->getErrors())) {
          $values = $form_state->getValues();
          if (isset($values['email'])) {
            if (isset($_COOKIE['download_creature_first_time_visit'])
              && is_numeric($_COOKIE['download_creature_first_time_visit'])) {
              $nid = $_COOKIE['download_creature_first_time_visit'];
              if (Node::load($nid) instanceof Drupal\node\NodeInterface && Node::load($nid)
                  ->bundle() == 'galleries' &&
                Node::load($nid)->field_gallery_anchor_id->value) {
                setcookie("download_creature_reuse", $values['email'], 2147483647, "/");
                setcookie("newsletter_status", $values['would_you_like_to_receive_stories_of_good_like_this_one_from_our'], 2147483647, "/");
              }
              else {
                unset($_COOKIE['download_creature_first_time_visit']);
                setcookie('download_creature_first_time_visit', NULL, -1, '/');
                $form_state->setErrorByName('invalid_nid', 'Invalid NID.');
              }
            }
          }
        }
        elseif (isset($form_state->getErrors()['captcha_response'])) {
          $form_state->setErrorByName('captcha_response', 'Anti bot checked.');
        }
        else {
          $form_state->setErrorByName('can_not_submit', 'Cannot submit.');
        }
        break;
      case 'support_us':
        $user_input = $form_state->getUserInput();
        $red_flag = FALSE;
        if (isset($user_input['amount_donation']) && isset($user_input['amount_donation']['radios']) && isset($user_input['amount_donation']['other'])) {
          if ($user_input['amount_donation']['radios'] == '_other_') {
            if (!is_numeric($user_input['amount_donation']['other'])) {
              $red_flag = TRUE;
            }
            else {
              $amount_donation = $user_input['amount_donation']['other'];
              $break_amount_donation = explode('.', $amount_donation);
              if ((is_array($break_amount_donation) && count($break_amount_donation) == 2 && strlen($break_amount_donation[1]) > 2)) {
                $red_flag = TRUE;
              }
              if ($amount_donation < 1) {
                $red_flag = TRUE;
              }
            }
          }
          if ($red_flag) {
            $form_state->setError($form['elements']['donation_details']['amount_wrapper']['amount_donation'], 'Invalid Amount Donation.');
            $form_state->setErrorByName('invalid_amount_donation', 'Invalid Amount Donation.');
          }

        }
        break;
    }
  }

}
