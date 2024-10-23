<?php

namespace Drupal\obw_utilities\Theme;

use Drupal\Core\Url;
use Drupal\views\Entity\View;

class PreprocessViewManager {

  public static function renderShareBlockInPreprocessView(&$variables) {
    if (!empty($variables['view_array']) && !empty($variables['view_array']['#view_id']) && $variables['view_array']['#view_id'] == 'story' && $variables['view_array']['#display_id'] == 'page_mental_health_2020_help_page') {
      $view_id = $variables['view_array']['#view_id'];
      $display_id = $variables['view_array']['#display_id'];
      $view_title = $variables['view_array']['#title']['#markup'];
      $share_html = self::renderShareBlockHtml($view_id, $display_id, $view_title);
      if ($share_html) {
        $variables['share_block_in_preprocess_view'] = $share_html;
      }
    }
  }

  public static function renderShareBlockHtml($view_id, $display_id, $view_title) {
    $view_url = NULL;
    global $base_url;
    $metatag_twitter_title = NULL;
    $view = View::load($view_id);
    if (!empty($view->getDisplay($display_id)['display_options']['display_extenders'])
      && !empty($view->getDisplay($display_id)['display_options']['display_extenders']['metatag_display_extender'])
      && !empty($view->getDisplay($display_id)['display_options']['display_extenders']['metatag_display_extender']['metatags']['twitter_cards_title'])) {
      $metatag_twitter_title = $view->getDisplay($display_id)['display_options']['display_extenders']['metatag_display_extender']['metatags']['twitter_cards_title'];
    }

    try {
      $view_url = Url::fromRoute('view.' . $view_id . '.' . $display_id)
        ->toString();
    } catch (\Exception $exception) {
      \Drupal::logger('get_view_url')->info($exception->getMessage());
    }

    if ($view_url == NULL) {
      return FALSE;
    }

    $title_share_twitter = $metatag_twitter_title ? $metatag_twitter_title : $view_title;
    $share_html = '<div class="share">
        <div id="fb-root"></div>
        <div class="share-title">
          <span class="share-label">SHARE<span class="number total-share"></span> </span>
          <svg width="11" height="12" viewBox="0 0 11 12" fill="none" xmlns="http://www.w3.org/2000/svg">
            <path d="M9 8.46C8.52 8.46 8.16 8.64 7.8 8.94L3.54 6.42C3.6 6.3 3.6 6.12 3.6 6C3.6 5.88 3.6 5.7 3.54 5.58L7.8 3.12C8.1 3.42 8.52 3.6 9 3.6C10.02 3.6 10.8 2.82 10.8 1.8C10.8 0.78 10.02 0 9 0C7.98 0 7.2 0.78 7.2 1.8C7.2 1.92 7.2 2.1 7.26 2.22L3 4.68C2.7 4.38 2.28 4.2 1.8 4.2C0.78 4.2 0 4.98 0 6C0 7.02 0.78 7.8 1.8 7.8C2.28 7.8 2.7 7.62 3 7.32L7.26 9.84C7.2 9.96 7.2 10.08 7.2 10.2C7.2 11.16 7.98 11.94 8.94 11.94C9.9 11.94 10.68 11.16 10.68 10.2C10.68 9.24 9.96 8.46 9 8.46Z" fill="#333333"/>
          </svg>
        </div>
        <div class="icon-container">
          <div class="icon-wrapper">
              <span class="share-icon share-icon-facebook" data-platform="facebook">
                <a href="#" id="shareBtn" style="cursor: pointer;" class="ico-facebook">
                  <svg width="32" height="32" viewBox="0 0 32 32" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <circle cx="16" cy="16" r="16"/>
                    <path d="M25 16C25 11.0289 20.9711 7 16 7C11.0289 7 7 11.0289 7 16C7 20.493 10.2906 24.216 14.5938 24.891V18.6016H12.3086V16H14.5938V14.0172C14.5938 11.7619 15.9367 10.5156 17.9934 10.5156C18.9777 10.5156 20.0078 10.6914 20.0078 10.6914V12.9062H18.8723C17.7543 12.9062 17.4062 13.6006 17.4062 14.3125V16H19.9023L19.5033 18.6016H17.4062V24.891C21.7094 24.216 25 20.493 25 16Z" fill="white"/>
                    <path class="icon-inner" d="M19.5033 18.6016L19.9023 16H17.4062V14.3125C17.4062 13.6006 17.7543 12.9062 18.8723 12.9062H20.0078V10.6914C20.0078 10.6914 18.9777 10.5156 17.9934 10.5156C15.9367 10.5156 14.5938 11.7619 14.5938 14.0172V16H12.3086V18.6016H14.5938V24.891C15.0525 24.9631 15.5219 25 16 25C16.4781 25 16.9475 24.9631 17.4062 24.891V18.6016H19.5033Z" />
                  </svg>
                </a>
              </span>
              <span class="share-icon share-icon-twitter" data-platform="twitter">
                <a class="ico-twitter"
                   href="https://twitter.com/intent/tweet?text=' . $title_share_twitter . '&url=' . $base_url . $view_url . '"
                   onclick="popupwindow(this.href,\'Share Twitter\', 800, 500); return false;">
                    <svg width="32" height="32" viewBox="0 0 32 32" fill="none" xmlns="http://www.w3.org/2000/svg">
                      <circle cx="16" cy="16" r="16"/>
                      <path d="M13.28 25.256C20.824 25.256 24.952 19 24.952 13.584C24.952 13.408 24.952 13.232 24.944 13.056C25.744 12.48 26.44 11.752 26.992 10.928C26.256 11.256 25.464 11.472 24.632 11.576C25.48 11.072 26.128 10.264 26.44 9.304C25.648 9.776 24.768 10.112 23.832 10.296C23.08 9.496 22.016 9 20.84 9C18.576 9 16.736 10.84 16.736 13.104C16.736 13.424 16.776 13.736 16.84 14.04C13.432 13.872 10.408 12.232 8.384 9.752C8.032 10.36 7.832 11.064 7.832 11.816C7.832 13.24 8.56 14.496 9.656 15.232C8.984 15.208 8.352 15.024 7.8 14.72C7.8 14.736 7.8 14.752 7.8 14.776C7.8 16.76 9.216 18.424 11.088 18.8C10.744 18.896 10.384 18.944 10.008 18.944C9.744 18.944 9.488 18.92 9.24 18.872C9.76 20.504 11.28 21.688 13.072 21.72C11.664 22.824 9.896 23.48 7.976 23.48C7.648 23.48 7.32 23.464 7 23.424C8.808 24.576 10.968 25.256 13.28 25.256Z" fill="white"/>
                    </svg>
                </a>
              </span>
              <span class="share-icon share-icon-email" data-platform="email">
                <a href="/form/email-a-friend?redirect_url=' . $view_url . '" data-dialog-type="modal"
                   class="ico-email use-ajax"
                   data-dialog-options=\'{"width":550, "show":"slideDown" , "closeOnEscape":true}\'>
                  <svg width="32" height="32" viewBox="0 0 32 32" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <circle cx="16" cy="16" r="16" />
                    <path d="M23 9H9.66667C8.75 9 8 9.75 8 10.6667V20.6667C8 21.5833 8.75 22.3333 9.66667 22.3333H23C23.9167 22.3333 24.6667 21.5833 24.6667 20.6667V10.6667C24.6667 9.75 23.9167 9 23 9ZM23 12.3333L16.3333 16.5L9.66667 12.3333V10.6667L16.3333 14.8333L23 10.6667V12.3333Z" fill="white"/>
                  </svg>
                </a>
              </span>
              <span id="whatsapp-detect-mobile" class="whatsapp-detect-mobile share-icon share-icon-whatsapp mr-0" data-platform="whatsapp">
                <a class="ico-what" href="https://api.whatsapp.com/send?text=' . $view_title . '%0a' . $base_url . $view_url . '"
                   data-action="share/whatsapp/share">
                  <svg width="32" height="32" viewBox="0 0 32 32" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <circle cx="16" cy="16" r="16" />
                    <path fill-rule="evenodd" clip-rule="evenodd" d="M22.3745 9.61725C20.6858 7.92801 18.4342 7 16.0365 7C11.0993 7 7.07735 11.0029 7.07305 15.9166C7.07305 17.4904 7.48556 19.0214 8.26761 20.377L7 25L11.7524 23.7598C13.063 24.4697 14.5369 24.846 16.0365 24.846H16.0408C20.978 24.846 25 20.8432 25.0043 15.9252C25 13.5431 24.0676 11.3022 22.3745 9.61725ZM16.0365 23.3364C14.6959 23.3364 13.3853 22.9772 12.2423 22.3015L11.9716 22.139L9.15278 22.8746L9.90475 20.1376L9.72857 19.8553C8.9809 18.675 8.58988 17.3108 8.58988 15.9123C8.58988 11.8325 11.9329 8.50535 16.0408 8.50535C18.0303 8.50535 19.8995 9.2794 21.3089 10.6778C22.714 12.0805 23.4875 13.9408 23.4875 15.9209C23.4832 20.0135 20.1401 23.3364 16.0365 23.3364ZM20.1229 17.7855C19.8995 17.6743 18.7995 17.1354 18.5932 17.0584C18.387 16.9857 18.2366 16.9473 18.0905 17.1696C17.9401 17.392 17.5104 17.8967 17.3815 18.0421C17.2526 18.1917 17.1194 18.2088 16.8959 18.0976C16.6725 17.9865 15.9506 17.7512 15.0955 16.99C14.4295 16.3999 13.9826 15.6686 13.8494 15.4462C13.7205 15.2238 13.8365 15.1041 13.9482 14.9929C14.047 14.8945 14.1716 14.732 14.2834 14.6037C14.3951 14.4754 14.4338 14.3813 14.5068 14.2316C14.5799 14.082 14.5455 13.9537 14.4896 13.8425C14.4338 13.7313 13.9869 12.6322 13.7978 12.1875C13.6173 11.7512 13.4326 11.8111 13.2951 11.8068C13.1661 11.7983 13.0158 11.7983 12.8654 11.7983C12.715 11.7983 12.4743 11.8539 12.2681 12.0763C12.0618 12.2986 11.486 12.8375 11.486 13.9366C11.486 15.0356 12.2896 16.0919 12.4013 16.2416C12.513 16.3913 13.9783 18.6408 16.2256 19.6073C16.7584 19.8382 17.1752 19.9751 17.5018 20.0777C18.0389 20.2488 18.5245 20.2231 18.9112 20.1675C19.3409 20.1033 20.2347 19.6287 20.4237 19.1069C20.6085 18.5852 20.6085 18.1404 20.5526 18.0463C20.4968 17.9522 20.3464 17.8966 20.1229 17.7855Z" fill="white"/>
                  </svg>
                </a>
              </span>
            <span id="whatsapp-detect-pc" class="whatsapp-detect-pc share-icon share-icon-whatsapp mr-0" data-platform="whatsapp">
              <a class="ico-what"
                 href="https://web.whatsapp.com/send?text=' . $view_title . '%0a' . $base_url . $view_url . '"
                 onclick="popupwindow(this.href,\'Share Whatsapp\', 800, 500); return false;">
                  <svg width="32" height="32" viewBox="0 0 32 32" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <circle cx="16" cy="16" r="16" />
                    <path fill-rule="evenodd" clip-rule="evenodd" d="M22.3745 9.61725C20.6858 7.92801 18.4342 7 16.0365 7C11.0993 7 7.07735 11.0029 7.07305 15.9166C7.07305 17.4904 7.48556 19.0214 8.26761 20.377L7 25L11.7524 23.7598C13.063 24.4697 14.5369 24.846 16.0365 24.846H16.0408C20.978 24.846 25 20.8432 25.0043 15.9252C25 13.5431 24.0676 11.3022 22.3745 9.61725ZM16.0365 23.3364C14.6959 23.3364 13.3853 22.9772 12.2423 22.3015L11.9716 22.139L9.15278 22.8746L9.90475 20.1376L9.72857 19.8553C8.9809 18.675 8.58988 17.3108 8.58988 15.9123C8.58988 11.8325 11.9329 8.50535 16.0408 8.50535C18.0303 8.50535 19.8995 9.2794 21.3089 10.6778C22.714 12.0805 23.4875 13.9408 23.4875 15.9209C23.4832 20.0135 20.1401 23.3364 16.0365 23.3364ZM20.1229 17.7855C19.8995 17.6743 18.7995 17.1354 18.5932 17.0584C18.387 16.9857 18.2366 16.9473 18.0905 17.1696C17.9401 17.392 17.5104 17.8967 17.3815 18.0421C17.2526 18.1917 17.1194 18.2088 16.8959 18.0976C16.6725 17.9865 15.9506 17.7512 15.0955 16.99C14.4295 16.3999 13.9826 15.6686 13.8494 15.4462C13.7205 15.2238 13.8365 15.1041 13.9482 14.9929C14.047 14.8945 14.1716 14.732 14.2834 14.6037C14.3951 14.4754 14.4338 14.3813 14.5068 14.2316C14.5799 14.082 14.5455 13.9537 14.4896 13.8425C14.4338 13.7313 13.9869 12.6322 13.7978 12.1875C13.6173 11.7512 13.4326 11.8111 13.2951 11.8068C13.1661 11.7983 13.0158 11.7983 12.8654 11.7983C12.715 11.7983 12.4743 11.8539 12.2681 12.0763C12.0618 12.2986 11.486 12.8375 11.486 13.9366C11.486 15.0356 12.2896 16.0919 12.4013 16.2416C12.513 16.3913 13.9783 18.6408 16.2256 19.6073C16.7584 19.8382 17.1752 19.9751 17.5018 20.0777C18.0389 20.2488 18.5245 20.2231 18.9112 20.1675C19.3409 20.1033 20.2347 19.6287 20.4237 19.1069C20.6085 18.5852 20.6085 18.1404 20.5526 18.0463C20.4968 17.9522 20.3464 17.8966 20.1229 17.7855Z" fill="white"/>
                </svg>
              </a>
            </span>
          </div>
          <div class="icon-close-button">
            <svg width="14" height="14" viewBox="0 0 14 14" fill="none" xmlns="http://www.w3.org/2000/svg">
              <path d="M13.3 2.1C13.6866 1.7134 13.6866 1.0866 13.3 0.7C12.9134 0.313401 12.2866 0.313401 11.9 0.7L7 5.6L2.1 0.7C1.7134 0.3134 1.0866 0.313401 0.7 0.7C0.313401 1.0866 0.313401 1.7134 0.7 2.1L5.6 7L0.7 11.9C0.3134 12.2866 0.313401 12.9134 0.7 13.3C1.0866 13.6866 1.7134 13.6866 2.1 13.3L7 8.4L11.9 13.3C12.2866 13.6866 12.9134 13.6866 13.3 13.3C13.6866 12.9134 13.6866 12.2866 13.3 11.9L8.4 7L13.3 2.1Z" fill="#333333"/>
            </svg>
          </div>
        </div>
      </div>';

    return $share_html;
  }

}
