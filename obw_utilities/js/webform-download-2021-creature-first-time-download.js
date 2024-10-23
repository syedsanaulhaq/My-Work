(function ($, window) {

  window.addEventListener('load', function () {
    triggerFileDownload();
  });

  function triggerFileDownload() {
    if (typeof ($.cookie('download_creature_first_time_visit')) !== 'undefined' && !isNaN($.cookie('download_creature_first_time_visit'))) {
      var id = $.cookie('download_creature_first_time_visit');
      if (id !== '') {
        if (typeof ($.cookie('download_creature_reuse')) !== 'undefined') {
          var form = $('.story-download-form');
          var html = `
            <div class="story-download-thank-you">
              <div class="close-form">
                <i class="fal fa-times"></i>
              </div>
              <img id="creature-thanks-message" src="https://www.ourbetterworld.org/sites/default/files/Thank_You_dekstop.png" xmlns="http://www.w3.org/1999/html">
              <h3>Thanks for downloading</h3>
              <p>We’ve also sent a link to your email so you can revisit the e-stories anytime.</p>
              <br><br>
              <p class="creature-notify">P.S. Can’t find our email?</br>Check your junk or spam folder.</p>
            </div>
          `;
          $('.story-download-form-overlay').css('display', 'block');
          form.addClass('active');
          form.html(html);
          $(document).ready(function () {
            $('#' + id).trigger('click');
          });
        }
        $.removeCookie('download_creature_first_time_visit', {path: '/'});
      }
    }
  }
})(jQuery, window, Drupal);
