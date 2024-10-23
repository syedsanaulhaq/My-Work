<?php


namespace Drupal\obw_utilities\Controller;


use Drupal\Core\Controller\ControllerBase;
use Drupal\node\Entity\Node;
use Symfony\Component\HttpFoundation\JsonResponse;

class StoryController extends ControllerBase
{


  const WL_STORY_2022_FORMAT = '18';

  /**
   * Get Quick Poll result
   *
   * @param null $resource
   * @param null $type
   *
   * @return JsonResponse
   */
  public function getSuggestedStories($node_id, $year = NULL)
  {
    /**
     * @var \Drupal\obw_utilities\Entity\StoryEntity $story
     */
    $story = Node::load($node_id);
    if (!$story) {
      return new JsonResponse([
        'status' => 'failed',
        'message' => t('Node ID is invalid'),
      ]);
    }

    if (!empty($story->field_story_format->getValue())
      && in_array(self::WL_STORY_2022_FORMAT, array_column($story->field_story_format->getValue(), 'value'))) {
      $story_format = 'wl_2022';
    }

    switch ($year) {
      case 2020:
      case 2021:
      case 2022:
        $exploring_stories = $story->getExploringStoriesByYear($year, PHP_INT_MAX);
        break;
      default:
        $exploring_stories = $story->getVideo2RelatedPost();
        break;
    }

    try {
      if (!empty($exploring_stories[$node_id]) && count($exploring_stories) > 1) {
        //moving the current reading story to the end
        $tmp = $exploring_stories[$node_id];
        unset($exploring_stories[$node_id]);
        $exploring_stories[$node_id] = $tmp;
      }
      $twig = \Drupal::service('twig');
      $template = $twig->loadTemplate(drupal_get_path('theme', 'obw_theme') . '/templates/includes/story/exploring-stories.html.twig');
      $result_html = $template->render([
        'related_posts' => $exploring_stories,
        'node' => $story,
        'format' => $story_format ?? ''
      ]);

      return new JsonResponse(['status' => 'success', 'html' => $result_html]);
    } catch (\Exception $e) {
      \Drupal::logger('exploring_stories')->critical($e->getMessage());
      return new JsonResponse([
        'status' => 'failed',
        'message' => t('Something went wrong, please try again'),
      ]);
    }
  }

}
