<?php
/**
 * @file
 * Definition of
 *   Drupal\obw_utilities\Plugin\views\field\GetTotalOfClicksOnANode
 */

namespace Drupal\obw_utilities\Plugin\views\field;

use Drupal\views\Plugin\views\field\FieldPluginBase;
use Drupal\views\ResultRow;

/**
 * Field handler to flag the node type.
 *
 * @ingroup views_field_handlers
 *
 * @ViewsField("get_total_of_clicks_node")
 */
class GetTotalOfClicksOnANode extends FieldPluginBase {

  /**
   * @{inheritdoc}
   */
  public function query() {
    // Leave empty to avoid a query on this field.
  }


  /**
   * @{inheritdoc}
   */
  public function render(ResultRow $values) {
    $node = $values->_entity;
    if ($node->getType() == 'call_to_action') {
      /** @var $user_action_data \Drupal\obw_actions\UserActionCtaData */
      $user_action_data = \Drupal::service('user_action.cta_data');
      $user_action_data->setCtaId($node->id());
      $total = $user_action_data->getTotalClick();

      if (in_array('administrator', \Drupal::currentUser()->getRoles())) {
        $action_service = \Drupal::service('action_entity.action_service');

        $params_data = $action_service->getParamsAction($node);
        if (!empty($params_data)) {
          $action_entity_id = $action_service->getActionEntityId($params_data, 'click');
          if ($action_entity_id) {
            return \Drupal\Core\Render\Markup::create('<a href="/admin/structure/action_status/' . $action_entity_id . '/edit" target="_blank">' . $total . '</a>');
          }
        }
      }
      return $total;
    }
    return 0;
  }

}
