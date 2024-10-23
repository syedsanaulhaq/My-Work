<?php

namespace Drupal\obw_zoom\Controller;


use Drupal\Core\Controller\ControllerBase;

/**
 * An example controller.
 */
class ObwZoom extends ControllerBase {

  public function init() {
    $zoom_config = \Drupal::config('obw_zoom.config');
    $apiKey = $zoom_config->get('obw_zoom_api_key');
    $apiSecret = $zoom_config->get('obw_zoom_secret_key');
    $meeting_number = $zoom_config->get('obw_zoom_mn');
    $roles = $zoom_config->get('obw_zoom_roles');        

    if ( \Drupal::currentUser()->isAnonymous()) {
      return [        
        '#theme' => 'custom_zoom_jwt_page',
        '#attached' => [
          'library' => [
            'obw_zoom/obw-zoom-not-login',
          ],
        ]
      ];
    } else { 
      return [
        //'#markup' => '<div id="meetingSDKElements"></div>',
        //'#allowed_tags' => ['script', 'div'],
        '#theme' => 'custom_zoom_jwt_page',
        '#attached' => [
          'library' => [
            'obw_zoom/obw-zoom',
          ],
          'drupalSettings' => [
            'apiKey' => $apiKey,    
            'apiSecret' => $apiSecret, 
            'mn' => $meeting_number,
            'name' => \Drupal::currentUser()->getAccountName(),
            'email' => \Drupal::currentUser()->getEmail(),
            'pwd' => $zoom_config->get('obw_zoom_pwd'),
            'role' => $zoom_config->get('obw_zoom_roles'),
            'signature' => $zoom_config->get('obw_zoom_signature'),          
            'china' => $zoom_config->get('obw_zoom_area'),
            'lang' => $zoom_config->get('obw_zoom_lang'),
            'version' => '2.4.5',
          ],
        ],
      ];
    }    
  }  
  
}

