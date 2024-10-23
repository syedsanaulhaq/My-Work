<?php

namespace Drupal\obw_utilities\Entity;

use Drupal\Core\Entity\EntityBase;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Render\Markup;
use Drupal\image\Entity\ImageStyle;
use Drupal\node\Entity\Node as BaseNode;
use Drupal\obw_utilities\Theme\PreprocessNodeManager;

class StoryEntity extends BaseNode {

  const MENTAL_HEALTH_2020_LANDING = '8';

  const ARTICLE_TYPE_BLOG = '2';

  const ARTICLE_TYPE_RESOURCE = '3';

  const WL_STORY_2020_FORMAT = '11';

  const WL_STORY_2021_FORMAT = '14';

  const WL_STORY_2022_FORMAT = '18';

  const ENVIRONMENTAL_STORY_2022 = '19';
  
  const OBW_10BIRTHDAY_YEAR = '20';

  const MENTAL_HEALTH_STORY_2022 = '22';

  const WOMEN_EMPOWER_SERIES = '25';

  const VIDEO_2_0 = '21';
  const VIDEO = '1';
  const TEXT = '2';

  /**
   * @return |null
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   */
  function getPublicProfileBlogAuthor() {
    $users = $this->hasField('field_story_blog_author') ? $this->get('field_story_blog_author')
      ->referencedEntities() : [];
    
    $user_type = $this->hasField('field_user_type');
    $user_info = NULL;
   
    if (isset($user_type) && !empty($user_type) && $this->get('field_user_type')->value == 'non__account__holder') {      
      $avt_non_account = $this->get('field_user_display_picture')->entity;   
      $user_avt_uri = $avt_non_account->uri->value;
      $image_style = ImageStyle::load('thumbnail');      
      $user_info['img_src'] = file_url_transform_relative($image_style->buildUrl($user_avt_uri));
      $user_info['name'] = $this->hasField('field_user_name') ? $this->get('field_user_name')->value : NULL;
      return $user_info;
    } 
    if (!$users) {
      return NULL;
    }
    $user = reset($users);  
    if ($user) {
      $public_profile = \Drupal::entityTypeManager()
        ->getStorage('profile')
        ->loadByProperties([
          'uid' => $user->id(),
          'type' => 'public_profile',
        ]);
      $public_profile = reset($public_profile);
      if ($public_profile) {
        $user_info['profile_url'] = $public_profile->toUrl()->toString();
        if (isset($public_profile->field_public_profile_bio) && !empty($public_profile->field_public_profile_bio->value)) {
          $user_info['bio'] = Markup::create($public_profile->get('field_public_profile_bio')->value);
        }
        $user_info['name'] = $public_profile->field_public_profile_name->value;
      }
      else {
        $first_name = $user->get('field_account_first_name')->value;
        /** @var \Drupal\Core\Field\EntityReferenceFieldItemListInterface $last_name */
        $last_name = $user->get('field_account_last_name')->value;
        $full_name = $first_name . ' ' . $last_name;
        $user_info['name'] = $full_name;
      }
      if (isset($user->user_picture) && !empty($user->user_picture->entity)) {
        $user_avt_uri = $user->user_picture->entity->getFileUri();
        $image_style = ImageStyle::load('thumbnail');
        $user_info['img_src'] = file_url_transform_relative($image_style->buildUrl($user_avt_uri));
      }
      return $user_info;
    }    
  }

