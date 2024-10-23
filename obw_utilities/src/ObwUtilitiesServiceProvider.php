<?php
/**
 * Created by PhpStorm.
 * User: Admin
 * Date: 6/23/2020
 * Time: 4:59 PM
 */

namespace Drupal\obw_utilities;

use Drupal\Core\DependencyInjection\ContainerBuilder;
use Drupal\Core\DependencyInjection\ServiceModifierInterface;
use Drupal\Core\DependencyInjection\ServiceProviderBase;

class ObwUtilitiesServiceProvider extends ServiceProviderBase implements ServiceModifierInterface {

  /**
   * override service Drupal\social_auth\User\UserAuthenticator
   *
   * @param ContainerBuilder $container
   */
  public function alter(ContainerBuilder $container) {
    if ($container->hasDefinition('asset.css.collection_renderer')) {
      $definition = $container->getDefinition('asset.css.collection_renderer');
      $definition->setClass('Drupal\obw_utilities\Asset\CssCollectionRenderer');
    }
    if ($container->hasDefinition('asset.js.collection_renderer')) {
      $definition = $container->getDefinition('asset.js.collection_renderer');
      $definition->setClass('Drupal\obw_utilities\Asset\JsCollectionRenderer');
    }

  }

}
