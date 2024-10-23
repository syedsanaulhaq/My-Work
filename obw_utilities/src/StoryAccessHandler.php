<?php
/**
 * Created by PhpStorm.
 * User: leopham
 * Date: 8/30/18
 * Time: 3:29 PM
 */

namespace Drupal\obw_utilities;

use Drupal\Core\Access\AccessResult;
use Drupal\Core\Session\AccountInterface;
use Drupal\node\Entity\Node;

/**
 * Builds an example page.
 */
class StoryAccessHandler {

  /**
   * Checks user can edit story
   *
   * @param \Drupal\Core\Session\AccountInterface $account
   *   Run access checks for this account.
   * @param null $node
   *
   * @return AccessResult
   */
  public function editAccess(AccountInterface $account, $node = NULL) {

    if (empty($node)) {
      /** @var Node $node */
      $node = \Drupal::routeMatch()->getParameter('node');
    }
    else {
      $node = Node::load($node);
    }

    if ('story' != $node->getType()) {
      return AccessResult::allowedIf($node->access('update', $account));
    }

    if ('draft' == $node->moderation_state->value) {
      return AccessResult::allowedIf(
        $account->hasPermission('use editorial transition create_new_draft') ||
        $account->hasPermission('use editorial transition need_review')
      );
    }
    return AccessResult::allowedIf(
      $account->hasPermission('use editorial transition back_to_need_review') ||
      $account->hasPermission('use editorial transition publish') ||
      $account->hasPermission('use editorial transition archived_draft')
    );
  }

}
