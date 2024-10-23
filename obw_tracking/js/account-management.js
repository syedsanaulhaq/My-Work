(function ($, Drupal, drupalSettings) {
  var running_count = 1;

  var element_chage_email_settings = {
    element_type: 'email_setting',
    common_fields: {
      event: "newsletter",
      eventCategory: "newsletter_submit",
      eventLabel: "Checkbox | Account Settings",
      newsletterSub: 1
    },
    email_subscribe_button: {
      checkbox_id: "input[checked=\"checked\"]#edit-field-account-news-subscribed",
      list_elements: [
        {
          id: "input#edit-field-account-news-subscribed"
        },
        {
          id: "label[for=\"edit-field-account-news-subscribed\"]"
        }
      ]
    },
    list_elements: [
      {
        id: ".email-settings input[type=\"submit\"]"
      }
      ]
  };

  Drupal.behaviors.accountManagement = {
    attach: function(context, settings) {
      if (running_count !== 1) {
        return;
      }
      running_count = running_count + 1;
      var _this = $;
      var email_subscribe_checkbox_id = element_chage_email_settings.email_subscribe_button.checkbox_id;
      var is_subscribe_email = _this(email_subscribe_checkbox_id).length > 0;

      // console.log("------ ACCOUNT-MANAGEMENT.js ------");
      // console.time("[time] ACCOUNT-MANAGEMENT.js");

      // change subscribe email status when user click button
      element_chage_email_settings.email_subscribe_button.
      list_elements.forEach(function(item) {
        _this(item.id).click(function(event) {
          is_subscribe_email = !is_subscribe_email;
        });
      });

      // handle when User save their profile
      Drupal.custom.trackingCommon.handle_element(
          _this,
          element_chage_email_settings,
          function(element, item, jquery_item) {
            var custom_dynamic_fields = {};
            var subscript_status = is_subscribe_email ? "Subscribes" : "Unsubscribes";

            custom_dynamic_fields.eventAction = subscript_status;
            return custom_dynamic_fields;
          }
      );

      // console.timeEnd("[time] ACCOUNT-MANAGEMENT.js");
    }
  };
})(jQuery, Drupal, drupalSettings);