<?php
/**
 * Created by PhpStorm.
 * User: leopham
 * Date: 8/30/18
 * Time: 6:34 PM
 */

namespace Drupal\obw_utilities;


use Drupal\media\Entity\Media;
use Drupal\obw_utilities\Theme\PreprocessNodeManager;

class StoryManager {

  public static function setThumbnailByVideo($entity) {

    $arr_node = $entity->toArray();
    if (!empty($arr_node)) {
      if (!isset($arr_node['field_story_feature_media'][0]['target_id'])) {
        return;
      }
      $fid_media = $arr_node['field_story_feature_media'][0]['target_id'];
      $media = Media::load($fid_media);

      if (is_null($media)) {
        return;
      }
      // Image
      if ($media->hasField('field_media_image')) {

        $media_field = $media->get('field_media_image')->first()->getValue();
        $fid = $media_field['target_id'];
      }
      // Video embedded field
      elseif ($media->hasField('field_media_video_embed_field')) {

        try {

          // TODO: Need update
          $media_type = \Drupal::entityTypeManager()
            ->getStorage('media_type')
            ->load('embedded_media');
          if (!is_null($media_type)) {

            $uri = $media_type->getSource()
              ->getMetadata($media, 'thumbnail_uri');
            $files = \Drupal::entityTypeManager()
              ->getStorage('file')
              ->loadByProperties(['uri' => $uri]);
            $fid = key($files);
          }
          else {
            \Drupal::logger('Story')
              ->error('Thumnail video: can\'t load video_embed_field');
          }
        } catch (\Exception $ex) {
          \Drupal::logger('Story')
            ->error('Thumnail video: ' . $ex->getMessage());
        }
      }

      if (isset($fid)) {

        // TODO: NEED CONFIRM TEXT For Alt, Title
        $thumbnail = [
          'target_id' => $fid,
          'alt' => $entity->label(),
          'title' => $entity->label(),
        ];
        $entity->set('field_story_thumbnail', $thumbnail);
      }
    }
  }

  public static function setHeroImage(&$node) {

    $arr_node = $node->toArray();
    if (!empty($arr_node)
      && !empty($arr_node['field_story_thumbnail'])
      && !empty($arr_node['field_story_thumbnail'][0]['target_id'])) {

      $media = isset($arr_node['field_story_feature_media'][0]['target_id']) ? Media::load($arr_node['field_story_feature_media'][0]['target_id']) : FALSE;
      if (!$media) {
        //set hero media
        // check first if the media exists for the title
        $image_media = \Drupal::entityTypeManager()
          ->getStorage('media')
          ->loadByProperties([
              'bundle' => 'image',
              'field_media_image' => [
                'target_id' => $arr_node['field_story_thumbnail'][0]['target_id'],
              ],
            ]
          );

        $image_story = $image_media ? reset($image_media) : FALSE;

        if (!$image_story) {

          // Create media entity with saved file.
          $image_story = Media::create([
            'bundle' => 'image',
            'uid' => \Drupal::currentUser()->id(),
            'langcode' => \Drupal::languageManager()
              ->getDefaultLanguage()
              ->getId(),
            'status' => 1, //published
            'field_media_image' => $arr_node['field_story_thumbnail'][0],
            'thumbnail' => $arr_node['field_story_thumbnail'][0],
          ]);
          $image_story->save();
        }
        $node->set('field_story_feature_media', []);
        $node->field_story_feature_media[] = ['target_id' => $image_story->id()];

        return;
      }
      // Already have image
      if ($media->hasField('field_media_image')) {
        $media->set('field_media_image', $arr_node['field_story_thumbnail'][0]);
        $media->set('thumbnail', $arr_node['field_story_thumbnail'][0]);
        $media->save();
      }
      elseif ($media->hasField('field_media_video_embed_field')) {
        $media->set('thumbnail', $arr_node['field_story_thumbnail'][0]);
        $media->save();
      }
    }
  }

  public static function calculateTheVideoDurationAutomatically(&$node) {
    if (isset($node->field_story_calculate_video_auto) && !empty($node->field_story_calculate_video_auto->value)
      && $node->field_story_calculate_video_auto->value == 1) {
      $len_body = PreprocessNodeManager::getLengthMarkupField($node, 'body');
      if ($len_body < 1500) {
        if ($yt_id = PreprocessNodeManager::getYoutubeId($node, 'field_story_feature_media')) {
          $obw_youtube_config = \Drupal::config('obw_utilities.youtube.config');
          if ($obw_youtube_config->get('OBW_YOUTUBE_API_LINK') && $obw_youtube_config->get('OBW_YOUTUBE_API_KEY')) {
            $yt_response = file_get_contents($obw_youtube_config->get('OBW_YOUTUBE_API_LINK') . "?part=contentDetails&id=$yt_id&key=" . $obw_youtube_config->get('OBW_YOUTUBE_API_KEY'));
            $yt_response_decode = json_decode($yt_response, TRUE);
            if (!empty($yt_response_decode['items'][0]['contentDetails']['duration'])) {
              $video_duration = $yt_response_decode['items'][0]['contentDetails']['duration'];
              if (preg_match_all('/(\d+)M(\d+)S/', $video_duration, $parts)) {
                $minutes = $parts[1][0];
                if ($parts[2][0] > 30) {
                  $minutes += 1;
                }
                $duration_text = $minutes . '-minute view';
                $node->set('field_story_override_read_time', $duration_text);
              }
            }
          }
        }
      }
    }
  }

}
