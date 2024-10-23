<?php

namespace Drupal\obw_utilities\Controller;

use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\HttpFoundation\RedirectResponse;

/**
 *
 */
class WlDownloadController extends ControllerBase {

  const MAPPING_STORY_WITH_WF = [
    'spotted-in-india-humans-and-leopards-living-in-harmony' => 'leopard_pdf_2021',
  ];

  const URL_WL_DOWNLOAD_PAGE = 'series/a-wild-life/story/{story_name}/download/page';

  /**
   * @param $story_name
   *
   * @return \Symfony\Component\HttpFoundation\RedirectResponse
   */
  public function dlCheck($story_name) {
    if (empty(self::MAPPING_STORY_WITH_WF[$story_name])) {
      return new RedirectResponse('/404');
    }

    return $this->redirectWlDownloadPage($story_name);
  }

  /**
   * @param $story_name
   *
   * @return \Symfony\Component\HttpFoundation\RedirectResponse
   */
  public function fbCheck($story_name) {
    if (empty(self::MAPPING_STORY_WITH_WF[$story_name])) {
      return new RedirectResponse('/404');
    }
    $wf_id = self::MAPPING_STORY_WITH_WF[$story_name];
    if (empty($_COOKIE['wl2021_' . $wf_id . '_cookie'])) {
      $request = \Drupal::request();
      if (!empty($request->server->get('HTTP_REFERER'))) {
        \Drupal::logger('wl2021')->info($request->server->get('HTTP_REFERER'));
        $referer = $request->server->get('HTTP_REFERER');
        if (preg_match('/https?:\/\/(l|m)\.facebook\.com\/$/', $referer)) {
          $request_query = $request->query;
          if (!empty($request_query->get('utm_source')) && $request_query->get('utm_source') === 'facebook'
            && !empty($request_query->get('utm_medium')) && $request_query->get('utm_medium') === 'video_lead_ad'
            && !empty($request_query->get('utm_campaign')) && $request_query->get('utm_campaign') === 'IN_TitliTrust') {
            setcookie('wl2021_' . $wf_id . '_cookie', TRUE, 0, '/');
            \Drupal::logger('wl2021')->info('set cookie successful!');
          }
        }
      }
    }
    return $this->redirectWlDownloadPage($story_name);
  }

  /**
   * @param $story_name
   *
   * @return \Symfony\Component\HttpFoundation\RedirectResponse
   */
  private function redirectWlDownloadPage($story_name) {
    $url_dl_page = '/' . str_replace('{story_name}', $story_name, self::URL_WL_DOWNLOAD_PAGE);
    return new RedirectResponse($url_dl_page);
  }

}
