<?php


namespace Drupal\obw_utilities\Controller;


use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Drupal\stripe_popup\StripeManager;

class SupportUsController extends ControllerBase {

  public function init($email, $amount): JsonResponse {
    if (filter_var($email, FILTER_VALIDATE_EMAIL) && is_numeric($amount)) {
      return new JsonResponse([
        'total_donation' => StripeManager::getTotalDonationByMail($email,$amount,TRUE),
      ]);
    }
    return new JsonResponse([
      'total_donation' => 0,
    ]);
  }


}
