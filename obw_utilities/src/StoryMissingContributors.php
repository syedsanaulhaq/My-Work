<?php

namespace Drupal\obw_utilities;

use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;
use Drupal\node\Entity\Node;
use Drupal\paragraphs\Entity\Paragraph;
use Drupal\taxonomy\Entity\Term;
use Drupal\user\Entity\User;

/**
 * Class StoryMissingContributors.
 */
class StoryMissingContributors {

  public static function exportMissingStoryContributors($node, $filename, &$context) {
    $context['sandbox']['current_item'] = $node;
    $data = [];
    if ($node->hasField('field_contributors')) {
      $paragraph_contributors = $node->get('field_contributors');
      if (!empty($p_ids = $paragraph_contributors->getValue())) {
        foreach ($p_ids as $pid) {
          $p_data = Paragraph::load($pid['target_id']);
          $p_contributor = $p_data->get('field_paragraph_user')->getValue();
          $p_roles = $p_data->get('field_story_roles')->getValue();
          if (empty($p_contributor) || empty($p_roles)) {
            $message = "Exporting: " . $node->id();
            $data[$node->id()]['id'] = $node->id();
            $data[$node->id()]['title'] = $node->label();
            $data[$node->id()]['url'] = $node->toUrl()->toString();
            $data[$node->id()]['user'] = empty($p_contributor) ? '' : User::load($p_contributor[0]['target_id'])
              ->getAccountName();
            if (empty($p_roles)) {
              $data[$node->id()]['roles'] = '';
            }
            else {
              $roles = Term::loadMultiple($p_roles);
              $roles_name = [];
              foreach ($roles as $role) {
                $roles_name[] = $role->getName();
              }
              $data[$node->id()]['roles'] = implode(',', $roles_name);
            }
            $data[$node->id()]['created_on'] = date('d-m-Y', $node->getCreatedTime());
            $context['message'] = $message;
            $context['results'][] = $node;
            self::makeDir($data, $filename);
          }
        }
      }
    }

  }


  public static function getStoryMissingContributors() {
    $query = \Drupal::entityQuery('node')->condition('type', 'story');
    $results = $query->execute();

    return Node::loadMultiple($results);
  }

  public static function exportMissingStoryContributorsCallback($success, $results, $operations) {
    if ($success) {
      $message = \Drupal::translation()->formatPlural(
        count($results),
        'One item processed.', '@count items processed.'
      );
    }
    else {
      $message = t('Finished with an error.');
    }
    $messenger = \Drupal::messenger();
    $messenger->addMessage($message);
  }

  public static function makeDir($data_format, $filename) {
    $path = DRUPAL_ROOT . '/sites/default/files/story-missing-contributors';
    if (!is_dir($path)) {
      \Drupal::service('file_system')->mkdir($path);
      self::putDataCSV($data_format, $path, $filename);
    }
    else {
      self::putDataCSV($data_format, $path, $filename);
    }
  }

  /**
   * Function put array data to csv.
   */
  public static function putDataCSV($data_format, $path, $filename) {
    $file_path = $path . '/' . $filename;
    $f = fopen($file_path, "a+");
    fprintf($f, chr(0xEF) . chr(0xBB) . chr(0xBF));
    foreach ($data_format as $line) {
      if ($line && is_array($line)) {
        fputcsv($f, $line);
      }
      else {
        \Drupal::logger('debug')->info($line);
      }
    }
    fclose($f);

  }

  public static function forcedDownloadCSV(FormStateInterface $form_state, $filename) {
    $path = self::getAbsolutePath();
    if (file_exists($path . '/' . $filename)) {
      $url = Url::fromRoute('obw_utilities.export_missing_story_contributors_download', ['filename' => $filename], ['absolute' => TRUE]);
      $form_state->setRedirectUrl($url);
    }
  }

  public static function clearDefaultFile() {
    $files = glob(self::getAbsolutePath() . '/*');
    foreach ($files as $file) {
      if (is_file($file)) {
        unlink($file);
      }
    }
  }

  public static function randomFileName($length = 10) {
    $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $charactersLength = strlen($characters);
    $randomString = '';
    for ($i = 0; $i < $length; $i++) {
      $randomString .= $characters[rand(0, $charactersLength - 1)];
    }
    return 'story-missing-contributors-' . date('d-m-Y-') . $randomString . '.csv';
  }

  public static function getAbsolutePath() {
    return DRUPAL_ROOT . '/sites/default/files/story-missing-contributors';
  }

  public static function createDefaultLabelForFile($filename) {
    $data_label = [
      [
        'ID',
        'Story Title',
        'Story URL',
        'User',
        'Roles',
        'Created On',
      ],
    ];
    self::makeDir($data_label, $filename);
  }

}
