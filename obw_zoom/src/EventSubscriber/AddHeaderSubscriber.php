<?php

namespace Drupal\obw_zoom\EventSubscriber;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\ResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;

class AddHeaderSubscriber implements EventSubscriberInterface {    

  public static function getSubscribedEvents() {    
    return [
      KernelEvents::RESPONSE => 'onResponse',
    ];    
  } 

  public function onResponse(ResponseEvent $event) {
    $response = $event->getResponse();
    $route_name = \Drupal::service('current_route_match')->getRouteName();      
    if ($route_name == 'obw_zoom.fe') {       
      // $response->headers->set('cross-origin-resource-policy', '*'); 
      $response->headers->set('Cross-Origin-Embedder-Policy', 'require-corp'); 
      $response->headers->set('Cross-Origin-Opener-Policy', 'same-origin');    
    }
    
  }

}

