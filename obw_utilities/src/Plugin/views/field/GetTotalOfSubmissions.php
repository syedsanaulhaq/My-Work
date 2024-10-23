<?php
/**
 * @file
 * Definition of
 *   Drupal\obw_utilities\Plugin\views\field\GetTotalOfSubmissions
 */

namespace Drupal\obw_utilities\Plugin\views\field;

use Drupal\Core\Render\Markup;
use Drupal\views\Plugin\views\field\FieldPluginBase;
use Drupal\views\ResultRow;

/**
 * Field handler to flag the node type.
 *
 * @ingroup views_field_handlers
 *
 * @ViewsField("get_total_of_submissions")
 */
class GetTotalOfSubmissions extends FieldPluginBase {

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
      if (!empty($node->field_cta_form_detail->getValue())) {
        /** @var $user_action_data \Drupal\obw_actions\UserActionCtaData */
        $user_action_data = \Drupal::service('user_action.cta_data');
        $user_action_data->setCtaId($node->id());
        $total = $user_action_data->getTotalSubmission();
        return Markup::create('<a href="/admin/structure/webform/manage/' . $node->field_cta_form_detail->target_id . '/results/submissions" target="_blank">' . $total . '</a>');
      }

      return 'NULL';
    }
    return 'NULL';
  }

}