  /**
   * @param $landing_node
   *
   * @return array
   */
  protected function getLandingSubStories($landing_node) {
    $story_type = ($this->hasField('field_story_mh2020_type') && !empty($this->get('field_story_mh2020_type')
        ->getString())) ? $this->get('field_story_mh2020_type')
      ->getString() : '1';

    if ($this->getRequestParamTypeOfMh2020()) {
      $story_type = $this->getRequestParamTypeOfMh2020();
    }

    switch ($story_type) {
      case 1: //Story
        $sub_stories = $landing_node->hasField('field_story_sub_stories') ? $landing_node->get('field_story_sub_stories')
          ->getValue() : [];
        break;
      case 2: //Blog
      case 'blog':
        if (isset($landing_node->field_landing_format)
          && !empty($landing_node->field_landing_format->value)
          && $landing_node->field_landing_format->value == self::MENTAL_HEALTH_2020_LANDING) {

          $spotlight_landings = $landing_node->hasField('field_spotlight_landing') ? $landing_node->get('field_spotlight_landing')
            ->referencedEntities() : [];
          $spotlight_landing = reset($spotlight_landings);
          $sub_stories = $spotlight_landing && $spotlight_landing->hasField('field_articles_spotlight_story') ? $spotlight_landing->get('field_articles_spotlight_story')
            ->getValue() : [];
          if ($landing_node->get('field_using_year_1_2_template')->value &&
            !empty($landing_node->get('field_using_year_1_2_template')->value)) {
            $sub_stories = $landing_node->hasField('field_landing_blogs') ? $landing_node->get('field_landing_blogs')
              ->getValue() : [];
          }
        }
        else {
          $sub_stories = $landing_node->hasField('field_landing_blogs') ? $landing_node->get('field_landing_blogs')
            ->getValue() : [];
        }
        break;
      case 3: //Resource
      case 'resource':
        $sub_stories = $landing_node->hasField('field_landing_resource_stories') ? $landing_node->get('field_landing_resource_stories')
          ->getValue() : [];
        break;
      default:
        $sub_stories = [];
        break;
    }

    return $sub_stories;
  }

  /**
   * @param bool $isNext
   *
   * @return bool|false|int|string
   */
  public function getNodeIDReferenced($isNext = TRUE) {
    //var_dump($this);
    $landing_nodes = $this->hasField('field_story_landing_node') ? $this->get('field_story_landing_node')
      ->referencedEntities() : [];
    if (!$landing_nodes) {
      return NULL;
    }

    $landing_node = reset($landing_nodes);
    $sub_stories = $this->getLandingSubStories($landing_node);

    if (!$sub_stories) {
      return NULL;
    }

    $nids = array_column($sub_stories, 'target_id');
    $nodes = \Drupal::entityTypeManager()
      ->getStorage('node')
      ->loadMultiple($nids);
    $nids_arr = [];
    foreach ($nodes as $nid => $node) {
      if ($node->field_story_embed_shorthand && !boolval($node->field_story_embed_shorthand->value)) {
        $nids_arr[] = $nid;
      }
    }

    $current_pos = array_search($this->id(), $nids_arr);
    $expected_nid = NULL;

    if ($current_pos || $current_pos === 0) {
      $expected_pos = $isNext ? $current_pos + 1 : $current_pos - 1;
      $expected_nid = isset($nids_arr[$expected_pos]) ? $nids_arr[$expected_pos] : NULL;
    }
    // var_dump($expected_nid);
    return $expected_nid;
  }

  public function getRequestParamTypeOfMh2020() {
    $request_type = FALSE;
    if (!empty(\Drupal::request()->query->get('type'))
      && in_array(\Drupal::request()->query->get('type'), [
        'blog',
        'resource',
      ])) {
      $request_type = \Drupal::request()->query->get('type');
    }
    return $request_type;
  }

  /**
   * @param $story_formats
   * @param int $limit
   * @param bool $is_resource
   *
   * @return mixed
   */
  protected function getExploringYears($story_formats, $is_resource = FALSE, $limit = 3) {
    $connection = \Drupal::database();

    // Do the magic here. We can just copy/paste the default sql_mode and just remove the "ONLY_FULL_GROUP_BY".
//    $connection->query("SET GLOBAL sql_mode=(SELECT REPLACE(@@sql_mode,'ONLY_FULL_GROUP_BY',''))")->execute();
    $query = $connection->select('node_field_data', 'node_fie ld_data');
    $query->leftjoin('node__field_story_format', 'story_format', 'node_field_data.nid = story_format.entity_id');
    $query->leftjoin('node__field_resource_article', 'story_resource', 'node_field_data.nid = story_resource.entity_id');
    $query->condition('story_format.field_story_format_value', $story_formats, 'IN');
    $query->condition('node_field_data.nid', $this->id(), '<>');
    $query->condition('node_field_data.type', 'story');
    $query->condition('node_field_data.status', 1);
    $query->addExpression('YEAR(FROM_UNIXTIME(node_field_data.published_at))', 'publish_year');
    $query->groupBy('publish_year');
    $query->orderBy('publish_year', 'DESC');
    $query->range(0, $limit);

    if ($is_resource) {
      $query->condition('story_resource.field_resource_article_value', 1);
    }
    else {
      $query->where('story_resource.field_resource_article_value IS NULL OR story_resource.field_resource_article_value = :is_article'
        , ['is_article' => 0]
      );
    }
    $response = [];


    foreach ($query->execute()->fetchAll() as $data) {
      $response[$data->publish_year] = $data->publish_year;
    }

    return $response;
  }

