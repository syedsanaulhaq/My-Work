(function ($, Drupal, drupalSettings) {
  var running_count = 1;

  Drupal.behaviors.markup = {
    attach: function(context, settings) {
      if (running_count !== 1) {
        return;
      }
      running_count = running_count + 1;
      var _this = $;

      // console.log("------ MARKUP.js ------");
      // console.time("[time] MARKUP.js");
      // console.timeEnd("[time] MARKUP.js");
    }
  };
})(jQuery, Drupal, drupalSettings);