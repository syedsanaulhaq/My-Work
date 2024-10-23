<?php

namespace Drupal\obw_utilities\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class WebCalGenerateController.
 */
class WebCalGenerateController extends ControllerBase {

  /**
   * Drupal\Core\Entity\EntityTypeManagerInterface definition.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * Constructs a new WebCalGenerateController object.
   */
  public function __construct(EntityTypeManagerInterface $entity_type_manager) {
    $this->entityTypeManager = $entity_type_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('entity_type.manager')
    );
  }

  /**
   * Generate.
   *
   * @return string
   *   Return Hello string.
   */
  public function generate($id, $subject, $date_start, $date_end, $detail, $location) {
    $now_day = Date("Ymd\THis");
    $detail = str_replace("\r\n", '\n', $detail);
    $calendarEvent =
      'BEGIN:VCALENDAR' . "\n" .
      'PRODID:Calendar' . "\n" .
      'NAME:' . $subject . "\n" .
      'X-WR-CALNAME:' . $subject . "\n" .
      'VERSION:2.0' . "\n" .
      'BEGIN:VEVENT' . "\n" .
      'UID: ' . $id . "\n" .
      'CLASS:PUBLIC' . "\n" .
      'DESCRIPTION:' . str_replace('|', '/', $detail) . "\n" .
      'DTSTAMP;VALUE=DATE-TIME:' . $now_day . "\n" .
      'DTSTART;VALUE=DATE-TIME:' . $date_start . "\n" .
      'DTEND;VALUE=DATE-TIME:' . $date_end . "\n" .
      'LOCATION:' . $location . "\n" .
      'SUMMARY;LANGUAGE=en-us:' . $subject . "\n" .
      'TRANSP:TRANSPARENT' . "\n" .
      'END:VEVENT' . "\n" .
      'END:VCALENDAR' . "\n";
    $response = new Response();
    $response->headers->set('Content-Type', 'text/calendar');
    // Set headers
    $response->headers->set('Pragma', 'no-cache');
    $response->headers->set('Content-Disposition', 'attachment; filename="event-' . $id . '.ics"');
    $response->headers->set('Content-Transfer-Encoding', 'binary');
    $response->headers->set('Cache-control', 'private');
    $response->headers->set('Content-length', strlen($calendarEvent));

    $response->setContent($calendarEvent);

    return $response;

  }

}
