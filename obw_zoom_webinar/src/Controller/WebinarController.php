<?php

namespace Drupal\obw_zoom_webinar\Controller;

use Drupal\Core\Controller\ControllerBase;

/**
 * Class WebinarController.
 */
class WebinarController extends ControllerBase {

  /**
   * Webinar.
   *
   * @return string
   *   Return Hello string.
   */
  public function webinar() {
    return [
      '#theme' => 'obw_zoom_webinar',
    ];
  }

  /**
   * Webinar.
   *
   * @return string
   *   Return Hello string.
   */
  public function join() {
    return [
      '#theme' => 'obw_zoom_join',
    ];
  }

}
