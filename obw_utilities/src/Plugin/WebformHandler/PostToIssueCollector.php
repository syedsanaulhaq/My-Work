<?php

namespace Drupal\obw_utilities\Plugin\WebformHandler;

use CURLFile;
use Drupal;
use Drupal\file\Entity\File;
use Drupal\webform\Plugin\WebformHandlerBase;
use Drupal\webform\WebformSubmissionInterface;

/**
 * Form submission handler.
 *
 * Handlers after the submit.
 *
 * @WebformHandler(
 *   id = "post_to_issue_collector",
 *   label = @Translation("Post to issue collector"),
 *   category = @Translation("OBW Custom"),
 *   description = @Translation("Post issue to the issue collector after the
 *   submission is created"), cardinality =
 *       \Drupal\webform\Plugin\WebformHandlerInterface::CARDINALITY_SINGLE,
 *   results =
 *    \Drupal\webform\Plugin\WebformHandlerInterface::RESULTS_PROCESSED,
 * )
 */
class PostToIssueCollector extends WebformHandlerBase {

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    return [];
  }

  /**
   * {@inheritdoc}
   */
  public function getSummary() {
    // Broken/missing webform handlers do not need a summary.
    return [];
  }

  public function postSave(WebformSubmissionInterface $webform_submission, $update = TRUE) {
    $webform = $webform_submission->getWebform();
    $webform_data = $webform_submission->getData();
    $webform_attribute = $webform->getElementsDecoded()['#attributes'];
    $authorization = !empty($webform_attribute["authorization"]) ? $webform_attribute["authorization"] : "";
    if (!empty($authorization)) {
      $authorization_base64 = 'Basic ' . base64_encode($authorization);
      $action_service = Drupal::service('action_entity.action_service');
      $url = 'https://ourbetterworld.atlassian.net/rest/api/3/issue';
      $headers = [
        'Content-Type:application/json',
        'Authorization:' . $authorization_base64,
      ];

      $description = !empty($webform_data["description"]) ? $webform_data["description"] : "";
      $email = !empty($webform_data["email"]) ? $webform_data["email"] : "";
      $name = !empty($webform_data["first_name"]) ? $webform_data["first_name"] : "";

      $id = !empty($webform_attribute["issuetype_id"]) ? $webform_attribute["issuetype_id"] : "";
      $key = !empty($webform_attribute["project_key"]) ? $webform_attribute["project_key"] : "";
      $label = !empty($webform_attribute["labels"]) ? $webform_attribute["labels"] : "";
      $env = "";
      if (!empty($webform_data['location'])) {
        $env .= "Location: " . $webform_data['location'];
      }
      if (!empty($webform_data['referrer'])) {
        $env .= "\nReferrer: " . $webform_data['referrer'];
      }
      if (!empty($webform_data['user_agent'])) {
        $env .= "\nUser-Agent: " . $webform_data['user_agent'];
      }

      $jsonData = [
        "fields" => [
          "summary" => $email . " reported an issue",
          "issuetype" => [
            "id" => $id,
          ],
          "project" => [
            "key" => $key,
          ],
          "description" => [
            "type" => "doc",
            "version" => 1,
            "content" => [
              [
                "type" => "paragraph",
                "content" => [
                  [
                    "text" => $description . ". \n\nEmail: " . $email . ". \nName: " . $name,
                    "type" => "text",
                  ],
                ],
              ],
            ],
          ],
          "environment" => [
            "type" => "doc",
            "version" => 1,
            "content" => [
              [
                "type" => "paragraph",
                "content" => [
                  [
                    "text" => $env,
                    "type" => "text",
                  ],
                ],
              ],
            ],
          ],
          "labels" => [$label],
        ],
      ];

      $response = $action_service->cUrlPost($url, $jsonData, $headers);
      Drupal::logger('post_to_issue_collector')
        ->info('Response: ' . json_encode($response));
      if (!empty($response) && !empty($response['key'])) {
        $attach_url = 'https://ourbetterworld.atlassian.net/rest/api/3/issue/' . $response['key'] . '/attachments';
        $files = !empty($webform_data["attach_file"]) ? $webform_data["attach_file"] : "";
        if (!empty($files)) {
          $files = !empty($webform_data["attach_file"]) ? $webform_data["attach_file"] : "";
          foreach ($files as $fid) {
            $file = File::load($fid);
            if ($file) {
              $file_name = $file->getFilename();
              $file_uri = $file->getFileUri();
              $file_mime = $file->getMimeType();

              $data = [
                'file' => new CURLFile(\Drupal::service('file_system')
                  ->realpath($file_uri), $file_mime, $file_name),
              ];

              $options = [
                CURLOPT_RETURNTRANSFER => TRUE,
                CURLOPT_POSTFIELDS => $data,
                CURLOPT_CUSTOMREQUEST => "POST",
                CURLOPT_HTTPHEADER => [
                  'Content-Type: multipart/form-data',
                  'Authorization:' . $authorization_base64,
                  'X-Atlassian-Token:no-check',
                ],
              ];

              $ch = curl_init($attach_url);
              curl_setopt_array($ch, $options);
              $result = curl_exec($ch);
              curl_close($ch);
              Drupal::logger('attach_file_jira')
                ->info('Response: ' . json_encode($result));
            }
          }
        }
      }
    }
  }

  /**
   * {@inheritdoc}
   */
  public function preSave(WebformSubmissionInterface $webform_submission) {
    $ws_data = $webform_submission->getData();
    if ($ws_data['include_current_env'] == "0") {
      $ws_data['user_agent'] = '';
      $ws_data['location'] = '';
      $ws_data['referrer'] = '';
      $webform_submission->setData($ws_data);
    }
  }

}
