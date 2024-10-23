<?php

namespace Drupal\obw_utilities\EventSubscriber;

use Drupal\Core\Routing\RouteSubscriberBase;
use Symfony\Component\Routing\RouteCollection;


class RouteSubscriber extends RouteSubscriberBase {

  /**
   * {@inheritdoc}
   */
  protected function alterRoutes(RouteCollection $collection) {

    if ($route = $collection->get('entity.node.edit_form')) {
      $route->setRequirements(['_custom_access' => '\Drupal\obw_utilities\StoryAccessHandler::editAccess']);
    }

    if ($route = $collection->get('profile.user_page.single')) {
      $route->setOption('_admin_route', TRUE);
    }
  }

}
