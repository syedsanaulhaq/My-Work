<?php

namespace Drupal\obw_utilities\Element;

use Drupal\Core\Form\FormStateInterface;
use Drupal\file\Element\ManagedFile;

/**
 * @FormElement("managed_image")
 */
class ManagedImage extends ManagedFile {

  /**
   * {@inheritdoc}
   */
  public function getInfo() {
    $info = parent::getInfo();
    $info['#accept'] = 'image/*';
    $info['#image_style'] = 'thumbnail';

    return $info;
  }

  /**
   * {@inheritdoc}
   */
  public static function processManagedFile(&$element, FormStateInterface $form_state, &$complete_form) {
    /** @var \Drupal\Core\Image\ImageFactory $image_factory */
    $image_factory = \Drupal::service('image.factory');
    $element['#upload_validators'] += [
      'file_validate_is_image' => [],
      'file_validate_extensions' => [implode(' ', $image_factory->getSupportedExtensions())],
    ];

    return parent::processManagedFile($element, $form_state, $complete_form);
  }

  /**
   * {@inheritdoc}
   */
  public static function preRenderManagedFile($element) {
    $element = parent::preRenderManagedFile($element);

    if (!empty($element['#files'])) {
      foreach ($element['#files'] as $delta => $file) {
        /** @var \Drupal\file\Entity\File $file */
        $element['file_' . $delta] = [
          'preview' => [
            '#type' => 'container',
            '#weight' => -10,
            '#attributes' => [
              'class' => [
                'image-preview',
                'avatar',
              ],
            ],
            'image' => [
              '#theme' => 'image_style',
              '#style_name' => $element['#image_style'],
              '#uri' => $file->getFileUri(),
            ],
          ],
        ];// + $element['file_' . $delta];
      }

      $element['#attributes']['class'][] = 'file-selected';
    }
    return $element;
  }
}
