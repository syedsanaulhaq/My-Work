(function ($, Drupal, drupalSettings) {
  var running_count = 1;

  var element_add_filter = {
    element_type: 'filter',
    common_fields: {
      event: 'content_filter',
      eventCategory: 'stories',
      eventAction: 'Select Filter'
    },
    list_elements: [
      {
        id: 'select#edit-causes',
        element_type: 'causes'
      },
      {
        id: 'select#edit-countries',
        element_type: 'countries'
      }
    ]
  };
  var element_remove_filter = {
    element_type: 'filter',
    common_fields: {
      event: 'content_filter',
      eventCategory: 'stories',
      eventAction: 'Remove Filter'
    },
    select_2: {
      cause: {
        id: "select#edit-causes"
      },
      country: {
        id: "select#edit-countries"
      }
    },
    list_elements: [
      {
        id: 'select#edit-causes',
        element_type: 'causes'
      },
      {
        id: 'select#edit-countries',
        element_type: 'countries'
      }
    ]
  };
  var element_clear_all_filter = {
    element_type: 'clear_filter',
    common_fields: {
      event: 'content_filter',
      eventCategory: 'stories',
      eventAction: 'Clear all Filters'
    },
    list_elements: [
      {
        id: '.clear-all-filter',
        element_type: 'causes'
      }
    ]
  };

  function idsSelected (id) {
    var selected_items = $(id).select2('data');
    var id_selected = [];
    $(selected_items).each(function () {
      id_selected.push(this.id);
    });
    return id_selected.join(',');
  }

  function unSelected (data, element) {
    var cause_selected   = idsSelected(element.list_elements[0].id);
    var country_selected = idsSelected(element.list_elements[1].id);

    var custom_dynamic_fields = {
      eventLabel: 'Remove: ' + data.id,
      filter: 'actions=;causes=' + cause_selected +
          ';countires=' + country_selected + ';tags='
    };

    Drupal.custom.trackingCommon.handle_layer_item(
        element,
        {},
        custom_dynamic_fields
    );
  }

  Drupal.behaviors.pageLoad = {
    attach: function(context, settings) {
      if (running_count !== 1) {
        return;
      }
      running_count++;
      var _this = $;

      var $causeSelect    = $(element_remove_filter.select_2.cause.id);
      var $countrySelect  = $(element_remove_filter.select_2.country.id);

      $causeSelect.select2();
      $countrySelect.select2();

      // console.log('------ FILTER.js ------');
      // console.time('[time] FILTER.js');
      // console.time('[time] add filter');
      element_add_filter.list_elements.forEach(function (item) {
        _this(item.id).on('select2:select', function(e) {
          var data = e.params.data;

          var cause_selected = idsSelected(element_add_filter.list_elements[0].id);
          var country_selected = idsSelected(element_add_filter.list_elements[1].id);

          var custom_dynamic_fields = {
            eventLabel: 'Select: ' + data.id,
            filter: 'actions=;causes=' + cause_selected +
                ';countires=' + country_selected + ';tags='
          };

          Drupal.custom.trackingCommon.handle_layer_item(
              element_add_filter,
              {},
              custom_dynamic_fields
          );
        });
      });
      // console.timeEnd('[time] add filter');
      //
      // console.time('[time] remove filter');
      $causeSelect.on("select2:unselect", function (e) {
        var data = e.params.data;
        unSelected(data, element_remove_filter);
      });
      $countrySelect.on("select2:unselect", function (e) {
        var data = e.params.data;
        unSelected(data, element_remove_filter);
      });

      // console.timeEnd('[time] remove filter');
      //
      // console.time('[time] clear all filter');
      _this(element_clear_all_filter.list_elements[0].id).click(function (event) {
        var cause_selected = idsSelected(element_remove_filter.list_elements[0].id);
        var country_selected = idsSelected(element_remove_filter.list_elements[1].id);
        var event_label = "";

        if (cause_selected && country_selected) {
          event_label = cause_selected + "," + country_selected;
        } else if (cause_selected) {
          event_label = cause_selected;
        } else {
          event_label = country_selected;
        }

        var custom_dynamic_fields = {
          eventLabel: event_label,
          filter: 'actions=;causes=;countires=;tags='
        };

        Drupal.custom.trackingCommon.handle_layer_item(
            element_clear_all_filter,
            {},
            custom_dynamic_fields
        );
      });
      // console.timeEnd('[time] clear all filter');
      //
      // console.timeEnd('[time] FILTER.js');

    }
  };
})(jQuery, Drupal, drupalSettings);