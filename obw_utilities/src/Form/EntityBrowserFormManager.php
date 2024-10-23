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
 * Alter form to fix issues in entity browser;
 *
 * - entity_browser_story_subject_form
 * - entity_browser_story_media_browser_form
 *
 *
 * Class EntityBrowserFormManager
 *
 * @package Drupal\obw_utilities\Form
 */
class EntityBrowserFormManager extends FormAlterBase {

  /**
   * {@inheritdoc}
   */
  public function alterForm(array &$form, FormStateInterface $form_state) {
    if (!empty($form['widget']['view']['entity_browser_select'])) {
      foreach ($form['widget']['view']['entity_browser_select'] as &$item) {
        if (!empty($item['#type']) && $item['#type'] == 'radio') {
          $item['#attributes']['name'] = 'entity_browser_select[]';
        }
      }
    }
    if ($form['#form_id'] == 'entity_browser_user_browser_form') {
      //$form['selection_display']['use_selected']['#limit_validation_errors'] = true;
    }

    if ($form['#form_id'] == 'entity_browser_story_media_browser_form') {
      $form['#attached']['library'][] = 'obw_utilities/media-entity-browser-view';
    }
  }

  /**
   * validate form register
   *
   * @param $form
   * @param FormStateInterface $form_state
   */
  public static function userRegisterFormValidate($form, \Drupal\Core\Form\FormStateInterface &$form_state) {
    $errors = $form_state->getErrors();
    $form_state->clearErrors();
    foreach ($errors as $key => $message) {
      if ('pass' !== $key && 'inline_entity_form][field_account_accept_tnc][value' != $key) {
        $form_state->setErrorByName($key, $message);
      }
    }
    $user_input = $form_state->getUserInput();
    $form_state->setValue('mail', $user_input['mail']);
    $register_mail = $form_state->getValue('mail');
    if (filter_var($register_mail, FILTER_VALIDATE_EMAIL)) {
      $user = user_load_by_mail($register_mail);
      if ($user) {
        $form_state->setValue('name', $user->getAccountName());
        $user_input['name'] = $user->getAccountName();
        $form_state->setErrorByName('mail', t('@mail is already taken.', ['@mail' => $register_mail]));
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
        $user_input['name'] = $new_name;
      }
      $form_state->setUserInput($user_input);
    }
    else {
      $form_state->setErrorByName('mail', t('@mail is error format.', ['@mail' => $register_mail]));
    }

    if (empty($user_input['pass']['pass1']) || empty($user_input['pass']['pass2'])) {
      $form_state->setErrorByName('pass', t('Password is required.'));
    }
    else {
      if ($user_input['pass']['pass1'] !== $user_input['pass']['pass2']) {
        $form_state->setErrorByName('pass', t('Confirm pass does not match.'));
      }
      else {
        $form_state->setValue('pass', $user_input['pass']);
      }
    }
  }
  /**
   * @FormValidate(
   *   priority = -5,
   *   strategy = "append",
   * )
   */
  /* public function nameOfValidateHandler1(array &$form, FormStateInterface $form_state) {
   }*/

  /**
   * @FormValidate(
   *   priority = 1,
   *   strategy = "append",
   * )
   */
  /*public function nameOfValidateHandler2(array &$form, FormStateInterface $form_state) {
  }*/

  /**
   * @FormSubmit(
   *   strategy = "prepend",
   * )
   */
  /*public function nameOfSubmitHandler1(array &$form, FormStateInterface $form_state) {
  }*/

  /**
   * @FormSubmit()
   */
  /*public function nameOfSubmitHandler2(array $form, FormStateInterface $form_state) {
  }*/

}
