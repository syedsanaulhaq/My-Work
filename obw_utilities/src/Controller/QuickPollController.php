<?php


namespace Drupal\obw_utilities\Controller;


use Drupal\Core\Controller\ControllerBase;
use PDO;
use Symfony\Component\HttpFoundation\JsonResponse;

class QuickPollController extends ControllerBase {

  /**
   * Get Quick Poll result
   *
   * @return JsonResponse
   */
  public function getResult($wf_id = NULL) {
    if ($wf_id == NULL) {
      \Drupal::logger('obw_mh_quick_poll')->critical('$wf_id is null!');
      return new JsonResponse([
        'status' => 'failed',
        'message' => t('Something went wrong, please try again'),
      ]);
    }
    $webform = \Drupal::entityTypeManager()
      ->getStorage('webform')
      ->load($wf_id);

    if (!$webform) {
      \Drupal::logger('obw_mh_quick_poll')
        ->critical("Webform id $wf_id is not found!");
      return new JsonResponse([
        'status' => 'failed',
        'message' => t('Something went wrong, please try again'),
      ]);
    }

    $question = $webform->getElement('question');
    $options = $question && isset($question['#options']) ? $question['#options'] : [];
    $connection = \Drupal::database();
    $query = $connection->select('webform_submission_data', 'webform_submission_data');
    $query->fields('webform_submission_data', ['value']);
    $query->addExpression('COUNT(*)', 'count');
    $query->groupBy('webform_id');
    $query->groupBy('name');
    $query->groupBy('value');

    $query->where('webform_submission_data.webform_id = :webform_id AND webform_submission_data.name = :name', [
      'webform_id' => $wf_id,
      'name' => 'question',
    ]);

    try {
      $options_data = $query->execute()
        ->fetchAllAssoc('value', PDO::FETCH_ASSOC);
      $result = $this->convertOptionsValue($options, $options_data);
      return new JsonResponse(['status' => 'success', 'data' => $result]);
    } catch (\Exception $e) {
      \Drupal::logger('obw_mh_quick_poll')->critical($e->getMessage());
      return new JsonResponse([
        'status' => 'failed',
        'message' => t('Something went wrong, please try again'),
      ]);
    }
  }

  /**
   * @param $options
   * @param $options_data
   *
   * @return array
   */
  private function convertOptionsValue($options, $options_data) {
    $result = [];

    if (!is_array($options) || !is_array($options_data)) {
      return $result;
    }

    foreach ($options as $key => $option) {
      if (isset($options_data[$key])) {
        $result[] = $options_data[$key];
      }
      else {
        $result[] = ['value' => $option, 'count' => 0];
      }
    }

    return $result;
  }

}
