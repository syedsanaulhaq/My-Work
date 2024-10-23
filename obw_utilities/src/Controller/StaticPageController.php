<?php

namespace Drupal\obw_utilities\Controller;

use Drupal\Core\Controller\ControllerBase;

/**
 * Class StaticPageController.
 */
class StaticPageController extends ControllerBase {

  /**
   * Temporary use this action to develop for static page.
   *
   * @return string[]
   *   Return Hello string.
   */
  public function pitchStoriesPage() {

    //@TODO find another solution to migrate content between dev environment
    return [
      '#theme' => 'static_page_pitch_stories',
    ];
  }

  public function createStories() {
    return [
      '#theme' => 'static_page_create_stories',
    ];
  }

  public function partnerUs() {
    return [
      '#theme' => 'static_page_partner_us',
    ];
  }

  public function obw345() {
    return [
      '#theme' => 'static_page_obw_345',
    ];
  }

  public function obw347() {
    return [
      '#theme' => 'static_page_obw_345',
    ];
  }

  public function obw330() {
    return [
      '#theme' => 'static_page_obw_345',
    ];
  }

}
