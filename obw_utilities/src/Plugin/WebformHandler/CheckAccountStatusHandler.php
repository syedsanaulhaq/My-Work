<?php

namespace Drupal\obw_utilities\Plugin\WebformHandler;

use Drupal;
use Drupal\Core\Form\FormStateInterface;
use Drupal\webform\Plugin\WebformHandlerBase;
use Drupal\webform\WebformSubmissionInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;

/**
 * Check Account Status Handler.
 *
 * @WebformHandler(
 *   id = "check_account_status_handler",
 *   label = @Translation("Check account status handler"),
 *   category = @Translation("OBW Custom"),
 *   description = @Translation("Need to create the select form field
 *   (field_key: account_status) with 3 values: [-1 => 'No account', 0 =>
 *   'Unverified account', 1 => 'Verified account']"), cardinality =
 *       \Drupal\webform\Plugin\WebformHandlerInterface::CARDINALITY_SINGLE,
 *   results =
 *    \Drupal\webform\Plugin\WebformHandlerInterface::RESULTS_PROCESSED,
 * )
 */
class CheckAccountStatusHandler extends WebformHandlerBase {

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    return [];
  }

  public function preSave(WebformSubmissionInterface $webform_submission) {
    $wf_data = $webform_submission->getData();
    if (key_exists('account_status', $wf_data) && !empty($wf_data['email'])) {
      if ($user = user_load_by_mail($wf_data['email'])) {
        if (intval($user->getLastAccessedTime()) == 0) {
          $wf_data['account_status'] = 0;
          $wf_data['send_mail'] = 1;
        }
        else {
          $wf_data['account_status'] = 1;
          $obw_query = \Drupal::service('obw_utilities.entity_query');
          $is_send_mail = $obw_query->checkToolkitWebformSendMail($wf_data['email'], $wf_data['persona_id']);
          if (empty($is_send_mail)) {
            $wf_data['send_mail'] = 1;
          }
        }
        if (!empty($user->id())) {
          $webform_submission->set('uid', $user->id());
        }
      }
      else {
        $wf_data['account_status'] = -1;
        $wf_data['send_mail'] = 1;
      }
      $webform_submission->setData($wf_data);
    }
  }

  public function confirmForm(array &$form, FormStateInterface $form_state, WebformSubmissionInterface $webform_submission) {
    //    $mailManager = \Drupal::service('plugin.manager.mail');
    //
    //    $module = 'obw_utilities';
    //    $key = 'node_insert';
    //    $to = \Drupal::currentUser()->getEmail();
    //    $params['message'] = 'test send mail';
    //    $params['body'] = 'test send mail';
    //    $params['node_title'] = 'test_label';
    //    $langcode = \Drupal::currentUser()->getPreferredLangcode();
    //    $send = true;
    //
    //    $result = $mailManager->mail($module, $key, $to, $langcode, $params, NULL, $send);

    $wf_data = $webform_submission->getData();
    $current_user = Drupal::currentUser();
    if (key_exists('account_status', $wf_data) && $wf_data['account_status'] == 1) {
      if (!empty($wf_data['persona_id'])) {
        $etm = Drupal::entityTypeManager();
        $node = $etm->getStorage('node')->load($wf_data['persona_id']);
        if ($node && $node->getType() == 'persona') {
          if (isset($node->field_persona_pdf_file) && !empty($node->field_persona_pdf_file->entity)) {
            $response = new RedirectResponse($node->field_persona_pdf_file->entity->toURL()
                ->toString() . '?login=true');
            $session_handler = Drupal::service('obw_social.session_handler');
            $session_handler->set('email_login', $wf_data['email']);
            $response->send();
            return;
          }
        }
      }

    }
  }

}
