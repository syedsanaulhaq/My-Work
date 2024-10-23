(function ($, Drupal, drupalSettings) {
  var running_count = 1;

  var element_subscription = {
    element_type: 'Subscribes',
    common_fields: {
      event: 'newsletter',
      eventCategory: 'newsletter_submit',
      eventAction: 'Subscribes',
      eventLabel: 'Footer',
      newsletterSub: 1
    }
  };

  Drupal.behaviors.subscription = {
    attach: function (context, settings) { // jshint ignore:line
      if (running_count !== 1) {
        return;
      }
      running_count++;

      var _this = $;

      // console.log('------ SUBSCRIPTION ------');
      //
      // console.time('[time] subscription thank you message');
      $(document).ajaxSuccess(function(event) {
        var subscription_message = _this('#subscribe form.webform-submission-form .webform-confirmation__message');
        if (subscription_message.length > 0) {
          Drupal.custom.trackingCommon.handle_layer_item(
              element_subscription,
              {},
              {}
          );
        }
      });
      // console.timeEnd('[time] subscription thank you message');
    }
  };
})(jQuery, Drupal, drupalSettings);