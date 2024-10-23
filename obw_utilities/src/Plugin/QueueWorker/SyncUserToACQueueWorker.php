<?php

namespace Drupal\obw_utilities\Plugin\QueueWorker;

use Drupal\Core\Queue\QueueWorkerBase;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class SyncUserToACQueueWorker
 * SyncUserToACQueueWorker
 *
 * @QueueWorker(
 *   id = "sync_user_to_ac_queue_worker",
 *   title = @Translation("Sync User To AC Queue Worker"),
 *   cron = {"time" = 180}
 * )
 */
class SyncUserToACQueueWorker extends QueueWorkerBase {

  /**
   * Constructs a new class instance.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin_id for the plugin instance.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   Entity type manager service.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition
    );
  }

  /**
   * {@inheritdoc}
   */
  public function processItem($item) {
    // Process Item.
    if ($item['request_method'] === 'POST') {
      $response = \Drupal::httpClient()
        ->post($item['request_url'], $item['request_options']);
      \Drupal::logger('sync_to_ac')
        ->info('Sync ' . $item['request_options']['form_params']['email'] . ' to AC successfully.');
    }
    else {
      $response = \Drupal::httpClient()
        ->get($item['request_url'], $item['request_options']);
    }
  }

}