  /**
   * @param $story_formats
   * @param null $year
   * @param int $limit
   * @param bool $is_resource
   *
   * @return mixed
   */
  protected function getExploringByYear($story_formats, $is_resource = FALSE, $year = NULL, $limit = 3) {
    $connection = \Drupal::database();
    $current_node = \Drupal::entityTypeManager()
      ->getStorage('node')
      ->load($this->id());
    $wl_year = '2021';
    // if (!empty($current_node->field_story_format->getValue())
    //   && in_array(self::WL_STORY_2022_FORMAT, array_column($current_node->field_story_format->getValue(), 'value'))) {
    //   $wl_year = '2022';
    // }

    // if (!empty($current_node->field_story_format->getValue())
    // && in_array(self::ENVIRONMENTAL_STORY_2022, array_column($current_node->field_story_format->getValue(), 'value'))) {
    //   $wl_year = '2022';
    // }
    
    // if (!empty($current_node->field_story_format->getValue())
    // && in_array(self::OBW_10BIRTHDAY_YEAR, array_column($current_node->field_story_format->getValue(), 'value'))) {
    //   $wl_year = '2022';
    // }

    // if (!empty($current_node->field_story_format->getValue())
    // && in_array(self::VIDEO_2_0, array_column($current_node->field_story_format->getValue(), 'value'))) {
    //   $wl_year = '2022';
    // }
     if (!empty($current_node->field_story_format->getValue())
    && in_array(self::WOMEN_EMPOWER_SERIES, array_column($current_node->field_story_format->getValue(), 'value'))) {
      $wl_year = '2023';
    }
    
    $query = $connection->select('node_field_data', 'node_field_data');
    $query->leftjoin('node__field_story_format', 'story_format', 'node_field_data.nid = story_format.entity_id');
    $query->leftjoin('node__field_resource_article', 'story_resource', 'node_field_data.nid = story_resource.entity_id');
    $query->leftjoin('node__field_story_article_type', 'article_type', 'node_field_data.nid = article_type.entity_id');
    $query->fields('node_field_data', ['nid']);
    $query->condition('story_format.field_story_format_value', $story_formats, 'IN');
    $query->condition('node_field_data.type', 'story');
    $query->condition('node_field_data.status', 1);
    $query->orderBy('node_field_data.published_at', 'DESC');
    $query->range(0, $limit);
    if ($limit < 4) {
      $query->condition('node_field_data.nid', $this->id(), '<>');
    }

    if ($is_resource) {
      $query->condition('story_resource.field_resource_article_value', 1);
    }
    else {
      $query->where('story_resource.field_resource_article_value IS NULL OR story_resource.field_resource_article_value = :is_article'
        , ['is_article' => 0]
      );
    }

    if ($wl_year == '2022') {
      $query->condition('article_type.field_story_article_type_value', 'story');
    }

    if ($wl_year == '2023') {
      $query->condition('article_type.field_story_article_type_value', 'story');
    }

    if ($year) {
      $query->where('YEAR(FROM_UNIXTIME(node_field_data.published_at)) = :year', ['year' => $year]);
    }
    else {
      $query->addExpression('YEAR(FROM_UNIXTIME(node_field_data.published_at))', 'publish_year');
      $query->groupBy('publish_year');
      $query->groupBy('nid');
      $query->groupBy('node_field_data.published_at');
    }

    return $query->execute()->fetchAllAssoc('nid');
  }

