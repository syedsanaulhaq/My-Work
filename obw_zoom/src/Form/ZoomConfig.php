<?php

/**
 * @file
 * Contains \Drupal\obw_zoom\Form\ZoomConfig.
 */

namespace Drupal\obw_zoom\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Class obwConfig.
 *
 * @package \Drupal\obw_zoom\Form
 *
 *
 */
class ZoomConfig extends ConfigFormBase {

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return [
      'obw_zoom.config',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'obw_zoom_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form = parent::buildForm($form, $form_state);
    $config = $this->config('obw_zoom.config');

    
    $form['obw_zoom_jwt'] = [
      '#markup' => '<h2>JWT</h2>',      
    ];
    $form['obw_zoom_api_key'] = [
      '#type' => 'textfield',
      '#title' => t('API Key'),
      //'#required' => TRUE,
      '#default_value' => $config->get('obw_zoom_api_key'),
    ];
    $form['obw_zoom_secret_key'] = [
      '#type' => 'textfield',
      '#title' => t('Secret Key'),
      //'#required' => TRUE,
      '#default_value' => $config->get('obw_zoom_secret_key'),
    ];
    $form['obw_zoom_mn'] = [
      '#type' => 'textfield',
      '#title' => t('Meeting/Webinar Number'),
      //'#required' => TRUE,
      '#default_value' => $config->get('obw_zoom_mn'),
    ];
    $form['obw_zoom_pwd'] = [
      '#type' => 'textfield',
      '#title' => t('Meeting/Webinar Password'),
      //'#required' => TRUE,
      '#default_value' => $config->get('obw_zoom_pwd'),
    ];

    // $form['obw_zoom_webinar_mn'] = [
    //   '#type' => 'textfield',
    //   '#title' => t('Zoom webinar Number'),
    //   //'#required' => TRUE,
    //   '#default_value' => $config->get('obw_zoom_webinar_mn'),
    // ];
    // $form['obw_zoom_webinar_pwd'] = [
    //   '#type' => 'textfield',
    //   '#title' => t('Zoom webinar Password'),
    //   //'#required' => TRUE,
    //   '#default_value' => $config->get('obw_zoom_webinar_pwd'),
    // ];


    $form['obw_zoom_roles'] = [
      '#type' => 'select',
      '#title' => t('Zoom Roles'),
      '#options' => ['Attendee', 'Host', 'Assistant'],
      '#default_value' => $config->get('obw_zoom_roles'),
    ];
    $form['obw_zoom_area'] = [
      '#type' => 'select',
      '#title' => t('Zoom Area'),
      '#options' => ['Global', 'China'],
      '#default_value' => $config->get('obw_zoom_area'),
    ];
    $form['obw_zoom_signature'] = [
      '#type' => 'textfield',
      '#title' => t('Signature'),
      '#disabled' => TRUE,
      '#default_value' => $config->get('obw_zoom_signature'),
    ];
    // $form['obw_zoom_webinar_signature'] = [
    //   '#type' => 'textfield',
    //   '#title' => t('Zoom Webinar Signature'),
    //   '#disabled' => TRUE,
    //   '#default_value' => $config->get('obw_zoom_webinar_signature'),
    // ];
    $form['obw_zoom_lang'] = [
      '#type' => 'select',
      '#title' => t('Zoom Languages'),
      '#options' => [
        "en-US" => "English",
        "de-DE" => "German Deutsch",
        "es-ES" => "Spanish Español",
        "fr-FR" => "French Français",
        "jp-JP" => "Japanese 日本語",
        "pt-PT" => "Portuguese Portuguese",
        "ru-RU" => "Russian Русский",
        "zh-CN" => "Chinese 简体中文",
        "zh-TW" => "Chinese 繁体中文",
        "ko-KO" => "Korean 한국어",
        "vi-VN" => "Vietnamese Tiếng Việt",
        "it-IT" => "Italian italiano",
      ],
      '#default_value' => $config->get('obw_zoom_lang'),
    ];   
    // $form['discord_server'] = [
    //   '#type' => 'textfield',
    //   '#title' => t('Discord server'),      
    //   '#default_value' => $config->get('discord_server'),
    // ];
    // $form['discord_channel'] = [
    //   '#type' => 'textfield',
    //   '#title' => t('Discord channel'),      
    //   '#default_value' => $config->get('discord_channel'),
    // ];

    // $form['obw_zoom_webinar'] = [
    //   '#markup' => '<h2>Webform Register</h2>',      
    // ];
    
    $form['obw_zoom_sdk_meeting_registrants_id'] = [
      '#type' => 'textfield',
      '#title' => t('Zoom meeting registrants ID'),
      //'#required' => TRUE,
      '#default_value' => $config->get('obw_zoom_sdk_meeting_registrants_id'),
    ];    

    $form['obw_zoom_sdk_webinar_registrants_id'] = [
      '#type' => 'textfield',
      '#title' => t('Zoom webinar registrants ID'),
      //'#required' => TRUE,
      '#default_value' => $config->get('obw_zoom_sdk_webinar_registrants_id'),
    ]; 
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    //
  }
 
  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $this->config('obw_zoom.config')
      ->set('obw_zoom_api_key', $form_state->getValue('obw_zoom_api_key'))
      ->set('obw_zoom_secret_key', $form_state->getValue('obw_zoom_secret_key'))
      ->set('obw_zoom_mn', $form_state->getValue('obw_zoom_mn'))
      ->set('obw_zoom_roles', $form_state->getValue('obw_zoom_roles'))
      ->set('obw_zoom_area', $form_state->getValue('obw_zoom_area'))
      ->set('obw_zoom_pwd', $form_state->getValue('obw_zoom_pwd'))
      ->set('obw_zoom_lang', $form_state->getValue('obw_zoom_lang'))

