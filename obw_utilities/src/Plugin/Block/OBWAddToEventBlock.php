<?php
/**
 * Created by PhpStorm.
 * User: TRI_TRAN
 * Date: 10-Sep-18
 * Time: 11:30 PM
 */

namespace Drupal\obw_utilities\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Datetime\DrupalDateTime;
use Drupal\Core\Form\FormStateInterface;

/**
 * Provides a 'OBWAddToEventBlock' block.
 *
 * @Block(
 *  id = "obw_add_to_event_block",
 *  admin_label = @Translation("OBW Add to Event Block"),
 * )
 */
class OBWAddToEventBlock extends BlockBase {

  /**
   * {@inheritdoc}
   */
  public function build() {
    $event_params = new \stdClass();
    $today = date("Ymd\THis");
    $event_params->subject = isset($this->configuration['subject']) ? $this->configuration['subject'] : 'no-subject';
    $event_params->date_start = isset($this->configuration['date_start']) ? $this->configuration['date_start'] : $today;
    $event_params->date_end = isset($this->configuration['date_end']) ? $this->configuration['date_end'] : $today;
    $event_params->details = isset($this->configuration['details']) ? $this->configuration['details'] : '';
    $event_params->locations = isset($this->configuration['locations']) ? $this->configuration['locations'] : '';
    $event_params->id_event = isset($this->configuration['id_event']) ? $this->configuration['id_event'] : 'no-event';
    // Joe add 17-10-2022
    $event_params->subject_in_person = isset($this->configuration['subject_in_person']) ? $this->configuration['subject_in_person'] : 'no-subject';
    $event_params->date_start_in_person = isset($this->configuration['date_start_in_person']) ? $this->configuration['date_start_in_person'] : $today;
    $event_params->date_end_in_person = isset($this->configuration['date_end_in_person']) ? $this->configuration['date_end_in_person'] : $today;
    $event_params->details_in_person = isset($this->configuration['details_in_person']) ? $this->configuration['details_in_person'] : '';
    $event_params->locations_in_person = isset($this->configuration['locations_in_person']) ? $this->configuration['locations_in_person'] : '';
    $event_params->id_event_in_person = isset($this->configuration['id_event_in_person']) ? $this->configuration['id_event'] : 'no-event';
    // Joe add 17-10-2022

    $json_event_params = json_encode($event_params);
    return [
      '#theme' => 'obw_add_to_event',
      '#subject' => $event_params->subject,
      '#date_start' => $event_params->date_start,
      '#date_end' => $event_params->date_end,
      '#details' => $event_params->details,
      '#locations' => $event_params->locations,
      '#id_event' => $event_params->id_event,
      // Joe add 17-10-2022
      '#subject_in_person' => $event_params->subject_in_person,
      '#date_start_in_person' => $event_params->date_start_in_person,
      '#date_end_in_person' => $event_params->date_end_in_person,
      '#details_in_person' => $event_params->details_in_person,
      '#locations_in_person' => $event_params->locations_in_person,
      '#id_event_in_person' => $event_params->id_event_in_person,
      // Joe add 17-10-2022
      '#attached' => [
        'library' => [
          'obw_utilities/obw-add-to-event',
        ],
        'drupalSettings' => [
          'event_params' => [$event_params->id_event => $json_event_params],
        ],
      ],
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function blockForm($form, FormStateInterface $form_state) {

    $form['label_display']['#default_value'] = TRUE;
    $form['label_display']['#return_value'] = 'invisible';
    $form['id_event'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Event id'),
      '#required' => TRUE,
      '#default_value' => isset($this->configuration['id_event']) ? $this->configuration['id_event'] : '',
    ];
    $form['subject'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Subject'),
      '#required' => TRUE,
      '#default_value' => isset($this->configuration['subject']) ? $this->configuration['subject'] : '',
    ];
    $date_start = isset($this->configuration['date_start']) ? strtotime($this->configuration['date_start']) : time();
    $form['date_start'] = [
      '#type' => 'datetime',
      '#title' => $this->t('Date time start'),
      '#required' => TRUE,
      '#default_value' => DrupalDateTime::createFromTimestamp($date_start),
    ];
    $date_end = isset($this->configuration['date_end']) ? strtotime($this->configuration['date_end']) : time();
    $form['date_end'] = [
      '#type' => 'datetime',
      '#title' => $this->t('Date time end'),
      '#required' => TRUE,
      '#default_value' => DrupalDateTime::createFromTimestamp($date_end),
    ];
    $form['details'] = [
      '#type' => 'textarea',
      '#title' => $this->t('Details'),
      '#required' => TRUE,
      '#default_value' => isset($this->configuration['details']) ? $this->configuration['details'] : '',
    ];
    $form['locations'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Locations'),
      '#required' => FALSE,
      '#default_value' => isset($this->configuration['locations']) ? $this->configuration['locations'] : '',
    ];
    // Joe add 17-10-2022
    // $form['label_display']['#default_value'] = TRUE;
    // $form['label_display']['#return_value'] = 'invisible';
    // $form['id_event_in_person'] = [
    //   '#type' => 'textfield',
    //   '#title' => $this->t('Event id In Person'),
    //   '#required' => TRUE,
    //   '#default_value' => isset($this->configuration['id_event_in_person']) ? $this->configuration['id_event_in_person'] : '',
    // ];
    // $form['subject_in_person'] = [
    //   '#type' => 'textfield',
    //   '#title' => $this->t('Subject In Person'),
    //   '#required' => TRUE,
    //   '#default_value' => isset($this->configuration['subject_in_person']) ? $this->configuration['subject_in_person'] : '',
    // ];
    // $date_start = isset($this->configuration['date_start_in_person']) ? strtotime($this->configuration['date_start_in_person']) : time();
    // $form['date_start_in_person'] = [
    //   '#type' => 'datetime',
    //   '#title' => $this->t('Date time start In Person'),
    //   '#required' => TRUE,
    //   '#default_value' => DrupalDateTime::createFromTimestamp($date_start),
    // ];
    // $date_end = isset($this->configuration['date_end_in_person']) ? strtotime($this->configuration['date_end_in_person']) : time();
    // $form['date_end_in_person'] = [
    //   '#type' => 'datetime',
    //   '#title' => $this->t('Date time end In Person'),
    //   '#required' => TRUE,
    //   '#default_value' => DrupalDateTime::createFromTimestamp($date_end),
    // ];
    // $form['details_in_person'] = [
    //   '#type' => 'textarea',
    //   '#title' => $this->t('Details In Person'),
    //   '#required' => TRUE,
    //   '#default_value' => isset($this->configuration['details_in_person']) ? $this->configuration['details_in_person'] : '',
    // ];
    // $form['locations_in_person'] = [
    //   '#type' => 'textfield',
    //   '#title' => $this->t('Locations In Person'),
    //   '#required' => FALSE,
    //   '#default_value' => isset($this->configuration['locations_in_person']) ? $this->configuration['locations_in_person'] : '',
    // ];
    // Joe add 17-10-2022

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function blockSubmit($form, FormStateInterface $form_state) {
    $this->configuration['id_event'] = $form_state->getValue('id_event');
    $this->configuration['subject'] = $form_state->getValue('subject');
    $this->configuration['date_start'] = $this->convertDateTimeToUTC($form_state->getValue('date_start'));
    $this->configuration['date_end'] = $this->convertDateTimeToUTC($form_state->getValue('date_end'));
    $this->configuration['details'] = $form_state->getValue('details');
    $this->configuration['locations'] = $form_state->getValue('locations');
    // Joe add 17-10-2022
    $this->configuration['id_event_in_person'] = $form_state->getValue('id_event_in_person');
    $this->configuration['subject_in_person'] = $form_state->getValue('subject_in_person');
    $this->configuration['date_start_in_person'] = $this->convertDateTimeToUTC($form_state->getValue('date_start_in_person'));
    $this->configuration['date_end_in_person'] = $this->convertDateTimeToUTC($form_state->getValue('date_end_in_person'));
    $this->configuration['details_in_person'] = $form_state->getValue('details_in_person');
    $this->configuration['locations_in_person'] = $form_state->getValue('locations_in_person');
    // Joe add 17-10-2022
  }

  public function convertDateTimeToUTC($datetime) {
    $system_date = \Drupal::config('system.date');
    $default_tz = $system_date->get('timezone.default') ? $system_date->get('timezone.default') : date_default_timezone_get();
    $convert_datetime = new \DateTime($datetime, new \DateTimeZone($default_tz));
    $convert_datetime->setTimeZone(new \DateTimeZone("UTC"));
    return $convert_datetime->format('Ymd\THis') . 'Z';
  }

}