  /**
   * @param bool $is_resource
   *
   * @return EntityBase[]|EntityInterface[]|null
   */
  public function getExploringPosts() {
    $story_formats = [
      self::WL_STORY_2020_FORMAT,
      self::WL_STORY_2021_FORMAT,
      self::ENVIRONMENTAL_STORY_2022,
      self::OBW_10BIRTHDAY_YEAR,
      self::VIDEO_2_0,
      self::MENTAL_HEALTH_STORY_2022,
      self::WOMEN_EMPOWER_SERIES,
    ];
    if (!empty($this->field_story_format->getValue())
      && in_array(self::WL_STORY_2022_FORMAT, array_column($this->field_story_format->getValue(), 'value'))) {
      $story_formats = [self::WL_STORY_2022_FORMAT];
    }
    if (!empty($this->field_story_format->getValue())
      && in_array(self::ENVIRONMENTAL_STORY_2022, array_column($this->field_story_format->getValue(), 'value'))) {
      $story_formats = [self::ENVIRONMENTAL_STORY_2022];      
    }
    if (!empty($this->field_story_format->getValue())
      && in_array(self::OBW_10BIRTHDAY_YEAR, array_column($this->field_story_format->getValue(), 'value'))) {
      $story_formats = [self::OBW_10BIRTHDAY_YEAR];      
    }
    if (!empty($this->field_story_format->getValue())
      && in_array(self::VIDEO_2_0, array_column($this->field_story_format->getValue(), 'value'))) {
      $story_formats = [self::VIDEO_2_0];      
    }
    if (!empty($this->field_story_format->getValue())
      && in_array(self::MENTAL_HEALTH_STORY_2022, array_column($this->field_story_format->getValue(), 'value'))) {
      
      $story_formats = [self::MENTAL_HEALTH_STORY_2022];      
    }
    if (!empty($this->field_story_format->getValue()) && in_array(self::WOMEN_EMPOWER_SERIES, array_column($this->field_story_format->getValue(), 'value'))) {    
      $story_formats = [self::WOMEN_EMPOWER_SERIES];      
    }
    $is_resource = $this->hasField('field_resource_article') ? $this->get('field_resource_article')
      ->getString() : 0;
    $latest_years = $this->getExploringYears($story_formats, $is_resource);

    $years = $latest_years ? array_keys($latest_years) : [];   

    switch (count($years)) {
      case 0:
        return NULL;
      case 1:
        $query_result = $this->getExploringByYear($story_formats, $is_resource, $years[0]);
        break;
      case 2:
        $query1 = $this->getExploringByYear($story_formats, $is_resource, $years[0], 2);
        $query2_limit = count($query1) < 2 ? 2 : 1;
        $query2 = $this->getExploringByYear($story_formats, $is_resource, $years[1], $query2_limit);
        $query_result = $query1 + $query2;
        break;
      case 3:
        $query_result = [];
        foreach ($years as $year) {
          $query_result += $this->getExploringByYear($story_formats, $is_resource, $year, 1);
        }
        break;
      default:
        $query_result = $this->getExploringByYear($story_formats, $is_resource);
        break;
    }

    if ($query_result) {
      $nids = array_keys($query_result);
      $exploring_stories = BaseNode::loadMultiple($nids);
    }
    else {
      $exploring_stories = NULL;
    }

    return $exploring_stories;
  }

  /**
   * @param $year
   * @param $limit
   *
   * @return EntityBase[]|EntityInterface[]|null
   */
  public function getExploringStoriesByYear($year, $limit) {
    $story_formats = [
      self::WL_STORY_2020_FORMAT,
      self::WL_STORY_2021_FORMAT,
    ];
    if (!empty($this->field_story_format->getValue())
      && in_array(self::WL_STORY_2022_FORMAT, array_column($this->field_story_format->getValue(), 'value'))) {
      $story_formats = [self::WL_STORY_2022_FORMAT];
    }
    $is_resource = $this->hasField('field_resource_article') ? $this->get('field_resource_article')
      ->getString() : 0;
    $query_result = $this->getExploringByYear($story_formats, $is_resource, $year, $limit);

    if ($query_result) {
      $nids = array_keys($query_result);
      $exploring_stories = BaseNode::loadMultiple($nids);
    }
    else {
      $exploring_stories = NULL;
    }
    return $exploring_stories;
  }

