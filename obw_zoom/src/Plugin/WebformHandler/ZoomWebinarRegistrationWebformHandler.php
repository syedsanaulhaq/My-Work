<?php

namespace Drupal\obw_zoom\Plugin\WebformHandler;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use Drupal\webform\Plugin\WebformHandlerBase;
use Drupal\webform\WebformSubmissionInterface;
use Firebase\JWT\JWT;


/**
 * Send a webform registration to Zoom webinar upon submission.
 *
 * @WebformHandler(
 *   id = "zoom_api_webinar",
 *   label = @Translation("Send webinar Registration to Zoom"),
 *   category = @Translation("Web Services"),
 *   description = @Translation("Sends a Webform registration to Zoom webinar upon submission."),
 *   cardinality = \Drupal\webform\Plugin\WebformHandlerInterface::CARDINALITY_UNLIMITED,
 *   results = \Drupal\webform\Plugin\WebformHandlerInterface::RESULTS_PROCESSED,
 *   submission = \Drupal\webform\Plugin\WebformHandlerInterface::SUBMISSION_REQUIRED,
 * )
 */

class ZoomWebinarRegistrationWebformHandler extends WebformHandlerBase {

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() { 
    return [];
  }   

	//function to generate JWT
	private function generateJWTKey() {
    $zoom_config = \Drupal::config('obw_zoom.config');
    $apiKey = $zoom_config->get('obw_zoom_api_key');
    $apiSecret = $zoom_config->get('obw_zoom_secret_key');
		$key = $apiKey;
		$secret = $apiSecret;
		$token = array(
			"iss" => $key,
			"exp" => time() + 3600 //60 seconds as suggested
		);
		return JWT::encode( $token, $secret );
	}
  
  /**
   * {@inheritdoc}
   */
  public function preSave(WebformSubmissionInterface $webform_submission) {
    $zoom_config = \Drupal::config('obw_zoom.config');
    $data = $webform_submission->getData();       
    
    $headers = array(
			"authorization: Bearer ". $this->generateJWTKey(),
			"content-type: application/json",
			"Accept: application/json",
		);
    
    //$meetingId = "81563219574";
    $webinarId = $zoom_config->get('obw_zoom_sdk_webinar_registrants_id');         
    
    $request_url = "https://api.zoom.us/v2/webinars/$webinarId/registrants";    
    
    $email = $data["email"];
    $first_name = $data["first_name"];
    $last_name = $data["last_name"];
    $phone_number = $data["field_account_contact_number"]['phone'];    
    
    $payload = "{\"email\":\"$email\",\"first_name\":\"$first_name\",\"last_name\":\"$last_name\",\"address\":\"1800 Amphibious Blvd.\",\"city\":\"Mountain View\",\"country\":\"US\",\"phone\":\"$phone_number\",\"custom_questions\":[{\"title\":\"What do you hope to learn from this\"}]}";             
    
   
    $ch = curl_init();
    curl_setopt_array($ch, array(
        CURLOPT_URL => $request_url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => "",
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 30,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => "POST",
        CURLOPT_POSTFIELDS => $payload,        
        CURLOPT_HTTPHEADER => $headers,
    ));
    
    $response = curl_exec($ch);
    $err = curl_error($ch);
    curl_close($ch);
    if (!$response) {      
      \Drupal::logger('zoom_webinar_error')->warning('<pre><code>' . print_r(json_decode($err), TRUE) . '</code></pre>');
    }     
    \Drupal::logger('zoom_webinar_success')->warning('<pre><code>' . print_r(json_decode($response), TRUE) . '</code></pre>');
  }

}
