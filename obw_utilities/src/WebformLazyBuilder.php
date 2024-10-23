<?php

namespace Drupal\obw_utilities;

use Drupal\Core\Render\Element\RenderCallbackInterface;

class WebformLazyBuilder implements RenderCallbackInterface {

  /**
   * {@inheritdoc}
   */
  public static function buildWebform($webform)
  {
    return  [
      'webform' => [
        '#type' => 'webform',
        '#webform' => $webform,
      ],
    ];
  }

}