  /**
   * Get Wildlife story status
   *
   * @param bool $returnYear
   *
   * @return string[]
   */
  public function getWLStatus($returnYear = FALSE) {
    $published_at = $this->hasField('published_at') ? $this->get('published_at')
      ->getString() : NULL;
    $year = \Drupal::service('date.formatter')->format(
      $published_at, 'custom', 'Y'
    );

    if ($returnYear) {
      return $year;
    }

    switch ($year) {
      case '2020':
        $status = [
          'class' => 'sea-turtle',
          'text' => t('Hunting and Trade'),
          'description' => t('Wildlife trade is a billion dollar industry but wiping out a species is a huge price to pay. These stories feature the inspiring efforts taken to save animals at risk.'),
        ];
        break;
      case '2021':
        $status = [
          'class' => 'lemon',
          'text' => t('Human-Wildlife Conflict'),
          'description' => t('The loss of forested areas has put wildlife and humans in greater contact but conservationists have come up with simple yet effective ways for us to live in harmony.'),
        ];
        break;
      case '2022':
        $status = [
          'class' => 'purple',
          'text' => t('Community Conservation'),
          'description' => t('While there are individuals working to protect wildlife, when a community comes together to help, it creates a better world where both wildlife and people can thrive.'),
        ];
        break;
      default:
        $status = ['class' => '', 'text' => ''];
        break;
    }

    return $status;
  }

  /**
   * Get estimation of reading time
   *
   * @return string
   * @see PreprocessNodeManager::getReadingTimeNode
   */
  public function getReadingTime() {
    $moduleHandler = \Drupal::service('module_handler');
    if (isset($this->field_story_override_read_time) && !empty($this->field_story_override_read_time->value)) {
      $reading_time = ' ' . $this->field_story_override_read_time->value;
    }
    elseif ($moduleHandler->moduleExists('node_read_time')) {
      $words_per_minute = \Drupal::config('node_read_time.settings')
        ->get('reading_time')['words_per_minute'] ?: 225;
      $reading_time_service = \Drupal::service('reading_time');
      $reading_time = $reading_time_service
        ->setWordsPerMinute($words_per_minute)
        ->collectWords($this)
        ->calculateReadingTime()
        ->getReadingTime();

      // Clear the words variable.
      $reading_time_service->setWords(0);
      return ' ' . str_replace(' minute', '-minute', str_replace('minutes', 'minute', $reading_time)) . ' read';
    }
  }

  public function getArticleType() {
    $type = 'story';
    $format = PreprocessNodeManager::getNodeFormat('story');
    if (!empty($this->field_story_format->getValue())) {
      $story_types = array_column($this->field_story_format->getValue(), 'value');
      foreach ($story_types as $s_type) {
        if ('community_blog' == $format[$s_type]) {
          $type = 'blog';
          break;
        }
        elseif ($this->hasField('field_story_mh2020_type') && !empty($this->field_story_mh2020_type->value)) {
          switch ($this->field_story_mh2020_type->value) {
            case self::ARTICLE_TYPE_BLOG:
              $type = 'blog';
              break;
            case self::ARTICLE_TYPE_RESOURCE:
              $type = 'resource';
              break;
          }
        }
      }
    }
    return $type;
  }

  /**
   * @return src  
   */  
  public function getImageStory($nid) {      
    $node_storage = \Drupal::entityTypeManager()->getStorage('node');
    $node = $node_storage->load($nid);
    $url = file_create_url($node->field_story_thumbnail->entity->getFileUri());  
    return $url;
  }
  
