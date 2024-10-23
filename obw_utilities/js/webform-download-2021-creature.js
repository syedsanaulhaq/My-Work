(function ($, window, Drupal, cookies) {

  Drupal.behaviors.obw_utilities = {
    attach: function (context, settings) {
      $('.storybook--item-body', context).once('obw_utilities').on('click', function () {
        var nid = $(this).find('#download-story-hidden').val();
        $.cookie("download_creature_first_time_visit", nid, {expires: 3650, path: '/'});
      });
    }
  };

})(jQuery, window, Drupal);
