(function ($, Drupal, drupalSettings) {
  var running_count = 1;

  var element_story_click = {
    element_type: "story_from_my_action",
    common_fields: {
      event: "generic",
      eventCategory: "Story Clickthrough",
      eventAction: "Story Reference"
    },
    list_elements: [
      {
        id: '.stories-card .stories-desc h3 a',
        element_type: 'stories-title'
      },
      {
        id: '.stories-card .stories-img a',
        element_type: 'stories-img'
      },
      {
        id: '.views-field-name .field-content',
        element_type: 'stories-field'
      }
    ]
  };
  Drupal.behaviors.actionInProfile = {
    attach: function(context, settings) {
      if (running_count !== 1) {
        return;
      }
      running_count = running_count + 1;
      var _this = $;
      var tab_name = "Stories I like";


      _this(".my-action .tabs-head ul > li > a").click(function(event) {
        tab_name = this.innerText.trim();
      });

      Drupal.custom.trackingCommon.handle_element(
          _this,
          element_story_click,
          function (element, item, jquery_item) {
            var element_type  = item.element_type;
            var custom_dynamic_fields = {};
            var story_title   = '';

            switch (element_type) {
              case 'stories-title':
                story_title = jquery_item.innerText.trim();
                break;
              case 'stories-img':
                story_title = _this(jquery_item).closest(".stories-card").
                find('.stories-desc').find('h3 a').text().trim();
                break;
              case 'stories-field':
                story_title = jquery_item.innerText.trim();
                break;
            }
            custom_dynamic_fields.eventLabel = tab_name + " | " + story_title;

            return custom_dynamic_fields;
          }
      );

      // console.log("------ ACTION-IN-PROFILE.js ------");
      // console.time("[time] ACTION-IN-PROFILE.js");
      // console.timeEnd("[time] ACTION-IN-PROFILE.js");
    }
  };
})(jQuery, Drupal, drupalSettings);