  /** Joe Added **/
  public function getURLYoutubeLinkStory($nid) {      
    $node_storage = \Drupal::entityTypeManager()->getStorage('node');
    $node = $node_storage->load($nid);
    $url = file_create_url($node->field_story_feature_media->entity->field_media_video_embed_field->value);  
    return $url;
  }
  /**
   * @return array
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   */
  public function getBlogsInArticleList() {
    $query = \Drupal::database()->select('node_field_data', 'nfd');
    $query->addField('nfd', 'nid');
    $query->leftjoin('node__field_story_format', 'story_format', 'nfd.nid = story_format.entity_id');
    $query->leftjoin('node__field_story_article_type', 'article_type', 'nfd.nid = article_type.entity_id');

    if ($this->hasField('field_story_format') && !empty($this->field_story_format->getString())) {
      $field_story_format = explode(',', $this->field_story_format->getString());

      if (in_array(self::WL_STORY_2022_FORMAT, $field_story_format) && in_array(self::WL_STORY_2021_FORMAT, $field_story_format)) {
        \Drupal::logger('obw_utitlites')
          ->info('The story ' . $this->id() . ' has 2 format: wl-2021, wl-2022');
      }

      if (in_array(self::WL_STORY_2021_FORMAT, $field_story_format)) {
        $query->condition('story_format.field_story_format_value', self::WL_STORY_2021_FORMAT, '=');
      }
      elseif (in_array(self::WL_STORY_2022_FORMAT, $field_story_format)) {
        $query->condition('story_format.field_story_format_value', self::WL_STORY_2022_FORMAT, '=');
      }
      elseif (in_array(self::ENVIRONMENTAL_STORY_2022, $field_story_format)) {
        $query->condition('story_format.field_story_format_value', self::ENVIRONMENTAL_STORY_2022, '=');
      }
      elseif (in_array(self::OBW_10BIRTHDAY_YEAR, $field_story_format)) {
        $query->condition('story_format.field_story_format_value', self::OBW_10BIRTHDAY_YEAR, '=');
      }
      elseif (in_array(self::VIDEO_2_0, $field_story_format)) {
        $query->condition('story_format.field_story_format_value', self::VIDEO_2_0, '=');
      }
      elseif (in_array(self::MENTAL_HEALTH_STORY_2022, $field_story_format)) {
        $query->condition('story_format.field_story_format_value', self::MENTAL_HEALTH_STORY_2022, '=');
      }
      elseif (in_array(self::WOMEN_EMPOWER_SERIES, $field_story_format)) {
        $query->condition('story_format.field_story_format_value', self::WOMEN_EMPOWER_SERIES, '=');
      }
    }

    $query->condition('article_type.field_story_article_type_value', 'blog', '=');
    $query->condition('nfd.nid', $this->id(), '!=');
    $query->condition('nfd.type', 'story');
    $query->condition('nfd.status', 1);
    $query->orderBy('nfd.published_at', 'DESC');
    $query->range(0, 3);
    $blogs = $query->execute()->fetchAllAssoc('nid');

    $nids_arr = [];
    foreach ($blogs as $nid => $node) {
      $nids_arr[] = $nid;
    }

    $exploring_stories = NULL;
    if (!empty($nids_arr)) {
      $exploring_stories = BaseNode::loadMultiple($nids_arr);
    }
    return $exploring_stories;
  }

