<?php
/**
 * Created by PhpStorm.
 * User: TRI_TRAN
 * Date: 14-Aug-18
 * Time: 11:57 AM
 */

namespace Drupal\obw_utilities\Validate;
use Drupal\Core\Field\FieldException;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Component\Utility\Xss;

class FormValidateConstraint {

    /**
     * Validates given element.
     *
     * @param array              $element      The form element to process.
     * @param FormStateInterface $formState    The form state.
     * @param array              $form The complete form structure.
     */
    public static function validatePattern(array &$element, FormStateInterface $form_state, array &$form) {

        $webformKey = $element['#webform_key'];
        $value = $form_state->getValue($webformKey);

        if ($value !== '') {
            if (!filter_var($value, FILTER_VALIDATE_EMAIL)) {

                if(!empty($form['elements']['container']['email']['#pattern_error'])) {
                  $form_state->setError($element, t($form['elements']['container']['email']['#pattern_error']));
                }
                else {
                  if($element['#webform'] == 'mh2021_narrative_coming_soon' && !empty($element['#pattern_error'])) {
                    $form_state->setError($element, t($element['#pattern_error']));
                  }
                  else {
                    $form_state->setError($element, t('We can’t seem to find this email address. Please try again.'));
                  }
                }
            }

            if( $element['#name'] == 'email'){
                $element['#unique_error'] = Xss::filter(str_replace('***',$element['#value'], $element['#unique_error']));
            }
        }
    }

    public static function alterValidateCity(array &$element, FormStateInterface $form_state, array &$form) {

        $webformKey = $element['#webform_key'];
        $value = $form_state->getValue($webformKey);
        if ($value !== '') {

            // Todo: remove validate field "city" to pass value was selected
            if (!is_null($form_state->getError($element))) {
                $form_errors = $form_state->getErrors();
                $form_state->clearErrors();
                unset($form_errors['city']);
                foreach ($form_errors as $name => $error_message) {
                    $form_state->setErrorByName($name, $error_message);
                }
            }
        }
    }


}
