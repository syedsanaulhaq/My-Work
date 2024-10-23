<?php

namespace Drupal\obw_zoom\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface; 
use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Ajax\OpenModalDialogCommand;



class ZoomDiscord extends FormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'ajax_example_autotextfields';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    // This container wil be replaced by AJAX.
    $form['container'] = [
      '#type' => 'container',
      '#attributes' => ['id' => 'box-container'],
    ];
    // The box contains some markup that we can change on a submit request.
    $form['container']['box'] = [
      '#type' => 'markup',
      '#markup' => '<h1>Discord</h1>',
    ];

    $form['submit'] = [
      '#type' => 'submit',
      // The AJAX handler will call our callback, and will replace whatever page
      // element has id box-container.
      '#ajax' => [
        'callback' => '::ajaxCallback',
        'wrapper' => 'box-container',
      ],
      '#value' => $this->t('Submit'),
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
  }

  public function ajaxCallback(array $form, FormStateInterface $form_state) {
    $response = new AjaxResponse();
    $response->addCommand(new OpenModalDialogCommand($this->t('Title!'), $this->t('<iframe title="WidgetBot Discord chat embed" src="https://e.widgetbot.io/channels/990875091271290880/990875091271290883/?api=14df1b35-73b4-4de8-a408-a0d9dd45e1b0" style="border: none; width: 100%; height: 100%;"></iframe>')));
    return $response;
  }

  /**
   * Callback for submit_driven example.
   *
   * Select the 'box' element, change the markup in it, and return it as a
   * renderable array.
   *
   * @return array
   *   Renderable array (the box element)
   */
  public function promptCallback(array &$form, FormStateInterface $form_state) {
    // In most cases, it is recommended that you put this logic in form
    // generation rather than the callback. Submit driven forms are an
    // exception, because you may not want to return the form at all.
    $element = $form['container'];
    $element['box']['#markup'] = "Clicked submit ({$form_state->getValue('op')}): " . date('c');
    return $element;
  }

}