  /**
   * @return array
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   */
  public function getResourcesArticle() {
    $query = \Drupal::database()->select('node_field_data', 'nfd');
    $query->addField('nfd', 'nid');
    $query->leftjoin('node__field_story_format', 'story_format', 'nfd.nid = story_format.entity_id');
    $query->leftjoin('node__field_story_article_type', 'article_type', 'nfd.nid = article_type.entity_id');

    if ($this->hasField('field_story_format') && !empty($this->field_story_format->getString())) {
      $field_story_format = explode(',', $this->field_story_format->getString());

      if (in_array(self::WL_STORY_2022_FORMAT, $field_story_format) && in_array(self::WL_STORY_2021_FORMAT, $field_story_format)) {
        \Drupal::logger('obw_utitlites')
          ->info('The story ' . $this->id() . ' has 2 format: wl-2021, wl-2022');
      }

      if (in_array(self::WL_STORY_2021_FORMAT, $field_story_format)) {
        $query->condition('story_format.field_story_format_value', self::WL_STORY_2021_FORMAT, '=');
      }
      elseif (in_array(self::WL_STORY_2022_FORMAT, $field_story_format)) {
        $query->condition('story_format.field_story_format_value', self::WL_STORY_2022_FORMAT, '=');
      }
    }

    $query->condition('article_type.field_story_article_type_value', 'resource', '=');
    $query->condition('nfd.nid', $this->id(), '!=');
    $query->condition('nfd.type', 'story');
    $query->condition('nfd.status', 1);
    $query->orderBy('nfd.published_at', 'DESC');
    $query->range(0, 3);
    $blogs = $query->execute()->fetchAllAssoc('nid');

    $nids_arr = [];
    foreach ($blogs as $nid => $node) {
      $nids_arr[] = $nid;
    }

    $exploring_stories = NULL;
    if (!empty($nids_arr)) {
      $exploring_stories = BaseNode::loadMultiple($nids_arr);
    }
    return $exploring_stories;
  }

  //OBW 5386
  protected function getExploringVideoStoryByYear($story_formats, $is_resource = FALSE, $year = NULL, $limit = 3) {
    $connection = \Drupal::database();
    $current_node = \Drupal::entityTypeManager()
      ->getStorage('node')
      ->load($this->id());
    $wl_year = '2021';

    //obw 5386
      //chuyển value field_story_format thành 1 mảng đơn
      $story_formats = array_column($this->field_story_format->getValue(), 'value');
      $s_value = [];
      $is_none_series = true;
      foreach ($story_formats as $key => $value) {
        if($value == self::VIDEO || $value == self::TEXT || $value == self::VIDEO_2_0){
        }else {
          array_push($s_value,$value);
          $is_none_series = false;
        }
      }    
    //end obw 5386
    // $is_mental_health_2022 = in_array("22", array_column($this->field_story_format->getValue(), 'value'));
    // if($is_mental_health_2022){
    //   $limit = 4;
    // }
    $query = $connection->select('node_field_data', 'node_field_data');
    $query->leftjoin('node__field_story_format', 'story_format', 'node_field_data.nid = story_format.entity_id');
    $query->leftjoin('node__field_resource_article', 'story_resource', 'node_field_data.nid = story_resource.entity_id');
    $query->leftjoin('node__field_story_article_type', 'article_type', 'node_field_data.nid = article_type.entity_id');

    $query->fields('node_field_data', ['nid']);


    $query->condition('node_field_data.type', 'story');
    $query->condition('node_field_data.status', 1);
    $query->orderBy('node_field_data.published_at', 'DESC');
    $query->range(0, $limit);
    if ($limit < 4) {
      $query->condition('node_field_data.nid', $this->id(), '<>');
    }

    if ($is_resource) {
      $query->condition('story_resource.field_resource_article_value', 1);
    }
    else {
      $query->where('story_resource.field_resource_article_value IS NULL OR story_resource.field_resource_article_value = :is_article'
        , ['is_article' => 0]
      );
    }

    if ($wl_year == '2022') {
      $query->condition('article_type.field_story_article_type_value', 'story');
    }

    if ($year) {
      $query->where('YEAR(FROM_UNIXTIME(node_field_data.published_at)) = :year', ['year' => $year]);
    }
    else {
      $query->addExpression('YEAR(FROM_UNIXTIME(node_field_data.published_at))', 'publish_year');
      $query->groupBy('publish_year');
      $query->groupBy('nid');
      $query->groupBy('node_field_data.published_at');
    }

    //OBW 5386
    if($is_none_series === true){
      $story_formats_arr = [
        self::MENTAL_HEALTH_2020_LANDING,
        self::WL_STORY_2020_FORMAT,
        self::WL_STORY_2021_FORMAT,
        self::WL_STORY_2022_FORMAT,
        self::ENVIRONMENTAL_STORY_2022,
        self::OBW_10BIRTHDAY_YEAR,
        self::MENTAL_HEALTH_STORY_2022,
      ];
      
      $subquery = $connection->select('node_field_data', 'node_field_data');
      $subquery->leftjoin('node__field_story_format', 'story_format', 'node_field_data.nid = story_format.entity_id');
      $subquery->fields('node_field_data', ['nid']);
      $subquery->condition('story_format.field_story_format_value', $story_formats_arr,'IN');
      $subquery->condition('node_field_data.nid', $this->id(), '<>');
      $subquery->where('YEAR(FROM_UNIXTIME(node_field_data.published_at)) = :year', ['year' => $year]);

      $query->condition('story_format.entity_id', $subquery, 'NOT IN');
      $query->condition('story_format.field_story_format_value', 21);

    }else{
      $query->condition('story_format.field_story_format_value', $s_value,'IN');
    }    
    //END OBW 5386

    return $query->execute()->fetchAllAssoc('nid');
  }


