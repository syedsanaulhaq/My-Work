<?php


namespace Drupal\obw_utilities\Controller;


use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Entity\EntityInterface;
use Symfony\Component\HttpFoundation\JsonResponse;


class CommunityBlogController extends ControllerBase {

  public function addCommunityBlogForm() {
    $node = $this->entityTypeManager()->getStorage('node')->create([
      'type' => 'story',
    ]);

    if ($node instanceof EntityInterface) {
      return $this->entityFormBuilder()->getForm($node, 'story_new_blog_post');
    }
    return new JsonResponse([
      'status' => 'failed',
      'html' => 'content type story not found',
    ]);
  }


}
