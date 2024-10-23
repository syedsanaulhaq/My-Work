<?php

namespace Drupal\obw_utilities\Plugin\WebformHandler;

use Drupal\webform\Plugin\WebformHandler\RemotePostWebformHandler;

/**
 * Webform submission remote post handler.
 *
 * @WebformHandler(
 *   id = "subscribe_weekly",
 *   label = @Translation("Subscribe Weekly"),
 *   category = @Translation("OBW Custom"),
 *   description = @Translation("Sync profile to list 44 in AC"),
 *   cardinality =
 *   \Drupal\webform\Plugin\WebformHandlerInterface::CARDINALITY_UNLIMITED,
 *   results =
 *   \Drupal\webform\Plugin\WebformHandlerInterface::RESULTS_PROCESSED,
 *   submission =
 *   \Drupal\webform\Plugin\WebformHandlerInterface::SUBMISSION_OPTIONAL,
 *   tokens = TRUE,
 * )
 */
class SubscribeWeeklyWebformHandler extends RemotePostWebformHandler {

  public function defaultConfiguration() {
    $default_config = parent::defaultConfiguration();
    $default_config['completed_url'] = 'https://ourbetterworld.api-us1.com/admin/api.php?api_action=contact_sync&api_key=343190acd6b26fedc65841f6e0d6b20284bb783c60c8de88c3b5c1d985c1d3199663fb60&api_output=json';
    $default_config['custom_data'] = "p[44]: 44 \r\nstatus[44]: 1";
    return $default_config;
  }


}