  public function getVideo2RelatedPost() {
    $story_formats = [
      self::WL_STORY_2020_FORMAT,
      self::WL_STORY_2021_FORMAT,
      self::ENVIRONMENTAL_STORY_2022,
      self::OBW_10BIRTHDAY_YEAR,
      self::VIDEO_2_0,
      self::VIDEO,
      self::MENTAL_HEALTH_STORY_2022,
    ];
    if (!empty($this->field_story_format->getValue())
      && in_array(self::WL_STORY_2022_FORMAT, array_column($this->field_story_format->getValue(), 'value'))) {
      $story_formats = [self::WL_STORY_2022_FORMAT];
    }
    if (!empty($this->field_story_format->getValue())
      && in_array(self::ENVIRONMENTAL_STORY_2022, array_column($this->field_story_format->getValue(), 'value'))) {
      $story_formats = [self::ENVIRONMENTAL_STORY_2022];      
    }
    if (!empty($this->field_story_format->getValue())
      && in_array(self::OBW_10BIRTHDAY_YEAR, array_column($this->field_story_format->getValue(), 'value'))) {
      $story_formats = [self::OBW_10BIRTHDAY_YEAR];      
    }
    if (!empty($this->field_story_format->getValue())
      && in_array(self::VIDEO_2_0, array_column($this->field_story_format->getValue(), 'value'))) {
      $story_formats = [self::VIDEO_2_0,self::VIDEO];
    }
    if (!empty($this->field_story_format->getValue())
      && in_array(self::MENTAL_HEALTH_STORY_2022, array_column($this->field_story_format->getValue(), 'value'))) {
      $story_formats = [self::MENTAL_HEALTH_STORY_2022];
    }
    
    $is_resource = $this->hasField('field_resource_article') ? $this->get('field_resource_article')
      ->getString() : 0;
    $latest_years = $this->getExploringYears($story_formats, $is_resource);

    $years = $latest_years ? array_keys($latest_years) : [];


    switch (count($years)) {
      case 0:
        return NULL;
      case 1:
        $query_result = $this->getExploringVideoStoryByYear($story_formats, $is_resource, $years[0]);
        break;
      case 2:
        $query1 = $this->getExploringVideoStoryByYear($story_formats, $is_resource, $years[0], 2);
        $query2_limit = count($query1) < 2 ? 2 : 1;
        $query2 = $this->getExploringVideoStoryByYear($story_formats, $is_resource, $years[1], $query2_limit);
        $query_result = $query1 + $query2;
        break;
      case 3:
        $query_result = $this->getExploringVideoStoryByYear($story_formats, $is_resource, $years[0]);
        break;
      default:
        $query_result = $this->getExploringVideoStoryByYear($story_formats, $is_resource);
        break;
    }
    $query_result = $this->getExploringVideoStoryByYear($story_formats, $is_resource, $years[0]);

    if ($query_result) {
      $nids = array_keys($query_result);
      $exploring_stories = BaseNode::loadMultiple($nids);
    }
    else {
      $exploring_stories = NULL;
    }

    return $exploring_stories;
  }  
  //END 5386

}
