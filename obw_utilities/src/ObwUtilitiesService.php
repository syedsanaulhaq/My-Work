<?php

namespace Drupal\obw_utilities;

use Drupal\Core\Url;
use Drupal\file\Entity\File;
use Drupal\image\Entity\ImageStyle;
use Drupal\node\Entity\Node;

/**
 *
 */
class ObwUtilitiesService {

  /**
   *
   */
  public function isContentTypePage($type, $current_path = '') {
    if (empty($current_path)) {
      $current_path = \Drupal::service('path.current')->getPath();
    }

    if (preg_match('/node\/(\d+)/', $current_path, $matches)) {
      $node = Node::load($matches[1]);
      if ($node->getType() == $type) {
        return $node;
      }
    }
    return FALSE;
  }

  /**
   *
   */
  public function getShapeTypeBirthday2021($width, $height) {
    $aspect = $width / $height;
    if ($aspect > 1.5) {
      return 'horizontal_rectangle';
    }
    elseif ($aspect < 0.8) {
      return 'vertical_rectangle';
    }
    return 'square';
  }

  /**
   *
   */
  public function cleanUrl($str) {
    $clean_url = preg_replace('/\-+/', '-', strtolower(preg_replace('/[^a-zA-Z0-9_-]+/', '', str_replace(' ', '-', $str))));
    return $clean_url;
  }

  /**
   *
   */
  public function getFileUrlFromFileId($file_id) {
    $file_url = '';
    $file = File::load($file_id);
    if ($file) {
      $file_url = file_url_transform_relative(file_create_url($file->getFileUri()));
    }
    return $file_url;
  }

  /**
   *
   */
  public function randomShapeStypeForPostBirthday2021(&$ws_data) {
    $shapes_args =
      [
        'shape1',
        'shape2',
        'shape3',
        'shape4',
        'shape5',
        'shape6',
        'shape7',
        'shape8',
        'shape9',
        'shape10',
        'shape11',
        'shape12',
        'shape13',
        'shape14',
        'shape15',
      ];
    if ($ws_data['image_upload']) {
      $file_load = File::load($ws_data['image_upload']);
      if (is_array(getimagesize($file_load->getFileUri()))) {
        $width = getimagesize($file_load->getFileUri())[0];
        $height = getimagesize($file_load->getFileUri())[1];
        $ws_data['image_type'] = $this->getShapeTypeBirthday2021($width, $height);
        if ($ws_data['image_type'] !== 'square') {
          unset($shapes_args[14]);
        }
      }
    }
    $shapes_args_rand = array_rand($shapes_args);
    $ws_data['shapes'] = $shapes_args[$shapes_args_rand];
  }

  public function getImgUrlByImageStyleForFileEntity($file_id, $image_styles, $shape_type = '') {
    $img_url = '';
    $file = File::load($file_id);
    if ($file) {
      if ($shape_type && !empty($image_styles[$shape_type])) {
        $img_url = self::buildImageStyle($file->getFileUri(), $image_styles[$shape_type]);
      }
      else {
        $img_url = self::buildImageStyle($file->getFileUri(), $image_styles);
      }
    }
    return $img_url;
  }

  public function buildImageStyle($thumbnail, $image_style) {
    $image_style = ImageStyle::load($image_style);
    return file_url_transform_relative($image_style->buildUrl($thumbnail));
  }

  public function getValueFormField($entity, $field_name, $field_type) {
    $field_values = [];
    if ($entity->hasField($field_name) && !empty($entity->get($field_name)
        ->getValue())) {
      switch ($field_type) {
        case 'image':
          /* @var \Drupal\file\FileInterface $img_entity */
          $img_entity = $entity->get($field_name)->entity;
          $field_values = [
            'url' => $img_entity->createFileUrl(),
            'alt' => !empty($entity->get($field_name)
              ->getValue()[0]['alt']) ? $entity->get($field_name)
              ->getValue()[0]['alt'] : '',
          ];
          break;
        case 'link':
          $link_field = $entity->get($field_name)->getValue()[0];
          $field_values = [
            'title' => $link_field['title'] ?? '',
            'target' => $link_field['options']['attributes']['target'] ?? '_self',
            'url' => !empty($link_field['uri']) ? Url::fromUri($link_field['uri'])
              ->toString() : '',
          ];
          break;
      }

    }
    return $field_values;
  }

}