      // ->set('obw_zoom_webinar_pwd', $form_state->getValue('obw_zoom_webinar_pwd'))
      // ->set('obw_zoom_webinar_mn', $form_state->getValue('obw_zoom_webinar_mn'))     
      // ->set('discord_server', $form_state->getValue('discord_server'))
      // ->set('discord_channel', $form_state->getValue('discord_channel'))
     
      ->set('obw_zoom_sdk_meeting_registrants_id', $form_state->getValue('obw_zoom_sdk_meeting_registrants_id'))
      ->set('obw_zoom_sdk_webinar_registrants_id', $form_state->getValue('obw_zoom_sdk_webinar_registrants_id'))  
      //->set('obw_zoom_webinar_signature', $this->generateSignature($form_state->getValue('obw_zoom_api_key'), $form_state->getValue('obw_zoom_secret_key'), $form_state->getValue('obw_zoom_webinar_mn'), $form_state->getValue('obw_zoom_roles')))
      ->set('obw_zoom_signature', $this->generateSignature($form_state->getValue('obw_zoom_api_key'), $form_state->getValue('obw_zoom_secret_key'), $form_state->getValue('obw_zoom_mn'), $form_state->getValue('obw_zoom_roles')))
      ->save();

    parent::submitForm($form, $form_state);
  }

  protected function generateSignature($api_key, $api_secret, $meeting_number, $role) {
    //Set the timezone to UTC
    date_default_timezone_set("UTC");

    $time = time() * 1000 - 30000;//time in milliseconds (or close enough)

    $data = base64_encode($api_key . $meeting_number . $time . $role);

    $hash = hash_hmac('sha256', $data, $api_secret, TRUE);

    $_sig = $api_key . "." . $meeting_number . "." . $time . "." . $role . "." . base64_encode($hash);

    //return signature, url safe base64 encoded
    return rtrim(strtr(base64_encode($_sig), '+/', '-_'), '=');
  }

}

