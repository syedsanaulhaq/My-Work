<?php

namespace Drupal\obw_utilities\Element;

use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Render\Element;

/**
 * Provides a 'obw_subscribe_element'.
 *
 * Webform elements are just wrappers around form elements, therefore every
 * webform element must have correspond FormElement.
 *
 * Below is the definition for a custom 'webform_example_element' which just
 * renders a simple text field.
 *
 * @FormElement("obw_subscribe_element")
 *
 * @see \Drupal\Core\Render\Element\FormElement
 * @see https://api.drupal.org/api/drupal/core%21lib%21Drupal%21Core%21Render%21Element%21FormElement.php/class/FormElement
 * @see \Drupal\Core\Render\Element\RenderElement
 * @see https://api.drupal.org/api/drupal/namespace/Drupal%21Core%21Render%21Element
 * @see \Drupal\webform_example_element\Element\WebformExampleElement
 */
class OBWSubscribeElement extends Element\Container {

  /**
   * {@inheritdoc}
   */
  public function getInfo() {
    $info = parent::getInfo();
    $info['#process'][] = [get_class($this), 'processOBWSubscribeElement'];
    return $info;
  }

  public static function processOBWSubscribeElement(&$element, FormStateInterface $form_state, &$complete_form) {
    $element['obw_subscribe_checkbox'] = [
      '#type' => 'checkbox',
      '#title' => 'Sign me up to receive more inspiring stories from Our Better World.',
    ];

    $element['obw_subscribe_container'] = [
      '#type' => 'container',
      '#title' => NULL,
      'obw_subscribe_radios' => [
        '#type' => 'radios',
        '#options' => [
          'weekly' => 'Weekly',
          'monthly' => 'Monthly',
        ],
        '#title' => '',
        '#default_value' => 'weekly',
      ],
      '#states' => [
        'visible' => [
          ':input[name="obw_subscribe_checkbox"]' => ['checked' => TRUE],
        ],
      ],
      '#attributes' => [
        'class' => [
          'pl-20',
        ],
      ],
    ];

    return $element;
  }

}
