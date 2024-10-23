(function ($, Drupal, drupalSettings) {
  var running_count = 1;

  var element_story_click = {
    element_type: 'filter',
    common_fields: {
      event: 'content_view',
      eventCategory: 'Story Clickthrough',
      eventAction: 'Story Series',
      contentType: 'series'
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
        id: '.stories-card .stories-desc .vote a',
        element_type: 'stories-vote'
      },
      {
        id: '.winner-item .desc.text-center a',
        element_type: 'winner-item'
      }
    ]
  };
  Drupal.behaviors.seriesGsoty = {
    attach: function(context, settings) {
      if (running_count !== 1) {
        return;
      }
      running_count++;
      var _this = $;

      // console.log('------ SERIES-GSOTY.js ------');
      // console.time('[time] SERIES-GSOTY.js');
      //
      // console.time('[time] Story clickthrough');

      var path_name     = window.location.pathname;
      var series_regex  = /(gsoty|series)\/[a-zA-Z0-9]+/;

      if (series_regex.test(path_name)) {
        element_story_click.list_elements.forEach(function(item) {
          _this(item.id).click(function(event) {
            // event.preventDefault();
            var element_type  = item.element_type;
            var series_name   = dataLayer[0].eventLabel;
            var series_title  = dataLayer[0].contentName;
            var series_id     = dataLayer[0].contentID;
            var story_title   = '';

            var custom_dynamic_fields = {
              contentName:  series_title,
              contentID:    series_id
            };

            switch (element_type) {
              case 'stories-title':
                story_title = this.innerText.trim();
                break;
              case 'stories-img':
                story_title = _this(this).parent().
                siblings('.stories-desc').find('a:first-child').text().trim();
                break;
              case 'stories-vote':
                story_title = _this(this).parent().
                siblings('h3').text().trim();
                break;
              case 'winner-item':
                if (this.innerText.trim() === 'VIEW STORY') {
                  story_title = _this(this).
                  siblings('h3').text().trim();
                } else {
                  story_title = this.innerText.trim();
                }
                break;
              default:
                break;
            }
            custom_dynamic_fields = {
              eventLabel:   series_name + ' | ' + story_title
            };
            Drupal.custom.trackingCommon.handle_layer_item(
                element_story_click,
                {},
                custom_dynamic_fields
            );
          });
        });
      }
      // console.timeEnd('[time] Story clickthrough');
      //
      // console.timeEnd('[time] SERIES-GSOTY.js');
    }
  };
})(jQuery, Drupal, drupalSettings);