<?php

namespace Drupal\obw_utilities\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Configure example settings for this site.
 */
class ObwExportMissingStoryContributors extends ConfigFormBase {

  /**
   * Config settings.
   *
   * @var string
   */
  const SETTINGS = 'export.missing.story.contributors';

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'export_missing_story_contributors';
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return [
      static::SETTINGS,
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form = parent::buildForm($form, $form_state);
    $form['actions']['submit']['#value'] = $this->t('Export');
    $this->service()->clearDefaultFile();

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $count = 1;
    $nodes = $this->service()->getStoryMissingContributors();
    $operations = [];
    $filename = $this->service()->randomFileName();
    $this->service()->createDefaultLabelForFile($filename);
    foreach ($nodes as $node) {
      $operations[] = [
        //        [['\Drupal\obw_utilities\Form\ObwExportMissingStoryContributors', 'exportMissingStoryContributors']],
        '\Drupal\obw_utilities\StoryMissingContributors::exportMissingStoryContributors',
        [$node, $filename],
      ];
      $count++;
    }

    $batch = [
      'title' => t('Exporting Data...'),
      'operations' => $operations,
      'init_message' => t('Exporting is starting.'),
      'finished' => '\Drupal\obw_utilities\StoryMissingContributors::exportMissingStoryContributorsCallback',
      //      'finished' => [
      //        '\Drupal\obw_utilities\Form\ObwExportMissingStoryContributors',
      //        'exportMissingStoryContributorsCallback',
      //      ],
    ];

    batch_set($batch);
    $this->service()->forcedDownloadCSV($form_state, $filename);
  }

  protected function service() {
    return \Drupal::service('obw_utilities.missing_story_export');
  }


}
