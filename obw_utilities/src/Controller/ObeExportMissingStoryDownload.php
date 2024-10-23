<?php

namespace Drupal\obw_utilities\Controller;

use Drupal\Core\Controller\ControllerBase;

/**
 * Controller routines for file downloadssss.
 */
class ObeExportMissingStoryDownload {

  /**
   * Download file.
   */
  public function index($filename) {
    $host = \Drupal::request()->getSchemeAndHttpHost();
    $related_path = '/sites/default/files/story-missing-contributors/';
    $file_url = $host . $related_path . $filename;

    $build['#attached']['html_head'][] = [
      [
        '#tag' => 'meta',
        '#attributes' => [
          'http-equiv' => 'refresh',
          'content' => '0; url=' . $file_url,
        ],
      ],
      'obw_story_missing_download',
    ];
    return $build;

  }


}
