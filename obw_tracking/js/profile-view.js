var eventParams = JSON.parse(drupalSettings.tracking_seo.event_item);

(function ($, Drupal, drupalSettings) {
  var running_count = 1;

  var element_profile_view = {
    element_type: "profile_view",
    common_fields: {
      event: "generic",
      eventCategory: "Our Team",
      eventAction: "Profile Views",
      pagename:         eventParams.pagename,
      previousPage:     eventParams.previousPage,
      userID:           eventParams.userID,
      userCookieID:     eventParams.userCookieID,
      storyPerference:  eventParams.storyPerference
    },
    list_elements: [
      {
        id: '.meet-the-team-list .wrap-item',
      }
    ]
  };
  Drupal.behaviors.proFileView = {
    attach: function(context, settings) {
      if (running_count !== 1) {
        return;
      }
      running_count = running_count + 1;
      var _this = $;

      Drupal.custom.trackingCommon.handle_element(
          _this,
          element_profile_view,
          function (element, item, jquery_item) {
            var custom_dynamic_fields = {};
            var profile_name = _this(jquery_item).find(".desc .head-desc .name").text().trim();
            profile_name = profile_name.replace(/(\r\n|\n|\r)/gm, "");
            
            custom_dynamic_fields.eventLabel = profile_name;

            return custom_dynamic_fields;
          }
      );

      // console.log("------ PROFILE-VIEW.js ------");
      // console.time("[time] PROFILE-VIEW.js");
      // console.timeEnd("[time] PROFILE-VIEW.js");
    }
  };
})(jQuery, Drupal, drupalSettings);