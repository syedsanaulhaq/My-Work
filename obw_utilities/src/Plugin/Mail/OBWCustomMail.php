<?php

namespace Drupal\obw_utilities\Plugin\Mail;

use Drupal\Core\Mail\MailFormatHelper;
use Drupal\webform\Plugin\Mail\WebformPhpMail;

/**
 * Extend's the default Drupal mail backend to support HTML email.
 *
 * @Mail(
 *   id = "obw_custom_mail",
 *   label = @Translation("OBW Custom Mail"),
 *   description = @Translation("Sends the message as plain text or HTML, using
 *   PHP's native mail() function.")
 * )
 */
class OBWCustomMail extends WebformPhpMail {

  /**
   * {@inheritdoc}`
   */
  public function format(array $message) {
    // Join the body array into one string.
    $message['body'] = implode("\n\n", $message['body']);

    if (!empty($message['params']['html'])) {
      // Wrap body in HTML template if the <html> tag is missing.
      if (strpos($message['body'], '<html') === FALSE) {
        // Make sure parameters exist.
        $message['params'] += ['webform_submission' => NULL, 'handler' => NULL];
        $default_mail_theme = 'webform_email_obw_html';
        if (key_exists('X-PM-Tag', $message['headers']) && $message['headers']['X-PM-Tag'] == 'OBW Birthd8') {
          $default_mail_theme = 'webform_email_obw_birthd8';
        }
        $build = [
          '#theme' => $default_mail_theme,
          '#body' => $message['body'],
          '#subject' => $message['subject'],
          '#webform_submission' => $message['params']['webform_submission'],
          '#handler' => $message['params']['handler'],
          '#tbt' => !empty($message['tbt-logo']) ? $message['tbt-logo'] : FALSE,
        ];

        $message['body'] = \Drupal::service('renderer')->renderPlain($build);
      }
      return $message;
    }
    else {
      // Wrap the mail body for sending.
      $message['body'] = MailFormatHelper::wrapMail($message['body']);
      return $message;
    }
  }

}
