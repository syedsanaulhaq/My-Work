<?php

namespace Drupal\obw_utilities;

use Drupal\Core\Field\Plugin\Field\FieldType\EntityReferenceItem;
use Drupal\Core\Session\AccountInterface;
use Drupal\field\Entity\FieldConfig;
use Drupal\file\Plugin\Field\FieldType\FileFieldItemList;
use Drupal\image\Entity\ImageStyle;
use Drupal\image\Plugin\Field\FieldType\ImageItem;
use Drupal\media\Entity\Media;

/**
 * Class ResponsiveImage.
 */
class ResponsiveImage {

  private $currentUser;

  /**
   * Constructs a new ResponsiveImage object.
   *
   * @param \Drupal\Core\Session\AccountInterface $currentUser
   */
  public function __construct(AccountInterface $currentUser) {
    $this->currentUser = $currentUser;
  }


  /**
   * Returns a a Drupal user as an owner.
   */
  public function whoIsYourOwner() {
    return $this->currentUser->getDisplayName();
  }

  public function imageStyle($path, $style) {
    if (is_array($path)) {
      $content = $path['content'];
      if (key_exists('#item', $content)) {
        $item = $content['#item'];
        if ($item instanceof ImageItem) {
          $path = $item->entity->getFileUri();
        }
      }
      else {
        if (key_exists('#media', $content)) {
          $media = $content['#media'];
          if ($media instanceof Media) {
            $path = $this->imageFromMedia($media)->entity->getFileUri();
          }
        }
      }
    }
    // Normal Drupal images
    elseif ($path instanceof ImageItem) {
      $path = $path->entity->getFileUri();
    }
    // Media images
    elseif ($path instanceof EntityReferenceItem) {
      $media = $path->entity;
      if ($media instanceof Media) {
        $path = $this->imageFromMedia($media)->entity->getFileUri();
      }
    }

    /** @var \Drupal\Image\ImageStyleInterface $image_style */
    if ($image_style = ImageStyle::load($style)) {
      return file_url_transform_relative($image_style->buildUrl($path));

    }
  }

  public function responsiveImageStyle($path, $style, $class_attr = []) {
    /** @var ImageItem $imageItem */

    $imageItem = NULL;
    if (is_array($path)) {
      $content = $path['content'];
      if (key_exists('#item', $content)) {
        $imageItem = $content['#item'];
      }
      else {
        if (key_exists('#media', $content)) {
          $media = $content['#media'];
          if ($media instanceof Media) {
            $imageItem = $this->imageFromMedia($media)->get(0);
          }
        }
      }
    }

    // Normal Drupal images
    elseif ($path instanceof ImageItem) {
      $imageItem = $path;
    }

    // Media images
    elseif ($path instanceof EntityReferenceItem) {
      $media = $path->entity;
      if ($media instanceof Media) {
        $imageItem = $this->imageFromMedia($media)->get(0);
      }
    }
    elseif ($path instanceof FileFieldItemList) {
      $imageItem = $path;
    }

    $file = $imageItem->entity;

    if (!is_null($file)) {
      // The image.factory service will check if our image is valid.
      /** @var \Drupal\Core\Image\Image $image */
      $image = \Drupal::service('image.factory')->get($file->getFileUri());

      // Check if image is valid and can be used.
      if ($image->isValid()) {

        //$item_attributes = $imageItem->attributes;

        // Set up the render array.
        $image_build = [
          '#theme' => 'responsive_image',
          '#width' => $image->getWidth(),
          '#height' => $image->getHeight(),
          '#responsive_image_style_id' => $style,
          '#uri' => $file->getFileUri(),
          '#attributes' => [
            'alt' => $imageItem->alt,
            'title' => $imageItem->title,
            'class' => $class_attr,
          ],
        ];

        // Cache logic.
        $renderer = \Drupal::service('renderer');
        $renderer->addCacheableDependency($image_build, $file);

        // Render image.
        return render($image_build);
      }
    }
  }

  private function imageFromMedia(Media $media) {
    // In case there is a custom Media bundle defined,
    // we search for an image inside it and return that
    foreach ($media->getFieldDefinitions() as $fieldDefinition) {
      if ($fieldDefinition instanceof FieldConfig) {
        if ($fieldDefinition->getType() == "image") {
          return $media->get($fieldDefinition->getName());
        }
      }
    }
    return NULL;
  }

}
