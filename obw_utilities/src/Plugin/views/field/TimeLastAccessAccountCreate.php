<?php
/**
 * @file
 * Definition of
 *   Drupal\obw_utilities\Plugin\views\field\TimeLastAccessAccountCreate
 */

namespace Drupal\obw_utilities\Plugin\views\field;

use Drupal\views\Plugin\views\field\FieldPluginBase;
use Drupal\views\ResultRow;

/**
 * Field handler to flag the node type.
 *
 * @ingroup views_field_handlers
 *
 * @ViewsField("time_last_access_account_created")
 */
class TimeLastAccessAccountCreate extends FieldPluginBase {

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
    $user_row = user_load_by_mail($node->getData()['email']);
    if (intval($user_row->access->value) == 0) {
      return $this->t('Unverified Account');
    }
    elseif (intval($user_row->access->value) > 0) {
      return $this->time_Ago($user_row->created->value, $user_row->access->value);
    }
  }

  public function time_Ago($created, $last_access) {
    // Formulate the Difference between two dates
    $diff = abs($last_access - $created);

    $days = floor($diff / (60 * 60 * 24));

    $hours = floor(($diff - $days * 60 * 60 * 24) / (60 * 60));
    $str_days = $days > 1 ? $days . ' days' : $days . ' day';
    $str_hours = $hours > 1 ? $hours . ' hours' : $hours . ' hour';
    return "$str_days $str_hours";
  }

}
