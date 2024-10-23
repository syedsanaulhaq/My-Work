<?php

namespace Drupal\obw_utilities\Plugin\Block;

use Drupal;
use Drupal\Core\Block\BlockBase;
use Drupal\Core\Url;

/**
 * Provides a 'OBW Forum Block' Block.
 *
 * @Block(
 *   id = "obw_forum_block",
 *   admin_label = @Translation("OBW Forum Block"),
 *   category = @Translation("OBW Forum Block"),
 * )
 */
class OBWForumBlock extends BlockBase {

  /**
   * {@inheritdoc}
   */
  public function build() {
    $current_url = Url::fromRoute('<current>')->toString();
    $node_path = Drupal::service('path_alias.manager')
      ->getPathByAlias($current_url);
    if (preg_match('/node\/(\d+)/', $node_path, $matches)) {
      $nid = $matches[1];
      $etm = Drupal::entityTypeManager();
      $node = $etm->getStorage('node')->load($nid);
      $node_type = $node->getType();
      $forum_info = [];
      if ($node_type == 'landing_page') {
        $this->loadForumBlock($node, 'field_landing_forum_block', $forum_info);
      }
      else {
        if ($node_type == 'story') {
          $this->loadForumBlock($node, 'field_story_forum_block', $forum_info);
        }
      }

    }
    return [
      '#theme' => 'obw_forum_block',
      '#forum_desc_html' => !empty($forum_info['forum_desc_html']) ? $forum_info['forum_desc_html'] : NULL,
      '#list_topics' => !empty($forum_info['list_topics']) ? $forum_info['list_topics'] : NULL,
      '#create_topic_html' => !empty($forum_info['create_topic_html']) ? $forum_info['create_topic_html'] : NULL,
      '#msg_no_topic' => !empty($forum_info['msg_no_topic']) ? $forum_info['msg_no_topic'] : NULL,
      '#dont_show_forum' => !empty($forum_info['dont_show_forum']) ? $forum_info['dont_show_forum'] : NULL,
    ];
  }

  //  public function getCacheMaxAge()
  //  {
  //    return 0;
  //  }

  private function loadForumBlock($node, $field_name, &$forum_info) {
    if (isset($node->$field_name) && !empty($node->$field_name->getValue())) {
      //      $forum_info['#attached']['library'][] = 'obw_theme/forum-style';
      //      $variables['#attached']['library'][] = 'obw_theme/mental-health-campaign';
      $para_forum_block = $node->$field_name->referencedEntities()[0];
      if (isset($para_forum_block->field_para_forum_description) && !empty($para_forum_block->field_para_forum_description->value)) {
        $forum_info['forum_desc_html'] = $para_forum_block->field_para_forum_description->value;
      }

      if (isset($para_forum_block->field_para_forum_sync_to_forum) && !empty($para_forum_block->field_para_forum_sync_to_forum->value) && $para_forum_block->field_para_forum_sync_to_forum->value === '1') {
        if (isset($para_forum_block->field_para_forum_api_get_topic) && !empty($para_forum_block->field_para_forum_api_get_topic->value)) {
          $api_get_topic = $para_forum_block->field_para_forum_api_get_topic->value;
          $action_service = Drupal::service('action_entity.action_service');
          $api_topic_response = $action_service->cUrlGet($api_get_topic, [], []);
          $list_topics = [];
          if (!empty($api_topic_response) && !empty($api_topic_response['payload']) && !empty($api_topic_response['payload']['showTopics'])) {
            $domain_forum = 'http://stg.ourbetterworld.org/forum';
            if (isset($para_forum_block->field_para_forum_domain) && !empty($para_forum_block->field_para_forum_domain->value)) {
              $domain_forum = $para_forum_block->field_para_forum_domain->value;
            }
            foreach ($api_topic_response['payload']['showTopics'] as $topic_response) {
              $topic = [
                'tid' => $topic_response['tid'],
                'title' => $topic_response['title'],
                'votes' => $topic_response['votes'],
                'replies' => $topic_response['postcount'] > 0 ? $topic_response['postcount'] - 1 : 0,
                'url' => $domain_forum . '/topic/' . $topic_response['tid'],
              ];
              $list_topics[] = $topic;
            }
          }
          if (!empty($list_topics)) {
            $forum_info['list_topics'] = $list_topics;
          }
          else {
            if (isset($para_forum_block->field_para_forum_msg_no_topic) && !empty($para_forum_block->field_para_forum_msg_no_topic->value)) {
              $forum_info['msg_no_topic'] = $para_forum_block->field_para_forum_msg_no_topic->value;
            }
          }
        }
      }

      if (isset($para_forum_block->field_para_forum_create_topic) && !empty($para_forum_block->field_para_forum_create_topic->value)) {
        $forum_info['create_topic_html'] = $para_forum_block->field_para_forum_create_topic->value;
      }
    }
    else {
      $forum_info['dont_show_forum'] = TRUE;
    }
  }

}
